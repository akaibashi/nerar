<?php

class Utils {
	/**
		* 標準フォーマットで日付時刻を取得する
		*
		* @param string $timestamp タイムスタンプまたは日付書式　デフォルト:現在時刻
		* @param string $format デフォルト:(Y-m-d H:i:s）
		*
		* @return string フォーマット済み日付時刻
		*/
	public static function getDatetime($timestamp = null, $format = null) {
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


	/**
		* ユーザのログイン状態を判断する
		*
		* @param $dbh
		* @param $_SESSION（参照渡し）
		* @param $data（参照渡し）
		*
		*/
	public static function isLogin($dbh, &$_SESSION, &$data){
		// ログイン状態を判断
		$data["is_login"] = false;
		// セッションに何か値はいってたとき
		if (isset($_SESSION['email'])) {
			// ユーザ情報を検索して存在しなければセッション削除
			// 該当のメールアドレスが存在しているかチェック
			$stmt = $dbh->prepare("select * from users where mail = :mail");
				$stmt->bindValue(':mail' , $_SESSION['email']); 	
			$stmt->execute();
			$user_info = $stmt->fetch(PDO::FETCH_ASSOC);	
			// ユーザがいた
			if($user_info){
				$data["is_login"] = true;
				$data["nick"] = $user_info['nick'];
				$data["user_id"] = $user_info['id'];
				$data["profile"] = $user_info['profile'];
				$data["profile_img"] = $user_info['profile_img'];
			}
			// ユーザいなかった
			else{
				$data["is_login"] = false;
				// セッションをクリア
						$_SESSION['username'] = "";
						$_SESSION['email'] = "";
						$_SESSION['oauth_provider'] = "";
						$_SESSION['nick'] = "";
			}
		}		
	}

	/**
	* HotTagsを取得する
	*
	* @param $dbh
	*
	* @return tags list
	*
	*/
	public static function getHotTags($dbh){
		$stmt = $dbh->prepare("
					select
					tag.tag_name
					from
					tag_relation as rel
					inner join
					tag
					on
					rel.tag_id = tag.tag_id
					group by

					tag.tag_name
					order by count(id) desc
					limit 20
				");	
		$stmt->execute();
		$tag_list = $stmt->fetchAll(PDO::FETCH_ASSOC);	
		// タグが存在した
		if($tag_list){
			return $tag_list;
		}
		// ユーザいなかった
		else{
			return null;
		}
	}



	/** 
	* フォロー状況を取得する user_idがtarget_idをフォローしてるかチェックする
	*
	* @param $dbh
	* @param $user_id
	* @param $target_id
	*
	* @return true:フォローしてる　false:フォローしてない
	*
	*/
	public static function isFollow($dbh, $user_id, $target_id){
		$stmt = $dbh->prepare("select * from follow where user_id = :user_id and follower_id = :follower_id");
			$stmt->bindValue(':user_id' , $target_id); 	
			$stmt->bindValue(':follower_id' , $user_id); 	
		$stmt->execute();
		$follow_info = $stmt->fetch(PDO::FETCH_ASSOC);	
		if($follow_info){
			return true;
		}
		return false;
	}
}
