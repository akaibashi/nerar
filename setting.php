<?php

require_once './init.php';
require_once './utils.php';

$data = array(
    'title' => 'upload',
    'name'  => 'twig hogereee',
);

try{

  // ユーザのログイン状態の判断
  // $data["is_login"] true or false
  // $data["nick"]
  // $data["user_id"]
  // $data["profile"]
  // $data["profile_img"]
  Utils::isLogin($dbh, &$_SESSION, &$data);

  // 未ログイン時の制御
  if(!$data["is_login"]){
    $dbh = null;    
    // ユーザページに遷移させる
    header("Location: /nerar/");  
  }

  // ユーザID
  $user_id = $data["user_id"];
  $mode = $_POST["submit"];


  // 初期値
  $is_valid = true;

  // submit時
  if("true" == $mode){
    // ファイルが指定されている場合はアップロード
    if($_FILES['upload_file']['name']){

      $name = $_FILES['upload_file']['name'];
      $tmp_file = $_FILES['upload_file']['tmp_name'];
      $profile = $_POST["profile"];
      $nick = $_POST["nick"];

      // ニックネームの空白チェック
      if(!$nick){
        $is_valid = false;
      }
      // ニックネームの重複チェック
      // 自分以外のニックネーム取得
      $stmt = $dbh->prepare("select * from users where id <> :user_id and nick = :nick");
      $stmt->bindValue(':user_id' , $user_id);  
      $stmt->bindValue(':nick' , $nick);  
      $stmt->execute();
      $user_info = $stmt->fetch(PDO::FETCH_ASSOC);

      if($user_info){
         $is_valid = false;  
      }
      // プロフィールの空白チェック
      if(!$profile){
        $is_valid = false;
      }

      // バリデーションOK
      if($is_valid){
        // 画像が適切か
        if(is_img($tmp_file)){
          // Content-typeテーブル
          $contents_type = array(
             'image/jpeg' => 'jpg',
             'image/png'  => 'png',
             'image/gif'  => 'gif',
             'image/bmp'  => 'bmp',
          );

          // mime type
          $type = $contents_type[$_FILES['upload_file']['type']];

          // ファイルの情報をDBに保存
          $sql = 'update users set
                    nick = :nick,
                    profile = :profile,
                    profile_img = :profile_img
                  where id = :id
          ';
          $stmt = $dbh->prepare($sql);
          $stmt->bindValue(':nick' , $nick);        
          $stmt->bindValue(':profile' , $profile);
          $stmt->bindValue(':profile_img' , $user_id . "." . $type); 
          $stmt->bindValue(':id' , $user_id);                
   
          $flag = $stmt->execute();

          // success
          if ($flag){
            // オリジナルファイルのアップロード
            if(move_uploaded_file($tmp_file, "./profile_images/" . $user_id . "." . $type)){
              $data["img_upload_complete"] = true;
            }
          }
        }        
      }

      // 成功していない場合はエラーフラグ設定
      if(!$data["img_upload_complete"]){
        $data["img_upload_error"] = true;
      }  

      $data["nick"] = $nick;
      $data["profile"] = $profile;

    }
    // 画像が指定されていない場合
    else{

      // 画像以外を保存
      $profile = $_POST["profile"];
      $nick = $_POST["nick"];

      // ニックネームの空白チェック
      if(!$nick){
        $is_valid = false;
      }
      // ニックネームの重複チェック
      // 自分以外のニックネーム取得
      $stmt = $dbh->prepare("select * from users where id <> :user_id and nick = :nick");
      $stmt->bindValue(':user_id' , $user_id);  
      $stmt->bindValue(':nick' , $nick);  
      $stmt->execute();
      $user_info = $stmt->fetch(PDO::FETCH_ASSOC);

      if($user_info){
         $is_valid = false;  
      }
      // プロフィールの空白チェック
      if(!$profile){
        $is_valid = false;
      }

      // バリデーションOK
      if($is_valid){
        // ファイルの情報をDBに保存
        $sql = 'update users set
                  nick = :nick,
                  profile = :profile
                where id = :id
        ';
        $stmt = $dbh->prepare($sql);
        $stmt->bindValue(':nick' , $nick);        
        $stmt->bindValue(':profile' , $profile);
        $stmt->bindValue(':id' , $user_id);                
        $flag = $stmt->execute();

        // success
        if ($flag){
          $data["img_upload_complete"] = true;
        }
      }

      // 成功していない場合はエラーフラグ設定
      if(!$data["img_upload_complete"]){
        $data["img_upload_error"] = true;
      }        

      $data["nick"] = $nick;
      $data["profile"] = $profile;

    }
  }

}catch (PDOException $e){
    print('Connection failed:'.$e->getMessage());
    die();
}

$dbh = null;

// template render
$template = $twig->loadTemplate('setting.html');
echo $template->render($data);


// アップロード対象のファイルが適切な画像か判定
function is_img($tmp_file){
  $res = exif_imagetype($tmp_file);
  // gif
  if(1 == $res) return true;
  // jpg
  else if(2 == $res) return true;
  // png
  else if(3 == $res) return true;
  //bmp
  else if(6 == $res) return true;
  else return false;
}



