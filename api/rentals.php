<?php

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE');
header('Access-Control-Allow-Headers: Content-Type');

require_once __DIR__ . '/../classes/Rental.php';

$method = $_SERVER['REQUEST_METHOD'];
$rental = new Rental();

try {
    switch ($method) {
        case 'GET':
            handleGet($rental);
            break;
        case 'POST':
            handlePost($rental);
            break;
        case 'PUT':
            handlePut($rental);
            break;
        default:
            http_response_code(405);
            echo json_encode(['error' => 'Method not allowed']);
    }
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['error' => $e->getMessage()]);
}

function handleGet($rental) {
    if (isset($_GET['id'])) {
        $rentalData = $rental->findById($_GET['id']);
        if ($rentalData) {
            echo json_encode($rentalData);
        } else {
            http_response_code(404);
            echo json_encode(['error' => 'Rental not found']);
        }
    } elseif (isset($_GET['active'])) {
        $rentals = $rental->findActive();
        echo json_encode($rentals);
    } elseif (isset($_GET['overdue'])) {
        $rentals = $rental->findOverdue();
        echo json_encode($rentals);
    } else {
        $rentals = $rental->findAll();
        echo json_encode($rentals);
    }
}

function handlePost($rental) {
    $data = json_decode(file_get_contents('php://input'), true);
    
    if (!$data) {
        http_response_code(400);
        echo json_encode(['error' => 'Invalid JSON data']);
        return;
    }
    
    if (isset($data['action']) && $data['action'] === 'return') {
        handleReturn($rental, $data);
        return;
    }
    
    $errors = $rental->validate(
        $data['car_id'] ?? '',
        $data['customer_id'] ?? '',
        $data['rental_date'] ?? '',
        $data['return_date'] ?? ''
    );
    
    if (!empty($errors)) {
        http_response_code(400);
        echo json_encode(['errors' => $errors]);
        return;
    }
    
    $id = $rental->create(
        $data['car_id'],
        $data['customer_id'],
        $data['rental_date'],
        $data['return_date']
    );
    
    http_response_code(201);
    echo json_encode(['id' => $id, 'message' => 'Rental created successfully']);
}

function handleReturn($rental, $data) {
    if (!isset($data['rental_id'])) {
        http_response_code(400);
        echo json_encode(['error' => 'Rental ID is required']);
        return;
    }
    
    $result = $rental->returnCar(
        $data['rental_id'],
        $data['actual_return_date'] ?? null
    );
    
    echo json_encode([
        'message' => 'Car returned successfully',
        'details' => $result
    ]);
}

function handlePut($rental) {
    if (isset($_GET['update_overdue'])) {
        $updated = $rental->updateOverdueStatus();
        echo json_encode([
            'message' => 'Overdue status updated',
            'updated_count' => $updated
        ]);
        return;
    }
    
    http_response_code(400);
    echo json_encode(['error' => 'Invalid update operation']);
}
?>