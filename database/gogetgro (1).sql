-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: May 03, 2026 at 06:37 AM
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
-- Database: `gogetgro`
--

-- --------------------------------------------------------

--
-- Table structure for table `admin_accounts`
--

CREATE TABLE `admin_accounts` (
  `id` int(11) NOT NULL,
  `username` varchar(60) NOT NULL,
  `display_name` varchar(120) NOT NULL,
  `role` enum('owner','staff') NOT NULL,
  `password_hash` varchar(255) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `admin_accounts`
--

INSERT INTO `admin_accounts` (`id`, `username`, `display_name`, `role`, `password_hash`, `created_at`) VALUES
(1, 'owner', 'Store Owner', 'owner', '$2y$10$28U98p./P4nyo4oPy0.omOzXunIxq9qSl/hUCOuJmUGUT9oO1usEq', '2026-04-24 14:35:33'),
(2, 'staff', 'Store Staff', 'staff', '$2y$10$riNwHqkbsDqdXSHg2GuIWePYZ/KU8ly4RgtcXLP5lEIEJDPrgSOMe', '2026-04-24 14:35:33');

-- --------------------------------------------------------

--
-- Table structure for table `cart_items`
--

CREATE TABLE `cart_items` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `product_id` int(11) NOT NULL,
  `quantity` int(11) NOT NULL DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `cart_items`
--

INSERT INTO `cart_items` (`id`, `user_id`, `product_id`, `quantity`, `created_at`) VALUES
(36, 2, 642, 6, '2026-05-02 03:38:28'),
(37, 2, 691, 6, '2026-05-02 03:38:41');

-- --------------------------------------------------------

--
-- Table structure for table `categories`
--

CREATE TABLE `categories` (
  `slug` varchar(80) NOT NULL,
  `name` varchar(120) NOT NULL,
  `icon_class` varchar(80) NOT NULL,
  `home_featured` tinyint(1) NOT NULL DEFAULT 0,
  `home_sort` int(11) NOT NULL DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `categories`
--

INSERT INTO `categories` (`slug`, `name`, `icon_class`, `home_featured`, `home_sort`) VALUES
('babies-kids', 'Babies & Kids', 'bi-gender-ambiguous', 0, 7),
('bakery', 'Bakery', 'bi-cup-hot', 0, 4),
('beverage', 'Beverage', 'bi-cup-straw', 1, 7),
('chilled-dairy', 'Chilled & Dairy Items', 'bi-thermometer-snow', 0, 3),
('diy-hardware', 'DIY/Hardware', 'bi-tools', 0, 10),
('fresh-meat-seafood', 'Fresh Meat & Seafood', 'bi-piggy-bank', 1, 6),
('fresh-produce', 'Fresh Produce', 'bi-brightness-high', 1, 5),
('frozen-goods', 'Frozen Goods', 'bi-snow', 1, 1),
('health-beauty', 'Health & Beauty', 'bi-heart-pulse', 1, 4),
('health-hygiene', 'Health & Hygiene Essentials', 'bi-shield-check', 1, 3),
('home-appliance', 'Home Appliance & Essentials', 'bi-tv', 0, 11),
('home-care', 'Home Care', 'bi-house-heart', 0, 8),
('international-goods', 'International Goods', 'bi-globe', 0, 5),
('only-in-gogetgro', 'Only in GoGetGro', 'bi-star', 0, 1),
('pantry', 'Pantry', 'bi-box-seam', 1, 2),
('pet-care', 'Pet Care', 'bi-heart', 0, 9),
('ready-to-cook', 'Ready to Cook', 'bi-fire', 0, 2),
('snacks', 'Snacks', 'bi-cookie', 0, 6);

-- --------------------------------------------------------

--
-- Table structure for table `orders`
--

CREATE TABLE `orders` (
  `id` int(10) UNSIGNED NOT NULL,
  `user_id` int(11) NOT NULL,
  `order_number` varchar(30) NOT NULL,
  `status` varchar(30) NOT NULL DEFAULT 'To Pay',
  `fulfillment_type` varchar(40) NOT NULL DEFAULT 'Regular Delivery',
  `delivery_type` varchar(20) NOT NULL DEFAULT 'regular',
  `delivery_address_id` int(10) UNSIGNED DEFAULT NULL,
  `subtotal` decimal(10,2) NOT NULL DEFAULT 0.00,
  `delivery_fee` decimal(10,2) NOT NULL DEFAULT 0.00,
  `discount_amount` decimal(10,2) NOT NULL DEFAULT 0.00,
  `total_amount` decimal(10,2) NOT NULL DEFAULT 0.00,
  `created_at` datetime NOT NULL DEFAULT current_timestamp(),
  `updated_at` datetime NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `rating` tinyint(1) DEFAULT NULL,
  `email_sent` tinyint(1) DEFAULT 0,
  `delivery_email_sent` tinyint(1) DEFAULT 0,
  `rating_email_sent` tinyint(1) DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `orders`
--

INSERT INTO `orders` (`id`, `user_id`, `order_number`, `status`, `fulfillment_type`, `delivery_type`, `delivery_address_id`, `subtotal`, `delivery_fee`, `discount_amount`, `total_amount`, `created_at`, `updated_at`, `rating`, `email_sent`, `delivery_email_sent`, `rating_email_sent`) VALUES
(1, 1, 'GG-69EF7667688F9', 'To Ship', 'delivery', 'regular', NULL, 0.00, 0.00, 0.00, 104810.70, '2026-04-27 22:44:55', '2026-04-27 22:44:55', NULL, 0, 0, 0),
(2, 1, 'GG-69EF95266D3A8', 'To Pay', 'Regular Delivery', 'regular', NULL, 279606.00, 50.00, 0.00, 279656.40, '2026-04-28 00:56:06', '2026-04-28 00:56:06', NULL, 0, 0, 0),
(3, 1, 'GG-69EF95BB98604', 'To Pay', 'Regular Delivery', 'regular', NULL, 279606.00, 50.00, 0.00, 279656.40, '2026-04-28 00:58:35', '2026-04-28 00:58:35', NULL, 0, 0, 0),
(4, 1, 'GG-69F5D8FFC4DCC', 'To Rate', 'Regular Delivery', 'regular', NULL, 1457.00, 50.00, 0.00, 1507.94, '2026-05-02 18:59:11', '2026-05-02 19:00:16', NULL, 1, 1, 0);

-- --------------------------------------------------------

--
-- Table structure for table `order_items`
--

CREATE TABLE `order_items` (
  `id` int(10) UNSIGNED NOT NULL,
  `order_id` int(10) UNSIGNED NOT NULL,
  `product_id` int(11) DEFAULT NULL,
  `product_name` varchar(200) NOT NULL,
  `quantity` int(11) NOT NULL DEFAULT 1,
  `unit_price` decimal(10,2) NOT NULL DEFAULT 0.00
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `order_items`
--

INSERT INTO `order_items` (`id`, `order_id`, `product_id`, `product_name`, `quantity`, `unit_price`) VALUES
(1, 1, 532, 'Great Earth Frozen Corn & Carrots 500g', 4, 259.95),
(2, 1, 534, 'Great Earth Frozen Strawberries 2lbs', 122, 389.95),
(3, 1, 871, 'Silver Swan Vinegar Pet Bottle 1L', 1234, 45.50),
(4, 2, 642, 'Lucky Me Instant Pancit Canton Chilimansi | 80g', 1500, 39.99),
(5, 2, 645, 'Silver Swan Soy Sauce 1.892L', 100, 101.00),
(6, 2, 532, 'Great Earth Frozen Corn & Carrots 500g', 8, 259.95),
(7, 2, 534, 'Great Earth Frozen Strawberries 2lbs', 244, 389.95),
(8, 2, 871, 'Silver Swan Vinegar Pet Bottle 1L', 2468, 45.50),
(9, 3, 642, 'Lucky Me Instant Pancit Canton Chilimansi | 80g', 1500, 39.99),
(10, 3, 645, 'Silver Swan Soy Sauce 1.892L', 100, 101.00),
(11, 3, 532, 'Great Earth Frozen Corn & Carrots 500g', 8, 259.95),
(12, 3, 534, 'Great Earth Frozen Strawberries 2lbs', 244, 389.95),
(13, 3, 871, 'Silver Swan Vinegar Pet Bottle 1L', 2468, 45.50),
(14, 4, 642, 'Lucky Me Instant Pancit Canton Chilimansi | 80g', 6, 39.99),
(15, 4, 692, 'Nestle Fresh Milk 1L', 10, 105.00),
(16, 4, 696, 'C2 Green Tea Apple 500ml', 6, 28.00);

-- --------------------------------------------------------

--
-- Table structure for table `products`
--

CREATE TABLE `products` (
  `id` int(11) NOT NULL,
  `category_slug` varchar(80) NOT NULL,
  `name` varchar(180) NOT NULL,
  `price` decimal(10,2) NOT NULL,
  `image_path` varchar(255) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `stock_quantity` int(11) NOT NULL DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `products`
--

INSERT INTO `products` (`id`, `category_slug`, `name`, `price`, `image_path`, `created_at`, `stock_quantity`) VALUES
(457, 'only-in-gogetgro', 'GoDeals | Korean Kimbap Roll | per pack', 135.00, 'images/oigkimbap.jpg', '2026-04-24 13:53:17', 22),
(458, 'only-in-gogetgro', 'GoDeals | Premium Paper Towel (Long Roll) | per roll', 65.00, 'images/oigpapertowel.jpg', '2026-04-24 13:53:17', 22),
(459, 'only-in-gogetgro', 'GoDeals | Sliced Beef Strips | per 500g', 220.00, 'images/oigbeef.jpg', '2026-04-24 13:53:17', 14),
(460, 'only-in-gogetgro', 'GoDeals | Purified Drinking Water | per bottle', 25.00, 'images/oigwater.jpg', '2026-04-24 13:53:17', 8),
(461, 'only-in-gogetgro', 'GoDeals | Fruit Gummies | per pack', 89.00, 'images/oiggummies.jpg', '2026-04-24 13:53:17', 22),
(462, 'only-in-gogetgro', 'GoDeals | Creamy Peanut Butter | per jar', 175.00, 'images/oigpeanutbutter.jpg', '2026-04-24 13:53:17', 3),
(463, 'only-in-gogetgro', 'GoDeals | Premium Almonds | per pack', 220.00, 'images/oigalmonds.jpg', '2026-04-24 13:53:17', 22),
(464, 'only-in-gogetgro', 'GoDeals | Chocolate Almonds | per pack', 195.00, 'images/oigchocoalmonds.jpg', '2026-04-24 13:53:17', 22),
(465, 'only-in-gogetgro', 'GoDeals | Dried Mangoes | per pack', 160.00, 'images/oigdriedmangoes.jpg', '2026-04-24 13:53:17', 8),
(466, 'only-in-gogetgro', 'GoDeals | Black Hoodie | per piece', 499.00, 'images/oigblackhoodie.jpg', '2026-04-24 13:53:17', 22),
(467, 'only-in-gogetgro', 'GoDeals | Glass Storage Container | per piece', 249.00, 'images/oigglassstorage.jpg', '2026-04-24 13:53:17', 22),
(468, 'only-in-gogetgro', 'GoDeals | Sea Moss Supplement | per jar', 320.00, 'images/oigseamoss.jpg', '2026-04-24 13:53:17', 14),
(469, 'only-in-gogetgro', 'GoDeals | Chicken Broth | per pack', 110.00, 'images/oigchickenbroth.jpg', '2026-04-24 13:53:17', 5),
(470, 'only-in-gogetgro', 'GoDeals | Women’s Multivitamins | per bottle', 390.00, 'images/oigwomenvitamin.jpg', '2026-04-24 13:53:17', 8),
(471, 'only-in-gogetgro', 'GoDeals | Nut Trail Mix | per pack', 185.00, 'images/oigtrailmix.jpg', '2026-04-24 13:53:17', 14),
(472, 'only-in-gogetgro', 'GoDeals | Plantain Chips | per pack', 145.00, 'images/oigplantainchips.jpg', '2026-04-24 13:53:17', 22),
(473, 'only-in-gogetgro', 'GoDeals | Chili Sauce | per bottle', 120.00, 'images/oigchilisauce.jpg', '2026-04-24 13:53:17', 3),
(474, 'only-in-gogetgro', 'GoDeals | Artisan Popcorn | per pack', 135.00, 'images/oigartisanpopcorn.jpg', '2026-04-24 13:53:17', 14),
(475, 'only-in-gogetgro', 'GoDeals | Raw Kale Chips | per pack', 165.00, 'images/oigrawkalechips.jpg', '2026-04-24 13:53:17', 8),
(476, 'only-in-gogetgro', 'GoDeals | Truffle Sauce | per bottle', 295.00, 'images/oigtrufflesauce.jpg', '2026-04-24 13:53:17', 5),
(477, 'only-in-gogetgro', 'GoDeals | Bagel Pack (3 pcs) | per pack', 180.00, 'images/oigbagelpack3.jpg', '2026-04-24 13:53:17', 14),
(478, 'only-in-gogetgro', 'GoDeals | Raw Milk | per bottle', 140.00, 'images/oigrawmilk.jpg', '2026-04-24 13:53:17', 22),
(479, 'only-in-gogetgro', 'GoDeals | Balsamic Vinegar | per bottle', 210.00, 'images/oigbalsamicvinegar.jpg', '2026-04-24 13:53:17', 22),
(480, 'only-in-gogetgro', 'GoDeals | Coconut Yogurt | per cup', 125.00, 'images/oigcoconutyogurt.jpg', '2026-04-24 13:53:17', 8),
(481, 'only-in-gogetgro', 'GoDeals | Pure Maple Syrup | per bottle', 275.00, 'images/oigmaplesyrup.jpg', '2026-04-24 13:53:17', 22),
(482, 'fresh-produce', 'Fuji Apple Large | per piece', 35.00, 'images/apple.jpg', '2026-04-24 13:53:17', 22),
(483, 'fresh-produce', 'Lakatan Banana | 1kg', 95.00, 'images/banana.jpg', '2026-04-24 13:53:17', 5),
(484, 'fresh-produce', 'Fresh Carrots Local | 500g', 65.00, 'images/carrot.png', '2026-04-24 13:53:17', 3),
(485, 'fresh-produce', 'Red Onions | 1kg', 180.00, 'images/onion.jpg', '2026-04-24 13:53:17', 8),
(486, 'fresh-produce', 'Fresh Broccoli | 500g', 120.00, 'images/broccoli.jpg', '2026-04-24 13:53:17', 14),
(487, 'fresh-produce', 'Navel Orange Large | 3pcs', 110.00, 'images/orange.png', '2026-04-24 13:53:17', 22),
(488, 'fresh-produce', 'Calamansi (RS) approx. 1kg (Cebu)', 124.95, 'images/calamansi.jpg', '2026-04-24 13:53:17', 22),
(489, 'fresh-produce', 'Gabi Fruits (EP) approx. 400g', 60.00, 'images/gabi.jpg', '2026-04-24 13:53:17', 14),
(490, 'fresh-produce', 'Sweet Corn (CT) approx. 1.5kg', 179.93, 'images/corn.jpg', '2026-04-24 13:53:17', 5),
(491, 'fresh-produce', 'Papaya Green (CT) approx. 1kg', 59.95, 'images/papaya.jpg', '2026-04-24 13:53:17', 22),
(492, 'fresh-produce', 'Veggie Mix (LU) approx. 400g', 58.38, 'images/veggiemix.jpg', '2026-04-24 13:53:17', 14),
(493, 'fresh-produce', 'Rambutan (MPI) approx. 500g', 87.50, 'images/rambutan.jpg', '2026-04-24 13:53:17', 22),
(494, 'fresh-produce', 'Local Mixed Fruits (LEI) approx. 500g', 99.98, 'images/mixedfruits.jpg', '2026-04-24 13:53:17', 22),
(495, 'fresh-produce', 'Tomato Baguio approx. 600g', 233.55, 'images/tomato.jpg', '2026-04-24 13:53:17', 3),
(496, 'fresh-produce', 'Pinakbet Mix Vegetables (CT) approx. 1.2kg', 215.94, 'images/pinakbet.jpg', '2026-04-24 13:53:17', 22),
(497, 'fresh-produce', 'Yellow Capsicum (RS) approx. 300g', 272.99, 'images/capsicum.jpg', '2026-04-24 13:53:17', 5),
(498, 'fresh-produce', 'Peanuts with Shells (LEI) approx. 400g', 75.00, 'images/peanut.jpg', '2026-04-24 13:53:17', 14),
(499, 'fresh-produce', 'Linaga Mix (LEI) approx. 700g', 104.97, 'images/linaga.jpg', '2026-04-24 13:53:17', 22),
(500, 'fresh-produce', 'Aubergine Eggplant (CT) approx. 350g', 104.98, 'images/eggplant.jpg', '2026-04-24 13:53:17', 8),
(501, 'fresh-produce', 'Sampaloc Sigang approx. 200g', 30.99, 'images/sampalok.jpg', '2026-04-24 13:53:17', 14),
(502, 'fresh-produce', 'Cherry Tomato (RGPM) approx. 400g', 228.38, 'images/cherrytomato.jpg', '2026-04-24 13:53:17', 22),
(503, 'fresh-produce', 'Orange Sweet Potato approx. 600g', 98.97, 'images/sweetpotato.jpg', '2026-04-24 13:53:17', 22),
(504, 'fresh-produce', 'Celery approx. 400g', 159.98, 'images/celery.jpg', '2026-04-24 13:53:17', 5),
(505, 'fresh-produce', 'Ginger approx. 900g', 180.00, 'images/ginger.jpg', '2026-04-24 13:53:17', 8),
(506, 'fresh-produce', 'Green Muscat Grapes (RGPM) approx. 1kg', 868.95, 'images/grapes.jpg', '2026-04-24 13:53:17', 3),
(507, 'fresh-meat-seafood', 'Fresh Angus Ribeye Steak Cut 500g', 899.00, 'images/beef-ribeye.jpg', '2026-04-24 13:53:17', 14),
(508, 'fresh-meat-seafood', 'Fresh Pork Belly Slice (Liempo) 1kg', 420.00, 'images/pork-belly.jpg', '2026-04-24 13:53:17', 22),
(509, 'fresh-meat-seafood', 'Fresh Whole Chicken Farm Raised 1.2kg', 210.00, 'images/chicken-whole.jpg', '2026-04-24 13:53:17', 22),
(510, 'fresh-meat-seafood', 'Fresh Ground Beef Lean 500g', 275.00, 'images/ground-beef.jpg', '2026-04-24 13:53:17', 8),
(511, 'fresh-meat-seafood', 'Fresh Atlantic Salmon Fillet 300g', 520.00, 'images/salmon-fillet.jpg', '2026-04-24 13:53:17', 5),
(512, 'fresh-meat-seafood', 'Fresh Tilapia Whole Cleaned 1kg', 180.00, 'images/tilapia.jpg', '2026-04-24 13:53:17', 22),
(513, 'fresh-meat-seafood', 'Fresh Milkfish (Bangus) Medium Size 2pcs', 240.00, 'images/bangus.jpg', '2026-04-24 13:53:17', 14),
(514, 'fresh-meat-seafood', 'Fresh White Shrimp Medium Size 500g', 390.00, 'images/shrimp_fresh.jpg', '2026-04-24 13:53:17', 22),
(515, 'fresh-meat-seafood', 'Fresh Mud Crab Live Medium 1kg', 850.00, 'images/crab.jpg', '2026-04-24 13:53:17', 8),
(516, 'fresh-meat-seafood', 'Fresh Squid Tubes Cleaned 500g', 330.00, 'images/squid.jpg', '2026-04-24 13:53:17', 14),
(517, 'fresh-meat-seafood', 'Fresh Green Mussels (Tahong) 1kg', 160.00, 'images/mussels.jpg', '2026-04-24 13:53:17', 3),
(518, 'fresh-meat-seafood', 'Fresh Pork Chop Cut Bone-in 500g', 310.00, 'images/pork-chop.jpg', '2026-04-24 13:53:17', 5),
(519, 'fresh-meat-seafood', 'Fresh Beef Shank for Soup (Bulalo Cut) 1kg', 690.00, 'images/beef-shank.jpg', '2026-04-24 13:53:17', 14),
(520, 'fresh-meat-seafood', 'Fresh Chicken Breast Fillet Skinless 500g', 180.00, 'images/chicken-breast.jpg', '2026-04-24 13:53:17', 8),
(521, 'fresh-meat-seafood', 'Fresh Tuna Steak Cut 500g', 420.00, 'images/tuna-steak.jpg', '2026-04-24 13:53:17', 22),
(522, 'fresh-meat-seafood', 'Fresh Pork Minced Meat 500g', 195.00, 'images/pork-mince.jpg', '2026-04-24 13:53:17', 14),
(523, 'fresh-meat-seafood', 'Fresh Beef Strips for Tapa 500g', 345.00, 'images/beef-tapa.jpg', '2026-04-24 13:53:17', 22),
(524, 'fresh-meat-seafood', 'Fresh Chicken Wings Cut 1kg', 260.00, 'images/chicken-wings.jpg', '2026-04-24 13:53:17', 22),
(525, 'fresh-meat-seafood', 'Fresh Lapu-Lapu Whole Cleaned 700g', 780.00, 'images/lapu-lapu.jpg', '2026-04-24 13:53:17', 5),
(526, 'fresh-meat-seafood', 'Fresh Jumbo Prawns 500g', 620.00, 'images/prawns.jpg', '2026-04-24 13:53:17', 22),
(527, 'fresh-meat-seafood', 'Fresh Beef Brisket Slice 1kg', 720.00, 'images/beef-brisket.jpg', '2026-04-24 13:53:17', 22),
(528, 'fresh-meat-seafood', 'Fresh White Fish Fillet Boneless 500g', 250.00, 'images/fish-fillet.jpg', '2026-04-24 13:53:17', 3),
(529, 'fresh-meat-seafood', 'Fresh Pork Ribs Cut (Baby Back) 1kg', 480.00, 'images/pork-ribs.jpg', '2026-04-24 13:53:17', 22),
(530, 'fresh-meat-seafood', 'Fresh Salmon Head for Soup 500g', 210.00, 'images/salmon-head.jpg', '2026-04-24 13:53:17', 8),
(531, 'fresh-meat-seafood', 'Fresh Beef Cubes for Stew 500g', 360.00, 'images/beef-cubes.jpg', '2026-04-24 13:53:17', 14),
(532, 'frozen-goods', 'Great Earth Frozen Corn & Carrots 500g', 259.95, 'images/CornCarrot.jpg', '2026-04-24 13:53:17', 5),
(533, 'frozen-goods', 'Unagi Kabayaki Frozen Roasted Eel 199g', 510.00, 'images/eel.jpg', '2026-04-24 13:53:17', 22),
(534, 'frozen-goods', 'Great Earth Frozen Strawberries 2lbs', 389.95, 'images/strawberry.jpg', '2026-04-24 13:53:17', 14),
(535, 'frozen-goods', 'Virginia Frozen Corned Beef 1kg', 470.00, 'images/virginiabeef.jpg', '2026-04-24 13:53:17', 8),
(536, 'frozen-goods', 'Sarangani Bay Frozen Fish Fingers 200g', 135.00, 'images/fishfinger.jpg', '2026-04-24 13:53:17', 22),
(537, 'frozen-goods', 'Ocean Mama Frozen Butterfish IQF 1Lb', 199.95, 'images/butterfish.jpg', '2026-04-24 13:53:17', 14),
(538, 'frozen-goods', 'Swiss Deli Beef Hungarian Sausage 500g', 491.95, 'images/sausage.jpg', '2026-04-24 13:53:17', 22),
(539, 'frozen-goods', 'Great Earth French Fries Shoestring 2Lbs.', 175.00, 'images/fries.jpg', '2026-04-24 13:53:17', 3),
(540, 'frozen-goods', 'Magnolia Ube Keso Ice Cream 1.3L', 269.95, 'images/icecream1.jpg', '2026-04-24 13:53:17', 8),
(541, 'frozen-goods', 'Seoju Chocomallow Ice Cream Bar 8 x 75mL', 258.95, 'images/icecreambar1.jpg', '2026-04-24 13:53:17', 22),
(542, 'frozen-goods', 'Emborg Edamame Whole Green Soybeans 400g', 315.00, 'images/soybeans.jpg', '2026-04-24 13:53:17', 22),
(543, 'frozen-goods', 'UnMeat Plant-Based Nuggets 200g', 125.95, 'images/nuggets.jpg', '2026-04-24 13:53:17', 14),
(544, 'frozen-goods', 'Simplot Breaded Jalapeno with Cheese 908g', 835.95, 'images/jalapeno.jpg', '2026-04-24 13:53:17', 22),
(545, 'frozen-goods', 'Great Earth Potato Wedges 2Lbs.', 336.95, 'images/wedges.jpg', '2026-04-24 13:53:17', 8),
(546, 'frozen-goods', 'Espuna Tapas Fuet D Olot 60g', 560.95, 'images/tapas.jpg', '2026-04-24 13:53:17', 5),
(547, 'frozen-goods', 'SeaKing Marinated Hot Boneless Milkfish Bangus', 342.95, 'images/milkfish.jpg', '2026-04-24 13:53:17', 22),
(548, 'frozen-goods', 'Taiwan Balls Crab Fish Ball 750g', 489.95, 'images/crabfishball.jpg', '2026-04-24 13:53:17', 22),
(549, 'frozen-goods', 'Parklane Hashbrown Potatoes 637g', 224.95, 'images/Hashbrown.jpg', '2026-04-24 13:53:17', 14),
(550, 'frozen-goods', 'Dae Jang Gum Lumpia Wrapper Big 210g/pack', 47.00, 'images/Wrapper.jpg', '2026-04-24 13:53:17', 3),
(551, 'frozen-goods', 'Maks Recipes Pork Siomai', 596.95, 'images/Siomai.jpg', '2026-04-24 13:53:17', 22),
(552, 'frozen-goods', 'Fishing Village Cooked Shrimps 150-200lbs', 1049.95, 'images/shrimp.jpg', '2026-04-24 13:53:17', 14),
(553, 'frozen-goods', 'Purefoods Tender Juicy Classic Hotdog 1kg', 198.00, 'images/hotdog.jpg', '2026-04-24 13:53:17', 5),
(554, 'frozen-goods', 'Beef Salpicao (PM) approx. 700g', 419.97, 'images/beef.jpg', '2026-04-24 13:53:17', 22),
(555, 'frozen-goods', 'Purefoods Classic Honeycured Bacon 1kg', 718.50, 'images/bacon.jpg', '2026-04-24 13:53:17', 8),
(556, 'frozen-goods', 'Tuna Sashimi Plain 500g', 458.95, 'images/sashimi.jpg', '2026-04-24 13:53:17', 22),
(557, 'ready-to-cook', 'Pork Tocino Sweet Cure | 250g', 95.00, 'images/tocino.jpg', '2026-04-24 13:53:17', 22),
(558, 'ready-to-cook', 'Garlic Longganisa Native Style | 200g', 85.00, 'images/longganisa.jpg', '2026-04-24 13:53:17', 14),
(559, 'ready-to-cook', 'Purefoods Red Hotdogs | 500g', 120.00, 'images/hotdog.jpg', '2026-04-24 13:53:17', 22),
(560, 'ready-to-cook', 'Beef Tapa Marinated | 250g', 135.00, 'images/tapa.jpg', '2026-04-24 13:53:17', 5),
(561, 'ready-to-cook', 'CDO Corned Beef Classic | 150g', 55.00, 'images/cornedbeef.jpg', '2026-04-24 13:53:17', 3),
(562, 'ready-to-cook', '555 Sardines in Tomato Sauce | 155g', 28.00, 'images/sardines.jpg', '2026-04-24 13:53:17', 22),
(563, 'ready-to-cook', 'Chicken Adobo Cut Marinated | 500g', 160.00, 'images/adobo.jpg', '2026-04-24 13:53:17', 22),
(564, 'ready-to-cook', 'Pork Liempo Marinated BBQ Style | 500g', 210.00, 'images/liempo.jpg', '2026-04-24 13:53:17', 14),
(565, 'ready-to-cook', 'Marinated Chicken Wings BBQ | 500g', 175.00, 'images/chickenwing.jpg', '2026-04-24 13:53:17', 8),
(566, 'ready-to-cook', 'Pork Sisig Ready-to-Cook | 250g', 110.00, 'images/sisig.jpg', '2026-04-24 13:53:17', 22),
(567, 'ready-to-cook', 'Pork Lumpiang Shanghai Frozen | 500g', 180.00, 'images/shanghai.jpg', '2026-04-24 13:53:17', 5),
(568, 'ready-to-cook', 'Breaded Fish Fillet Frozen | 400g', 165.00, 'images/fishfillet.jpg', '2026-04-24 13:53:17', 22),
(569, 'ready-to-cook', 'Asado Siopao Frozen Pack | 6pcs', 140.00, 'images/siopao.jpg', '2026-04-24 13:53:17', 22),
(570, 'ready-to-cook', 'Beef Burger Patties Frozen | 500g', 190.00, 'images/burgerpatty.jpg', '2026-04-24 13:53:17', 8),
(571, 'ready-to-cook', 'Chicken Nuggets Crispy Frozen | 300g', 120.00, 'images/nuggets.jpg', '2026-04-24 13:53:17', 22),
(572, 'ready-to-cook', 'Frozen Meatballs Italian Style | 400g', 155.00, 'images/meatballs.jpg', '2026-04-24 13:53:17', 3),
(573, 'ready-to-cook', 'Daing na Bangus Marinated | 2pcs', 140.00, 'images/rotbangus.jpg', '2026-04-24 13:53:17', 14),
(574, 'ready-to-cook', 'Frozen Tilapia Cleaned | 1kg', 160.00, 'images/rottilapia.jpg', '2026-04-24 13:53:17', 5),
(575, 'ready-to-cook', 'Frozen Beef Empanada | 6pcs', 150.00, 'images/empanada.jpg', '2026-04-24 13:53:17', 8),
(576, 'ready-to-cook', 'Kikiam Street Food Style Frozen | 500g', 130.00, 'images/kikiam.jpg', '2026-04-24 13:53:17', 14),
(577, 'chilled-dairy', 'Lipa Fresh Coconut Juice 500mL', 76.95, 'images/coconutjuice.jpg', '2026-04-24 13:53:17', 22),
(578, 'chilled-dairy', 'Magnolia Butter-licious Unsalted Butter 200g', 97.00, 'images/butter.jpg', '2026-04-24 13:53:17', 22),
(579, 'chilled-dairy', 'Crisco All-Vegetable Shortening 453g', 299.25, 'images/crisco.jpg', '2026-04-24 13:53:17', 14),
(580, 'chilled-dairy', 'Emborg Mild White Cheddar Cheese 200g', 279.00, 'images/cheddar.jpg', '2026-04-24 13:53:17', 8),
(581, 'chilled-dairy', 'Australian Gold Creamy Brie Cheese 115 g', 199.95, 'images/brie.jpg', '2026-04-24 13:53:17', 5),
(582, 'chilled-dairy', 'Coca-Cola Original Taste 1.5L', 85.00, 'images/coke.jpg', '2026-04-24 13:53:17', 14),
(583, 'chilled-dairy', 'Nibou Yourgurt Fruity Pudding with Nata De Coco 33 x 35g', 299.95, 'images/fruitypudding.jpg', '2026-04-24 13:53:17', 3),
(584, 'chilled-dairy', 'Bounty Fresh Premium Eggs Large size 12s', 141.95, 'images/egg.jpg', '2026-04-24 13:53:17', 22),
(585, 'chilled-dairy', 'Nestle All-Purpose Cream 250mL', 69.75, 'images/allpurposecream.jpg', '2026-04-24 13:53:17', 8),
(586, 'chilled-dairy', 'Nestle Sour Cream 240 g', 150.00, 'images/sourcream.jpg', '2026-04-24 13:53:17', 22),
(587, 'chilled-dairy', 'Haagen-Dazs Belgian Chocolate Ice Cream 460mL', 468.95, 'images/icecream3.jpg', '2026-04-24 13:53:17', 22),
(588, 'chilled-dairy', 'Hersheys Chocolate Ice Cream Bar 7 x 53mL', 453.95, 'images/icebar.jpg', '2026-04-24 13:53:17', 5),
(589, 'chilled-dairy', 'Yakult Probiotics Drink 5 x 80mL', 45.50, 'images/yakult.jpg', '2026-04-24 13:53:17', 22),
(590, 'chilled-dairy', 'Dutch Mill Delight Probiotic Drink 400mL', 110.00, 'images/delight.jpg', '2026-04-24 13:53:17', 8),
(591, 'chilled-dairy', 'Cowhead Premium Chocolate Milk 1L', 125.25, 'images/chocomilk.jpg', '2026-04-24 13:53:17', 14),
(592, 'chilled-dairy', 'Soyfresh Natural Soya Milk Drink 1L', 85.00, 'images/soya.jpg', '2026-04-24 13:53:17', 22),
(593, 'chilled-dairy', 'Nestle Greek Flavored Yogurt 500 g', 293.00, 'images/greek.jpg', '2026-04-24 13:53:17', 22),
(594, 'chilled-dairy', 'Nestle Blissful Berry Mix Yogurt 4 x 110g', 196.00, 'images/berrymixyogurt.jpg', '2026-04-24 13:53:17', 3),
(595, 'chilled-dairy', 'Dutch Mill Strawberry Yogurt Drink 4 x 180mL', 80.95, 'images/dutchmill.jpg', '2026-04-24 13:53:17', 5),
(596, 'chilled-dairy', 'The Gutsy Captain Ginger & Lemon Kombucha 1L', 214.95, 'images/kombucha.jpg', '2026-04-24 13:53:17', 22),
(597, 'chilled-dairy', 'Tropicana Twister Regular Orange PET 1L', 57.95, 'images/orangejuice1.jpg', '2026-04-24 13:53:17', 14),
(598, 'chilled-dairy', 'Del Monte Four Seasons Juice Drink Promo Pack', 198.95, 'images/fourseasons.jpg', '2026-04-24 13:53:17', 22),
(599, 'chilled-dairy', 'Mogu Mogu Lychee Juice with Nata de Coco 320mL', 235.95, 'images/mogu.jpg', '2026-04-24 13:53:17', 22),
(600, 'chilled-dairy', 'Ocean Spray Cranberry x Raspberry Juice Drink', 325.95, 'images/cranberry.jpg', '2026-04-24 13:53:17', 8),
(601, 'chilled-dairy', 'Granini Banana Juice 1L', 230.95, 'images/bananajuice.jpg', '2026-04-24 13:53:17', 22),
(602, 'bakery', 'Gardenia Classic White Bread 600g', 55.00, 'images/bread.jpg', '2026-04-24 13:53:17', 5),
(603, 'bakery', 'Eng Bee Tin Premium Brown Tikoy Medium', 295.00, 'images/tikoy.jpg', '2026-04-24 13:53:17', 14),
(604, 'bakery', 'Euro Glaze Fondant Mini Doughnut 8 x 30g', 360.00, 'images/donut.jpg', '2026-04-24 13:53:17', 22),
(605, 'bakery', 'Tous Les Jours Butter Croissant 60g', 75.00, 'images/croissant.jpg', '2026-04-24 13:53:17', 3),
(606, 'bakery', 'Muffin Break Blueberry Muffin 80g', 65.00, 'images/muffin.jpg', '2026-04-24 13:53:17', 14),
(607, 'bakery', 'Einstein Bros. Bagels Everything Bagel 4pcs', 220.00, 'images/bagel.jpg', '2026-04-24 13:53:17', 22),
(608, 'bakery', 'Brioche Pasquier Soft Brioche Burger Buns 4pcs', 150.00, 'images/brioche.jpg', '2026-04-24 13:53:17', 22),
(609, 'bakery', 'Pan de Sal (GO) approx. 500g', 50.00, 'images/pandesal.jpg', '2026-04-24 13:53:17', 5),
(610, 'bakery', 'Dough and Co. US Chocolate Tres Leches', 272.00, 'images/tresletche.jpg', '2026-04-24 13:53:17', 8),
(611, 'bakery', 'Alec\'s Artisan Sourdough approx. 900g', 159.00, 'images/sourdough.jpg', '2026-04-24 13:53:17', 22),
(612, 'bakery', 'Dough and Co. Custard Twist Roll', 199.00, 'images/custardroll.jpg', '2026-04-24 13:53:17', 14),
(613, 'bakery', 'Blueberry Cheesecake 6', 529.00, 'images/cheesecake.jpg', '2026-04-24 13:53:17', 22),
(614, 'bakery', 'Dough and Co. Caramel Pastel de Nata 5s', 284.00, 'images/pasteldenata.jpg', '2026-04-24 13:53:17', 22),
(615, 'bakery', 'Dough and Co. Egg Pie 820g', 380.00, 'images/eggpie.jpg', '2026-04-24 13:53:17', 8),
(616, 'bakery', 'Hungrypac Chocolate Crinkles 24s', 370.00, 'images/crinkles.jpg', '2026-04-24 13:53:17', 3),
(617, 'bakery', 'European Gourmet Triple Choco Cookie 6s', 360.00, 'images/chococookie2.jpg', '2026-04-24 13:53:17', 22),
(618, 'bakery', 'Ensaymada 6s', 219.00, 'images/ensaymada.jpg', '2026-04-24 13:53:17', 14),
(619, 'bakery', 'Nic\'s Cinnamon Rolls 6s', 90.00, 'images/cinnamon.jpg', '2026-04-24 13:53:17', 22),
(620, 'bakery', 'Dough and Co. Red Bean Anpan 4s', 157.00, 'images/redbean.jpg', '2026-04-24 13:53:17', 8),
(621, 'bakery', 'Euro Classic Baguette', 136.00, 'images/baguette.jpg', '2026-04-24 13:53:17', 14),
(622, 'international-goods', 'Oreo Original Cookies Imported | 137g', 65.00, 'images/oreo.jpg', '2026-04-24 13:53:17', 22),
(623, 'international-goods', 'Nutella Hazelnut Spread Imported | 350g', 245.00, 'images/nutella.jpg', '2026-04-24 13:53:17', 5),
(624, 'international-goods', 'Kellogg’s Corn Flakes Imported | 250g', 180.00, 'images/kellogs.jpg', '2026-04-24 13:53:17', 14),
(625, 'international-goods', 'Honey Nut Cheerios Imported | 300g', 220.00, 'images/cheerios.jpg', '2026-04-24 13:53:17', 8),
(626, 'international-goods', 'Pringles Sour Cream & Onion | 158g', 120.00, 'images/pringles.jpg', '2026-04-24 13:53:17', 22),
(627, 'international-goods', 'Lay’s Classic Potato Chips Imported | 184g', 130.00, 'images/lays.jpg', '2026-04-24 13:53:17', 3),
(628, 'international-goods', 'Coca-Cola Original Imported Can | 330ml', 45.00, 'images/cocacola.jpg', '2026-04-24 13:53:17', 22),
(629, 'international-goods', 'Pepsi Cola Imported Can | 330ml', 42.00, 'images/pepsi.jpg', '2026-04-24 13:53:17', 22),
(630, 'international-goods', 'Red Bull Energy Drink Imported | 250ml', 95.00, 'images/redbull.jpg', '2026-04-24 13:53:17', 5),
(631, 'international-goods', 'Hershey’s Milk Chocolate Bar Imported | 43g', 55.00, 'images/hershey.jpg', '2026-04-24 13:53:17', 22),
(632, 'international-goods', 'KitKat Classic Imported | 4 Finger 41.5g', 60.00, 'images/kitkat.jpg', '2026-04-24 13:53:17', 22),
(633, 'international-goods', 'M&M’s Chocolate Candies Imported | 45g', 65.00, 'images/mms.jpg', '2026-04-24 13:53:17', 14),
(634, 'international-goods', 'Ferrero Rocher 3pcs Imported Box | 37.5g', 110.00, 'images/ferrero.jpg', '2026-04-24 13:53:17', 22),
(635, 'international-goods', 'Skippy Peanut Butter Imported | 340g', 195.00, 'images/skippy.jpg', '2026-04-24 13:53:17', 8),
(636, 'international-goods', 'Maggi Instant Noodles Imported | 79g', 35.00, 'images/maggi.jpg', '2026-04-24 13:53:17', 14),
(637, 'international-goods', 'Nissin Cup Noodles Imported | 70g', 40.00, 'images/nissin.jpg', '2026-04-24 13:53:17', 5),
(638, 'international-goods', 'SPAM Classic Luncheon Meat Imported | 340g', 260.00, 'images/spam.jpg', '2026-04-24 13:53:17', 3),
(639, 'international-goods', 'Toblerone Swiss Chocolate Imported | 100g', 145.00, 'images/toblerone.jpg', '2026-04-24 13:53:17', 14),
(640, 'international-goods', 'Jacobs Coffee Instant Imported | 200g', 210.00, 'images/jacobs.jpg', '2026-04-24 13:53:17', 8),
(641, 'international-goods', 'Lipton Yellow Label Tea Imported | 50 bags', 180.00, 'images/lipton.jpg', '2026-04-24 13:53:17', 22),
(642, 'pantry', 'Lucky Me Instant Pancit Canton Chilimansi | 80g', 39.99, 'images/Pancit.png', '2026-04-24 13:53:17', 14),
(643, 'pantry', 'Spam Lite 50% Less Fat Luncheon Meat 340g', 226.95, 'images/Spam.jpg', '2026-04-24 13:53:17', 22),
(644, 'pantry', 'Jasmine Rice Premium Grade 10kg', 678.95, 'images/Rice.jpg', '2026-04-24 13:53:17', 5),
(645, 'pantry', 'Silver Swan Soy Sauce 1.892L', 101.00, 'images/Soy Sauce.png', '2026-04-24 13:53:17', 8),
(646, 'pantry', 'Baguio Pure Coconut Oil 1Gal.', 799.99, 'images/coconutoil.jpg', '2026-04-24 13:53:17', 22),
(647, 'pantry', 'Heinz Tomato Squeeze Ketchup 570g', 197.95, 'images/ketchup.jpg', '2026-04-24 13:53:17', 22),
(648, 'pantry', 'Quaker Oats Quick Cook Oatmeal 800g', 134.95, 'images/oats.jpg', '2026-04-24 13:53:17', 14),
(649, 'pantry', 'Hunts Pork & Beans 390g', 53.95, 'images/beans.jpg', '2026-04-24 13:53:17', 3),
(650, 'pantry', 'Century Tuna Flakes Hot & Spicy 3 x 180g', 138.99, 'images/centurytuna.jpg', '2026-04-24 13:53:17', 8),
(651, 'pantry', 'Palermo Pure Olive Oil 3L', 1849.95, 'images/oliveoil.jpg', '2026-04-24 13:53:17', 5),
(652, 'pantry', 'Silver Swan Cane Vinegar 1.893L', 80.95, 'images/vinegar.jpg', '2026-04-24 13:53:17', 22),
(653, 'pantry', 'Indomie Mi Goreng Instant Noodles 5 x 85g', 89.95, 'images/goreng.jpg', '2026-04-24 13:53:17', 22),
(654, 'pantry', 'Ottogi Jin Ramen Spicy Instant Noodles 5 x 120g', 209.99, 'images/ramen.jpg', '2026-04-24 13:53:17', 14),
(655, 'pantry', 'Kirkland Signature Pink Salt Grinder with Refill', 534.95, 'images/salt.jpg', '2026-04-24 13:53:17', 8),
(656, 'pantry', 'Palermo Rainbow Peppercorns 165g', 299.95, 'images/pepper.jpg', '2026-04-24 13:53:17', 22),
(657, 'pantry', 'Wonderful Pepper & Garlic Pistachios 300g', 459.00, 'images/pistachios.jpg', '2026-04-24 13:53:17', 14),
(658, 'pantry', 'Red Onions (GO) approx. 800g', 300.95, 'images/redonion1.jpg', '2026-04-24 13:53:17', 5),
(659, 'pantry', 'Garlic Imported (RS) approx. 1.1kg (Cebu)', 318.95, 'images/garlic2.jpg', '2026-04-24 13:53:17', 22),
(660, 'pantry', 'Potato approx. 1.5kg', 216.00, 'images/potato.jpg', '2026-04-24 13:53:17', 3),
(661, 'pantry', 'Ritz Original Crackers 200g', 149.99, 'images/crackers.jpg', '2026-04-24 13:53:17', 22),
(662, 'pantry', 'Combos Cheddar Cheese Baked Crackers 178.6g', 188.95, 'images/combos.jpg', '2026-04-24 13:53:17', 22),
(663, 'pantry', 'Palermo Premium Honeycomb 454g', 429.95, 'images/honey.jpg', '2026-04-24 13:53:17', 14),
(664, 'pantry', 'Honey Stars Kids Cereal 450g', 253.00, 'images/cereal.jpg', '2026-04-24 13:53:17', 22),
(665, 'pantry', 'Doña Maria Jasponica Brown Rice 10kg', 1299.95, 'images/brownrice.jpg', '2026-04-24 13:53:17', 5),
(666, 'snacks', 'Lays Classic Potato Chips 180g', 89.95, 'images/lays.jpg', '2026-04-24 13:53:17', 14),
(667, 'snacks', 'Cadbury Dairy Milk Chocolate Bar 100g', 79.95, 'images/cadbury.jpg', '2026-04-24 13:53:17', 22),
(668, 'snacks', 'Oreo Original Vanilla Sandwich Cookies 9 x 27.6g', 78.95, 'images/oreo.jpg', '2026-04-24 13:53:17', 22),
(669, 'snacks', 'Movie Theater Butter Microwave Popcorn 3 x 78g', 149.95, 'images/popcorn.jpg', '2026-04-24 13:53:17', 14),
(670, 'snacks', 'Samjin Cheddar Cheese Pretzel 300g', 269.95, 'images/pretzel.jpg', '2026-04-24 13:53:17', 8),
(671, 'snacks', 'Party Mixed Snacks 1kg', 311.95, 'images/mixedsnacks.jpg', '2026-04-24 13:53:17', 3),
(672, 'snacks', 'Koko Krunch Cookie Cereal Bar 24 x 14.5g', 279.95, 'images/cerealbar.jpg', '2026-04-24 13:53:17', 5),
(673, 'snacks', 'Cheezy Curls Cheese Flavored Snack 300g', 219.95, 'images/cheezycurls.jpg', '2026-04-24 13:53:17', 22),
(674, 'snacks', 'Rise Buddy Seaweed Rice Snack 60g', 61.95, 'images/seaweed.jpg', '2026-04-24 13:53:17', 22),
(675, 'snacks', 'Skittles Fruit Snacks 42s', 1104.95, 'images/skittles.jpg', '2026-04-24 13:53:17', 8),
(676, 'snacks', 'Takis Fuego Rolled Tortilla Chips 280g', 349.95, 'images/takis.jpg', '2026-04-24 13:53:17', 22),
(677, 'snacks', 'Welchs Island Fruits Fruit Snacks 227g', 329.95, 'images/fruitsnack.jpg', '2026-04-24 13:53:17', 22),
(678, 'snacks', 'Mr. Ito Choco Chips 165g', 164.95, 'images/chocochip.png', '2026-04-24 13:53:17', 14),
(679, 'snacks', 'Brownies Cookie Chips 80g', 123.95, 'images/brownie.jpg', '2026-04-24 13:53:17', 5),
(680, 'snacks', 'Nabisco Chips Ahoy! Chunky Chocolate Chip', 269.95, 'images/chipsahoy.jpg', '2026-04-24 13:53:17', 8),
(681, 'snacks', 'Ruffles Cheddar & Sour Cream Potato Chips 170g', 156.95, 'images/ruffles.jpg', '2026-04-24 13:53:17', 14),
(682, 'snacks', 'Healthy Tropics 100% Natural Philippine Banana Chips 100g', 122.95, 'images/bananachips.jpg', '2026-04-24 13:53:17', 3),
(683, 'snacks', 'AriZona Green Tea Fruit Gummy Snacks 142g', 129.95, 'images/gummytea.jpg', '2026-04-24 13:53:17', 22),
(684, 'snacks', 'Pantoja Sansrival Chips in Canister 80g', 214.95, 'images/sansrival.jpg', '2026-04-24 13:53:17', 14),
(685, 'snacks', 'Doritos Smokin BBQ Tortilla Chips 190g', 156.95, 'images/doritos.jpg', '2026-04-24 13:53:17', 8),
(686, 'snacks', 'Edelyns Homemade Nuts Crunchy Garlic Chips 170g', 289.95, 'images/garlicchips.jpg', '2026-04-24 13:53:17', 5),
(687, 'snacks', 'Snack Pack Berry Blue Gel 4s 368g', 109.95, 'images/bluegel.jpg', '2026-04-24 13:53:17', 14),
(688, 'snacks', 'Nestle Crunch Snack 3 x 30g', 129.95, 'images/crunch.jpg', '2026-04-24 13:53:17', 22),
(689, 'snacks', 'Gerber Puffs Strawberry Apple Cereal Snack 42g', 254.95, 'images/babypuffs.jpg', '2026-04-24 13:53:17', 22),
(690, 'snacks', 'Quaker Chocolate Chip Oat Cookies Multipack 150g', 89.95, 'images/chipoat.jpg', '2026-04-24 13:53:17', 8),
(691, 'beverage', 'Coca-Cola Original Taste 1.5L', 85.00, 'images/coke.jpg', '2026-04-24 13:53:17', 22),
(692, 'beverage', 'Nestle Fresh Milk 1L', 105.00, 'images/milk.jpg', '2026-04-24 13:53:17', 22),
(693, 'beverage', 'Del Monte 100% Pineapple Juice 1L', 98.00, 'images/juice.jpg', '2026-04-24 13:53:17', 3),
(694, 'beverage', 'Nature\'s Spring Mineral Water 6L', 110.00, 'images/water.jpg', '2026-04-24 13:53:17', 22),
(695, 'beverage', 'Nescafe Gold Intense 200g', 435.00, 'images/coffee.jpg', '2026-04-24 13:53:17', 8),
(696, 'beverage', 'C2 Green Tea Apple 500ml', 28.00, 'images/tea.jpg', '2026-04-24 13:53:17', 14),
(697, 'beverage', 'Red Bull Energy Drink, 4x250mL', 343.95, 'images/redbull.jpg', '2026-04-24 13:53:17', 22),
(698, 'beverage', 'Pocari Sweat Ion Supply Drink 2L', 172.50, 'images/pocari.jpg', '2026-04-24 13:53:17', 22),
(699, 'beverage', 'Prime Ice Pop Hydration Drink 500mL', 129.25, 'images/prime.jpg', '2026-04-24 13:53:17', 14),
(700, 'beverage', 'Mogu Mogu Lychee Juice with Nata de Coco 320mL', 235.95, 'images/mogu.jpg', '2026-04-24 13:53:17', 5),
(701, 'beverage', 'Granini Banana Juice 1L', 230.95, 'images/juice.jpg', '2026-04-24 13:53:17', 22),
(702, 'beverage', 'Del Monte Four Seasons Juice Drink Promo Pack', 198.95, 'images/fourseasons.jpg', '2026-04-24 13:53:17', 14),
(703, 'beverage', 'Lipa Fresh Coconut Juice 500mL', 76.95, 'images/coconutjuice.jpg', '2026-04-24 13:53:17', 22),
(704, 'beverage', 'Tropicana Twister Regular Orange PET 1L', 57.95, 'images/orangejuice1.jpg', '2026-04-24 13:53:17', 3),
(705, 'beverage', 'Dutch Mill Delight Probiotic Drink 400mL', 110.00, 'images/delight.jpg', '2026-04-24 13:53:17', 8),
(706, 'beverage', 'The Gutsy Captain Ginger & Lemon Kombucha 1L', 214.95, 'images/kombucha.jpg', '2026-04-24 13:53:17', 22),
(707, 'beverage', 'Ocean Spray Cranberry x Raspberry Juice Drink', 325.95, 'images/cranberry.jpg', '2026-04-24 13:53:17', 5),
(708, 'beverage', 'Dutch Mill Strawberry Yogurt Drink 4 x 180mL', 80.95, 'images/dutchmill.jpg', '2026-04-24 13:53:17', 14),
(709, 'beverage', 'Yakult Probiotics Drink 5 x 80mL', 45.50, 'images/yakult.jpg', '2026-04-24 13:53:17', 22),
(710, 'beverage', 'Cowhead Premium Chocolate Milk 1L', 125.25, 'images/chocomilk.jpg', '2026-04-24 13:53:17', 8),
(711, 'health-beauty', 'Pantene Hair Fall Control 450ml', 249.00, 'images/shampoo.jpg', '2026-04-24 13:53:17', 14),
(712, 'health-beauty', 'Vaseline Healthy Bright 400ml', 320.00, 'images/lotion.jpg', '2026-04-24 13:53:17', 22),
(713, 'health-beauty', 'Colgate Total Whitening 150g', 145.00, 'images/toothpaste.jpg', '2026-04-24 13:53:17', 22),
(714, 'health-beauty', 'Rexona Men Ice Cool 50ml', 115.00, 'images/deodorant.jpg', '2026-04-24 13:53:17', 5),
(715, 'health-beauty', 'Cetaphil Gentle Skin Cleanser 250ml', 465.00, 'images/cleanser.jpg', '2026-04-24 13:53:17', 3),
(716, 'health-beauty', 'Cream Silk Damage Control 350ml', 210.00, 'images/conditioner.jpg', '2026-04-24 13:53:17', 22),
(717, 'health-beauty', 'Biore UV Aqua Rich Watery Essence SPF 50+ PA++++ 50g', 495.00, 'images/sunscreen.jpg', '2026-04-24 13:53:17', 14),
(718, 'health-beauty', 'Neutrogena Oil-Free Acne Wash 200ml', 299.00, 'images/facewash.jpg', '2026-04-24 13:53:17', 22),
(719, 'health-beauty', 'Gillette Mach3 Turbo Razor with 8 Cartridges', 1499.95, 'images/razor.jpg', '2026-04-24 13:53:17', 22),
(720, 'health-beauty', 'Calvin Klein CK One Unisex Eau de Toilette 100ml', 1899.95, 'images/perfume.jpg', '2026-04-24 13:53:17', 8),
(721, 'health-beauty', 'Dove Deep Moisture Body Wash 500ml', 199.00, 'images/bodywash.jpg', '2026-04-24 13:53:17', 5),
(722, 'health-beauty', 'Safeguard Pure White Liquid Hand Soap 450mL', 149.95, 'images/handsoap.jpg', '2026-04-24 13:53:17', 22),
(723, 'health-beauty', 'Listerine Cool Mint Mouthwash 1L', 478.00, 'images/mouthwash.jpg', '2026-04-24 13:53:17', 14),
(724, 'health-beauty', 'LOreal Paris 4 Brown Hair Color', 479.00, 'images/haircolor.jpg', '2026-04-24 13:53:17', 22),
(725, 'health-beauty', 'OPI Nail Lacquer 15ml', 850.00, 'images/nailpolish.jpg', '2026-04-24 13:53:17', 8),
(726, 'health-beauty', 'Nivea Body Lotion 400ml', 250.00, 'images/bodylotion.jpg', '2026-04-24 13:53:17', 3),
(727, 'health-beauty', 'Schwarzkopf Gliss 4-in-1 Regeneration Bond-Building Hair Mask 400mL', 457.00, 'images/regeneration.jpg', '2026-04-24 13:53:17', 22),
(728, 'health-beauty', 'Olay Complete Sensitive Plus Face Moisturizer 2 x 177mL', 2495.00, 'images/olay.jpg', '2026-04-24 13:53:17', 5),
(729, 'health-beauty', 'Nenuco Baby Cologne Spray 240mL', 294.00, 'images/colone.jpg', '2026-04-24 13:53:17', 14),
(730, 'health-beauty', 'Nivea Extra Whitening Anti-Perspirant Spray 150 mL', 275.25, 'images/spray.jpg', '2026-04-24 13:53:17', 8),
(731, 'babies-kids', 'MamyPoko Pants Diapers Medium | 38pcs', 320.00, 'images/diapers.jpg', '2026-04-24 13:53:17', 22),
(732, 'babies-kids', 'Pampers Baby Dry Large | 32pcs', 310.00, 'images/diapers_l.jpg', '2026-04-24 13:53:17', 14),
(733, 'babies-kids', 'Baby Wipes Unscented Soft Pack | 80sheets', 65.00, 'images/bkwipes.jpg', '2026-04-24 13:53:17', 22),
(734, 'babies-kids', 'Nido 3+ Growing Up Milk Powder | 700g', 320.00, 'images/bkmilk.jpg', '2026-04-24 13:53:17', 22),
(735, 'babies-kids', 'Bear Brand Fortified Powder Milk | 300g', 135.00, 'images/bkmilk2.jpg', '2026-04-24 13:53:17', 5),
(736, 'babies-kids', 'Similac Infant Formula Stage 1 | 400g', 850.00, 'images/formula.jpg', '2026-04-24 13:53:17', 22),
(737, 'babies-kids', 'Cerelac Rice & Milk Cereal | 250g', 120.00, 'images/cerelac.jpg', '2026-04-24 13:53:17', 3),
(738, 'babies-kids', 'Gerber Baby Cereal Oatmeal | 227g', 165.00, 'images/cerelac2.jpg', '2026-04-24 13:53:17', 14),
(739, 'babies-kids', 'Johnson’s Baby Shampoo Gentle Care | 200ml', 115.00, 'images/babyshampoo.jpg', '2026-04-24 13:53:17', 22),
(740, 'babies-kids', 'Johnson’s Baby Soap Mild | 100g', 45.00, 'images/babysoap.jpg', '2026-04-24 13:53:17', 8),
(741, 'babies-kids', 'Johnson’s Baby Lotion Soft & Smooth | 200ml', 150.00, 'images/bklotion.jpg', '2026-04-24 13:53:17', 14),
(742, 'babies-kids', 'Johnson’s Baby Powder Classic | 200g', 120.00, 'images/powder.jpg', '2026-04-24 13:53:17', 5),
(743, 'babies-kids', 'Baby Feeding Bottles Set BPA-Free | 3pcs', 180.00, 'images/bottles.jpg', '2026-04-24 13:53:17', 22),
(744, 'babies-kids', 'Silicone Baby Pacifier Soft Comfort | 2pcs', 90.00, 'images/pacifier.jpg', '2026-04-24 13:53:17', 14),
(745, 'babies-kids', 'Baby Rattle Toys Set Educational | 5pcs', 250.00, 'images/toys.jpg', '2026-04-24 13:53:17', 8),
(746, 'babies-kids', 'Baby Teether Silicone Cooling Toy | 1pc', 110.00, 'images/teether.jpg', '2026-04-24 13:53:17', 22),
(747, 'babies-kids', 'Gerber Baby Snacks Puffs Banana | 42g', 95.00, 'images/snacks.jpg', '2026-04-24 13:53:17', 14),
(748, 'babies-kids', 'Kids Juice Apple Flavor Pack | 6x200ml', 120.00, 'images/bkjuice.jpg', '2026-04-24 13:53:17', 3),
(749, 'babies-kids', 'Milo Kids Biscuits Chocolate | 120g', 55.00, 'images/bkcookies.jpg', '2026-04-24 13:53:17', 5),
(750, 'babies-kids', 'Bear Brand Jr. School Milk Drink | 200ml', 25.00, 'images/schoolmilk.jpg', '2026-04-24 13:53:17', 8),
(751, 'home-care', 'Tide Powder Detergent Original | 1kg', 175.00, 'images/detergent.jpg', '2026-04-24 13:53:17', 22),
(752, 'home-care', 'Surf Powder Detergent Calamansi | 1.4kg', 135.00, 'images/surf.jpg', '2026-04-24 13:53:17', 22),
(753, 'home-care', 'Downy Fabric Conditioner April Fresh | 1.5L', 165.00, 'images/downy.jpg', '2026-04-24 13:53:17', 14),
(754, 'home-care', 'Zonrox Bleach Regular | 1L', 75.00, 'images/zonrox.jpg', '2026-04-24 13:53:17', 22),
(755, 'home-care', 'Mr. Clean Multi-Surface Cleaner | 500ml', 95.00, 'images/mrclean.jpg', '2026-04-24 13:53:17', 8),
(756, 'home-care', 'Pine-Sol Disinfectant Cleaner Original | 500ml', 140.00, 'images/pinesol.jpg', '2026-04-24 13:53:17', 5),
(757, 'home-care', 'Joy Dishwashing Liquid Lemon | 495ml', 95.00, 'images/joy.jpg', '2026-04-24 13:53:17', 22),
(758, 'home-care', 'Pril Dishwashing Liquid Aloe Vera | 400ml', 85.00, 'images/pril.jpg', '2026-04-24 13:53:17', 22),
(759, 'home-care', 'Kitchen Sponge Scrubber Set | 5pcs', 60.00, 'images/sponge.jpg', '2026-04-24 13:53:17', 3),
(760, 'home-care', 'Garbage Bags Heavy Duty Black | 20pcs', 90.00, 'images/trashbag.jpg', '2026-04-24 13:53:17', 8),
(761, 'home-care', 'Glade Air Freshener Lavender | 320ml', 120.00, 'images/hcairfreshener.jpg', '2026-04-24 13:53:17', 22),
(762, 'home-care', 'Domex Toilet Bowl Cleaner | 500ml', 85.00, 'images/toiletcleaner.jpg', '2026-04-24 13:53:17', 14),
(763, 'home-care', 'Kleenex Bathroom Tissue 2-Ply | 10 rolls', 160.00, 'images/hctissue.jpg', '2026-04-24 13:53:17', 5),
(764, 'home-care', 'Kitchen Paper Towels Multi-Purpose | 2 rolls', 110.00, 'images/paper.jpg', '2026-04-24 13:53:17', 22),
(765, 'home-care', 'Microfiber Mop Floor Cleaning Set | 1pc', 250.00, 'images/mop.jpg', '2026-04-24 13:53:17', 8),
(766, 'home-care', 'Soft Bristle Broom Indoor Use | 1pc', 140.00, 'images/broom.jpg', '2026-04-24 13:53:17', 22),
(767, 'home-care', 'Plastic Cleaning Bucket 10L Durable | 1pc', 180.00, 'images/bucket.jpg', '2026-04-24 13:53:17', 22),
(768, 'home-care', 'Rubber Cleaning Gloves Household Use | Pair', 55.00, 'images/hcgloves.jpg', '2026-04-24 13:53:17', 14),
(769, 'home-care', 'Baygon Insect Killer Spray Original | 300ml', 145.00, 'images/insect.jpg', '2026-04-24 13:53:17', 22),
(770, 'home-care', 'Alcohol Spray 70% Ethyl Sanitizer | 500ml', 95.00, 'images/hcsanitizer.jpg', '2026-04-24 13:53:17', 3),
(771, 'pet-care', 'Pedigree Adult Dog Food 1kg', 180.00, 'images/dogfood.jpg', '2026-04-24 13:53:17', 14),
(772, 'pet-care', 'Whiskas Ocean Fish Cat Food 1kg', 220.00, 'images/catfood.jpg', '2026-04-24 13:53:17', 22),
(773, 'pet-care', 'Pedigree Dentastix Dog Treats', 95.00, 'images/dogtreats.jpg', '2026-04-24 13:53:17', 22),
(774, 'pet-care', 'Temptations Cat Treats 85g', 120.00, 'images/cattreats.jpg', '2026-04-24 13:53:17', 14),
(775, 'pet-care', 'Saint Roche Dog Shampoo 250ml', 160.00, 'images/petshampoo.jpg', '2026-04-24 13:53:17', 8),
(776, 'pet-care', 'Madre de Cacao Pet Soap', 80.00, 'images/petsoap.jpg', '2026-04-24 13:53:17', 22),
(777, 'pet-care', 'Hartz Flea & Tick Powder', 210.00, 'images/flea_powder.jpg', '2026-04-24 13:53:17', 5),
(778, 'pet-care', 'Adjustable Nylon Dog Collar', 120.00, 'images/petcollar.jpg', '2026-04-24 13:53:17', 22),
(779, 'pet-care', 'Nylon Dog Leash 1.2m', 150.00, 'images/petleash.jpg', '2026-04-24 13:53:17', 22),
(780, 'pet-care', 'Orocan Plastic Pet Bowl', 70.00, 'images/petbowl.jpg', '2026-04-24 13:53:17', 8),
(781, 'pet-care', 'Automatic Pet Water Dispenser', 350.00, 'images/petwaterdispenser.jpg', '2026-04-24 13:53:17', 3),
(782, 'pet-care', 'Meowtech Cat Litter 5L', 180.00, 'images/littersand.jpg', '2026-04-24 13:53:17', 22),
(783, 'pet-care', 'Cat Litter Box with Scoop', 320.00, 'images/litterbox.jpg', '2026-04-24 13:53:17', 14),
(784, 'pet-care', 'Biodegradable Pet Waste Bags (Roll)', 90.00, 'images/poopbags.jpg', '2026-04-24 13:53:17', 5),
(785, 'pet-care', 'Pet Cleaning Wipes 80s', 140.00, 'images/petwipes.jpg', '2026-04-24 13:53:17', 8),
(786, 'pet-care', 'Rubber Dog Chew Toy', 110.00, 'images/dogtoy.jpg', '2026-04-24 13:53:17', 14),
(787, 'pet-care', 'Feather Teaser Cat Toy', 95.00, 'images/cattoy.jpg', '2026-04-24 13:53:17', 22),
(788, 'pet-care', 'Foldable Pet Cage Small', 850.00, 'images/cage.jpg', '2026-04-24 13:53:17', 22),
(789, 'pet-care', 'Pet Carrier Bag Medium', 600.00, 'images/carrier.jpg', '2026-04-24 13:53:17', 14),
(790, 'pet-care', 'LC-Vit Plus Multivitamins 60ml', 130.00, 'images/vitamins.jpg', '2026-04-24 13:53:17', 8),
(791, 'pet-care', 'Dr. Shiba Anti-Tick & Flea Soap', 90.00, 'images/ticksoap.jpg', '2026-04-24 13:53:17', 5),
(792, 'pet-care', 'Disposable Dog Diapers (10pcs)', 180.00, 'images/dogdiaper.jpg', '2026-04-24 13:53:17', 3),
(793, 'pet-care', 'Pet Training Pads 10pcs', 150.00, 'images/trainingpads.jpg', '2026-04-24 13:53:17', 22),
(794, 'pet-care', 'Basic Pet Grooming Kit', 280.00, 'images/groomingkit.jpg', '2026-04-24 13:53:17', 22),
(795, 'pet-care', 'Glade Pet Odor Eliminator Spray', 370.00, 'images/airfreshener.jpg', '2026-04-24 13:53:17', 8),
(796, 'diy-hardware', 'Lotus Claw Hammer 16oz', 320.00, 'images/hammer.jpg', '2026-04-24 13:53:17', 22),
(797, 'diy-hardware', 'Stanley Screwdriver Set 6pcs', 450.00, 'images/screwdriver.jpg', '2026-04-24 13:53:17', 22),
(798, 'diy-hardware', 'Ingco Measuring Tape 5m', 180.00, 'images/measuringtape.jpg', '2026-04-24 13:53:17', 5),
(799, 'diy-hardware', 'Lotus Adjustable Wrench 8-inch', 280.00, 'images/adjustablewrench.jpg', '2026-04-24 13:53:17', 22),
(800, 'diy-hardware', 'Ingco Combination Pliers 7-inch', 220.00, 'images/pliers.jpg', '2026-04-24 13:53:17', 8),
(801, 'diy-hardware', 'Stanley Hand Saw 20-inch', 350.00, 'images/handsaw.jpg', '2026-04-24 13:53:17', 14),
(802, 'diy-hardware', 'Davies Paint Brush 2-inch', 120.00, 'images/paintbrush.jpg', '2026-04-24 13:53:17', 22),
(803, 'diy-hardware', 'Boysen Paint Roller Set', 250.00, 'images/paintroller.jpg', '2026-04-24 13:53:17', 3),
(804, 'diy-hardware', 'Pioneer Electrical Tape Black', 35.00, 'images/electricaltape.jpg', '2026-04-24 13:53:17', 14),
(805, 'diy-hardware', 'Omni Extension Cord 4-Gang', 300.00, 'images/extensioncord.jpg', '2026-04-24 13:53:17', 5),
(806, 'diy-hardware', 'Firefly LED Bulb 9W', 130.00, 'images/ledbulb.jpg', '2026-04-24 13:53:17', 22),
(807, 'diy-hardware', 'Firefly Rechargeable Flashlight', 320.00, 'images/flashlight.jpg', '2026-04-24 13:53:17', 14),
(808, 'diy-hardware', 'Bosch Electric Drill 10mm', 2500.00, 'images/drill.jpg', '2026-04-24 13:53:17', 22),
(809, 'diy-hardware', 'Ingco Safety Gloves', 90.00, 'images/gloves.jpg', '2026-04-24 13:53:17', 22),
(810, 'diy-hardware', '3M Dust Mask (Pack of 2)', 120.00, 'images/mask.jpg', '2026-04-24 13:53:17', 8),
(811, 'diy-hardware', 'Rugby Silicone Sealant Clear', 180.00, 'images/silicone.jpg', '2026-04-24 13:53:17', 22),
(812, 'diy-hardware', 'Rugby Contact Cement 200ml', 95.00, 'images/rugby.jpg', '2026-04-24 13:53:17', 5),
(813, 'diy-hardware', 'Yale Padlock 40mm', 350.00, 'images/padlock.jpg', '2026-04-24 13:53:17', 14),
(814, 'diy-hardware', 'Omni Door Hinges 3-inch (Pair)', 75.00, 'images/hinges.jpg', '2026-04-24 13:53:17', 3),
(815, 'diy-hardware', 'Royu Light Switch Single', 85.00, 'images/switch.jpg', '2026-04-24 13:53:17', 8),
(816, 'diy-hardware', 'Royu Convenience Outlet', 95.00, 'images/outlet.jpg', '2026-04-24 13:53:17', 14),
(817, 'diy-hardware', 'Firefly Bulb Holder E27', 60.00, 'images/bulbholder.jpg', '2026-04-24 13:53:17', 22),
(818, 'diy-hardware', 'Bosny Spray Paint 400ml', 180.00, 'images/spraypaint.jpg', '2026-04-24 13:53:17', 22),
(819, 'diy-hardware', 'WD-40 Multi-Use Product 200ml', 220.00, 'images/wd40.jpg', '2026-04-24 13:53:17', 5),
(820, 'diy-hardware', 'Glade Automatic Air Freshener Spray', 370.00, 'images/airfreshener.jpg', '2026-04-24 13:53:17', 8),
(821, 'home-appliance', 'Asahi Electric Fan 16-inch', 1450.00, 'images/electricfan.jpg', '2026-04-24 13:53:17', 22),
(822, 'home-appliance', 'Imarflex Rice Cooker 1.5L', 1350.00, 'images/ricecooker.jpg', '2026-04-24 13:53:17', 14),
(823, 'home-appliance', 'Kyowa Electric Kettle 1.7L', 900.00, 'images/electrickettle.jpg', '2026-04-24 13:53:17', 22),
(824, 'home-appliance', 'Hanabishi Blender 1.5L', 1200.00, 'images/blender.jpg', '2026-04-24 13:53:17', 22),
(825, 'home-appliance', 'Philips Dry Iron Non-Stick', 850.00, 'images/flatiron.jpg', '2026-04-24 13:53:17', 3),
(826, 'home-appliance', 'Dowell Washing Machine 7kg Single Tub', 4800.00, 'images/washingmachine.jpg', '2026-04-24 13:53:17', 5),
(827, 'home-appliance', 'Sharp Microwave Oven 20L', 3500.00, 'images/microwave.jpg', '2026-04-24 13:53:17', 22),
(828, 'home-appliance', 'Condura Mini Refrigerator 3.5 cu.ft', 7800.00, 'images/refrigerator.jpg', '2026-04-24 13:53:17', 14),
(829, 'home-appliance', 'Royu Extension Cord 4-Gang with Switch', 280.00, 'images/extensioncord.jpg', '2026-04-24 13:53:17', 22),
(830, 'home-appliance', 'Firefly LED Bulb 9W', 130.00, 'images/ledbulb.jpg', '2026-04-24 13:53:17', 8),
(831, 'home-appliance', 'Firefly Rechargeable Flashlight', 320.00, 'images/flashlight.jpg', '2026-04-24 13:53:17', 14),
(832, 'home-appliance', 'Fujidenzo Water Dispenser Hot & Cold', 4200.00, 'images/waterdispenser.jpg', '2026-04-24 13:53:17', 22),
(833, 'home-appliance', 'Imarflex Induction Cooker', 1600.00, 'images/inductioncooker.jpg', '2026-04-24 13:53:17', 5),
(834, 'home-appliance', 'La Germania Single Burner Gas Stove', 950.00, 'images/gasstove.jpg', '2026-04-24 13:53:17', 14),
(835, 'home-appliance', 'Omni Universal Adapter Plug', 160.00, 'images/electricplug.jpg', '2026-04-24 13:53:17', 8),
(836, 'home-appliance', 'Asahi Floor Fan 18-inch', 1900.00, 'images/floorfan.jpg', '2026-04-24 13:53:17', 3),
(837, 'home-appliance', 'Kyowa Handheld Vacuum Cleaner', 1300.00, 'images/vacuum.jpg', '2026-04-24 13:53:17', 14),
(838, 'home-appliance', 'Hanabishi Air Cooler 10L', 3700.00, 'images/aircooler.jpg', '2026-04-24 13:53:17', 22),
(839, 'home-appliance', 'Jaguar Power Bank 10000mAh', 750.00, 'images/powerbank.jpg', '2026-04-24 13:53:17', 22),
(840, 'home-appliance', 'Orocan Digital Alarm Clock', 380.00, 'images/clock.jpg', '2026-04-24 13:53:17', 5),
(841, 'home-appliance', 'Asahi Wall Fan 16-inch', 1500.00, 'images/wallfan.jpg', '2026-04-24 13:53:17', 22),
(842, 'home-appliance', 'Firefly LED Desk Lamp', 480.00, 'images/lamp.jpg', '2026-04-24 13:53:17', 22),
(843, 'home-appliance', 'Kyowa 2-Slice Bread Toaster', 1050.00, 'images/toaster.jpg', '2026-04-24 13:53:17', 14),
(844, 'home-appliance', 'Imarflex Coffee Maker 0.6L', 1400.00, 'images/coffeemaker.jpg', '2026-04-24 13:53:17', 22),
(845, 'home-appliance', 'Glade Automatic Air Freshener Spray', 370.00, 'images/airfreshener.jpg', '2026-04-24 13:53:17', 8),
(846, 'health-hygiene', 'Green Cross Isopropyl Alcohol 70% | 500ml', 89.00, 'images/alcohol.jpg', '2026-04-24 13:53:17', 14),
(847, 'health-hygiene', 'Interfolded Paper Towel 175 Pulls', 45.00, 'images/tissue.jpg', '2026-04-24 13:53:17', 3),
(848, 'health-hygiene', 'Safeguard Liquid Hand Soap | 225ml', 120.00, 'images/handsoap.jpg', '2026-04-24 13:53:17', 22),
(849, 'health-hygiene', 'Colgate Total Toothpaste | 150g', 95.00, 'images/toothpaste.jpg', '2026-04-24 13:53:17', 14),
(850, 'health-hygiene', 'Oral-B Toothbrush Medium', 75.00, 'images/toothbrush.jpg', '2026-04-24 13:53:17', 8),
(851, 'health-hygiene', 'Head & Shoulders Shampoo Cool Menthol | 170ml', 145.00, 'images/shampoo.jpg', '2026-04-24 13:53:17', 22),
(852, 'health-hygiene', 'Cream Silk Conditioner Standout Straight | 180ml', 110.00, 'images/conditioner.jpg', '2026-04-24 13:53:17', 14),
(853, 'health-hygiene', 'Dove Deeply Nourishing Body Wash | 200ml', 160.00, 'images/bodywash.jpg', '2026-04-24 13:53:17', 22),
(854, 'health-hygiene', 'Celeteque Hydration Facial Wash | 100ml', 130.00, 'images/facewash.jpg', '2026-04-24 13:53:17', 5),
(855, 'health-hygiene', 'Lactacyd Feminine Wash | 150ml', 180.00, 'images/femininewash.jpg', '2026-04-24 13:53:17', 8),
(856, 'health-hygiene', 'Rexona Roll-On Deodorant Powder Dry | 50ml', 120.00, 'images/deodorant.jpg', '2026-04-24 13:53:17', 22),
(857, 'health-hygiene', 'Johnson\'s Cotton Buds | 100 pcs', 65.00, 'images/cottonbuds.jpg', '2026-04-24 13:53:17', 22),
(858, 'health-hygiene', 'Swisspers Cotton Balls | 100 pcs', 90.00, 'images/cottonballs.jpg', '2026-04-24 13:53:17', 3),
(859, 'health-hygiene', 'Disposable Face Mask 3-Ply | 10 pcs', 70.00, 'images/facemask.jpg', '2026-04-24 13:53:17', 22),
(860, 'health-hygiene', 'Sanicare Baby Wipes Unscented | 80 pulls', 95.00, 'images/wetwipes.jpg', '2026-04-24 13:53:17', 8),
(861, 'health-hygiene', 'Whisper Regular Flow Sanitary Pads | 8 pcs', 85.00, 'images/sanitarypads.jpg', '2026-04-24 13:53:17', 5),
(862, 'health-hygiene', 'Carefree Pantyliners | 20 pcs', 75.00, 'images/pantyliner.jpg', '2026-04-24 13:53:17', 22),
(863, 'health-hygiene', 'Gillette Blue II Disposable Razor | 2 pcs', 110.00, 'images/razor.jpg', '2026-04-24 13:53:17', 22),
(864, 'health-hygiene', 'Hygienix Hand Gel Sanitizer | 50ml', 60.00, 'images/handgel.jpg', '2026-04-24 13:53:17', 14),
(865, 'health-hygiene', 'Listerine Cool Mint Mouthwash | 250ml', 180.00, 'images/mouthwash.jpg', '2026-04-24 13:53:17', 8),
(866, 'health-hygiene', 'Fissan Foot Powder | 50g', 70.00, 'images/footpowder.jpg', '2026-04-24 13:53:17', 22),
(867, 'health-hygiene', 'Band-Aid Adhesive Bandages | 20 pcs', 85.00, 'images/bandage.jpg', '2026-04-24 13:53:17', 14),
(868, 'health-hygiene', 'Hydrogen Peroxide Solution | 120ml', 50.00, 'images/hydrogenperoxide.jpg', '2026-04-24 13:53:17', 5),
(869, 'health-hygiene', 'Bioderm Germicidal Soap | 135g', 45.00, 'images/antibacterialsoap.jpg', '2026-04-24 13:53:17', 3),
(870, 'health-hygiene', 'Bath Loofah Scrubber', 55.00, 'images/loofah.jpg', '2026-04-24 13:53:17', 8),
(871, 'pantry', 'Silver Swan Vinegar Pet Bottle 1L', 45.50, 'images/1777213705_silverswanvinegar.jpg', '2026-04-26 14:28:25', 25);

-- --------------------------------------------------------

--
-- Table structure for table `subscribers`
--

CREATE TABLE `subscribers` (
  `id` int(11) NOT NULL,
  `email` varchar(255) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `subscribers`
--

INSERT INTO `subscribers` (`id`, `email`, `created_at`) VALUES
(2, 'chogscastro@gmail.com', '2026-05-02 03:26:34'),
(3, 'chogsaxie@gmail.com', '2026-05-02 11:02:44');

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` int(11) NOT NULL,
  `full_name` varchar(120) NOT NULL,
  `username` varchar(60) NOT NULL,
  `email` varchar(150) NOT NULL,
  `password_hash` varchar(255) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `newsletter_subscribed` tinyint(1) NOT NULL DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `full_name`, `username`, `email`, `password_hash`, `created_at`, `newsletter_subscribed`) VALUES
(1, 'chogs castro', 'chogs', 'chogscastro@gmail.com', '$2y$10$fRNWr3kiW2dldh7EkEPLqObS3TAT5mEHaRf4V2In66TggDR0nEa1a', '2026-04-24 13:38:32', 1),
(2, 'jung jung', 'jungjungsahur', 'emailnijung@gmail.com', '$2y$10$woFjJ6jXFGyz.FBmcJj4q.FwTMtNS.e1KA3k30BlZe7byz2T7r1f6', '2026-04-24 14:02:20', 1);

-- --------------------------------------------------------

--
-- Table structure for table `user_addresses`
--

CREATE TABLE `user_addresses` (
  `id` int(10) UNSIGNED NOT NULL,
  `user_id` int(11) NOT NULL,
  `label` varchar(60) NOT NULL DEFAULT 'Home',
  `address_line` varchar(300) NOT NULL,
  `is_default` tinyint(1) NOT NULL DEFAULT 0,
  `created_at` datetime NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `user_payment_gateways`
--

CREATE TABLE `user_payment_gateways` (
  `id` int(10) UNSIGNED NOT NULL,
  `user_id` int(11) NOT NULL,
  `gateway_key` varchar(40) NOT NULL,
  `created_at` datetime NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `user_payment_gateways`
--

INSERT INTO `user_payment_gateways` (`id`, `user_id`, `gateway_key`, `created_at`) VALUES
(1, 1, 'gcash', '2026-04-27 22:55:05');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `admin_accounts`
--
ALTER TABLE `admin_accounts`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `username` (`username`);

--
-- Indexes for table `cart_items`
--
ALTER TABLE `cart_items`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `unique_user_product` (`user_id`,`product_id`),
  ADD KEY `fk_cart_product` (`product_id`);

--
-- Indexes for table `categories`
--
ALTER TABLE `categories`
  ADD PRIMARY KEY (`slug`);

--
-- Indexes for table `orders`
--
ALTER TABLE `orders`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `order_number` (`order_number`),
  ADD KEY `user_id` (`user_id`),
  ADD KEY `delivery_address_id` (`delivery_address_id`);

--
-- Indexes for table `order_items`
--
ALTER TABLE `order_items`
  ADD PRIMARY KEY (`id`),
  ADD KEY `order_id` (`order_id`),
  ADD KEY `product_id` (`product_id`);

--
-- Indexes for table `products`
--
ALTER TABLE `products`
  ADD PRIMARY KEY (`id`),
  ADD KEY `fk_products_category` (`category_slug`);

--
-- Indexes for table `subscribers`
--
ALTER TABLE `subscribers`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `email` (`email`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `username` (`username`),
  ADD UNIQUE KEY `email` (`email`);

--
-- Indexes for table `user_addresses`
--
ALTER TABLE `user_addresses`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `user_payment_gateways`
--
ALTER TABLE `user_payment_gateways`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `uq_gw` (`user_id`,`gateway_key`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `admin_accounts`
--
ALTER TABLE `admin_accounts`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `cart_items`
--
ALTER TABLE `cart_items`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=40;

--
-- AUTO_INCREMENT for table `orders`
--
ALTER TABLE `orders`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `order_items`
--
ALTER TABLE `order_items`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=17;

--
-- AUTO_INCREMENT for table `products`
--
ALTER TABLE `products`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=872;

--
-- AUTO_INCREMENT for table `subscribers`
--
ALTER TABLE `subscribers`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `user_addresses`
--
ALTER TABLE `user_addresses`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `user_payment_gateways`
--
ALTER TABLE `user_payment_gateways`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `cart_items`
--
ALTER TABLE `cart_items`
  ADD CONSTRAINT `fk_cart_product` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk_cart_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `orders`
--
ALTER TABLE `orders`
  ADD CONSTRAINT `orders_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `orders_ibfk_2` FOREIGN KEY (`delivery_address_id`) REFERENCES `user_addresses` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `order_items`
--
ALTER TABLE `order_items`
  ADD CONSTRAINT `order_items_ibfk_1` FOREIGN KEY (`order_id`) REFERENCES `orders` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `order_items_ibfk_2` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `products`
--
ALTER TABLE `products`
  ADD CONSTRAINT `fk_products_category` FOREIGN KEY (`category_slug`) REFERENCES `categories` (`slug`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `user_addresses`
--
ALTER TABLE `user_addresses`
  ADD CONSTRAINT `user_addresses_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `user_payment_gateways`
--
ALTER TABLE `user_payment_gateways`
  ADD CONSTRAINT `user_payment_gateways_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
