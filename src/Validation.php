<?php
    defined('EXEC') or die;
    
    class Validator{
        /**
         * Set request content for validation.
         * 
         * @var array<string|null> get
         */
        private $request = [
            "id"=>"requested",
            "submit"=>"requested",
            "username"=>"requested|min:2|max:55",
            "password"=>"requested|min:2|max:55",
            "value"=>"requested|min:2|max:55",
            "description"=>"requested|min:2|max:255",
        ];

        /** 
         * Run function for validation of requests. 
         * 
         * @return bool
        */
        public function validate() : bool
        {
            $result = true;
            foreach ($this->request as $validate_key => $validate_param) {
                if(isset($_POST[$validate_key])){
                    foreach (explode("|", $validate_param) as $param) {
                        $param_value = explode(":", $param);
                        if ($param == "requested" && $_POST[$validate_key] == ""|NULL){ 
                            Factor::setMessage(Factor::getContent()["request"]);
                            $result = false;
                            break;
                        }elseif($param == "email" && !filter_var($_POST[$validate_key], FILTER_VALIDATE_EMAIL)){
                            Factor::setMessage(Factor::getContent()["email"]);
                            $result = false;
                            break;
                        }elseif($param_value[0] == "min" && strlen($_POST[$validate_key]) < intval($param_value[1])){
                            Factor::setMessage(sprintf(Factor::getContent()["min"], $validate_key, $param_value[1]));
                            $result = false;
                            break;
                        }elseif($param_value[0] == "max" && strlen($_POST[$validate_key]) > intval($param_value[1])){
                            Factor::setMessage(sprintf(Factor::getContent()["max"], $validate_key, $param_value[1]));
                            $result = false;
                            break;
                        }else{
                            continue;
                        }
                    }
                }
            }
            return $result;
        }
    }
 