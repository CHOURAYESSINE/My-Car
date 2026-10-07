<?php
	include 'includes/autoloader.inc.php';

	$thought = new Thought();
	if(isset($_GET['id'])){
		$thoughts = $thought->get_thought($_GET['id']);
	}

if(!isset($thoughts)||$thoughts->num_rows===0){http_response_code(404);exit('Article not found.');}
