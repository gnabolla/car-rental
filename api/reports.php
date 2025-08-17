<?php

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET');
header('Access-Control-Allow-Headers: Content-Type');

require_once __DIR__ . '/../classes/Rental.php';
require_once __DIR__ . '/../classes/Car.php';
require_once __DIR__ . '/../classes/Customer.php';

$method = $_SERVER['REQUEST_METHOD'];

try {
    if ($method !== 'GET') {
        http_response_code(405);
        echo json_encode(['error' => 'Method not allowed']);
        exit;
    }
    
    if (isset($_GET['type'])) {
        switch ($_GET['type']) {
            case 'available_cars':
                getAvailableCarsReport();
                break;
            case 'overdue_rentals':
                getOverdueRentalsReport();
                break;
            case 'income':
                getIncomeReport();
                break;
            case 'dashboard':
                getDashboardReport();
                break;
            default:
                http_response_code(400);
                echo json_encode(['error' => 'Invalid report type']);
        }
    } else {
        http_response_code(400);
        echo json_encode(['error' => 'Report type is required']);
    }
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['error' => $e->getMessage()]);
}

function getAvailableCarsReport() {
    $car = new Car();
    $availableCars = $car->findAvailable();
    
    echo json_encode([
        'report_type' => 'Available Cars',
        'generated_at' => date('Y-m-d H:i:s'),
        'total_available' => count($availableCars),
        'cars' => $availableCars
    ]);
}

function getOverdueRentalsReport() {
    $rental = new Rental();
    $overdueRentals = $rental->findOverdue();
    
    $totalOverdueAmount = 0;
    foreach ($overdueRentals as $overdue) {
        $totalOverdueAmount += $overdue['total_amount'];
    }
    
    echo json_encode([
        'report_type' => 'Overdue Rentals',
        'generated_at' => date('Y-m-d H:i:s'),
        'total_overdue' => count($overdueRentals),
        'total_overdue_amount' => $totalOverdueAmount,
        'rentals' => $overdueRentals
    ]);
}

function getIncomeReport() {
    $rental = new Rental();
    
    $start_date = $_GET['start_date'] ?? date('Y-m-01');
    $end_date = $_GET['end_date'] ?? date('Y-m-d');
    
    $dailyIncome = $rental->getIncomeReport($start_date, $end_date);
    $totalIncome = $rental->getTotalIncome($start_date, $end_date);
    
    $monthlyTotal = 0;
    $todayIncome = 0;
    $today = date('Y-m-d');
    
    foreach ($dailyIncome as $day) {
        $monthlyTotal += $day['daily_income'];
        if ($day['date'] === $today) {
            $todayIncome = $day['daily_income'];
        }
    }
    
    echo json_encode([
        'report_type' => 'Income Report',
        'generated_at' => date('Y-m-d H:i:s'),
        'period' => ['start' => $start_date, 'end' => $end_date],
        'total_income' => $totalIncome,
        'period_income' => $monthlyTotal,
        'today_income' => $todayIncome,
        'daily_breakdown' => $dailyIncome
    ]);
}

function getDashboardReport() {
    $car = new Car();
    $customer = new Customer();
    $rental = new Rental();
    
    $allCars = $car->findAll();
    $availableCars = $car->findAvailable();
    $allCustomers = $customer->findAll();
    $activeRentals = $rental->findActive();
    $overdueRentals = $rental->findOverdue();
    
    $totalIncome = $rental->getTotalIncome();
    $monthlyIncome = $rental->getTotalIncome(date('Y-m-01'), date('Y-m-d'));
    
    $carsByStatus = [];
    foreach ($allCars as $carData) {
        $status = $carData['status'];
        $carsByStatus[$status] = ($carsByStatus[$status] ?? 0) + 1;
    }
    
    echo json_encode([
        'report_type' => 'Dashboard Summary',
        'generated_at' => date('Y-m-d H:i:s'),
        'summary' => [
            'total_cars' => count($allCars),
            'available_cars' => count($availableCars),
            'rented_cars' => $carsByStatus['rented'] ?? 0,
            'maintenance_cars' => $carsByStatus['maintenance'] ?? 0,
            'total_customers' => count($allCustomers),
            'active_rentals' => count($activeRentals),
            'overdue_rentals' => count($overdueRentals),
            'total_income' => $totalIncome,
            'monthly_income' => $monthlyIncome
        ],
        'cars_by_status' => $carsByStatus,
        'recent_rentals' => array_slice($rental->findAll(), 0, 5)
    ]);
}
?>