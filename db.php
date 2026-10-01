<?php
$host = "localhost";
$user = "root";
$pass = "";
$dbname = "assessment_db";

// 1. Connect to MySQL server without selecting a database first
$conn = mysqli_connect($host, $user,$pass);

if (!$conn) {
    die("Database connection failed: " . mysqli_connect_error());
}

// 2. Create database if it doesn't exist
$sql_db = "CREATE DATABASE IF NOT EXISTS `$dbname` DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci";
if (!mysqli_query($conn,$sql_db)) {
    die("Error creating database: " . mysqli_error($conn));
}

// 3. Select the database
mysqli_select_db($conn,$dbname);

// 4. Create tables automatically if they don't exist
$tables = [
    "clients" => "CREATE TABLE IF NOT EXISTS `clients` (
      `client_id` int(11) NOT NULL AUTO_INCREMENT,
      `full_name` varchar(150) NOT NULL,
      `email` varchar(150) NOT NULL,
      `phone` varchar(50) DEFAULT NULL,
      `address` varchar(255) DEFAULT NULL,
      `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
      PRIMARY KEY (`client_id`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;",

    "services" => "CREATE TABLE IF NOT EXISTS `services` (
      `service_id` int(11) NOT NULL AUTO_INCREMENT,
      `service_name` varchar(150) NOT NULL,
      `description` text DEFAULT NULL,
      `hourly_rate` decimal(10,2) NOT NULL DEFAULT 0.00,
      `is_active` tinyint(1) NOT NULL DEFAULT 1,
      `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
      PRIMARY KEY (`service_id`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;",

    "tools" => "CREATE TABLE IF NOT EXISTS `tools` (
      `tool_id` int(11) NOT NULL AUTO_INCREMENT,
      `tool_name` varchar(150) NOT NULL,
      `quantity_total` int(11) NOT NULL DEFAULT 0,
      `quantity_available` int(11) NOT NULL DEFAULT 0,
      PRIMARY KEY (`tool_id`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;",

    "bookings" => "CREATE TABLE IF NOT EXISTS `bookings` (
      `booking_id` int(11) NOT NULL AUTO_INCREMENT,
      `client_id` int(11) NOT NULL,
      `service_id` int(11) NOT NULL,
      `booking_date` date NOT NULL,
      `hours` int(11) NOT NULL DEFAULT 1,
      `hourly_rate_snapshot` decimal(10,2) NOT NULL DEFAULT 0.00,
      `total_cost` decimal(10,2) NOT NULL DEFAULT 0.00,
      `status` varchar(30) NOT NULL DEFAULT 'PENDING',
      `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
      PRIMARY KEY (`booking_id`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;",

    "booking_tools" => "CREATE TABLE IF NOT EXISTS `booking_tools` (
      `booking_tool_id` int(11) NOT NULL AUTO_INCREMENT,
      `booking_id` int(11) NOT NULL,
      `tool_id` int(11) NOT NULL,
      `qty_used` int(11) NOT NULL DEFAULT 1,
      `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
      PRIMARY KEY (`booking_tool_id`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;",

    "payments" => "CREATE TABLE IF NOT EXISTS `payments` (
      `payment_id` int(11) NOT NULL AUTO_INCREMENT,
      `booking_id` int(11) NOT NULL,
      `amount_paid` decimal(10,2) NOT NULL DEFAULT 0.00,
      `method` varchar(50) NOT NULL DEFAULT 'CASH',
      `payment_date` datetime NOT NULL DEFAULT current_timestamp(),
      PRIMARY KEY (`payment_id`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;",

    "users" => "CREATE TABLE IF NOT EXISTS `users` (
      `user_id` int(11) NOT NULL AUTO_INCREMENT,
      `username` varchar(100) NOT NULL,
      `password` varchar(255) NOT NULL,
      `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
      PRIMARY KEY (`user_id`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;"
];

foreach ($tables as $table_name =>$sql_create) {
    mysqli_query($conn,$sql_create);
}

// 5. Insert initial seed data if tables are empty
$check_services = mysqli_query($conn, "SELECT COUNT(*) as count FROM services");
$row_services = mysqli_fetch_assoc($check_services);
if ($row_services['count'] == 0) {
    mysqli_query($conn, "INSERT INTO `services` (`service_name`, `description`, `hourly_rate`, `is_active`) VALUES
    ('Plumbing', 'Leak repairs and installation', 500.00, 1),
    ('Electrical', 'Wiring and troubleshooting', 600.00, 1),
    ('Carpentry Works', 'Wood repairs and minor builds', 450.00, 1)");
}

$check_tools = mysqli_query($conn, "SELECT COUNT(*) as count FROM tools");
$row_tools = mysqli_fetch_assoc($check_tools);
if ($row_tools['count'] == 0) {
    mysqli_query($conn, "INSERT INTO `tools` (`tool_name`, `quantity_total`, `quantity_available`) VALUES
    ('Power Drill', 5, 5),
    ('Hammer', 10, 10),
    ('Ladder', 3, 3)");
}

$check_users = mysqli_query($conn, "SELECT COUNT(*) as count FROM users");
$row_users = mysqli_fetch_assoc($check_users);
if ($row_users['count'] == 0) {$default_pass = password_hash('password123', PASSWORD_DEFAULT);
    $stmt = mysqli_prepare($conn, "INSERT INTO `users` (`username`, `password`) VALUES (?, ?)");
    $username = 'admin';
    mysqli_stmt_bind_param($stmt, "ss", $username,$default_pass);
    mysqli_stmt_execute($stmt);
}
?>