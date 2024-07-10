<?php
    defined('EXEC') or die;
    require_once './controllers/api-controllers/TestApiController.php';

    $test_api = new TestApiController;
    Factor::apiData("alltest", $test_api, "getAll");
    Factor::apiData("counttest", $test_api, "getCount");
    Factor::apiData("test", $test_api, "getItem");