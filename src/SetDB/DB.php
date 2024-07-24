<?php
    defined('EXEC') or die;
    require_once './src/SetDB/SetConn.php';
    
    class DB extends SetConn
    {
        /**
         * Get result from database.
         * 
         * @var array
         */
        private $result;

        /**
         * Set query.
         * 
         * @var string
         */
        private $setQuery = "";

        /**
         * Set table name for query.
         * 
         * @var string
         */
        private $table;

        /**
         * Set where parameter for query.
         * 
         * @var string
         */
        private $where;

        /**
         * Set limit parameter for query.
         * 
         * @var string
         */
        private $limit;

        /**
         * Set join parameter for query.
         * 
         * @var string
         */
        private $join;
        
        /**
         * Set order parameter for query.
         * 
         * @var string
         */
        private $order;

        /**
         * Set delete parameter for query.
         * 
         * @var string
         */
        private $delete;

        /**
         * Set select parameter for query.
         * 
         * @var string
         */
        private $select = "*";
        
        /**
         * Set insert parameter for query.
         * 
         * @var array<string>
         */
        private $insert = array();

        /**
         * Set quote.
         * 
         * @var array<string>
         */
        private $quote = array();

        /**
         * Set update parameter for query.
         * 
         * @var array<string>
         */
        private $update = array();

        /**
         * Set where parameter for query.
         * 
         * Set database query.
         * 
         * @param string query.
         */
        public function setQuery($query){
            $this->setQuery = $query;
        }

        /**
         * Set table name for database query.
         * 
         * @param string name
         */
        public function table($name){
            $this->table = $name;
        }

        /**
         * Set where parameter for database query.
         * 
         * @param string where
         */
        public function where($where){
            $this->where = $where;
        }

        /**
         * Set select parameter for database query.
         * 
         * @param string select
         */
        public function select($select){
            $this->select = $select;
        }

        /**
         * Set Delete parameter for database query.
         * 
         * @param string delete
         */
        public function delete($delete){
            $this->delete = $delete;
        }

        /**
         * Set insert parameter for database query.
         * 
         * @param array<int, string>|string|null insert
         * @param array<int, string>|string|null quote
         */
        public function insert($insert, $quote){
            $this->insert = $insert;
            $this->quote = $quote;
        }

        /**
         * Set update parameter for database query.
         * 
         * @param array<int, string>|string|null update
         * @param array<int, string>|string|null quote
         */
        public function update($update, $quote){
            $this->update = $update;
            $this->quote = $quote;
        }

        /**
         * Set inner parameter for database query.
         * 
         * @param string name
         * @param string quote
         */
        public function inner($name, $quote){
            $this->join = " INNER JOIN ".$name." ON ".$quote;
        }

        /**
         * Set left parameter for database query.
         * 
         * @param string name
         * @param string quote
         */
        public function left($name, $quote){
            $this->join = " LEFT JOIN ".$name." ON ".$quote;
        }

        /**
         * Set right parameter for database query.
         * 
         * @param string name
         * @param string quote
         */
        public function right($name, $quote){
            $this->join = " RIGHT JOIN ".$name." ON ".$quote;
        }

        /**
         * Set order parameter for database query.
         * 
         * @param string order
         */
        public function order($order){
            $this->order = " ORDER BY ".$order;
        }

        /**
         * Set limit parameter for database query.
         * 
         * @param int limit
         */
        public function limit($limit){
            $this->limit = " LIMIT ".$limit;
        }

        /**
         * Generate database result as Object.
         * 
         * @param array<int, string> array
         * @return object
         */
        private function toObject($array) {
            $object = new stdClass();
            foreach ($array as $key => $value) {
                if (is_array($value)) {
                    $value = $this->toObject($value);
                }
                $object->$key = $value;
            }
            return $object;
        }

        /**
         * Generate database query.
         * 
         * @param bool boll
         */
        public function setDB($boll=true){
            if($boll){
                try {
                    $showQuery = false;
                    $conn = new PDO("mysql:host={$this->getHostName()};dbname={$this->getDBName()}", $this->getUserName(), $this->getPasswordName());
                    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

                    if(is_array($this->insert)&&!empty($this->insert)&&is_array($this->quote)&&!empty($this->quote)){
                        $this->setQuery = "INSERT INTO ".$this->table." (";
                        foreach ($this->insert as $key => $value) {
                            $this->setQuery .= $value;
                            if($key != count($this->insert)-1){
                                $this->setQuery .= ",";
                            }
                        }
                        $this->setQuery .= ") VALUES (";
                        foreach ($this->quote as $key => $value) {
                            $this->setQuery .= "'".$value."'";
                            if($key != count($this->quote)-1){
                                $this->setQuery .= ",";
                            }
                        }
                        $this->setQuery .= ") ";
                    }else if(is_string($this->delete)){
                        $this->setQuery = "DELETE FROM ".$this->table." WHERE ".$this->delete;
                    }

                    if(strlen($this->setQuery)>0){
                        $conn->exec($this->setQuery);
                        return true;
                    }else{
                        $showQuery = true;
                    }
                    
                    if($showQuery){
                        $this->setQuery = "UPDATE ".$this->table." SET ";
                        if(is_string($this->update)){
                            $this->setQuery .= $this->update." = '".$this->quote."'";
                        }else if(is_array($this->update) && !empty($this->update)){
                            foreach ($this->update as $key => $value) {
                                $this->setQuery .= $value." = '".$this->quote[$key]."'";
                                if($key != count($this->update)-1){
                                    $this->setQuery .= ",";
                                }
                            }
                        }else{
                            $this->setQuery = "SELECT ";
                            if(is_array($this->select) && !empty($this->select)){
                                foreach ($this->select as $key => $value) {
                                    $this->setQuery .= $value;
                                    if($key != count($this->select)-1){
                                        $this->setQuery .= ",";
                                    }
                                }
                            }else{
                                $this->setQuery .= $this->select." FROM ".$this->table;
                            }
                        }
                        if(isset($this->where)){
                            $this->setQuery .= " WHERE ".$this->where;
                        }
                        if(isset($this->join)){
                            $this->setQuery .= $this->join;
                        }
                        if(isset($this->order)){
                            $this->setQuery .= $this->order;
                        }
                        if(isset($this->limit)){
                            $this->setQuery .= $this->limit;
                        }

                        $stmt = $conn->prepare($this->setQuery);
                        $stmt->execute();
                        $setResult = $stmt->setFetchMode(PDO::FETCH_ASSOC);

                        $this->result = $stmt->fetchAll();
                    }
                }catch(PDOException $e) {
                    die("<h4>Query:: {$this->setQuery}<br> Error:: {$e->getMessage()}</h4>");
                }
            }
        }

        /**
         * Generate database result as json.
         * 
         * @return object 
         */
        public function loadJson(){
            return json_encode($this->result[0]);
        }

        /**
         * Generate database result as json.
         * 
         * @param string key
         * @return object 
         */
        public function loadJsonList($key=NULL){
            return json_encode($this->loadAssocList($key));
        }

        /**
         * Generate database result as array.
         * 
         * @return array<int, string>
         */
        public function loadResult(){
            return $this->loadRow()[0];
        }

        /**
         * Generate database result as array.
         * 
         * @param string key
         * @return array<int, string>
         */
        public function loadAssocList($key=NULL){
            if(is_null($key)){
                $setResult = $this->result;
            }else{
                $setResult = array();
                foreach ($this->result as $value) {
                    $setResult[$value[$key]] = $value;
                }
            }
            return $setResult;
        }

        /**
         * Generate database result as array.
         * 
         * @param string key
         * @return array<int, string>
         */
        public function loadAssoc(){
            return $this->result[0];
        }

        /**
         * Generate database result as array.
         * 
         * @param string key
         * @return array<int, string>
         */
        public function loadRowList($key=NULL){
            $setResult = array();
            if(is_null($key)){
                foreach ($this->result as $value) {
                    array_push($setResult, array_values($value));
                }
            }else{
                foreach ($this->result as $value) {
                    $setResult[$value[$key]] = array_values($value);
                }
            }
            return $setResult;
        }

        /**
         * Generate database result as array.
         * 
         * @return array<int, string>
         */
        public function loadRow(){
            return array_values($this->result[0]);
        }

        /**
         * Generate database result as object.
         * 
         * @param string key
         * @return object
         */
        public function loadObjectList($key=NULL){
            if(is_null($key)){
                return $this->toObject($this->result);
            }else{
                return $this->toObject($this->loadAssocList($key));
            }
        }

        /**
         * Generate database result as object.
         * 
         * @return object
         */
        public function loadObject(){
            return $this->toObject($this->result[0]);
        }
    }