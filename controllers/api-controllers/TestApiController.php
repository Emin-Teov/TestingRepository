<?php
    defined('EXEC');
    require_once './controllers/Controller.php';
    
    class TestApiController {
        /**
         * Get count of datas from database via api.
         * 
         * @return int
         */
        public function getCount(){
            $db = Factor::getDB();
            $db->table('test');
            $db->select("COUNT(*)");
            $db->setDB();
            return $db->loadResult();
        }

        /**
         * Get datas from database via api.
         * 
         * @return object
         */
        public function getAll(){
            $db = Factor::getDB();
            $db->table('test');
            $db->order('id DESC');
            $db->setDB();
            return $db->loadJsonList('id');
        }

        /**
         * Get data from database via api.
         * 
         * @return object
         */
        public function getItem(){
            $db = Factor::getDB();
            $db->table('test');
            $db->order('id DESC');
            $db->setDB();
            return $db->loadJson();
        }
    }