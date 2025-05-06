<?php
// Database Configuration (adjust as necessary)
$host = "127.0.0.1"; // Or your database host
$username = "root";  // Or your database username
$password = "";  // Or your database password
$database = "agritruck"; // Or your database name

// Establish Database Connection
$conn = new mysqli($host, $username, $password, $database);

// Check Connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// SQL Queries (from agritruck.sql)
$sql_queries = [
    "CREATE TABLE IF NOT EXISTS `consumer` (
        `ConsumerId` int(11) NOT NULL PRIMARY KEY,
        `ConsumerName` varchar(100) DEFAULT NULL,
        `emailID` varchar(100) DEFAULT NULL
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;",

    "CREATE TABLE IF NOT EXISTS `consumer_demand` (
        `id` int(11) NOT NULL PRIMARY KEY AUTO_INCREMENT,
        `CropId` int(11) NOT NULL,
        `ConsumptionRate` varchar(20) NOT NULL,
        `DemandValue` decimal(5,2) NOT NULL,
        `DateRecorded` timestamp NOT NULL DEFAULT current_timestamp(),
        FOREIGN KEY (`CropId`) REFERENCES `crop`(`CropId`) ON DELETE CASCADE ON UPDATE CASCADE
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;",

    "INSERT IGNORE INTO `consumer_demand` (`CropId`, `ConsumptionRate`, `DemandValue`, `DateRecorded`) VALUES
        (101, 'Low', 0.50, '2025-05-03 12:20:10'),
        (105, 'Medium', 2.00, '2025-05-03 12:20:17'),
        (109, 'Low', 1.00, '2025-05-03 12:20:27'),
        (108, 'High', 3.50, '2025-05-03 12:20:34'),
        (102, 'Medium', 1.75, '2025-05-03 12:20:41'),
        (104, 'Low', 0.75, '2025-05-03 12:20:48'),
        (110, 'High', 4.00, '2025-05-03 12:20:55'),
        (106, 'Medium', 2.25, '2025-05-03 12:21:02'),
        (103, 'Low', 1.25, '2025-05-03 12:21:09'),
        (107, 'High', 3.75, '2025-05-03 12:21:16');",

    "CREATE TABLE IF NOT EXISTS `crop` (
        `CropId` int(11) NOT NULL PRIMARY KEY,
        `CropName` varchar(100) DEFAULT NULL,
        `CropType` varchar(50) DEFAULT NULL
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;",

    "INSERT IGNORE INTO `crop` (`CropId`, `CropName`, `CropType`) VALUES
        (101, 'Wheat', 'Grain'),
        (102, 'Rice', 'Grain'),
        (103, 'Corn', 'Grain'),
        (104, 'Soybean', 'Oilseed'),
        (105, 'Cotton', 'Fiber'),
        (106, 'Sugarcane', 'Sugar'),
        (107, 'Coffee', 'Beverage'),
        (108, 'Tea', 'Beverage'),
        (109, 'Banana', 'Fruit'),
        (110, 'Apple', 'Fruit');",

    "CREATE TABLE IF NOT EXISTS `farmer` (
        `FarmerId` int(11) NOT NULL PRIMARY KEY,
        `FarmerName` varchar(100) DEFAULT NULL,
        `Location` varchar(100) DEFAULT NULL,
        `ContactNo` varchar(20) DEFAULT NULL
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;",

    "CREATE TABLE IF NOT EXISTS `farmer_crop` (
        `RelationId` int(11) NOT NULL PRIMARY KEY AUTO_INCREMENT,
        `FarmerId` int(11) DEFAULT NULL,
        `CropId` int(11) DEFAULT NULL,
        `Acreage` decimal(10,2) DEFAULT NULL,
        `Yield` decimal(10,2) DEFAULT NULL,
        FOREIGN KEY (`FarmerId`) REFERENCES `farmer`(`FarmerId`) ON DELETE CASCADE ON UPDATE CASCADE,
        FOREIGN KEY (`CropId`) REFERENCES `crop`(`CropId`) ON DELETE CASCADE ON UPDATE CASCADE
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;",

    "CREATE TABLE IF NOT EXISTS `fertilizer` (
        `FertilizerId` int(11) NOT NULL PRIMARY KEY,
        `FertilizerName` varchar(100) DEFAULT NULL,
        `ChemicalComposition` varchar(255) DEFAULT NULL,
        `Cost` decimal(10,2) DEFAULT NULL
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;",

    "CREATE TABLE IF NOT EXISTS `inventory_manager` (
        `StaffId` int(11) NOT NULL PRIMARY KEY,
        `StaffName` varchar(100) DEFAULT NULL,
        `email` varchar(100) DEFAULT NULL
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;",

    "CREATE TABLE IF NOT EXISTS `order` (
        `OrderId` int(11) NOT NULL PRIMARY KEY,
        `CustomerId` int(11) DEFAULT NULL,
        `OrderDate` timestamp NOT NULL DEFAULT current_timestamp(),
        `TotalAmount` decimal(10,2) DEFAULT NULL,
        FOREIGN KEY (`CustomerId`) REFERENCES `consumer`(`ConsumerId`) ON DELETE CASCADE ON UPDATE CASCADE
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;",

    "CREATE TABLE IF NOT EXISTS `order_line` (
        `LineId` int(11) NOT NULL PRIMARY KEY AUTO_INCREMENT,
        `OrderId` int(11) DEFAULT NULL,
        `VendorId` int(11) DEFAULT NULL,
        `CropId` int(11) DEFAULT NULL,
        `Quantity` int(11) DEFAULT NULL,
        `UnitPrice` decimal(10,2) DEFAULT NULL,
        FOREIGN KEY (`OrderId`) REFERENCES `order`(`OrderId`) ON DELETE CASCADE ON UPDATE CASCADE,
        FOREIGN KEY (`VendorId`) REFERENCES `vendor`(`VendorId`) ON DELETE CASCADE ON UPDATE CASCADE,
        FOREIGN KEY (`CropId`) REFERENCES `crop`(`CropId`) ON DELETE CASCADE ON UPDATE CASCADE
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;",

    "CREATE TABLE IF NOT EXISTS `pesticide` (
        `PesticideId` int(11) NOT NULL PRIMARY KEY,
        `PesticideName` varchar(100) DEFAULT NULL,
        `ActiveIngredient` varchar(100) DEFAULT NULL,
        `Cost` decimal(10,2) DEFAULT NULL
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;",

    "CREATE TABLE IF NOT EXISTS `price_elasticity` (
        `ElasticityId` int(11) NOT NULL PRIMARY KEY AUTO_INCREMENT,
        `CropId` int(11) DEFAULT NULL,
        `ElasticityValue` decimal(5,2) DEFAULT NULL,
        `DateCalculated` timestamp NOT NULL DEFAULT current_timestamp(),
        FOREIGN KEY (`CropId`) REFERENCES `crop`(`CropId`) ON DELETE CASCADE ON UPDATE CASCADE
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;",

    "CREATE TABLE IF NOT EXISTS `production_cost` (
        `CostId` int(11) NOT NULL PRIMARY KEY AUTO_INCREMENT,
        `CropId` int(11) DEFAULT NULL,
        `LaborCost` decimal(10,2) DEFAULT NULL,
        `SeedCost` decimal(10,2) DEFAULT NULL,
        `FertilizerCost` decimal(10,2) DEFAULT NULL,
        `PesticideCost` decimal(10,2) DEFAULT NULL,
        `OtherCosts` decimal(10,2) DEFAULT NULL,
        FOREIGN KEY (`CropId`) REFERENCES `crop`(`CropId`) ON DELETE CASCADE ON UPDATE CASCADE
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;",

    "CREATE TABLE IF NOT EXISTS `transport` (
        `TransportId` int(11) NOT NULL PRIMARY KEY AUTO_INCREMENT,
        `CropId` int(11) DEFAULT NULL,
        `VehicleType` varchar(50) DEFAULT NULL,
        `Capacity` varchar(50) DEFAULT NULL,
        `CostPerMile` decimal(10,2) DEFAULT NULL,
        `StaffId` int(11) DEFAULT NULL,
        FOREIGN KEY (`CropId`) REFERENCES `crop`(`CropId`) ON DELETE CASCADE ON UPDATE CASCADE,
        FOREIGN KEY (`StaffId`) REFERENCES `inventory_manager`(`StaffId`) ON DELETE CASCADE
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;",

    "CREATE TABLE IF NOT EXISTS `vendor` (
        `VendorId` int(11) NOT NULL PRIMARY KEY,
        `VendorName` varchar(100) DEFAULT NULL,
        `Location` varchar(100) DEFAULT NULL
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;",

    "CREATE TABLE IF NOT EXISTS `vendor_contact` (
        `ContactId` int(11) NOT NULL PRIMARY KEY AUTO_INCREMENT,
        `VendorId` int(11) DEFAULT NULL,
        `ContactPerson` varchar(100) DEFAULT NULL,
        `ContactNo` varchar(20) DEFAULT NULL,
        FOREIGN KEY (`VendorId`) REFERENCES `vendor`(`VendorId`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;",

    "CREATE TABLE IF NOT EXISTS `warehouse` (
        `WarehouseId` int(11) NOT NULL PRIMARY KEY AUTO_INCREMENT,
        `Location` varchar(100) DEFAULT NULL,
        `Capacity` varchar(50) DEFAULT NULL,
        `CropId` int(11) DEFAULT NULL,
        FOREIGN KEY (`CropId`) REFERENCES `crop`(`CropId`) ON DELETE SET NULL
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;"
];

// Execute SQL Queries
foreach ($sql_queries as $sql) {
    if ($conn->query($sql) === TRUE) {
        echo "Query executed successfully<br>";
    } else {
        echo "Error executing query: " . $conn->error . "<br>";
    }
}


?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Crop Supply & Demand Analysis</title>
    <style>
         /* style.css */
        body {
            font-family: sans-serif;
            margin: 0;
            background-color: #f4f4f4;
            display: flex;
            justify-content: center;
            align-items: center;
            min-height: 100vh;
        }

        .container {
            display: flex;
            width: 90%;
            max-width: 1200px;
            background-color: #fff;
            border-radius: 8px;
            box-shadow: 0 0 20px rgba(0, 0, 0, 0.1);
            overflow: hidden;
        }

        aside {
            background-color: #333;
            color: #fff;
            padding: 20px;
            width: 250px;
            display: flex;
            flex-direction: column;
        }

        aside header h1 {
            margin-top: 0;
            margin-bottom: 20px;
            text-align: center;
        }

        aside nav ul {
            list-style: none;
            padding: 0;
            margin: 0;
        }

        aside nav ul li a {
            display: block;
            color: #ddd;
            text-decoration: none;
            padding: 10px;
            border-bottom: 1px solid #555;
            transition: background-color 0.3s ease;
        }

        aside nav ul li a:hover {
            background-color: #555;
        }

        aside nav ul li ul {
            background-color: #444;
        }

        aside nav ul li ul li a {
            padding-left: 20px;
            border-bottom: none;
        }

        main {
            flex: 1;
            padding: 20px;
        }

        section {
            margin-bottom: 20px;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 10px;
        }

        th, td {
            border: 1px solid #ddd;
            padding: 8px;
            text-align: left;
        }

        th {
            background-color: #f2f2f2;
        }

        input[type="number"],
        input[type="text"] {
            width: 100%;
            padding: 8px;
            margin: 4px 0;
            box-sizing: border-box;
        }

        button {
            padding: 8px 12px;
            margin: 5px;
            border: none;
            cursor: pointer;
            background-color: #009879;
            color: white;
            border-radius: 4px;
        }

        button:hover {
            background-color: #007963;
        }

        #add-row-btn {
            display: block;
            margin: 10px auto;
        }

        /* Forms */
        form div {
            margin-bottom: 10px;
        }
        form label {
            display: block;
            margin-bottom: 5px;
        }
        form input, form textarea, form select {
            width: 100%;
            padding: 8px;
            box-sizing: border-box;
            border: 1px solid #ccc;
            border-radius: 4px;
        }
        form button {
            background-color: #4caf50;
            color: white;
            padding: 10px 15px;
            border: none;
            border-radius: 4px;
            cursor: pointer;
        }
        form button:hover {
            background-color: #45a049;
        }
    </style>
</head>
<body>
    <div class="container">
        <aside>
            <header>
                <h1>Crop Researcher</h1>
            </header>
            <nav>
                <ul>
                    <li>
                        <a href="index.php">Dashboard</a>
                    </li>
                    <li>
                        <a href="#">History</a>
                        <ul>
                            <li><a href="yield_data.php">Yield Data</a></li>
                            <li><a href="acreage_data.php">Acreage Data</a></li>
                            <li><a href="cost_data.php">Cost Data</a></li>
                        </ul>
                    </li>
                    <li>
                        <a href="#">Farmer Option</a>
                        <ul>
                            <li><a href="farmer_consultation.php">Consultation</a></li>
                            <li><a href="review_data.php">Review Crop Data</a></li>
                            <li><a href="aid_request.php">Aid Request</a></li>
                        </ul>
                    </li>
                    <li><a href="charts_forecast.php">Charts, Forecast & Analysis</a></li>
                    <li class="logout"><a href="logout.php">Log Out</a></li>
                </ul>
            </nav>
            <footer>
                <p>&copy; 2025</p>
            </footer>
        </aside>
        <main>
            <section id="dashboard">
                <h2>Welcome to the Dashboard</h2>
                <p>Select options from the left navigation to view data and access farmer tools.</p>
            </section>
        </main>
    </div>