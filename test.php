<?php
require_once './init.php';

$template = $twig->loadTemplate('pict_edit.html');
$data = array(
    'title' => 'sample01aaaa',
    'name'  => 'twig hogereee',
);
echo $template->render($data);
