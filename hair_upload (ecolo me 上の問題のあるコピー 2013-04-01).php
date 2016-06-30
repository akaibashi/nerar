<?php

require_once './init.php';
require_once './utils.php';


$cnt = $_POST['img_count'];


        $sql = 'insert into req_log
                (
                param
                 )
                values
                (
                :param
                )
        ';
        $stmt = $dbh->prepare($sql);
        //$stmt->bindValue(':param' , print_r($_POST, true));
        $stmt->bindValue(':param' , $_POST['hogehoge'] . $cnt);
        $stmt->execute();


for($i = 0 ; $i < $cnt; $i++){
  
  $file_name = "data" . $i;
   // get param
  $img = $_FILES["$file_name"];
  move_uploaded_file($_FILES["$file_name"]['tmp_name'], "./images/origin/" . $_FILES["$file_name"]['name']);

}



//  move_uploaded_file($_FILES['imgdata']['tmp_name'], "./images/origin/" . $_FILES['imgdata']['name']);
