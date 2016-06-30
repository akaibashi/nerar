<?php
//require_once './init.php';

try{
	// get param
	$is_fav = $_GET["is_fav"];
	$user_id = $_GET["user_id"];
	$img_id = $_GET["img_id"];

	// db connect
	$dsn = 'mysql:dbname=nerar;host=localhost';
	$user = 'root';
	$password = '';
	$dbh = new PDO($dsn, $user, $password);

	// ふぁぼ
	if($is_fav){
		// 既にフォローしていないかチェック
		$stmt = $dbh->prepare("select * from favorite where img_id = :img_id and user_id = :user_id");
		$stmt->bindValue(':img_id' , $img_id);	
		$stmt->bindValue(':user_id' , $user_id);	

		$stmt->execute();
		$fav_info = $stmt->fetch(PDO::FETCH_ASSOC);

		// フォローがされていない場合にフォロー
		if(!$fav_info){
			$stmt = $dbh->prepare("insert into favorite (img_id,user_id,dt_created)values(:img_id,:user_id,now())");
			$stmt->bindValue(':img_id' , $img_id);	
			$stmt->bindValue(':user_id' , $user_id);	
			$res = $stmt->execute();

			if($res){
				$arr = array('is_success' => "true");		
			}else{
				$arr = array('is_success' => "false");		
			}			
		}
	}
	// ふぁぼ解除
	else{
		// followテーブルからDELETEする
		$stmt = $dbh->prepare("delete from favorite where img_id = :img_id and user_id = :user_id");
		$stmt->bindValue(':img_id' , $img_id);	
		$stmt->bindValue(':user_id' , $user_id);		
		$res = $stmt->execute();		
		if($res){
			$arr = array('is_success' => "true");		
		}else{
			$arr = array('is_success' => "false");		
		}			
	}

}catch (PDOException $e){
    print('Connection failed:'.$e->getMessage());
    die();
}

$dbh = null;
// echo
echo json_encode($arr);

