<?php

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/Car.php';
require_once __DIR__ . '/Customer.php';

class Rental {
    private $db;
    
    public $id;
    public $car_id;
    public $customer_id;
    public $rental_date;
    public $return_date;
    public $actual_return_date;
    public $total_amount;
    public $status;
    public $created_at;

    public function __construct() {
        $this->db = Database::getInstance();
    }

    public function create($car_id, $customer_id, $rental_date, $return_date) {
        $car = new Car();
        if (!$car->isAvailable($car_id)) {
            throw new Exception("Car is not available for rental");
        }

        $customer = new Customer();
        if (!$customer->findById($customer_id)) {
            throw new Exception("Customer not found");
        }

        $carData = $car->findById($car_id);
        if (!$carData) {
            throw new Exception("Car not found");
        }

        $days = $this->calculateDays($rental_date, $return_date);
        $total_amount = $days * $carData->daily_rate;

        $sql = "INSERT INTO rentals (car_id, customer_id, rental_date, return_date, total_amount, status) 
                VALUES (?, ?, ?, ?, ?, 'active')";
        
        try {
            $this->db->beginTransaction();
            
            $this->db->query($sql, [$car_id, $customer_id, $rental_date, $return_date, $total_amount]);
            $this->id = $this->db->lastInsertId();
            
            $car->updateStatus($car_id, 'rented');
            
            $this->db->commit();
            
            $this->car_id = $car_id;
            $this->customer_id = $customer_id;
            $this->rental_date = $rental_date;
            $this->return_date = $return_date;
            $this->total_amount = $total_amount;
            $this->status = 'active';
            
            return $this->id;
        } catch (Exception $e) {
            $this->db->rollback();
            throw new Exception("Failed to create rental: " . $e->getMessage());
        }
    }

    public function returnCar($rental_id, $actual_return_date = null) {
        if ($actual_return_date === null) {
            $actual_return_date = date('Y-m-d');
        }

        $rental = $this->findById($rental_id);
        if (!$rental) {
            throw new Exception("Rental not found");
        }

        if ($rental->status !== 'active') {
            throw new Exception("Rental is not active");
        }

        $car = new Car();
        $carData = $car->findById($rental->car_id);
        
        $plannedDays = $this->calculateDays($rental->rental_date, $rental->return_date);
        $actualDays = $this->calculateDays($rental->rental_date, $actual_return_date);
        
        $new_total = $actualDays * $carData->daily_rate;
        
        $late_fee = 0;
        if ($actual_return_date > $rental->return_date) {
            $late_days = $this->calculateDays($rental->return_date, $actual_return_date);
            $late_fee = $late_days * $carData->daily_rate * 1.5;
            $new_total += $late_fee;
        }

        $sql = "UPDATE rentals SET actual_return_date = ?, total_amount = ?, status = 'completed' WHERE id = ?";
        
        try {
            $this->db->beginTransaction();
            
            $this->db->query($sql, [$actual_return_date, $new_total, $rental_id]);
            $car->updateStatus($rental->car_id, 'available');
            
            $this->db->commit();
            
            return [
                'total_amount' => $new_total,
                'late_fee' => $late_fee,
                'actual_days' => $actualDays,
                'planned_days' => $plannedDays
            ];
        } catch (Exception $e) {
            $this->db->rollback();
            throw new Exception("Failed to return car: " . $e->getMessage());
        }
    }

    public function findById($id) {
        $sql = "SELECT * FROM rentals WHERE id = ?";
        $result = $this->db->fetch($sql, [$id]);
        
        if ($result) {
            $this->populateFromArray($result);
            return $this;
        }
        
        return null;
    }

    public function findAll() {
        $sql = "SELECT r.*, c.make, c.model, c.year, c.registration_number, 
                       cu.name as customer_name, cu.email as customer_email
                FROM rentals r 
                JOIN cars c ON r.car_id = c.id 
                JOIN customers cu ON r.customer_id = cu.id 
                ORDER BY r.created_at DESC";
        
        return $this->db->fetchAll($sql);
    }

    public function findActive() {
        $sql = "SELECT r.*, c.make, c.model, c.year, c.registration_number, 
                       cu.name as customer_name, cu.email as customer_email
                FROM rentals r 
                JOIN cars c ON r.car_id = c.id 
                JOIN customers cu ON r.customer_id = cu.id 
                WHERE r.status = 'active'
                ORDER BY r.rental_date";
        
        return $this->db->fetchAll($sql);
    }

    public function findOverdue() {
        $today = date('Y-m-d');
        $sql = "SELECT r.*, c.make, c.model, c.year, c.registration_number, 
                       cu.name as customer_name, cu.email as customer_email,
                       (JULIANDAY(?) - JULIANDAY(r.return_date)) as days_overdue
                FROM rentals r 
                JOIN cars c ON r.car_id = c.id 
                JOIN customers cu ON r.customer_id = cu.id 
                WHERE r.status = 'active' AND r.return_date < ?
                ORDER BY r.return_date";
        
        return $this->db->fetchAll($sql, [$today, $today]);
    }

    public function getIncomeReport($start_date, $end_date) {
        $sql = "SELECT 
                    DATE(r.rental_date) as date,
                    COUNT(*) as rental_count,
                    SUM(r.total_amount) as daily_income
                FROM rentals r 
                WHERE r.rental_date BETWEEN ? AND ? 
                AND r.status IN ('completed', 'active')
                GROUP BY DATE(r.rental_date)
                ORDER BY DATE(r.rental_date)";
        
        return $this->db->fetchAll($sql, [$start_date, $end_date]);
    }

    public function getTotalIncome($start_date = null, $end_date = null) {
        if ($start_date && $end_date) {
            $sql = "SELECT SUM(total_amount) as total_income 
                    FROM rentals 
                    WHERE rental_date BETWEEN ? AND ? 
                    AND status IN ('completed', 'active')";
            $result = $this->db->fetch($sql, [$start_date, $end_date]);
        } else {
            $sql = "SELECT SUM(total_amount) as total_income 
                    FROM rentals 
                    WHERE status IN ('completed', 'active')";
            $result = $this->db->fetch($sql);
        }
        
        return $result['total_income'] ?? 0;
    }

    public function updateOverdueStatus() {
        $today = date('Y-m-d');
        $sql = "UPDATE rentals SET status = 'overdue' 
                WHERE status = 'active' AND return_date < ?";
        
        try {
            $stmt = $this->db->query($sql, [$today]);
            return $stmt->rowCount();
        } catch (Exception $e) {
            throw new Exception("Failed to update overdue status: " . $e->getMessage());
        }
    }

    private function calculateDays($start_date, $end_date) {
        $start = new DateTime($start_date);
        $end = new DateTime($end_date);
        $diff = $end->diff($start);
        return max(1, $diff->days);
    }

    private function populateFromArray($data) {
        $this->id = $data['id'];
        $this->car_id = $data['car_id'];
        $this->customer_id = $data['customer_id'];
        $this->rental_date = $data['rental_date'];
        $this->return_date = $data['return_date'];
        $this->actual_return_date = $data['actual_return_date'];
        $this->total_amount = $data['total_amount'];
        $this->status = $data['status'];
        $this->created_at = $data['created_at'];
    }

    public function validate($car_id, $customer_id, $rental_date, $return_date) {
        $errors = [];

        if (empty($car_id)) {
            $errors[] = "Car selection is required";
        }

        if (empty($customer_id)) {
            $errors[] = "Customer selection is required";
        }

        if (empty($rental_date)) {
            $errors[] = "Rental date is required";
        } elseif ($rental_date < date('Y-m-d')) {
            $errors[] = "Rental date cannot be in the past";
        }

        if (empty($return_date)) {
            $errors[] = "Return date is required";
        } elseif ($return_date <= $rental_date) {
            $errors[] = "Return date must be after rental date";
        }

        return $errors;
    }
}
?>