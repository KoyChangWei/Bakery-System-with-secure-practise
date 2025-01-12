-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- 主机： 127.0.0.1:3306
-- 生成日期： 2024-12-27 09:56:18
-- 服务器版本： 10.4.32-MariaDB
-- PHP 版本： 8.2.12

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- 数据库： `bakery_db`
--

-- --------------------------------------------------------

--
-- 表的结构 `admin_db`
--

CREATE TABLE `admin_db` (
  `name_tbl` varchar(255) NOT NULL,
  `gender_tbl` varchar(255) NOT NULL,
  `role_tbl` varchar(255) NOT NULL,
  `email_tbl` varchar(255) NOT NULL,
  `password_tbl` varchar(255) NOT NULL,
  `admin_id` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- 转存表中的数据 `admin_db`
--

INSERT INTO `admin_db` (`name_tbl`, `gender_tbl`, `role_tbl`, `email_tbl`, `password_tbl`, `admin_id`) VALUES
('koy', 'male', 'baker', 'k@baker.com', '$2y$10$dXBjEsPg0gZYT.fUPkFIWOOJNEmc5X1MgwIpbwLgh5uCaUk1QwvuO', 1),
('kcw', 'male', 'supervisor', 'a@supervisor.com', '$2y$10$P/CcnmLo7z8Qe7Bubn5n8O9gbQ4haMBDv3Ln/k8zrq2nJtotwam2e', 2),
('legend', 'male', 'baker', 'abc@baker.com', '$2y$10$ihP61M/zPu5RQ0utpipSxOfM21HjzOzeQhFzsNykGbyPAks0TqNo2', 3),
('ggg', 'female', 'supervisor', 'aaa@supervisor.com', '$2y$10$c5IpxLl.p7ShMaWHwrmSYOjx2x1ZVymMD6tBobugOM7p1QkitUHPa', 4);

-- --------------------------------------------------------

--
-- 表的结构 `batch_db`
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
-- 转存表中的数据 `batch_db`
--

INSERT INTO `batch_db` (`batch_no_tbl`, `startDate_tbl`, `endDate_tbl`, `production_stage_tbl`, `quality_check_tbl`, `status_tbl`) VALUES
('1222', '2024-12-26 19:42:00', '2024-12-26 02:43:00', 'preparation', 'good1', 'Completed'),
('1233333333', '2024-12-10 15:09:00', '2024-12-30 15:09:00', 'preparation', 'Temperature: 23°C\nMoisture: 22%\nWeight: 100g\nVisual Checks: texture\nNotes: good', 'Scheduled'),
('2147483647', '2024-12-03 02:53:00', '2025-01-09 02:54:00', 'cooling', 'kkk', 'In Progress'),
('6666', '2024-12-06 02:46:00', '2024-12-28 02:46:00', 'preparation', '6', 'Completed'),
('666633777', '2024-12-18 02:53:00', '2024-12-13 02:53:00', 'baking', 'k', 'Scheduled'),
('66667777777777777', '2024-12-11 15:05:00', '2024-12-28 15:05:00', 'preparation', '', 'In Progress'),
('6669', '2024-12-02 03:15:00', '2024-12-29 03:16:00', 'mixing', 'hh', 'Completed'),
('asd1', '2024-12-05 16:18:00', '2024-12-31 19:15:00', 'mixing', 'Temperature: 22°C\nMoisture: 21%\nWeight: 12g\nVisual Checks: texture\nNotes: good', 'Scheduled');

-- --------------------------------------------------------

--
-- 表的结构 `production_db`
--

CREATE TABLE `production_db` (
  `production_id` int(255) NOT NULL,
  `order_volumn_tbl` int(255) NOT NULL,
  `capacity_tbl` int(255) NOT NULL,
  `staff_availability_tbl` varchar(255) NOT NULL,
  `equipment_status_tbl` varchar(255) NOT NULL,
  `created_by` int(255) NOT NULL,
  `updated_by` int(255) NOT NULL,
  `created_at` datetime NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- 转存表中的数据 `production_db`
--

INSERT INTO `production_db` (`production_id`, `order_volumn_tbl`, `capacity_tbl`, `staff_availability_tbl`, `equipment_status_tbl`, `created_by`, `updated_by`, `created_at`) VALUES
(2, 122, 123, 'ddd', 'operational', 0, 2, '2024-12-27 15:41:22');

-- --------------------------------------------------------

--
-- 表的结构 `recipe_db`
--

CREATE TABLE `recipe_db` (
  `ingredient_id` int(255) NOT NULL,
  `ingredient_name_tbl` varchar(255) NOT NULL,
  `quantity_tbl` int(255) NOT NULL,
  `preparation_step_tbl` varchar(255) NOT NULL,
  `equipment_tbl` varchar(255) NOT NULL,
  `created_by` int(255) NOT NULL,
  `updated_by` int(255) NOT NULL,
  `created_at` datetime NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- 转存表中的数据 `recipe_db`
--

INSERT INTO `recipe_db` (`ingredient_id`, `ingredient_name_tbl`, `quantity_tbl`, `preparation_step_tbl`, `equipment_tbl`, `created_by`, `updated_by`, `created_at`) VALUES
(2, 'beautiful rotikk', 2, 'dd', 'dd', 2, 2, '2024-12-27 15:31:57'),
(3, 'beautiful rotikkgg', 4, 'ccd', 'sfdd', 2, 2, '2024-12-27 16:02:02');

--
-- 转储表的索引
--

--
-- 表的索引 `admin_db`
--
ALTER TABLE `admin_db`
  ADD PRIMARY KEY (`admin_id`);

--
-- 表的索引 `batch_db`
--
ALTER TABLE `batch_db`
  ADD PRIMARY KEY (`batch_no_tbl`);

--
-- 表的索引 `production_db`
--
ALTER TABLE `production_db`
  ADD PRIMARY KEY (`production_id`);

--
-- 表的索引 `recipe_db`
--
ALTER TABLE `recipe_db`
  ADD PRIMARY KEY (`ingredient_id`);

--
-- 在导出的表使用AUTO_INCREMENT
--

--
-- 使用表AUTO_INCREMENT `admin_db`
--
ALTER TABLE `admin_db`
  MODIFY `admin_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- 使用表AUTO_INCREMENT `production_db`
--
ALTER TABLE `production_db`
  MODIFY `production_id` int(255) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- 使用表AUTO_INCREMENT `recipe_db`
--
ALTER TABLE `recipe_db`
  MODIFY `ingredient_id` int(255) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
