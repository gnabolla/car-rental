<?php

require_once __DIR__ . '/../classes/Car.php';

class CarTest {
    private $car;
    private $testCarId;

    public function __construct() {
        $this->car = new Car();
    }

    public function runTests() {
        echo "Running Car Tests...\n";
        
        $this->testCreateCar();
        $this->testFindById();
        $this->testUpdateCar();
        $this->testValidation();
        $this->testSearchCars();
        $this->testCarAvailability();
        $this->testDeleteCar();
        
        echo "Car Tests Completed!\n\n";
    }

    private function testCreateCar() {
        echo "Testing car creation... ";
        
        try {
            $this->testCarId = $this->car->create(
                'Test Make',
                'Test Model',
                2023,
                'TEST123',
                50.00,
                'available'
            );
            
            if ($this->testCarId > 0) {
                echo "✓ PASSED\n";
            } else {
                echo "✗ FAILED - No ID returned\n";
            }
        } catch (Exception $e) {
            echo "✗ FAILED - " . $e->getMessage() . "\n";
        }
    }

    private function testFindById() {
        echo "Testing find car by ID... ";
        
        try {
            $foundCar = $this->car->findById($this->testCarId);
            
            if ($foundCar && $foundCar->make === 'Test Make') {
                echo "✓ PASSED\n";
            } else {
                echo "✗ FAILED - Car not found or data incorrect\n";
            }
        } catch (Exception $e) {
            echo "✗ FAILED - " . $e->getMessage() . "\n";
        }
    }

    private function testUpdateCar() {
        echo "Testing car update... ";
        
        try {
            $success = $this->car->update(
                $this->testCarId,
                'Updated Make',
                'Updated Model',
                2024,
                'TEST123',
                60.00,
                'maintenance'
            );
            
            if ($success) {
                $updatedCar = $this->car->findById($this->testCarId);
                if ($updatedCar->make === 'Updated Make' && $updatedCar->status === 'maintenance') {
                    echo "✓ PASSED\n";
                } else {
                    echo "✗ FAILED - Update did not persist\n";
                }
            } else {
                echo "✗ FAILED - Update returned false\n";
            }
        } catch (Exception $e) {
            echo "✗ FAILED - " . $e->getMessage() . "\n";
        }
    }

    private function testValidation() {
        echo "Testing car validation... ";
        
        $errors = $this->car->validate('', '', '', '', '');
        
        if (count($errors) > 0) {
            echo "✓ PASSED - Validation caught empty fields\n";
        } else {
            echo "✗ FAILED - Validation should have failed\n";
        }
        
        $errors = $this->car->validate('Make', 'Model', 2023, 'REG123', 50.00);
        
        if (count($errors) === 0) {
            echo "✓ PASSED - Valid data accepted\n";
        } else {
            echo "✗ FAILED - Valid data rejected: " . implode(', ', $errors) . "\n";
        }
    }

    private function testSearchCars() {
        echo "Testing car search... ";
        
        try {
            $results = $this->car->search('Updated');
            
            if (count($results) > 0) {
                echo "✓ PASSED\n";
            } else {
                echo "✗ FAILED - Search should have found test car\n";
            }
        } catch (Exception $e) {
            echo "✗ FAILED - " . $e->getMessage() . "\n";
        }
    }

    private function testCarAvailability() {
        echo "Testing car availability... ";
        
        try {
            $this->car->updateStatus($this->testCarId, 'available');
            $isAvailable = $this->car->isAvailable($this->testCarId);
            
            if ($isAvailable) {
                echo "✓ PASSED - Available status check\n";
            } else {
                echo "✗ FAILED - Should be available\n";
            }
            
            $this->car->updateStatus($this->testCarId, 'rented');
            $isAvailable = $this->car->isAvailable($this->testCarId);
            
            if (!$isAvailable) {
                echo "✓ PASSED - Rented status check\n";
            } else {
                echo "✗ FAILED - Should not be available\n";
            }
        } catch (Exception $e) {
            echo "✗ FAILED - " . $e->getMessage() . "\n";
        }
    }

    private function testDeleteCar() {
        echo "Testing car deletion... ";
        
        try {
            $success = $this->car->delete($this->testCarId);
            
            if ($success) {
                $deletedCar = $this->car->findById($this->testCarId);
                if (!$deletedCar) {
                    echo "✓ PASSED\n";
                } else {
                    echo "✗ FAILED - Car still exists after deletion\n";
                }
            } else {
                echo "✗ FAILED - Delete returned false\n";
            }
        } catch (Exception $e) {
            echo "✗ FAILED - " . $e->getMessage() . "\n";
        }
    }
}
?>