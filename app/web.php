<?php
    defined('EXEC') or die;

    require_once './controllers/UserController.php';
    require_once './controllers/TestController.php';
    require_once './controllers/ContentController.php';

    $test_controller = new TestController;

    Factor::setData("userenter", new UserController, "login", array("username", "password"));
    Factor::setData("userexit", new UserController, "logout");

    Factor::setData("setlang", new ContentController, "changeLang", array("lang"));
    
    Factor::setData("addtest", $test_controller, "setItem", array("value", "description"));
    Factor::setData("removetest", $test_controller, "deleteItem", array("id"));
    Factor::setData("edittest", $test_controller, "updateItem", array("id", "value", "description"));

    $_token = Factor::setToken();