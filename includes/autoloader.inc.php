<?php
require_once dirname(__DIR__).'/bootstrap.php';
spl_autoload_register(function($class){$path=dirname(__DIR__).'/classes/'.strtolower($class).'.class.php';if(is_file($path))require_once $path;});
