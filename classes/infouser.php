<?php
require_once(__DIR__ . '/../config/Database.php' );

class infouser extends Database {

    private $table = "users";
    
    public function readAll() {
        $query = "SELECT * FROM $this->table ORDER BY id DESC";
        return $this->conn->query($query);
    }
    public function insert($username, $password, $role){
        $query = "INSERT INTO $this->table (username, password, role) VALUES (?, MD5(?), ?)";
        $stmt = $this->conn->prepare($query);
        $stmt->bind_param("sss", $username, $password, $role);
        $stmt->execute();
    }
    public function delete($id){
        $query = "DELETE FROM $this->table WHERE id = ?";
        $stmt = $this->conn->prepare($query);
        $stmt->bind_param("i", $id);
        $stmt->execute();
    }
    public function update($id, $role){
        $query = "UPDATE $this->table  SET role = ? WHERE id = ?";
        $stmt = $this->conn->prepare($query);
        $stmt->bind_param("si", $role, $id);
        $stmt->execute();
    }
    public function readById($id) {
        $query = "SELECT * FROM $this->table WHERE id = ?";
        $stmt = $this->conn->prepare($query);
        $stmt->bind_param("i", $id);
        $stmt->execute();
        return $stmt->get_result()->fetch_assoc();
    }
    public function findName($nama){
        $query = "SELECT * FROM $this->table WHERE nama = ?";
        $stmt = $this->conn->prepare($query); 
        $stmt->bind_param("s", $nama);
        $stmt->execute();
        return $stmt->get_result()->fetch_assoc();
    }

}
?>
