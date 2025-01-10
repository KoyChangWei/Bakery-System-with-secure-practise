-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: localhost
-- Generation Time: Jan 10, 2025 at 02:44 PM
-- Server version: 10.4.28-MariaDB
-- PHP Version: 8.2.4

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `bakery_db`
--

-- --------------------------------------------------------

--
-- Table structure for table `admin_db`
--

CREATE TABLE `admin_db` (
  `admin_id` int(11) UNSIGNED NOT NULL,
  `name_tbl` varchar(255) NOT NULL,
  `gender_tbl` varchar(10) NOT NULL,
  `role_tbl` varchar(50) NOT NULL,
  `email_tbl` varchar(255) NOT NULL,
  `password_tbl` varchar(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `admin_db`
--

INSERT INTO `admin_db` (`admin_id`, `name_tbl`, `gender_tbl`, `role_tbl`, `email_tbl`, `password_tbl`) VALUES
(1, 'syusyi', 'female', 'supervisor', 'syusyi@supervisor.com', '$2y$12$m4P5fYgvXHbdsxo8QuhtiuFpmWHaksMAuAXbir0UryX5VIrVT6CZ2'),
(2, 'CARROT', 'male', 'baker', 'carrottan@baker.com', '$2y$12$0b88et0uf8FFMul2czfXXOzZvkExZmmZaIB0ySoAWvcneOIQoM2/2'),
(3, 'rhino', 'male', 'baker', 'A@baker.com', '$2y$12$JcrOSHv1Pn6uQMr7LzjN9uWhYyJzu.cVUhR6VvrfjlH6Dkluigthu');

-- --------------------------------------------------------

--
-- Table structure for table `equipment_status`
--

CREATE TABLE `equipment_status` (
  `equipment_id` int(11) UNSIGNED NOT NULL,
  `equipment_name` varchar(255) NOT NULL,
  `status` enum('Available','In Use','Maintenance','Repair') DEFAULT 'Available',
  `last_updated` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `notes` text DEFAULT NULL,
  `maintenance_schedule` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `equipment_status`
--

INSERT INTO `equipment_status` (`equipment_id`, `equipment_name`, `status`, `last_updated`, `notes`, `maintenance_schedule`) VALUES
(1, 'Mixer A', 'Available', '2025-01-09 07:09:08', NULL, NULL),
(2, 'Oven 1', 'Available', '2025-01-09 07:09:08', NULL, NULL),
(3, 'Cooling Rack', 'Maintenance', '2025-01-09 07:09:08', NULL, NULL),
(4, 'Industrial Mixer', 'Available', '2025-01-09 07:57:49', NULL, NULL),
(5, 'Commercial Oven', 'In Use', '2025-01-10 06:45:43', NULL, NULL),
(6, 'Dough Sheeter', 'Available', '2025-01-09 07:57:49', NULL, NULL),
(7, 'Proofing Cabinet', 'Available', '2025-01-09 07:57:49', NULL, NULL),
(8, 'Cooling Racks', 'Available', '2025-01-09 07:57:49', NULL, NULL),
(9, 'Baking Pans', 'In Use', '2025-01-10 12:32:44', NULL, NULL),
(10, 'Stand Mixer', 'Available', '2025-01-09 07:57:49', NULL, NULL),
(11, 'Hand Tools', 'In Use', '2025-01-10 06:48:56', NULL, NULL);

-- --------------------------------------------------------

--
-- Table structure for table `production_db`
--

CREATE TABLE `production_db` (
  `production_id` int(11) UNSIGNED NOT NULL,
  `order_volume` int(11) NOT NULL,
  `capacity` int(11) NOT NULL,
  `staff_availability` varchar(255) NOT NULL,
  `equipment_id` int(11) UNSIGNED NOT NULL,
  `recipe_id` int(11) UNSIGNED NOT NULL,
  `production_date` date NOT NULL,
  `created_by` int(11) UNSIGNED DEFAULT NULL,
  `updated_by` int(11) UNSIGNED DEFAULT NULL,
  `created_at` datetime NOT NULL DEFAULT current_timestamp(),
  `updated_at` datetime DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `production_db`
--

INSERT INTO `production_db` (`production_id`, `order_volume`, `capacity`, `staff_availability`, `equipment_id`, `recipe_id`, `production_date`, `created_by`, `updated_by`, `created_at`, `updated_at`) VALUES
(1, 12, 12, '2', 10, 6, '2025-01-17', 1, NULL, '2025-01-10 20:02:52', '2025-01-10 20:02:52'),
(2, 100, 100, '2', 2, 4, '2025-01-10', 1, NULL, '2025-01-10 20:21:04', '2025-01-10 20:21:04'),
(3, 11, 11, '2', 2, 3, '2025-01-10', 1, NULL, '2025-01-10 20:27:36', '2025-01-10 20:27:36'),
(4, 1, 1, '2', 6, 5, '2025-01-15', 1, NULL, '2025-01-10 20:30:16', '2025-01-10 20:30:16'),
(5, 1, 1, '2', 6, 7, '2025-01-29', 1, NULL, '2025-01-10 20:33:07', '2025-01-10 20:33:07'),
(6, 12, 12, '3', 7, 2, '2025-02-07', 1, NULL, '2025-01-10 20:34:55', '2025-01-10 20:34:55'),
(7, 2, 2, '2', 4, 3, '2025-01-10', 1, NULL, '2025-01-10 20:43:01', '2025-01-10 20:43:01');

-- --------------------------------------------------------

--
-- Table structure for table `production_schedule`
--

CREATE TABLE `production_schedule` (
  `schedule_id` int(11) UNSIGNED NOT NULL,
  `product_id` int(11) NOT NULL,
  `production_date` date NOT NULL,
  `order_volume` int(11) NOT NULL,
  `production_capacity` int(11) NOT NULL,
  `equipment_name` varchar(255) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `production_staff_assignment`
--

CREATE TABLE `production_staff_assignment` (
  `assignment_id` int(11) NOT NULL,
  `schedule_id` int(10) UNSIGNED NOT NULL,
  `staff_id` int(10) UNSIGNED NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `recipe_db`
--

CREATE TABLE `recipe_db` (
  `recipe_id` int(11) UNSIGNED NOT NULL,
  `recipe_name` varchar(255) NOT NULL,
  `preparation_step_tbl` text NOT NULL,
  `equipment_tbl` varchar(255) NOT NULL,
  `created_by` int(11) UNSIGNED DEFAULT NULL,
  `updated_by` int(11) UNSIGNED DEFAULT NULL,
  `created_at` datetime NOT NULL DEFAULT current_timestamp(),
  `updated_at` datetime DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `recipe_db`
--

INSERT INTO `recipe_db` (`recipe_id`, `recipe_name`, `preparation_step_tbl`, `equipment_tbl`, `created_by`, `updated_by`, `created_at`, `updated_at`) VALUES
(1, 'J', '1. U', 'Commercial Oven', 1, 1, '2025-01-10 14:19:18', '2025-01-10 14:19:18'),
(2, 'JNKKJ', '1. JOOII', 'Commercial Oven', 1, 1, '2025-01-10 14:19:55', '2025-01-10 14:19:55'),
(3, 'x', '1. v', 'Commercial Oven', 1, 1, '2025-01-10 14:24:17', '2025-01-10 14:24:17'),
(4, 'F', '1. F', 'Commercial Oven', 1, 1, '2025-01-10 14:27:57', '2025-01-10 14:27:57'),
(5, 'V', '1. V', 'Oven 1', 1, 1, '2025-01-10 14:30:36', '2025-01-10 14:47:16'),
(6, 'RotiBoy', '1. put flour into bowl', 'Hand Tools', 1, 1, '2025-01-10 14:48:56', '2025-01-10 14:48:56'),
(7, 'SASA', '1. CXV', 'Baking Pans', 1, 1, '2025-01-10 20:32:44', '2025-01-10 20:32:44');

--
-- Triggers `recipe_db`
--
DELIMITER $$
CREATE TRIGGER `update_equipment_status` AFTER INSERT ON `recipe_db` FOR EACH ROW BEGIN
    -- Update the equipment status to 'In Use' when equipment is assigned
    UPDATE equipment_status
    SET status = 'In Use'
    WHERE equipment_name = NEW.equipment_tbl;
END
$$
DELIMITER ;

-- --------------------------------------------------------

--
-- Table structure for table `recipe_ingredients`
--

CREATE TABLE `recipe_ingredients` (
  `ingredient_id` int(11) UNSIGNED NOT NULL,
  `recipe_id` int(11) UNSIGNED NOT NULL,
  `ingredient_name` varchar(255) NOT NULL,
  `quantity` decimal(10,2) NOT NULL,
  `unit_tbl` varchar(50) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `recipe_ingredients`
--

INSERT INTO `recipe_ingredients` (`ingredient_id`, `recipe_id`, `ingredient_name`, `quantity`, `unit_tbl`) VALUES
(1, 1, 'I', 9.00, 'kg'),
(2, 2, 'KJK', 9.00, 'kg'),
(3, 3, 'x', 4.00, 'kg'),
(6, 4, 'V', 4.00, 'L'),
(11, 5, 'V', 2.00, 'tbsp'),
(12, 6, 'Flour', 23.00, 'kg'),
(13, 7, 'Flour', 34.00, 'g');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `admin_db`
--
ALTER TABLE `admin_db`
  ADD PRIMARY KEY (`admin_id`);

--
-- Indexes for table `equipment_status`
--
ALTER TABLE `equipment_status`
  ADD PRIMARY KEY (`equipment_id`);

--
-- Indexes for table `production_db`
--
ALTER TABLE `production_db`
  ADD PRIMARY KEY (`production_id`),
  ADD KEY `equipment_id` (`equipment_id`),
  ADD KEY `recipe_id` (`recipe_id`),
  ADD KEY `created_by` (`created_by`),
  ADD KEY `updated_by` (`updated_by`);

--
-- Indexes for table `production_schedule`
--
ALTER TABLE `production_schedule`
  ADD PRIMARY KEY (`schedule_id`);

--
-- Indexes for table `production_staff_assignment`
--
ALTER TABLE `production_staff_assignment`
  ADD PRIMARY KEY (`assignment_id`),
  ADD KEY `schedule_id` (`schedule_id`),
  ADD KEY `staff_id` (`staff_id`);

--
-- Indexes for table `recipe_db`
--
ALTER TABLE `recipe_db`
  ADD PRIMARY KEY (`recipe_id`),
  ADD KEY `created_by` (`created_by`),
  ADD KEY `updated_by` (`updated_by`);

--
-- Indexes for table `recipe_ingredients`
--
ALTER TABLE `recipe_ingredients`
  ADD PRIMARY KEY (`ingredient_id`),
  ADD KEY `recipe_id` (`recipe_id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `admin_db`
--
ALTER TABLE `admin_db`
  MODIFY `admin_id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `equipment_status`
--
ALTER TABLE `equipment_status`
  MODIFY `equipment_id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=12;

--
-- AUTO_INCREMENT for table `production_db`
--
ALTER TABLE `production_db`
  MODIFY `production_id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- AUTO_INCREMENT for table `production_schedule`
--
ALTER TABLE `production_schedule`
  MODIFY `schedule_id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `production_staff_assignment`
--
ALTER TABLE `production_staff_assignment`
  MODIFY `assignment_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `recipe_db`
--
ALTER TABLE `recipe_db`
  MODIFY `recipe_id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- AUTO_INCREMENT for table `recipe_ingredients`
--
ALTER TABLE `recipe_ingredients`
  MODIFY `ingredient_id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=14;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `production_db`
--
ALTER TABLE `production_db`
  ADD CONSTRAINT `production_db_ibfk_1` FOREIGN KEY (`equipment_id`) REFERENCES `equipment_status` (`equipment_id`) ON DELETE CASCADE,
  ADD CONSTRAINT `production_db_ibfk_2` FOREIGN KEY (`recipe_id`) REFERENCES `recipe_db` (`recipe_id`) ON DELETE CASCADE,
  ADD CONSTRAINT `production_db_ibfk_3` FOREIGN KEY (`created_by`) REFERENCES `admin_db` (`admin_id`) ON DELETE SET NULL,
  ADD CONSTRAINT `production_db_ibfk_4` FOREIGN KEY (`updated_by`) REFERENCES `admin_db` (`admin_id`) ON DELETE SET NULL;

--
-- Constraints for table `production_staff_assignment`
--
ALTER TABLE `production_staff_assignment`
  ADD CONSTRAINT `production_staff_assignment_ibfk_1` FOREIGN KEY (`schedule_id`) REFERENCES `production_schedule` (`schedule_id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `production_staff_assignment_ibfk_2` FOREIGN KEY (`staff_id`) REFERENCES `admin_db` (`admin_id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `recipe_db`
--
ALTER TABLE `recipe_db`
  ADD CONSTRAINT `recipe_db_ibfk_1` FOREIGN KEY (`created_by`) REFERENCES `admin_db` (`admin_id`) ON DELETE SET NULL,
  ADD CONSTRAINT `recipe_db_ibfk_2` FOREIGN KEY (`updated_by`) REFERENCES `admin_db` (`admin_id`) ON DELETE SET NULL;

--
-- Constraints for table `recipe_ingredients`
--
ALTER TABLE `recipe_ingredients`
  ADD CONSTRAINT `recipe_ingredients_ibfk_1` FOREIGN KEY (`recipe_id`) REFERENCES `recipe_db` (`recipe_id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
