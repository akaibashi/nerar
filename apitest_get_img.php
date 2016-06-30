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

//  $sql = 'select a1.id,a1.file_name,a1.mini_width,a1.mini_height from image_info as a1 inner join image_info as a2 inner join image_info as a3 where a1.token="22d16a573e757629b86edfd0c131eaa3" or a1.token="602ac7a2522cbb90773812b6df86e933 limit 50"';

  $sql = 'select a1.id,a1.file_name,a1.mini_width,a1.mini_height from image_info as a1 inner join image_info as a2 inner join image_info as a3  limit 50';



  // ユーザのログイン状態の判断
  // $data["is_login"] true or false
  // $data["nick"]
  Utils::isLogin($dbh, &$_SESSION, &$data);

  // 30件取得
  $stmt = $dbh->prepare($sql);
  $stmt->execute();
  $img_list = $stmt->fetchAll(PDO::FETCH_ASSOC);


sleep(3);)

}catch (PDOException $e){
    print('Connection failed:'.$e->getMessage());
    die();
}

$dbh = null;

echo json_encode($img_list);



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

