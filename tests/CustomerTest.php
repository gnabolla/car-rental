<?php

require_once __DIR__ . '/../classes/Customer.php';

class CustomerTest {
    private $customer;
    private $testCustomerId;

    public function __construct() {
        $this->customer = new Customer();
    }

    public function runTests() {
        echo "Running Customer Tests...\n";
        
        $this->testCreateCustomer();
        $this->testFindById();
        $this->testFindByEmail();
        $this->testUpdateCustomer();
        $this->testValidation();
        $this->testSearchCustomers();
        $this->testDeleteCustomer();
        
        echo "Customer Tests Completed!\n\n";
    }

    private function testCreateCustomer() {
        echo "Testing customer creation... ";
        
        try {
            $this->testCustomerId = $this->customer->create(
                'Test Customer',
                'test@example.com',
                '+1234567890',
                'TEST123456'
            );
            
            if ($this->testCustomerId > 0) {
                echo "✓ PASSED\n";
            } else {
                echo "✗ FAILED - No ID returned\n";
            }
        } catch (Exception $e) {
            echo "✗ FAILED - " . $e->getMessage() . "\n";
        }
    }

    private function testFindById() {
        echo "Testing find customer by ID... ";
        
        try {
            $foundCustomer = $this->customer->findById($this->testCustomerId);
            
            if ($foundCustomer && $foundCustomer->name === 'Test Customer') {
                echo "✓ PASSED\n";
            } else {
                echo "✗ FAILED - Customer not found or data incorrect\n";
            }
        } catch (Exception $e) {
            echo "✗ FAILED - " . $e->getMessage() . "\n";
        }
    }

    private function testFindByEmail() {
        echo "Testing find customer by email... ";
        
        try {
            $foundCustomer = $this->customer->findByEmail('test@example.com');
            
            if ($foundCustomer && $foundCustomer->name === 'Test Customer') {
                echo "✓ PASSED\n";
            } else {
                echo "✗ FAILED - Customer not found by email\n";
            }
        } catch (Exception $e) {
            echo "✗ FAILED - " . $e->getMessage() . "\n";
        }
    }

    private function testUpdateCustomer() {
        echo "Testing customer update... ";
        
        try {
            $success = $this->customer->update(
                $this->testCustomerId,
                'Updated Customer',
                'updated@example.com',
                '+0987654321',
                'UPD123456'
            );
            
            if ($success) {
                $updatedCustomer = $this->customer->findById($this->testCustomerId);
                if ($updatedCustomer->name === 'Updated Customer' && $updatedCustomer->email === 'updated@example.com') {
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
        echo "Testing customer validation... ";
        
        $errors = $this->customer->validate('', '', '', '');
        
        if (count($errors) > 0) {
            echo "✓ PASSED - Validation caught empty fields\n";
        } else {
            echo "✗ FAILED - Validation should have failed\n";
        }
        
        $errors = $this->customer->validate('Valid Name', 'invalid-email', '+1234567890', 'DL123');
        
        if (count($errors) > 0) {
            echo "✓ PASSED - Validation caught invalid email\n";
        } else {
            echo "✗ FAILED - Should have caught invalid email\n";
        }
        
        $errors = $this->customer->validate('Valid Name', 'new@example.com', '+1234567890', 'NEW123456');
        
        if (count($errors) === 0) {
            echo "✓ PASSED - Valid data accepted\n";
        } else {
            echo "✗ FAILED - Valid data rejected: " . implode(', ', $errors) . "\n";
        }
    }

    private function testSearchCustomers() {
        echo "Testing customer search... ";
        
        try {
            $results = $this->customer->search('Updated');
            
            if (count($results) > 0) {
                echo "✓ PASSED\n";
            } else {
                echo "✗ FAILED - Search should have found test customer\n";
            }
        } catch (Exception $e) {
            echo "✗ FAILED - " . $e->getMessage() . "\n";
        }
    }

    private function testDeleteCustomer() {
        echo "Testing customer deletion... ";
        
        try {
            $success = $this->customer->delete($this->testCustomerId);
            
            if ($success) {
                $deletedCustomer = $this->customer->findById($this->testCustomerId);
                if (!$deletedCustomer) {
                    echo "✓ PASSED\n";
                } else {
                    echo "✗ FAILED - Customer still exists after deletion\n";
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