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

	$keywords = $_GET["keywords"];

	// キーワードが空の場合はTOPへ
	if(!$keywords){
		$dbh = null;    
		header("Location: /nerar/");  
	}

	$keywords_list = preg_split('/[\s|\x{3000}]+/u', $keywords);

	$data["keywords"] = $keywords;

	$is_or = false;
	// 先頭30件取得

	$sql = "
	select distinct
		img.*
	from
		image_info as img
		inner join
		tag_relation as rel
		on
		img.id = rel.id
		inner join
		tag as tag
		on
		tag.tag_id = rel.tag_id
	";

	if($keywords_list){
		$sql .= "
		where
		";
		foreach( $keywords_list as $value ){	
			// カンマ追加
			if($is_or){
			$sql .= " or ";
			}
			$sql .= " tag.tag_name like '%" . $value . "%' ";
			$is_or = true;
		}
	}

	$sql .= "
		order by img.created desc limit 30
	";

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

$template = $twig->loadTemplate('search.html');
echo $template->render($data);

