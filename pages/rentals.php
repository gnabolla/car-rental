<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Rentals - Car Rental System</title>
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
                <li><a href="rentals.php" class="active">Rentals</a></li>
                <li><a href="reports.php">Reports</a></li>
            </ul>
        </nav>
    </header>

    <main class="container">
        <div class="page-header">
            <h2>Rental Management</h2>
            <div class="page-actions">
                <button class="btn btn-primary" onclick="showAddRentalModal()">New Rental</button>
            </div>
        </div>

        <div class="filters">
            <select id="statusFilter" onchange="filterRentals()">
                <option value="">All Rentals</option>
                <option value="active">Active</option>
                <option value="completed">Completed</option>
                <option value="overdue">Overdue</option>
            </select>
        </div>

        <div class="table-container">
            <table id="rentalsTable">
                <thead>
                    <tr>
                        <th>Customer</th>
                        <th>Car</th>
                        <th>Rental Date</th>
                        <th>Return Date</th>
                        <th>Actual Return</th>
                        <th>Status</th>
                        <th>Amount</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody id="rentalsTableBody">
                    <tr>
                        <td colspan="8">Loading...</td>
                    </tr>
                </tbody>
            </table>
        </div>
    </main>

    <!-- Add Rental Modal -->
    <div id="rentalModal" class="modal">
        <div class="modal-content">
            <div class="modal-header">
                <h3>New Rental</h3>
                <span class="close" onclick="closeRentalModal()">&times;</span>
            </div>
            <form id="rentalForm">
                <div class="form-group">
                    <label for="customer_id">Customer:</label>
                    <select id="customer_id" name="customer_id" required>
                        <option value="">Select Customer</option>
                    </select>
                </div>
                <div class="form-group">
                    <label for="car_id">Car:</label>
                    <select id="car_id" name="car_id" required>
                        <option value="">Select Car</option>
                    </select>
                </div>
                <div class="form-group">
                    <label for="rental_date">Rental Date:</label>
                    <input type="date" id="rental_date" name="rental_date" required>
                </div>
                <div class="form-group">
                    <label for="return_date">Return Date:</label>
                    <input type="date" id="return_date" name="return_date" required>
                </div>
                <div id="calculatedAmount" class="calculated-amount"></div>
                <div class="form-actions">
                    <button type="button" class="btn btn-secondary" onclick="closeRentalModal()">Cancel</button>
                    <button type="submit" class="btn btn-primary">Create Rental</button>
                </div>
            </form>
        </div>
    </div>

    <!-- Return Car Modal -->
    <div id="returnModal" class="modal">
        <div class="modal-content">
            <div class="modal-header">
                <h3>Return Car</h3>
                <span class="close" onclick="closeReturnModal()">&times;</span>
            </div>
            <form id="returnForm">
                <input type="hidden" id="return_rental_id">
                <div id="returnDetails"></div>
                <div class="form-group">
                    <label for="actual_return_date">Return Date:</label>
                    <input type="date" id="actual_return_date" name="actual_return_date" required>
                </div>
                <div id="returnCalculation" class="calculated-amount"></div>
                <div class="form-actions">
                    <button type="button" class="btn btn-secondary" onclick="closeReturnModal()">Cancel</button>
                    <button type="submit" class="btn btn-primary">Process Return</button>
                </div>
            </form>
        </div>
    </div>

    <script src="../assets/js/app.js"></script>
    <script>
        let rentals = [];
        let customers = [];
        let cars = [];

        document.addEventListener('DOMContentLoaded', function() {
            loadRentals();
            loadCustomers();
            loadAvailableCars();
            
            document.getElementById('rentalForm').addEventListener('submit', function(e) {
                e.preventDefault();
                createRental();
            });

            document.getElementById('returnForm').addEventListener('submit', function(e) {
                e.preventDefault();
                processReturn();
            });

            document.getElementById('rental_date').value = new Date().toISOString().split('T')[0];
            document.getElementById('actual_return_date').value = new Date().toISOString().split('T')[0];

            document.getElementById('car_id').addEventListener('change', calculateAmount);
            document.getElementById('rental_date').addEventListener('change', calculateAmount);
            document.getElementById('return_date').addEventListener('change', calculateAmount);
        });

        function loadRentals() {
            fetch('../api/rentals.php')
                .then(response => response.json())
                .then(data => {
                    rentals = data;
                    displayRentals(rentals);
                })
                .catch(error => {
                    console.error('Error loading rentals:', error);
                    showAlert('Error loading rentals', 'error');
                });
        }

        function loadCustomers() {
            fetch('../api/customers.php')
                .then(response => response.json())
                .then(data => {
                    customers = data;
                    const select = document.getElementById('customer_id');
                    select.innerHTML = '<option value="">Select Customer</option>' +
                        customers.map(customer => `<option value="${customer.id}">${customer.name}</option>`).join('');
                })
                .catch(error => console.error('Error loading customers:', error));
        }

        function loadAvailableCars() {
            fetch('../api/cars.php?available=1')
                .then(response => response.json())
                .then(data => {
                    cars = data;
                    const select = document.getElementById('car_id');
                    select.innerHTML = '<option value="">Select Car</option>' +
                        cars.map(car => `<option value="${car.id}" data-rate="${car.daily_rate}">${car.year} ${car.make} ${car.model} - ₱${car.daily_rate}/day</option>`).join('');
                })
                .catch(error => console.error('Error loading cars:', error));
        }

        function displayRentals(rentalsToShow) {
            const tbody = document.getElementById('rentalsTableBody');
            
            if (rentalsToShow.length === 0) {
                tbody.innerHTML = '<tr><td colspan="8">No rentals found</td></tr>';
                return;
            }

            tbody.innerHTML = rentalsToShow.map(rental => `
                <tr>
                    <td>${rental.customer_name || 'N/A'}</td>
                    <td>${rental.year} ${rental.make} ${rental.model}</td>
                    <td>${rental.rental_date}</td>
                    <td>${rental.return_date}</td>
                    <td>${rental.actual_return_date || '-'}</td>
                    <td><span class="status-badge status-${rental.status}">${rental.status}</span></td>
                    <td>₱${parseFloat(rental.total_amount).toFixed(2)}</td>
                    <td>
                        ${rental.status === 'active' ? 
                            `<button class="btn btn-sm btn-success" onclick="showReturnModal(${rental.id})">Return</button>` :
                            '<span class="text-muted">Completed</span>'
                        }
                    </td>
                </tr>
            `).join('');
        }

        function showAddRentalModal() {
            document.getElementById('rentalForm').reset();
            document.getElementById('rental_date').value = new Date().toISOString().split('T')[0];
            document.getElementById('calculatedAmount').innerHTML = '';
            document.getElementById('rentalModal').style.display = 'block';
        }

        function showReturnModal(rentalId) {
            const rental = rentals.find(r => r.id == rentalId);
            if (!rental) return;

            document.getElementById('return_rental_id').value = rentalId;
            document.getElementById('actual_return_date').value = new Date().toISOString().split('T')[0];
            
            document.getElementById('returnDetails').innerHTML = `
                <div class="rental-info">
                    <p><strong>Customer:</strong> ${rental.customer_name}</p>
                    <p><strong>Car:</strong> ${rental.year} ${rental.make} ${rental.model}</p>
                    <p><strong>Rental Date:</strong> ${rental.rental_date}</p>
                    <p><strong>Expected Return:</strong> ${rental.return_date}</p>
                    <p><strong>Original Amount:</strong> ₱${parseFloat(rental.total_amount).toFixed(2)}</p>
                </div>
            `;

            calculateReturnAmount(rental);
            document.getElementById('returnModal').style.display = 'block';
        }

        function calculateAmount() {
            const carSelect = document.getElementById('car_id');
            const rentalDate = document.getElementById('rental_date').value;
            const returnDate = document.getElementById('return_date').value;
            
            if (!carSelect.value || !rentalDate || !returnDate) {
                document.getElementById('calculatedAmount').innerHTML = '';
                return;
            }

            const dailyRate = parseFloat(carSelect.options[carSelect.selectedIndex].dataset.rate);
            const start = new Date(rentalDate);
            const end = new Date(returnDate);
            const days = Math.max(1, Math.ceil((end - start) / (1000 * 60 * 60 * 24)));
            const total = days * dailyRate;

            document.getElementById('calculatedAmount').innerHTML = `
                <div class="amount-breakdown">
                    <p><strong>Duration:</strong> ${days} day(s)</p>
                    <p><strong>Daily Rate:</strong> ₱${dailyRate.toFixed(2)}</p>
                    <p><strong>Total Amount:</strong> ₱${total.toFixed(2)}</p>
                </div>
            `;
        }

        function calculateReturnAmount(rental) {
            const actualReturnDate = document.getElementById('actual_return_date').value;
            if (!actualReturnDate) return;

            const rentalStart = new Date(rental.rental_date);
            const expectedReturn = new Date(rental.return_date);
            const actualReturn = new Date(actualReturnDate);
            
            const actualDays = Math.max(1, Math.ceil((actualReturn - rentalStart) / (1000 * 60 * 60 * 24)));
            const plannedDays = Math.max(1, Math.ceil((expectedReturn - rentalStart) / (1000 * 60 * 60 * 24)));
            
            const car = cars.find(c => c.id == rental.car_id);
            const dailyRate = car ? parseFloat(car.daily_rate) : (parseFloat(rental.total_amount) / plannedDays);
            
            let newTotal = actualDays * dailyRate;
            let lateFee = 0;
            
            if (actualReturn > expectedReturn) {
                const lateDays = Math.ceil((actualReturn - expectedReturn) / (1000 * 60 * 60 * 24));
                lateFee = lateDays * dailyRate * 1.5;
                newTotal += lateFee;
            }

            document.getElementById('returnCalculation').innerHTML = `
                <div class="amount-breakdown">
                    <p><strong>Actual Days:</strong> ${actualDays}</p>
                    <p><strong>Planned Days:</strong> ${plannedDays}</p>
                    ${lateFee > 0 ? `<p><strong>Late Fee:</strong> ₱${lateFee.toFixed(2)}</p>` : ''}
                    <p><strong>Final Amount:</strong> ₱${newTotal.toFixed(2)}</p>
                </div>
            `;
        }

        function createRental() {
            const formData = new FormData(document.getElementById('rentalForm'));
            const rentalData = Object.fromEntries(formData);

            fetch('../api/rentals.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify(rentalData)
            })
            .then(response => response.json())
            .then(data => {
                if (data.errors) {
                    showAlert(data.errors.join(', '), 'error');
                } else {
                    showAlert(data.message, 'success');
                    closeRentalModal();
                    loadRentals();
                    loadAvailableCars();
                }
            })
            .catch(error => {
                console.error('Error creating rental:', error);
                showAlert('Error creating rental', 'error');
            });
        }

        function processReturn() {
            const rentalId = document.getElementById('return_rental_id').value;
            const actualReturnDate = document.getElementById('actual_return_date').value;

            fetch('../api/rentals.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify({
                    action: 'return',
                    rental_id: rentalId,
                    actual_return_date: actualReturnDate
                })
            })
            .then(response => response.json())
            .then(data => {
                if (data.error) {
                    showAlert(data.error, 'error');
                } else {
                    showAlert(data.message, 'success');
                    closeReturnModal();
                    loadRentals();
                    loadAvailableCars();
                }
            })
            .catch(error => {
                console.error('Error processing return:', error);
                showAlert('Error processing return', 'error');
            });
        }

        function filterRentals() {
            const statusFilter = document.getElementById('statusFilter').value;
            let rentalsToShow = rentals;

            if (statusFilter) {
                rentalsToShow = rentals.filter(rental => rental.status === statusFilter);
            }

            displayRentals(rentalsToShow);
        }

        function closeRentalModal() {
            document.getElementById('rentalModal').style.display = 'none';
        }

        function closeReturnModal() {
            document.getElementById('returnModal').style.display = 'none';
        }

        document.getElementById('actual_return_date').addEventListener('change', function() {
            const rental = rentals.find(r => r.id == document.getElementById('return_rental_id').value);
            if (rental) calculateReturnAmount(rental);
        });

        window.onclick = function(event) {
            const rentalModal = document.getElementById('rentalModal');
            const returnModal = document.getElementById('returnModal');
            if (event.target === rentalModal) {
                closeRentalModal();
            } else if (event.target === returnModal) {
                closeReturnModal();
            }
        }
    </script>
</body>
</html>