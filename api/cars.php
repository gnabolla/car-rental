<?php

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE');
header('Access-Control-Allow-Headers: Content-Type');

require_once __DIR__ . '/../classes/Car.php';

$method = $_SERVER['REQUEST_METHOD'];
$car = new Car();

try {
    switch ($method) {
        case 'GET':
            handleGet($car);
            break;
        case 'POST':
            handlePost($car);
            break;
        case 'PUT':
            handlePut($car);
            break;
        case 'DELETE':
            handleDelete($car);
            break;
        default:
            http_response_code(405);
            echo json_encode(['error' => 'Method not allowed']);
    }
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['error' => $e->getMessage()]);
}

function handleGet($car) {
    if (isset($_GET['id'])) {
        $carData = $car->findById($_GET['id']);
        if ($carData) {
            echo json_encode($carData);
        } else {
            http_response_code(404);
            echo json_encode(['error' => 'Car not found']);
        }
    } elseif (isset($_GET['available'])) {
        $cars = $car->findAvailable();
        echo json_encode($cars);
    } elseif (isset($_GET['search'])) {
        $cars = $car->search($_GET['search']);
        echo json_encode($cars);
    } else {
        $cars = $car->findAll();
        echo json_encode($cars);
    }
}

function handlePost($car) {
    $data = json_decode(file_get_contents('php://input'), true);
    
    if (!$data) {
        http_response_code(400);
        echo json_encode(['error' => 'Invalid JSON data']);
        return;
    }
    
    $errors = $car->validate(
        $data['make'] ?? '',
        $data['model'] ?? '',
        $data['year'] ?? '',
        $data['registration_number'] ?? '',
        $data['daily_rate'] ?? ''
    );
    
    if (!empty($errors)) {
        http_response_code(400);
        echo json_encode(['errors' => $errors]);
        return;
    }
    
    $id = $car->create(
        $data['make'],
        $data['model'],
        $data['year'],
        $data['registration_number'],
        $data['daily_rate'],
        $data['status'] ?? 'available'
    );
    
    http_response_code(201);
    echo json_encode(['id' => $id, 'message' => 'Car created successfully']);
}

function handlePut($car) {
    if (!isset($_GET['id'])) {
        http_response_code(400);
        echo json_encode(['error' => 'Car ID is required']);
        return;
    }
    
    $data = json_decode(file_get_contents('php://input'), true);
    
    if (!$data) {
        http_response_code(400);
        echo json_encode(['error' => 'Invalid JSON data']);
        return;
    }
    
    $errors = $car->validate(
        $data['make'] ?? '',
        $data['model'] ?? '',
        $data['year'] ?? '',
        $data['registration_number'] ?? '',
        $data['daily_rate'] ?? ''
    );
    
    if (!empty($errors)) {
        http_response_code(400);
        echo json_encode(['errors' => $errors]);
        return;
    }
    
    $success = $car->update(
        $_GET['id'],
        $data['make'],
        $data['model'],
        $data['year'],
        $data['registration_number'],
        $data['daily_rate'],
        $data['status'] ?? 'available'
    );
    
    if ($success) {
        echo json_encode(['message' => 'Car updated successfully']);
    } else {
        http_response_code(404);
        echo json_encode(['error' => 'Car not found']);
    }
}

function handleDelete($car) {
    if (!isset($_GET['id'])) {
        http_response_code(400);
        echo json_encode(['error' => 'Car ID is required']);
        return;
    }
    
    $success = $car->delete($_GET['id']);
    
    if ($success) {
        echo json_encode(['message' => 'Car deleted successfully']);
    } else {
        http_response_code(404);
        echo json_encode(['error' => 'Car not found']);
    }
}
?>