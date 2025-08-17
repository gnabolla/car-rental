<?php

require_once __DIR__ . '/../config/database.php';

class Car {
    private $db;
    
    public $id;
    public $make;
    public $model;
    public $year;
    public $registration_number;
    public $daily_rate;
    public $status;
    public $created_at;

    public function __construct() {
        $this->db = Database::getInstance();
    }

    public function create($make, $model, $year, $registration_number, $daily_rate, $status = 'available') {
        $sql = "INSERT INTO cars (make, model, year, registration_number, daily_rate, status) 
                VALUES (?, ?, ?, ?, ?, ?)";
        
        try {
            $this->db->query($sql, [$make, $model, $year, $registration_number, $daily_rate, $status]);
            $this->id = $this->db->lastInsertId();
            
            $this->make = $make;
            $this->model = $model;
            $this->year = $year;
            $this->registration_number = $registration_number;
            $this->daily_rate = $daily_rate;
            $this->status = $status;
            
            return $this->id;
        } catch (Exception $e) {
            throw new Exception("Failed to create car: " . $e->getMessage());
        }
    }

    public function findById($id) {
        $sql = "SELECT * FROM cars WHERE id = ?";
        $result = $this->db->fetch($sql, [$id]);
        
        if ($result) {
            $this->populateFromArray($result);
            return $this;
        }
        
        return null;
    }

    public function findAll() {
        $sql = "SELECT * FROM cars ORDER BY created_at DESC";
        return $this->db->fetchAll($sql);
    }

    public function findAvailable() {
        $sql = "SELECT * FROM cars WHERE status = 'available' ORDER BY make, model";
        return $this->db->fetchAll($sql);
    }

    public function update($id, $make, $model, $year, $registration_number, $daily_rate, $status) {
        $sql = "UPDATE cars SET make = ?, model = ?, year = ?, registration_number = ?, 
                daily_rate = ?, status = ? WHERE id = ?";
        
        try {
            $stmt = $this->db->query($sql, [$make, $model, $year, $registration_number, $daily_rate, $status, $id]);
            return $stmt->rowCount() > 0;
        } catch (Exception $e) {
            throw new Exception("Failed to update car: " . $e->getMessage());
        }
    }

    public function delete($id) {
        $sql = "DELETE FROM cars WHERE id = ?";
        
        try {
            $stmt = $this->db->query($sql, [$id]);
            return $stmt->rowCount() > 0;
        } catch (Exception $e) {
            throw new Exception("Failed to delete car: " . $e->getMessage());
        }
    }

    public function updateStatus($id, $status) {
        $sql = "UPDATE cars SET status = ? WHERE id = ?";
        
        try {
            $stmt = $this->db->query($sql, [$status, $id]);
            return $stmt->rowCount() > 0;
        } catch (Exception $e) {
            throw new Exception("Failed to update car status: " . $e->getMessage());
        }
    }

    public function isAvailable($id) {
        $sql = "SELECT status FROM cars WHERE id = ?";
        $result = $this->db->fetch($sql, [$id]);
        
        return $result && $result['status'] === 'available';
    }

    public function search($query) {
        $sql = "SELECT * FROM cars WHERE 
                make LIKE ? OR 
                model LIKE ? OR 
                registration_number LIKE ? 
                ORDER BY make, model";
        
        $searchTerm = '%' . $query . '%';
        return $this->db->fetchAll($sql, [$searchTerm, $searchTerm, $searchTerm]);
    }

    private function populateFromArray($data) {
        $this->id = $data['id'];
        $this->make = $data['make'];
        $this->model = $data['model'];
        $this->year = $data['year'];
        $this->registration_number = $data['registration_number'];
        $this->daily_rate = $data['daily_rate'];
        $this->status = $data['status'];
        $this->created_at = $data['created_at'];
    }

    public function getFullName() {
        return $this->year . ' ' . $this->make . ' ' . $this->model;
    }

    public function validate($make, $model, $year, $registration_number, $daily_rate) {
        $errors = [];

        if (empty($make)) {
            $errors[] = "Make is required";
        }

        if (empty($model)) {
            $errors[] = "Model is required";
        }

        if (empty($year) || !is_numeric($year) || $year < 1900 || $year > date('Y') + 1) {
            $errors[] = "Valid year is required";
        }

        if (empty($registration_number)) {
            $errors[] = "Registration number is required";
        }

        if (empty($daily_rate) || !is_numeric($daily_rate) || $daily_rate <= 0) {
            $errors[] = "Valid daily rate is required";
        }

        return $errors;
    }
}
?>