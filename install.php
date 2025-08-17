<?php
/**
 * Car Rental System - Installation Script
 * This script sets up the database and initial configuration
 */

echo "<h1>🚗 Car Rental System - Installation</h1>\n";

// Check PHP version
if (version_compare(PHP_VERSION, '8.0.0') < 0) {
    die("<p style='color: red;'>❌ PHP 8.0 or higher is required. Current version: " . PHP_VERSION . "</p>");
}

// Check SQLite extension
if (!extension_loaded('sqlite3')) {
    die("<p style='color: red;'>❌ SQLite3 extension is required but not installed.</p>");
}

echo "<p>✅ PHP Version: " . PHP_VERSION . "</p>";
echo "<p>✅ SQLite3 extension is loaded</p>";

// Create database directory if it doesn't exist
$dbDir = __DIR__ . '/database';
if (!is_dir($dbDir)) {
    mkdir($dbDir, 0755, true);
    echo "<p>✅ Created database directory</p>";
}

// Check if database exists
$dbFile = $dbDir . '/car_rental.db';
$dbExists = file_exists($dbFile);

if ($dbExists) {
    echo "<p>ℹ️ Database already exists at: " . $dbFile . "</p>";
    echo "<p>Do you want to recreate it? <a href='?recreate=1'>Yes, recreate database</a></p>";
    
    if (!isset($_GET['recreate'])) {
        echo "<p>✅ Installation appears to be complete!</p>";
        echo "<p><a href='index.php'>🚀 Go to Car Rental System</a></p>";
        exit;
    }
}

// Create/recreate database
try {
    if ($dbExists && isset($_GET['recreate'])) {
        unlink($dbFile);
        echo "<p>🗑️ Removed existing database</p>";
    }
    
    // Read schema file
    $schemaFile = $dbDir . '/schema.sql';
    if (!file_exists($schemaFile)) {
        die("<p style='color: red;'>❌ Schema file not found: " . $schemaFile . "</p>");
    }
    
    $schema = file_get_contents($schemaFile);
    
    // Create database and execute schema
    $pdo = new PDO("sqlite:" . $dbFile);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    // Execute schema
    $pdo->exec($schema);
    
    echo "<p>✅ Database created successfully at: " . $dbFile . "</p>";
    
    // Set permissions
    chmod($dbFile, 0664);
    chmod($dbDir, 0755);
    
    echo "<p>✅ Set database permissions</p>";
    
    // Test database connection
    require_once __DIR__ . '/config/database.php';
    $db = Database::getInstance();
    $cars = $db->fetchAll("SELECT COUNT(*) as count FROM cars");
    
    echo "<p>✅ Database connection test successful</p>";
    echo "<p>✅ Sample data loaded: " . $cars[0]['count'] . " cars</p>";
    
    echo "<h2>🎉 Installation Complete!</h2>";
    echo "<p><strong>Your Car Rental System is ready to use.</strong></p>";
    echo "<p><a href='index.php' style='background: #007bff; color: white; padding: 10px 20px; text-decoration: none; border-radius: 5px;'>🚀 Launch Car Rental System</a></p>";
    
    echo "<h3>📋 Next Steps:</h3>";
    echo "<ul>";
    echo "<li>✅ Database is set up with sample data</li>";
    echo "<li>✅ You can start adding cars and customers</li>";
    echo "<li>✅ Process your first rental transaction</li>";
    echo "<li>✅ View reports and analytics</li>";
    echo "</ul>";
    
    echo "<h3>🔧 System Information:</h3>";
    echo "<ul>";
    echo "<li><strong>Database:</strong> " . $dbFile . "</li>";
    echo "<li><strong>PHP Version:</strong> " . PHP_VERSION . "</li>";
    echo "<li><strong>Currency:</strong> Philippine Peso (₱)</li>";
    echo "<li><strong>Sample Cars:</strong> 5 vehicles loaded</li>";
    echo "<li><strong>Sample Customers:</strong> 3 customers loaded</li>";
    echo "</ul>";
    
    // Clean up - remove this installer for security
    echo "<p><em>For security, you may want to delete this install.php file after installation.</em></p>";
    
} catch (Exception $e) {
    echo "<p style='color: red;'>❌ Installation failed: " . $e->getMessage() . "</p>";
    echo "<p>Please check file permissions and try again.</p>";
}
?>