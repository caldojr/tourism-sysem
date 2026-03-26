CREATE DATABASE IF NOT EXISTS zanzibar_admin;
USE zanzibar_admin;

CREATE TABLE `admin_posts` (
  `id` int(11) NOT NULL,
  `category` enum('hotel','transport','safari') NOT NULL,
  `vehicle_type` varchar(50) DEFAULT NULL,
  `region` enum('tanzania','zanzibar') NOT NULL,
  `title` varchar(255) NOT NULL,
  `description` text NOT NULL,
  `capacity` varchar(20) DEFAULT NULL,
  `luggage` varchar(20) DEFAULT NULL,
  `price_per_day` decimal(10,2) DEFAULT NULL,
  `duration` varchar(50) DEFAULT NULL,
  `national_park` varchar(100) DEFAULT NULL,
  `max_people` int(11) DEFAULT NULL,
  `accommodation_type` varchar(100) DEFAULT NULL,
  `price` decimal(10,2) DEFAULT NULL,
  `image_path` varchar(500) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `deleted` tinyint(1) DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

CREATE TABLE `admin_post_images` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `post_id` int(11) NOT NULL,
  `image_path` varchar(500) NOT NULL,
  `sort_order` int(11) NOT NULL DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `idx_post_images_post_id` (`post_id`),
  KEY `idx_post_images_sort` (`post_id`,`sort_order`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;


INSERT INTO `admin_posts` (`id`, `category`, `vehicle_type`, `region`, `title`, `description`, `capacity`, `luggage`, `price_per_day`, `duration`, `national_park`, `max_people`, `accommodation_type`, `price`, `image_path`, `created_at`, `deleted`) VALUES
(1, 'safari', NULL, 'tanzania', 'manyara', 'welcom ata manyara', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'uploads/68dbffb09b81a.png', '2025-09-30 16:05:04', 1),
(2, 'hotel', NULL, 'tanzania', 'zanz hots', 'welcom', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'uploads/68dc067e1c242.jpg', '2025-09-30 16:34:06', 0),
(3, 'hotel', NULL, 'zanzibar', 'hots', 'wellll', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'uploads/68dc06ee25852.jpg', '2025-09-30 16:35:58', 0),
(4, 'hotel', NULL, 'zanzibar', 'hots', 'karibuni nyotee katika hotel yetu', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'uploads/68dc073432376.jpg', '2025-09-30 16:37:08', 0),
(6, 'transport', NULL, 'zanzibar', 'four by four', 'weeeel', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'uploads/68dc2fb71d214.jpg', '2025-09-30 19:29:59', 1),
(7, 'transport', NULL, 'zanzibar', 'cruser', 'utalii unyamaaa', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'uploads/68dc2fd579214.jpg', '2025-09-30 19:30:29', 1),
(8, 'safari', NULL, 'tanzania', 'ngorongoro', 'karibu mje muonee crater', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'uploads/68dc563e2c9e8.jpg', '2025-09-30 22:14:22', 0),
(9, 'safari', NULL, 'tanzania', 'kilimanjaro', 'mlima mkubwa tanzania', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'uploads/68dc565c37222.jpg', '2025-09-30 22:14:52', 0);


CREATE TABLE `admin_users` (
  `id` int(11) NOT NULL,
  `full_name` varchar(100) NOT NULL,
  `email` varchar(100) NOT NULL,
  `country` varchar(50) NOT NULL,
  `phone_number` varchar(20) NOT NULL,
  `gender` enum('Male','Female','Other') NOT NULL,
  `password` varchar(255) NOT NULL,
  `reset_token` varchar(100) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

INSERT INTO `admin_users` (`id`, `full_name`, `email`, `country`, `phone_number`, `gender`, `password`, `reset_token`, `created_at`) VALUES
(1, 'admin admin admin', 'admin@gmail.com', 'United States', '0714343162', 'Male', '$2y$10$SLvtKWSq3O/orLLdFRI3vuPUKrOPJxH.CmN5E0VgXIT3TEisguPVO', NULL, '2025-09-30 15:11:33');

INSERT INTO `admin_users` (`full_name`, `email`, `country`, `phone_number`, `gender`, `password`) VALUES
('caldo', 'caldo@gmail.com', 'Tanzania', '0620144829', 'Male', 'caldo@1234');


CREATE TABLE `bookings` (
  `id` int(11) NOT NULL,
  `full_name` varchar(255) NOT NULL,
  `email` varchar(255) NOT NULL,
  `phone_number` varchar(20) NOT NULL,
  `start_date` date NOT NULL,
  `end_date` date NOT NULL,
  `region` enum('tanzania','zanzibar') NOT NULL,
  `selected_safaris` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`selected_safaris`)),
  `selected_hotels` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`selected_hotels`)),
  `selected_transports` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`selected_transports`)),
  `total_travelers` int(11) NOT NULL,
  `special_requests` text DEFAULT NULL,
  `status` enum('pending','approved','cancelled') DEFAULT 'pending',
  `total_price` decimal(10,2) DEFAULT NULL,
  `hotel_room_number` varchar(50) DEFAULT NULL,
  `admin_notes` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;


INSERT INTO `bookings` (`id`, `full_name`, `email`, `phone_number`, `start_date`, `end_date`, `region`, `selected_safaris`, `selected_hotels`, `selected_transports`, `total_travelers`, `special_requests`, `status`, `total_price`, `hotel_room_number`, `admin_notes`, `created_at`, `updated_at`) VALUES
(2, 'jumanne admin kafunzi', 'tz@gmail.com', '0714313167', '2025-10-02', '2025-10-16', 'tanzania', '[\"9\",\"8\"]', '[\"2\"]', '[]', 1, '', 'approved', 100000.00, '23', 'NAOMBA TUWASILIANE', '2025-10-01 00:16:36', '2025-10-01 01:17:35'),
(3, 'RASHID ATHUMANI KILANDA', 'rashidkilanda225@gmail.com', '0619690638', '2025-10-02', '2025-10-10', 'zanzibar', '[]', '[\"4\"]', '[]', 2, 'NATAKA HOTEL TU', 'cancelled', 0.00, '', 'SUBILI', '2025-10-01 01:20:06', '2025-10-01 01:21:04');



CREATE TABLE `notifications` (
  `id` int(11) NOT NULL,
  `message` text NOT NULL,
  `is_active` tinyint(1) DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;



INSERT INTO `notifications` (`id`, `message`, `is_active`, `created_at`) VALUES
(1, 'MAMBO SAFI', 1, '2025-10-01 01:16:43'),
(2, 'NAFASI ZIPO', 1, '2025-10-01 01:17:25');


ALTER TABLE `admin_posts`
  ADD PRIMARY KEY (`id`);


ALTER TABLE `admin_users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `email` (`email`);


ALTER TABLE `bookings`
  ADD PRIMARY KEY (`id`);

ALTER TABLE `notifications`
  ADD PRIMARY KEY (`id`);

ALTER TABLE `admin_posts`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=10;

ALTER TABLE `admin_users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;


ALTER TABLE `bookings`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;


ALTER TABLE `notifications`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;
COMMIT;

