<?php
    defined('EXEC') or die;
    require_once './controllers/Controller.php';

    class ContentController implements Controller {
        /**
         * Change language content.
         * 
         * @param string lang
         */
        public function changeLang($lang){
            Factor::setSession("lang", $lang);
            header("Location: index.php");
        }

    }