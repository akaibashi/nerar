<?php

require_once './init.php';
require_once './utils.php';

$pjax = (!empty($_GET["is_pjax"]) && ($_GET["is_pjax"] == "true")) ? 1 : 0;

$data = array(
    'title' => 'detail',
    'name'  => 'twig hogereee',
    'is_pjax'  => $pjax,    
);

$token = $_GET["token"];

try{

	Utils::isLogin($dbh, &$_SESSION, &$data);

	// ユーザID
	$user_id = $data["user_id"];

	// tokenの存在チェックが必要

	// tokenからデータ取得
	//$token = '2c3dbbd4a783c368dc7a31bc3663db29';
	$stmt = $dbh->prepare('select * from image_info where token = :token');
	$stmt->bindValue(':token' , $token);
	$stmt->execute();
	$img_info = $stmt->fetch(PDO::FETCH_ASSOC);

	$file_name = $img_info["file_name"];
	$image_id = $img_info["id"];
	$data["image_id"] = $image_id;
	$follower_id = $img_info["user_id"];	

	// 画像idからhint情報取得
	$stmt = $dbh->prepare("select * from hint where image_id = :image_id");
	$stmt->bindValue(':image_id' , $image_id);
	$stmt->execute();
	$hint_list = $stmt->fetchAll(PDO::FETCH_ASSOC);
	$data["hint_list"] = $hint_list;

	$is_followdsp = true;
	// 未ログイン時の制御
	if(!$data["is_login"]){
		$is_followdsp = false;
	}	

	// 閲覧しているユーザが閲覧対象者をフォローしているかどうかの情報取得
	$data["target_id"] = $follower_id;
	$is_follow = Utils::isFollow($dbh, $user_id, $follower_id);
	$data["is_follow"] = $is_follow;


//　テストで2秒停止
sleep(2);	



}catch (PDOException $e){
    print('Connection failed:'.$e->getMessage());
    die();
}

$dbh = null;


//テストでSVG
//$template = $twig->loadTemplate('detail.html');
$template = $twig->loadTemplate('hint.html');

$data["img_url"] = "/nerar/images/origin/" . $file_name;
$img_w = $img_info["origin_width"];
$img_h = $img_info["origin_height"];
$img_arr = array();
$img_arr = ratio($img_w,$img_h);
$data["width"] = $img_arr['w'];
$data["height"] = $img_arr['h'];

echo $template->render($data);




/** 
* 限度超えてたら比率で小さくする
* @param $width
* @param $height
* @return array 変換後の数値2つ
*
*/
function ratio($a,$b){

	//true +a; false +b;
	$flg = true;
	$flg = ($a-$b) > 0 ? true : false;
	
	$_max = 1200;
	$_min = 600;
	
	
	//Max
	if($flg){
		if($_max > $a){
			$_a = $a;
			$_b = $b;
		}else{
			$_a = $_max;
			$_b = (int) ($_max/$a*$b);
		}
  }else{
		if($_max > $b){
			$_b = $b;
			$_a = $a;
		}else{
			$_b = $_max;
			$_a = (int) ($_max/$b*$a);
		}
	}
	
	//Min
	if(!$flg){
		if($_min > $_a){
			$_a = $_min;
			if($_max != $_b){
				$_b = (int) ($_min/$a*$_b);
			}
		}
	}else{
		if($_min > $_b){
			$_b = $_min;
			if($_max != $_a){
				$_a = (int) ($_min/$b*$_a);
			}
		}
	}
	
	$array = array(
	'w' => $_a,
	'h' => $_b,
	);
	return $array;
}

