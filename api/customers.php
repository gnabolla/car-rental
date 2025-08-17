<?php

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE');
header('Access-Control-Allow-Headers: Content-Type');

require_once __DIR__ . '/../classes/Customer.php';

$method = $_SERVER['REQUEST_METHOD'];
$customer = new Customer();

try {
    switch ($method) {
        case 'GET':
            handleGet($customer);
            break;
        case 'POST':
            handlePost($customer);
            break;
        case 'PUT':
            handlePut($customer);
            break;
        case 'DELETE':
            handleDelete($customer);
            break;
        default:
            http_response_code(405);
            echo json_encode(['error' => 'Method not allowed']);
    }
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['error' => $e->getMessage()]);
}

function handleGet($customer) {
    if (isset($_GET['id'])) {
        $customerData = $customer->findById($_GET['id']);
        if ($customerData) {
            echo json_encode($customerData);
        } else {
            http_response_code(404);
            echo json_encode(['error' => 'Customer not found']);
        }
    } elseif (isset($_GET['search'])) {
        $customers = $customer->search($_GET['search']);
        echo json_encode($customers);
    } elseif (isset($_GET['rental_history'])) {
        $history = $customer->getRentalHistory($_GET['rental_history']);
        echo json_encode($history);
    } elseif (isset($_GET['active_rentals'])) {
        $rentals = $customer->getActiveRentals($_GET['active_rentals']);
        echo json_encode($rentals);
    } else {
        $customers = $customer->findAll();
        echo json_encode($customers);
    }
}

function handlePost($customer) {
    $data = json_decode(file_get_contents('php://input'), true);
    
    if (!$data) {
        http_response_code(400);
        echo json_encode(['error' => 'Invalid JSON data']);
        return;
    }
    
    $errors = $customer->validate(
        $data['name'] ?? '',
        $data['email'] ?? '',
        $data['phone'] ?? '',
        $data['driver_license'] ?? ''
    );
    
    if (!empty($errors)) {
        http_response_code(400);
        echo json_encode(['errors' => $errors]);
        return;
    }
    
    $id = $customer->create(
        $data['name'],
        $data['email'],
        $data['phone'],
        $data['driver_license']
    );
    
    http_response_code(201);
    echo json_encode(['id' => $id, 'message' => 'Customer created successfully']);
}

function handlePut($customer) {
    if (!isset($_GET['id'])) {
        http_response_code(400);
        echo json_encode(['error' => 'Customer ID is required']);
        return;
    }
    
    $data = json_decode(file_get_contents('php://input'), true);
    
    if (!$data) {
        http_response_code(400);
        echo json_encode(['error' => 'Invalid JSON data']);
        return;
    }
    
    $errors = $customer->validate(
        $data['name'] ?? '',
        $data['email'] ?? '',
        $data['phone'] ?? '',
        $data['driver_license'] ?? '',
        $_GET['id']
    );
    
    if (!empty($errors)) {
        http_response_code(400);
        echo json_encode(['errors' => $errors]);
        return;
    }
    
    $success = $customer->update(
        $_GET['id'],
        $data['name'],
        $data['email'],
        $data['phone'],
        $data['driver_license']
    );
    
    if ($success) {
        echo json_encode(['message' => 'Customer updated successfully']);
    } else {
        http_response_code(404);
        echo json_encode(['error' => 'Customer not found']);
    }
}

function handleDelete($customer) {
    if (!isset($_GET['id'])) {
        http_response_code(400);
        echo json_encode(['error' => 'Customer ID is required']);
        return;
    }
    
    if ($customer->hasActiveRentals($_GET['id'])) {
        http_response_code(400);
        echo json_encode(['error' => 'Cannot delete customer with active rentals']);
        return;
    }
    
    $success = $customer->delete($_GET['id']);
    
    if ($success) {
        echo json_encode(['message' => 'Customer deleted successfully']);
    } else {
        http_response_code(404);
        echo json_encode(['error' => 'Customer not found']);
    }
}
?>