<?php
declare(strict_types=1);

/** Offline package -> reviewed gifts. Never loads application credentials. */
final class HistoricalGiftRecovery
{
    public const FIELDS = ['id','name','slug','description','category','type','price','image_path','redirect_url','quantity','available_qty','reserved','is_active','featured','sort_order','created_at','updated_at'];
    private const INTS = ['id','quantity','available_qty','reserved','is_active','featured','sort_order'];

    public function __construct(private PDO $db) {
        if ($db->getAttribute(PDO::ATTR_DRIVER_NAME) !== 'mysql') throw new RuntimeException('MariaDB/MySQL is required');
        $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        $db->setAttribute(PDO::ATTR_EMULATE_PREPARES, false);
        $db->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
        if($db->query('SELECT @@character_set_connection')->fetchColumn()!=='utf8mb4')throw new RuntimeException('Explicit utf8mb4 connection is required');
        $db->exec("SET time_zone = '+00:00'"); // session only; matches the original export directive
    }

    public static function package(string $directory): array {
        $manifest = json_decode((string) file_get_contents($directory . '/manifest.json'), true, 64, JSON_THROW_ON_ERROR);
        $raw = (string) file_get_contents($directory . '/payload.json');
        if (($manifest['schema'] ?? '') !== 'dyogo-recovery-manifest/1' || !preg_match('/^[a-f0-9]{64}$/D', $manifest['source_dump_sha256'] ?? '') || !hash_equals($manifest['payload_sha256'] ?? '', hash('sha256', $raw))) throw new RuntimeException('Package integrity failed');
        $p = json_decode($raw, true, 64, JSON_THROW_ON_ERROR);
        $keys=array_keys($p);sort($keys);$expected=['schema','source_time_zone','gifts','historical_gifts','historical_reservations','assets'];sort($expected);
        if($keys!==$expected || $p['source_time_zone']!=='+00:00')throw new RuntimeException('Unexpected package sections/timezone');
        if (($p['schema'] ?? '') !== 'dyogo-recovery/1' || count($p['gifts'] ?? []) !== 26 || ($manifest['gift_count'] ?? 0) !== 26) throw new RuntimeException('Expected 26 reviewed historical gifts');
        if (count($p['historical_gifts'] ?? []) !== 26 || count($p['historical_reservations'] ?? []) !== ($manifest['reservation_archive_count'] ?? -1)) throw new RuntimeException('Historical archive count mismatch');
        $ids=[]; $slugs=[];
        foreach ($p['gifts'] as $row) {
            $keys=array_keys($row);sort($keys);$expected=self::FIELDS;sort($expected);
            if($keys!==$expected)throw new RuntimeException('Unexpected gift fields');
            self::normalized($row);
            if (isset($ids[$row['id']]) || isset($slugs[$row['slug']])) throw new RuntimeException('Duplicate gift identity');
            $ids[$row['id']]=true; $slugs[$row['slug']]=true;
        }
        $historicalIds=[];
        foreach($p['historical_gifts'] as $original){
            $keys=array_keys($original);sort($keys);$expected=array_merge(self::FIELDS,['purchase_url']);sort($expected);
            if($keys!==$expected || !isset($ids[$original['id']]) || isset($historicalIds[$original['id']]))throw new RuntimeException('Historical gift identity mismatch');
            $historicalIds[$original['id']]=true;
            self::normalized($original);
        }
        $reservationIds=[];
        foreach ($p['historical_reservations'] as $r) {
            $keys=array_keys($r);sort($keys);$expected=['id','gift_id','user_id','status','action_type','quantity','purchase_confirmed','created_at'];sort($expected);
            if ($keys !== $expected || !is_int($r['id']) || $r['id']<1 || !is_int($r['user_id']) || $r['user_id']<1 || !isset($ids[$r['gift_id']]) || isset($reservationIds[$r['id']]) || !in_array($r['status'], ['active','cancelled'], true) || !in_array($r['action_type'],['reserved','pix'],true) || !is_int($r['quantity']) || $r['quantity']<1 || !in_array($r['purchase_confirmed'],[null,0,1],true) || !preg_match('/^\d{4}-\d{2}-\d{2} \d{2}:\d{2}:\d{2}$/D',$r['created_at'])) throw new RuntimeException('Invalid historical reservation');
            $reservationIds[$r['id']]=true;
        }
        if (count($p['assets'] ?? []) !== 26) throw new RuntimeException('Missing image manifest');
        $assetPaths=[];
        foreach ($p['assets'] as $a) {
            $path=$a['path'] ?? '';
            if (!preg_match('~^/uploads/gifts/[A-Za-z0-9_.-]+\.(png|jpe?g|webp)$~D', $path) || isset($assetPaths[$path]) || !preg_match('/^[a-f0-9]{64}$/D', $a['sha256'] ?? '')) throw new RuntimeException('Unsafe asset manifest');
            $assetPaths[$path]=true;
            $file=$directory.'/assets'.$path;
            if (!is_file($file) || filesize($file) !== $a['bytes'] || !hash_equals($a['sha256'], hash_file('sha256',$file))) throw new RuntimeException('Image integrity failed');
        }
        foreach ($p['gifts'] as $row) if (!isset($assetPaths[$row['image_path']])) throw new RuntimeException('Gift image absent from package');
        return ['id'=>$manifest['payload_sha256'],'source'=>$manifest['source_dump_sha256'],'data'=>$p,'raw'=>$raw];
    }

    private static function normalized(array $row): array {
        $out=[];
        foreach (self::FIELDS as $key) {
            if (!array_key_exists($key,$row)) throw new RuntimeException('Missing gift field');
            $v=$row[$key];
            if (in_array($key,self::INTS,true)) {
                if ((!is_int($v) && !(is_string($v) && ctype_digit($v))) || (int)$v < 0 || (int)$v > 4294967295) throw new RuntimeException('Invalid gift integer');
                $v=(int)$v;
            } elseif (!is_string($v) && $v !== null) throw new RuntimeException('Invalid gift text');
            $out[$key]=$v;
        }
        if ($out['id'] < 1 || !in_array($out['type'],['single','multi'],true) || !in_array($out['is_active'],[0,1],true) || !in_array($out['featured'],[0,1],true) || $out['available_qty']>$out['quantity']) throw new RuntimeException('Invalid gift domain values');
        if (!preg_match('/^\d{1,8}\.\d{2}$/D',$out['price'] ?? '') || !preg_match('/^[a-z0-9][a-z0-9-]{0,254}$/D',$out['slug'] ?? '') || trim($out['name'] ?? '')==='') throw new RuntimeException('Invalid money/identity');
        foreach (['created_at','updated_at'] as $key) if ($out[$key] !== null && !preg_match('/^\d{4}-\d{2}-\d{2} \d{2}:\d{2}:\d{2}$/D',$out[$key])) throw new RuntimeException('Invalid historical timestamp');
        foreach (['name'=>255,'slug'=>255,'category'=>100,'image_path'=>500,'redirect_url'=>500] as $key=>$max) if (mb_strlen($out[$key] ?? '')>$max) throw new RuntimeException('Field exceeds target schema');
        if ($out['redirect_url'] && (!filter_var($out['redirect_url'],FILTER_VALIDATE_URL) || !in_array(parse_url($out['redirect_url'],PHP_URL_SCHEME),['http','https'],true))) throw new RuntimeException('Unsafe link');
        return $out;
    }
    private static function rowHash(array $row): string {
        return hash('sha256',json_encode(self::normalized($row),JSON_THROW_ON_ERROR|JSON_UNESCAPED_UNICODE|JSON_UNESCAPED_SLASHES));
    }
    private function query(string $sql,array $args=[]): PDOStatement {
        $q=$this->db->prepare($sql);$q->execute($args);return $q;
    }
    private function schema(): void {
        $cols=$this->query('SHOW COLUMNS FROM gifts')->fetchAll();
        if (array_column($cols,'Field') !== self::FIELDS || !str_starts_with($cols[6]['Type'],'decimal(10,2)')) throw new RuntimeException('Target schema drift; review required');
        $engine=$this->query("SELECT ENGINE FROM information_schema.TABLES WHERE TABLE_SCHEMA=DATABASE() AND TABLE_NAME='gifts'")->fetchColumn();
        if ($engine!=='InnoDB') throw new RuntimeException('Transactional InnoDB gifts table is required');
    }
    public function initializeLedger(): void {
        // DDL is deliberately separate from the atomic data transaction.
        $this->schema();
        $this->db->exec("CREATE TABLE IF NOT EXISTS dyogo_recovery_batches (batch_id CHAR(64) PRIMARY KEY, source_sha256 CHAR(64) NOT NULL, state VARCHAR(20) NOT NULL, payload_json LONGTEXT NOT NULL, created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");
        $this->db->exec("CREATE TABLE IF NOT EXISTS dyogo_recovery_rows (batch_id CHAR(64) NOT NULL, gift_id INT UNSIGNED NOT NULL, row_sha256 CHAR(64) NOT NULL, owned TINYINT NOT NULL, PRIMARY KEY(batch_id,gift_id), FOREIGN KEY(batch_id) REFERENCES dyogo_recovery_batches(batch_id)) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");
        foreach(['dyogo_recovery_batches'=>['batch_id','source_sha256','state','payload_json','created_at'],'dyogo_recovery_rows'=>['batch_id','gift_id','row_sha256','owned']] as $table=>$fields){
            if(array_column($this->query("SHOW COLUMNS FROM `$table`")->fetchAll(),'Field')!==$fields)throw new RuntimeException('Recovery ledger schema drift');
        }
    }
    public function plan(array $package,bool $lock=false): array {
        $this->schema();$gifts=$package['data']['gifts'];
        $marks=implode(',',array_fill(0,count($gifts),'?'));
        $rows=$this->query("SELECT * FROM gifts WHERE id IN ($marks) OR slug IN ($marks) ORDER BY id".($lock?' FOR UPDATE':''),array_merge(array_column($gifts,'id'),array_column($gifts,'slug')))->fetchAll();
        $existing=[];$bySlug=[];
        foreach($rows as $r){$existing[(int)$r['id']]=$r;$bySlug[$r['slug']]=(int)$r['id'];}
        $new=[];$same=[];
        foreach($gifts as $r){
            $id=$r['id'];
            if(isset($bySlug[$r['slug']]) && $bySlug[$r['slug']]!==$id) throw new RuntimeException('Slug collision; nothing will be overwritten');
            if(isset($existing[$id])){
                if(!hash_equals(self::rowHash($r),self::rowHash($existing[$id]))) throw new RuntimeException("Gift ID conflict: $id");
                $same[]=$id;
            } else $new[]=$id;
        }
        $imageRefs=(int)$this->query("SELECT COUNT(*) FROM gifts WHERE image_path IN ($marks)",array_column($gifts,'image_path'))->fetchColumn();
        return ['batch'=>$package['id'],'insert'=>$new,'already_identical'=>$same,'live_reservations_to_insert'=>0,'archive_reservations'=>count($package['data']['historical_reservations']),'image_references'=>$imageRefs];
    }
    private function transaction(callable $fn): array {
        $database=(string)$this->query('SELECT DATABASE()')->fetchColumn();
        $lock='dyogo-recovery:'.substr(hash('sha256',$database),0,32);
        if((int)$this->query('SELECT GET_LOCK(?,10)',[$lock])->fetchColumn()!==1) throw new RuntimeException('Recovery lock busy');
        try{
            $this->db->beginTransaction();$result=$fn();$this->db->commit();return $result;
        }catch(Throwable $e){if($this->db->inTransaction())$this->db->rollBack();throw $e;}
        finally{$this->query('SELECT RELEASE_LOCK(?)',[$lock]);}
    }
    private function receipts(array $package,array $batch): array {
        if(!hash_equals($package['id'],hash('sha256',$batch['payload_json'])) || !hash_equals($package['source'],$batch['source_sha256']))throw new RuntimeException('Batch provenance changed');
        $rows=$this->query('SELECT * FROM dyogo_recovery_rows WHERE batch_id=? ORDER BY gift_id FOR UPDATE',[$package['id']])->fetchAll();
        $expected=[];foreach($package['data']['gifts'] as $gift)$expected[$gift['id']]=self::rowHash($gift);
        if(count($rows)!==count($expected))throw new RuntimeException('Recovery receipts incomplete');
        foreach($rows as $row)if(!isset($expected[$row['gift_id']]) || !hash_equals($expected[$row['gift_id']],$row['row_sha256']) || !in_array((int)$row['owned'],[0,1],true))throw new RuntimeException('Recovery receipts changed');
        return $rows;
    }
    public function apply(array $package): array {
        return $this->transaction(function()use($package){
            $plan=$this->plan($package,true);
            $batch=$this->query('SELECT * FROM dyogo_recovery_batches WHERE batch_id=? FOR UPDATE',[$package['id']])->fetch();
            if($batch){
                if($batch['state']!=='applied') throw new RuntimeException('Batch was rolled back; a new review is required');
                $this->receipts($package,$batch);
                if(count($plan['already_identical'])!==26) throw new RuntimeException('Applied batch is incomplete');
                return ['state'=>'already_applied','inserted'=>0];
            }
            $this->query("INSERT INTO dyogo_recovery_batches(batch_id,source_sha256,state,payload_json) VALUES (?,?,'applied',?)",[$package['id'],$package['source'],$package['raw']]);
            $names='`'.implode('`,`',self::FIELDS).'`';$marks=implode(',',array_fill(0,count(self::FIELDS),'?'));
            foreach($package['data']['gifts'] as $gift){
                $owned=in_array($gift['id'],$plan['insert'],true);
                if($owned)$this->query("INSERT INTO gifts ($names) VALUES ($marks)",array_values(self::normalized($gift)));
                $this->query('INSERT INTO dyogo_recovery_rows(batch_id,gift_id,row_sha256,owned) VALUES (?,?,?,?)',[$package['id'],$gift['id'],self::rowHash($gift),$owned?1:0]);
            }
            return ['state'=>'applied','inserted'=>count($plan['insert']),'archived_reservations'=>count($package['data']['historical_reservations'])];
        });
    }
    public function rollback(array $package): array {
        return $this->transaction(function()use($package){
            $batch=$this->query('SELECT * FROM dyogo_recovery_batches WHERE batch_id=? FOR UPDATE',[$package['id']])->fetch();
            if(!$batch)throw new RuntimeException('Unknown recovery batch');
            if($batch['state']==='rolled_back')return ['state'=>'already_rolled_back','deleted'=>0];
            if($batch['state']!=='applied')throw new RuntimeException('Unknown batch state');
            $owned=array_values(array_filter($this->receipts($package,$batch),fn($r)=>(int)$r['owned']===1));
            foreach($owned as $receipt){
                $id=$receipt['gift_id'];$row=$this->query('SELECT * FROM gifts WHERE id=? FOR UPDATE',[$id])->fetch();
                if(!$row || !hash_equals($receipt['row_sha256'],self::rowHash($row)))throw new RuntimeException('Rollback blocked: recovered gift changed or missing');
            }
            $relations=$this->query("SELECT TABLE_NAME,COLUMN_NAME FROM information_schema.KEY_COLUMN_USAGE WHERE REFERENCED_TABLE_SCHEMA=DATABASE() AND REFERENCED_TABLE_NAME='gifts'")->fetchAll();
            foreach($relations as $relation){
                $table=$relation['TABLE_NAME'];$column=$relation['COLUMN_NAME'];
                if(!preg_match('/^[A-Za-z0-9_]+$/D',$table.$column))throw new RuntimeException('Unsupported foreign-key name');
                foreach($owned as $receipt)if($this->query("SELECT 1 FROM `$table` WHERE `$column`=? LIMIT 1 FOR UPDATE",[$receipt['gift_id']])->fetchColumn())throw new RuntimeException('Rollback blocked: new gift relationships exist');
            }
            foreach($owned as $receipt)$this->query('DELETE FROM gifts WHERE id=?',[$receipt['gift_id']]);
            $this->query("UPDATE dyogo_recovery_batches SET state='rolled_back' WHERE batch_id=?",[$package['id']]);
            return ['state'=>'rolled_back','deleted'=>count($owned)];
        });
    }
}
