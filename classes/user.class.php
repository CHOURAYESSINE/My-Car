<?php
class User extends DB {
 public function get_user($email=''){return new Rows(DB::run('SELECT * FROM users'.($email!==''?' WHERE lower(email)=lower(?)':'').' ORDER BY id',$email!==''?[$email]:[])->fetchAll());}
 public function update_cart_increment($id){}
 public function update_cart_decrement($id){}
 public function insert_user($username,$email,$password,$repassword,$admin){
  $errors=[];
  if(trim($username)===''||strlen($username)>100||!filter_var($email,FILTER_VALIDATE_EMAIL)||strlen($password)<8)$errors[]='Enter a name, a valid email and a password of at least 8 characters.';
  if($password!==$repassword)$errors[]='Passwords did not match';
  if($this->get_user($email)->num_rows)$errors[]='This email is taken';
  if(!$errors){try{DB::run('INSERT INTO users (username,email,password,admin) VALUES (?,?,?,0)',[trim($username),strtolower($email),password_hash($password,PASSWORD_DEFAULT)]);}catch(PDOException $e){if($e->getCode()==='23505')$errors[]='This email is taken';else throw $e;}}
  $_SESSION['sign_message']=$errors[0]??'Account created successfully';unset($_SESSION['log_message']);return $errors;
 }
 public function login($input){
  $key=hash('sha256',strtolower(trim($input['email']??'')));$period=(int)floor(time()/600);
  if(random_int(1,100)===1)DB::run('DELETE FROM auth_attempts WHERE period<?',[$period-144]);
  $attempts=DB::run('INSERT INTO auth_attempts (key,period,count) VALUES (?,?,1) ON CONFLICT (key,period) DO UPDATE SET count=auth_attempts.count+1 RETURNING count',[$key,$period])->fetchColumn();
  if($attempts>10){$_SESSION['log_message']='Too many attempts. Try again in 10 minutes.';return [$_SESSION['log_message']];}
  $row=DB::run('SELECT * FROM users WHERE lower(email)=lower(?)',[$input['email']??''])->fetch();
  $ok=$row&&password_verify($input['password']??'',$row['password']);
  $_SESSION['log_message']=$ok?'':'Invalid email or password';unset($_SESSION['sign_message']);return $ok?[]:['Invalid email or password'];
 }
 public function delete_user($id){if((int)$id===(int)$_SESSION['id'])throw new InvalidArgumentException('You cannot delete your own administrator account.');DB::run('DELETE FROM users WHERE id=?',[(int)$id]);}
}
