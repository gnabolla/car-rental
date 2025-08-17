# 🚗 Car Rental System

A comprehensive web-based car rental management system built with PHP, SQLite, and modern web technologies.

## 📋 Features

### 🚙 Car Management
- Add, edit, and delete cars
- Track car availability (Available, Rented, Maintenance)
- Search and filter cars
- Daily rate management in Philippine Peso (₱)

### 👥 Customer Management
- Customer registration and profile management
- Rental history tracking
- Active rentals overview
- Email and driver license validation

### 📝 Rental Transactions
- Create new rentals with availability checks
- Process car returns with automatic calculations
- Late fee calculations for overdue returns
- Real-time status updates

### 📊 Reports & Analytics
- Available cars report
- Overdue rentals report
- Income reports with date filtering
- Dashboard with key business metrics

## 🛠️ Technology Stack

- **Backend**: PHP 8.4+ with Object-Oriented Programming
- **Database**: SQLite (lightweight, serverless)
- **Frontend**: HTML5, CSS3, Vanilla JavaScript
- **Web Server**: Nginx compatible
- **Currency**: Philippine Peso (₱)

## 📁 Project Structure

```
car-rental/
├── api/                    # RESTful API endpoints
│   ├── cars.php           # Car CRUD operations
│   ├── customers.php      # Customer management
│   ├── rentals.php        # Rental transactions
│   └── reports.php        # Business reports
├── assets/
│   ├── css/
│   │   └── style.css      # Main stylesheet
│   └── js/
│       └── app.js         # JavaScript utilities
├── classes/               # PHP model classes
│   ├── Car.php           # Car model
│   ├── Customer.php      # Customer model
│   └── Rental.php        # Rental model
├── config/
│   └── database.php      # Database connection
├── database/
│   ├── schema.sql        # Database schema
│   └── car_rental.db     # SQLite database (auto-created)
├── pages/                # Frontend pages
│   ├── cars.php          # Car management interface
│   ├── customers.php     # Customer management
│   ├── rentals.php       # Rental transactions
│   └── reports.php       # Reports dashboard
├── tests/                # Unit tests
│   ├── CarTest.php       # Car class tests
│   ├── CustomerTest.php  # Customer class tests
│   ├── RentalTest.php    # Rental class tests
│   └── run_tests.php     # Test runner
└── index.php             # Main dashboard
```

## 🚀 Quick Start

### Prerequisites
- PHP 8.4 or higher
- SQLite extension enabled
- Web server (Nginx/Apache)

### Installation

1. **Clone the repository**
   ```bash
   git clone https://github.com/gnabolla/car-rental.git
   cd car-rental
   ```

2. **Set up the database**
   ```bash
   sqlite3 database/car_rental.db < database/schema.sql
   ```

3. **Configure web server**
   - Point your web server document root to the project directory
   - Ensure PHP has read/write permissions to the `database/` directory

4. **Access the application**
   ```
   http://your-domain/car-rental/
   ```

## 📖 Usage

### Dashboard
Access the main dashboard at `/` to view:
- Total cars, available cars, customers
- Active and overdue rentals
- Monthly income statistics
- Recent rental transactions

### Managing Cars
1. Navigate to **Cars** section
2. Click **"Add New Car"** to register vehicles
3. Set daily rates in Philippine Peso (₱)
4. Manage car status (Available/Rented/Maintenance)

### Managing Customers
1. Go to **Customers** section
2. Register new customers with required details
3. View customer rental history and active rentals
4. Edit customer information as needed

### Processing Rentals
1. Visit **Rentals** section
2. Click **"New Rental"** to create bookings
3. Select customer and available car
4. Set rental and return dates
5. Process returns with automatic late fee calculations

### Viewing Reports
Access comprehensive reports in the **Reports** section:
- **Available Cars**: Current inventory status
- **Overdue Rentals**: Late returns requiring attention
- **Income Report**: Financial analytics with date filters

## 🧪 Testing

Run the included unit tests:
```bash
php tests/run_tests.php
```

Tests cover:
- Car CRUD operations
- Customer management
- Rental transaction logic
- Database operations

## 💰 Currency Configuration

The system is configured for Philippine Peso (₱). Sample pricing:
- Economy cars: ₱1,800 - ₱2,500/day
- Luxury cars: ₱4,000 - ₱4,500/day

## 🔧 Configuration

### Database Configuration
Database settings are in `config/database.php`. The system uses SQLite by default for simplicity and portability.

### Customization
- Modify CSS in `assets/css/style.css` for styling changes
- Update JavaScript in `assets/js/app.js` for functionality enhancements
- Adjust currency formatting in the `formatCurrency()` function

## 🤝 Contributing

1. Fork the repository
2. Create a feature branch (`git checkout -b feature/amazing-feature`)
3. Commit your changes (`git commit -m 'Add amazing feature'`)
4. Push to the branch (`git push origin feature/amazing-feature`)
5. Open a Pull Request

## 📄 License

This project is open source and available under the [MIT License](LICENSE).

## 🆘 Support

For support, please open an issue on GitHub or contact the maintainers.

---

**Built with ❤️ for efficient car rental management**