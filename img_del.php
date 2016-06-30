<?php
require_once './init.php';

try{
	// get param
	$token = $_GET["token"];
	// db connect
	$dsn = 'mysql:dbname=nerar;host=localhost';
	$user = 'root';
	$password = '';
	$dbh = new PDO($dsn, $user, $password);

	// 該当hint削除
	$stmt = $dbh->prepare("delete from image_info where token = '" . $token . "'");
	$res = $stmt->execute();

	if($res){
		$arr = array('is_success' => "true");		
	}else{
		$arr = array('is_success' => "false");		
	}			

}catch (PDOException $e){
    print('Connection failed:'.$e->getMessage());
    die();
}

$dbh = null;
// echo
echo json_encode($arr);

