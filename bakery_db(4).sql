-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1:3307
-- Generation Time: Jan 13, 2025 at 12:43 PM
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
(5, 'zczc', 'male', 'supervisor', '222@gmail.com', '$2y$10$iXx0mm3m8NMOr1UuU7qKyuABSOQb9yN74fQgz2WSHn7rXMpuhn3F.'),
(6, 'aaa', 'male', 'baker', '333@gmail.com', '$2y$10$omgLa74HPCL6Tf0efjgEUeRyi035.NWPfd37ZTeX6qDIhfjdiZ5h.');

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
('123', '2025-01-12 19:07:00', '2025-01-12 05:09:00', 'Mixing', 'aaa', 'Completed'),
('111', '2025-01-13 20:38:00', '2025-01-13 19:40:00', 'Preparation', '', 'In Progress'),
('654', '2025-01-13 20:39:00', '2025-01-16 19:39:00', 'Cooling', 'the texture is good', 'In Progress'),
('842', '2025-01-20 19:40:00', '2025-01-22 19:40:00', 'Baking', 'everything is perfect', 'Completed');

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
(123, 7, 'a,b,c,d,e,f,g', 100.00, 55.00, 55.00, 50, 25, 25),
(111, 5, 'ali, abu, atan, jess, nanny', 100.00, 99.00, 2314.00, 676, 452, 224),
(654, 4, 'murugan, lim, tan, harith', 98.00, 100.00, 8542.00, 1111, 987, 124),
(842, 4, 'terry, marcus, bob, syasya', 100.00, 100.00, 5444.00, 1555, 1532, 23);

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
(1, 'Mixer A', 'In Use', '2025-01-13 09:01:54', NULL, NULL),
(2, 'Oven 1', 'In Use', '2025-01-13 09:01:54', NULL, NULL),
(3, 'Cooling Rack', 'Available', '2025-01-13 09:58:34', NULL, NULL),
(4, 'Industrial Mixer', 'In Use', '2025-01-13 09:30:46', NULL, NULL),
(5, 'Commercial Oven', 'In Use', '2025-01-13 09:34:41', NULL, NULL),
(6, 'Dough Sheeter', 'In Use', '2025-01-13 09:35:57', NULL, NULL),
(7, 'Proofing Cabinet', 'Available', '2025-01-09 07:57:49', NULL, NULL),
(8, 'Cooling Racks', 'Available', '2025-01-13 09:15:54', NULL, NULL),
(9, 'Baking Pans', 'In Use', '2025-01-13 09:33:07', NULL, NULL),
(10, 'Stand Mixer', 'In Use', '2025-01-13 09:37:33', NULL, NULL),
(11, 'Hand Tools', 'Available', '2025-01-13 08:29:24', NULL, NULL);

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
(20, 111, 111, '3', 2, 8, '2025-01-08', 5, NULL, '2025-01-13 16:57:40', '2025-01-13 16:57:40'),
(22, 1000, 95, '2', 9, 2, '2025-01-14', 5, NULL, '2025-01-13 19:42:13', '2025-01-13 19:42:13'),
(23, 2222, 152, '3', 6, 4, '2025-01-16', 5, NULL, '2025-01-13 19:42:30', '2025-01-13 19:42:30');

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
(1, 'Pandan Kaya Cake', '1. Preheat oven to 350°F (180°C).\n2. Blend pandan leaves with water and strain to extract juice.\n3. Mix dry ingredients in a bowl (flour, sugar, baking powder, salt).\n4. In a separate bowl, combine coconut milk, pandan juice, oil, and eggs.\n5. Gradually mix the wet ingredients into the dry ingredients.\n6. Pour batter into a greased cake pan and bake for 25-30 minutes.\n7. Once cool, spread kaya between layers or on top.', 'Industrial Mixer', NULL, 5, '2025-01-13 17:25:51', '2025-01-13 17:30:46'),
(2, 'Kuih Bahulu', '1. Preheat oven to 375°F (190°C).\n2. Whisk eggs and sugar until pale and fluffy.\n3. Fold in sifted flour and vanilla extract gently.\n4. Grease kuih bahulu molds and fill them with batter.\n5. Bake for 10-15 minutes until golden.', 'Baking Pans', NULL, 5, '2025-01-13 17:25:51', '2025-01-13 17:33:07'),
(3, 'Coconut Buns (Roti Kelapa)', '1. Mix bread flour, yeast, sugar, and salt in a bowl.\n2. Add coconut milk and egg, knead until dough forms.\n3. Knead in butter until smooth and elastic.\n4. Let dough proof for 1 hour until doubled.\n5. Mix grated coconut and brown sugar for the filling.\n6. Divide dough into balls, fill with coconut filling, and shape.\n7. Let buns proof for 30 minutes, then bake at 375°F (190°C) for 15-20 minutes.', 'Commercial Oven', NULL, 5, '2025-01-13 17:25:51', '2025-01-13 17:34:41'),
(4, 'Pineapple Tarts (Tart Nanas)', '1. Mix flour, sugar, and butter until crumbly.\n2. Add egg yolk and cold water, knead until a dough forms.\n3. Chill dough for 30 minutes.\n4. Roll out dough and cut into small circles or rectangles.\n5. Add pineapple jam and shape into tarts.\n6. Bake at 350°F (180°C) for 15-20 minutes.', 'Dough Sheeter', NULL, 5, '2025-01-13 17:25:51', '2025-01-13 17:35:57'),
(5, 'Apam Balik', '1. Mix flour, baking powder, and sugar in a bowl.\n2. Add egg and milk, whisking until smooth.\n3. Heat a non-stick pan over medium heat.\n4. Pour batter to form a thick pancake.\n5. Sprinkle crushed peanuts, sugar, and creamed corn on top.\n6. Fold pancake in half and serve warm.', 'Stand Mixer', NULL, 5, '2025-01-13 17:25:51', '2025-01-13 17:37:33'),
(8, 'Chocolate Ganache Cupcakes', '1. Preheat oven to 350°F (180°C).\n2. Combine dry ingredients (flour, sugar, cocoa powder, baking powder) in a bowl.\n3. Mix wet ingredients (eggs, milk, melted butter) in a separate bowl.\n4. Gradually combine wet and dry mixtures.\n5. Pour batter into cupcake liners and bake for 18-20 minutes.\n6. For ganache: Heat heavy cream and pour over chocolate chips, stirring until smooth.\n7. Let cupcakes cool, then frost with ganache.', 'Oven 1', 5, 5, '2025-01-13 00:46:49', '2025-01-13 00:46:49'),
(9, 'Sourdough Loaf', '1. Mix flour, water, and sourdough starter in a bowl and let it autolyse for 30 minutes.\n2. Add salt and knead until smooth.\n3. Perform stretch-and-folds over 4 hours, every 30 minutes.\n4. Shape dough into a loaf and place it in a proofing basket.\n5. Let it proof overnight in the fridge.\n6. Preheat oven with a Dutch oven inside to 475°F (245°C).\n7. Transfer dough to oven, score, and bake covered for 20 minutes, then uncovered for another 20 minutes.', 'Mixer A', 5, 5, '2025-01-13 00:59:26', '2025-01-13 00:59:26');

--
-- Triggers `recipe_db`
--
DELIMITER $$
CREATE TRIGGER `update_equipment_status` AFTER INSERT ON `recipe_db` FOR EACH ROW BEGIN
    UPDATE equipment_status
    SET status = 'In Use'
    WHERE equipment_name = NEW.equipment_tbl;
END
$$
DELIMITER ;
DELIMITER $$
CREATE TRIGGER `update_equipment_status_on_update` AFTER UPDATE ON `recipe_db` FOR EACH ROW BEGIN
    IF OLD.equipment_tbl != NEW.equipment_tbl THEN
        UPDATE equipment_status
        SET status = 'Available'
        WHERE equipment_name = OLD.equipment_tbl
        AND NOT EXISTS (
            SELECT 1 FROM recipe_db 
            WHERE equipment_tbl = OLD.equipment_tbl 
            AND recipe_id != NEW.recipe_id
        );
        
        UPDATE equipment_status
        SET status = 'In Use'
        WHERE equipment_name = NEW.equipment_tbl;
    END IF;
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
(94, 1, 'All-purpose flour', 3.00, 'cups'),
(95, 1, 'Granulated sugar', 1.00, 'cups'),
(96, 1, 'Baking powder', 1.00, 'tsp'),
(97, 1, 'Salt', 1.00, 'tsp'),
(98, 1, 'Coconut milk', 1.00, 'cups'),
(99, 2, 'All-purpose flour', 1.00, 'cups'),
(100, 2, 'Eggs', 4.00, 'pcs'),
(101, 2, 'Granulated sugar', 1.00, 'cups'),
(102, 2, 'Vanilla extract', 1.00, 'tsp'),
(103, 3, 'Bread flour', 3.00, 'cups'),
(104, 3, 'Instant yeast', 2.00, 'tsp'),
(105, 3, 'Granulated sugar', 3.00, 'tbsp'),
(106, 3, 'Coconut milk', 1.00, 'cups'),
(107, 4, 'All-purpose flour', 2.00, 'cups'),
(108, 4, 'Unsalted butter', 1.00, 'cups'),
(109, 4, 'Egg yolk', 1.00, 'pcs'),
(110, 4, 'Granulated sugar', 2.00, 'tbsp'),
(111, 4, 'Cold water', 2.00, 'tbsp'),
(112, 4, 'Pineapple jam', 1.00, 'cups'),
(113, 5, 'All-purpose flour', 1.00, 'cups'),
(114, 5, 'Baking powder', 1.00, 'tsp'),
(115, 5, 'Sugar', 2.00, 'tbsp'),
(116, 5, 'Egg', 1.00, 'pcs'),
(117, 5, 'Milk', 1.00, 'cups'),
(118, 5, 'Crushed peanuts', 1.00, 'cups'),
(119, 5, 'Granulated sugar', 1.00, 'cups');

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
  MODIFY `admin_id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT for table `equipment_status`
--
ALTER TABLE `equipment_status`
  MODIFY `equipment_id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=12;

--
-- AUTO_INCREMENT for table `production_db`
--
ALTER TABLE `production_db`
  MODIFY `production_id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=24;

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
  MODIFY `recipe_id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=20;

--
-- AUTO_INCREMENT for table `recipe_ingredients`
--
ALTER TABLE `recipe_ingredients`
  MODIFY `ingredient_id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=121;

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
