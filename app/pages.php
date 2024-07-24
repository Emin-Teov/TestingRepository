<?php 
    defined('EXEC') or die;
    require_once './controllers/ContentController.php';

    Factor::get_client_ip()["status"]=="success" ? date_default_timezone_set(Factor::get_client_ip()["timezone"]) : NULL;
    $c = Factor::getContent();
    $lang_btn = array_keys(Factor::setContent()->set);

    include './resources/view/head.php';
    include './resources/view/header.php';
    include './resources/view/aside.php';
    include './resources/view/section.php';
    include './resources/view/script.php';
    include './resources/view/footer.php';