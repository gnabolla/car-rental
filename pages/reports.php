<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reports - Car Rental System</title>
    <link rel="stylesheet" href="../assets/css/style.css">
</head>
<body>
    <header>
        <nav class="navbar">
            <div class="nav-brand">
                <h1>🚗 Car Rental System</h1>
            </div>
            <ul class="nav-links">
                <li><a href="../index.php">Dashboard</a></li>
                <li><a href="cars.php">Cars</a></li>
                <li><a href="customers.php">Customers</a></li>
                <li><a href="rentals.php">Rentals</a></li>
                <li><a href="reports.php" class="active">Reports</a></li>
            </ul>
        </nav>
    </header>

    <main class="container">
        <div class="page-header">
            <h2>Reports</h2>
            <div class="page-actions">
                <button class="btn btn-secondary" onclick="printReport()">Print Report</button>
            </div>
        </div>

        <div class="report-tabs">
            <button class="tab-btn active" onclick="showReport('available-cars')">Available Cars</button>
            <button class="tab-btn" onclick="showReport('overdue-rentals')">Overdue Rentals</button>
            <button class="tab-btn" onclick="showReport('income')">Income Report</button>
        </div>

        <!-- Available Cars Report -->
        <div id="available-cars-report" class="report-section active">
            <div class="report-header">
                <h3>Available Cars Report</h3>
                <p id="available-generated-time">Generated: -</p>
            </div>
            <div class="report-summary">
                <div class="summary-item">
                    <span class="summary-label">Total Available:</span>
                    <span class="summary-value" id="available-total">-</span>
                </div>
            </div>
            <div class="table-container">
                <table id="availableCarsTable">
                    <thead>
                        <tr>
                            <th>Make</th>
                            <th>Model</th>
                            <th>Year</th>
                            <th>Registration</th>
                            <th>Daily Rate</th>
                        </tr>
                    </thead>
                    <tbody id="availableCarsBody">
                        <tr><td colspan="5">Loading...</td></tr>
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Overdue Rentals Report -->
        <div id="overdue-rentals-report" class="report-section">
            <div class="report-header">
                <h3>Overdue Rentals Report</h3>
                <p id="overdue-generated-time">Generated: -</p>
            </div>
            <div class="report-summary">
                <div class="summary-item">
                    <span class="summary-label">Total Overdue:</span>
                    <span class="summary-value" id="overdue-total">-</span>
                </div>
                <div class="summary-item">
                    <span class="summary-label">Total Amount:</span>
                    <span class="summary-value" id="overdue-amount">-</span>
                </div>
            </div>
            <div class="table-container">
                <table id="overdueRentalsTable">
                    <thead>
                        <tr>
                            <th>Customer</th>
                            <th>Car</th>
                            <th>Rental Date</th>
                            <th>Expected Return</th>
                            <th>Days Overdue</th>
                            <th>Amount</th>
                        </tr>
                    </thead>
                    <tbody id="overdueRentalsBody">
                        <tr><td colspan="6">Loading...</td></tr>
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Income Report -->
        <div id="income-report" class="report-section">
            <div class="report-header">
                <h3>Income Report</h3>
                <div class="date-filters">
                    <label for="start-date">From:</label>
                    <input type="date" id="start-date" onchange="loadIncomeReport()">
                    <label for="end-date">To:</label>
                    <input type="date" id="end-date" onchange="loadIncomeReport()">
                </div>
                <p id="income-generated-time">Generated: -</p>
            </div>
            <div class="report-summary">
                <div class="summary-item">
                    <span class="summary-label">Total Income:</span>
                    <span class="summary-value" id="income-total">-</span>
                </div>
                <div class="summary-item">
                    <span class="summary-label">Period Income:</span>
                    <span class="summary-value" id="income-period">-</span>
                </div>
                <div class="summary-item">
                    <span class="summary-label">Today's Income:</span>
                    <span class="summary-value" id="income-today">-</span>
                </div>
            </div>
            <div class="table-container">
                <table id="incomeTable">
                    <thead>
                        <tr>
                            <th>Date</th>
                            <th>Rentals</th>
                            <th>Daily Income</th>
                        </tr>
                    </thead>
                    <tbody id="incomeBody">
                        <tr><td colspan="3">Loading...</td></tr>
                    </tbody>
                </table>
            </div>
        </div>
    </main>

    <script src="../assets/js/app.js"></script>
    <script>
        let currentReport = 'available-cars';

        document.addEventListener('DOMContentLoaded', function() {
            const today = new Date();
            const firstDay = new Date(today.getFullYear(), today.getMonth(), 1);
            
            document.getElementById('start-date').value = firstDay.toISOString().split('T')[0];
            document.getElementById('end-date').value = today.toISOString().split('T')[0];
            
            loadAvailableCarsReport();
        });

        function showReport(reportType) {
            document.querySelectorAll('.report-section').forEach(section => {
                section.classList.remove('active');
            });
            
            document.querySelectorAll('.tab-btn').forEach(btn => {
                btn.classList.remove('active');
            });

            document.getElementById(reportType + '-report').classList.add('active');
            event.target.classList.add('active');
            
            currentReport = reportType;

            switch(reportType) {
                case 'available-cars':
                    loadAvailableCarsReport();
                    break;
                case 'overdue-rentals':
                    loadOverdueRentalsReport();
                    break;
                case 'income':
                    loadIncomeReport();
                    break;
            }
        }

        function loadAvailableCarsReport() {
            fetch('../api/reports.php?type=available_cars')
                .then(response => response.json())
                .then(data => {
                    document.getElementById('available-generated-time').textContent = 'Generated: ' + data.generated_at;
                    document.getElementById('available-total').textContent = data.total_available;
                    
                    const tbody = document.getElementById('availableCarsBody');
                    if (data.cars.length === 0) {
                        tbody.innerHTML = '<tr><td colspan="5">No available cars</td></tr>';
                    } else {
                        tbody.innerHTML = data.cars.map(car => `
                            <tr>
                                <td>${car.make}</td>
                                <td>${car.model}</td>
                                <td>${car.year}</td>
                                <td>${car.registration_number}</td>
                                <td>₱${parseFloat(car.daily_rate).toFixed(2)}</td>
                            </tr>
                        `).join('');
                    }
                })
                .catch(error => {
                    console.error('Error loading available cars report:', error);
                    showAlert('Error loading report', 'error');
                });
        }

        function loadOverdueRentalsReport() {
            fetch('../api/reports.php?type=overdue_rentals')
                .then(response => response.json())
                .then(data => {
                    document.getElementById('overdue-generated-time').textContent = 'Generated: ' + data.generated_at;
                    document.getElementById('overdue-total').textContent = data.total_overdue;
                    document.getElementById('overdue-amount').textContent = '₱' + parseFloat(data.total_overdue_amount || 0).toFixed(2);
                    
                    const tbody = document.getElementById('overdueRentalsBody');
                    if (data.rentals.length === 0) {
                        tbody.innerHTML = '<tr><td colspan="6">No overdue rentals</td></tr>';
                    } else {
                        tbody.innerHTML = data.rentals.map(rental => `
                            <tr>
                                <td>${rental.customer_name}</td>
                                <td>${rental.year} ${rental.make} ${rental.model}</td>
                                <td>${rental.rental_date}</td>
                                <td>${rental.return_date}</td>
                                <td>${Math.ceil(rental.days_overdue)} days</td>
                                <td>₱${parseFloat(rental.total_amount).toFixed(2)}</td>
                            </tr>
                        `).join('');
                    }
                })
                .catch(error => {
                    console.error('Error loading overdue rentals report:', error);
                    showAlert('Error loading report', 'error');
                });
        }

        function loadIncomeReport() {
            const startDate = document.getElementById('start-date').value;
            const endDate = document.getElementById('end-date').value;
            
            fetch(`../api/reports.php?type=income&start_date=${startDate}&end_date=${endDate}`)
                .then(response => response.json())
                .then(data => {
                    document.getElementById('income-generated-time').textContent = 'Generated: ' + data.generated_at;
                    document.getElementById('income-total').textContent = '₱' + parseFloat(data.total_income || 0).toFixed(2);
                    document.getElementById('income-period').textContent = '₱' + parseFloat(data.period_income || 0).toFixed(2);
                    document.getElementById('income-today').textContent = '₱' + parseFloat(data.today_income || 0).toFixed(2);
                    
                    const tbody = document.getElementById('incomeBody');
                    if (data.daily_breakdown.length === 0) {
                        tbody.innerHTML = '<tr><td colspan="3">No income data for selected period</td></tr>';
                    } else {
                        tbody.innerHTML = data.daily_breakdown.map(day => `
                            <tr>
                                <td>${day.date}</td>
                                <td>${day.rental_count}</td>
                                <td>₱${parseFloat(day.daily_income).toFixed(2)}</td>
                            </tr>
                        `).join('');
                    }
                })
                .catch(error => {
                    console.error('Error loading income report:', error);
                    showAlert('Error loading report', 'error');
                });
        }

        function printReport() {
            window.print();
        }
    </script>
    
    <style>
        @media print {
            .navbar, .page-actions, .report-tabs {
                display: none !important;
            }
            
            .report-section:not(.active) {
                display: none !important;
            }
            
            .container {
                margin: 0;
                padding: 0;
            }
            
            .table-container {
                overflow: visible;
            }
            
            table {
                width: 100%;
                border-collapse: collapse;
            }
            
            th, td {
                border: 1px solid #ddd;
                padding: 8px;
                text-align: left;
            }
        }
    </style>
</body>
</html>