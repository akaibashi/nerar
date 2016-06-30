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
	// $data["user_id"]	
	Utils::isLogin($dbh, &$_SESSION, &$data);


	// 自分自身のユーザID
	$user_id = $data["user_id"];	

	// self: 自分のdashboardを見たとき
	// other:他人のdashboardを見たとき
	$mode = $_GET["mode"];

	// 他人のdashboardを見たとき
	if("other" == $mode){
		// 閲覧対象となるユーザのIDを取得
		$view_user_id = $_GET["view_id"];
		$data["target_id"] = $view_user_id;

		if($user_id == $view_user_id){
			$dbh = null;    
			// 対象が自分自身の場合は自分のページに遷移させる
			header("Location: /nerar/dash_board.php?mode=self");  

		}

		// 閲覧対象者の情報取得
		$stmt = $dbh->prepare("select * from users where id = :id");
	    $stmt->bindValue(':id' , $view_user_id); 	
		$stmt->execute();
		$view_user_info = $stmt->fetch(PDO::FETCH_ASSOC);	
		// ユーザがいた
		if($view_user_info){
			$data["view_nick"] = $view_user_info['nick'];
			$data["view_profile"] = $view_user_info['profile'];
			$data["view_profile_img"] = $view_user_info['profile_img'];

			// 閲覧しているユーザが閲覧対象者をフォローしているかどうかの情報取得
			$is_follow = Utils::isFollow($dbh, $user_id, $view_user_id);
			$data["is_follow"] = $is_follow;


			// 閲覧対象者が投稿した内容取得
			$sql = "
				select
					2 as division, -- 画像の投稿
					own_u.nick as own_nick,
					null as nick,
					file_name as file_name,
					token as token,
					(i.mini_width DIV 2) as width,
					(i.mini_height DIV 2) as height,					
					comment as comment,
					created as created
				from
					image_info as i
					left outer join
					users as own_u
					on
					i.user_id = own_u.id
				where
					i.user_id =" . $view_user_id . "
				union
				select
					1 as division, -- 誰かの画像へのコメント
					own_u.nick as own_nick,
					u.nick as nick,
					i.file_name as file_name,
					i.token as token,
					(i.mini_width DIV 2) as width,
					(i.mini_height DIV 2) as height,					
					h.comment as comment,
					h.created as created
				from
					hint as h
					inner join
					image_info as i
					on
					h.image_id = i.id
					left outer join
					users as u
					on
					i.user_id = u.id
					left outer join
					users as own_u
					on
					h.user_id = own_u.id
				where
					h.user_id =" . $view_user_id . "				
				order by created desc
				-- limit 100
			";

			$stmt = $dbh->prepare($sql);
			$stmt->execute();
			$view_stream_list = $stmt->fetchAll(PDO::FETCH_ASSOC);

			// stream設定
			$data["view_stream"] = $view_stream_list;

			/* followタブ表示内容取得 */
			// 自分がフォローしてるユーザを取得
			$sql = "
				select
					* 
				from
					follow as fol
					inner join users as usr
					on
					usr.id = fol.user_id
				where fol.follower_id = 
			" . $view_user_id . " order by fol.dt_created desc";
			$stmt = $dbh->prepare($sql);
			$stmt->execute();
			$view_follow_list = $stmt->fetchAll(PDO::FETCH_ASSOC);

			// follow_list設定
			$data["view_follow_list"] = $view_follow_list;

			/* followerタブ表示内容取得 */
			// 自分をフォローしてるユーザを取得
			$sql = "
				select
					* 
				from
					follow as fol
					inner join users as usr
					on
					usr.id = fol.follower_id
				where fol.user_id = 
			" . $view_user_id . " order by fol.dt_created desc";
			$stmt = $dbh->prepare($sql);
			$stmt->execute();
			$view_follower_list = $stmt->fetchAll(PDO::FETCH_ASSOC);

			// follower_list設定
			$data["view_follower_list"] = $view_follower_list;

		}
		else{
			// 該当ユーザはいないためとりあえずTOPに遷移させる
			$dbh = null;    
			// ユーザページに遷移させる
			header("Location: /nerar/");  
		}

	}
	// それ以外は自分のdashboardを見たときと判断
	else  {

		// 自分自身を表示するモード
		$data["is_self"] = true;

		// 自分がフォローしている人一覧を取得する
		$stmt = $dbh->prepare('select * from follow where follower_id = :user_id');
		$stmt->bindValue(':user_id' , $user_id);
		$stmt->execute();
		$follow_list = $stmt->fetchAll(PDO::FETCH_ASSOC);

		// フォローしてる人がいる場合
		if($follow_list){

			/* streamタブ表示内容取得 */
			// フォローしている人の投稿した画像、コメントした情報を1つのSQLで先頭200件を取得する
			$sql = "
				select
					2 as division, -- 画像の投稿
					own_u.nick as own_nick,
					null as nick,
					file_name as file_name,
					token as token,
					(i.mini_width DIV 2) as width,
					(i.mini_height DIV 2) as height,					
					comment as comment,
					created as created
				from
					image_info as i
					left outer join
					users as own_u
					on
					i.user_id = own_u.id
				where
					i.user_id in(
						";

				$is_comma = false;
				foreach( $follow_list as $value ){
					if($is_comma){
						$sql .= ",";
					}
					// フォローしているユーザのIDを設定
					$sql .= $value["user_id"];
					$is_comma = true;
				}	

				$sql .= 
						"
				)
				union
				select
					1 as division, -- 誰かの画像へのコメント
					own_u.nick as own_nick,
					u.nick as nick,
					i.file_name as file_name,
					i.token as token,
					(i.mini_width DIV 2) as width,
					(i.mini_height DIV 2) as height,					
					h.comment as comment,
					h.created as created
				from
					hint as h
					inner join
					image_info as i
					on
					h.image_id = i.id
					left outer join
					users as u
					on
					i.user_id = u.id
					left outer join
					users as own_u
					on
					h.user_id = own_u.id
				where
					h.user_id in(
						";

				$is_comma = false;
				foreach( $follow_list as $value ){
					if($is_comma){
						$sql .= ",";
					}
					// フォローしているユーザのIDを設定
					$sql .= $value["user_id"];
					$is_comma = true;
				}	
				$sql .= 
						")
				order by created desc
				-- limit 100
			";

			$stmt = $dbh->prepare($sql);
			$stmt->execute();
			$stream_list = $stmt->fetchAll(PDO::FETCH_ASSOC);

			// stream設定
			$data["stream"] = $stream_list;


			/* followタブ表示内容取得 */
			// 自分がフォローしてるユーザを取得
			$sql = "
				select
					* 
				from
					follow as fol
					inner join users as usr
					on
					usr.id = fol.user_id
				where fol.follower_id = 
			" . $user_id . " order by fol.dt_created desc";
			$stmt = $dbh->prepare($sql);
			$stmt->execute();
			$follow_list = $stmt->fetchAll(PDO::FETCH_ASSOC);

			// follow_list設定
			$data["follow_list"] = $follow_list;

			/* followerタブ表示内容取得 */
			// 自分をフォローしてるユーザを取得
			$sql = "
				select
					* 
				from
					follow as fol
					inner join users as usr
					on
					usr.id = fol.follower_id
				where fol.user_id = 
			" . $user_id . " order by fol.dt_created desc";
			$stmt = $dbh->prepare($sql);
			$stmt->execute();
			$follower_list = $stmt->fetchAll(PDO::FETCH_ASSOC);

			// follower_list設定
			$data["follower_list"] = $follower_list;

		}

		/* histor tab内情報取得 */
		// 自分の投稿履歴の取得
		$sql = "
			select
				2 as division, -- 画像の投稿
				own_u.nick as own_nick,
				null as nick,
				file_name as file_name,
				token as token,
				(i.mini_width DIV 2) as width,
				(i.mini_height DIV 2) as height,					
				comment as comment,
				created as created,
				null as hint_id				
			from
				image_info as i
				left outer join
				users as own_u
				on
				i.user_id = own_u.id
			where
				i.user_id =" . $user_id . "
			union
			select
				1 as division, -- 誰かの画像へのコメント
				own_u.nick as own_nick,
				u.nick as nick,
				i.file_name as file_name,
				i.token as token,
				(i.mini_width DIV 2) as width,
				(i.mini_height DIV 2) as height,					
				h.comment as comment,
				h.created as created,
				h.id as hint_id				
			from
				hint as h
				inner join
				image_info as i
				on
				h.image_id = i.id
				left outer join
				users as u
				on
				i.user_id = u.id
				left outer join
				users as own_u
				on
				h.user_id = own_u.id
			where
				h.user_id =" . $user_id . "				
			order by created desc
			-- limit 100
		";

		$stmt = $dbh->prepare($sql);
		$stmt->execute();
		$history_list = $stmt->fetchAll(PDO::FETCH_ASSOC);

		// stream設定
		$data["history"] = $history_list;




		/* likes タブ見たとき */
		$sql = "
			select
				usr.nick as nick,
				fav.dt_created as created,
				img.token as token,
				img.file_name as file_name,
				img.mini_width as width,
				img.mini_height as height,
				img.comment as comment
			from
				favorite as fav
				inner join 
				image_info as img
				on
				fav.img_id = img.id
				inner join
				users as usr
				on
				img.user_id = usr.id
			where
				fav.user_id = " . $user_id . "
			order by created desc
		";

		$stmt = $dbh->prepare($sql);
		$stmt->execute();
		$likes_list = $stmt->fetchAll(PDO::FETCH_ASSOC);

		// likes設定
		$data["likes"] = $likes_list;


	}

	// hot tagの取得
	$data["hot_tags"] = Utils::getHotTags($dbh);

}catch (PDOException $e){
    print('Connection failed:'.$e->getMessage());
    die();
}

$dbh = null;

$template = $twig->loadTemplate('dash_board.html');
echo $template->render($data);


