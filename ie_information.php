<?php
require_once './init.php';

$data = array(
    'title' => 'sample01aaaa',
    'name'  => 'twig hogereee',
);


$template = $twig->loadTemplate('ie_information.html');
echo $template->render($data);

