<?php
class Catalog extends DB {
 protected string $table;protected string $folder;protected array $allowed;
 protected function add(array $input,array $file):int{
  $fields=$this->fields($input,$this->allowed);
  foreach(['manufacturer','model','price'] as $key)if(!isset($fields[$key])||trim((string)$fields[$key])==='')throw new InvalidArgumentException('Manufacturer, model and price are required.');
  $fields['image']=$this->image($file,$this->folder);return $this->save($this->table,$fields);
 }
 protected function edit(array $input):void{if(empty($input['id']))throw new InvalidArgumentException('Missing identifier.');$fields=$this->fields($input,$this->allowed);if($fields)$this->save($this->table,$fields,(int)$input['id']);}
 protected function remove(int $id,string $cartColumn):void{DB::run('DELETE FROM cart WHERE '.$cartColumn.'=?',[$id]);DB::run('DELETE FROM '.$this->table.' WHERE id=?',[$id]);}
}
