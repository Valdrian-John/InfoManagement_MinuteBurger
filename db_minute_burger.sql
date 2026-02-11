-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1:3307
-- Generation Time: Feb 11, 2026 at 07:08 AM
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
-- Database: `db_minute_burger`
--

-- --------------------------------------------------------

--
-- Table structure for table `category_product`
--

CREATE TABLE `category_product` (
  `category_id` int(11) NOT NULL,
  `category` varchar(50) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `category_product`
--

INSERT INTO `category_product` (`category_id`, `category`) VALUES
(1, 'Value'),
(2, 'Double'),
(3, 'Bigtime'),
(4, 'Hotdog'),
(5, 'Sides'),
(6, 'Beverages'),
(7, 'Extras');

-- --------------------------------------------------------

--
-- Table structure for table `category_stockout`
--

CREATE TABLE `category_stockout` (
  `category_id` int(11) NOT NULL,
  `category` enum('Damaged','Expired','Missing') NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `civil_status`
--

CREATE TABLE `civil_status` (
  `civil_status_id` int(11) NOT NULL,
  `civil_status` enum('Single','Married','Widow') NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `employee`
--

CREATE TABLE `employee` (
  `employee_id` int(11) NOT NULL,
  `name` varchar(100) NOT NULL,
  `gender_id` int(11) NOT NULL,
  `date_of_birth` date NOT NULL,
  `hea_id` int(11) NOT NULL,
  `civil_status_id` int(11) NOT NULL,
  `contact_no` varchar(20) DEFAULT NULL,
  `address` varchar(255) DEFAULT NULL,
  `role_id` int(11) NOT NULL,
  `date_hired` date NOT NULL,
  `status_id` int(11) NOT NULL,
  `created_at` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `gender`
--

CREATE TABLE `gender` (
  `gender_id` int(11) NOT NULL,
  `gender_title` varchar(20) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `highest_educational_attainment`
--

CREATE TABLE `highest_educational_attainment` (
  `hea_id` int(11) NOT NULL,
  `hea_title` enum('HS','College','GS') NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `inventoryitem`
--

CREATE TABLE `inventoryitem` (
  `item_id` int(11) NOT NULL,
  `item_name` varchar(100) NOT NULL,
  `unit` varchar(20) NOT NULL,
  `price_per_unit` decimal(10,2) NOT NULL,
  `current_stock` int(11) NOT NULL,
  `reorder_level` int(11) NOT NULL,
  `status_id` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `inventoryitem`
--

INSERT INTO `inventoryitem` (`item_id`, `item_name`, `unit`, `price_per_unit`, `current_stock`, `reorder_level`, `status_id`) VALUES
(1, 'Value Burger Buns 10s', 'pcs', 0.00, 1045, 50, 1),
(2, 'Double Minute Burger Buns', 'pcs', 0.00, 170, 50, 1),
(3, 'Value Patty', 'pcs', 0.00, 954, 50, 1),
(4, 'Chicken Patty', 'pcs', 0.00, 80, 50, 1),
(5, 'Super Dressing', 'ml', 0.00, 420, 50, 1),
(6, 'Cheesy Dressing', 'ml', 0.00, 110, 50, 1),
(7, 'Chicken Dressing', 'ml', 0.00, 134, 50, 1),
(8, 'Brioche Burger Buns', 'pcs', 0.00, 512, 50, 1),
(9, 'Premium Chicken Patty 20s', 'pcs', 0.00, 106, 50, 1),
(10, 'Shredded Cabbage 20s', 'pcs', 0.00, 70, 50, 1),
(11, 'Roast Sesame Dressing 20s', 'ml', 0.00, 70, 50, 1),
(12, 'Chimi Pesto Mayo 20s', 'ml', 0.00, 96, 50, 1),
(13, 'Chimichurri Sauce 20s', 'ml', 0.00, 72, 50, 1),
(14, 'Pickled Onions', 'pcs', 0.00, 72, 50, 1),
(15, 'Veggie Patty 20s', 'pcs', 0.00, 58, 50, 1),
(16, 'Honey Mustard Sauce 20s', 'ml', 0.00, 58, 50, 1),
(17, 'Veggie Cabbage 20s', 'pcs', 0.00, 58, 50, 1),
(18, 'Premium Patty', 'pcs', 0.00, 256, 50, 1),
(19, 'Beef Bacon Ultimate', 'pcs', 0.00, 90, 50, 1),
(20, 'Premium Coleslaw', 'pcs', 0.00, 90, 50, 1),
(21, 'Ultimate Bacon Cheese Sauce', 'ml', 0.00, 90, 50, 1),
(22, 'Roasted Mushrooms', 'pcs', 0.00, 262, 50, 1),
(23, 'Caramelized Onions', 'pcs', 0.00, 204, 50, 1),
(24, 'Grilled Black Pepper Sauce', 'ml', 0.00, 204, 50, 1),
(25, 'Chili Cheese Sauce', 'ml', 0.00, 56, 50, 1),
(26, 'Shawarma Dressing', 'ml', 0.00, 56, 50, 1),
(27, 'All Meat Steak Patty 20s', 'pcs', 0.00, 96, 50, 1),
(28, 'Cheddar Cheese Sauce 20s', 'ml', 0.00, 90, 50, 1),
(29, 'Ultimate Dressing 20s', 'ml', 0.00, 86, 50, 1),
(30, 'Ultimate Roasted Onions 20s', 'pcs', 0.00, 86, 50, 1),
(31, 'Tomato Ketchup 20s', 'ml', 0.00, 86, 50, 1),
(32, 'Pandesal Buns (ucp)', 'pcs', 0.00, 12, 50, 1),
(33, 'Hotdog Buns 10s', 'pcs', 0.00, 64, 50, 1),
(34, 'Cheesedog 40s', 'pcs', 0.00, 50, 50, 1),
(35, 'Oatmeal Sprinkle Hotdog Buns', 'pcs', 0.00, 56, 50, 1),
(36, 'Bigtime Franks 20s', 'pcs', 0.00, 56, 50, 1),
(37, 'French Onion Dressing 20s', 'ml', 0.00, 90, 50, 1),
(38, 'Dried Herb Leaves 20s', 'pcs', 0.00, 46, 50, 1),
(39, 'Hungarian Hotdog', 'pcs', 0.00, 42, 50, 1),
(40, 'Oatmeal Hotdog Buns', 'pcs', 0.00, 42, 50, 1),
(41, 'Mayo', 'ml', 0.00, 42, 50, 1),
(42, 'Tomato', 'ml', 0.00, 42, 50, 1),
(43, 'Nachos Chili con carne', 'pcs', 0.00, 8, 50, 1),
(44, 'Nachos cheese sauce', 'ml', 0.00, 18, 50, 1),
(45, 'Farmer John Chips (fries)', 'pcs', 0.00, 19, 50, 1),
(46, 'Clover', 'pcs', 0.00, 14, 50, 1),
(47, 'Calamantea', 'ml', 0.00, 161, 50, 1),
(48, 'Fruittwist', 'ml', 0.00, 150, 50, 1),
(49, 'Choco Mallows', 'ml', 0.00, 43, 50, 1),
(50, 'Iced Choco', 'ml', 0.00, 47, 50, 1),
(51, 'Hot Coffee', 'ml', 0.00, 10, 50, 1),
(52, 'Bottled Water 500ml', 'ml', 0.00, 32, 50, 1),
(53, 'Cheese', 'pcs', 0.00, 256, 50, 1),
(54, 'Egg', 'pcs', 0.00, 56, 50, 1),
(55, 'Extra Coleslaw', 'pcs', 0.00, 52, 50, 1),
(56, '16oz cup', 'pcs', 0.00, 779, 50, 1),
(57, '8 oz cups', 'pcs', 0.00, 795, 50, 1);

-- --------------------------------------------------------

--
-- Table structure for table `inventory_status`
--

CREATE TABLE `inventory_status` (
  `status_id` int(11) NOT NULL,
  `status` enum('In Stock','Out of Stock') NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `inventory_status`
--

INSERT INTO `inventory_status` (`status_id`, `status`) VALUES
(1, 'In Stock'),
(2, 'Out of Stock');

-- --------------------------------------------------------

--
-- Table structure for table `make_product`
--

CREATE TABLE `make_product` (
  `product_id` int(11) NOT NULL,
  `item_id` int(11) NOT NULL,
  `unit` varchar(20) NOT NULL,
  `unit_quantity` decimal(10,2) NOT NULL,
  `price_per_unit` decimal(10,2) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `make_product`
--

INSERT INTO `make_product` (`product_id`, `item_id`, `unit`, `unit_quantity`, `price_per_unit`) VALUES
(1, 1, 'pcs', 2.00, 0.00),
(1, 3, 'pcs', 2.00, 0.00),
(2, 1, 'pcs', 2.00, 0.00),
(2, 3, 'pcs', 2.00, 0.00),
(2, 6, 'ml', 2.00, 0.00),
(3, 1, 'pcs', 2.00, 0.00),
(3, 3, 'pcs', 2.00, 0.00),
(3, 25, 'ml', 2.00, 0.00),
(4, 1, 'pcs', 2.00, 0.00),
(4, 4, 'pcs', 2.00, 0.00),
(5, 1, 'pcs', 2.00, 0.00),
(5, 4, 'pcs', 2.00, 0.00),
(5, 53, 'pcs', 2.00, 0.00),
(6, 2, 'pcs', 2.00, 0.00),
(6, 3, 'pcs', 4.00, 0.00),
(7, 2, 'pcs', 2.00, 0.00),
(7, 3, 'pcs', 4.00, 0.00),
(7, 53, 'pcs', 2.00, 0.00),
(8, 2, 'pcs', 2.00, 0.00),
(8, 3, 'pcs', 4.00, 0.00),
(8, 25, 'ml', 2.00, 0.00),
(9, 2, 'pcs', 2.00, 0.00),
(9, 4, 'pcs', 4.00, 0.00),
(10, 8, 'pcs', 2.00, 0.00),
(10, 9, 'pcs', 2.00, 0.00),
(10, 11, 'ml', 2.00, 0.00),
(11, 8, 'pcs', 2.00, 0.00),
(11, 13, 'ml', 2.00, 0.00),
(11, 18, 'pcs', 2.00, 0.00),
(12, 4, 'pcs', 1.00, 0.00),
(12, 8, 'pcs', 2.00, 0.00),
(12, 15, 'pcs', 1.00, 0.00),
(13, 8, 'pcs', 2.00, 0.00),
(13, 18, 'pcs', 2.00, 0.00),
(13, 19, 'pcs', 4.00, 0.00),
(14, 8, 'pcs', 2.00, 0.00),
(14, 18, 'pcs', 2.00, 0.00),
(14, 24, 'ml', 2.00, 0.00),
(15, 8, 'pcs', 2.00, 0.00),
(15, 18, 'pcs', 2.00, 0.00),
(15, 26, 'ml', 2.00, 0.00),
(16, 8, 'pcs', 2.00, 0.00),
(16, 27, 'pcs', 2.00, 0.00),
(17, 32, 'pcs', 1.00, 0.00),
(18, 33, 'pcs', 2.00, 0.00),
(18, 34, 'pcs', 2.00, 0.00),
(19, 35, 'pcs', 2.00, 0.00),
(19, 36, 'pcs', 2.00, 0.00),
(19, 37, 'ml', 2.00, 0.00),
(20, 25, 'ml', 2.00, 0.00),
(20, 35, 'pcs', 2.00, 0.00),
(20, 36, 'pcs', 2.00, 0.00),
(21, 43, 'pcs', 1.00, 0.00),
(21, 44, 'ml', 1.00, 0.00),
(22, 47, 'ml', 1.00, 0.00),
(22, 56, 'pcs', 1.00, 0.00),
(23, 48, 'ml', 1.00, 0.00),
(23, 56, 'pcs', 1.00, 0.00),
(24, 49, 'ml', 1.00, 0.00),
(24, 57, 'pcs', 1.00, 0.00),
(25, 57, 'pcs', 1.00, 0.00),
(26, 50, 'ml', 1.00, 0.00),
(26, 56, 'pcs', 1.00, 0.00),
(28, 56, 'pcs', 1.00, 0.00),
(29, 52, 'ml', 1.00, 0.00),
(30, 53, 'pcs', 2.00, 0.00),
(31, 54, 'pcs', 1.00, 0.00),
(32, 55, 'pcs', 1.00, 0.00),
(99, 39, 'pcs', 2.00, 0.00),
(99, 40, 'pcs', 2.00, 0.00);

-- --------------------------------------------------------

--
-- Table structure for table `orders`
--

CREATE TABLE `orders` (
  `order_id` int(11) NOT NULL,
  `customer_id` int(11) DEFAULT NULL,
  `employee_id` int(11) NOT NULL,
  `order_date` date NOT NULL,
  `total_amount` decimal(10,2) DEFAULT NULL,
  `discount` decimal(10,2) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `order_details`
--

CREATE TABLE `order_details` (
  `order_id` int(11) NOT NULL,
  `product_id` int(11) NOT NULL,
  `discount` decimal(10,2) DEFAULT NULL,
  `price` decimal(10,2) NOT NULL,
  `quantity` int(11) NOT NULL,
  `sub_total` decimal(10,2) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `product`
--

CREATE TABLE `product` (
  `product_id` int(11) NOT NULL,
  `product_name` varchar(100) NOT NULL,
  `quantity` decimal(10,2) DEFAULT NULL,
  `selling_price` decimal(10,2) NOT NULL,
  `category_id` int(11) NOT NULL,
  `created_at` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `product`
--

INSERT INTO `product` (`product_id`, `product_name`, `quantity`, `selling_price`, `category_id`, `created_at`) VALUES
(1, 'B1T1 MB Value Burger', NULL, 41.00, 1, '2026-02-11 11:26:41'),
(2, 'B1T1 Cheesy MB Burger', NULL, 52.00, 1, '2026-02-11 11:26:41'),
(3, 'B1T1 Spicy Cheese Burger', NULL, 52.00, 1, '2026-02-11 11:26:41'),
(4, 'B1T1 Chickentime', NULL, 50.00, 1, '2026-02-11 11:26:41'),
(5, 'B1T1 Chickentime with Cheese', NULL, 65.00, 1, '2026-02-11 11:26:41'),
(6, 'B1T1 Double Minute Burger', NULL, 64.00, 2, '2026-02-11 11:26:41'),
(7, 'B1T1 Double Cheesy Burger', NULL, 80.00, 2, '2026-02-11 11:26:41'),
(8, 'B1T1 Double Spicy Cheese Burger', NULL, 80.00, 2, '2026-02-11 11:26:41'),
(9, 'B1T1 Double Chickentime', NULL, 80.00, 2, '2026-02-11 11:26:41'),
(10, 'B1T1 Roast Sesame Chicken Burger', NULL, 94.00, 3, '2026-02-11 11:26:41'),
(11, 'B1T1 Chimi Pesto Burger', NULL, 99.00, 3, '2026-02-11 11:26:41'),
(12, 'B1T1 50/50 Veggie Chicken Burger', NULL, 86.00, 3, '2026-02-11 11:26:41'),
(13, 'B1T1 Bacon Cheeseburger', NULL, 96.00, 3, '2026-02-11 11:26:41'),
(14, 'B1T1 Black Pepper Burger', NULL, 89.00, 3, '2026-02-11 11:26:41'),
(15, 'B1T1 Beef Shawarma', NULL, 91.00, 3, '2026-02-11 11:26:41'),
(16, 'B1T1 All Meat Steak Burger', NULL, 140.00, 3, '2026-02-11 11:26:41'),
(17, 'Beef Asado Pandesal', NULL, 55.00, 3, '2026-02-11 11:26:41'),
(18, 'B1T1 Cheesedog', NULL, 49.00, 4, '2026-02-11 11:26:41'),
(19, 'B1T1 French Onion Franks', NULL, 94.00, 4, '2026-02-11 11:26:41'),
(20, 'B1T1 Chili Con Franks', NULL, 96.00, 4, '2026-02-11 11:26:41'),
(21, 'Cheesy Carne Nachos', NULL, 52.00, 5, '2026-02-11 11:26:41'),
(22, 'Calamantea 16 oz', NULL, 24.00, 6, '2026-02-11 11:26:41'),
(23, 'Fruitwist 16 oz', NULL, 24.00, 6, '2026-02-11 11:26:41'),
(24, 'Hot Choco 8 oz', NULL, 20.00, 6, '2026-02-11 11:26:41'),
(25, 'Hot Mocha', NULL, 20.00, 6, '2026-02-11 11:26:41'),
(26, 'Iced Choco 16 oz', NULL, 23.00, 6, '2026-02-11 11:26:41'),
(28, 'Wintermelon Milk Tea', NULL, 30.00, 6, '2026-02-11 11:26:41'),
(29, 'Bottled Water 500ml', NULL, 16.00, 6, '2026-02-11 11:26:41'),
(30, 'B1T1 Cheese', NULL, 15.00, 7, '2026-02-11 11:26:41'),
(31, 'Egg', NULL, 15.00, 7, '2026-02-11 11:26:41'),
(32, 'Coleslaw', NULL, 12.00, 7, '2026-02-11 11:26:41'),
(99, 'B1T1 Hungarian Hotdog', NULL, 111.00, 4, '2026-02-11 11:26:41');

-- --------------------------------------------------------

--
-- Table structure for table `role`
--

CREATE TABLE `role` (
  `role_id` int(11) NOT NULL,
  `role` enum('Trainee','Trained') NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `status`
--

CREATE TABLE `status` (
  `status_id` int(11) NOT NULL,
  `status` enum('Active','Inactive','AWOL','LayOff') NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `status`
--

INSERT INTO `status` (`status_id`, `status`) VALUES
(1, 'Active'),
(2, 'Inactive'),
(3, 'AWOL'),
(4, 'LayOff');

-- --------------------------------------------------------

--
-- Table structure for table `stockout`
--

CREATE TABLE `stockout` (
  `stockout_id` int(11) NOT NULL,
  `date` date NOT NULL,
  `employee_id` int(11) NOT NULL,
  `item_id` int(11) NOT NULL,
  `category_id` int(11) NOT NULL,
  `description` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `supply`
--

CREATE TABLE `supply` (
  `supply_id` int(11) NOT NULL,
  `supply_date` date NOT NULL,
  `total_amount` decimal(10,2) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `supply_details`
--

CREATE TABLE `supply_details` (
  `supply_id` int(11) NOT NULL,
  `item_id` int(11) NOT NULL,
  `price` decimal(10,2) NOT NULL,
  `quantity` int(11) NOT NULL,
  `sub_total` decimal(10,2) DEFAULT NULL,
  `shelf_life` date DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Indexes for dumped tables
--

--
-- Indexes for table `category_product`
--
ALTER TABLE `category_product`
  ADD PRIMARY KEY (`category_id`);

--
-- Indexes for table `category_stockout`
--
ALTER TABLE `category_stockout`
  ADD PRIMARY KEY (`category_id`);

--
-- Indexes for table `civil_status`
--
ALTER TABLE `civil_status`
  ADD PRIMARY KEY (`civil_status_id`);

--
-- Indexes for table `employee`
--
ALTER TABLE `employee`
  ADD PRIMARY KEY (`employee_id`),
  ADD KEY `gender_id` (`gender_id`),
  ADD KEY `hea_id` (`hea_id`),
  ADD KEY `civil_status_id` (`civil_status_id`),
  ADD KEY `role_id` (`role_id`),
  ADD KEY `status_id` (`status_id`);

--
-- Indexes for table `gender`
--
ALTER TABLE `gender`
  ADD PRIMARY KEY (`gender_id`);

--
-- Indexes for table `highest_educational_attainment`
--
ALTER TABLE `highest_educational_attainment`
  ADD PRIMARY KEY (`hea_id`);

--
-- Indexes for table `inventoryitem`
--
ALTER TABLE `inventoryitem`
  ADD PRIMARY KEY (`item_id`),
  ADD KEY `status_id` (`status_id`);

--
-- Indexes for table `inventory_status`
--
ALTER TABLE `inventory_status`
  ADD PRIMARY KEY (`status_id`);

--
-- Indexes for table `make_product`
--
ALTER TABLE `make_product`
  ADD PRIMARY KEY (`product_id`,`item_id`),
  ADD KEY `item_id` (`item_id`);

--
-- Indexes for table `orders`
--
ALTER TABLE `orders`
  ADD PRIMARY KEY (`order_id`),
  ADD KEY `employee_id` (`employee_id`);

--
-- Indexes for table `order_details`
--
ALTER TABLE `order_details`
  ADD PRIMARY KEY (`order_id`,`product_id`),
  ADD KEY `product_id` (`product_id`);

--
-- Indexes for table `product`
--
ALTER TABLE `product`
  ADD PRIMARY KEY (`product_id`),
  ADD KEY `category_id` (`category_id`);

--
-- Indexes for table `role`
--
ALTER TABLE `role`
  ADD PRIMARY KEY (`role_id`);

--
-- Indexes for table `status`
--
ALTER TABLE `status`
  ADD PRIMARY KEY (`status_id`);

--
-- Indexes for table `stockout`
--
ALTER TABLE `stockout`
  ADD PRIMARY KEY (`stockout_id`),
  ADD KEY `employee_id` (`employee_id`),
  ADD KEY `item_id` (`item_id`),
  ADD KEY `category_id` (`category_id`);

--
-- Indexes for table `supply`
--
ALTER TABLE `supply`
  ADD PRIMARY KEY (`supply_id`);

--
-- Indexes for table `supply_details`
--
ALTER TABLE `supply_details`
  ADD PRIMARY KEY (`supply_id`,`item_id`),
  ADD KEY `item_id` (`item_id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `category_product`
--
ALTER TABLE `category_product`
  MODIFY `category_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- AUTO_INCREMENT for table `category_stockout`
--
ALTER TABLE `category_stockout`
  MODIFY `category_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `civil_status`
--
ALTER TABLE `civil_status`
  MODIFY `civil_status_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `employee`
--
ALTER TABLE `employee`
  MODIFY `employee_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `gender`
--
ALTER TABLE `gender`
  MODIFY `gender_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `highest_educational_attainment`
--
ALTER TABLE `highest_educational_attainment`
  MODIFY `hea_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `inventoryitem`
--
ALTER TABLE `inventoryitem`
  MODIFY `item_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=58;

--
-- AUTO_INCREMENT for table `inventory_status`
--
ALTER TABLE `inventory_status`
  MODIFY `status_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `orders`
--
ALTER TABLE `orders`
  MODIFY `order_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `product`
--
ALTER TABLE `product`
  MODIFY `product_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=100;

--
-- AUTO_INCREMENT for table `role`
--
ALTER TABLE `role`
  MODIFY `role_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `status`
--
ALTER TABLE `status`
  MODIFY `status_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `stockout`
--
ALTER TABLE `stockout`
  MODIFY `stockout_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `supply`
--
ALTER TABLE `supply`
  MODIFY `supply_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `employee`
--
ALTER TABLE `employee`
  ADD CONSTRAINT `employee_ibfk_1` FOREIGN KEY (`gender_id`) REFERENCES `gender` (`gender_id`),
  ADD CONSTRAINT `employee_ibfk_2` FOREIGN KEY (`hea_id`) REFERENCES `highest_educational_attainment` (`hea_id`),
  ADD CONSTRAINT `employee_ibfk_3` FOREIGN KEY (`civil_status_id`) REFERENCES `civil_status` (`civil_status_id`),
  ADD CONSTRAINT `employee_ibfk_4` FOREIGN KEY (`role_id`) REFERENCES `role` (`role_id`),
  ADD CONSTRAINT `employee_ibfk_5` FOREIGN KEY (`status_id`) REFERENCES `status` (`status_id`);

--
-- Constraints for table `inventoryitem`
--
ALTER TABLE `inventoryitem`
  ADD CONSTRAINT `inventoryitem_ibfk_1` FOREIGN KEY (`status_id`) REFERENCES `inventory_status` (`status_id`);

--
-- Constraints for table `make_product`
--
ALTER TABLE `make_product`
  ADD CONSTRAINT `make_product_ibfk_1` FOREIGN KEY (`product_id`) REFERENCES `product` (`product_id`),
  ADD CONSTRAINT `make_product_ibfk_2` FOREIGN KEY (`item_id`) REFERENCES `inventoryitem` (`item_id`);

--
-- Constraints for table `orders`
--
ALTER TABLE `orders`
  ADD CONSTRAINT `orders_ibfk_1` FOREIGN KEY (`employee_id`) REFERENCES `employee` (`employee_id`);

--
-- Constraints for table `order_details`
--
ALTER TABLE `order_details`
  ADD CONSTRAINT `order_details_ibfk_1` FOREIGN KEY (`order_id`) REFERENCES `orders` (`order_id`),
  ADD CONSTRAINT `order_details_ibfk_2` FOREIGN KEY (`product_id`) REFERENCES `product` (`product_id`);

--
-- Constraints for table `product`
--
ALTER TABLE `product`
  ADD CONSTRAINT `product_ibfk_1` FOREIGN KEY (`category_id`) REFERENCES `category_product` (`category_id`);

--
-- Constraints for table `stockout`
--
ALTER TABLE `stockout`
  ADD CONSTRAINT `stockout_ibfk_1` FOREIGN KEY (`employee_id`) REFERENCES `employee` (`employee_id`),
  ADD CONSTRAINT `stockout_ibfk_2` FOREIGN KEY (`item_id`) REFERENCES `inventoryitem` (`item_id`),
  ADD CONSTRAINT `stockout_ibfk_3` FOREIGN KEY (`category_id`) REFERENCES `category_stockout` (`category_id`);

--
-- Constraints for table `supply_details`
--
ALTER TABLE `supply_details`
  ADD CONSTRAINT `supply_details_ibfk_1` FOREIGN KEY (`supply_id`) REFERENCES `supply` (`supply_id`),
  ADD CONSTRAINT `supply_details_ibfk_2` FOREIGN KEY (`item_id`) REFERENCES `inventoryitem` (`item_id`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
