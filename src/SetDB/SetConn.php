<?php
    defined('EXEC') or die;

    class SetConn{
        /**
         * Set host name for connection database.
         * 
         * @var string
         */
        // private $host = "sql110.infinityfree.com";
        private $host = "localhost";

        /**
         * Set user name for connection database.
         * 
         * @var string
         */
        // private $user = "if0_36821573";
        private $user = "root";
        
        /**
         * Set password for connection database.
         * 
         * @var string
         */
        // private $password = "AoeGBvQ0spP";
        private $password = "";

        /**
         * Set database name for connection database.
         * 
         * @var string
         */
        // private $db = "if0_36821573_testing";
        private $db = "testing";

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