"""Install reviewed missing images without replacing files. Plan is read-only."""
import argparse, hashlib, json, os, pathlib, re, shutil

def load(package):
    manifest=json.loads((package/'manifest.json').read_text(encoding='utf-8'))
    raw=(package/'payload.json').read_bytes()
    if hashlib.sha256(raw).hexdigest()!=manifest['payload_sha256']:raise ValueError('Payload integrity failed')
    data=json.loads(raw)
    for a in data['assets']:
        if not re.fullmatch(r'/uploads/gifts/[A-Za-z0-9_.-]+\.(png|jpe?g|webp)',a['path']):raise ValueError('Unsafe image path')
        source=package/'assets'/a['path'].lstrip('/')
        if not source.is_file() or hashlib.sha256(source.read_bytes()).hexdigest()!=a['sha256']:raise ValueError('Image integrity failed')
    return manifest,data['assets']

def plan(package,root):
    manifest,assets=load(package);root=root.resolve();rows=[]
    for a in assets:
        target=root/a['path'].lstrip('/')
        if not target.resolve().is_relative_to(root) or target.is_symlink():raise ValueError('Target path escapes public root')
        exists=target.exists()
        if exists and (not target.is_file() or hashlib.sha256(target.read_bytes()).hexdigest()!=a['sha256']):raise ValueError('Existing image differs; sync/review before recovery')
        rows.append({**a,'preexisting':exists})
    return {'batch':manifest['payload_sha256'],'root':str(root),'rows':rows,'state':'planned'}

def save(receipt,data):
    temp=receipt/'receipt.new';temp.write_text(json.dumps(data,indent=2),encoding='utf-8');os.replace(temp,receipt/'receipt.json')

def apply(package,root,receipt,approved):
    manifest,assets=load(package)
    if approved!=manifest['payload_sha256']:raise ValueError('Reviewed hash is required')
    root=root.resolve();receipt=receipt.resolve()
    if receipt.is_relative_to(root):raise ValueError('Receipt must stay outside docroot')
    if receipt.exists():data=json.loads((receipt/'receipt.json').read_text(encoding='utf-8'))
    else:
        data=plan(package,root)
        receipt.parent.mkdir(parents=True,exist_ok=True)
        if receipt.parent.stat().st_dev!=root.stat().st_dev:raise ValueError('Ownership receipt must share filesystem with public root')
        receipt.mkdir(mode=0o700);save(receipt,data)
    if data['batch']!=approved or data['root']!=str(root) or data['state']=='rolled_back':raise ValueError('Receipt identity/state mismatch')
    lock=receipt/'lock';lock.mkdir()
    try:
        for a in data['rows']:
            target=root/a['path'].lstrip('/');source=package/'assets'/a['path'].lstrip('/');stage=receipt/pathlib.Path(a['path']).name
            if not target.resolve().is_relative_to(root) or target.is_symlink():raise ValueError('Unsafe target')
            if a['preexisting']:
                if not target.is_file() or hashlib.sha256(target.read_bytes()).hexdigest()!=a['sha256']:raise ValueError('Preexisting image changed')
                continue
            if not target.parent.is_dir():raise ValueError('Prepare the approved upload directory and permissions first')
            if not stage.exists():
                with stage.open('xb') as f:f.write(source.read_bytes());f.flush();os.fsync(f.fileno())
                os.chmod(stage,0o644)
                if hasattr(os,'chown'):
                    parent=target.parent.stat();os.chown(stage,parent.st_uid,parent.st_gid)
            if hashlib.sha256(stage.read_bytes()).hexdigest()!=a['sha256']:raise ValueError('Stage changed')
            if target.exists():
                if not stage.samefile(target):raise ValueError('Concurrent image insertion; no overwrite')
            else:os.link(stage,target)  # exclusive; stage inode proves ownership after a crash
        data['state']='applied';save(receipt,data)
    finally:lock.rmdir()
    return {'state':'applied','created':sum(not a['preexisting'] for a in data['rows'])}

def rollback(root,receipt,approved,database_confirmed):
    root=root.resolve();receipt=receipt.resolve()
    if receipt.is_relative_to(root):raise ValueError('Unsafe receipt location')
    data=json.loads((receipt/'receipt.json').read_text(encoding='utf-8'))
    if approved!=data['batch'] or database_confirmed!=approved or data['root']!=str(root):raise ValueError('Database rollback and reviewed hash must be confirmed first')
    if data['state']=='rolled_back':return {'state':'already_rolled_back','deleted':0}
    lock=receipt/'lock';lock.mkdir()
    try:
        owned=[]
        for a in data['rows']:
            if a['preexisting']:continue
            target=root/a['path'].lstrip('/');stage=receipt/pathlib.Path(a['path']).name
            if not target.resolve().is_relative_to(root) or target.is_symlink():raise ValueError('Unsafe rollback path')
            if target.exists():
                if not stage.exists() or not stage.samefile(target) or hashlib.sha256(target.read_bytes()).hexdigest()!=a['sha256']:raise ValueError('Image changed or was replaced; rollback refused')
                owned.append(target)
        for target in owned:target.unlink()
        data['state']='rolled_back';save(receipt,data)
        return {'state':'rolled_back','deleted':len(owned)}
    finally:lock.rmdir()

if __name__=='__main__':
    p=argparse.ArgumentParser();p.add_argument('--package',type=pathlib.Path,required=True);p.add_argument('--public-root',type=pathlib.Path,required=True);p.add_argument('--receipt',type=pathlib.Path);p.add_argument('--mode',choices=['plan','apply','rollback'],default='plan');p.add_argument('--approved-sha256');p.add_argument('--database-rollback-confirmed')
    a=p.parse_args()
    if a.mode!='plan' and a.receipt is None:p.error('--receipt is required for writes')
    if a.mode=='plan':r=plan(a.package,a.public_root);r={'batch':r['batch'],'missing':sum(not x['preexisting'] for x in r['rows']),'identical':sum(x['preexisting'] for x in r['rows'])}
    elif a.mode=='apply':r=apply(a.package,a.public_root,a.receipt,a.approved_sha256)
    else:r=rollback(a.public_root,a.receipt,a.approved_sha256,a.database_rollback_confirmed)
    print(json.dumps(r))
