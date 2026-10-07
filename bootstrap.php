<?php
require_once __DIR__.'/classes/db.class.php';
if(!getenv('DATABASE_URL')&&is_file(__DIR__.'/.env'))foreach(file(__DIR__.'/.env',FILE_IGNORE_NEW_LINES|FILE_SKIP_EMPTY_LINES) as $line){if(str_contains($line,'='))putenv($line);}
ini_set('display_errors','0');error_reporting(E_ALL);
set_exception_handler(function(Throwable $error){
 error_log(get_class($error).': '.$error->getMessage());
 http_response_code($error instanceof InvalidArgumentException?422:500);
 echo $error instanceof InvalidArgumentException?htmlspecialchars($error->getMessage()):'The application could not complete this request.';
});
class DatabaseSessions implements SessionHandlerInterface,SessionUpdateTimestampHandlerInterface {
 public function open(string $path,string $name):bool{return true;}
 public function close():bool{return true;}
 private function crypt(string $data,bool $encrypt):string{
  $key=hash('sha256',getenv('APP_KEY'),true);
  if($encrypt){$iv=random_bytes(12);$bytes=openssl_encrypt($data,'aes-256-gcm',$key,OPENSSL_RAW_DATA,$iv,$tag);return base64_encode($iv.$tag.$bytes);}
  $bytes=base64_decode($data,true);if($bytes===false||strlen($bytes)<28)return '';
  return openssl_decrypt(substr($bytes,28),'aes-256-gcm',$key,OPENSSL_RAW_DATA,substr($bytes,0,12),substr($bytes,12,16))?:'';
 }
 public function read(string $id):string{$data=DB::run('SELECT content FROM sessions WHERE id=? AND expires_at>now()',[$id])->fetchColumn();return $data?$this->crypt($data,false):'';}
 public function write(string $id,string $data):bool{DB::run("INSERT INTO sessions (id,content,expires_at) VALUES (?,?,now()+interval '1 day') ON CONFLICT (id) DO UPDATE SET content=excluded.content,expires_at=excluded.expires_at",[$id,$this->crypt($data,true)]);return true;}
 public function destroy(string $id):bool{DB::run('DELETE FROM sessions WHERE id=?',[$id]);return true;}
 public function gc(int $max_lifetime):int|false{return DB::run('DELETE FROM sessions WHERE expires_at<now()')->rowCount();}
 public function validateId(string $id):bool{return (bool)DB::run('SELECT 1 FROM sessions WHERE id=? AND expires_at>now()',[$id])->fetchColumn();}
 public function updateTimestamp(string $id,string $data):bool{DB::run("UPDATE sessions SET expires_at=now()+interval '1 day' WHERE id=?",[$id]);return true;}
}
if(PHP_SAPI!=='cli'&&session_status()===PHP_SESSION_NONE){
 if(!getenv('APP_KEY'))throw new RuntimeException('APP_KEY is required.');
 session_set_save_handler(new DatabaseSessions(),true);ini_set('session.use_strict_mode','1');
 session_name('my_car_session');session_set_cookie_params(['lifetime'=>86400,'path'=>'/','secure'=>(bool)getenv('VERCEL'),'httponly'=>true,'samesite'=>'Lax']);session_start();
 $_SESSION['csrf']??=bin2hex(random_bytes(32));
 if(isset($_SESSION['id'])){
  $account=DB::run('SELECT id,username,email,admin FROM users WHERE id=?',[$_SESSION['id']])->fetch();
  if(!$account){$_SESSION=['csrf'=>bin2hex(random_bytes(32))];session_regenerate_id(true);}
  else{$_SESSION['admin']=(int)$account['admin'];$_SESSION['username']=htmlspecialchars($account['username'],ENT_QUOTES|ENT_SUBSTITUTE,'UTF-8');$_SESSION['email']=$account['email'];$_SESSION['cart']=(int)DB::run('SELECT count(*) FROM cart WHERE user_id=?',[$account['id']])->fetchColumn();}
 }
 $page=parse_url($_SERVER['REQUEST_URI'],PHP_URL_PATH);
 if(in_array($page,['/admin.php','/orders.php'])&&empty($_SESSION['admin'])){http_response_code(403);exit('Administrator access required.');}
 if(in_array($page,['/cart.php','/checkout.php','/includes/order.inc.php'])&&empty($_SESSION['id'])){header('Location: /index.php');exit;}
 if($_SERVER['REQUEST_METHOD']==='POST'){
  if(!hash_equals($_SESSION['csrf'],(string)($_POST['_csrf']??''))){http_response_code(419);exit('Please reload the page and try again.');}
  if((isset($_POST['add-to-cart'])||isset($_POST['add-to-cart-product']))&&empty($_SESSION['id'])){header('Location: /index.php');exit;}
 }
 foreach(['id','product_id','order_id','del_id','del_user','story_show','delete_story','product_del_id','delete_thought','cnl_id'] as $key){if(isset($_GET[$key])&&!ctype_digit((string)$_GET[$key])){http_response_code(400);exit('Invalid identifier.');}}
 ob_start(function($html){$token=htmlspecialchars($_SESSION['csrf']??'');return preg_replace('/(<form\b[^>]*>)/i','$1<input type="hidden" name="_csrf" value="'.$token.'">',$html);});
}
