<?php
class Thought extends DB {
 public function get_thought($id=''){return $this->select('my_thoughts',$id);}
 public function insert_thought($input,$file){$fields=$this->fields($input,['title','body','tag']);if(empty($fields['title']))throw new InvalidArgumentException('A title is required.');$fields['image']=$this->image($file,'Thought_images');return $this->save('my_thoughts',$fields);}
 public function delete_thought($id){DB::run('DELETE FROM my_thoughts WHERE id=?',[(int)$id]);}
}
