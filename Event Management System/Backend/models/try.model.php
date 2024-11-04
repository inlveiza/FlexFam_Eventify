<?php
    interface IEXample{
        public function getAll();
        public function getSingle($id);
        public function insert($data);
        public function update($id, $data);
        public function delete($data);
    }

    class Example implements IEXample
    {
        
        protected $pdo, $global;
        protected $table_name = "users";

        
        public function __construct(\PDO $pdo, GlobalMethods $gm){
            $this->pdo = $pdo;
            $this->gm = $gm;
            }
        

        public function getAll(){
            $sql = "SELECT * FROM users";
            try{
                $stmt = $this->pdo->prepare($sql);

                if($stmt->execute()){
                    $data = $stmt->fetchAll();
                    if($stmt->rowCount()>=1){
                        return $this->glob->responsePayload($data, "success","Here's your data",200);
                    }else{
                        return $this->glob->responsePayload(null,"failed", "No data found", 404);
                    }
                }

            }catch(\PDOException $e){
                echo $e->getMessage();
            }
        }
        public function getSingle($id){
            $sql = "SELECT * FROM ".$this->table_name." WHERE id = :id";
            
            try{
                $stmt = $this->pdo->prepare($sql);
                $stmt->bindParam(':id', $id, PDO::PARAM_INT);

                if ($stmt->execute()) {
                    $data = $stmt->fetch(PDO::FETCH_ASSOC);
                    if($data){
                        return $this->glob->responsePayload($data, "success","Here's your data",200);
                    }else{
                        return $this->glob->responsePayload(null,"failed", "No data found", 404);
                    }
                }

            }catch(\PDOException $e){
                echo $e->getMessage();
            }
        }
        public function insert($data){
            $sql = "INSERT INTO".$this->table_name."(firstname,lastname,is_admin) VALUES(?,?,?)";
            try{
                $stmt = $this->pdo->prepare($sql);
                
                if($stmt->execute([$data->firstname, $data->lastname,$data->is_admin])){
                    return $this->glob->responsePayload(null,"success","Successfully inserted data",200);
                }else{
                    return $this->glob->responsePayload(null,"failed", "Failed to insert data", 404);
                }
            }catch(\PDOException $e){
                echo $e->getMessage();
            }
        }
        
        public function update($id, $data){
            $sql = "UPDATE " .$this->table_name. " SET firstname=:firstname, lastname=:lastname, is_admin=:is_admin WHERE id=:id";
            try{
                $stmt = $this->pdo->prepare($sql);
                $stmt->bindParam(':firstname',$data['firstname']);
                $stmt->bindParam(':lastname',$data['lastname']);
                $stmt->bindParam(':is_admin',$data['is_admin']);
                $stmt->bindParam(':id', $id, PDO::PARAM_INT);

                if($stmt->execute()){
                    if($stmt->rowCount()>0){
                        return $this->glob->responsePayload($data, "success","Record Successfully Updated",200);
                    }else{
                        return $this->glob->responsePayload(null,"failed", "No record found to be updated", 404);
                    }
                }
            }catch(\PDOException $e){
                echo $e->getMessage();
            }
        }

        public function delete($id){
            $sql ="DELETE FROM ".$this->table_name." WHERE id = :id";
            try{
                $stmt = $this->pdo->prepare($sql);
                $stmt->bindParam(':id',$id,PDO::PARAM_INT);

                if($stmt->execute()){
                    if($stmt->rowCount()>0){
                        return $this->glob->responsePayload(null, "success","Record Successfully Deleted",200);
                    }else{
                        return $this->glob->responsePayload(null,"failed", "No records found to be deleted", 404);
                    }
                }

            }catch(\PDOException $e){
                echo $e->getMessage();
            }
        }
    }