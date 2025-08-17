<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Car Rental System</title>
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body>
    <header>
        <nav class="navbar">
            <div class="nav-brand">
                <h1>🚗 Car Rental System</h1>
            </div>
            <ul class="nav-links">
                <li><a href="index.php" class="active">Dashboard</a></li>
                <li><a href="pages/cars.php">Cars</a></li>
                <li><a href="pages/customers.php">Customers</a></li>
                <li><a href="pages/rentals.php">Rentals</a></li>
                <li><a href="pages/reports.php">Reports</a></li>
            </ul>
        </nav>
    </header>

    <main class="container">
        <div class="dashboard-header">
            <h2>Dashboard</h2>
            <p>Welcome to the Car Rental Management System</p>
        </div>

        <div class="stats-grid" id="statsGrid">
            <div class="stat-card">
                <div class="stat-icon">🚗</div>
                <div class="stat-info">
                    <h3 id="totalCars">-</h3>
                    <p>Total Cars</p>
                </div>
            </div>
            <div class="stat-card">
                <div class="stat-icon">✅</div>
                <div class="stat-info">
                    <h3 id="availableCars">-</h3>
                    <p>Available Cars</p>
                </div>
            </div>
            <div class="stat-card">
                <div class="stat-icon">👥</div>
                <div class="stat-info">
                    <h3 id="totalCustomers">-</h3>
                    <p>Total Customers</p>
                </div>
            </div>
            <div class="stat-card">
                <div class="stat-icon">📋</div>
                <div class="stat-info">
                    <h3 id="activeRentals">-</h3>
                    <p>Active Rentals</p>
                </div>
            </div>
            <div class="stat-card">
                <div class="stat-icon">⚠️</div>
                <div class="stat-info">
                    <h3 id="overdueRentals">-</h3>
                    <p>Overdue Rentals</p>
                </div>
            </div>
            <div class="stat-card">
                <div class="stat-icon">💰</div>
                <div class="stat-info">
                    <h3 id="monthlyIncome">-</h3>
                    <p>Monthly Income</p>
                </div>
            </div>
        </div>

        <div class="dashboard-content">
            <div class="dashboard-section">
                <h3>Recent Rentals</h3>
                <div class="table-container">
                    <table id="recentRentalsTable">
                        <thead>
                            <tr>
                                <th>Customer</th>
                                <th>Car</th>
                                <th>Rental Date</th>
                                <th>Return Date</th>
                                <th>Status</th>
                                <th>Amount</th>
                            </tr>
                        </thead>
                        <tbody id="recentRentalsBody">
                            <tr>
                                <td colspan="6">Loading...</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>

            <div class="dashboard-section">
                <h3>Quick Actions</h3>
                <div class="quick-actions">
                    <a href="pages/cars.php" class="action-btn">
                        <span class="btn-icon">➕</span>
                        Add New Car
                    </a>
                    <a href="pages/customers.php" class="action-btn">
                        <span class="btn-icon">👤</span>
                        Add Customer
                    </a>
                    <a href="pages/rentals.php" class="action-btn">
                        <span class="btn-icon">📝</span>
                        New Rental
                    </a>
                    <a href="pages/reports.php" class="action-btn">
                        <span class="btn-icon">📊</span>
                        View Reports
                    </a>
                </div>
            </div>
        </div>
    </main>

    <script src="assets/js/app.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            loadDashboardData();
        });

        function loadDashboardData() {
            fetch('api/reports.php?type=dashboard')
                .then(response => response.json())
                .then(data => {
                    if (data.summary) {
                        document.getElementById('totalCars').textContent = data.summary.total_cars;
                        document.getElementById('availableCars').textContent = data.summary.available_cars;
                        document.getElementById('totalCustomers').textContent = data.summary.total_customers;
                        document.getElementById('activeRentals').textContent = data.summary.active_rentals;
                        document.getElementById('overdueRentals').textContent = data.summary.overdue_rentals;
                        document.getElementById('monthlyIncome').textContent = '₱' + parseFloat(data.summary.monthly_income || 0).toFixed(2);
                    }

                    if (data.recent_rentals) {
                        displayRecentRentals(data.recent_rentals);
                    }
                })
                .catch(error => {
                    console.error('Error loading dashboard data:', error);
                });
        }

        function displayRecentRentals(rentals) {
            const tbody = document.getElementById('recentRentalsBody');
            
            if (rentals.length === 0) {
                tbody.innerHTML = '<tr><td colspan="6">No recent rentals found</td></tr>';
                return;
            }

            tbody.innerHTML = rentals.map(rental => `
                <tr>
                    <td>${rental.customer_name || 'N/A'}</td>
                    <td>${rental.year} ${rental.make} ${rental.model}</td>
                    <td>${rental.rental_date}</td>
                    <td>${rental.return_date}</td>
                    <td><span class="status-badge status-${rental.status}">${rental.status}</span></td>
                    <td>₱${parseFloat(rental.total_amount).toFixed(2)}</td>
                </tr>
            `).join('');
        }
    </script>
</body>
</html>