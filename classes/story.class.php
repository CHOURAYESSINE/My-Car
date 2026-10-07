<?php
class Story extends DB {
 public function get_story($id=''){return $this->select('stories',$id);}
 public function add_story($title,$body,$file,$showing){if(!trim($title)||strlen($title)>256)throw new InvalidArgumentException('A title is required.');$image=$this->image($file,'Story_images');return $this->save('stories',['title'=>$title,'body'=>$body,'showing'=>(int)$showing,'image'=>$image]);}
 public function story_visibality($id){DB::run('UPDATE stories SET showing=1-showing WHERE id=?',[(int)$id]);}
 public function delete_story($id){DB::run('DELETE FROM stories WHERE id=?',[(int)$id]);}
}
