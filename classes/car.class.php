<?php
class Car extends Catalog {
 protected string $table='cars';protected string $folder='Car_images';protected array $allowed=['manufacturer','model','price','condition','phone','email','speed','mileage','battery','fuel','total_run','gear','car_type','stock'];
 public function get_car($id=''){return $this->select('cars',$id);}
 public function insert_car($input,$file){return $this->add($input,$file);}
 public function update_car($input){$this->edit($input);}
 public function delete_car($id){$this->remove((int)$id,'product_id');}
 public function search(){if(!isset($_POST['regular-search']))return null;$term='%'.trim($_POST['regular-search']).'%';return new Rows(DB::run('SELECT * FROM cars WHERE manufacturer ILIKE ? OR model ILIKE ? ORDER BY id',[$term,$term])->fetchAll());}
}
