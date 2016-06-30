<?php
require_once './init.php';
require_once './utils.php';

$data = array(
    'title' => 'sample01aaaa',
    'name'  => 'twig hogereee',
);

try{

	// get param
	$idx = $_GET["page_idx"];

	// ユーザのログイン状態の判断
	// $data["is_login"] true or false
	// $data["nick"]
	Utils::isLogin($dbh, &$_SESSION, &$data);

	// オフセット取得
	$offset = get_offset($idx);

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
			order by created desc limit ' . $offset . ' , 30
		';
	}else{
		$sql = 'select * from image_info order by created desc limit ' . $offset . ' , 30';
	}

	// 30件取得
	$stmt = $dbh->prepare($sql);
	$stmt->execute();
	$img_list = $stmt->fetchAll(PDO::FETCH_ASSOC);
	// 表示データ整形
	foreach( $img_list as &$value ){
		// margin-topの設定
		$height = intval($value["mini_height"]);
		if($height > 100){
			$value["margin_top"] = ($height - 100);
		}else{
			$value["margin_top"] = 0;
		}
		// 作成日の設定
		$value["created_disp"] = getDatetime($value["created"], "Y/m/d");
	}

	$data["img_list"] = $img_list;

}catch (PDOException $e){
    print('Connection failed:'.$e->getMessage());
    die();
}

$dbh = null;

$template = $twig->loadTemplate('index_auto_load.html');
echo $template->render($data);



/**
 * 一覧表示用のoffsetを取得
 *
 */
function get_offset($idx){
	// 1ページ = 10件換算で、次に取得するデータの開始行を返す
	return ($idx * 30);
}


/**
 * 標準フォーマットで日付時刻を取得する
 *
 * @param string $timestamp タイムスタンプまたは日付書式　デフォルト:現在時刻
 * @param string $format デフォルト:(Y-m-d H:i:s）
 *
 * @return string フォーマット済み日付時刻
 */
function getDatetime($timestamp = null, $format = null) {
	if ($timestamp == null) {
		$date = new DateTime();
	} else {
		if (is_numeric($timestamp)) {
			$timestamp = date('Y-m-d H:i:s', $timestamp);
		}
		
		$date = new DateTime($timestamp);
	}
	if ($format == null) {
		$format = 'Y-m-d H:i:s';
	}
	return $date->format($format);
}

