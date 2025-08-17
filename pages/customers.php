<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Customers - Car Rental System</title>
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
                <li><a href="customers.php" class="active">Customers</a></li>
                <li><a href="rentals.php">Rentals</a></li>
                <li><a href="reports.php">Reports</a></li>
            </ul>
        </nav>
    </header>

    <main class="container">
        <div class="page-header">
            <h2>Customer Management</h2>
            <div class="page-actions">
                <button class="btn btn-primary" onclick="showAddCustomerModal()">Add New Customer</button>
            </div>
        </div>

        <div class="filters">
            <input type="text" id="searchInput" placeholder="Search customers..." onkeyup="searchCustomers()">
        </div>

        <div class="table-container">
            <table id="customersTable">
                <thead>
                    <tr>
                        <th>Name</th>
                        <th>Email</th>
                        <th>Phone</th>
                        <th>Driver License</th>
                        <th>Member Since</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody id="customersTableBody">
                    <tr>
                        <td colspan="6">Loading...</td>
                    </tr>
                </tbody>
            </table>
        </div>
    </main>

    <!-- Add/Edit Customer Modal -->
    <div id="customerModal" class="modal">
        <div class="modal-content">
            <div class="modal-header">
                <h3 id="modalTitle">Add New Customer</h3>
                <span class="close" onclick="closeCustomerModal()">&times;</span>
            </div>
            <form id="customerForm">
                <input type="hidden" id="customerId">
                <div class="form-group">
                    <label for="name">Full Name:</label>
                    <input type="text" id="name" name="name" required>
                </div>
                <div class="form-group">
                    <label for="email">Email:</label>
                    <input type="email" id="email" name="email" required>
                </div>
                <div class="form-group">
                    <label for="phone">Phone:</label>
                    <input type="tel" id="phone" name="phone" required>
                </div>
                <div class="form-group">
                    <label for="driver_license">Driver License:</label>
                    <input type="text" id="driver_license" name="driver_license" required>
                </div>
                <div class="form-actions">
                    <button type="button" class="btn btn-secondary" onclick="closeCustomerModal()">Cancel</button>
                    <button type="submit" class="btn btn-primary">Save Customer</button>
                </div>
            </form>
        </div>
    </div>

    <!-- Customer Details Modal -->
    <div id="customerDetailsModal" class="modal">
        <div class="modal-content">
            <div class="modal-header">
                <h3>Customer Details</h3>
                <span class="close" onclick="closeCustomerDetailsModal()">&times;</span>
            </div>
            <div id="customerDetailsContent">
                Loading...
            </div>
        </div>
    </div>

    <script src="../assets/js/app.js"></script>
    <script>
        let customers = [];
        let filteredCustomers = [];

        document.addEventListener('DOMContentLoaded', function() {
            loadCustomers();
            
            document.getElementById('customerForm').addEventListener('submit', function(e) {
                e.preventDefault();
                saveCustomer();
            });
        });

        function loadCustomers() {
            fetch('../api/customers.php')
                .then(response => response.json())
                .then(data => {
                    customers = data;
                    filteredCustomers = customers;
                    displayCustomers(customers);
                })
                .catch(error => {
                    console.error('Error loading customers:', error);
                    showAlert('Error loading customers', 'error');
                });
        }

        function displayCustomers(customersToShow) {
            const tbody = document.getElementById('customersTableBody');
            
            if (customersToShow.length === 0) {
                tbody.innerHTML = '<tr><td colspan="6">No customers found</td></tr>';
                return;
            }

            tbody.innerHTML = customersToShow.map(customer => `
                <tr>
                    <td>${customer.name}</td>
                    <td>${customer.email}</td>
                    <td>${customer.phone}</td>
                    <td>${customer.driver_license}</td>
                    <td>${new Date(customer.created_at).toLocaleDateString()}</td>
                    <td>
                        <button class="btn btn-sm btn-info" onclick="viewCustomerDetails(${customer.id})">View</button>
                        <button class="btn btn-sm btn-secondary" onclick="editCustomer(${customer.id})">Edit</button>
                        <button class="btn btn-sm btn-danger" onclick="deleteCustomer(${customer.id})">Delete</button>
                    </td>
                </tr>
            `).join('');
        }

        function showAddCustomerModal() {
            document.getElementById('modalTitle').textContent = 'Add New Customer';
            document.getElementById('customerForm').reset();
            document.getElementById('customerId').value = '';
            document.getElementById('customerModal').style.display = 'block';
        }

        function editCustomer(id) {
            const customer = customers.find(c => c.id == id);
            if (!customer) return;

            document.getElementById('modalTitle').textContent = 'Edit Customer';
            document.getElementById('customerId').value = customer.id;
            document.getElementById('name').value = customer.name;
            document.getElementById('email').value = customer.email;
            document.getElementById('phone').value = customer.phone;
            document.getElementById('driver_license').value = customer.driver_license;
            document.getElementById('customerModal').style.display = 'block';
        }

        function viewCustomerDetails(id) {
            document.getElementById('customerDetailsContent').innerHTML = 'Loading...';
            document.getElementById('customerDetailsModal').style.display = 'block';

            Promise.all([
                fetch(`../api/customers.php?id=${id}`).then(r => r.json()),
                fetch(`../api/customers.php?rental_history=${id}`).then(r => r.json()),
                fetch(`../api/customers.php?active_rentals=${id}`).then(r => r.json())
            ])
            .then(([customer, rentalHistory, activeRentals]) => {
                document.getElementById('customerDetailsContent').innerHTML = `
                    <div class="customer-details">
                        <h4>Customer Information</h4>
                        <p><strong>Name:</strong> ${customer.name}</p>
                        <p><strong>Email:</strong> ${customer.email}</p>
                        <p><strong>Phone:</strong> ${customer.phone}</p>
                        <p><strong>Driver License:</strong> ${customer.driver_license}</p>
                        <p><strong>Member Since:</strong> ${new Date(customer.created_at).toLocaleDateString()}</p>
                        
                        <h4>Active Rentals (${activeRentals.length})</h4>
                        ${activeRentals.length > 0 ? `
                            <table class="details-table">
                                <thead>
                                    <tr><th>Car</th><th>Rental Date</th><th>Return Date</th><th>Amount</th></tr>
                                </thead>
                                <tbody>
                                    ${activeRentals.map(rental => `
                                        <tr>
                                            <td>${rental.year} ${rental.make} ${rental.model}</td>
                                            <td>${rental.rental_date}</td>
                                            <td>${rental.return_date}</td>
                                            <td>₱${parseFloat(rental.total_amount).toFixed(2)}</td>
                                        </tr>
                                    `).join('')}
                                </tbody>
                            </table>
                        ` : '<p>No active rentals</p>'}
                        
                        <h4>Rental History (${rentalHistory.length} total)</h4>
                        ${rentalHistory.length > 0 ? `
                            <table class="details-table">
                                <thead>
                                    <tr><th>Car</th><th>Rental Date</th><th>Return Date</th><th>Status</th><th>Amount</th></tr>
                                </thead>
                                <tbody>
                                    ${rentalHistory.slice(0, 5).map(rental => `
                                        <tr>
                                            <td>${rental.year} ${rental.make} ${rental.model}</td>
                                            <td>${rental.rental_date}</td>
                                            <td>${rental.return_date}</td>
                                            <td><span class="status-badge status-${rental.status}">${rental.status}</span></td>
                                            <td>₱${parseFloat(rental.total_amount).toFixed(2)}</td>
                                        </tr>
                                    `).join('')}
                                </tbody>
                            </table>
                            ${rentalHistory.length > 5 ? '<p><em>Showing 5 most recent rentals</em></p>' : ''}
                        ` : '<p>No rental history</p>'}
                    </div>
                `;
            })
            .catch(error => {
                console.error('Error loading customer details:', error);
                document.getElementById('customerDetailsContent').innerHTML = 'Error loading customer details';
            });
        }

        function closeCustomerModal() {
            document.getElementById('customerModal').style.display = 'none';
        }

        function closeCustomerDetailsModal() {
            document.getElementById('customerDetailsModal').style.display = 'none';
        }

        function saveCustomer() {
            const formData = new FormData(document.getElementById('customerForm'));
            const customerData = Object.fromEntries(formData);
            const customerId = document.getElementById('customerId').value;

            const url = customerId ? `../api/customers.php?id=${customerId}` : '../api/customers.php';
            const method = customerId ? 'PUT' : 'POST';

            fetch(url, {
                method: method,
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify(customerData)
            })
            .then(response => response.json())
            .then(data => {
                if (data.errors) {
                    showAlert(data.errors.join(', '), 'error');
                } else {
                    showAlert(data.message, 'success');
                    closeCustomerModal();
                    loadCustomers();
                }
            })
            .catch(error => {
                console.error('Error saving customer:', error);
                showAlert('Error saving customer', 'error');
            });
        }

        function deleteCustomer(id) {
            if (!confirm('Are you sure you want to delete this customer?')) return;

            fetch(`../api/customers.php?id=${id}`, {
                method: 'DELETE'
            })
            .then(response => response.json())
            .then(data => {
                if (data.error) {
                    showAlert(data.error, 'error');
                } else {
                    showAlert(data.message, 'success');
                    loadCustomers();
                }
            })
            .catch(error => {
                console.error('Error deleting customer:', error);
                showAlert('Error deleting customer', 'error');
            });
        }

        function searchCustomers() {
            const searchTerm = document.getElementById('searchInput').value.toLowerCase();
            filteredCustomers = customers.filter(customer => 
                customer.name.toLowerCase().includes(searchTerm) ||
                customer.email.toLowerCase().includes(searchTerm) ||
                customer.phone.toLowerCase().includes(searchTerm) ||
                customer.driver_license.toLowerCase().includes(searchTerm)
            );
            displayCustomers(filteredCustomers);
        }

        window.onclick = function(event) {
            const customerModal = document.getElementById('customerModal');
            const detailsModal = document.getElementById('customerDetailsModal');
            if (event.target === customerModal) {
                closeCustomerModal();
            } else if (event.target === detailsModal) {
                closeCustomerDetailsModal();
            }
        }
    </script>
</body>
</html>