<?php
// initialize php 
require_once './Twig/Autoloader.php';
Twig_Autoloader::register();

// loader
$loader = new Twig_Loader_Filesystem('./template/');
// extension
$escaper = new Twig_Extension_Escaper(true);

$twig = new Twig_Environment($loader);
$twig->addExtension($escaper);


// db connect
$dsn = 'mysql:dbname=nerar;host=localhost';
$user = 'root';
$password = '';
$dbh = new PDO($dsn, $user, $password);

// セッションの有効期限を1週間に設定
session_cache_expire(10080);

// セッションスタート
session_start();

// アクセスされた端末の判定
$ua = $_SERVER['HTTP_USER_AGENT'];
$is_sp = false;
if((strpos($ua, 'iPhone') !== false ) || strpos($ua, 'iPod') !== false || strpos($ua, 'Android' !== false)){
  $is_sp = true;
};




