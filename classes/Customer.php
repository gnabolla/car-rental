<?php

require_once __DIR__ . '/../config/database.php';

class Customer {
    private $db;
    
    public $id;
    public $name;
    public $email;
    public $phone;
    public $driver_license;
    public $created_at;

    public function __construct() {
        $this->db = Database::getInstance();
    }

    public function create($name, $email, $phone, $driver_license) {
        $sql = "INSERT INTO customers (name, email, phone, driver_license) VALUES (?, ?, ?, ?)";
        
        try {
            $this->db->query($sql, [$name, $email, $phone, $driver_license]);
            $this->id = $this->db->lastInsertId();
            
            $this->name = $name;
            $this->email = $email;
            $this->phone = $phone;
            $this->driver_license = $driver_license;
            
            return $this->id;
        } catch (Exception $e) {
            throw new Exception("Failed to create customer: " . $e->getMessage());
        }
    }

    public function findById($id) {
        $sql = "SELECT * FROM customers WHERE id = ?";
        $result = $this->db->fetch($sql, [$id]);
        
        if ($result) {
            $this->populateFromArray($result);
            return $this;
        }
        
        return null;
    }

    public function findByEmail($email) {
        $sql = "SELECT * FROM customers WHERE email = ?";
        $result = $this->db->fetch($sql, [$email]);
        
        if ($result) {
            $this->populateFromArray($result);
            return $this;
        }
        
        return null;
    }

    public function findByDriverLicense($driver_license) {
        $sql = "SELECT * FROM customers WHERE driver_license = ?";
        $result = $this->db->fetch($sql, [$driver_license]);
        
        if ($result) {
            $this->populateFromArray($result);
            return $this;
        }
        
        return null;
    }

    public function findAll() {
        $sql = "SELECT * FROM customers ORDER BY name";
        return $this->db->fetchAll($sql);
    }

    public function update($id, $name, $email, $phone, $driver_license) {
        $sql = "UPDATE customers SET name = ?, email = ?, phone = ?, driver_license = ? WHERE id = ?";
        
        try {
            $stmt = $this->db->query($sql, [$name, $email, $phone, $driver_license, $id]);
            return $stmt->rowCount() > 0;
        } catch (Exception $e) {
            throw new Exception("Failed to update customer: " . $e->getMessage());
        }
    }

    public function delete($id) {
        $sql = "DELETE FROM customers WHERE id = ?";
        
        try {
            $stmt = $this->db->query($sql, [$id]);
            return $stmt->rowCount() > 0;
        } catch (Exception $e) {
            throw new Exception("Failed to delete customer: " . $e->getMessage());
        }
    }

    public function search($query) {
        $sql = "SELECT * FROM customers WHERE 
                name LIKE ? OR 
                email LIKE ? OR 
                phone LIKE ? OR 
                driver_license LIKE ? 
                ORDER BY name";
        
        $searchTerm = '%' . $query . '%';
        return $this->db->fetchAll($sql, [$searchTerm, $searchTerm, $searchTerm, $searchTerm]);
    }

    public function getRentalHistory($customer_id) {
        $sql = "SELECT r.*, c.make, c.model, c.year, c.registration_number 
                FROM rentals r 
                JOIN cars c ON r.car_id = c.id 
                WHERE r.customer_id = ? 
                ORDER BY r.rental_date DESC";
        
        return $this->db->fetchAll($sql, [$customer_id]);
    }

    public function getActiveRentals($customer_id) {
        $sql = "SELECT r.*, c.make, c.model, c.year, c.registration_number 
                FROM rentals r 
                JOIN cars c ON r.car_id = c.id 
                WHERE r.customer_id = ? AND r.status = 'active' 
                ORDER BY r.rental_date DESC";
        
        return $this->db->fetchAll($sql, [$customer_id]);
    }

    public function hasActiveRentals($customer_id) {
        $sql = "SELECT COUNT(*) as count FROM rentals WHERE customer_id = ? AND status = 'active'";
        $result = $this->db->fetch($sql, [$customer_id]);
        
        return $result['count'] > 0;
    }

    private function populateFromArray($data) {
        $this->id = $data['id'];
        $this->name = $data['name'];
        $this->email = $data['email'];
        $this->phone = $data['phone'];
        $this->driver_license = $data['driver_license'];
        $this->created_at = $data['created_at'];
    }

    public function validate($name, $email, $phone, $driver_license, $id = null) {
        $errors = [];

        if (empty($name)) {
            $errors[] = "Name is required";
        }

        if (empty($email)) {
            $errors[] = "Email is required";
        } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $errors[] = "Valid email is required";
        } else {
            $existingCustomer = $this->findByEmail($email);
            if ($existingCustomer && (!$id || $existingCustomer->id != $id)) {
                $errors[] = "Email already exists";
            }
        }

        if (empty($phone)) {
            $errors[] = "Phone is required";
        }

        if (empty($driver_license)) {
            $errors[] = "Driver license is required";
        } else {
            $existingCustomer = $this->findByDriverLicense($driver_license);
            if ($existingCustomer && (!$id || $existingCustomer->id != $id)) {
                $errors[] = "Driver license already exists";
            }
        }

        return $errors;
    }
}
?>