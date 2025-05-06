-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: May 06, 2025 at 10:03 PM
-- Server version: 10.4.32-MariaDB
-- PHP Version: 8.2.12

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `agritruck`
--

-- --------------------------------------------------------

--
-- Table structure for table `consumer`
--

CREATE TABLE `consumer` (
  `ConsumerId` int(11) NOT NULL,
  `ConsumerName` varchar(100) DEFAULT NULL,
  `emailID` varchar(100) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `consumer`
--

INSERT INTO `consumer` (`ConsumerId`, `ConsumerName`, `emailID`) VALUES
(101, 'Rafiq Hossain', 'rafiq.h@gmail.com'),
(102, 'Nusrat Jahan', 'nusrat.j@gmail.com'),
(103, 'Tanvir Alam', 'tanvir.alam@yahoo.com'),
(104, 'Mouree Akter', 'mouree.ak@yahoo.com'),
(105, 'Shahidul Islam', 'shahidul.i@gmail.com'),
(106, 'Farzana Sultana', 'farzana.sultana@gmail.com'),
(107, 'Habibur Rahman', 'habibur.rahman@gmail.com'),
(108, 'Rumana Kabir', 'rumana.kabir@hotmail.com'),
(109, 'Shamim Chowdhury', 'shamim.c@yahoo.com'),
(110, 'Tania Rahman', 'tania.r@gmail.com');

-- --------------------------------------------------------

--
-- Table structure for table `consumer_demand`
--

CREATE TABLE `consumer_demand` (
  `id` int(11) NOT NULL,
  `CropId` int(11) NOT NULL,
  `ConsumptionRate` varchar(20) NOT NULL,
  `DemandValue` decimal(5,2) NOT NULL,
  `DateRecorded` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `consumer_demand`
--

INSERT INTO `consumer_demand` (`id`, `CropId`, `ConsumptionRate`, `DemandValue`, `DateRecorded`) VALUES
(16, 101, 'Low', 0.50, '2025-05-03 06:20:10'),
(17, 105, 'Medium', 2.00, '2025-05-03 06:20:17'),
(18, 109, 'Low', 1.00, '2025-05-03 06:20:27'),
(19, 108, 'High', 3.00, '2025-05-03 06:40:59'),
(20, 107, 'Medium', 2.00, '2025-05-03 06:41:07'),
(21, 111, 'Low', 1.00, '2025-05-03 06:41:17');

-- --------------------------------------------------------

--
-- Table structure for table `crop`
--

CREATE TABLE `crop` (
  `CropId` int(11) NOT NULL,
  `CropName` varchar(100) DEFAULT NULL,
  `price` float DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `crop`
--

INSERT INTO `crop` (`CropId`, `CropName`, `price`) VALUES
(101, 'rice', 200),
(102, 'wheat', 100),
(103, 'corn', 200),
(104, 'potato', 25),
(105, 'tomato', 60),
(106, 'onion', 60),
(107, 'carrot', 60),
(108, 'cucumber', 100),
(109, 'ginger', 220),
(110, 'spinach', 50),
(111, 'cabbage', 65);

-- --------------------------------------------------------

--
-- Table structure for table `crop_seasonality`
--

CREATE TABLE `crop_seasonality` (
  `CropId` int(11) DEFAULT NULL,
  `seasonality` varchar(50) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `crop_seasonality`
--

INSERT INTO `crop_seasonality` (`CropId`, `seasonality`) VALUES
(103, 'Grishsho, Borsha'),
(101, 'Borsha, Hemonto'),
(102, 'Sheet, Boshonto'),
(104, 'Sheet, Boshonto'),
(105, 'Grishsho, Borsha'),
(106, 'Sheet, Shorot'),
(107, 'Sheet, Boshonto'),
(108, 'Grishsho, Boshonto'),
(109, 'All seasons'),
(110, 'Sheet, Shorot'),
(111, 'Shorot, Hemonto');

-- --------------------------------------------------------

--
-- Table structure for table `crop_type`
--

CREATE TABLE `crop_type` (
  `CropId` int(11) DEFAULT NULL,
  `Type` varchar(50) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `crop_type`
--

INSERT INTO `crop_type` (`CropId`, `Type`) VALUES
(103, 'Cereal'),
(101, 'Cereal'),
(102, 'Cereal'),
(104, 'Vegetable'),
(105, 'Vegetable'),
(106, 'Vegetable'),
(107, 'Vegetable'),
(108, 'Vegetable'),
(109, 'Spice'),
(110, 'Leafy Green'),
(111, 'Vegetable');

-- --------------------------------------------------------

--
-- Table structure for table `crop_variety`
--

CREATE TABLE `crop_variety` (
  `CropId` int(11) DEFAULT NULL,
  `variety` varchar(100) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `crop_variety`
--

INSERT INTO `crop_variety` (`CropId`, `variety`) VALUES
(103, 'BARI Hybrid Maize-9'),
(103, 'NK40'),
(101, 'BRRI Dhan-28'),
(101, 'BRRI Dhan-29'),
(101, 'Miniket'),
(101, 'Nazirshail'),
(102, 'Shatabdi'),
(102, 'BARI Gom-26'),
(104, 'Granola'),
(104, 'Cardinal'),
(105, 'Ratan'),
(105, 'BARI Tomato-14'),
(106, 'Taherpuri'),
(106, 'BARI Piaz-1'),
(107, 'Deshi Carrot'),
(107, 'Nantes'),
(108, 'BARI Shosha-1'),
(108, 'Hybrid Shosha'),
(109, 'Deshi Ada'),
(109, 'Hybrid Ada'),
(110, 'Pui Shak'),
(110, 'Deshi Palong'),
(111, 'Green Express'),
(111, 'BARI Bandha Kopi-1');

-- --------------------------------------------------------

--
-- Table structure for table `delivery`
--

CREATE TABLE `delivery` (
  `deliveryId` varchar(50) NOT NULL,
  `WarehouseId` int(11) NOT NULL,
  `deliveryDate` date NOT NULL,
  `quantity` decimal(10,2) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `farmer`
--

CREATE TABLE `farmer` (
  `FarmerId` int(11) NOT NULL,
  `FarmerName` varchar(100) DEFAULT NULL,
  `Thana` varchar(100) DEFAULT NULL,
  `ZipCode` varchar(10) DEFAULT NULL,
  `City` varchar(100) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `farmer`
--

INSERT INTO `farmer` (`FarmerId`, `FarmerName`, `Thana`, `ZipCode`, `City`) VALUES
(1, 'Abdul Karim', 'Kishoreganj Sadar', '2300', 'Kishoreganj'),
(2, 'Selina Begum', 'Madaripur', '3800', 'Madaripur'),
(3, 'Shafiqul Islam', 'Mirpur', '1216', 'Dhaka'),
(4, 'Mohammad Ali', 'Chandpur', '4200', 'Chandpur'),
(5, 'Rina Akter', 'Mongla', '9310', 'Bagerhat');

-- --------------------------------------------------------

--
-- Table structure for table `farmer_contact_no`
--

CREATE TABLE `farmer_contact_no` (
  `FarmerId` int(11) DEFAULT NULL,
  `ContactNo` varchar(20) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `farmer_contact_no`
--

INSERT INTO `farmer_contact_no` (`FarmerId`, `ContactNo`) VALUES
(1, '01712345678'),
(2, '01823456789'),
(3, '01934567890'),
(4, '01645678901'),
(5, '01756789012');

-- --------------------------------------------------------

--
-- Table structure for table `farmer_harvest`
--

CREATE TABLE `farmer_harvest` (
  `harvestId` int(11) DEFAULT NULL,
  `FarmerId` int(11) DEFAULT NULL,
  `DateCompleted` date DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `farmer_recommendation`
--

CREATE TABLE `farmer_recommendation` (
  `recommendationId` int(11) NOT NULL,
  `FarmerId` int(11) DEFAULT NULL,
  `details` varchar(50) DEFAULT NULL,
  `date` date DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `farmer_recommendation`
--

INSERT INTO `farmer_recommendation` (`recommendationId`, `FarmerId`, `details`, `date`) VALUES
(0, 2, 'nnn', '2025-05-07'),
(1, 1, 'Use improved seeds for better yield.', '2025-05-01'),
(2, 2, 'Apply organic fertilizers to increase soil fertili', '2025-05-02'),
(3, 3, 'Install drip irrigation system to optimize water u', '2025-05-03'),
(5, 5, 'Use protective nets to reduce pest damage.', '2025-05-05');

-- --------------------------------------------------------

--
-- Table structure for table `harvest`
--

CREATE TABLE `harvest` (
  `harvestId` int(11) NOT NULL,
  `acreage` float DEFAULT NULL,
  `yields` float DEFAULT NULL,
  `harvestingCost` int(11) DEFAULT NULL,
  `WarehouseId` int(11) DEFAULT NULL,
  `VendorId` int(11) DEFAULT NULL,
  `harvestYear` year(4) NOT NULL,
  `harvestPrice` float DEFAULT NULL,
  `CropId` int(11) DEFAULT NULL,
  `CropName` varchar(100) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `harvest`
--

INSERT INTO `harvest` (`harvestId`, `acreage`, `yields`, `harvestingCost`, `WarehouseId`, `VendorId`, `harvestYear`, `harvestPrice`, `CropId`, `CropName`) VALUES
(1, 10.5, 500, 2000, 1, 201, '2025', 150, 101, 'rice'),
(2, 8.2, 400, 1800, 3, 202, '2025', 120, 103, 'corn'),
(3, 15, 700, 2500, 4, 203, '2025', 90, 102, 'wheat'),
(4, 5.5, 300, 1500, 1, 204, '2025', 200, 109, 'ginger'),
(5, 7, 350, 1700, 3, 202, '2025', 60, 105, 'tomato'),
(6, 12, 600, 2200, 4, 204, '2025', 60, 106, 'onion'),
(7, 9.5, 450, 1900, 1, 203, '2025', 60, 107, 'carrot'),
(8, 20, 1000, 3000, 1, 201, '2025', 25, 104, 'potato'),
(9, 6.3, 315, 1600, 3, 202, '2025', 100, 108, 'cucumber'),
(10, 4, 200, 1400, 4, 203, '2025', 220, 109, 'ginger'),
(11, 8.7, 435, 1750, 4, 204, '2025', 50, 110, 'spinach'),
(12, 11.2, 560, 2100, 1, 201, '2025', 65, 111, 'cabbage'),
(13, 18.5, 925, 2800, 3, 202, '2025', 100, 102, 'wheat'),
(14, 10.5, 500, 2000, 1, 201, '2023', 150, 101, 'rice'),
(15, 8.2, 400, 1800, 3, 202, '2024', 120, 103, 'corn'),
(16, 15, 700, 2500, 4, 203, '2023', 90, 102, 'wheat'),
(17, 5.5, 300, 1500, 1, 204, '2024', 200, 109, 'ginger'),
(18, 7, 350, 1700, 3, 202, '2025', 60, 105, 'tomato'),
(19, 12, 600, 2200, 4, 204, '2025', 60, 106, 'onion'),
(20, 9.5, 450, 1900, 1, 203, '2023', 60, 107, 'carrot'),
(21, 20, 1000, 3000, 1, 201, '2024', 25, 104, 'potato'),
(22, 6.3, 315, 1600, 3, 202, '2022', 100, 108, 'cucumber'),
(23, 4, 200, 1400, 4, 203, '2025', 220, 109, 'ginger'),
(24, 8.7, 435, 1750, 4, 204, '2023', 50, 110, 'spinach'),
(25, 11.2, 560, 2100, 1, 201, '2024', 65, 111, 'cabbage');

-- --------------------------------------------------------

--
-- Table structure for table `harvest_month`
--

CREATE TABLE `harvest_month` (
  `harvestId` int(11) DEFAULT NULL,
  `harvestMonth` varchar(20) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `inventory_manager`
--

CREATE TABLE `inventory_manager` (
  `StaffId` int(11) NOT NULL,
  `S_name` varchar(100) DEFAULT NULL,
  `emailId` varchar(100) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `inventory_manager_contactno`
--

CREATE TABLE `inventory_manager_contactno` (
  `StaffId` int(11) DEFAULT NULL,
  `S_contactNo` varchar(20) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `order_line`
--

CREATE TABLE `order_line` (
  `OrderId` int(11) DEFAULT NULL,
  `VendorId` int(11) DEFAULT NULL,
  `CropId` int(11) DEFAULT NULL,
  `quantity` int(11) DEFAULT NULL,
  `price` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `order_line`
--

INSERT INTO `order_line` (`OrderId`, `VendorId`, `CropId`, `quantity`, `price`) VALUES
(1, 201, 101, 50, 200),
(2, 202, 102, 30, 100),
(3, 203, 103, 40, 200),
(4, 204, 104, 60, 25),
(5, 201, 105, 35, 60),
(6, 202, 106, 45, 60),
(7, 203, 107, 20, 60),
(8, 204, 108, 25, 100),
(9, 201, 109, 15, 220),
(10, 202, 110, 55, 50),
(11, 203, 102, 50, 55),
(12, 201, 107, 25, 65),
(13, 204, 111, 40, 70),
(14, 202, 104, 30, 30),
(15, 203, 109, 10, 230),
(16, 201, 101, 60, 210),
(17, 204, 106, 45, 70),
(18, 202, 103, 35, 205),
(19, 203, 108, 20, 105),
(20, 201, 105, 55, 62);

-- --------------------------------------------------------

--
-- Table structure for table `order_t`
--

CREATE TABLE `order_t` (
  `OrderId` int(11) NOT NULL,
  `orderDate` date DEFAULT NULL,
  `time` time DEFAULT NULL,
  `ConsumerId` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `order_t`
--

INSERT INTO `order_t` (`OrderId`, `orderDate`, `time`, `ConsumerId`) VALUES
(1, '2024-01-01', '09:00:00', 101),
(2, '2024-01-02', '10:30:00', 102),
(3, '2024-01-03', '11:15:00', 103),
(4, '2024-01-04', '12:00:00', 104),
(5, '2024-01-05', '13:45:00', 105),
(6, '2024-01-06', '14:10:00', 106),
(7, '2024-01-07', '08:20:00', 107),
(8, '2024-01-08', '16:30:00', 108),
(9, '2024-01-09', '15:15:00', 109),
(10, '2024-01-10', '17:45:00', 110),
(11, '2024-05-01', '10:00:00', 105),
(12, '2024-05-03', '14:30:00', 101),
(13, '2024-05-05', '09:45:00', 108),
(14, '2024-05-07', '16:00:00', 103),
(15, '2024-05-09', '11:30:00', 106),
(16, '2024-05-11', '17:15:00', 102),
(17, '2024-05-13', '08:00:00', 109),
(18, '2024-05-15', '12:45:00', 104),
(19, '2024-05-17', '15:30:00', 107),
(20, '2024-05-19', '13:00:00', 110);

-- --------------------------------------------------------

--
-- Table structure for table `price_elasticity`
--

CREATE TABLE `price_elasticity` (
  `ElasticityId` int(11) NOT NULL,
  `CropId` int(11) DEFAULT NULL,
  `ElasticityValue` decimal(5,2) DEFAULT NULL,
  `DateRecorded` date DEFAULT NULL,
  `Source` varchar(100) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `price_elasticity`
--

INSERT INTO `price_elasticity` (`ElasticityId`, `CropId`, `ElasticityValue`, `DateRecorded`, `Source`) VALUES
(1, 108, -0.20, '2025-05-05', 'Manual Entry'),
(2, 107, -0.60, '2025-05-05', 'Manual Entry'),
(3, 109, -1.00, '2025-05-05', 'Manual Entry'),
(4, 101, -1.80, '2025-05-05', 'Manual Entry'),
(5, 110, -0.30, '2025-05-05', 'Manual Entry'),
(6, 106, -0.30, '2025-05-05', 'Manual Entry'),
(7, 111, -0.40, '2025-05-06', 'Manual Entry');

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` int(11) NOT NULL,
  `username` varchar(100) NOT NULL,
  `email` varchar(100) NOT NULL,
  `password` varchar(255) NOT NULL,
  `designation` enum('admin','crop_researcher','customer','farmer') NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `username`, `email`, `password`, `designation`, `created_at`, `updated_at`) VALUES
(1, 'tashrif', 'miyad@gmail.com', '$2y$10$ILr3FHk.TxcbV3MEHWKg.eSmSr2gY4jOoMXLR8uTJ3mOOmroSBDDO', 'admin', '2025-05-06 19:45:50', '2025-05-06 19:45:50');

-- --------------------------------------------------------

--
-- Table structure for table `vendor`
--

CREATE TABLE `vendor` (
  `VendorId` int(11) NOT NULL,
  `vendorName` varchar(100) DEFAULT NULL,
  `thana` varchar(100) DEFAULT NULL,
  `zipcode` varchar(10) DEFAULT NULL,
  `city` varchar(100) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `vendor`
--

INSERT INTO `vendor` (`VendorId`, `vendorName`, `thana`, `zipcode`, `city`) VALUES
(201, 'Mizan Agro Traders', 'Dhanmondi', '1209', 'Dhaka'),
(202, 'GreenGrow Supply', 'Panchlaish', '4000', 'Chattogram'),
(203, 'Fertile Harvest Co.', 'Shibganj', '5800', 'Bogura'),
(204, 'AgroMart Limited', 'Kushtia Sadar', '7000', 'Kushtia');

-- --------------------------------------------------------

--
-- Table structure for table `vendor_contact`
--

CREATE TABLE `vendor_contact` (
  `VendorId` int(11) DEFAULT NULL,
  `contactNo` varchar(20) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `warehouse`
--

CREATE TABLE `warehouse` (
  `warehouseId` int(11) NOT NULL,
  `warehouseName` varchar(50) DEFAULT NULL,
  `stage` varchar(50) DEFAULT NULL,
  `CropId` int(11) DEFAULT NULL,
  `CropName` varchar(100) DEFAULT NULL,
  `date` date DEFAULT NULL,
  `quantity` int(11) DEFAULT NULL,
  `details` varchar(255) DEFAULT NULL,
  `storage_id` varchar(50) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `warehouse`
--

INSERT INTO `warehouse` (`warehouseId`, `warehouseName`, `stage`, `CropId`, `CropName`, `date`, `quantity`, `details`, `storage_id`) VALUES
(1, NULL, 'logistics', 103, 'corn', '2025-05-12', 10, '0', NULL),
(3, NULL, 'logistics', 103, 'corn', '2025-05-14', 30, '0', NULL),
(4, NULL, 'inventory', 102, 'wheat', '2025-05-21', 30, '0', NULL),
(5, NULL, 'logistics', 107, 'carrot', '2025-05-28', 4, '0', NULL);

--
-- Indexes for dumped tables
--

--
-- Indexes for table `consumer`
--
ALTER TABLE `consumer`
  ADD PRIMARY KEY (`ConsumerId`);

--
-- Indexes for table `crop`
--
ALTER TABLE `crop`
  ADD PRIMARY KEY (`CropId`);

--
-- Indexes for table `crop_seasonality`
--
ALTER TABLE `crop_seasonality`
  ADD KEY `crop_seasonality_ibfk_1` (`CropId`);

--
-- Indexes for table `crop_type`
--
ALTER TABLE `crop_type`
  ADD KEY `crop_type_ibfk_1` (`CropId`);

--
-- Indexes for table `crop_variety`
--
ALTER TABLE `crop_variety`
  ADD KEY `crop_variety_ibfk_1` (`CropId`);

--
-- Indexes for table `delivery`
--
ALTER TABLE `delivery`
  ADD PRIMARY KEY (`deliveryId`),
  ADD KEY `delivery_ibfk_1` (`WarehouseId`);

--
-- Indexes for table `farmer`
--
ALTER TABLE `farmer`
  ADD PRIMARY KEY (`FarmerId`);

--
-- Indexes for table `farmer_contact_no`
--
ALTER TABLE `farmer_contact_no`
  ADD KEY `farmer_contact_no_ibfk_1` (`FarmerId`);

--
-- Indexes for table `farmer_harvest`
--
ALTER TABLE `farmer_harvest`
  ADD KEY `farmer_harvest_FK1` (`harvestId`),
  ADD KEY `farmer_harvest_FK2` (`FarmerId`);

--
-- Indexes for table `farmer_recommendation`
--
ALTER TABLE `farmer_recommendation`
  ADD PRIMARY KEY (`recommendationId`),
  ADD KEY `farmer_recommendationFK` (`FarmerId`);

--
-- Indexes for table `harvest`
--
ALTER TABLE `harvest`
  ADD PRIMARY KEY (`harvestId`),
  ADD KEY `WarehouseId` (`WarehouseId`),
  ADD KEY `VendorId` (`VendorId`),
  ADD KEY `CropId` (`CropId`);

--
-- Indexes for table `harvest_month`
--
ALTER TABLE `harvest_month`
  ADD KEY `harvest_month_FK` (`harvestId`);

--
-- Indexes for table `inventory_manager`
--
ALTER TABLE `inventory_manager`
  ADD PRIMARY KEY (`StaffId`);

--
-- Indexes for table `inventory_manager_contactno`
--
ALTER TABLE `inventory_manager_contactno`
  ADD KEY `StaffId` (`StaffId`);

--
-- Indexes for table `order_line`
--
ALTER TABLE `order_line`
  ADD KEY `order_line_FK1` (`OrderId`),
  ADD KEY `order_line_FK2` (`VendorId`),
  ADD KEY `order_line_FK3` (`CropId`);

--
-- Indexes for table `order_t`
--
ALTER TABLE `order_t`
  ADD PRIMARY KEY (`OrderId`),
  ADD KEY `order_t_FK` (`ConsumerId`);

--
-- Indexes for table `price_elasticity`
--
ALTER TABLE `price_elasticity`
  ADD PRIMARY KEY (`ElasticityId`),
  ADD KEY `priceelasticity_ibfk_1` (`CropId`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `username` (`username`),
  ADD UNIQUE KEY `email` (`email`);

--
-- Indexes for table `vendor`
--
ALTER TABLE `vendor`
  ADD PRIMARY KEY (`VendorId`);

--
-- Indexes for table `vendor_contact`
--
ALTER TABLE `vendor_contact`
  ADD KEY `VendorId` (`VendorId`);

--
-- Indexes for table `warehouse`
--
ALTER TABLE `warehouse`
  ADD PRIMARY KEY (`warehouseId`),
  ADD KEY `warehouse_FK` (`CropId`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `harvest`
--
ALTER TABLE `harvest`
  MODIFY `harvestId` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=26;

--
-- AUTO_INCREMENT for table `price_elasticity`
--
ALTER TABLE `price_elasticity`
  MODIFY `ElasticityId` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `warehouse`
--
ALTER TABLE `warehouse`
  MODIFY `warehouseId` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `crop_seasonality`
--
ALTER TABLE `crop_seasonality`
  ADD CONSTRAINT `crop_seasonality_ibfk_1` FOREIGN KEY (`CropId`) REFERENCES `crop` (`CropId`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `crop_type`
--
ALTER TABLE `crop_type`
  ADD CONSTRAINT `crop_type_ibfk_1` FOREIGN KEY (`CropId`) REFERENCES `crop` (`CropId`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `crop_variety`
--
ALTER TABLE `crop_variety`
  ADD CONSTRAINT `crop_variety_ibfk_1` FOREIGN KEY (`CropId`) REFERENCES `crop` (`CropId`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `delivery`
--
ALTER TABLE `delivery`
  ADD CONSTRAINT `delivery_ibfk_1` FOREIGN KEY (`WarehouseId`) REFERENCES `warehouse` (`warehouseId`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `farmer_contact_no`
--
ALTER TABLE `farmer_contact_no`
  ADD CONSTRAINT `farmer_contact_no_ibfk_1` FOREIGN KEY (`FarmerId`) REFERENCES `farmer` (`FarmerId`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `farmer_harvest`
--
ALTER TABLE `farmer_harvest`
  ADD CONSTRAINT `farmer_harvest_FK1` FOREIGN KEY (`harvestId`) REFERENCES `harvest` (`harvestId`),
  ADD CONSTRAINT `farmer_harvest_FK2` FOREIGN KEY (`FarmerId`) REFERENCES `farmer` (`FarmerId`);

--
-- Constraints for table `farmer_recommendation`
--
ALTER TABLE `farmer_recommendation`
  ADD CONSTRAINT `farmer_recommendationFK` FOREIGN KEY (`FarmerId`) REFERENCES `farmer` (`FarmerId`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `harvest`
--
ALTER TABLE `harvest`
  ADD CONSTRAINT `harvest_ibfk_1` FOREIGN KEY (`WarehouseId`) REFERENCES `warehouse` (`warehouseId`),
  ADD CONSTRAINT `harvest_ibfk_2` FOREIGN KEY (`VendorId`) REFERENCES `vendor` (`VendorId`),
  ADD CONSTRAINT `harvest_ibfk_3` FOREIGN KEY (`CropId`) REFERENCES `crop` (`CropId`);

--
-- Constraints for table `harvest_month`
--
ALTER TABLE `harvest_month`
  ADD CONSTRAINT `harvest_month_FK` FOREIGN KEY (`harvestId`) REFERENCES `harvest` (`harvestId`);

--
-- Constraints for table `inventory_manager_contactno`
--
ALTER TABLE `inventory_manager_contactno`
  ADD CONSTRAINT `inventory_manager_contactno_ibfk_1` FOREIGN KEY (`StaffId`) REFERENCES `inventory_manager` (`StaffId`) ON DELETE CASCADE;

--
-- Constraints for table `order_line`
--
ALTER TABLE `order_line`
  ADD CONSTRAINT `order_line_FK1` FOREIGN KEY (`OrderId`) REFERENCES `order_t` (`OrderId`),
  ADD CONSTRAINT `order_line_FK2` FOREIGN KEY (`VendorId`) REFERENCES `vendor` (`VendorId`),
  ADD CONSTRAINT `order_line_FK3` FOREIGN KEY (`CropId`) REFERENCES `crop` (`CropId`);

--
-- Constraints for table `order_t`
--
ALTER TABLE `order_t`
  ADD CONSTRAINT `order_t_FK` FOREIGN KEY (`ConsumerId`) REFERENCES `consumer` (`ConsumerId`);

--
-- Constraints for table `price_elasticity`
--
ALTER TABLE `price_elasticity`
  ADD CONSTRAINT `price_elasticity_ibfk_1` FOREIGN KEY (`CropId`) REFERENCES `crop` (`CropId`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `vendor_contact`
--
ALTER TABLE `vendor_contact`
  ADD CONSTRAINT `vendor_contact_ibfk_1` FOREIGN KEY (`VendorId`) REFERENCES `vendor` (`VendorId`);

--
-- Constraints for table `warehouse`
--
ALTER TABLE `warehouse`
  ADD CONSTRAINT `warehouse_FK` FOREIGN KEY (`CropId`) REFERENCES `crop` (`CropId`) ON DELETE SET NULL;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
