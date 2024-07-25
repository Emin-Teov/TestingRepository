<?php
    defined('EXEC') or die;

    class SetConn{
        /**
         * Set host name for connection database.
         * 
         * @var string
         */
        private $host = "";

        /**
         * Set user name for connection database.
         * 
         * @var string
         */
        private $user = "";
        
        /**
         * Set password for connection database.
         * 
         * @var string
         */
        private $password = "";

        /**
         * Set database name for connection database.
         * 
         * @var string
         */
        private $db = "";

        /**
         * Get host name.
         * 
         * @return string
         */
        protected function getHostName(){
            return $this->host;
        }

        /**
         * Get user name.
         * 
         * @return string
         */
        protected function getUserName(){
            return $this->user;
        }

        /**
         * Get password.
         * 
         * @return string
         */
        protected function getPasswordName(){
            return $this->password;
        }

        /**
         * Get database name.
         * 
         * @return string
         */
        protected function getDBName(){
            return $this->db;
        }
    }