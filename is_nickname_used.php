<?php
require_once './init.php';

try{
	// get param
	$nick = $_GET["nick"];
	// db connect
	$dsn = 'mysql:dbname=nerar;host=localhost';
	$user = 'root';
	$password = '';
	$dbh = new PDO($dsn, $user, $password);

	// ニックネーム取得
	$stmt = $dbh->prepare("select * from users where nick = '" . $nick . "'");
	$stmt->execute();
	$user_info = $stmt->fetch(PDO::FETCH_ASSOC);

	if($user_info){
		$arr = array('is_use' => "true");		
	}else{
		$arr = array('is_use' => "false");		
	}
}catch (PDOException $e){
    print('Connection failed:'.$e->getMessage());
    die();
}

$dbh = null;
// echo
echo json_encode($arr);

