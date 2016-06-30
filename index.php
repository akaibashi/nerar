<?php
require_once './init.php';
require_once './utils.php';

$data = array(
    'title' => 'sample01aaaa',
    'name'  => 'twig hogereee',
);

try{
	// ユーザのログイン状態の判断
	// $data["is_login"] true or false
	// $data["nick"]
	Utils::isLogin($dbh, &$_SESSION, &$data);

	// ログインしている場合
	if($data["is_login"]){
		$sql = '
			select
				img.*,
				fav.img_id as is_fav
			from
				image_info as img 
				left outer join
				favorite as fav
			on
				img.id = fav.img_id
			order by created desc limit 30
		';
	}else{
		$sql = 'select * from image_info order by created desc limit 30';
	}
	// 先頭30件取得
	$stmt = $dbh->prepare($sql);
	$stmt->execute();
	$img_list = $stmt->fetchAll(PDO::FETCH_ASSOC);

	// 表示データ整形
	if($img_list){
		foreach( $img_list as &$value ){
			// margin-topの設定
			$height = intval($value["mini_height"]);
			if($height > 100){
				$value["margin_top"] = ($height - 100);
			}else{
				$value["margin_top"] = 0;
			}
			// 作成日の設定
			$value["created_disp"] = Utils::getDatetime($value["created"], "Y/m/d");
		}	
	}

	$data["img_list"] = $img_list;

	// hot tagの取得
	$data["hot_tags"] = Utils::getHotTags($dbh);

}catch (PDOException $e){
    print('Connection failed:'.$e->getMessage());
    die();
}

$dbh = null;


if($is_sp){
  $template = $twig->loadTemplate('sp/index.html');
}else{
  $template = $twig->loadTemplate('index.html');
}

echo $template->render($data);

