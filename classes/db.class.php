<?php
class Rows {
 public int $num_rows;private array $rows;private int $position=0;
 public function __construct(array $rows){$this->rows=$rows;$this->num_rows=count($rows);}
 public function fetch_assoc(){
  if(!isset($this->rows[$this->position]))return null;
  $row=$this->rows[$this->position++];
  foreach($row as $key=>$value){if(is_string($value)){
   $row[$key]=htmlspecialchars($value,ENT_QUOTES|ENT_SUBSTITUTE,'UTF-8',false);
   if($key==='body')$row[$key]=str_replace(['&lt;br&gt;','&lt;br/&gt;','&lt;br /&gt;'],'<br>',$row[$key]);
  }}return $row;
 }
}
class DB {
 private static ?PDO $pdo=null;
 public static function connection():PDO{
  if(self::$pdo)return self::$pdo;
  $url=getenv('DATABASE_URL');if(!$url)throw new RuntimeException('DATABASE_URL is required.');
  $u=parse_url($url);$ssl=getenv('VERCEL')?'verify-full':'require';
  $dsn='pgsql:host='.$u['host'].';port='.($u['port']??5432).';dbname='.ltrim($u['path'],'/').';sslmode='.$ssl;
  self::$pdo=new PDO($dsn,rawurldecode($u['user']),rawurldecode($u['pass']),[PDO::ATTR_ERRMODE=>PDO::ERRMODE_EXCEPTION,PDO::ATTR_DEFAULT_FETCH_MODE=>PDO::FETCH_ASSOC,PDO::ATTR_EMULATE_PREPARES=>false,PDO::PGSQL_ATTR_DISABLE_PREPARES=>true]);
  self::$pdo->exec('SET search_path TO my_car');return self::$pdo;
 }
 public static function run(string $sql,array $values=[]):PDOStatement{$stmt=self::connection()->prepare($sql);$stmt->execute($values);return $stmt;}
 protected function select(string $table,$id=''):Rows{
  return new Rows(self::run('SELECT * FROM '.$table.($id!==''?' WHERE id=?':'').' ORDER BY id',$id!==''?[(int)$id]:[])->fetchAll());
 }
 protected function save(string $table,array $fields,?int $id=null):int{
  $columns=array_map(fn($k)=>'"'.$k.'"',array_keys($fields));
  if($id!==null){$sets=array_map(fn($c)=>$c.'=?',$columns);self::run('UPDATE '.$table.' SET '.implode(',',$sets).' WHERE id=?',[...array_values($fields),$id]);return $id;}
  return self::run('INSERT INTO '.$table.' ('.implode(',',$columns).') VALUES ('.implode(',',array_fill(0,count($fields),'?')).') RETURNING id',array_values($fields))->fetchColumn();
 }
 protected function image(array $file,string $folder):string{
  if(($file['error']??UPLOAD_ERR_NO_FILE)!==UPLOAD_ERR_OK||$file['size']>2*1024*1024)throw new InvalidArgumentException('Choose an image smaller than 2 MB.');
  $mime=(new finfo(FILEINFO_MIME_TYPE))->file($file['tmp_name']);$types=['image/jpeg'=>'jpg','image/png'=>'png','image/gif'=>'gif','image/webp'=>'webp'];
  if(!isset($types[$mime])||!getimagesize($file['tmp_name']))throw new InvalidArgumentException('Invalid image.');
  $name=bin2hex(random_bytes(16)).'.'.$types[$mime];
  self::run('INSERT INTO uploaded_images (path,mime,content) VALUES (?,?,?)',['assets/'.$folder.'/'.$name,$mime,base64_encode(file_get_contents($file['tmp_name']))]);return $name;
 }
 protected function fields(array $input,array $allowed):array{
  $fields=array_intersect_key($input,array_flip($allowed));
  foreach($fields as $key=>$value){if(!is_scalar($value)||strlen((string)$value)>10000)throw new InvalidArgumentException('Invalid field.');if($key==='price'&&(!is_numeric($value)||$value<0))throw new InvalidArgumentException('Invalid price.');}
  return $fields;
 }
 public function printer($value){echo '<pre>'.htmlspecialchars(print_r($value,true)).'</pre>';}
}
