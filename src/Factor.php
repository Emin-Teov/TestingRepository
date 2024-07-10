<?php 
    require_once './src/SetDB/DB.php';

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
         * Get multilingual content for view.
         * 
         * @var array<int|string>
         */
        public static $content = array(
            "en"=>array(
                // buttons
                "login"=>"Login",
                "logout"=>"Logout",
                "add"=>"Add Item",
                "yes"=>"Yes",
                "no"=>"No",
                "search"=>"Search",

                // input values
                "name"=>"Enter name",
                "password"=>"Enter password",
                "value"=>"Set value",
                "description"=>"Set description",

                //heads
                "values"=>"Value",
                "descriptions"=>"Description",
                "created"=>"Created at",
                "updated"=>"Updated at",

                //title
                "delete_data"=>"Are you sure you want to delete that item?",
                "no_data"=>"Database is empty",
                "no_user"=>"User is not found",
                "no_update"=>"The item never updated",
                "copy"=>"Email address copied",
            ),
            "az"=>array(
                // buttons
                "login"=>"Daxil ol",
                "logout"=>"Çıx",
                "add"=>"Əlavə et",
                "yes"=>"Hə",
                "no"=>"Yox",
                "search"=>"Axtar",

                // input values
                "name"=>"İstifadəçi adını daxil edin",
                "password"=>"Parolunuzu daxil edin",
                "value"=>"Başlığı daxil edin",
                "description"=>"Izzahı daxil edin",

                //heads
                "values"=>"Başlıqlar",
                "descriptions"=>"İzzahlar",
                "created"=>"Yaradıldıt",
                "updated"=>"Dəyişdirildi",

                //title
                "delete_data"=>"Həmin elementi silmək istədiyinizə əminsiniz?",
                "no_data"=>"Verilənlər bazası boşdur",
                "no_user"=>"İstifadəçi tapılmadı",
                "no_update"=>"Element heç vaxt yenilənməyib",
                "copy"=>"Poçt ünvanı kopyalandı",
            ),
            "ru"=>array(
                // buttons
                "login"=>"Войти",
                "logout"=>"Выход",
                "add"=>"Добавить элемент",
                "yes"=>"Да",
                "no"=>"Нет",
                "search"=>"Поиск",

                // input values
                "name"=>"Введите имя",
                "password"=>"Введите пароль",
                "value"=>"Установить значение",
                "description"=>"Установить описание",

                //heads
                "no_data"=>"Database is empty",
                "values"=>"Значение",
                "descriptions"=>"Описание",
                "created"=>"Создано в",
                "updated"=>"Обновлено в",

                //title
                "delete_data"=>"Вы уверены, что хотите удалить этот элемент?",
                "no_data"=>"База данных пуста",
                "no_user"=>"Пользователь не найден",
                "no_update"=>"Элемент никогда не обновлялся",
                "copy"=>"Адрес почты скопирован",
            ),
        );

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
         * @return /src/SetDB/DB
        */
        public static function getDB(){
            return new DB;
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
            if(isset($_GET["url"]) && $_GET["url"] == $url && isset($_POST["submit"]) && $_POST["_token"] == $_SESSION["token"] ){
                call_user_func_array( [$controller,  $function], array_filter($_POST, fn ($key) => in_array($key, $param), ARRAY_FILTER_USE_KEY) );
            }
        }

        /** 
         * Run function from controllers. 
         * 
         * @param string url
         * @param string controller
         * @param string function
         * @return JSON
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
            return self::$content[self::getLang()];
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