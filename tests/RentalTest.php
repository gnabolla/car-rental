<?php

require_once __DIR__ . '/../classes/Rental.php';

class RentalTest {
    private $rental;
    private $car;
    private $customer;
    private $testRentalId;
    private $testCarId;
    private $testCustomerId;

    public function __construct() {
        $this->rental = new Rental();
        $this->car = new Car();
        $this->customer = new Customer();
    }

    public function runTests() {
        echo "Running Rental Tests...\n";
        
        $this->setupTestData();
        $this->testCreateRental();
        $this->testFindById();
        $this->testValidation();
        $this->testReturnCar();
        $this->testOverdueRentals();
        $this->cleanupTestData();
        
        echo "Rental Tests Completed!\n\n";
    }

    private function setupTestData() {
        echo "Setting up test data... ";
        
        try {
            $this->testCarId = $this->car->create(
                'Test Car',
                'Test Model',
                2023,
                'RENTAL123',
                50.00,
                'available'
            );
            
            $this->testCustomerId = $this->customer->create(
                'Test Rental Customer',
                'rental@test.com',
                '+1111111111',
                'RENT123456'
            );
            
            echo "✓ SETUP COMPLETE\n";
        } catch (Exception $e) {
            echo "✗ SETUP FAILED - " . $e->getMessage() . "\n";
        }
    }

    private function testCreateRental() {
        echo "Testing rental creation... ";
        
        try {
            $rentalDate = date('Y-m-d');
            $returnDate = date('Y-m-d', strtotime('+5 days'));
            
            $this->testRentalId = $this->rental->create(
                $this->testCarId,
                $this->testCustomerId,
                $rentalDate,
                $returnDate
            );
            
            if ($this->testRentalId > 0) {
                $carStatus = $this->car->findById($this->testCarId);
                if ($carStatus->status === 'rented') {
                    echo "✓ PASSED - Rental created and car status updated\n";
                } else {
                    echo "✗ FAILED - Car status not updated to rented\n";
                }
            } else {
                echo "✗ FAILED - No rental ID returned\n";
            }
        } catch (Exception $e) {
            echo "✗ FAILED - " . $e->getMessage() . "\n";
        }
    }

    private function testFindById() {
        echo "Testing find rental by ID... ";
        
        try {
            $foundRental = $this->rental->findById($this->testRentalId);
            
            if ($foundRental && $foundRental->car_id == $this->testCarId) {
                echo "✓ PASSED\n";
            } else {
                echo "✗ FAILED - Rental not found or data incorrect\n";
            }
        } catch (Exception $e) {
            echo "✗ FAILED - " . $e->getMessage() . "\n";
        }
    }

    private function testValidation() {
        echo "Testing rental validation... ";
        
        $errors = $this->rental->validate('', '', '', '');
        
        if (count($errors) > 0) {
            echo "✓ PASSED - Validation caught empty fields\n";
        } else {
            echo "✗ FAILED - Validation should have failed\n";
        }
        
        $pastDate = date('Y-m-d', strtotime('-1 day'));
        $futureDate = date('Y-m-d', strtotime('+5 days'));
        
        $errors = $this->rental->validate($this->testCarId, $this->testCustomerId, $pastDate, $futureDate);
        
        if (count($errors) > 0) {
            echo "✓ PASSED - Validation caught past rental date\n";
        } else {
            echo "✗ FAILED - Should have caught past rental date\n";
        }
        
        $todayDate = date('Y-m-d');
        $tomorrowDate = date('Y-m-d', strtotime('+1 day'));
        
        $errors = $this->rental->validate(999, 999, $todayDate, $tomorrowDate);
        
        if (count($errors) === 0) {
            echo "✓ PASSED - Valid future dates accepted\n";
        } else {
            echo "✗ FAILED - Valid dates rejected: " . implode(', ', $errors) . "\n";
        }
    }

    private function testReturnCar() {
        echo "Testing car return... ";
        
        try {
            $returnDate = date('Y-m-d');
            $result = $this->rental->returnCar($this->testRentalId, $returnDate);
            
            if ($result && isset($result['total_amount'])) {
                $returnedRental = $this->rental->findById($this->testRentalId);
                $carStatus = $this->car->findById($this->testCarId);
                
                if ($returnedRental->status === 'completed' && $carStatus->status === 'available') {
                    echo "✓ PASSED - Car returned and statuses updated\n";
                } else {
                    echo "✗ FAILED - Statuses not updated correctly\n";
                }
            } else {
                echo "✗ FAILED - Return result invalid\n";
            }
        } catch (Exception $e) {
            echo "✗ FAILED - " . $e->getMessage() . "\n";
        }
    }

    private function testOverdueRentals() {
        echo "Testing overdue rental detection... ";
        
        try {
            $overdue = $this->rental->findOverdue();
            
            echo "✓ PASSED - Overdue query executed (found " . count($overdue) . " overdue rentals)\n";
        } catch (Exception $e) {
            echo "✗ FAILED - " . $e->getMessage() . "\n";
        }
    }

    private function cleanupTestData() {
        echo "Cleaning up test data... ";
        
        try {
            if ($this->testCustomerId) {
                $this->customer->delete($this->testCustomerId);
            }
            if ($this->testCarId) {
                $this->car->delete($this->testCarId);
            }
            
            echo "✓ CLEANUP COMPLETE\n";
        } catch (Exception $e) {
            echo "✗ CLEANUP FAILED - " . $e->getMessage() . "\n";
        }
    }
}
?>