<?php
require dirname(__DIR__).'/bootstrap.php';
if(PHP_SAPI!=='cli')exit;
$password=getenv('ADMIN_PASSWORD');if(!$password)throw new RuntimeException('ADMIN_PASSWORD is required.');
$pdo=DB::connection();$pdo->beginTransaction();
try{
 $pdo->exec(file_get_contents(__DIR__.'/schema.sql'));
 foreach(json_decode(file_get_contents(__DIR__.'/catalog.json'),true) as $table=>$rows){
  foreach($rows as $row){$columns=array_map(fn($k)=>'"'.$k.'"',array_keys($row));DB::run('INSERT INTO '.$table.' ('.implode(',',$columns).') VALUES ('.implode(',',array_fill(0,count($row),'?')).') ON CONFLICT (id) DO NOTHING',array_values($row));}
  DB::run("SELECT setval(pg_get_serial_sequence(?,'id'),GREATEST(COALESCE((SELECT MAX(id) FROM ".$table."),1),1),true)",[$table]);
 }
 DB::run('INSERT INTO users (username,email,password,admin) VALUES (?,?,?,1) ON CONFLICT DO NOTHING',['Yessine Admin','admin@mycar.com',password_hash($password,PASSWORD_DEFAULT)]);
 $pdo->commit();echo 'My-Car database initialized.';
}catch(Throwable $e){$pdo->rollBack();throw $e;}
