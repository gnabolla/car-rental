-- Car Rental System Database Schema

-- Cars table
CREATE TABLE IF NOT EXISTS cars (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    make TEXT NOT NULL,
    model TEXT NOT NULL,
    year INTEGER NOT NULL,
    registration_number TEXT UNIQUE NOT NULL,
    daily_rate DECIMAL(10,2) NOT NULL,
    status TEXT DEFAULT 'available' CHECK(status IN ('available', 'rented', 'maintenance')),
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP
);

-- Customers table
CREATE TABLE IF NOT EXISTS customers (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    name TEXT NOT NULL,
    email TEXT UNIQUE NOT NULL,
    phone TEXT NOT NULL,
    driver_license TEXT UNIQUE NOT NULL,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP
);

-- Rentals table
CREATE TABLE IF NOT EXISTS rentals (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    car_id INTEGER NOT NULL,
    customer_id INTEGER NOT NULL,
    rental_date DATE NOT NULL,
    return_date DATE NOT NULL,
    actual_return_date DATE DEFAULT NULL,
    total_amount DECIMAL(10,2) DEFAULT 0,
    status TEXT DEFAULT 'active' CHECK(status IN ('active', 'completed', 'overdue')),
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (car_id) REFERENCES cars(id),
    FOREIGN KEY (customer_id) REFERENCES customers(id)
);

-- Insert sample data
INSERT OR IGNORE INTO cars (make, model, year, registration_number, daily_rate, status) VALUES
('Toyota', 'Camry', 2022, 'ABC123', 2500.00, 'available'),
('Honda', 'Civic', 2021, 'XYZ789', 2200.00, 'available'),
('Ford', 'Focus', 2020, 'DEF456', 1800.00, 'available'),
('BMW', 'X3', 2023, 'BMW001', 4500.00, 'available'),
('Mercedes', 'C-Class', 2022, 'MER002', 4200.00, 'rented');

INSERT OR IGNORE INTO customers (name, email, phone, driver_license) VALUES
('John Doe', 'john@example.com', '+1234567890', 'DL123456789'),
('Jane Smith', 'jane@example.com', '+0987654321', 'DL987654321'),
('Bob Johnson', 'bob@example.com', '+1122334455', 'DL555666777');

INSERT OR IGNORE INTO rentals (car_id, customer_id, rental_date, return_date, total_amount, status) VALUES
(5, 1, '2025-08-15', '2025-08-20', 21000.00, 'active'),
(2, 2, '2025-08-10', '2025-08-15', 11000.00, 'overdue');