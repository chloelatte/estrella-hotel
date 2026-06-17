-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Jun 17, 2026 at 02:46 PM
-- Server version: 10.4.32-MariaDB
-- PHP Version: 8.0.30

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `estrella_hotel`
--

DELIMITER $$
--
-- Functions
--
CREATE DEFINER=`root`@`localhost` FUNCTION `fn_calculate_total` (`p_room_price` DECIMAL(15,2), `p_nights` INT, `p_rooms` INT, `p_addon_total` DECIMAL(15,2)) RETURNS DECIMAL(15,2) DETERMINISTIC BEGIN
    DECLARE v_room_total  DECIMAL(15,2);
    DECLARE v_service     DECIMAL(15,2);
    DECLARE v_tax         DECIMAL(15,2);
    DECLARE v_grand_total DECIMAL(15,2);

    SET v_room_total  = p_room_price * p_nights * p_rooms;
    SET v_service     = v_room_total * 0.10;
    SET v_tax         = v_room_total * 0.10;
    SET v_grand_total = v_room_total + v_service + v_tax + p_addon_total;

    RETURN v_grand_total;
END$$

CREATE DEFINER=`root`@`localhost` FUNCTION `fn_generate_booking_code` (`p_date` DATE) RETURNS VARCHAR(30) CHARSET utf8mb4 COLLATE utf8mb4_unicode_ci DETERMINISTIC BEGIN
    DECLARE v_code VARCHAR(30);
    SET v_code = CONCAT(
        'EST-',
        DATE_FORMAT(p_date, '%y%m%d'),
        '-',
        LPAD(FLOOR(RAND() * 9999 + 1), 4, '0')
    );
    RETURN v_code;
END$$

DELIMITER ;

-- --------------------------------------------------------

--
-- Table structure for table `amenities`
--

CREATE TABLE `amenities` (
  `id` int(11) NOT NULL,
  `name` varchar(100) NOT NULL,
  `icon` varchar(100) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `amenities`
--

INSERT INTO `amenities` (`id`, `name`, `icon`, `created_at`) VALUES
(1, 'Free Wi-Fi', 'bi-wifi', '2026-06-17 04:06:53'),
(2, 'Air Conditioning', 'bi-thermometer-half', '2026-06-17 04:06:53'),
(3, 'Ocean View', 'bi-water', '2026-06-17 04:06:53'),
(4, 'Smart TV', 'bi-tv', '2026-06-17 04:06:53'),
(5, 'Minibar', 'bi-cup-straw', '2026-06-17 04:06:53'),
(6, 'Private Balcony', 'bi-door-open', '2026-06-17 04:06:53'),
(7, 'Room Service', 'bi-bell', '2026-06-17 04:06:53'),
(8, 'Safety Box', 'bi-shield-lock', '2026-06-17 04:06:53'),
(9, 'Bath Amenities', 'bi-droplet', '2026-06-17 04:06:53'),
(10, 'Hair Dryer', 'bi-wind', '2026-06-17 04:06:53'),
(11, 'Bathrobe & Slippers', 'bi-star', '2026-06-17 04:06:53'),
(12, 'Daily Housekeeping', 'bi-house-heart', '2026-06-17 04:06:53'),
(13, 'Coffee & Tea Maker', 'bi-cup-hot', '2026-06-17 04:06:53'),
(14, 'Workspace Desk', 'bi-briefcase', '2026-06-17 04:06:53');

-- --------------------------------------------------------

--
-- Table structure for table `reservations`
--

CREATE TABLE `reservations` (
  `id` int(11) NOT NULL,
  `booking_code` varchar(30) NOT NULL,
  `user_id` int(11) DEFAULT NULL,
  `room_id` int(11) NOT NULL,
  `guest_name` varchar(100) NOT NULL,
  `guest_email` varchar(100) NOT NULL,
  `guest_phone` varchar(20) NOT NULL,
  `check_in` date NOT NULL,
  `check_out` date NOT NULL,
  `guests` int(11) NOT NULL DEFAULT 2,
  `rooms_count` int(11) NOT NULL DEFAULT 1,
  `room_price` decimal(15,2) NOT NULL,
  `addon_total` decimal(15,2) DEFAULT 0.00,
  `service_charge` decimal(15,2) DEFAULT 0.00,
  `tax` decimal(15,2) DEFAULT 0.00,
  `total_price` decimal(15,2) NOT NULL,
  `special_request` text DEFAULT NULL,
  `payment_method` varchar(50) DEFAULT NULL,
  `payment_status` enum('pending','paid','cancelled') DEFAULT 'pending',
  `status` enum('pending','confirmed','cancelled','completed') DEFAULT 'pending',
  `addons_json` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `reservations`
--

INSERT INTO `reservations` (`id`, `booking_code`, `user_id`, `room_id`, `guest_name`, `guest_email`, `guest_phone`, `check_in`, `check_out`, `guests`, `rooms_count`, `room_price`, `addon_total`, `service_charge`, `tax`, `total_price`, `special_request`, `payment_method`, `payment_status`, `status`, `addons_json`, `created_at`, `updated_at`) VALUES
(1, 'EST-250601-0001', 2, 2, 'Sophie Laurent', 'sophie@example.com', '+62 812 3456 7890', '2025-06-01', '2025-06-04', 2, 1, 1800000.00, 900000.00, 540000.00, 540000.00, 6780000.00, NULL, 'credit_card', 'paid', 'completed', NULL, '2026-06-17 04:06:53', '2026-06-17 04:06:53'),
(2, 'EST-250610-0042', 3, 3, 'Marco Bellini', 'marco@example.com', '+62 821 9876 5432', '2025-06-10', '2025-06-13', 2, 1, 2500000.00, 0.00, 750000.00, 750000.00, 9000000.00, NULL, 'bank_transfer', 'paid', 'completed', NULL, '2026-06-17 04:06:53', '2026-06-17 04:06:53'),
(3, 'EST-250615-0017', 4, 1, 'Isabelle Dubois', 'isabelle@example.com', '+62 813 5555 1234', '2025-06-15', '2025-06-17', 1, 1, 1200000.00, 300000.00, 240000.00, 240000.00, 2980000.00, NULL, 'ewallet', 'paid', 'confirmed', NULL, '2026-06-17 04:06:53', '2026-06-17 04:06:53'),
(4, 'EST-260620-0088', 5, 4, 'Alexandre Moreau', 'alex@example.com', '+62 878 1111 2222', '2026-06-20', '2026-06-25', 4, 1, 5000000.00, 1500000.00, 1500000.00, 1500000.00, 29500000.00, NULL, 'credit_card', 'pending', 'pending', NULL, '2026-06-17 04:06:53', '2026-06-17 04:06:53'),
(5, 'EST-260625-0099', 2, 2, 'Sophie Laurent', 'sophie@example.com', '+62 812 3456 7890', '2026-06-25', '2026-06-28', 2, 1, 1800000.00, 650000.00, 540000.00, 540000.00, 7330000.00, NULL, 'credit_card', 'pending', 'confirmed', NULL, '2026-06-17 04:06:53', '2026-06-17 04:06:53'),
(6, 'EST-260701-0111', 3, 1, 'Marco Bellini', 'marco@example.com', '+62 821 9876 5432', '2026-07-01', '2026-07-03', 2, 1, 1200000.00, 0.00, 240000.00, 240000.00, 2880000.00, NULL, 'pay_hotel', 'pending', 'pending', NULL, '2026-06-17 04:06:53', '2026-06-17 04:06:53');

--
-- Triggers `reservations`
--
DELIMITER $$
CREATE TRIGGER `trg_after_reservation_insert` AFTER INSERT ON `reservations` FOR EACH ROW BEGIN
    INSERT INTO reservation_logs (reservation_id, action, new_status)
    VALUES (NEW.id, 'INSERT', NEW.status);
END
$$
DELIMITER ;
DELIMITER $$
CREATE TRIGGER `trg_after_reservation_update` AFTER UPDATE ON `reservations` FOR EACH ROW BEGIN
    IF OLD.status <> NEW.status THEN
        INSERT INTO reservation_logs (reservation_id, action, old_status, new_status)
        VALUES (NEW.id, 'STATUS_CHANGE', OLD.status, NEW.status);
    END IF;
END
$$
DELIMITER ;

-- --------------------------------------------------------

--
-- Table structure for table `reservation_logs`
--

CREATE TABLE `reservation_logs` (
  `id` int(11) NOT NULL,
  `reservation_id` int(11) NOT NULL,
  `action` varchar(20) NOT NULL,
  `old_status` varchar(30) DEFAULT NULL,
  `new_status` varchar(30) DEFAULT NULL,
  `logged_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `reservation_logs`
--

INSERT INTO `reservation_logs` (`id`, `reservation_id`, `action`, `old_status`, `new_status`, `logged_at`) VALUES
(1, 1, 'INSERT', NULL, 'completed', '2026-06-17 04:06:53'),
(2, 2, 'INSERT', NULL, 'completed', '2026-06-17 04:06:53'),
(3, 3, 'INSERT', NULL, 'confirmed', '2026-06-17 04:06:53'),
(4, 4, 'INSERT', NULL, 'pending', '2026-06-17 04:06:53'),
(5, 5, 'INSERT', NULL, 'confirmed', '2026-06-17 04:06:53'),
(6, 6, 'INSERT', NULL, 'pending', '2026-06-17 04:06:53'),
(7, 1, 'INSERT', NULL, 'pending', '2026-06-17 04:06:53'),
(8, 2, 'INSERT', NULL, 'pending', '2026-06-17 04:06:53'),
(9, 3, 'INSERT', NULL, 'pending', '2026-06-17 04:06:53'),
(10, 4, 'INSERT', NULL, 'pending', '2026-06-17 04:06:53'),
(11, 5, 'INSERT', NULL, 'pending', '2026-06-17 04:06:53'),
(12, 6, 'INSERT', NULL, 'pending', '2026-06-17 04:06:53'),
(13, 1, 'STATUS_CHANGE', 'pending', 'confirmed', '2026-06-17 04:06:53'),
(14, 1, 'STATUS_CHANGE', 'confirmed', 'completed', '2026-06-17 04:06:53'),
(15, 2, 'STATUS_CHANGE', 'pending', 'confirmed', '2026-06-17 04:06:53'),
(16, 2, 'STATUS_CHANGE', 'confirmed', 'completed', '2026-06-17 04:06:53'),
(17, 3, 'STATUS_CHANGE', 'pending', 'confirmed', '2026-06-17 04:06:53'),
(18, 5, 'STATUS_CHANGE', 'pending', 'confirmed', '2026-06-17 04:06:53');

-- --------------------------------------------------------

--
-- Table structure for table `rooms`
--

CREATE TABLE `rooms` (
  `id` int(11) NOT NULL,
  `name` varchar(100) NOT NULL,
  `type` enum('standard','deluxe','executive','presidential') NOT NULL DEFAULT 'standard',
  `description` text DEFAULT NULL,
  `price_per_night` decimal(15,2) NOT NULL,
  `capacity` int(11) NOT NULL DEFAULT 2,
  `size_sqm` int(11) DEFAULT NULL,
  `view_type` varchar(80) DEFAULT NULL,
  `bed_type` varchar(80) DEFAULT NULL,
  `is_available` tinyint(1) DEFAULT 1,
  `image_url` varchar(255) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `rooms`
--

INSERT INTO `rooms` (`id`, `name`, `type`, `description`, `price_per_night`, `capacity`, `size_sqm`, `view_type`, `bed_type`, `is_available`, `image_url`, `created_at`, `updated_at`) VALUES
(1, 'Standard Room', 'standard', 'A cozy and comfortable room with modern amenities for a relaxing stay. Features elegant coastal décor and all the essentials for a pleasant overnight experience.', 1200000.00, 2, 24, 'Garden View', '1 Queen Bed', 1, 'https://images.unsplash.com/photo-1631049307264-da0ec9d70304?w=700&q=80', '2026-06-17 04:06:53', '2026-06-17 04:06:53'),
(2, 'Deluxe Room', 'deluxe', 'Experience refined comfort in our Deluxe Room, featuring elegant interiors, modern amenities, and a private balcony with stunning ocean views. Perfect for couples.', 1800000.00, 2, 32, 'Ocean View', '1 King Bed / 2 Twin Beds', 1, 'https://images.unsplash.com/photo-1618773928121-c32242e63f39?w=700&q=80', '2026-06-17 04:06:53', '2026-06-17 04:06:53'),
(3, 'Executive Room', 'executive', 'Enjoy extra space, a private seating area, and premium ocean-view facilities. Perfect for business travelers and couples seeking a luxurious retreat.', 2500000.00, 3, 40, 'Premium Ocean View', '1 King Bed', 1, 'https://images.unsplash.com/photo-1582719478250-c89cae4dc85b?w=700&q=80', '2026-06-17 04:06:53', '2026-06-17 04:06:53'),
(4, 'Presidential Room', 'presidential', 'The pinnacle of luxury with expansive space, exclusive services, and breathtaking panoramic ocean views. Unmatched elegance for the most discerning guests.', 5000000.00, 4, 60, 'Panoramic Ocean View', '1 King Bed + Living Area', 1, 'https://images.unsplash.com/photo-1578683010236-d716f9a3f461?w=700&q=80', '2026-06-17 04:06:53', '2026-06-17 04:06:53');

-- --------------------------------------------------------

--
-- Table structure for table `room_amenities`
--

CREATE TABLE `room_amenities` (
  `id` int(11) NOT NULL,
  `room_id` int(11) NOT NULL,
  `amenity_id` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `room_amenities`
--

INSERT INTO `room_amenities` (`id`, `room_id`, `amenity_id`) VALUES
(1, 1, 1),
(2, 1, 2),
(3, 1, 4),
(4, 1, 7),
(5, 1, 9),
(6, 1, 12),
(7, 1, 13),
(8, 2, 1),
(9, 2, 2),
(10, 2, 3),
(11, 2, 4),
(12, 2, 5),
(13, 2, 6),
(14, 2, 7),
(15, 2, 8),
(16, 2, 9),
(17, 2, 10),
(18, 2, 12),
(19, 2, 13),
(20, 3, 1),
(21, 3, 2),
(22, 3, 3),
(23, 3, 4),
(24, 3, 5),
(25, 3, 6),
(26, 3, 7),
(27, 3, 8),
(28, 3, 9),
(29, 3, 10),
(30, 3, 11),
(31, 3, 12),
(32, 3, 13),
(33, 3, 14),
(34, 4, 1),
(35, 4, 2),
(36, 4, 3),
(37, 4, 4),
(38, 4, 5),
(39, 4, 6),
(40, 4, 7),
(41, 4, 8),
(42, 4, 9),
(43, 4, 10),
(44, 4, 11),
(45, 4, 12),
(46, 4, 13),
(47, 4, 14);

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` int(11) NOT NULL,
  `full_name` varchar(100) NOT NULL,
  `email` varchar(100) NOT NULL,
  `username` varchar(50) NOT NULL,
  `password` varchar(255) NOT NULL,
  `phone` varchar(20) DEFAULT NULL,
  `role` enum('admin','guest') DEFAULT 'guest',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `full_name`, `email`, `username`, `password`, `phone`, `role`, `created_at`, `updated_at`) VALUES
(1, 'Administrator', 'admin@estrella.com', 'admin', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', '+62 21 6017 8120', 'admin', '2026-06-17 04:06:53', '2026-06-17 04:06:53'),
(2, 'Sophie Laurent', 'sophie@example.com', 'guest1', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', '+62 812 3456 7890', 'guest', '2026-06-17 04:06:53', '2026-06-17 12:21:00'),
(3, 'Marco Bellini', 'marco@example.com', 'guest2', '$2y$10$TKh8H1.PpuAjmi.h6/M8.uRlFp0wJuHmZqGp3nIsBVh6OvC5FEjUG', '+62 821 9876 5432', 'guest', '2026-06-17 04:06:53', '2026-06-17 04:06:53'),
(4, 'Isabelle Dubois', 'isabelle@example.com', 'guest3', '$2y$10$TKh8H1.PpuAjmi.h6/M8.uRlFp0wJuHmZqGp3nIsBVh6OvC5FEjUG', '+62 813 5555 1234', 'guest', '2026-06-17 04:06:53', '2026-06-17 04:06:53'),
(5, 'Alexandre Moreau', 'alex@example.com', 'guest4', '$2y$10$TKh8H1.PpuAjmi.h6/M8.uRlFp0wJuHmZqGp3nIsBVh6OvC5FEjUG', '+62 878 1111 2222', 'guest', '2026-06-17 04:06:53', '2026-06-17 04:06:53');

-- --------------------------------------------------------

--
-- Stand-in structure for view `v_reservation_summary`
-- (See below for the actual view)
--
CREATE TABLE `v_reservation_summary` (
`id` int(11)
,`booking_code` varchar(30)
,`check_in` date
,`check_out` date
,`nights` int(7)
,`guests` int(11)
,`total_price` decimal(15,2)
,`status` enum('pending','confirmed','cancelled','completed')
,`payment_status` enum('pending','paid','cancelled')
,`payment_method` varchar(50)
,`created_at` timestamp
,`room_name` varchar(100)
,`room_type` enum('standard','deluxe','executive','presidential')
,`price_per_night` decimal(15,2)
,`guest_name` varchar(100)
,`guest_email` varchar(100)
);

-- --------------------------------------------------------

--
-- Stand-in structure for view `v_room_availability`
-- (See below for the actual view)
--
CREATE TABLE `v_room_availability` (
`id` int(11)
,`name` varchar(100)
,`type` enum('standard','deluxe','executive','presidential')
,`price_per_night` decimal(15,2)
,`capacity` int(11)
,`is_available` tinyint(1)
,`active_bookings_today` bigint(21)
,`total_bookings_ever` bigint(21)
);

-- --------------------------------------------------------

--
-- Structure for view `v_reservation_summary`
--
DROP TABLE IF EXISTS `v_reservation_summary`;

CREATE ALGORITHM=UNDEFINED DEFINER=`root`@`localhost` SQL SECURITY DEFINER VIEW `v_reservation_summary`  AS SELECT `r`.`id` AS `id`, `r`.`booking_code` AS `booking_code`, `r`.`check_in` AS `check_in`, `r`.`check_out` AS `check_out`, to_days(`r`.`check_out`) - to_days(`r`.`check_in`) AS `nights`, `r`.`guests` AS `guests`, `r`.`total_price` AS `total_price`, `r`.`status` AS `status`, `r`.`payment_status` AS `payment_status`, `r`.`payment_method` AS `payment_method`, `r`.`created_at` AS `created_at`, `rm`.`name` AS `room_name`, `rm`.`type` AS `room_type`, `rm`.`price_per_night` AS `price_per_night`, `u`.`full_name` AS `guest_name`, `u`.`email` AS `guest_email` FROM ((`reservations` `r` join `rooms` `rm` on(`r`.`room_id` = `rm`.`id`)) left join `users` `u` on(`r`.`user_id` = `u`.`id`)) ;

-- --------------------------------------------------------

--
-- Structure for view `v_room_availability`
--
DROP TABLE IF EXISTS `v_room_availability`;

CREATE ALGORITHM=UNDEFINED DEFINER=`root`@`localhost` SQL SECURITY DEFINER VIEW `v_room_availability`  AS SELECT `rm`.`id` AS `id`, `rm`.`name` AS `name`, `rm`.`type` AS `type`, `rm`.`price_per_night` AS `price_per_night`, `rm`.`capacity` AS `capacity`, `rm`.`is_available` AS `is_available`, (select count(0) from `reservations` `res` where `res`.`room_id` = `rm`.`id` and `res`.`status` <> 'cancelled' and `res`.`check_in` <= curdate() and `res`.`check_out` > curdate()) AS `active_bookings_today`, (select count(0) from `reservations` `res` where `res`.`room_id` = `rm`.`id`) AS `total_bookings_ever` FROM `rooms` AS `rm` ;

--
-- Indexes for dumped tables
--

--
-- Indexes for table `amenities`
--
ALTER TABLE `amenities`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `reservations`
--
ALTER TABLE `reservations`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `booking_code` (`booking_code`),
  ADD KEY `room_id` (`room_id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `reservation_logs`
--
ALTER TABLE `reservation_logs`
  ADD PRIMARY KEY (`id`),
  ADD KEY `reservation_id` (`reservation_id`);

--
-- Indexes for table `rooms`
--
ALTER TABLE `rooms`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `room_amenities`
--
ALTER TABLE `room_amenities`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `uq_room_amenity` (`room_id`,`amenity_id`),
  ADD KEY `amenity_id` (`amenity_id`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `email` (`email`),
  ADD UNIQUE KEY `username` (`username`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `amenities`
--
ALTER TABLE `amenities`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=15;

--
-- AUTO_INCREMENT for table `reservations`
--
ALTER TABLE `reservations`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT for table `reservation_logs`
--
ALTER TABLE `reservation_logs`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=19;

--
-- AUTO_INCREMENT for table `rooms`
--
ALTER TABLE `rooms`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `room_amenities`
--
ALTER TABLE `room_amenities`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=48;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `reservations`
--
ALTER TABLE `reservations`
  ADD CONSTRAINT `reservations_ibfk_1` FOREIGN KEY (`room_id`) REFERENCES `rooms` (`id`),
  ADD CONSTRAINT `reservations_ibfk_2` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `reservation_logs`
--
ALTER TABLE `reservation_logs`
  ADD CONSTRAINT `reservation_logs_ibfk_1` FOREIGN KEY (`reservation_id`) REFERENCES `reservations` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `room_amenities`
--
ALTER TABLE `room_amenities`
  ADD CONSTRAINT `room_amenities_ibfk_1` FOREIGN KEY (`room_id`) REFERENCES `rooms` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `room_amenities_ibfk_2` FOREIGN KEY (`amenity_id`) REFERENCES `amenities` (`id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
