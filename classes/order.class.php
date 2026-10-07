<?php
require_once __DIR__.'/db.class.php';
class Order extends DB {
 public function get_order($id=''){return new Rows(DB::run('SELECT orders.*,users.username,users.email FROM orders JOIN users ON users.id=orders.user_id'.($id!==''?' WHERE orders.id=?':'').' ORDER BY orders.id DESC',$id!==''?[(int)$id]:[])->fetchAll());}
 public function delete_order($id){DB::run('DELETE FROM orders WHERE id=?',[(int)$id]);}
 public function get_items($id){return new Rows(DB::run('SELECT * FROM order_items WHERE order_id=? ORDER BY id',[(int)$id])->fetchAll());}
 public function checkout(array $input):int{
  foreach(['address','city','phone','postal_code'] as $key)if(empty(trim($input[$key]??''))||strlen($input[$key])>256)throw new InvalidArgumentException('Please complete all delivery fields.');
  $pdo=DB::connection();$pdo->beginTransaction();
  try{
   DB::run('SELECT pg_advisory_xact_lock(?)',[(int)$_SESSION['id']]);
   $cart=DB::run('SELECT * FROM cart WHERE user_id=? ORDER BY id FOR UPDATE',[$_SESSION['id']])->fetchAll();
   if(!$cart)throw new InvalidArgumentException('Your cart is empty.');
   $id=DB::run('INSERT INTO orders (user_id,address,city,phone,postal_code) VALUES (?,?,?,?,?) RETURNING id',[$_SESSION['id'],$input['address'],$input['city'],$input['phone'],$input['postal_code']])->fetchColumn();
   foreach($cart as $row){
    $part=(int)$row['product_id']===-1;$item=DB::run('SELECT * FROM '.($part?'products':'cars').' WHERE id=?',[$part?$row['product_id_2']:$row['product_id']])->fetch();
    if(!$item)throw new InvalidArgumentException('An item in your cart is unavailable.');
    DB::run('INSERT INTO order_items (order_id,product_id,product_id2,manufacturer,model,price,image) VALUES (?,?,?,?,?,?,?)',[$id,$row['product_id'],$row['product_id_2'],$item['manufacturer'],$item['model'],$item['price'],'assets/'.($part?'Product_images':'Car_images').'/'.$item['image']]);
   }
   DB::run('DELETE FROM cart WHERE user_id=?',[$_SESSION['id']]);$pdo->commit();$_SESSION['cart']=0;return (int)$id;
  }catch(Throwable $error){$pdo->rollBack();throw $error;}
 }
}
