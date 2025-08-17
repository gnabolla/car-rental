<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Cars - Car Rental System</title>
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
                <li><a href="cars.php" class="active">Cars</a></li>
                <li><a href="customers.php">Customers</a></li>
                <li><a href="rentals.php">Rentals</a></li>
                <li><a href="reports.php">Reports</a></li>
            </ul>
        </nav>
    </header>

    <main class="container">
        <div class="page-header">
            <h2>Car Management</h2>
            <div class="page-actions">
                <button class="btn btn-primary" onclick="showAddCarModal()">Add New Car</button>
            </div>
        </div>

        <div class="filters">
            <input type="text" id="searchInput" placeholder="Search cars..." onkeyup="searchCars()">
            <select id="statusFilter" onchange="filterCars()">
                <option value="">All Status</option>
                <option value="available">Available</option>
                <option value="rented">Rented</option>
                <option value="maintenance">Maintenance</option>
            </select>
        </div>

        <div class="table-container">
            <table id="carsTable">
                <thead>
                    <tr>
                        <th>Make</th>
                        <th>Model</th>
                        <th>Year</th>
                        <th>Registration</th>
                        <th>Daily Rate</th>
                        <th>Status</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody id="carsTableBody">
                    <tr>
                        <td colspan="7">Loading...</td>
                    </tr>
                </tbody>
            </table>
        </div>
    </main>

    <!-- Add/Edit Car Modal -->
    <div id="carModal" class="modal">
        <div class="modal-content">
            <div class="modal-header">
                <h3 id="modalTitle">Add New Car</h3>
                <span class="close" onclick="closeCarModal()">&times;</span>
            </div>
            <form id="carForm">
                <input type="hidden" id="carId">
                <div class="form-group">
                    <label for="make">Make:</label>
                    <input type="text" id="make" name="make" required>
                </div>
                <div class="form-group">
                    <label for="model">Model:</label>
                    <input type="text" id="model" name="model" required>
                </div>
                <div class="form-group">
                    <label for="year">Year:</label>
                    <input type="number" id="year" name="year" min="1900" max="2030" required>
                </div>
                <div class="form-group">
                    <label for="registration_number">Registration Number:</label>
                    <input type="text" id="registration_number" name="registration_number" required>
                </div>
                <div class="form-group">
                    <label for="daily_rate">Daily Rate (₱):</label>
                    <input type="number" id="daily_rate" name="daily_rate" step="0.01" min="0" required>
                </div>
                <div class="form-group">
                    <label for="status">Status:</label>
                    <select id="status" name="status">
                        <option value="available">Available</option>
                        <option value="maintenance">Maintenance</option>
                    </select>
                </div>
                <div class="form-actions">
                    <button type="button" class="btn btn-secondary" onclick="closeCarModal()">Cancel</button>
                    <button type="submit" class="btn btn-primary">Save Car</button>
                </div>
            </form>
        </div>
    </div>

    <script src="../assets/js/app.js"></script>
    <script>
        let cars = [];
        let filteredCars = [];

        document.addEventListener('DOMContentLoaded', function() {
            loadCars();
            
            document.getElementById('carForm').addEventListener('submit', function(e) {
                e.preventDefault();
                saveCar();
            });
        });

        function loadCars() {
            fetch('../api/cars.php')
                .then(response => response.json())
                .then(data => {
                    cars = data;
                    filteredCars = cars;
                    displayCars(cars);
                })
                .catch(error => {
                    console.error('Error loading cars:', error);
                    showAlert('Error loading cars', 'error');
                });
        }

        function displayCars(carsToShow) {
            const tbody = document.getElementById('carsTableBody');
            
            if (carsToShow.length === 0) {
                tbody.innerHTML = '<tr><td colspan="7">No cars found</td></tr>';
                return;
            }

            tbody.innerHTML = carsToShow.map(car => `
                <tr>
                    <td>${car.make}</td>
                    <td>${car.model}</td>
                    <td>${car.year}</td>
                    <td>${car.registration_number}</td>
                    <td>₱${parseFloat(car.daily_rate).toFixed(2)}</td>
                    <td><span class="status-badge status-${car.status}">${car.status}</span></td>
                    <td>
                        <button class="btn btn-sm btn-secondary" onclick="editCar(${car.id})">Edit</button>
                        <button class="btn btn-sm btn-danger" onclick="deleteCar(${car.id})">Delete</button>
                    </td>
                </tr>
            `).join('');
        }

        function showAddCarModal() {
            document.getElementById('modalTitle').textContent = 'Add New Car';
            document.getElementById('carForm').reset();
            document.getElementById('carId').value = '';
            document.getElementById('carModal').style.display = 'block';
        }

        function editCar(id) {
            const car = cars.find(c => c.id == id);
            if (!car) return;

            document.getElementById('modalTitle').textContent = 'Edit Car';
            document.getElementById('carId').value = car.id;
            document.getElementById('make').value = car.make;
            document.getElementById('model').value = car.model;
            document.getElementById('year').value = car.year;
            document.getElementById('registration_number').value = car.registration_number;
            document.getElementById('daily_rate').value = car.daily_rate;
            document.getElementById('status').value = car.status;
            document.getElementById('carModal').style.display = 'block';
        }

        function closeCarModal() {
            document.getElementById('carModal').style.display = 'none';
        }

        function saveCar() {
            const formData = new FormData(document.getElementById('carForm'));
            const carData = Object.fromEntries(formData);
            const carId = document.getElementById('carId').value;

            const url = carId ? `../api/cars.php?id=${carId}` : '../api/cars.php';
            const method = carId ? 'PUT' : 'POST';

            fetch(url, {
                method: method,
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify(carData)
            })
            .then(response => response.json())
            .then(data => {
                if (data.errors) {
                    showAlert(data.errors.join(', '), 'error');
                } else {
                    showAlert(data.message, 'success');
                    closeCarModal();
                    loadCars();
                }
            })
            .catch(error => {
                console.error('Error saving car:', error);
                showAlert('Error saving car', 'error');
            });
        }

        function deleteCar(id) {
            if (!confirm('Are you sure you want to delete this car?')) return;

            fetch(`../api/cars.php?id=${id}`, {
                method: 'DELETE'
            })
            .then(response => response.json())
            .then(data => {
                showAlert(data.message, 'success');
                loadCars();
            })
            .catch(error => {
                console.error('Error deleting car:', error);
                showAlert('Error deleting car', 'error');
            });
        }

        function searchCars() {
            const searchTerm = document.getElementById('searchInput').value.toLowerCase();
            filteredCars = cars.filter(car => 
                car.make.toLowerCase().includes(searchTerm) ||
                car.model.toLowerCase().includes(searchTerm) ||
                car.registration_number.toLowerCase().includes(searchTerm)
            );
            filterCars();
        }

        function filterCars() {
            const statusFilter = document.getElementById('statusFilter').value;
            let carsToShow = filteredCars;

            if (statusFilter) {
                carsToShow = filteredCars.filter(car => car.status === statusFilter);
            }

            displayCars(carsToShow);
        }

        window.onclick = function(event) {
            const modal = document.getElementById('carModal');
            if (event.target === modal) {
                closeCarModal();
            }
        }
    </script>
</body>
</html>