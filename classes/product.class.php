<?php
class Product extends Catalog {
 protected string $table='products';protected string $folder='Product_images';protected array $allowed=['manufacturer','model','price','type','condition','email','phone'];
 public function get_product($id=''){return $this->select('products',$id);}
 public function insert_product($input,$file){return $this->add($input,$file);}
 public function update_product($input){$this->edit($input);}
 public function delete_product($id){$this->remove((int)$id,'product_id_2');}
}
