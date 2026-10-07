<?php
chdir(dirname(__DIR__));
if(PHP_SAPI==='cli-server'){
 $static=rawurldecode(parse_url($_SERVER['REQUEST_URI'],PHP_URL_PATH));
 if(preg_match('#^/(assets|css|javaScript)/#',$static)&&!str_contains($static,'..')&&is_file(ltrim($static,'/')))return false;
}
require_once 'bootstrap.php';
$path=rawurldecode(parse_url($_SERVER['REQUEST_URI'],PHP_URL_PATH));
if(str_starts_with($path,'/assets/')){
 if(str_contains($path,'..')){http_response_code(404);exit;}
 $file=ltrim($path,'/');
 if(is_file($file)){header('Content-Type: '.mime_content_type($file));readfile($file);exit;}
 $image=DB::run('SELECT mime, content FROM uploaded_images WHERE path=?',[$file])->fetch(PDO::FETCH_ASSOC);
 if(!$image){http_response_code(404);exit;}
 header('Content-Type: '.$image['mime']);header('X-Content-Type-Options: nosniff');header('Cache-Control: public,max-age=86400');echo base64_decode($image['content']);exit;
}
$pages=['/'=>'index.php','/index.php'=>'index.php','/admin.php'=>'admin.php','/cart.php'=>'cart.php','/checkout.php'=>'checkout.php','/product.php'=>'product.php','/more.php'=>'more.php','/post.php'=>'post.php','/orders.php'=>'orders.php','/includes/order.inc.php'=>'includes/order.inc.php'];
if(!isset($pages[$path])){http_response_code(404);echo 'Page not found';exit;}
require $pages[$path];
