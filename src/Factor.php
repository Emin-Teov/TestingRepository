<?php 
    require_once './src/SetDB/DB.php';
    require_once './src/Content.php';
    require_once './src/Validation.php';

    class Factor
    {
        /**
         * Set modal content for view.
         * 
         * @var string
         */
        private static $modal;
        /**
         * Get default language.
         * 
         * @var string
         */
        private static $defult_language = "en";

        /** 
         * Set dates about users.
         * 
         * @return array<string|null>
        */
        public static function get_client_ip()
        {
            return @unserialize (file_get_contents('http://ip-api.com/php/'));
        }

        /** 
         * Get database fields. 
         * 
         * @return object
        */
        public static function getDB(){
            return new DB;
        }

        /** 
         * Get content fields. 
         * 
         * @return object
        */
        public static function setContent(){
            return new Content;
        }

        /** 
         * Run validation function. 
         * 
         * @return object
        */
        public static function getValidator(){
            return new Validator;
        }

        /** 
         * Set message for users. 
         * 
         * @param string message
         * @param array<string|null> buttons
        */
        public static function setMessage($message, $buttons=array()){
            self::$modal = "<div id='message-modal'>".
                    "<span onclick='document.querySelector(\"#message-modal\").style.display=\"none\"'>x</span>".
                    "<h3>$message</h3>";
            if(!empty($buttons)){
                self::$modal .= "<div class=\"modal-btn\">";
                foreach ($buttons as $button => $params) {
                    self::$modal .= "<button class=\"btn\" ";
                    foreach ($params as $att => $param) {
                        self::$modal .= "$att = \"$param\" ";
                    }
                    self::$modal .= ">$button</button>";
                }
                self::$modal .= "</div>";
            }
            self::$modal .= "</div>";
            
            echo self::$modal;
        }
        
        /** 
         * Run function from controllers. 
         * 
         * @param string url
         * @param string controller
         * @param string function
         * @param array<string|null> param
        */
        public static function setData($url, $controller, $function, $param=array()){
            if(isset($_GET["url"]) && $_GET["url"] == $url && isset($_POST["submit"]) && $_POST["_token"] == $_SESSION["token"] && self::getValidator()->validate()){
                call_user_func_array( [$controller,  $function], array_filter($_POST, fn ($key) => in_array($key, $param), ARRAY_FILTER_USE_KEY) );
            }
        }

        /** 
         * Run function from controllers for api. 
         * 
         * @param string url
         * @param string controller
         * @param string function
         * @return object
        */
        public static function apiData($page, $controller, $function){
            if(isset($_GET["url"]) && $_GET["url"] == "api" && isset($_GET["page"]) && $_GET["page"] == $page){
                print_r(call_user_func_array( [$controller,  $function], (isset($_GET["param"])) ? [$_GET["param"]] : [] )); die;
            }
        }

        /** 
         * Generate token for controllers. 
         * 
         * @return string token
        */
        public static function setToken(){
            $_SESSION["token"] = md5(uniqid());
            return $_SESSION["token"];
        }

        /** 
         * Generate session for user interface. 
         * 
         * @param string index
         * @param string session
        */
        public static function setSession($index, $session){
            $_SESSION[$index] = $session;
        }

        /** 
         * Change language content from view. 
         * 
         * @return string lang
        */
        public static function getLang(){
            return isset($_SESSION["lang"]) ? $_SESSION["lang"] : self::$defult_language ;
        }

        /** 
         * Get values of content for view.
         * 
         * @return array<string>
        */
        public static function getContent(){
            return self::setContent()->set[self::getLang()];
        }
        
        /** 
         * Get user datas for view content. 
         * 
         * @return array<string>
        */
        public static function getUser(){
            return array_key_exists("user", $_SESSION) ? $_SESSION["user"] : array("permission" => 0, "username"=>"Guest");
        }
    }