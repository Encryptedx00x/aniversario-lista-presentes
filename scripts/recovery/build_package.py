"""Build a private, reviewable recovery package. Does not connect to a database."""
import argparse, hashlib, html, json, pathlib, re, shutil, urllib.parse
from sql_reader import read_tables, statements

GIFT_FIELDS='id name slug description category type price image_path redirect_url quantity available_qty reserved is_active featured sort_order created_at updated_at'.split()
RESERVATION_FIELDS='id gift_id user_id status action_type quantity purchase_confirmed created_at'.split()

def build(dump, public_root, output):
    raw=dump.read_bytes(); tables=read_tables(raw.decode('utf-8-sig'))
    zones=[m[1] for s in statements(raw.decode('utf-8-sig')) if (m:=re.fullmatch(r'''SET\s+time_zone\s*=\s*['"]([^'"]+)['"]''',s,re.I))]
    if zones!=['+00:00']:raise ValueError('Explicit UTC export timezone is required; review other dumps separately')
    historical=tables['gifts']; reservations=tables['reservations']
    if len(historical)!=26 or len({x['id'] for x in historical})!=26:
        raise ValueError('Expected exactly 26 distinct historical gifts')
    gifts=[];assets=[]
    for original in historical:
        row={k:original[k] for k in GIFT_FIELDS}
        old_link=original.get('purchase_url')
        if old_link and row['redirect_url'] and old_link!=row['redirect_url']:
            raise ValueError('Two different links require owner review')
        if old_link and not row['redirect_url']:row['redirect_url']=old_link
        link=row['redirect_url']
        if link and (urllib.parse.urlparse(link).scheme not in ('http','https') or not urllib.parse.urlparse(link).netloc):
            raise ValueError('Unsafe gift URL')
        image=row['image_path']
        if not isinstance(image,str) or not re.fullmatch(r'/uploads/gifts/[A-Za-z0-9_.-]+\.(?:png|jpe?g|webp)',image):
            raise ValueError('Unexpected image path')
        source=(public_root/image.lstrip('/')).resolve()
        if not source.is_relative_to(public_root.resolve()) or not source.is_file():
            raise ValueError(f'Missing historical image for gift {row["id"]}')
        content=source.read_bytes()
        if not (content.startswith(b'\x89PNG\r\n\x1a\n') or content.startswith(b'\xff\xd8\xff') or (content[:4]==b'RIFF' and content[8:12]==b'WEBP')):
            raise ValueError('Image signature is invalid')
        assets.append({'path':image,'sha256':hashlib.sha256(content).hexdigest(),'bytes':len(content)})
        gifts.append(row)
    ids={x['id'] for x in gifts}
    for r in reservations:
        if set(r)!=set(RESERVATION_FIELDS) or r['gift_id'] not in ids:raise ValueError('Unresolved historical reservation')
    payload={'schema':'dyogo-recovery/1','source_time_zone':'+00:00','gifts':gifts,'historical_gifts':historical,
             'historical_reservations':reservations,'assets':assets}
    output.mkdir(parents=True,exist_ok=False)
    data=json.dumps(payload,ensure_ascii=False,indent=2).encode('utf-8')+b'\n'
    (output/'payload.json').write_bytes(data)
    manifest={'schema':'dyogo-recovery-manifest/1','source_dump_sha256':hashlib.sha256(raw).hexdigest(),
              'payload_sha256':hashlib.sha256(data).hexdigest(),'gift_count':26,'reservation_archive_count':len(reservations)}
    (output/'manifest.json').write_text(json.dumps(manifest,indent=2)+'\n',encoding='utf-8')
    for a in assets:
        target=output/'assets'/a['path'].lstrip('/');target.parent.mkdir(parents=True,exist_ok=True)
        shutil.copyfile(public_root/a['path'].lstrip('/'),target)
    preview(payload,output,manifest)
    return manifest

def preview(payload,output,manifest):
    esc=lambda v:html.escape(str(v),quote=True)
    cards=[]
    for g in payload['gifts']:
        rs=[r for r in payload['historical_reservations'] if r['gift_id']==g['id']]
        warnings=[]
        if not g['redirect_url']:warnings.append('Link ausente')
        if g['quantity']-g['available_qty']!=g['reserved']:warnings.append('Estoque histórico diverge do contador de reservas')
        if g['id']==7:warnings.append('Conferir link: aponta para perfume, título indica tênis')
        if g['available_qty']==0 and not rs:warnings.append('Esgotado sem reserva correspondente no export')
        reservation_text='; '.join(f"#{r['id']} · {r['status']} · compra {'confirmada' if r['purchase_confirmed']==1 else 'não confirmada'} · usuário histórico #{r['user_id']} · {r['created_at']}" for r in rs) or 'Nenhuma reserva no export'
        details=''.join(f'<dt>{esc(k)}</dt><dd>{esc(v) if v not in (None,"") else "<em>Ausente</em>"}</dd>' for k,v in g.items() if k not in ('description','name'))
        href=esc(g['redirect_url'])
        link=f'<a href="{href}" target="_blank" rel="noopener noreferrer">Abrir link histórico ↗</a>' if href else '<span>Link ausente no backup</span>'
        cards.append(f'''<article data-search="{esc((g['name']+' '+g['category']).lower())}">
          <div class="picture"><img loading="lazy" src="assets{esc(g['image_path'])}" alt="{esc(g['name'])}"><span class="id">#{g['id']:02d}</span></div>
          <div class="body"><p class="category">{esc(g['category'])}</p><h2>{esc(g['name'])}</h2>
          <p class="price">R$ {esc(g['price'].replace('.',','))}</p><p class="desc">{esc(g['description'])}</p>
          <p class="stock">Disponível no backup: <strong>{g['available_qty']}</strong> de {g['quantity']}</p>
          {''.join('<p class="warning">'+esc(w)+'</p>' for w in warnings)}<p>{link}</p>
          <details><summary>Campos e reservas históricas</summary><p>{esc(reservation_text)}</p><dl>{details}</dl></details></div></article>''')
    page='''<!doctype html><html lang="pt-BR"><meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1">
    <title>Dyogo — acervo histórico para revisão</title><style>
    *{box-sizing:border-box}body{margin:0;background:#f4f0e7;color:#29291f;font:16px Georgia,serif}header,main,footer{max-width:1400px;margin:auto;padding:32px}
    .eyebrow,.category,.id,.stock,summary,input,.notice,footer{font:12px 'Courier New',monospace}.eyebrow,.category{letter-spacing:.12em;text-transform:uppercase}
    header{border-bottom:2px solid #29291f}h1{font-size:clamp(38px,5vw,76px);font-weight:400;letter-spacing:-.05em;margin:22px 0;line-height:1}header p{max-width:850px;line-height:1.6}
    .notice{background:#e2e7d0;padding:18px;line-height:1.7}.toolbar{display:flex;align-items:center;justify-content:space-between;gap:20px;margin-bottom:24px}input{width:min(420px,100%);background:transparent;border:1px solid #777867;padding:14px;font-size:15px}
    .grid{display:grid;grid-template-columns:repeat(3,minmax(0,1fr));gap:24px}article{min-width:0;background:#fffcf5;border:1px solid #d1ccbe}.picture{position:relative;height:250px;background:#e7e3d9;padding:16px}.picture img{width:100%;height:100%;object-fit:contain}.id{position:absolute;top:12px;left:12px;background:#29291f;color:white;padding:7px}
    .body{padding:24px}h2{font-size:25px;font-weight:400;line-height:1.15;margin:10px 0}.price{font-size:22px;margin:18px 0}.desc{font-size:15px;line-height:1.65;white-space:pre-wrap;overflow-wrap:anywhere}.stock{line-height:1.6}a{color:#45502a;text-underline-offset:4px;overflow-wrap:anywhere}.warning{font:12px 'Courier New',monospace;border-left:3px solid #ad752c;padding-left:10px;line-height:1.6}
    details{border-top:1px solid #d1ccbe;padding-top:16px;margin-top:22px}summary{cursor:pointer;font-size:13px}details p{font-size:13px;line-height:1.7}dl{font:12px 'Courier New',monospace}dt{margin-top:12px;color:#687153}dd{margin:4px 0;overflow-wrap:anywhere}footer{line-height:1.8;overflow-wrap:anywhere}button:focus-visible,input:focus-visible,summary:focus-visible,a:focus-visible{outline:3px solid #778b45;outline-offset:3px}
    @media(max-width:950px){.grid{grid-template-columns:repeat(2,minmax(0,1fr))}}@media(max-width:600px){header,main,footer{padding:22px}.grid{grid-template-columns:1fr}.toolbar{align-items:stretch;flex-direction:column}.picture{height:280px}}[hidden]{display:none!important}
    </style><header><p class="eyebrow">Dyogo.site / acervo de 08.07.2026</p><h1>26 presentes.<br>Uma história preservada.</h1>
    <p>Prévia para sua avaliação. Nomes, descrições, preços e estoque reproduzem o export antigo. A disponibilidade não representa a situação de hoje.</p>
    <div class="notice">26 descrições · 26 categorias · 26 imagens recuperadas · 22 links · 6 reservas arquivadas.<br>purchase_url está vazio nos 26 registros; os links existentes vêm de redirect_url. Quatro presentes não têm link.<br>Produção permanece vazia. Nenhuma conta ou reserva será reativada por este pacote. As seis reservas (3 canceladas e 3 com compra confirmada) ficam em arquivo histórico separado.<br>Os quatro esgotados e os contadores originais serão preservados, inclusive divergências sinalizadas.</div></header>
    <main><div class="toolbar"><input id="search" type="search" aria-label="Buscar no acervo" placeholder="Buscar por nome ou categoria"><span id="count">26 presentes históricos</span></div><div class="grid">'''+''.join(cards)+'''</div></main><footer>Fonte: export Hostinger de 08/07/2026. Horários preservados literalmente, sem inferir fuso.<br>Pacote: '''+esc(manifest['payload_sha256'])+'''<br>Os arquivos de imagem foram encontrados localmente; links externos não foram validados quanto à disponibilidade ou ao preço atual.</footer>
    <script>document.querySelector('#search').addEventListener('input',e=>{let n=0;document.querySelectorAll('article').forEach(a=>{a.hidden=!a.dataset.search.includes(e.target.value.toLowerCase());if(!a.hidden)n++});document.querySelector('#count').textContent=n+' presentes históricos'})</script></html>'''
    page=page.replace('Horários preservados literalmente, sem inferir fuso.','Timestamps exportados em UTC (+00:00), conforme instrução explícita do dump.')
    (output/'PREVIA.html').write_text(page,encoding='utf-8')

if __name__=='__main__':
    p=argparse.ArgumentParser();p.add_argument('--dump',type=pathlib.Path,required=True);p.add_argument('--public-root',type=pathlib.Path,required=True);p.add_argument('--out',type=pathlib.Path,required=True)
    a=p.parse_args();print(json.dumps(build(a.dump,a.public_root,a.out),indent=2))
