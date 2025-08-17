<?php

require_once __DIR__ . '/CarTest.php';
require_once __DIR__ . '/CustomerTest.php';
require_once __DIR__ . '/RentalTest.php';

echo "========================================\n";
echo "       CAR RENTAL SYSTEM TESTS\n";
echo "========================================\n\n";

$carTest = new CarTest();
$customerTest = new CustomerTest();
$rentalTest = new RentalTest();

$carTest->runTests();
$customerTest->runTests();
$rentalTest->runTests();

echo "========================================\n";
echo "         ALL TESTS COMPLETED\n";
echo "========================================\n";
?>