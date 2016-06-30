<?php

//exec("/root/Dropbox/nerar/opencv/hello", $out, $return_var);
exec("/var/www/nerar/opencv/hello", $out, $return_var);

$hoge = $out[0];
$hoge_2 = $out[1];

$hoge2 = $return_var;

var_dump($hoge);
var_dump($hoge_2);
var_dump($hoge2);


