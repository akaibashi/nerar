<?php

require_once './init.php';
require_once './utils.php';

$data = array(
    'title' => 'upload',
    'name'  => 'twig hogereee',
);

try{


/*
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
      $user_id = $user_info['id'];
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
*/
  // ユーザのログイン状態の判断
  // $data["is_login"] true or false
  // $data["nick"]
  Utils::isLogin($dbh, &$_SESSION, &$data);

  // 未ログイン時の制御
  if(!$data["is_login"]){
    $dbh = null;    
    // ユーザページに遷移させる
    header("Location: /nerar/");  
  }

  // ユーザID
  $user_id = $data["user_id"];

  // hot tagの取得
  $data["hot_tags"] = Utils::getHotTags($dbh);

  $mode = $_POST["upload"];

  // getting tags
  $tag = $_POST["tags"];
  $lock_tag = $_POST["lock_tags"];
  if($tag){
    $tag_list = explode(",", $tag);
  }
  if($lock_tag){
    $lock_tag_list = explode(",", $lock_tag);
  }

  // ローカルからアップロード
  if("local" == $mode){
    // ファイルが指定されている場合はアップロード
    if($_FILES['upload_file']){
      $name = $_FILES['upload_file']['name'];
      $tmp_file = $_FILES['upload_file']['tmp_name'];
      $comment = $_POST["comment"];
      
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

        // MD5でユニークなhashを生成
        $hash = get_file_name_recursive($dbh, $name);

        // ファイルの情報をDBに保存
        $sql = 'insert into image_info
                (user_id,
                 file_name, 
                 token,
                 comment,
                 origin_width,
                 origin_height,
                 mini_width,
                 mini_height,
                 division,
                 created
                 )
                values
                (
                :user_id,
                :file_name,
                :token,
                :comment,
                :origin_w,
                :origin_h,
                :mini_w,
                :mini_h,
                1,
                now()
                )
        ';
        $stmt = $dbh->prepare($sql);
        // 一覧表示用にリサイズした画像を生成する
        // 画像サイズを取得
        list($width, $height) = getimagesize($tmp_file);
        // リサイズ時画像サイズを取得
        list($dst_w, $dst_h) = get_resize($width, $height);
        // 小数点はintで切り捨て
        $dst_w = intval($dst_w);
        $dst_h = intval($dst_h);
        $stmt->bindValue(':user_id' , $user_id);
        $stmt->bindValue(':file_name' , $hash . "." . $type);
        $stmt->bindValue(':token' , $hash);
        $stmt->bindValue(':comment' , $comment);
        $stmt->bindValue(':origin_w' , $width);
        $stmt->bindValue(':origin_h' , $height);
        $stmt->bindValue(':mini_w' , $dst_w);
        $stmt->bindValue(':mini_h' , $dst_h);
        $flag = $stmt->execute();

        // success
        if ($flag){
          // 最後にインサートされた画像IDを取得
          $img_id = $dbh->lastInsertId();

          // リサイズ画像生成
          $dst_image = imagecreatetruecolor($dst_w, $dst_h);
//          $origin_image = imagecreatefromjpeg($tmp_file);
          $origin_image = imagecreatefromstring(file_get_contents($tmp_file));
          imagecopyresampled($dst_image,$origin_image,0, 0, 0, 0, $dst_w, $dst_h, $width, $height);

          // リサイズ画像ファイルのアップロード
          if(mini_upload($dst_image, $hash, $type)){
            // アップロード成功
            // オリジナルファイルのアップロード
            if(move_uploaded_file($_FILES['upload_file']['tmp_name'], "./images/origin/" . $hash . "." . $type)){

              if($tag_list){
                foreach ($tag_list as $key => $value){
                  // 既にロックされていないタグがあるかチェック
                  $stmt = $dbh->prepare('select * from tag where tag_name = :tag_name and is_lock = 0');
                  $stmt->bindValue(':tag_name' , $value);
                  $stmt->execute();
                  $tag_info = $stmt->fetch(PDO::FETCH_ASSOC);
                  // タグが無かった場合は登録
                  if(!$tag_info){
                    // タグの登録
                    $sql = 'insert into tag
                            (
                              tag_name,
                              is_lock
                             )
                            values
                            (
                            :tag_name,
                            0
                            )
                    ';
                    $stmt = $dbh->prepare($sql);    
                    $stmt->bindValue(':tag_name' , $value);    
                    $flag = $stmt->execute();       
                    // 最後にインサートされたタグIDを取得
                    $tag_id = $dbh->lastInsertId();
                    // タグと画像の関連登録
                    $sql = 'insert into tag_relation
                            (
                              id,
                              tag_id
                             )
                            values
                            (
                            :id,
                            :tag_id
                            )
                    ';
                    $stmt = $dbh->prepare($sql);    
                    $stmt->bindValue(':id' , $img_id);    
                    $stmt->bindValue(':tag_id' , $tag_id);    
                    $flag = $stmt->execute();  
                  }
                  // タグが存在した場合
                  else{
                    // タグと画像の関連登録
                    $sql = 'insert into tag_relation
                            (
                              id,
                              tag_id
                             )
                            values
                            (
                            :id,
                            :tag_id
                            )
                    ';
                    $stmt = $dbh->prepare($sql);    
                    $stmt->bindValue(':id' , $img_id);    
                    $stmt->bindValue(':tag_id' , $tag_info["tag_id"]);    
                    $flag = $stmt->execute(); 
                  }
                }
              }

              if($lock_tag_list){
                foreach ($lock_tag_list as $key => $value){
                  // 既にロックされているタグがあるかチェック
                  $stmt = $dbh->prepare('select * from tag where tag_name = :tag_name and is_lock = 1');
                  $stmt->bindValue(':tag_name' , $value);
                  $stmt->execute();
                  $tag_info = $stmt->fetch(PDO::FETCH_ASSOC);
                  // タグが無かった場合は登録
                  if(!$tag_info){
                    // タグの登録
                    $sql = 'insert into tag
                            (
                              tag_name,
                              is_lock
                             )
                            values
                            (
                            :tag_name,
                            1
                            )
                    ';
                    $stmt = $dbh->prepare($sql);    
                    $stmt->bindValue(':tag_name' , $value);    
                    $flag = $stmt->execute();       
                    // 最後にインサートされたタグIDを取得
                    $tag_id = $dbh->lastInsertId();
                    // タグと画像の関連登録
                    $sql = 'insert into tag_relation
                            (
                              id,
                              tag_id
                             )
                            values
                            (
                            :id,
                            :tag_id
                            )
                    ';
                    $stmt = $dbh->prepare($sql);    
                    $stmt->bindValue(':id' , $img_id);    
                    $stmt->bindValue(':tag_id' , $tag_id);    
                    $flag = $stmt->execute();  
                  }
                  // タグが存在した場合
                  else{
                    // タグと画像の関連登録
                    $sql = 'insert into tag_relation
                            (
                              id,
                              tag_id
                             )
                            values
                            (
                            :id,
                            :tag_id
                            )
                    ';
                    $stmt = $dbh->prepare($sql);    
                    $stmt->bindValue(':id' , $img_id);    
                    $stmt->bindValue(':tag_id' , $tag_info["tag_id"]);    
                    $flag = $stmt->execute(); 
                  }
                }
              }

              // 全部完了して成功
              $data["img_upload_complete"] = true;
            }  
          }
        }
      }
    }
    // 成功していない場合はエラーフラグ設定
    if(!$data["img_upload_complete"]){
      $data["img_upload_error"] = true;
    }  

  }
  // インターネットの画像をアップロード
  else if("net" == $mode){
    $link = $_POST["target_link"];
    $comment = $_POST["comment"];

    // 画像が適切
    if(is_img($link)){
      // mime type
      $type = get_img_type($link);
      // 画像サイズを取得
      list($width, $height) = getimagesize($link);
      // リサイズ時画像サイズを取得
      list($dst_w, $dst_h) = get_resize($width, $height);
      $dst_w = intval($dst_w);
      $dst_h = intval($dst_h);

      // MD5でユニークなhashを生成
      $hash = get_file_name_recursive($dbh, $name);

      // ファイルの情報をDBに保存
      $sql = 'insert into image_info
              (
               user_id,
               file_name, 
               token,
               comment,                
               origin_width,
               origin_height,
               mini_width,
               mini_height,
               division,
               url,
               created
               )
              values
              (
              :user_id,
              :file_name,
              :token,
              :comment,              
              :origin_w,
              :origin_h,
              :mini_w,
              :mini_h,
              2,
              :url,
              now()
              )
      ';
      $stmt = $dbh->prepare($sql);
      $stmt->bindValue(':user_id' , $user_id);      
      $stmt->bindValue(':file_name' , $hash . "." . $type);
      $stmt->bindValue(':token' , $hash); 
      $stmt->bindValue(':comment' , $comment);                      
      $stmt->bindValue(':origin_w' , $width);
      $stmt->bindValue(':origin_h' , $height);
      $stmt->bindValue(':mini_w' , $dst_w);    
      $stmt->bindValue(':mini_h' , $dst_h);    
      $stmt->bindValue(':url' , $link);    

      $flag = $stmt->execute();

      // success
      if ($flag){
        // 最後にインサートされた画像IDを取得
        $img_id = $dbh->lastInsertId();

        // ファイルの取得
        $handle = fopen($link, "r");
        $contents = stream_get_contents($handle);    
        fclose($handle);
        // オリジナルファイルの保存
        $fp = fopen("./images/origin/" . $hash . "." . $type, "w+");
        $origin_res = fwrite( $fp, $contents );
        fclose( $fp );

        // オリジナル画像保存完了
        if($origin_res){
          // リサイズ画像生成
          $dst_image = imagecreatetruecolor($dst_w, $dst_h);
//          $origin_image = imagecreatefromjpeg($tmp_file);
          $origin_image = imagecreatefromstring(file_get_contents($link));
          imagecopyresampled($dst_image,$origin_image,0, 0, 0, 0, $dst_w, $dst_h, $width, $height);
          // リサイズ画像ファイルのアップロード
          if(mini_upload($dst_image, $hash, $type)){

           if($tag_list){
              foreach ($tag_list as $key => $value){
                // 既にロックされていないタグがあるかチェック
                $stmt = $dbh->prepare('select * from tag where tag_name = :tag_name and is_lock = 0');
                $stmt->bindValue(':tag_name' , $value);
                $stmt->execute();
                $tag_info = $stmt->fetch(PDO::FETCH_ASSOC);
                // タグが無かった場合は登録
                if(!$tag_info){
                  // タグの登録
                  $sql = 'insert into tag
                          (
                            tag_name,
                            is_lock
                           )
                          values
                          (
                          :tag_name,
                          0
                          )
                  ';
                  $stmt = $dbh->prepare($sql);    
                  $stmt->bindValue(':tag_name' , $value);    
                  $flag = $stmt->execute();       
                  // 最後にインサートされたタグIDを取得
                  $tag_id = $dbh->lastInsertId();

                  // タグと画像の関連登録
                  $sql = 'insert into tag_relation
                          (
                            id,
                            tag_id
                           )
                          values
                          (
                            :id,
                            :tag_id
                          )
                  ';
                  $stmt = $dbh->prepare($sql);    
                  $stmt->bindValue(':id' , $img_id);    
                  $stmt->bindValue(':tag_id' , $tag_id);    
                  $flag = $stmt->execute();  
                }
                // タグが存在した場合
                else{
                  // タグと画像の関連登録
                  $sql = 'insert into tag_relation
                          (
                            id,
                            tag_id
                           )
                          values
                          (
                            :id,
                            :tag_id
                          )
                  ';
                  $stmt = $dbh->prepare($sql);    
                  $stmt->bindValue(':id' , $img_id);    
                  $stmt->bindValue(':tag_id' , $tag_info["tag_id"]);    
                  $flag = $stmt->execute(); 
                }
              }
            }

            if($lock_tag_list){
              foreach ($lock_tag_list as $key => $value){
                // 既にロックされているタグがあるかチェック
                $stmt = $dbh->prepare('select * from tag where tag_name = :tag_name and is_lock = 1');
                $stmt->bindValue(':tag_name' , $value);
                $stmt->execute();
                $tag_info = $stmt->fetch(PDO::FETCH_ASSOC);
                // タグが無かった場合は登録
                if(!$tag_info){
                  // タグの登録
                  $sql = 'insert into tag
                          (
                            tag_name,
                            is_lock
                           )
                          values
                          (
                          :tag_name,
                          1
                          )
                  ';
                  $stmt = $dbh->prepare($sql);    
                  $stmt->bindValue(':tag_name' , $value);    
                  $flag = $stmt->execute();       
                  // 最後にインサートされたタグIDを取得
                  $tag_id = $dbh->lastInsertId();
                  // タグと画像の関連登録
                  $sql = 'insert into tag_relation
                          (
                            id,
                            tag_id
                           )
                          values
                          (
                            :id,
                            :tag_id
                          )
                  ';
                  $stmt = $dbh->prepare($sql);    
                  $stmt->bindValue(':id' , $img_id);    
                  $stmt->bindValue(':tag_id' , $tag_id);    
                  $flag = $stmt->execute();  
                }
                // タグが存在した場合
                else{
                  // タグと画像の関連登録
                  $sql = 'insert into tag_relation
                          (
                            id,
                            tag_id
                           )
                          values
                          (
                            :id,
                            :tag_id
                          )
                  ';
                  $stmt = $dbh->prepare($sql);    
                  $stmt->bindValue(':id' , $img_id);    
                  $stmt->bindValue(':tag_id' , $tag_info["tag_id"]);    
                  $flag = $stmt->execute(); 
                }
              }
            }

            // 全部完了して成功
            $data["img_upload_complete"] = true;
          }        
        }
      }
    }
    // 成功していない場合はエラーフラグ設定
    if(!$data["img_upload_complete"]){
      $data["img_upload_error"] = true;
    }  
  }

}catch (PDOException $e){
    print('Connection failed:'.$e->getMessage());
    die();
}

$dbh = null;

// template render
$template = $twig->loadTemplate('upload.html');
echo $template->render($data);



/* リサイズ後のwidht,heightを取得する */
function get_resize($width, $height){
  // 横が大きいときは横をベースにリサイズする
  $dst_w = $width;
  $dst_h = $height;

  // とりあえず横幅ベースで
  if($width > 300){
    $dst_w = 300;
    $dst_h = $height * (300 / $width);      
    return array($dst_w, $dst_h);
  }else{
    return array($dst_w, $dst_h); 
  }
  

/*
  // 横は300pxまで
  if($width > $height){
  
    if($width > 300){
      $dst_w = 300;
      $dst_h = $height * (300 / $width);      
      return array($dst_w, $dst_h);
    }else{
      return array($dst_w, $dst_h); 
    }
  }
  // 縦が大きいときは縦をベースにリサイズする
  else if($width < $height){
  // 縦は350pxまで
    if($height > 350){
      $dst_h = 350;
      $dst_w = $width * (350 / $height);      
      return array($dst_w, $dst_h);
    }else{
       return array($dst_w, $dst_h);     
    }
  }
  // 縦横同じときは縦をベースにリサイズする
  else{
    if($width > 300){
      $dst_w = 300;
      $dst_h = 300;      
      return array($dst_w, $dst_h);
    }else{
       return array($dst_w, $dst_h);     
    }
  } 
*/

}


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

// アップロード対象のファイルが適切な画像か判定
function get_img_type($tmp_file){
  $res = exif_imagetype($tmp_file);
  // gif
  if(1 == $res) return "gif";
  // jpg
  else if(2 == $res) return "jpg";
  // png
  else if(3 == $res) return "png";
  //bmp
  else if(6 == $res) return "bmp";
  else return "";
}


/* uniqueなファイル名（hash）を再起的に取得する */
function get_file_name_recursive($dbh, $name){
  // MD5でユニークなhashを生成
  $hash = MD5(MD5($name . date('Y-m-d H:i:s')));
  $stmt = $dbh->prepare('select * from image_info where token = :token');
  $stmt->bindValue(':token' , $hash);
  $stmt->execute();
  $is_file = $stmt->fetch(PDO::FETCH_ASSOC);
  if($is_file){
    return get_file_name_recursive($dbh, $name);
  }else{
    return $hash;
  }
}

/* リサイズ画像をアップロードする true:成功 false:失敗 */
function mini_upload($dst_image, $hash, $type){
  if('jpg' == $type){
    return imagejpeg($dst_image, "./images/mini/" . $hash . "." . $type);  
  }else if('png' == $type){
    return imagepng($dst_image, "./images/mini/" . $hash . "." . $type);  
  }else if('gif' == $type){
    return imagegif($dst_image, "./images/mini/" . $hash . "." . $type);  
  }else if('bmp' == $type){
    return imagewbmp($dst_image, "./images/mini/" . $hash . "." . $type);  
  }else{
    return false;
  }
}




