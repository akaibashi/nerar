<?php
//require_once './init.php';

try{
	// get param
	$is_follow = $_GET["is_follow"];
	$user_id = $_GET["user_id"];
	$target_id = $_GET["target_id"];

	// db connect
	$dsn = 'mysql:dbname=nerar;host=localhost';
	$user = 'root';
	$password = '';
	$dbh = new PDO($dsn, $user, $password);

	// フォロー
	if($is_follow){
		// 既にフォローしていないかチェック
		$stmt = $dbh->prepare("select * from follow where user_id = :user_id and follower_id = :follower_id");
		$stmt->bindValue(':user_id' , $target_id);	
		$stmt->bindValue(':follower_id' , $user_id);	

		$stmt->execute();
		$follow_info = $stmt->fetch(PDO::FETCH_ASSOC);
		// フォローがされていない場合にフォロー
		if(!$follow_info){
			$stmt = $dbh->prepare("insert into follow (user_id,follower_id,dt_created) values(:user_id,:follower_id,now())");
			$stmt->bindValue(':user_id' , $target_id);	
			$stmt->bindValue(':follower_id' , $user_id);	
			$res = $stmt->execute();
			if($res){
				$arr = array('is_success' => "true");		
			}else{
				$arr = array('is_success' => "false");		
			}			
		}
	}
	// フォロー解除
	else{
		// followテーブルからDELETEする
		$stmt = $dbh->prepare("delete from follow where user_id = :user_id and follower_id = :follower_id");
		$stmt->bindValue(':user_id' , $target_id);	
		$stmt->bindValue(':follower_id' , $user_id);	
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

