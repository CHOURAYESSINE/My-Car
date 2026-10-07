<?php
class Cart extends DB {
 public function get_cart($user=''){return new Rows(DB::run('SELECT * FROM cart WHERE user_id=? ORDER BY id',[(int)($_SESSION['id']??0)])->fetchAll());}
 private function add(int $id,bool $part):void{
  $item=DB::run('SELECT * FROM '.($part?'products':'cars').' WHERE id=?',[$id])->fetch();
  if(!$item)throw new InvalidArgumentException('This item is unavailable.');
  DB::run('INSERT INTO cart (product_name,user_id,product_id,product_price,user_name,product_model,user_email,product_image,product_id_2) VALUES (?,?,?,?,?,?,?,?,?)',[$item['manufacturer'],$_SESSION['id'],$part?-1:$id,$item['price'],$_SESSION['username'],$item['model'],$_SESSION['email'],$item['image'],$part?$id:-1]);
 }
 public function add_to_cart($id,...$unused){$this->add((int)$id,false);}
 public function add_to_cart_product($id,...$values){$this->add((int)end($values),true);}
 public function delete_cart($id){$count=DB::run('DELETE FROM cart WHERE id=? AND user_id=?',[(int)$id,$_SESSION['id']])->rowCount();if(!$count)throw new InvalidArgumentException('This cart item is unavailable.');}
}
