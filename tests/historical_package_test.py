import base64, importlib.util, json, pathlib, sys, tempfile, unittest
sys.path.insert(0,str(pathlib.Path(__file__).resolve().parents[1]/'scripts/recovery'))
import assets
from build_package import build
from sql_reader import read_tables

class RecoveryPackageTests(unittest.TestCase):
    def fixture(self,tmp):
        root=pathlib.Path(tmp)/'original';root.mkdir();(root/'uploads/gifts').mkdir(parents=True)
        image=base64.b64decode('iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAQAAAC1HAwCAAAAC0lEQVR42mP8/x8AAwMCAO+jrfsAAAAASUVORK5CYII=')
        rows=[]
        for i in [1,2,3]+list(range(5,28)):
            (root/f'uploads/gifts/{i}.png').write_bytes(image)
            rows.append(f"({i},'Gift {i}','gift-{i}','<script>alert(1)</script>, text; safe',NULL,'Test','single',10.00,'/uploads/gifts/{i}.png',NULL,1,1,0,1,0,{i},'2026-05-01 00:00:00','2026-05-01 00:00:00')")
        dump=pathlib.Path(tmp)/'source.sql';dump.write_text("SET time_zone = '+00:00'; INSERT INTO gifts (id,name,slug,description,purchase_url,category,type,price,image_path,redirect_url,quantity,available_qty,reserved,is_active,featured,sort_order,created_at,updated_at) VALUES "+','.join(rows)+';',encoding='utf-8')
        return root,dump
    def test_parser_never_executes_and_handles_quoted_delimiters(self):
        s="-- ignored\nINSERT INTO gifts (id,name) VALUES (1,'A, B; C\\\'D'),(2,'it''s fine'); DROP TABLE gifts;"
        self.assertEqual(read_tables(s)['gifts'],[{'id':1,'name':"A, B; C'D"},{'id':2,'name':"it's fine"}])
        self.assertEqual(read_tables("INSERT INTO users(id,password_hash) VALUES (1,'secret');")['gifts'],[])
    def test_preview_and_integrity(self):
        with tempfile.TemporaryDirectory() as tmp:
            root,dump=self.fixture(tmp);out=pathlib.Path(tmp)/'package';m=build(dump,root,out)
            preview=(out/'PREVIA.html').read_text(encoding='utf-8')
            self.assertEqual(preview.count('<article '),26);self.assertNotIn('<script>alert(1)</script>',preview)
            self.assertIn('&lt;script&gt;alert(1)&lt;/script&gt;',preview)
            self.assertEqual(len(assets.plan(out,root)['rows']),26)
            (out/'payload.json').write_text('{}')
            with self.assertRaises(ValueError):assets.plan(out,root)
    def test_images_idempotency_and_rollback_ownership(self):
        with tempfile.TemporaryDirectory() as tmp:
            source,dump=self.fixture(tmp);out=pathlib.Path(tmp)/'package';m=build(dump,source,out)
            root=pathlib.Path(tmp)/'target';(root/'uploads/gifts').mkdir(parents=True)
            (root/'uploads/gifts/1.png').write_bytes((source/'uploads/gifts/1.png').read_bytes())
            receipt=pathlib.Path(tmp)/'receipt';batch=m['payload_sha256']
            self.assertEqual(assets.apply(out,root,receipt,batch)['created'],25)
            self.assertEqual(assets.apply(out,root,receipt,batch)['created'],25)
            with self.assertRaises(ValueError):assets.rollback(root,receipt,batch,'unconfirmed')
            target=root/'uploads/gifts/2.png';target.write_bytes(b'changed')
            with self.assertRaises(ValueError):assets.rollback(root,receipt,batch,batch)
            self.assertEqual(len(list((root/'uploads/gifts').glob('*.png'))),26)
            target.write_bytes((source/'uploads/gifts/2.png').read_bytes())
            self.assertEqual(assets.rollback(root,receipt,batch,batch)['deleted'],25)
            self.assertTrue((root/'uploads/gifts/1.png').exists())
            self.assertEqual(assets.rollback(root,receipt,batch,batch)['deleted'],0)
    def test_conflicting_existing_image_and_path_escape(self):
        with tempfile.TemporaryDirectory() as tmp:
            source,dump=self.fixture(tmp);out=pathlib.Path(tmp)/'package';m=build(dump,source,out)
            root=pathlib.Path(tmp)/'target';(root/'uploads/gifts').mkdir(parents=True)
            (root/'uploads/gifts/1.png').write_bytes(b'owner image')
            with self.assertRaises(ValueError):assets.apply(out,root,pathlib.Path(tmp)/'receipt',m['payload_sha256'])
            self.assertEqual((root/'uploads/gifts/1.png').read_bytes(),b'owner image')
            dump.write_text(dump.read_text().replace('/uploads/gifts/1.png','/uploads/gifts/../../escape.png'))
            with self.assertRaises(ValueError):build(dump,source,pathlib.Path(tmp)/'bad')
    def test_missing_images(self):
        with tempfile.TemporaryDirectory() as tmp:
            root,dump=self.fixture(tmp)
            (root/'uploads/gifts/1.png').unlink()
            with self.assertRaises(ValueError):build(dump,root,pathlib.Path(tmp)/'missing')
    def test_ambiguous_links_require_review(self):
        with tempfile.TemporaryDirectory() as tmp:
            root,dump=self.fixture(tmp)
            s=dump.read_text().replace("NULL,'Test'","'https://example.com/old','Test'",1).replace("'/uploads/gifts/1.png',NULL","'/uploads/gifts/1.png','https://example.com/different'",1)
            dump.write_text(s)
            with self.assertRaises(ValueError):build(dump,root,pathlib.Path(tmp)/'ambiguous')
    def test_legacy_link_mapped_only_when_current_link_missing(self):
        with tempfile.TemporaryDirectory() as tmp:
            root,dump=self.fixture(tmp);dump.write_text(dump.read_text().replace("NULL,'Test'","'https://example.com/historical','Test'",1))
            out=pathlib.Path(tmp)/'mapped';build(dump,root,out)
            payload=json.loads((out/'payload.json').read_text(encoding='utf-8'))
            self.assertEqual(payload['gifts'][0]['redirect_url'],'https://example.com/historical')
            self.assertEqual(payload['historical_gifts'][0]['purchase_url'],'https://example.com/historical')
    def test_builder_source_is_unchanged(self):
        with tempfile.TemporaryDirectory() as tmp:
            root,dump=self.fixture(tmp);before=dump.read_bytes();build(dump,root,pathlib.Path(tmp)/'package')
            self.assertEqual(before,dump.read_bytes())
    def test_unknown_export_timezone_requires_review(self):
        with tempfile.TemporaryDirectory() as tmp:
            root,dump=self.fixture(tmp);dump.write_text(dump.read_text().replace("SET time_zone = '+00:00';",''))
            with self.assertRaises(ValueError):build(dump,root,pathlib.Path(tmp)/'unknown-zone')

if __name__=='__main__':unittest.main()
