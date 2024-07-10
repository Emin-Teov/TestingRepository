<?php
    defined('EXEC') or die;
    require_once './controllers/Controller.php';

    class UserController implements Controller{
        /**
         * Login user profile username and password.
         * 
         * @param string username
         * @param string password
         */
        public function login($username, $password){
            $db = Factor::getDB();
            $db->table('users');
            $db->select("COUNT(*)");
            $db->where("username = '$username' AND password='".md5("LOGIN-".$password)."'");
            $db->setDB();
            if($db->loadResult()){
                $user = Factor::getDB();
                $user->table('users');
                $user->where("username = '$username' AND password='".md5("LOGIN-".$password)."'");
                $user->setDB();
                Factor::setSession("user", $user->loadAssoc());
                header("Location: index.php");
            }else{
                Factor::setMessage(Factor::getContent()["no_user"]);
            }
        }

        /**
         * Logout from user profile.
         */
        public function logout(){
            session_destroy();
            header("Location: index.php");
        }
    }