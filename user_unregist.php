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

	$target_id = $_POST['user_id'];

	// 削除対象のIDがセッションのIDと同じ場合のみ　それ以外はエラー
	
	if($target_id == $data["user_id"]){

		// ユーザの全情報取得
		$sql = 'select * from users where id = ' . $data["user_id"];
		$stmt = $dbh->prepare($sql);
		$stmt->execute();
		$user_data = $stmt->fetch(PDO::FETCH_ASSOC);

		// 削除ユーザを保存する
        $sql = 'insert into unregist_users
                (
                id,
            	mail,
            	oauth_provider,
            	username,
            	nick,
            	profile,
            	profile_img,
            	dt_created
                 )
                values
                (
                ' . $user_data['id'] . ',
                "' . $user_data['mail'] . '",
                "' . $user_data['oauth_provider'] . '",
                "' . $user_data['username'] . '",
                "' . $user_data['nick'] . '",
                "' . $user_data['profile'] . '",
                "' . $user_data['profile_img'] . '",
                now()
                )
        ';
        $stmt = $dbh->prepare($sql);
        $flag = $stmt->execute();		

        // ユーザを削除する
 		$sql = 'delete from users where id = ' . $data["user_id"];
		$stmt = $dbh->prepare($sql);
		$stmt->execute();
		$user_data = $stmt->fetch(PDO::FETCH_ASSOC);

	}else{
        $data["is_error"] = true;		
	}
}catch (PDOException $e){
    print('Connection failed:'.$e->getMessage());
    die();
}

$dbh = null;

$template = $twig->loadTemplate("byebye.html");
echo $template->render($data);

