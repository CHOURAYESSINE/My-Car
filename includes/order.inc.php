<?php
require_once __DIR__.'/autoloader.inc.php';
if($_SERVER['REQUEST_METHOD']!=='POST'){http_response_code(405);exit;}
if(isset($_POST['order-submit'])){(new Order())->checkout($_POST);header('Location: /index.php?success=order_placed');exit;}
http_response_code(400);
