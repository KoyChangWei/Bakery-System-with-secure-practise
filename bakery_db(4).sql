-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1:3307
-- Generation Time: Jan 12, 2025 at 08:36 PM
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
-- Database: `bakery_db(4)`
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
(3, 'rhino', 'male', 'baker', 'A@baker.com', '$2y$12$JcrOSHv1Pn6uQMr7LzjN9uWhYyJzu.cVUhR6VvrfjlH6Dkluigthu'),
(4, 'tan', 'male', 'baker', '111@gmail.com', '$2y$10$HfR5TrZVm3V6yD1zAmiTF.EyFRLd3R1aMN5kP0d.VG0BQrdC1pXCm'),
(5, 'zczc', 'male', 'supervisor', '222@gmail.com', '$2y$10$iXx0mm3m8NMOr1UuU7qKyuABSOQb9yN74fQgz2WSHn7rXMpuhn3F.');

-- --------------------------------------------------------

--
-- Table structure for table `batch_db`
--

CREATE TABLE `batch_db` (
  `batch_no_tbl` varchar(255) NOT NULL,
  `startDate_tbl` datetime NOT NULL,
  `endDate_tbl` datetime NOT NULL,
  `production_stage_tbl` varchar(255) NOT NULL,
  `quality_check_tbl` varchar(255) NOT NULL,
  `status_tbl` varchar(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `batch_db`
--

INSERT INTO `batch_db` (`batch_no_tbl`, `startDate_tbl`, `endDate_tbl`, `production_stage_tbl`, `quality_check_tbl`, `status_tbl`) VALUES
('1222', '2024-12-26 19:42:00', '2024-12-26 02:43:00', 'Preparation', 'bad', 'Completed'),
('1233333333', '2024-12-10 15:09:00', '2024-12-30 15:09:00', 'preparation', 'Temperature: 23°C\nMoisture: 22%\nWeight: 100g\nVisual Checks: texture\nNotes: good', 'Scheduled'),
('2147483647', '2024-12-03 02:53:00', '2025-01-09 02:54:00', 'cooling', 'kkk', 'In Progress'),
('6666', '2024-12-06 02:46:00', '2024-12-28 02:46:00', 'preparation', '6', 'Completed'),
('666633777', '2024-12-18 02:53:00', '2024-12-13 02:53:00', 'baking', 'k', 'Scheduled'),
('66667777777777777', '2024-12-11 15:05:00', '2024-12-28 15:05:00', 'preparation', '', 'In Progress'),
('6669', '2024-12-02 03:15:00', '2024-12-29 03:16:00', 'mixing', 'hh', 'Completed'),
('asd1', '2024-12-05 16:18:00', '2024-12-31 19:15:00', 'mixing', 'Temperature: 22°C\nMoisture: 21%\nWeight: 12g\nVisual Checks: texture\nNotes: good', 'Scheduled'),
('123', '2025-01-12 19:07:00', '2025-01-12 05:09:00', 'Mixing', 'aaa', 'Completed');

-- --------------------------------------------------------

--
-- Table structure for table `batch_reports`
--

CREATE TABLE `batch_reports` (
  `batch_no` int(11) UNSIGNED NOT NULL,
  `worker_count` int(11) NOT NULL,
  `worker_names` varchar(255) NOT NULL,
  `temperature` decimal(5,2) NOT NULL,
  `moisture` decimal(5,2) NOT NULL,
  `weight` decimal(10,2) NOT NULL,
  `target_quantity` int(11) NOT NULL,
  `actual_quantity` int(11) NOT NULL,
  `defect_count` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `batch_reports`
--

INSERT INTO `batch_reports` (`batch_no`, `worker_count`, `worker_names`, `temperature`, `moisture`, `weight`, `target_quantity`, `actual_quantity`, `defect_count`) VALUES
(123, 7, 'a,b,c,d,e,f,g', 100.00, 55.00, 55.00, 50, 25, 25);

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
(1, 'Mixer A', 'In Use', '2025-01-12 16:59:26', NULL, NULL),
(2, 'Oven 1', 'In Use', '2025-01-12 16:46:49', NULL, NULL),
(3, 'Cooling Rack', 'Maintenance', '2025-01-09 07:09:08', NULL, NULL),
(4, 'Industrial Mixer', 'In Use', '2025-01-12 18:28:01', NULL, NULL),
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
(14, 25, 5, '2', 2, 8, '2025-01-16', 5, NULL, '2025-01-13 02:05:32', '2025-01-13 02:05:32'),
(15, 500, 25, '4', 4, 10, '2025-01-22', 5, NULL, '2025-01-13 02:28:27', '2025-01-13 02:28:27'),
(16, 1000, 100, '3', 4, 10, '2025-01-14', 5, NULL, '2025-01-13 02:28:56', '2025-01-13 02:28:56');

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
(8, 'Chocolate Ganache Cupcakes', '1. Preheat oven to 350°F (180°C).\n2. Combine dry ingredients (flour, sugar, cocoa powder, baking powder) in a bowl.\n3. Mix wet ingredients (eggs, milk, melted butter) in a separate bowl.\n4. Gradually combine wet and dry mixtures.\n5. Pour batter into cupcake liners and bake for 18-20 minutes.\n6. For ganache: Heat heavy cream and pour over chocolate chips, stirring until smooth.\n7. Let cupcakes cool, then frost with ganache.', 'Oven 1', 5, 5, '2025-01-13 00:46:49', '2025-01-13 00:46:49'),
(9, 'Sourdough Loaf', '1. Mix flour, water, and sourdough starter in a bowl and let it autolyse for 30 minutes.\n2. Add salt and knead until smooth.\n3. Perform stretch-and-folds over 4 hours, every 30 minutes.\n4. Shape dough into a loaf and place it in a proofing basket.\n5. Let it proof overnight in the fridge.\n6. Preheat oven with a Dutch oven inside to 475°F (245°C).\n7. Transfer dough to oven, score, and bake covered for 20 minutes, then uncovered for another 20 minutes.', 'Mixer A', 5, 5, '2025-01-13 00:59:26', '2025-01-13 00:59:26'),
(10, 'Pandan Kaya Cake', '1. Preheat oven to 350°F (180°C).\n2. Blend pandan leaves with water and strain to extract juice.\n3. Mix dry ingredients in a bowl (flour, sugar, baking powder, salt).\n4. In a separate bowl, combine coconut milk, pandan juice, oil, and eggs.\n5. Gradually mix the wet ingredients into the dry ingredients.\n6. Pour batter into a greased cake pan and bake for 25-30 minutes.\n7. Once cool, spread kaya between layers or on top.', 'Industrial Mixer', 5, 5, '2025-01-13 02:28:01', '2025-01-13 02:28:01');

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
(14, 8, 'All-purpose flour', 190.00, 'g'),
(15, 8, 'Granulated sugar', 200.00, 'g'),
(16, 8, 'Cocoa powder', 50.00, 'g'),
(17, 8, 'Baking powder', 2.00, 'tsp'),
(18, 8, 'Eggs', 2.00, 'pcs'),
(19, 8, 'Milk', 120.00, 'ml'),
(20, 8, 'Butter', 115.00, 'g'),
(21, 9, 'Bread Flour', 500.00, 'g'),
(22, 9, 'Active Sourdough Starter', 100.00, 'g'),
(23, 9, 'Water', 350.00, 'ml'),
(24, 9, 'Salt', 2.00, 'tsp'),
(25, 10, 'All-purpose flour', 310.00, 'g'),
(26, 10, 'Granulated Sugar', 200.00, 'g'),
(27, 10, 'Baking Powder', 1.00, 'tsp'),
(28, 10, 'Coconut Milk', 240.00, 'ml'),
(29, 10, 'Pandan Juice', 120.00, 'ml'),
(30, 10, 'Eggs', 4.00, 'pcs');

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
  MODIFY `admin_id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `equipment_status`
--
ALTER TABLE `equipment_status`
  MODIFY `equipment_id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=12;

--
-- AUTO_INCREMENT for table `production_db`
--
ALTER TABLE `production_db`
  MODIFY `production_id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=17;

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
  MODIFY `recipe_id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

--
-- AUTO_INCREMENT for table `recipe_ingredients`
--
ALTER TABLE `recipe_ingredients`
  MODIFY `ingredient_id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=31;

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
