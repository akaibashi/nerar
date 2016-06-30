<?php
require_once './init.php';

$data = array(
    'title' => 'sample01aaaa',
    'name'  => 'twig hogereee',
);

try{

	// get param
	$idx = $_GET["page_idx"];

	$keywords = $_GET["keywords"];
	$keywords_list = preg_split('/[\s|\x{3000}]+/u', $keywords);

	$data["keywords"] = $keywords;

	// オフセット取得
	$offset = get_offset($idx);

	$is_or = false;

	// 仮に全件
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
		order by img.created desc limit" . $offset . " , 30
	";

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

