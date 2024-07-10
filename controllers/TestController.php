<?php
    defined('EXEC') or die;
    require_once './controllers/Controller.php';

    class TestController implements Controller {
        /**
         * Get count of datas from database via api.
         * 
         * @return object
         */
        public function getCount(){
            $db = Factor::getDB();
            $db->table('test');
            $db->select("COUNT(*)");
            $db->setDB();
            return $db->loadResult();
        }

        /**
         * Get datas from database.
         * 
         * @return object
         */
        public function getAll(){
            $db = Factor::getDB();
            $db->table('test');
            $db->order('id DESC');
            $db->setDB();
            return $db->loadAssocList('id');
        }

        /**
         * Delete data from database.
         * 
         * @param int id
         */
        public function deleteItem($id){
            $db = Factor::getDB();
            $db->table('test');
            $db->delete('id = '.$id);
            $db->setDB();
        }

        /**
         * Add data to database.
         * 
         * @param string value
         * @param string description
         */
        public function setItem($value, $description){
            $db = Factor::getDB();
            $db->table('test');
            $db->insert(array('value', 'description', 'created_at'), array($value, $description, time()));
            $db->setDB();  
        }

        /**
         * Get data from database.
         * 
         * @param int id
         * @return array<int, string>
         */
        public function getItem($id){
            $db = Factor::getDB();
            $db->table('test');
            $db->where('id = '.$id);
            $db->setDB();
            return $db->loadAssoc();
        }

        /**
         * Change data from database.
         * 
         * @param int id
         * @param string value
         * @param string description
         */
        public function updateItem($id, $value, $description){
            $db = Factor::getDB();
            $db->table('test');
            $db->update(array('value', 'description', 'updated_at'), array($value, $description, time()));
            $db->where('id = '.$id);
            $db->setDB();  
        }
    }