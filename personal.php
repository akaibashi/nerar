<?php
require_once './init.php';

session_start();

$data = array(
    'title' => 'sample01aaaa',
    'name'  => 'twig hogereee',
);

try{

	// db connect
	$dsn = 'mysql:dbname=nerar;host=localhost';
	$db_user = 'root';
	$password = '';
	$dbh = new PDO($dsn, $db_user, $password);

	// view or submit
	$mode = $_POST["mode"];

	// 該当のメールアドレスが存在しているかチェック
	$stmt = $dbh->prepare("select * from users where mail = :mail");
    $stmt->bindValue(':mail' , $_SESSION['email']); 	
	$stmt->execute();
	$user = $stmt->fetch(PDO::FETCH_ASSOC);	
	// 既に登録済み
	if($user){
        $dbh = null;		
		//　ニックネームを新たにsessionに追加
		$_SESSION['nick'] = $user["nick"];
		// 既にユーザ登録されているのでTOPへ遷移
		// TODO ユーザページに遷移させる
        header("Location: /nerar/");	
	}

	// 登録時の時
	if($mode == "submit"){
		// ニックネーム取得
		$nick = $_POST["nick"];

		// バリデーション　重複チェック
		// ニックネーム取得
		$stmt = $dbh->prepare("select * from users where nick = '" . $nick . "'");
		$stmt->execute();
		$user_info = $stmt->fetch(PDO::FETCH_ASSOC);
		if(!$user_info){
			// ニックネーム重複チェック
			//　ニックネームを新たにsessionに追加
			$_SESSION['nick'] = $nick;

			// ユーザデータ登録
	        $sql = 'insert into users
	                (
	            	mail,
	            	oauth_provider,
	            	username,
	            	nick,
	            	dt_created
	                 )
	                values
	                (
	                :mail,
	                :oauth_provider,
	                :username,
	                :nick,
	                now()
	                )
	        ';
	        $stmt = $dbh->prepare($sql);
	        $stmt->bindValue(':mail' , $_SESSION['email']);
	        $stmt->bindValue(':oauth_provider' , $_SESSION['oauth_provider']); 
	        $stmt->bindValue(':username' , $_SESSION['username']);                
	        $stmt->bindValue(':nick' , $nick);  
	        $flag = $stmt->execute();
	        // success
	        if ($flag){
	        	$data["is_success"] = true;
	        }
	    }else{
	        	$data["nick"] = $nick;
	        	$data["is_err"] = true;	        	
	    }    
	}

	// データ設定
	$data["username"] = $_SESSION['username'];
	$data["email"] = $_SESSION['email'];
	$data["oauth_provider"] = $_SESSION['oauth_provider'];

}catch (PDOException $e){
    print('Connection failed:'.$e->getMessage());
    die();
}

$dbh = null;

$template = $twig->loadTemplate('input_nick.html');
echo $template->render($data);




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

