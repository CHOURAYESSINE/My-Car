<?php
class Gallary extends DB {
 public function get_gallary(){return $this->select('gallary');}
 public function insert_gallary($file,$column){if(!preg_match('/^image[1-7]$/',$column))throw new InvalidArgumentException('Invalid gallery slot.');$this->save('gallary',[$column=>$this->image($file,'Gallary_images')],1);}
}
