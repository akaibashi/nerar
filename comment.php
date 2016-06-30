<?php
//require_once './init.php';

try{
	// get param
	$is_comment = $_GET["is_comment"];
	$user_id = $_GET["user_id"];
	$target_id = $_GET["image_id"];
	$pos_x = $_GET["posx"];
	$pos_y = $_GET["posy"];
	$angle = $_GET["angle"];
	$color_r = $_GET["color_r"];
	$color_g = $_GET["color_g"];
	$color_b = $_GET["color_b"];
	$color_p = $_GET["color_p"];
	$rect_x = $_GET["rect_x"];
	$rect_y = $_GET["rect_y"];

	// db connect
	$dsn = 'mysql:dbname=nerar;host=localhost';
	$user = 'root';
	$password = '';
	$dbh = new PDO($dsn, $user, $password);

	// フォロー
	if($is_comment){
		$stmt = $dbh->prepare("insert into hint (image_id,user_id,x,y,comment,angle,color_r,color_g,color_b,color_p,rect_x,rect_y,created) values(:target_id,:user_id,:posx,:posy,:comment,:angle,:color_r,:color_g,:color_b,:color_p,:rect_x,:rect_y,now())");
		$stmt->bindValue(':user_id' , $user_id);	
		$stmt->bindValue(':target_id' , $target_id);	
		$stmt->bindValue(':comment' , $is_comment);	
		$stmt->bindValue(':posx' , $pos_x);	
		$stmt->bindValue(':posy' , $pos_y);	
		$stmt->bindValue(':angle' , $angle);	
		$stmt->bindValue(':color_r' , $color_r);	
		$stmt->bindValue(':color_g' , $color_g);	
		$stmt->bindValue(':color_b' , $color_b);	
		$stmt->bindValue(':color_p' , $color_p);	
		$stmt->bindValue(':rect_x' , $rect_y);	
		$stmt->bindValue(':rect_y' , $rect_x);	
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

