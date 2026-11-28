-- ============================================================
-- Pretty Layers — FULL database (your data of 19 Nov 2026 + latest updates)
-- Import from anywhere in phpMyAdmin:  Import → Choose file → Go
-- Uses database `pretty_layers` (creates it if missing) and replaces
-- the existing tables. Includes Super Admin login:
--   Username: prettylayerss.com   (old admin/admin123 removed)
-- ============================================================
CREATE DATABASE IF NOT EXISTS `pretty_layers` DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE `pretty_layers`;
SET FOREIGN_KEY_CHECKS = 0;

-- phpMyAdmin SQL Dump
-- version 5.2.3
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Nov 19, 2026 at 03:48 AM
-- Server version: 10.11.16-MariaDB
-- PHP Version: 8.3.36

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `pretty_layers`
--

-- --------------------------------------------------------

--
-- Table structure for table `admins`
--

DROP TABLE IF EXISTS `admins`;
CREATE TABLE `admins` (
  `id` int(10) UNSIGNED NOT NULL,
  `username` varchar(60) NOT NULL,
  `password` varchar(255) NOT NULL,
  `name` varchar(100) DEFAULT NULL,
  `created_at` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `admins`
--

INSERT INTO `admins` (`id`, `username`, `password`, `name`, `created_at`) VALUES
(1, 'admin', '$2y$10$y1FzaDxMfPhUoJESaPXBueKBXxtBym1rdxduTvjRS2tvMDKf1tPXe', 'Administrator', '2026-11-16 17:37:23');

-- --------------------------------------------------------

--
-- Table structure for table `categories`
--

DROP TABLE IF EXISTS `categories`;
CREATE TABLE `categories` (
  `id` int(10) UNSIGNED NOT NULL,
  `name` varchar(120) NOT NULL,
  `slug` varchar(140) NOT NULL,
  `image` varchar(255) DEFAULT NULL,
  `description` text DEFAULT NULL,
  `sort_order` int(11) DEFAULT 0,
  `is_active` tinyint(4) DEFAULT 1,
  `kind` varchar(10) DEFAULT 'normal'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `categories`
--

INSERT INTO `categories` (`id`, `name`, `slug`, `image`, `description`, `sort_order`, `is_active`, `kind`) VALUES
(1, 'Kurti', 'kurti', 'assets/img/products/kurti-floral-lavender.svg', '', 100, 0, 'normal'),
(2, 'Saree', 'saree', 'assets/img/products/saree-jamdani-green.svg', '', 101, 0, 'normal'),
(3, 'Three Piece', 'three-piece', 'assets/img/products/threepiece-lawn-mint.svg', '', 102, 0, 'normal'),
(4, 'Gowns', 'gowns', 'assets/img/products/gown-tiered-lavender.svg', '', 103, 0, 'normal'),
(5, 'Tops', 'tops', 'assets/img/products/top-peplum-milk.svg', '', 104, 0, 'normal'),
(6, 'Hijab & Scarf', 'hijab-scarf', 'assets/img/products/hijab-chiffon-rose.svg', '', 105, 0, 'normal'),
(7, 'Bottoms', 'bottoms', 'assets/img/products/palazzo-wide-green.svg', '', 106, 0, 'normal'),
(8, 'Bags', 'bags', 'uploads/products/20261117-eca6467e8e6e.png', '', 107, 1, 'normal'),
(9, 'Jewelry', 'jewelry', 'assets/img/products/jewelry-jhumka-gold.svg', '', 108, 0, 'normal'),
(10, 'NEW!', 'new', 'uploads/products/20261117-fb26accadf8e.png', '', 0, 1, 'new'),
(11, 'BRAS', 'bras', 'uploads/products/20261117-ecb93d5dfac6.png', '', 1, 1, 'normal'),
(12, 'PANTIES', 'panties', 'uploads/products/20261117-b75a29af9d4c.png', '', 2, 1, 'normal'),
(13, 'SLEEP', 'sleep', 'uploads/products/20261117-11875cadd7bf.png', '', 3, 1, 'normal'),
(14, 'BEAUTY', 'beauty', 'uploads/products/20261117-3c01c3134da5.png', '', 4, 1, 'normal'),
(15, 'LINGERIE', 'lingerie', 'uploads/products/20261117-80d05d24f7ff.png', '', 5, 1, 'normal'),
(16, 'SPORT & LOUNGE', 'sport-lounge', 'uploads/products/20261117-ecd521d74603.png', '', 6, 1, 'normal'),
(17, 'ACCESSORIES', 'accessories', 'uploads/products/20261117-6e9444945799.png', '', 7, 1, 'normal'),
(18, 'SALE', 'sale', 'uploads/products/20261117-f192ac9a5f67.png', '', 9, 1, 'sale'),
(19, 'CLOTHING', 'clothing', 'assets/img/catalog/pl-d9879dcb.jpg', 'Dresses, tops & beachwear', 8, 1, 'normal');

-- --------------------------------------------------------

--
-- Table structure for table `customers`
--

DROP TABLE IF EXISTS `customers`;
CREATE TABLE `customers` (
  `id` int(10) UNSIGNED NOT NULL,
  `name` varchar(120) NOT NULL,
  `phone` varchar(20) NOT NULL,
  `email` varchar(150) DEFAULT NULL,
  `password` varchar(255) NOT NULL,
  `address` text DEFAULT NULL,
  `city` varchar(80) DEFAULT NULL,
  `created_at` datetime DEFAULT NULL,
  `last_login` datetime DEFAULT NULL,
  `google_id` varchar(64) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `newsletter`
--

DROP TABLE IF EXISTS `newsletter`;
CREATE TABLE `newsletter` (
  `id` int(10) UNSIGNED NOT NULL,
  `email` varchar(150) NOT NULL,
  `created_at` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `orders`
--

DROP TABLE IF EXISTS `orders`;
CREATE TABLE `orders` (
  `id` int(10) UNSIGNED NOT NULL,
  `order_no` varchar(30) NOT NULL,
  `customer_name` varchar(120) DEFAULT NULL,
  `phone` varchar(30) DEFAULT NULL,
  `email` varchar(150) DEFAULT NULL,
  `address` text DEFAULT NULL,
  `city` varchar(80) DEFAULT NULL,
  `zone` varchar(20) DEFAULT NULL,
  `note` text DEFAULT NULL,
  `subtotal` decimal(10,2) DEFAULT NULL,
  `shipping` decimal(10,2) DEFAULT NULL,
  `total` decimal(10,2) DEFAULT NULL,
  `payment_method` varchar(20) DEFAULT NULL,
  `trx_id` varchar(60) DEFAULT NULL,
  `status` varchar(20) DEFAULT 'pending',
  `stock_returned` tinyint(4) DEFAULT 0,
  `created_at` datetime DEFAULT NULL,
  `updated_at` datetime DEFAULT NULL,
  `customer_id` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `orders`
--

INSERT INTO `orders` (`id`, `order_no`, `customer_name`, `phone`, `email`, `address`, `city`, `zone`, `note`, `subtotal`, `shipping`, `total`, `payment_method`, `trx_id`, `status`, `stock_returned`, `created_at`, `updated_at`, `customer_id`) VALUES
(1, 'LV2611101001', 'Nusrat Jahan', '01711-223344', '', 'Mirpur 10, Dhaka', 'Dhaka', 'inside', '', 2550.00, 70.00, 2620.00, 'cod', '', 'delivered', 0, '2026-11-10 15:37:23', '2026-11-10 15:37:23', NULL),
(2, 'LV2611121002', 'Farzana Akter', '01822-556677', '', 'Agrabad, Chattogram', 'Chattogram', 'outside', '', 2990.00, 130.00, 3120.00, 'cod', '', 'shipped', 0, '2026-11-12 14:37:23', '2026-11-12 14:37:23', NULL),
(3, 'LV2611141003', 'Sadia Islam', '01933-778899', '', 'Uttara Sector 7, Dhaka', 'Dhaka', 'inside', '', 5100.00, 0.00, 5100.00, 'cod', '', 'confirmed', 0, '2026-11-14 13:37:23', '2026-11-14 13:37:23', NULL),
(4, 'LV2611161004', 'Tanjila Rahman', '01644-112233', '', 'Zindabazar, Sylhet', 'Sylhet', 'outside', '', 3970.00, 0.00, 3970.00, 'cod', '', 'shipped', 0, '2026-11-16 12:37:23', '2026-11-16 17:58:29', NULL);

-- --------------------------------------------------------

--
-- Table structure for table `order_items`
--

DROP TABLE IF EXISTS `order_items`;
CREATE TABLE `order_items` (
  `id` int(10) UNSIGNED NOT NULL,
  `order_id` int(11) NOT NULL,
  `product_id` int(11) DEFAULT NULL,
  `variant_id` int(11) DEFAULT NULL,
  `name` varchar(200) DEFAULT NULL,
  `size` varchar(40) DEFAULT NULL,
  `color` varchar(40) DEFAULT NULL,
  `sku` varchar(80) DEFAULT NULL,
  `image` varchar(255) DEFAULT NULL,
  `price` decimal(10,2) DEFAULT NULL,
  `cost_price` decimal(10,2) DEFAULT NULL,
  `qty` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `order_items`
--

INSERT INTO `order_items` (`id`, `order_id`, `product_id`, `variant_id`, `name`, `size`, `color`, `sku`, `image`, `price`, `cost_price`, `qty`) VALUES
(1, 1, 1, 3, 'Floral Embroidered Cotton Kurti', 'L', 'Lavender', 'LV-0101-L', 'assets/img/products/kurti-floral-lavender.svg', 1250.00, 780.00, 1),
(2, 1, 18, 51, 'Kundan Jhumka Earrings', '', 'Gold', 'LV-0118-FS', 'assets/img/products/jewelry-jhumka-gold.svg', 650.00, 250.00, 2),
(3, 2, 9, 29, 'Lavender Tiered Maxi Gown', 'L', 'Lavender', 'LV-0109-L', 'assets/img/products/gown-tiered-lavender.svg', 2990.00, 1900.00, 1),
(4, 3, 4, 13, 'Jamdani Silk Saree', '', 'Forest Green', 'LV-0104-FS', 'assets/img/products/saree-jamdani-green.svg', 4200.00, 2900.00, 1),
(5, 3, 12, 39, 'Premium Chiffon Hijab', '', 'Dusty Rose', 'LV-0112-FS', 'assets/img/products/hijab-chiffon-rose.svg', 450.00, 190.00, 2),
(6, 4, 10, 34, 'Puff Sleeve Peplum Top', 'XL', 'Milk White', 'LV-0110-XL', 'assets/img/products/top-peplum-milk.svg', 990.00, 480.00, 2),
(7, 4, 16, 49, 'Classic Leather Tote Bag', '', 'Cherry', 'LV-0116-FS', 'assets/img/products/bag-tote-cherry.svg', 1990.00, 1300.00, 1);

-- --------------------------------------------------------

--
-- Table structure for table `products`
--

DROP TABLE IF EXISTS `products`;
CREATE TABLE `products` (
  `id` int(10) UNSIGNED NOT NULL,
  `category_id` int(11) DEFAULT NULL,
  `name` varchar(200) NOT NULL,
  `slug` varchar(220) NOT NULL,
  `sku` varchar(80) DEFAULT NULL,
  `short_desc` varchar(255) DEFAULT NULL,
  `description` text DEFAULT NULL,
  `price` decimal(10,2) NOT NULL DEFAULT 0.00,
  `sale_price` decimal(10,2) DEFAULT 0.00,
  `cost_price` decimal(10,2) DEFAULT 0.00,
  `image` varchar(255) DEFAULT NULL,
  `low_stock` int(11) DEFAULT 5,
  `is_featured` tinyint(4) DEFAULT 0,
  `is_active` tinyint(4) DEFAULT 1,
  `created_at` datetime DEFAULT NULL,
  `updated_at` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `products`
--

INSERT INTO `products` (`id`, `category_id`, `name`, `slug`, `sku`, `short_desc`, `description`, `price`, `sale_price`, `cost_price`, `image`, `low_stock`, `is_featured`, `is_active`, `created_at`, `updated_at`) VALUES
(1, 1, 'Floral Embroidered Cotton Kurti', 'floral-embroidered-cotton-kurti', 'LV-0101', 'A graceful kurti piece from our new season collection.', 'Made with breathable, skin-friendly fabric and finished with delicate detailing, this piece is designed to keep you comfortable from morning to evening.\n\n• Premium quality fabric\n• Colour-fast, easy care\n• Fits true to size — see the size guide\n• Hand-checked before dispatch', 1450.00, 1250.00, 780.00, 'assets/img/products/kurti-floral-lavender.svg', 5, 0, 0, '2026-10-02 17:37:23', '2026-11-16 17:37:23'),
(2, 1, 'Mint Garden Cotton Kurti', 'mint-garden-cotton-kurti', 'LV-0102', 'A graceful kurti piece from our new season collection.', 'Made with breathable, skin-friendly fabric and finished with delicate detailing, this piece is designed to keep you comfortable from morning to evening.\n\n• Premium quality fabric\n• Colour-fast, easy care\n• Fits true to size — see the size guide\n• Hand-checked before dispatch', 1450.00, 0.00, 780.00, 'assets/img/products/kurti-floral-mint.svg', 5, 0, 0, '2026-10-04 17:37:23', '2026-11-16 17:37:23'),
(3, 1, 'Block Print Straight Kurti', 'block-print-straight-kurti', 'LV-0103', 'A graceful kurti piece from our new season collection.', 'Made with breathable, skin-friendly fabric and finished with delicate detailing, this piece is designed to keep you comfortable from morning to evening.\n\n• Premium quality fabric\n• Colour-fast, easy care\n• Fits true to size — see the size guide\n• Hand-checked before dispatch', 1150.00, 0.00, 600.00, 'assets/img/products/kurti-blockprint-cherry.svg', 5, 0, 0, '2026-10-06 17:37:23', '2026-11-16 17:37:23'),
(4, 2, 'Jamdani Silk Saree', 'jamdani-silk-saree', 'LV-0104', 'A graceful saree piece from our new season collection.', 'Made with breathable, skin-friendly fabric and finished with delicate detailing, this piece is designed to keep you comfortable from morning to evening.\n\n• Premium quality fabric\n• Colour-fast, easy care\n• Fits true to size — see the size guide\n• Hand-checked before dispatch', 4800.00, 4200.00, 2900.00, 'assets/img/products/saree-jamdani-green.svg', 5, 0, 0, '2026-10-08 17:37:23', '2026-11-16 17:37:23'),
(5, 2, 'Soft Georgette Saree', 'soft-georgette-saree', 'LV-0105', 'A graceful saree piece from our new season collection.', 'Made with breathable, skin-friendly fabric and finished with delicate detailing, this piece is designed to keep you comfortable from morning to evening.\n\n• Premium quality fabric\n• Colour-fast, easy care\n• Fits true to size — see the size guide\n• Hand-checked before dispatch', 3200.00, 0.00, 1800.00, 'assets/img/products/saree-georgette-blush.svg', 5, 0, 0, '2026-10-10 17:37:23', '2026-11-16 17:37:23'),
(6, 3, 'Embroidered Lawn Three Piece', 'embroidered-lawn-three-piece', 'LV-0106', 'A graceful three piece piece from our new season collection.', 'Made with breathable, skin-friendly fabric and finished with delicate detailing, this piece is designed to keep you comfortable from morning to evening.\n\n• Premium quality fabric\n• Colour-fast, easy care\n• Fits true to size — see the size guide\n• Hand-checked before dispatch', 2650.00, 2350.00, 1500.00, 'assets/img/products/threepiece-lawn-mint.svg', 5, 0, 0, '2026-10-12 17:37:23', '2026-11-16 17:37:23'),
(7, 3, 'Pastel Cotton Three Piece', 'pastel-cotton-three-piece', 'LV-0107', 'A graceful three piece piece from our new season collection.', 'Made with breathable, skin-friendly fabric and finished with delicate detailing, this piece is designed to keep you comfortable from morning to evening.\n\n• Premium quality fabric\n• Colour-fast, easy care\n• Fits true to size — see the size guide\n• Hand-checked before dispatch', 2200.00, 0.00, 1250.00, 'assets/img/products/threepiece-pastel-lavender.svg', 5, 0, 0, '2026-10-14 17:37:23', '2026-11-16 17:37:23'),
(8, 4, 'Princess Flare Party Gown', 'princess-flare-party-gown', 'LV-0108', 'A graceful gowns piece from our new season collection.', 'Made with breathable, skin-friendly fabric and finished with delicate detailing, this piece is designed to keep you comfortable from morning to evening.\n\n• Premium quality fabric\n• Colour-fast, easy care\n• Fits true to size — see the size guide\n• Hand-checked before dispatch', 3900.00, 0.00, 2200.00, 'assets/img/products/gown-princess-cherry.svg', 5, 0, 0, '2026-10-16 17:37:23', '2026-11-16 17:37:23'),
(9, 4, 'Lavender Tiered Maxi Gown', 'lavender-tiered-maxi-gown', 'LV-0109', 'A graceful gowns piece from our new season collection.', 'Made with breathable, skin-friendly fabric and finished with delicate detailing, this piece is designed to keep you comfortable from morning to evening.\n\n• Premium quality fabric\n• Colour-fast, easy care\n• Fits true to size — see the size guide\n• Hand-checked before dispatch', 3500.00, 2990.00, 1900.00, 'assets/img/products/gown-tiered-lavender.svg', 5, 0, 0, '2026-10-18 17:37:23', '2026-11-16 17:37:23'),
(10, 5, 'Puff Sleeve Peplum Top', 'puff-sleeve-peplum-top', 'LV-0110', 'A graceful tops piece from our new season collection.', 'Made with breathable, skin-friendly fabric and finished with delicate detailing, this piece is designed to keep you comfortable from morning to evening.\n\n• Premium quality fabric\n• Colour-fast, easy care\n• Fits true to size — see the size guide\n• Hand-checked before dispatch', 990.00, 0.00, 480.00, 'assets/img/products/top-peplum-milk.svg', 5, 0, 0, '2026-10-20 17:37:23', '2026-11-16 17:37:23'),
(11, 5, 'Ruffle Georgette Top', 'ruffle-georgette-top', 'LV-0111', 'A graceful tops piece from our new season collection.', 'Made with breathable, skin-friendly fabric and finished with delicate detailing, this piece is designed to keep you comfortable from morning to evening.\n\n• Premium quality fabric\n• Colour-fast, easy care\n• Fits true to size — see the size guide\n• Hand-checked before dispatch', 1090.00, 890.00, 520.00, 'assets/img/products/top-ruffle-green.svg', 5, 0, 0, '2026-10-22 17:37:23', '2026-11-16 17:37:23'),
(12, 6, 'Premium Chiffon Hijab', 'premium-chiffon-hijab', 'LV-0112', 'A graceful hijab & scarf piece from our new season collection.', 'Made with breathable, skin-friendly fabric and finished with delicate detailing, this piece is designed to keep you comfortable from morning to evening.\n\n• Premium quality fabric\n• Colour-fast, easy care\n• Fits true to size — see the size guide\n• Hand-checked before dispatch', 450.00, 0.00, 190.00, 'assets/img/products/hijab-chiffon-rose.svg', 5, 0, 0, '2026-10-24 17:37:23', '2026-11-16 17:37:23'),
(13, 6, 'Printed Viscose Scarf', 'printed-viscose-scarf', 'LV-0113', 'A graceful hijab & scarf piece from our new season collection.', 'Made with breathable, skin-friendly fabric and finished with delicate detailing, this piece is designed to keep you comfortable from morning to evening.\n\n• Premium quality fabric\n• Colour-fast, easy care\n• Fits true to size — see the size guide\n• Hand-checked before dispatch', 550.00, 0.00, 230.00, 'assets/img/products/scarf-printed-purple.svg', 5, 0, 0, '2026-10-26 17:37:23', '2026-11-16 17:37:23'),
(14, 7, 'Wide Leg Palazzo', 'wide-leg-palazzo', 'LV-0114', 'A graceful bottoms piece from our new season collection.', 'Made with breathable, skin-friendly fabric and finished with delicate detailing, this piece is designed to keep you comfortable from morning to evening.\n\n• Premium quality fabric\n• Colour-fast, easy care\n• Fits true to size — see the size guide\n• Hand-checked before dispatch', 850.00, 0.00, 400.00, 'assets/img/products/palazzo-wide-green.svg', 5, 0, 0, '2026-10-28 17:37:23', '2026-11-16 17:37:23'),
(15, 7, 'Checked Cotton Palazzo', 'checked-cotton-palazzo', 'LV-0115', 'A graceful bottoms piece from our new season collection.', 'Made with breathable, skin-friendly fabric and finished with delicate detailing, this piece is designed to keep you comfortable from morning to evening.\n\n• Premium quality fabric\n• Colour-fast, easy care\n• Fits true to size — see the size guide\n• Hand-checked before dispatch', 890.00, 750.00, 410.00, 'assets/img/products/palazzo-check-cherry.svg', 5, 0, 0, '2026-10-30 17:37:23', '2026-11-16 17:37:23'),
(16, 8, 'Classic Leather Tote Bag', 'classic-leather-tote-bag', 'LV-0116', 'A graceful bags piece from our new season collection.', 'Made with breathable, skin-friendly fabric and finished with delicate detailing, this piece is designed to keep you comfortable from morning to evening.\n\n• Premium quality fabric\n• Colour-fast, easy care\n• Fits true to size — see the size guide\n• Hand-checked before dispatch', 2450.00, 1990.00, 1300.00, 'assets/img/products/bag-tote-cherry.svg', 5, 0, 0, '2026-11-01 17:37:23', '2026-11-16 17:37:23'),
(17, 8, 'Mini Crossbody Bag', 'mini-crossbody-bag', 'LV-0117', 'A graceful bags piece from our new season collection.', 'Made with breathable, skin-friendly fabric and finished with delicate detailing, this piece is designed to keep you comfortable from morning to evening.\n\n• Premium quality fabric\n• Colour-fast, easy care\n• Fits true to size — see the size guide\n• Hand-checked before dispatch', 1650.00, 0.00, 900.00, 'assets/img/products/bag-crossbody-milk.svg', 5, 0, 0, '2026-11-03 17:37:23', '2026-11-16 17:37:23'),
(18, 9, 'Kundan Jhumka Earrings', 'kundan-jhumka-earrings', 'LV-0118', 'A graceful jewelry piece from our new season collection.', 'Made with breathable, skin-friendly fabric and finished with delicate detailing, this piece is designed to keep you comfortable from morning to evening.\n\n• Premium quality fabric\n• Colour-fast, easy care\n• Fits true to size — see the size guide\n• Hand-checked before dispatch', 650.00, 0.00, 250.00, 'assets/img/products/jewelry-jhumka-gold.svg', 5, 0, 0, '2026-11-05 17:37:23', '2026-11-16 17:37:23'),
(19, 9, 'Pearl Drop Jhumka', 'pearl-drop-jhumka', 'LV-0119', 'A graceful jewelry piece from our new season collection.', 'Made with breathable, skin-friendly fabric and finished with delicate detailing, this piece is designed to keep you comfortable from morning to evening.\n\n• Premium quality fabric\n• Colour-fast, easy care\n• Fits true to size — see the size guide\n• Hand-checked before dispatch', 550.00, 490.00, 220.00, 'assets/img/products/jewelry-pearl-silver.svg', 5, 0, 0, '2026-11-07 17:37:23', '2026-11-16 17:59:21'),
(20, 13, 'Avocado Print Pyjama Set', 'avocado-print-pyjama-set', 'PL-0201', 'Soft, breathable night suit with a collared shirt and comfortable elastic-waist pyjama — made for lazy mornings and cosy nights.', 'Soft, breathable night suit with a collared shirt and comfortable elastic-waist pyjama — made for lazy mornings and cosy nights.\n\n• Smooth, skin-friendly fabric\n• Button-down shirt with contrast piping\n• Elastic waist pyjama\n• Easy to wash, colour-fast', 2200.00, 0.00, 0.00, 'assets/img/catalog/pl-5fc414a3.jpg', 2, 1, 1, '2026-11-18 19:16:24', '2026-11-18 19:16:24'),
(21, 13, 'Blue Bouquet Cream Pyjama Set', 'blue-bouquet-cream-pyjama-set', 'PL-0202', 'Soft, breathable night suit with a collared shirt and comfortable elastic-waist pyjama — made for lazy mornings and cosy nights.', 'Soft, breathable night suit with a collared shirt and comfortable elastic-waist pyjama — made for lazy mornings and cosy nights.\n\n• Smooth, skin-friendly fabric\n• Button-down shirt with contrast piping\n• Elastic waist pyjama\n• Easy to wash, colour-fast', 2200.00, 0.00, 0.00, 'assets/img/catalog/pl-ffd57e7c.jpg', 2, 0, 1, '2026-11-18 19:15:24', '2026-11-18 19:16:24'),
(22, 13, 'Peach Dotted Pyjama Set', 'peach-dotted-pyjama-set', 'PL-0203', 'Soft, breathable night suit with a collared shirt and comfortable elastic-waist pyjama — made for lazy mornings and cosy nights.', 'Soft, breathable night suit with a collared shirt and comfortable elastic-waist pyjama — made for lazy mornings and cosy nights.\n\n• Smooth, skin-friendly fabric\n• Button-down shirt with contrast piping\n• Elastic waist pyjama\n• Easy to wash, colour-fast', 2200.00, 0.00, 0.00, 'assets/img/catalog/pl-a58b228c.jpg', 2, 0, 1, '2026-11-18 19:14:24', '2026-11-18 19:16:24'),
(23, 13, 'Sky Daisy Teddy Pyjama Set', 'sky-daisy-teddy-pyjama-set', 'PL-0204', 'Soft, breathable night suit with a collared shirt and comfortable elastic-waist pyjama — made for lazy mornings and cosy nights.', 'Soft, breathable night suit with a collared shirt and comfortable elastic-waist pyjama — made for lazy mornings and cosy nights.\n\n• Smooth, skin-friendly fabric\n• Button-down shirt with contrast piping\n• Elastic waist pyjama\n• Easy to wash, colour-fast', 2200.00, 0.00, 0.00, 'assets/img/catalog/pl-bac814bf.jpg', 2, 0, 1, '2026-11-18 19:13:24', '2026-11-18 19:16:24'),
(24, 13, 'Pink Garden Floral Pyjama Set', 'pink-garden-floral-pyjama-set', 'PL-0205', 'Soft, breathable night suit with a collared shirt and comfortable elastic-waist pyjama — made for lazy mornings and cosy nights.', 'Soft, breathable night suit with a collared shirt and comfortable elastic-waist pyjama — made for lazy mornings and cosy nights.\n\n• Smooth, skin-friendly fabric\n• Button-down shirt with contrast piping\n• Elastic waist pyjama\n• Easy to wash, colour-fast', 2400.00, 0.00, 0.00, 'assets/img/catalog/pl-35e83c84.jpg', 2, 1, 1, '2026-11-18 19:12:24', '2026-11-18 19:16:24'),
(25, 13, 'Tulip Bloom Pyjama Set', 'tulip-bloom-pyjama-set', 'PL-0206', 'Soft, breathable night suit with a collared shirt and comfortable elastic-waist pyjama — made for lazy mornings and cosy nights.', 'Soft, breathable night suit with a collared shirt and comfortable elastic-waist pyjama — made for lazy mornings and cosy nights.\n\n• Smooth, skin-friendly fabric\n• Button-down shirt with contrast piping\n• Elastic waist pyjama\n• Easy to wash, colour-fast', 2200.00, 0.00, 0.00, 'assets/img/catalog/pl-af5c3d9d.jpg', 2, 1, 1, '2026-11-18 19:11:24', '2026-11-18 19:16:24'),
(26, 13, 'Cute Cartoon Print Pyjama Set', 'cute-cartoon-print-pyjama-set', 'PL-0207', 'Soft, breathable night suit with a collared shirt and comfortable elastic-waist pyjama — made for lazy mornings and cosy nights.', 'Soft, breathable night suit with a collared shirt and comfortable elastic-waist pyjama — made for lazy mornings and cosy nights.\n\n• Smooth, skin-friendly fabric\n• Button-down shirt with contrast piping\n• Elastic waist pyjama\n• Easy to wash, colour-fast', 2200.00, 0.00, 0.00, 'assets/img/catalog/pl-22cd4201.jpg', 2, 0, 1, '2026-11-18 19:10:24', '2026-11-18 19:16:24'),
(27, 13, 'Sky Blue Pizza & Flowers Pyjama Set', 'sky-blue-pizza-flowers-pyjama-set', 'PL-0208', 'Soft, breathable night suit with a collared shirt and comfortable elastic-waist pyjama — made for lazy mornings and cosy nights.', 'Soft, breathable night suit with a collared shirt and comfortable elastic-waist pyjama — made for lazy mornings and cosy nights.\n\n• Smooth, skin-friendly fabric\n• Button-down shirt with contrast piping\n• Elastic waist pyjama\n• Easy to wash, colour-fast', 2200.00, 0.00, 0.00, 'assets/img/catalog/pl-077ec7a4.jpg', 2, 0, 1, '2026-11-18 19:09:24', '2026-11-18 19:16:24'),
(28, 13, 'Cherry Kitty Patchwork Pyjama Set', 'cherry-kitty-patchwork-pyjama-set', 'PL-0209', 'Soft, breathable night suit with a collared shirt and comfortable elastic-waist pyjama — made for lazy mornings and cosy nights.', 'Soft, breathable night suit with a collared shirt and comfortable elastic-waist pyjama — made for lazy mornings and cosy nights.\n\n• Smooth, skin-friendly fabric\n• Button-down shirt with contrast piping\n• Elastic waist pyjama\n• Easy to wash, colour-fast', 2200.00, 0.00, 0.00, 'assets/img/catalog/pl-6fd6d88f.jpg', 2, 1, 1, '2026-11-18 19:08:24', '2026-11-18 19:16:24'),
(29, 13, 'Blue Toile Print Pyjama Set', 'blue-toile-print-pyjama-set', 'PL-0210', 'Soft, breathable night suit with a collared shirt and comfortable elastic-waist pyjama — made for lazy mornings and cosy nights.', 'Soft, breathable night suit with a collared shirt and comfortable elastic-waist pyjama — made for lazy mornings and cosy nights.\n\n• Smooth, skin-friendly fabric\n• Button-down shirt with contrast piping\n• Elastic waist pyjama\n• Easy to wash, colour-fast', 2200.00, 0.00, 0.00, 'assets/img/catalog/pl-7b78cad4.jpg', 2, 0, 1, '2026-11-18 19:07:24', '2026-11-18 19:16:24'),
(30, 13, 'Sage Blossom Pyjama Set', 'sage-blossom-pyjama-set', 'PL-0211', 'Soft, breathable night suit with a collared shirt and comfortable elastic-waist pyjama — made for lazy mornings and cosy nights.', 'Soft, breathable night suit with a collared shirt and comfortable elastic-waist pyjama — made for lazy mornings and cosy nights.\n\n• Smooth, skin-friendly fabric\n• Button-down shirt with contrast piping\n• Elastic waist pyjama\n• Easy to wash, colour-fast', 2200.00, 0.00, 0.00, 'assets/img/catalog/pl-73bb8ac9.jpg', 2, 0, 1, '2026-11-18 19:06:24', '2026-11-18 19:16:24'),
(31, 13, 'Lilac Iris Pyjama Set', 'lilac-iris-pyjama-set', 'PL-0212', 'Soft, breathable night suit with a collared shirt and comfortable elastic-waist pyjama — made for lazy mornings and cosy nights.', 'Soft, breathable night suit with a collared shirt and comfortable elastic-waist pyjama — made for lazy mornings and cosy nights.\n\n• Smooth, skin-friendly fabric\n• Button-down shirt with contrast piping\n• Elastic waist pyjama\n• Easy to wash, colour-fast', 2200.00, 0.00, 0.00, 'assets/img/catalog/pl-ae7610ed.jpg', 2, 0, 1, '2026-11-18 19:05:24', '2026-11-18 19:16:24'),
(32, 13, 'Lavender Bunny Garden Pyjama Set', 'lavender-bunny-garden-pyjama-set', 'PL-0213', 'Soft, breathable night suit with a collared shirt and comfortable elastic-waist pyjama — made for lazy mornings and cosy nights.', 'Soft, breathable night suit with a collared shirt and comfortable elastic-waist pyjama — made for lazy mornings and cosy nights.\n\n• Smooth, skin-friendly fabric\n• Button-down shirt with contrast piping\n• Elastic waist pyjama\n• Easy to wash, colour-fast', 2200.00, 0.00, 0.00, 'assets/img/catalog/pl-64a6d4e4.jpg', 2, 1, 1, '2026-11-18 19:04:24', '2026-11-18 19:16:24'),
(33, 13, 'Tropical Pineapple Pyjama Set', 'tropical-pineapple-pyjama-set', 'PL-0214', 'Soft, breathable night suit with a collared shirt and comfortable elastic-waist pyjama — made for lazy mornings and cosy nights.', 'Soft, breathable night suit with a collared shirt and comfortable elastic-waist pyjama — made for lazy mornings and cosy nights.\n\n• Smooth, skin-friendly fabric\n• Button-down shirt with contrast piping\n• Elastic waist pyjama\n• Easy to wash, colour-fast', 2200.00, 0.00, 0.00, 'assets/img/catalog/pl-9cc73823.jpg', 2, 0, 1, '2026-11-18 19:03:24', '2026-11-18 19:16:24'),
(34, 13, 'Black Tulip Satin Pyjama Set', 'black-tulip-satin-pyjama-set', 'PL-0215', 'Soft, breathable night suit with a collared shirt and comfortable elastic-waist pyjama — made for lazy mornings and cosy nights.', 'Soft, breathable night suit with a collared shirt and comfortable elastic-waist pyjama — made for lazy mornings and cosy nights.\n\n• Smooth, skin-friendly fabric\n• Button-down shirt with contrast piping\n• Elastic waist pyjama\n• Easy to wash, colour-fast', 2400.00, 0.00, 0.00, 'assets/img/catalog/pl-50ae4225.jpg', 2, 1, 1, '2026-11-18 19:02:24', '2026-11-18 19:16:24'),
(35, 13, 'Abstract Art Satin Pyjama Set', 'abstract-art-satin-pyjama-set', 'PL-0216', 'Soft, breathable night suit with a collared shirt and comfortable elastic-waist pyjama — made for lazy mornings and cosy nights.', 'Soft, breathable night suit with a collared shirt and comfortable elastic-waist pyjama — made for lazy mornings and cosy nights.\n\n• Smooth, skin-friendly fabric\n• Button-down shirt with contrast piping\n• Elastic waist pyjama\n• Easy to wash, colour-fast', 2500.00, 0.00, 0.00, 'assets/img/catalog/pl-a6b26a78.jpg', 2, 1, 1, '2026-11-18 19:01:24', '2026-11-18 19:16:24'),
(36, 13, 'Panda Bamboo Pyjama Set', 'panda-bamboo-pyjama-set', 'PL-0217', 'Soft, breathable night suit with a collared shirt and comfortable elastic-waist pyjama — made for lazy mornings and cosy nights.', 'Soft, breathable night suit with a collared shirt and comfortable elastic-waist pyjama — made for lazy mornings and cosy nights.\n\n• Smooth, skin-friendly fabric\n• Button-down shirt with contrast piping\n• Elastic waist pyjama\n• Easy to wash, colour-fast', 2200.00, 0.00, 0.00, 'assets/img/catalog/pl-4f986db3.jpg', 2, 0, 1, '2026-11-18 19:00:24', '2026-11-18 19:16:24'),
(37, 13, 'Painted Flowers Pyjama Set', 'painted-flowers-pyjama-set', 'PL-0218', 'Soft, breathable night suit with a collared shirt and comfortable elastic-waist pyjama — made for lazy mornings and cosy nights.', 'Soft, breathable night suit with a collared shirt and comfortable elastic-waist pyjama — made for lazy mornings and cosy nights.\n\n• Smooth, skin-friendly fabric\n• Button-down shirt with contrast piping\n• Elastic waist pyjama\n• Easy to wash, colour-fast', 2200.00, 0.00, 0.00, 'assets/img/catalog/pl-29742c1b.jpg', 2, 0, 1, '2026-11-18 18:59:24', '2026-11-18 19:16:24'),
(38, 13, 'Watercolour Poppy Pyjama Set', 'watercolour-poppy-pyjama-set', 'PL-0219', 'Soft, breathable night suit with a collared shirt and comfortable elastic-waist pyjama — made for lazy mornings and cosy nights.', 'Soft, breathable night suit with a collared shirt and comfortable elastic-waist pyjama — made for lazy mornings and cosy nights.\n\n• Smooth, skin-friendly fabric\n• Button-down shirt with contrast piping\n• Elastic waist pyjama\n• Easy to wash, colour-fast', 2200.00, 0.00, 0.00, 'assets/img/catalog/pl-e94b0011.jpg', 2, 0, 1, '2026-11-18 18:58:24', '2026-11-18 19:16:24'),
(39, 13, 'Lace Trim Satin Long Nightie', 'lace-trim-satin-long-nightie', 'PL-0220', 'Elegant nightwear for special evenings — soft fabric, pretty lace and a beautiful drape.', 'Elegant nightwear for special evenings — soft fabric, pretty lace and a beautiful drape.\n\n• Delicate lace detailing\n• Lightweight, smooth fabric\n• Free size, relaxed fit\n• Hand wash recommended', 2800.00, 0.00, 0.00, 'assets/img/catalog/pl-ec0ce0e2.jpg', 2, 1, 1, '2026-11-18 18:57:24', '2026-11-18 19:16:24'),
(40, 13, 'Black Satin Lace Slip', 'black-satin-lace-slip', 'PL-0221', 'Elegant nightwear for special evenings — soft fabric, pretty lace and a beautiful drape.', 'Elegant nightwear for special evenings — soft fabric, pretty lace and a beautiful drape.\n\n• Delicate lace detailing\n• Lightweight, smooth fabric\n• Free size, relaxed fit\n• Hand wash recommended', 1800.00, 0.00, 0.00, 'assets/img/catalog/pl-6021bdfb.jpg', 2, 0, 1, '2026-11-18 18:56:24', '2026-11-18 19:16:24'),
(41, 13, 'Emerald Ruffle Babydoll Nightie', 'emerald-ruffle-babydoll-nightie', 'PL-0222', 'Elegant nightwear for special evenings — soft fabric, pretty lace and a beautiful drape.', 'Elegant nightwear for special evenings — soft fabric, pretty lace and a beautiful drape.\n\n• Delicate lace detailing\n• Lightweight, smooth fabric\n• Free size, relaxed fit\n• Hand wash recommended', 2200.00, 0.00, 0.00, 'assets/img/catalog/pl-e3c1b7d5.jpg', 2, 1, 1, '2026-11-18 18:55:25', '2026-11-18 19:16:25'),
(42, 15, 'Red Lace Long Robe Set', 'red-lace-long-robe-set', 'PL-0223', 'Elegant nightwear for special evenings — soft fabric, pretty lace and a beautiful drape.', 'Elegant nightwear for special evenings — soft fabric, pretty lace and a beautiful drape.\n\n• Delicate lace detailing\n• Lightweight, smooth fabric\n• Free size, relaxed fit\n• Hand wash recommended', 3500.00, 0.00, 0.00, 'assets/img/catalog/pl-c9c6d158.jpg', 2, 1, 1, '2026-11-18 18:54:25', '2026-11-18 19:16:25'),
(43, 15, 'Maroon Satin 4-Piece Robe Set', 'maroon-satin-4-piece-robe-set', 'PL-0224', 'Elegant nightwear for special evenings — soft fabric, pretty lace and a beautiful drape.', 'Elegant nightwear for special evenings — soft fabric, pretty lace and a beautiful drape.\n\n• Delicate lace detailing\n• Lightweight, smooth fabric\n• Free size, relaxed fit\n• Hand wash recommended', 3800.00, 0.00, 0.00, 'assets/img/catalog/pl-4f9c4589.jpg', 2, 1, 1, '2026-11-18 18:53:25', '2026-11-18 19:16:25'),
(44, 15, 'Red Lace Babydoll & Robe Set', 'red-lace-babydoll-robe-set', 'PL-0225', 'Elegant nightwear for special evenings — soft fabric, pretty lace and a beautiful drape.', 'Elegant nightwear for special evenings — soft fabric, pretty lace and a beautiful drape.\n\n• Delicate lace detailing\n• Lightweight, smooth fabric\n• Free size, relaxed fit\n• Hand wash recommended', 2800.00, 0.00, 0.00, 'assets/img/catalog/pl-227f34a9.jpg', 2, 1, 1, '2026-11-18 18:52:25', '2026-11-18 19:16:25'),
(45, 15, 'Rose Embroidered Mesh Nightie', 'rose-embroidered-mesh-nightie', 'PL-0226', 'Elegant nightwear for special evenings — soft fabric, pretty lace and a beautiful drape.', 'Elegant nightwear for special evenings — soft fabric, pretty lace and a beautiful drape.\n\n• Delicate lace detailing\n• Lightweight, smooth fabric\n• Free size, relaxed fit\n• Hand wash recommended', 2400.00, 0.00, 0.00, 'assets/img/catalog/pl-2a46a4dc.jpg', 2, 0, 1, '2026-11-18 18:51:25', '2026-11-18 19:16:25'),
(46, 15, 'Feather Cuff Robe & Lace Set', 'feather-cuff-robe-lace-set', 'PL-0227', 'Elegant nightwear for special evenings — soft fabric, pretty lace and a beautiful drape.', 'Elegant nightwear for special evenings — soft fabric, pretty lace and a beautiful drape.\n\n• Delicate lace detailing\n• Lightweight, smooth fabric\n• Free size, relaxed fit\n• Hand wash recommended', 3800.00, 0.00, 0.00, 'assets/img/catalog/pl-9ccfe306.jpg', 2, 0, 1, '2026-11-18 18:50:25', '2026-11-18 19:16:25'),
(47, 15, 'White Lace Corset Bustier', 'white-lace-corset-bustier', 'PL-0228', 'Elegant nightwear for special evenings — soft fabric, pretty lace and a beautiful drape.', 'Elegant nightwear for special evenings — soft fabric, pretty lace and a beautiful drape.\n\n• Delicate lace detailing\n• Lightweight, smooth fabric\n• Free size, relaxed fit\n• Hand wash recommended', 2200.00, 0.00, 0.00, 'assets/img/catalog/pl-c522c81f.jpg', 2, 1, 1, '2026-11-18 18:49:25', '2026-11-18 19:16:25'),
(48, 15, 'Black Lace Trim Bustier Top', 'black-lace-trim-bustier-top', 'PL-0229', 'Elegant nightwear for special evenings — soft fabric, pretty lace and a beautiful drape.', 'Elegant nightwear for special evenings — soft fabric, pretty lace and a beautiful drape.\n\n• Delicate lace detailing\n• Lightweight, smooth fabric\n• Free size, relaxed fit\n• Hand wash recommended', 1400.00, 0.00, 0.00, 'assets/img/catalog/pl-eb25861f.jpg', 2, 0, 1, '2026-11-18 18:48:25', '2026-11-18 19:16:25'),
(49, 15, 'Mesh Body Shaper Bodysuit', 'mesh-body-shaper-bodysuit', 'PL-0230', 'Shapewear that smooths and supports under any outfit while staying comfortable.', 'Shapewear that smooths and supports under any outfit while staying comfortable.\n\n• Firm yet breathable compression\n• Seamless look under clothes\n• High-waist tummy control\n• Hand wash recommended', 2200.00, 0.00, 0.00, 'assets/img/catalog/pl-cf770e38.jpg', 2, 0, 1, '2026-11-18 18:47:25', '2026-11-18 19:16:25'),
(50, 15, 'Black Mesh Body Shaper Bodysuit', 'black-mesh-body-shaper-bodysuit', 'PL-0231', 'Shapewear that smooths and supports under any outfit while staying comfortable.', 'Shapewear that smooths and supports under any outfit while staying comfortable.\n\n• Firm yet breathable compression\n• Seamless look under clothes\n• High-waist tummy control\n• Hand wash recommended', 2200.00, 0.00, 0.00, 'assets/img/catalog/pl-5f70c0cb.jpg', 2, 0, 1, '2026-11-18 18:46:25', '2026-11-18 19:16:25'),
(51, 15, 'Hook Front High Waist Shaper', 'hook-front-high-waist-shaper', 'PL-0232', 'Shapewear that smooths and supports under any outfit while staying comfortable.', 'Shapewear that smooths and supports under any outfit while staying comfortable.\n\n• Firm yet breathable compression\n• Seamless look under clothes\n• High-waist tummy control\n• Hand wash recommended', 1500.00, 0.00, 0.00, 'assets/img/catalog/pl-48934448.jpg', 2, 0, 1, '2026-11-18 18:45:25', '2026-11-18 19:16:25'),
(52, 15, 'High Waist Tummy Control Panty', 'high-waist-tummy-control-panty', 'PL-0233', 'Shapewear that smooths and supports under any outfit while staying comfortable.', 'Shapewear that smooths and supports under any outfit while staying comfortable.\n\n• Firm yet breathable compression\n• Seamless look under clothes\n• High-waist tummy control\n• Hand wash recommended', 1200.00, 0.00, 0.00, 'assets/img/catalog/pl-b4935105.jpg', 2, 0, 1, '2026-11-18 18:44:25', '2026-11-18 19:16:25'),
(53, 15, 'Satin High Waist Shaper Panty', 'satin-high-waist-shaper-panty', 'PL-0234', 'Shapewear that smooths and supports under any outfit while staying comfortable.', 'Shapewear that smooths and supports under any outfit while staying comfortable.\n\n• Firm yet breathable compression\n• Seamless look under clothes\n• High-waist tummy control\n• Hand wash recommended', 1400.00, 0.00, 0.00, 'assets/img/catalog/pl-e615aaec.jpg', 2, 0, 1, '2026-11-18 18:43:25', '2026-11-18 19:16:25'),
(54, 15, 'High Waist Thigh Shaper Shorts', 'high-waist-thigh-shaper-shorts', 'PL-0235', 'Shapewear that smooths and supports under any outfit while staying comfortable.', 'Shapewear that smooths and supports under any outfit while staying comfortable.\n\n• Firm yet breathable compression\n• Seamless look under clothes\n• High-waist tummy control\n• Hand wash recommended', 1700.00, 0.00, 0.00, 'assets/img/catalog/pl-22bac228.jpg', 2, 1, 1, '2026-11-18 18:42:25', '2026-11-18 19:16:25'),
(55, 11, 'Strapless Multiway Bra', 'strapless-multiway-bra', 'PL-0236', 'A comfortable, supportive bra designed to look pretty and feel even better all day.', 'A comfortable, supportive bra designed to look pretty and feel even better all day.\n\n• Soft padded cups for a smooth shape\n• Adjustable straps\n• Breathable, skin-friendly fabric\n• Hand wash recommended', 1100.00, 0.00, 0.00, 'assets/img/catalog/pl-a9a634ef.jpg', 2, 1, 1, '2026-11-18 18:41:25', '2026-11-18 19:16:25'),
(56, 11, 'Seamless Wireless Comfort Bra', 'seamless-wireless-comfort-bra', 'PL-0237', 'A comfortable, supportive bra designed to look pretty and feel even better all day.', 'A comfortable, supportive bra designed to look pretty and feel even better all day.\n\n• Soft padded cups for a smooth shape\n• Adjustable straps\n• Breathable, skin-friendly fabric\n• Hand wash recommended', 1200.00, 0.00, 0.00, 'assets/img/catalog/pl-aed0f0d2.jpg', 2, 1, 1, '2026-11-18 18:40:26', '2026-11-18 19:16:26'),
(57, 11, 'Ivory Lace Jewel Bra', 'ivory-lace-jewel-bra', 'PL-0238', 'A comfortable, supportive bra designed to look pretty and feel even better all day.', 'A comfortable, supportive bra designed to look pretty and feel even better all day.\n\n• Soft padded cups for a smooth shape\n• Adjustable straps\n• Breathable, skin-friendly fabric\n• Hand wash recommended', 1300.00, 0.00, 0.00, 'assets/img/catalog/pl-2d58f44a.jpg', 2, 0, 1, '2026-11-18 18:39:26', '2026-11-18 19:16:26'),
(58, 11, 'Mauve Lace Push-Up Bra', 'mauve-lace-push-up-bra', 'PL-0239', 'A comfortable, supportive bra designed to look pretty and feel even better all day.', 'A comfortable, supportive bra designed to look pretty and feel even better all day.\n\n• Soft padded cups for a smooth shape\n• Adjustable straps\n• Breathable, skin-friendly fabric\n• Hand wash recommended', 1400.00, 0.00, 0.00, 'assets/img/catalog/pl-643b6f8d.jpg', 2, 0, 1, '2026-11-18 18:38:26', '2026-11-18 19:16:26'),
(59, 11, 'Magenta Floral Lace Bra', 'magenta-floral-lace-bra', 'PL-0240', 'A comfortable, supportive bra designed to look pretty and feel even better all day.', 'A comfortable, supportive bra designed to look pretty and feel even better all day.\n\n• Soft padded cups for a smooth shape\n• Adjustable straps\n• Breathable, skin-friendly fabric\n• Hand wash recommended', 1500.00, 0.00, 0.00, 'assets/img/catalog/pl-71e26159.jpg', 2, 1, 1, '2026-11-18 18:37:26', '2026-11-18 19:16:26'),
(60, 11, 'Blush Lace Gold Charm Bra', 'blush-lace-gold-charm-bra', 'PL-0241', 'A comfortable, supportive bra designed to look pretty and feel even better all day.', 'A comfortable, supportive bra designed to look pretty and feel even better all day.\n\n• Soft padded cups for a smooth shape\n• Adjustable straps\n• Breathable, skin-friendly fabric\n• Hand wash recommended', 1400.00, 0.00, 0.00, 'assets/img/catalog/pl-044efd6f.jpg', 2, 0, 1, '2026-11-18 18:36:26', '2026-11-18 19:16:26'),
(61, 11, 'Lace Edge Everyday Bra', 'lace-edge-everyday-bra', 'PL-0242', 'A comfortable, supportive bra designed to look pretty and feel even better all day.', 'A comfortable, supportive bra designed to look pretty and feel even better all day.\n\n• Soft padded cups for a smooth shape\n• Adjustable straps\n• Breathable, skin-friendly fabric\n• Hand wash recommended', 950.00, 0.00, 0.00, 'assets/img/catalog/pl-232cf7c4.jpg', 2, 0, 1, '2026-11-18 18:35:26', '2026-11-18 19:16:26'),
(62, 11, 'Soft Pink Lace Comfort Bra', 'soft-pink-lace-comfort-bra', 'PL-0243', 'A comfortable, supportive bra designed to look pretty and feel even better all day.', 'A comfortable, supportive bra designed to look pretty and feel even better all day.\n\n• Soft padded cups for a smooth shape\n• Adjustable straps\n• Breathable, skin-friendly fabric\n• Hand wash recommended', 900.00, 0.00, 0.00, 'assets/img/catalog/pl-512bc6e2.jpg', 2, 0, 1, '2026-11-18 18:34:26', '2026-11-18 19:16:26'),
(63, 11, 'Smooth T-Shirt Bra', 'smooth-t-shirt-bra', 'PL-0244', 'A comfortable, supportive bra designed to look pretty and feel even better all day.', 'A comfortable, supportive bra designed to look pretty and feel even better all day.\n\n• Soft padded cups for a smooth shape\n• Adjustable straps\n• Breathable, skin-friendly fabric\n• Hand wash recommended', 1300.00, 0.00, 0.00, 'assets/img/catalog/pl-fdce5f89.jpg', 2, 0, 1, '2026-11-18 18:33:26', '2026-11-18 19:16:26'),
(64, 11, 'Peach Lace Bra & Panty Set', 'peach-lace-bra-panty-set', 'PL-0245', 'A matching lace bra & panty set that feels as lovely as it looks.', 'A matching lace bra & panty set that feels as lovely as it looks.\n\n• Delicate floral lace\n• Padded cups with adjustable straps\n• Matching full-coverage panty\n• Hand wash recommended', 1850.00, 0.00, 0.00, 'assets/img/catalog/pl-7983d251.jpg', 2, 1, 1, '2026-11-18 18:32:27', '2026-11-18 19:16:27'),
(65, 11, 'Chocolate Lace Bra & Panty Set', 'chocolate-lace-bra-panty-set', 'PL-0246', 'A matching lace bra & panty set that feels as lovely as it looks.', 'A matching lace bra & panty set that feels as lovely as it looks.\n\n• Delicate floral lace\n• Padded cups with adjustable straps\n• Matching full-coverage panty\n• Hand wash recommended', 1850.00, 0.00, 0.00, 'assets/img/catalog/pl-b36d80e1.jpg', 2, 0, 1, '2026-11-18 18:31:27', '2026-11-18 19:16:27'),
(66, 11, 'Nude Lace Bra & Panty Set', 'nude-lace-bra-panty-set', 'PL-0247', 'A matching lace bra & panty set that feels as lovely as it looks.', 'A matching lace bra & panty set that feels as lovely as it looks.\n\n• Delicate floral lace\n• Padded cups with adjustable straps\n• Matching full-coverage panty\n• Hand wash recommended', 1850.00, 0.00, 0.00, 'assets/img/catalog/pl-4098f7f5.jpg', 2, 0, 1, '2026-11-18 18:30:27', '2026-11-18 19:16:27'),
(67, 11, 'Mint Embroidered Bra & Panty Set', 'mint-embroidered-bra-panty-set', 'PL-0248', 'A matching lace bra & panty set that feels as lovely as it looks.', 'A matching lace bra & panty set that feels as lovely as it looks.\n\n• Delicate floral lace\n• Padded cups with adjustable straps\n• Matching full-coverage panty\n• Hand wash recommended', 1850.00, 0.00, 0.00, 'assets/img/catalog/pl-a0e2e725.jpg', 2, 1, 1, '2026-11-18 18:29:27', '2026-11-18 19:16:27'),
(68, 12, 'Lace Edge Seamless Panty', 'lace-edge-seamless-panty', 'PL-0249', 'Everyday comfort panty with a soft, stretchy fit that stays in place.', 'Everyday comfort panty with a soft, stretchy fit that stays in place.\n\n• Breathable fabric\n• Comfortable waistband\n• Fits most sizes (see size guide)\n• Hand wash recommended', 450.00, 0.00, 0.00, 'assets/img/catalog/pl-4d7b5b82.jpg', 2, 0, 1, '2026-11-18 18:28:27', '2026-11-18 19:16:27'),
(69, 12, 'Smiley Cotton Panty', 'smiley-cotton-panty', 'PL-0250', 'Everyday comfort panty with a soft, stretchy fit that stays in place.', 'Everyday comfort panty with a soft, stretchy fit that stays in place.\n\n• Breathable fabric\n• Comfortable waistband\n• Fits most sizes (see size guide)\n• Hand wash recommended', 400.00, 0.00, 0.00, 'assets/img/catalog/pl-60986301.jpg', 2, 0, 1, '2026-11-18 18:27:27', '2026-11-18 19:16:27'),
(70, 12, 'Monogram Cotton Panty', 'monogram-cotton-panty', 'PL-0251', 'Everyday comfort panty with a soft, stretchy fit that stays in place.', 'Everyday comfort panty with a soft, stretchy fit that stays in place.\n\n• Breathable fabric\n• Comfortable waistband\n• Fits most sizes (see size guide)\n• Hand wash recommended', 450.00, 0.00, 0.00, 'assets/img/catalog/pl-48186bc8.jpg', 2, 0, 1, '2026-11-18 18:26:27', '2026-11-18 19:16:27'),
(71, 12, 'No-Show Seamless Bikini Panty', 'no-show-seamless-bikini-panty', 'PL-0252', 'Everyday comfort panty with a soft, stretchy fit that stays in place.', 'Everyday comfort panty with a soft, stretchy fit that stays in place.\n\n• Breathable fabric\n• Comfortable waistband\n• Fits most sizes (see size guide)\n• Hand wash recommended', 500.00, 0.00, 0.00, 'assets/img/catalog/pl-06cfe570.jpg', 2, 1, 1, '2026-11-18 18:25:27', '2026-11-18 19:16:27'),
(72, 16, 'Oversized Sports T-Shirt', 'oversized-sports-t-shirt', 'PL-0253', 'Relaxed, breathable sports T-shirt for workouts, travel or lounging at home.', 'Relaxed, breathable sports T-shirt for workouts, travel or lounging at home.\n\n• Quick-dry, stretchy fabric\n• Loose comfortable fit\n• Easy care', 1490.00, 0.00, 0.00, 'assets/img/catalog/pl-bc8e2753.jpg', 2, 1, 1, '2026-11-18 18:24:27', '2026-11-18 19:16:27'),
(73, 17, 'Colour Block Canvas Tote', 'colour-block-canvas-tote', 'PL-0254', 'A roomy, sturdy everyday tote — perfect for work, shopping or the beach.', 'A roomy, sturdy everyday tote — perfect for work, shopping or the beach.\n\n• Strong canvas fabric\n• Comfortable shoulder handles\n• Spacious inside', 2500.00, 0.00, 0.00, 'assets/img/catalog/pl-4fe9a5a1.jpg', 2, 1, 1, '2026-11-18 18:23:27', '2026-11-18 19:16:27'),
(74, 17, 'Printed Canvas Shopper Tote', 'printed-canvas-shopper-tote', 'PL-0255', 'A roomy, sturdy everyday tote — perfect for work, shopping or the beach.', 'A roomy, sturdy everyday tote — perfect for work, shopping or the beach.\n\n• Strong canvas fabric\n• Comfortable shoulder handles\n• Spacious inside', 2200.00, 0.00, 0.00, 'assets/img/catalog/pl-4fe9a5a1.jpg', 2, 0, 1, '2026-11-18 18:22:27', '2026-11-18 19:16:27'),
(75, 19, 'Ivory Crochet Beach Set', 'ivory-crochet-beach-set', 'PL-0256', 'A pretty piece from the Pretty Layers collection, chosen to keep you comfortable and stylish.', 'A pretty piece from the Pretty Layers collection, chosen to keep you comfortable and stylish.\n\n• Lightweight fabric\n• Easy to style\n• Hand wash recommended', 3200.00, 0.00, 0.00, 'assets/img/catalog/pl-3420b2ca.jpg', 2, 1, 1, '2026-11-18 18:21:27', '2026-11-18 19:16:27'),
(76, 19, 'Granny Square Crochet Top', 'granny-square-crochet-top', 'PL-0257', 'A pretty piece from the Pretty Layers collection, chosen to keep you comfortable and stylish.', 'A pretty piece from the Pretty Layers collection, chosen to keep you comfortable and stylish.\n\n• Lightweight fabric\n• Easy to style\n• Hand wash recommended', 2400.00, 0.00, 0.00, 'assets/img/catalog/pl-4ee35313.jpg', 2, 1, 1, '2026-11-18 18:20:27', '2026-11-18 19:16:27'),
(77, 19, 'Pink Boho Floral Two-Piece', 'pink-boho-floral-two-piece', 'PL-0258', 'A pretty piece from the Pretty Layers collection, chosen to keep you comfortable and stylish.', 'A pretty piece from the Pretty Layers collection, chosen to keep you comfortable and stylish.\n\n• Lightweight fabric\n• Easy to style\n• Hand wash recommended', 3200.00, 0.00, 0.00, 'assets/img/catalog/pl-287f9bb8.jpg', 2, 1, 1, '2026-11-18 18:19:27', '2026-11-18 19:16:27'),
(78, 19, 'Blue Tie-Dye Maxi Dress', 'blue-tie-dye-maxi-dress', 'PL-0259', 'A pretty piece from the Pretty Layers collection, chosen to keep you comfortable and stylish.', 'A pretty piece from the Pretty Layers collection, chosen to keep you comfortable and stylish.\n\n• Lightweight fabric\n• Easy to style\n• Hand wash recommended', 2500.00, 0.00, 0.00, 'assets/img/catalog/pl-15a5334a.jpg', 2, 0, 1, '2026-11-18 18:18:27', '2026-11-18 19:16:27'),
(79, 19, 'Brown Polka Cut-Out Dress', 'brown-polka-cut-out-dress', 'PL-0260', 'A pretty piece from the Pretty Layers collection, chosen to keep you comfortable and stylish.', 'A pretty piece from the Pretty Layers collection, chosen to keep you comfortable and stylish.\n\n• Lightweight fabric\n• Easy to style\n• Hand wash recommended', 2200.00, 0.00, 0.00, 'assets/img/catalog/pl-1461cf1f.jpg', 2, 1, 1, '2026-11-18 18:17:27', '2026-11-18 19:16:27'),
(80, 19, 'Black Polka Ruffle Peplum Top', 'black-polka-ruffle-peplum-top', 'PL-0261', 'A pretty piece from the Pretty Layers collection, chosen to keep you comfortable and stylish.', 'A pretty piece from the Pretty Layers collection, chosen to keep you comfortable and stylish.\n\n• Lightweight fabric\n• Easy to style\n• Hand wash recommended', 1650.00, 0.00, 0.00, 'assets/img/catalog/pl-d9879dcb.jpg', 2, 1, 1, '2026-11-18 18:16:27', '2026-11-18 19:16:27'),
(81, 19, 'Sheer Knit Beach Cover Top', 'sheer-knit-beach-cover-top', 'PL-0262', 'A pretty piece from the Pretty Layers collection, chosen to keep you comfortable and stylish.', 'A pretty piece from the Pretty Layers collection, chosen to keep you comfortable and stylish.\n\n• Lightweight fabric\n• Easy to style\n• Hand wash recommended', 1500.00, 0.00, 0.00, 'assets/img/catalog/pl-52a4fcf1.jpg', 2, 0, 1, '2026-11-18 18:15:27', '2026-11-18 19:16:27');

-- --------------------------------------------------------

--
-- Table structure for table `product_images`
--

DROP TABLE IF EXISTS `product_images`;
CREATE TABLE `product_images` (
  `id` int(10) UNSIGNED NOT NULL,
  `product_id` int(11) NOT NULL,
  `path` varchar(255) NOT NULL,
  `sort_order` int(11) DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `product_images`
--

INSERT INTO `product_images` (`id`, `product_id`, `path`, `sort_order`) VALUES
(1, 20, 'assets/img/catalog/pl-65c07e80.jpg', 1),
(2, 20, 'assets/img/catalog/pl-3112ae07.jpg', 2),
(3, 35, 'assets/img/catalog/pl-52fdb7ce.jpg', 1),
(4, 44, 'assets/img/catalog/pl-5fe6e295.jpg', 1),
(5, 44, 'assets/img/catalog/pl-c061a120.jpg', 2),
(6, 49, 'assets/img/catalog/pl-a9480f7b.jpg', 1),
(7, 49, 'assets/img/catalog/pl-43cba894.jpg', 2),
(8, 52, 'assets/img/catalog/pl-71554a2c.jpg', 1),
(9, 53, 'assets/img/catalog/pl-43cba894.jpg', 1),
(10, 54, 'assets/img/catalog/pl-63f18dbc.jpg', 1),
(11, 54, 'assets/img/catalog/pl-18818e97.jpg', 2),
(12, 68, 'assets/img/catalog/pl-74299618.jpg', 1);

-- --------------------------------------------------------

--
-- Table structure for table `settings`
--

DROP TABLE IF EXISTS `settings`;
CREATE TABLE `settings` (
  `skey` varchar(100) NOT NULL,
  `svalue` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `settings`
--

INSERT INTO `settings` (`skey`, `svalue`) VALUES
('address', 'Shop No. 218, 1st Floor, Khulshi Town Center, Chittagong'),
('announcement', 'Free delivery on orders over ৳3000  •  Cash on delivery all over Bangladesh'),
('bkash_number', '01700-000000'),
('color_accent', '#B01FB3'),
('color_bg', '#FCF6FB'),
('color_dark', '#4B1648'),
('color_primary', '#C27BD6'),
('color_text', '#2E1F2D'),
('currency_symbol', '৳'),
('db_version', '9'),
('email', 'hello@prettylayers.shop'),
('facebook', '#'),
('free_shipping_over', '3000'),
('hero_badge', 'New Season Collection'),
('hero_card1_sub', ''),
('hero_card1_title', ''),
('hero_card2_sub', ''),
('hero_card2_title', ''),
('hero_cards_show', '0'),
('hero_fit', 'contain'),
('hero_images', '[\"uploads\\/banners\\/20261117-4377ed70b95e.avif\",\"uploads\\/banners\\/20261117-f340271a4e9b.avif\",\"uploads\\/banners\\/20261117-95b52eff88f6.avif\",\"uploads\\/banners\\/20261117-4930e2de1ff1.webp\"]'),
('hero_interval', '2'),
('hero_subtitle', 'Discover soft night suits, pretty lingerie and everyday comfort — handpicked for every beautiful moment of your life.'),
('hero_title', 'Wrapped in Sweet Dreams'),
('instagram', '#'),
('loader_enabled', '1'),
('logo', 'assets/img/brand/pretty-layers-logo.png'),
('logo_icon', 'assets/img/brand/pretty-layers-icon.png'),
('low_stock_default', '5'),
('map_query', 'Khulshi Town Center, Chittagong'),
('nagad_number', '01800-000000'),
('phone', '01805-531038'),
('shipping_inside', '70'),
('shipping_outside', '130'),
('site_name', 'Pretty Layers'),
('tagline', 'Wrapped in sweet dreams'),
('theme_preset', 'pretty'),
('visit_address', 'Khulshi Town Center, Chittagong\n1st Floor, Shop No. 218'),
('visit_eyebrow', 'Come say hello'),
('visit_text', 'We welcome you to explore our premium collection of 100% imported ladies’ lingerie, nightwear, and undergarments.'),
('visit_title', 'Visit Our Outlet in Chittagong!');

-- --------------------------------------------------------

--
-- Table structure for table `stock_movements`
--

DROP TABLE IF EXISTS `stock_movements`;
CREATE TABLE `stock_movements` (
  `id` int(10) UNSIGNED NOT NULL,
  `variant_id` int(11) DEFAULT NULL,
  `product_id` int(11) DEFAULT NULL,
  `qty_change` int(11) DEFAULT NULL,
  `balance_after` int(11) DEFAULT NULL,
  `type` varchar(20) DEFAULT NULL,
  `note` varchar(255) DEFAULT NULL,
  `ref` varchar(60) DEFAULT NULL,
  `created_by` varchar(60) DEFAULT NULL,
  `created_at` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `stock_movements`
--

INSERT INTO `stock_movements` (`id`, `variant_id`, `product_id`, `qty_change`, `balance_after`, `type`, `note`, `ref`, `created_by`, `created_at`) VALUES
(1, 1, 1, 6, 6, 'initial', 'Opening stock', NULL, 'installer', '2026-11-16 17:37:23'),
(2, 2, 1, 12, 12, 'initial', 'Opening stock', NULL, 'installer', '2026-11-16 17:37:23'),
(3, 3, 1, 9, 9, 'initial', 'Opening stock', NULL, 'installer', '2026-11-16 17:37:23'),
(4, 4, 1, 3, 3, 'initial', 'Opening stock', NULL, 'installer', '2026-11-16 17:37:23'),
(5, 5, 2, 4, 4, 'initial', 'Opening stock', NULL, 'installer', '2026-11-16 17:37:23'),
(6, 6, 2, 8, 8, 'initial', 'Opening stock', NULL, 'installer', '2026-11-16 17:37:23'),
(7, 7, 2, 7, 7, 'initial', 'Opening stock', NULL, 'installer', '2026-11-16 17:37:23'),
(8, 8, 2, 2, 2, 'initial', 'Opening stock', NULL, 'installer', '2026-11-16 17:37:23'),
(9, 9, 3, 10, 10, 'initial', 'Opening stock', NULL, 'installer', '2026-11-16 17:37:23'),
(10, 10, 3, 14, 14, 'initial', 'Opening stock', NULL, 'installer', '2026-11-16 17:37:23'),
(11, 11, 3, 11, 11, 'initial', 'Opening stock', NULL, 'installer', '2026-11-16 17:37:23'),
(12, 12, 3, 6, 6, 'initial', 'Opening stock', NULL, 'installer', '2026-11-16 17:37:23'),
(13, 13, 4, 7, 7, 'initial', 'Opening stock', NULL, 'installer', '2026-11-16 17:37:23'),
(14, 14, 5, 3, 3, 'initial', 'Opening stock', NULL, 'installer', '2026-11-16 17:37:23'),
(15, 15, 6, 5, 5, 'initial', 'Opening stock', NULL, 'installer', '2026-11-16 17:37:23'),
(16, 16, 6, 9, 9, 'initial', 'Opening stock', NULL, 'installer', '2026-11-16 17:37:23'),
(17, 17, 6, 8, 8, 'initial', 'Opening stock', NULL, 'installer', '2026-11-16 17:37:23'),
(18, 18, 6, 4, 4, 'initial', 'Opening stock', NULL, 'installer', '2026-11-16 17:37:23'),
(19, 20, 7, 2, 2, 'initial', 'Opening stock', NULL, 'installer', '2026-11-16 17:37:23'),
(20, 21, 7, 1, 1, 'initial', 'Opening stock', NULL, 'installer', '2026-11-16 17:37:23'),
(21, 23, 8, 3, 3, 'initial', 'Opening stock', NULL, 'installer', '2026-11-16 17:37:23'),
(22, 24, 8, 6, 6, 'initial', 'Opening stock', NULL, 'installer', '2026-11-16 17:37:23'),
(23, 25, 8, 5, 5, 'initial', 'Opening stock', NULL, 'installer', '2026-11-16 17:37:23'),
(24, 26, 8, 2, 2, 'initial', 'Opening stock', NULL, 'installer', '2026-11-16 17:37:23'),
(25, 27, 9, 6, 6, 'initial', 'Opening stock', NULL, 'installer', '2026-11-16 17:37:23'),
(26, 28, 9, 8, 8, 'initial', 'Opening stock', NULL, 'installer', '2026-11-16 17:37:23'),
(27, 29, 9, 6, 6, 'initial', 'Opening stock', NULL, 'installer', '2026-11-16 17:37:23'),
(28, 30, 9, 3, 3, 'initial', 'Opening stock', NULL, 'installer', '2026-11-16 17:37:23'),
(29, 31, 10, 12, 12, 'initial', 'Opening stock', NULL, 'installer', '2026-11-16 17:37:23'),
(30, 32, 10, 15, 15, 'initial', 'Opening stock', NULL, 'installer', '2026-11-16 17:37:23'),
(31, 33, 10, 10, 10, 'initial', 'Opening stock', NULL, 'installer', '2026-11-16 17:37:23'),
(32, 34, 10, 8, 8, 'initial', 'Opening stock', NULL, 'installer', '2026-11-16 17:37:23'),
(33, 35, 11, 8, 8, 'initial', 'Opening stock', NULL, 'installer', '2026-11-16 17:37:23'),
(34, 36, 11, 10, 10, 'initial', 'Opening stock', NULL, 'installer', '2026-11-16 17:37:23'),
(35, 37, 11, 9, 9, 'initial', 'Opening stock', NULL, 'installer', '2026-11-16 17:37:23'),
(36, 38, 11, 5, 5, 'initial', 'Opening stock', NULL, 'installer', '2026-11-16 17:37:23'),
(37, 39, 12, 40, 40, 'initial', 'Opening stock', NULL, 'installer', '2026-11-16 17:37:23'),
(38, 40, 13, 25, 25, 'initial', 'Opening stock', NULL, 'installer', '2026-11-16 17:37:23'),
(39, 41, 14, 9, 9, 'initial', 'Opening stock', NULL, 'installer', '2026-11-16 17:37:23'),
(40, 42, 14, 11, 11, 'initial', 'Opening stock', NULL, 'installer', '2026-11-16 17:37:23'),
(41, 43, 14, 10, 10, 'initial', 'Opening stock', NULL, 'installer', '2026-11-16 17:37:23'),
(42, 44, 14, 6, 6, 'initial', 'Opening stock', NULL, 'installer', '2026-11-16 17:37:23'),
(43, 49, 16, 8, 8, 'initial', 'Opening stock', NULL, 'installer', '2026-11-16 17:37:23'),
(44, 50, 17, 4, 4, 'initial', 'Opening stock', NULL, 'installer', '2026-11-16 17:37:23'),
(45, 51, 18, 30, 30, 'initial', 'Opening stock', NULL, 'installer', '2026-11-16 17:37:23'),
(46, 52, 19, 18, 18, 'initial', 'Opening stock', NULL, 'installer', '2026-11-16 17:37:23'),
(47, 3, 1, -1, 8, 'order', 'Order placed', 'LV2611101001', 'customer', '2026-11-16 17:37:23'),
(48, 51, 18, -2, 28, 'order', 'Order placed', 'LV2611101001', 'customer', '2026-11-16 17:37:23'),
(49, 29, 9, -1, 5, 'order', 'Order placed', 'LV2611121002', 'customer', '2026-11-16 17:37:23'),
(50, 13, 4, -1, 6, 'order', 'Order placed', 'LV2611141003', 'customer', '2026-11-16 17:37:23'),
(51, 39, 12, -2, 38, 'order', 'Order placed', 'LV2611141003', 'customer', '2026-11-16 17:37:23'),
(52, 34, 10, -2, 6, 'order', 'Order placed', 'LV2611161004', 'customer', '2026-11-16 17:37:23'),
(53, 49, 16, -1, 7, 'order', 'Order placed', 'LV2611161004', 'customer', '2026-11-16 17:37:23'),
(54, 53, 20, 5, 5, 'initial', 'Opening stock (please check)', NULL, 'import', '2026-11-18 19:16:24'),
(55, 54, 20, 5, 5, 'initial', 'Opening stock (please check)', NULL, 'import', '2026-11-18 19:16:24'),
(56, 55, 20, 5, 5, 'initial', 'Opening stock (please check)', NULL, 'import', '2026-11-18 19:16:24'),
(57, 56, 20, 5, 5, 'initial', 'Opening stock (please check)', NULL, 'import', '2026-11-18 19:16:24'),
(58, 57, 21, 5, 5, 'initial', 'Opening stock (please check)', NULL, 'import', '2026-11-18 19:16:24'),
(59, 58, 21, 5, 5, 'initial', 'Opening stock (please check)', NULL, 'import', '2026-11-18 19:16:24'),
(60, 59, 21, 5, 5, 'initial', 'Opening stock (please check)', NULL, 'import', '2026-11-18 19:16:24'),
(61, 60, 21, 5, 5, 'initial', 'Opening stock (please check)', NULL, 'import', '2026-11-18 19:16:24'),
(62, 61, 22, 5, 5, 'initial', 'Opening stock (please check)', NULL, 'import', '2026-11-18 19:16:24'),
(63, 62, 22, 5, 5, 'initial', 'Opening stock (please check)', NULL, 'import', '2026-11-18 19:16:24'),
(64, 63, 22, 5, 5, 'initial', 'Opening stock (please check)', NULL, 'import', '2026-11-18 19:16:24'),
(65, 64, 22, 5, 5, 'initial', 'Opening stock (please check)', NULL, 'import', '2026-11-18 19:16:24'),
(66, 65, 23, 5, 5, 'initial', 'Opening stock (please check)', NULL, 'import', '2026-11-18 19:16:24'),
(67, 66, 23, 5, 5, 'initial', 'Opening stock (please check)', NULL, 'import', '2026-11-18 19:16:24'),
(68, 67, 23, 5, 5, 'initial', 'Opening stock (please check)', NULL, 'import', '2026-11-18 19:16:24'),
(69, 68, 23, 5, 5, 'initial', 'Opening stock (please check)', NULL, 'import', '2026-11-18 19:16:24'),
(70, 69, 24, 5, 5, 'initial', 'Opening stock (please check)', NULL, 'import', '2026-11-18 19:16:24'),
(71, 70, 24, 5, 5, 'initial', 'Opening stock (please check)', NULL, 'import', '2026-11-18 19:16:24'),
(72, 71, 24, 5, 5, 'initial', 'Opening stock (please check)', NULL, 'import', '2026-11-18 19:16:24'),
(73, 72, 24, 5, 5, 'initial', 'Opening stock (please check)', NULL, 'import', '2026-11-18 19:16:24'),
(74, 73, 25, 5, 5, 'initial', 'Opening stock (please check)', NULL, 'import', '2026-11-18 19:16:24'),
(75, 74, 25, 5, 5, 'initial', 'Opening stock (please check)', NULL, 'import', '2026-11-18 19:16:24'),
(76, 75, 25, 5, 5, 'initial', 'Opening stock (please check)', NULL, 'import', '2026-11-18 19:16:24'),
(77, 76, 25, 5, 5, 'initial', 'Opening stock (please check)', NULL, 'import', '2026-11-18 19:16:24'),
(78, 77, 26, 5, 5, 'initial', 'Opening stock (please check)', NULL, 'import', '2026-11-18 19:16:24'),
(79, 78, 26, 5, 5, 'initial', 'Opening stock (please check)', NULL, 'import', '2026-11-18 19:16:24'),
(80, 79, 26, 5, 5, 'initial', 'Opening stock (please check)', NULL, 'import', '2026-11-18 19:16:24'),
(81, 80, 26, 5, 5, 'initial', 'Opening stock (please check)', NULL, 'import', '2026-11-18 19:16:24'),
(82, 81, 27, 5, 5, 'initial', 'Opening stock (please check)', NULL, 'import', '2026-11-18 19:16:24'),
(83, 82, 27, 5, 5, 'initial', 'Opening stock (please check)', NULL, 'import', '2026-11-18 19:16:24'),
(84, 83, 27, 5, 5, 'initial', 'Opening stock (please check)', NULL, 'import', '2026-11-18 19:16:24'),
(85, 84, 27, 5, 5, 'initial', 'Opening stock (please check)', NULL, 'import', '2026-11-18 19:16:24'),
(86, 85, 28, 5, 5, 'initial', 'Opening stock (please check)', NULL, 'import', '2026-11-18 19:16:24'),
(87, 86, 28, 5, 5, 'initial', 'Opening stock (please check)', NULL, 'import', '2026-11-18 19:16:24'),
(88, 87, 28, 5, 5, 'initial', 'Opening stock (please check)', NULL, 'import', '2026-11-18 19:16:24'),
(89, 88, 28, 5, 5, 'initial', 'Opening stock (please check)', NULL, 'import', '2026-11-18 19:16:24'),
(90, 89, 29, 5, 5, 'initial', 'Opening stock (please check)', NULL, 'import', '2026-11-18 19:16:24'),
(91, 90, 29, 5, 5, 'initial', 'Opening stock (please check)', NULL, 'import', '2026-11-18 19:16:24'),
(92, 91, 29, 5, 5, 'initial', 'Opening stock (please check)', NULL, 'import', '2026-11-18 19:16:24'),
(93, 92, 29, 5, 5, 'initial', 'Opening stock (please check)', NULL, 'import', '2026-11-18 19:16:24'),
(94, 93, 30, 5, 5, 'initial', 'Opening stock (please check)', NULL, 'import', '2026-11-18 19:16:24'),
(95, 94, 30, 5, 5, 'initial', 'Opening stock (please check)', NULL, 'import', '2026-11-18 19:16:24'),
(96, 95, 30, 5, 5, 'initial', 'Opening stock (please check)', NULL, 'import', '2026-11-18 19:16:24'),
(97, 96, 30, 5, 5, 'initial', 'Opening stock (please check)', NULL, 'import', '2026-11-18 19:16:24'),
(98, 97, 31, 5, 5, 'initial', 'Opening stock (please check)', NULL, 'import', '2026-11-18 19:16:24'),
(99, 98, 31, 5, 5, 'initial', 'Opening stock (please check)', NULL, 'import', '2026-11-18 19:16:24'),
(100, 99, 31, 5, 5, 'initial', 'Opening stock (please check)', NULL, 'import', '2026-11-18 19:16:24'),
(101, 100, 31, 5, 5, 'initial', 'Opening stock (please check)', NULL, 'import', '2026-11-18 19:16:24'),
(102, 101, 32, 5, 5, 'initial', 'Opening stock (please check)', NULL, 'import', '2026-11-18 19:16:24'),
(103, 102, 32, 5, 5, 'initial', 'Opening stock (please check)', NULL, 'import', '2026-11-18 19:16:24'),
(104, 103, 32, 5, 5, 'initial', 'Opening stock (please check)', NULL, 'import', '2026-11-18 19:16:24'),
(105, 104, 32, 5, 5, 'initial', 'Opening stock (please check)', NULL, 'import', '2026-11-18 19:16:24'),
(106, 105, 33, 5, 5, 'initial', 'Opening stock (please check)', NULL, 'import', '2026-11-18 19:16:24'),
(107, 106, 33, 5, 5, 'initial', 'Opening stock (please check)', NULL, 'import', '2026-11-18 19:16:24'),
(108, 107, 33, 5, 5, 'initial', 'Opening stock (please check)', NULL, 'import', '2026-11-18 19:16:24'),
(109, 108, 33, 5, 5, 'initial', 'Opening stock (please check)', NULL, 'import', '2026-11-18 19:16:24'),
(110, 109, 34, 5, 5, 'initial', 'Opening stock (please check)', NULL, 'import', '2026-11-18 19:16:24'),
(111, 110, 34, 5, 5, 'initial', 'Opening stock (please check)', NULL, 'import', '2026-11-18 19:16:24'),
(112, 111, 34, 5, 5, 'initial', 'Opening stock (please check)', NULL, 'import', '2026-11-18 19:16:24'),
(113, 112, 34, 5, 5, 'initial', 'Opening stock (please check)', NULL, 'import', '2026-11-18 19:16:24'),
(114, 113, 35, 5, 5, 'initial', 'Opening stock (please check)', NULL, 'import', '2026-11-18 19:16:24'),
(115, 114, 35, 5, 5, 'initial', 'Opening stock (please check)', NULL, 'import', '2026-11-18 19:16:24'),
(116, 115, 35, 5, 5, 'initial', 'Opening stock (please check)', NULL, 'import', '2026-11-18 19:16:24'),
(117, 116, 35, 5, 5, 'initial', 'Opening stock (please check)', NULL, 'import', '2026-11-18 19:16:24'),
(118, 117, 36, 5, 5, 'initial', 'Opening stock (please check)', NULL, 'import', '2026-11-18 19:16:24'),
(119, 118, 36, 5, 5, 'initial', 'Opening stock (please check)', NULL, 'import', '2026-11-18 19:16:24'),
(120, 119, 36, 5, 5, 'initial', 'Opening stock (please check)', NULL, 'import', '2026-11-18 19:16:24'),
(121, 120, 36, 5, 5, 'initial', 'Opening stock (please check)', NULL, 'import', '2026-11-18 19:16:24'),
(122, 121, 37, 5, 5, 'initial', 'Opening stock (please check)', NULL, 'import', '2026-11-18 19:16:24'),
(123, 122, 37, 5, 5, 'initial', 'Opening stock (please check)', NULL, 'import', '2026-11-18 19:16:24'),
(124, 123, 37, 5, 5, 'initial', 'Opening stock (please check)', NULL, 'import', '2026-11-18 19:16:24'),
(125, 124, 37, 5, 5, 'initial', 'Opening stock (please check)', NULL, 'import', '2026-11-18 19:16:24'),
(126, 125, 38, 5, 5, 'initial', 'Opening stock (please check)', NULL, 'import', '2026-11-18 19:16:24'),
(127, 126, 38, 5, 5, 'initial', 'Opening stock (please check)', NULL, 'import', '2026-11-18 19:16:24'),
(128, 127, 38, 5, 5, 'initial', 'Opening stock (please check)', NULL, 'import', '2026-11-18 19:16:24'),
(129, 128, 38, 5, 5, 'initial', 'Opening stock (please check)', NULL, 'import', '2026-11-18 19:16:24'),
(130, 129, 39, 4, 4, 'initial', 'Opening stock (please check)', NULL, 'import', '2026-11-18 19:16:24'),
(131, 130, 39, 4, 4, 'initial', 'Opening stock (please check)', NULL, 'import', '2026-11-18 19:16:24'),
(132, 131, 40, 4, 4, 'initial', 'Opening stock (please check)', NULL, 'import', '2026-11-18 19:16:25'),
(133, 132, 40, 4, 4, 'initial', 'Opening stock (please check)', NULL, 'import', '2026-11-18 19:16:25'),
(134, 133, 41, 4, 4, 'initial', 'Opening stock (please check)', NULL, 'import', '2026-11-18 19:16:25'),
(135, 134, 42, 3, 3, 'initial', 'Opening stock (please check)', NULL, 'import', '2026-11-18 19:16:25'),
(136, 135, 43, 3, 3, 'initial', 'Opening stock (please check)', NULL, 'import', '2026-11-18 19:16:25'),
(137, 136, 44, 3, 3, 'initial', 'Opening stock (please check)', NULL, 'import', '2026-11-18 19:16:25'),
(138, 137, 45, 3, 3, 'initial', 'Opening stock (please check)', NULL, 'import', '2026-11-18 19:16:25'),
(139, 138, 46, 3, 3, 'initial', 'Opening stock (please check)', NULL, 'import', '2026-11-18 19:16:25'),
(140, 139, 47, 3, 3, 'initial', 'Opening stock (please check)', NULL, 'import', '2026-11-18 19:16:25'),
(141, 140, 47, 3, 3, 'initial', 'Opening stock (please check)', NULL, 'import', '2026-11-18 19:16:25'),
(142, 141, 47, 3, 3, 'initial', 'Opening stock (please check)', NULL, 'import', '2026-11-18 19:16:25'),
(143, 142, 48, 4, 4, 'initial', 'Opening stock (please check)', NULL, 'import', '2026-11-18 19:16:25'),
(144, 143, 48, 4, 4, 'initial', 'Opening stock (please check)', NULL, 'import', '2026-11-18 19:16:25'),
(145, 144, 48, 4, 4, 'initial', 'Opening stock (please check)', NULL, 'import', '2026-11-18 19:16:25'),
(146, 145, 49, 3, 3, 'initial', 'Opening stock (please check)', NULL, 'import', '2026-11-18 19:16:25'),
(147, 146, 49, 3, 3, 'initial', 'Opening stock (please check)', NULL, 'import', '2026-11-18 19:16:25'),
(148, 147, 49, 3, 3, 'initial', 'Opening stock (please check)', NULL, 'import', '2026-11-18 19:16:25'),
(149, 148, 49, 3, 3, 'initial', 'Opening stock (please check)', NULL, 'import', '2026-11-18 19:16:25'),
(150, 149, 49, 3, 3, 'initial', 'Opening stock (please check)', NULL, 'import', '2026-11-18 19:16:25'),
(151, 150, 50, 3, 3, 'initial', 'Opening stock (please check)', NULL, 'import', '2026-11-18 19:16:25'),
(152, 151, 50, 3, 3, 'initial', 'Opening stock (please check)', NULL, 'import', '2026-11-18 19:16:25'),
(153, 152, 50, 3, 3, 'initial', 'Opening stock (please check)', NULL, 'import', '2026-11-18 19:16:25'),
(154, 153, 50, 3, 3, 'initial', 'Opening stock (please check)', NULL, 'import', '2026-11-18 19:16:25'),
(155, 154, 50, 3, 3, 'initial', 'Opening stock (please check)', NULL, 'import', '2026-11-18 19:16:25'),
(156, 155, 51, 3, 3, 'initial', 'Opening stock (please check)', NULL, 'import', '2026-11-18 19:16:25'),
(157, 156, 51, 3, 3, 'initial', 'Opening stock (please check)', NULL, 'import', '2026-11-18 19:16:25'),
(158, 157, 51, 3, 3, 'initial', 'Opening stock (please check)', NULL, 'import', '2026-11-18 19:16:25'),
(159, 158, 51, 3, 3, 'initial', 'Opening stock (please check)', NULL, 'import', '2026-11-18 19:16:25'),
(160, 159, 51, 3, 3, 'initial', 'Opening stock (please check)', NULL, 'import', '2026-11-18 19:16:25'),
(161, 160, 52, 4, 4, 'initial', 'Opening stock (please check)', NULL, 'import', '2026-11-18 19:16:25'),
(162, 161, 52, 4, 4, 'initial', 'Opening stock (please check)', NULL, 'import', '2026-11-18 19:16:25'),
(163, 162, 52, 4, 4, 'initial', 'Opening stock (please check)', NULL, 'import', '2026-11-18 19:16:25'),
(164, 163, 52, 4, 4, 'initial', 'Opening stock (please check)', NULL, 'import', '2026-11-18 19:16:25'),
(165, 164, 52, 4, 4, 'initial', 'Opening stock (please check)', NULL, 'import', '2026-11-18 19:16:25'),
(166, 165, 52, 4, 4, 'initial', 'Opening stock (please check)', NULL, 'import', '2026-11-18 19:16:25'),
(167, 166, 52, 4, 4, 'initial', 'Opening stock (please check)', NULL, 'import', '2026-11-18 19:16:25'),
(168, 167, 52, 4, 4, 'initial', 'Opening stock (please check)', NULL, 'import', '2026-11-18 19:16:25'),
(169, 168, 52, 4, 4, 'initial', 'Opening stock (please check)', NULL, 'import', '2026-11-18 19:16:25'),
(170, 169, 52, 4, 4, 'initial', 'Opening stock (please check)', NULL, 'import', '2026-11-18 19:16:25'),
(171, 170, 53, 3, 3, 'initial', 'Opening stock (please check)', NULL, 'import', '2026-11-18 19:16:25'),
(172, 171, 53, 3, 3, 'initial', 'Opening stock (please check)', NULL, 'import', '2026-11-18 19:16:25'),
(173, 172, 53, 3, 3, 'initial', 'Opening stock (please check)', NULL, 'import', '2026-11-18 19:16:25'),
(174, 173, 53, 3, 3, 'initial', 'Opening stock (please check)', NULL, 'import', '2026-11-18 19:16:25'),
(175, 174, 53, 3, 3, 'initial', 'Opening stock (please check)', NULL, 'import', '2026-11-18 19:16:25'),
(176, 175, 54, 3, 3, 'initial', 'Opening stock (please check)', NULL, 'import', '2026-11-18 19:16:25'),
(177, 176, 54, 3, 3, 'initial', 'Opening stock (please check)', NULL, 'import', '2026-11-18 19:16:25'),
(178, 177, 54, 3, 3, 'initial', 'Opening stock (please check)', NULL, 'import', '2026-11-18 19:16:25'),
(179, 178, 54, 3, 3, 'initial', 'Opening stock (please check)', NULL, 'import', '2026-11-18 19:16:25'),
(180, 179, 54, 3, 3, 'initial', 'Opening stock (please check)', NULL, 'import', '2026-11-18 19:16:25'),
(181, 180, 54, 3, 3, 'initial', 'Opening stock (please check)', NULL, 'import', '2026-11-18 19:16:25'),
(182, 181, 54, 3, 3, 'initial', 'Opening stock (please check)', NULL, 'import', '2026-11-18 19:16:25'),
(183, 182, 54, 3, 3, 'initial', 'Opening stock (please check)', NULL, 'import', '2026-11-18 19:16:25'),
(184, 183, 54, 3, 3, 'initial', 'Opening stock (please check)', NULL, 'import', '2026-11-18 19:16:25'),
(185, 184, 54, 3, 3, 'initial', 'Opening stock (please check)', NULL, 'import', '2026-11-18 19:16:25'),
(186, 185, 55, 3, 3, 'initial', 'Opening stock (please check)', NULL, 'import', '2026-11-18 19:16:25'),
(187, 186, 55, 3, 3, 'initial', 'Opening stock (please check)', NULL, 'import', '2026-11-18 19:16:25'),
(188, 187, 55, 3, 3, 'initial', 'Opening stock (please check)', NULL, 'import', '2026-11-18 19:16:25'),
(189, 188, 55, 3, 3, 'initial', 'Opening stock (please check)', NULL, 'import', '2026-11-18 19:16:25'),
(190, 189, 55, 3, 3, 'initial', 'Opening stock (please check)', NULL, 'import', '2026-11-18 19:16:25'),
(191, 190, 55, 3, 3, 'initial', 'Opening stock (please check)', NULL, 'import', '2026-11-18 19:16:25'),
(192, 191, 55, 3, 3, 'initial', 'Opening stock (please check)', NULL, 'import', '2026-11-18 19:16:25'),
(193, 192, 55, 3, 3, 'initial', 'Opening stock (please check)', NULL, 'import', '2026-11-18 19:16:25'),
(194, 193, 55, 3, 3, 'initial', 'Opening stock (please check)', NULL, 'import', '2026-11-18 19:16:25'),
(195, 194, 55, 3, 3, 'initial', 'Opening stock (please check)', NULL, 'import', '2026-11-18 19:16:25'),
(196, 195, 55, 3, 3, 'initial', 'Opening stock (please check)', NULL, 'import', '2026-11-18 19:16:25'),
(197, 196, 55, 3, 3, 'initial', 'Opening stock (please check)', NULL, 'import', '2026-11-18 19:16:25'),
(198, 197, 55, 3, 3, 'initial', 'Opening stock (please check)', NULL, 'import', '2026-11-18 19:16:25'),
(199, 198, 55, 3, 3, 'initial', 'Opening stock (please check)', NULL, 'import', '2026-11-18 19:16:25'),
(200, 199, 55, 3, 3, 'initial', 'Opening stock (please check)', NULL, 'import', '2026-11-18 19:16:25'),
(201, 200, 55, 3, 3, 'initial', 'Opening stock (please check)', NULL, 'import', '2026-11-18 19:16:25'),
(202, 201, 55, 3, 3, 'initial', 'Opening stock (please check)', NULL, 'import', '2026-11-18 19:16:25'),
(203, 202, 55, 3, 3, 'initial', 'Opening stock (please check)', NULL, 'import', '2026-11-18 19:16:25'),
(204, 203, 55, 3, 3, 'initial', 'Opening stock (please check)', NULL, 'import', '2026-11-18 19:16:25'),
(205, 204, 55, 3, 3, 'initial', 'Opening stock (please check)', NULL, 'import', '2026-11-18 19:16:25'),
(206, 205, 55, 3, 3, 'initial', 'Opening stock (please check)', NULL, 'import', '2026-11-18 19:16:25'),
(207, 206, 55, 3, 3, 'initial', 'Opening stock (please check)', NULL, 'import', '2026-11-18 19:16:26'),
(208, 207, 55, 3, 3, 'initial', 'Opening stock (please check)', NULL, 'import', '2026-11-18 19:16:26'),
(209, 208, 55, 3, 3, 'initial', 'Opening stock (please check)', NULL, 'import', '2026-11-18 19:16:26'),
(210, 209, 55, 3, 3, 'initial', 'Opening stock (please check)', NULL, 'import', '2026-11-18 19:16:26'),
(211, 210, 55, 3, 3, 'initial', 'Opening stock (please check)', NULL, 'import', '2026-11-18 19:16:26'),
(212, 211, 55, 3, 3, 'initial', 'Opening stock (please check)', NULL, 'import', '2026-11-18 19:16:26'),
(213, 212, 55, 3, 3, 'initial', 'Opening stock (please check)', NULL, 'import', '2026-11-18 19:16:26'),
(214, 213, 55, 3, 3, 'initial', 'Opening stock (please check)', NULL, 'import', '2026-11-18 19:16:26'),
(215, 214, 55, 3, 3, 'initial', 'Opening stock (please check)', NULL, 'import', '2026-11-18 19:16:26'),
(216, 215, 56, 3, 3, 'initial', 'Opening stock (please check)', NULL, 'import', '2026-11-18 19:16:26'),
(217, 216, 56, 3, 3, 'initial', 'Opening stock (please check)', NULL, 'import', '2026-11-18 19:16:26'),
(218, 217, 56, 3, 3, 'initial', 'Opening stock (please check)', NULL, 'import', '2026-11-18 19:16:26'),
(219, 218, 56, 3, 3, 'initial', 'Opening stock (please check)', NULL, 'import', '2026-11-18 19:16:26'),
(220, 219, 56, 3, 3, 'initial', 'Opening stock (please check)', NULL, 'import', '2026-11-18 19:16:26'),
(221, 220, 56, 3, 3, 'initial', 'Opening stock (please check)', NULL, 'import', '2026-11-18 19:16:26'),
(222, 221, 56, 3, 3, 'initial', 'Opening stock (please check)', NULL, 'import', '2026-11-18 19:16:26'),
(223, 222, 56, 3, 3, 'initial', 'Opening stock (please check)', NULL, 'import', '2026-11-18 19:16:26'),
(224, 223, 56, 3, 3, 'initial', 'Opening stock (please check)', NULL, 'import', '2026-11-18 19:16:26'),
(225, 224, 56, 3, 3, 'initial', 'Opening stock (please check)', NULL, 'import', '2026-11-18 19:16:26'),
(226, 225, 56, 3, 3, 'initial', 'Opening stock (please check)', NULL, 'import', '2026-11-18 19:16:26'),
(227, 226, 56, 3, 3, 'initial', 'Opening stock (please check)', NULL, 'import', '2026-11-18 19:16:26'),
(228, 227, 56, 3, 3, 'initial', 'Opening stock (please check)', NULL, 'import', '2026-11-18 19:16:26'),
(229, 228, 56, 3, 3, 'initial', 'Opening stock (please check)', NULL, 'import', '2026-11-18 19:16:26'),
(230, 229, 56, 3, 3, 'initial', 'Opening stock (please check)', NULL, 'import', '2026-11-18 19:16:26'),
(231, 230, 56, 3, 3, 'initial', 'Opening stock (please check)', NULL, 'import', '2026-11-18 19:16:26'),
(232, 231, 56, 3, 3, 'initial', 'Opening stock (please check)', NULL, 'import', '2026-11-18 19:16:26'),
(233, 232, 56, 3, 3, 'initial', 'Opening stock (please check)', NULL, 'import', '2026-11-18 19:16:26'),
(234, 233, 56, 3, 3, 'initial', 'Opening stock (please check)', NULL, 'import', '2026-11-18 19:16:26'),
(235, 234, 56, 3, 3, 'initial', 'Opening stock (please check)', NULL, 'import', '2026-11-18 19:16:26'),
(236, 235, 57, 3, 3, 'initial', 'Opening stock (please check)', NULL, 'import', '2026-11-18 19:16:26'),
(237, 236, 57, 3, 3, 'initial', 'Opening stock (please check)', NULL, 'import', '2026-11-18 19:16:26'),
(238, 237, 57, 3, 3, 'initial', 'Opening stock (please check)', NULL, 'import', '2026-11-18 19:16:26'),
(239, 238, 57, 3, 3, 'initial', 'Opening stock (please check)', NULL, 'import', '2026-11-18 19:16:26'),
(240, 239, 57, 3, 3, 'initial', 'Opening stock (please check)', NULL, 'import', '2026-11-18 19:16:26'),
(241, 240, 58, 3, 3, 'initial', 'Opening stock (please check)', NULL, 'import', '2026-11-18 19:16:26'),
(242, 241, 58, 3, 3, 'initial', 'Opening stock (please check)', NULL, 'import', '2026-11-18 19:16:26'),
(243, 242, 58, 3, 3, 'initial', 'Opening stock (please check)', NULL, 'import', '2026-11-18 19:16:26'),
(244, 243, 58, 3, 3, 'initial', 'Opening stock (please check)', NULL, 'import', '2026-11-18 19:16:26'),
(245, 244, 58, 3, 3, 'initial', 'Opening stock (please check)', NULL, 'import', '2026-11-18 19:16:26'),
(246, 245, 59, 3, 3, 'initial', 'Opening stock (please check)', NULL, 'import', '2026-11-18 19:16:26'),
(247, 246, 59, 3, 3, 'initial', 'Opening stock (please check)', NULL, 'import', '2026-11-18 19:16:26'),
(248, 247, 59, 3, 3, 'initial', 'Opening stock (please check)', NULL, 'import', '2026-11-18 19:16:26'),
(249, 248, 59, 3, 3, 'initial', 'Opening stock (please check)', NULL, 'import', '2026-11-18 19:16:26'),
(250, 249, 59, 3, 3, 'initial', 'Opening stock (please check)', NULL, 'import', '2026-11-18 19:16:26'),
(251, 250, 60, 3, 3, 'initial', 'Opening stock (please check)', NULL, 'import', '2026-11-18 19:16:26'),
(252, 251, 60, 3, 3, 'initial', 'Opening stock (please check)', NULL, 'import', '2026-11-18 19:16:26'),
(253, 252, 60, 3, 3, 'initial', 'Opening stock (please check)', NULL, 'import', '2026-11-18 19:16:26'),
(254, 253, 60, 3, 3, 'initial', 'Opening stock (please check)', NULL, 'import', '2026-11-18 19:16:26'),
(255, 254, 60, 3, 3, 'initial', 'Opening stock (please check)', NULL, 'import', '2026-11-18 19:16:26'),
(256, 255, 61, 3, 3, 'initial', 'Opening stock (please check)', NULL, 'import', '2026-11-18 19:16:26'),
(257, 256, 61, 3, 3, 'initial', 'Opening stock (please check)', NULL, 'import', '2026-11-18 19:16:26'),
(258, 257, 61, 3, 3, 'initial', 'Opening stock (please check)', NULL, 'import', '2026-11-18 19:16:26'),
(259, 258, 61, 3, 3, 'initial', 'Opening stock (please check)', NULL, 'import', '2026-11-18 19:16:26'),
(260, 259, 61, 3, 3, 'initial', 'Opening stock (please check)', NULL, 'import', '2026-11-18 19:16:26'),
(261, 260, 61, 3, 3, 'initial', 'Opening stock (please check)', NULL, 'import', '2026-11-18 19:16:26'),
(262, 261, 61, 3, 3, 'initial', 'Opening stock (please check)', NULL, 'import', '2026-11-18 19:16:26'),
(263, 262, 61, 3, 3, 'initial', 'Opening stock (please check)', NULL, 'import', '2026-11-18 19:16:26'),
(264, 263, 61, 3, 3, 'initial', 'Opening stock (please check)', NULL, 'import', '2026-11-18 19:16:26'),
(265, 264, 61, 3, 3, 'initial', 'Opening stock (please check)', NULL, 'import', '2026-11-18 19:16:26'),
(266, 265, 61, 3, 3, 'initial', 'Opening stock (please check)', NULL, 'import', '2026-11-18 19:16:26'),
(267, 266, 61, 3, 3, 'initial', 'Opening stock (please check)', NULL, 'import', '2026-11-18 19:16:26'),
(268, 267, 61, 3, 3, 'initial', 'Opening stock (please check)', NULL, 'import', '2026-11-18 19:16:26'),
(269, 268, 61, 3, 3, 'initial', 'Opening stock (please check)', NULL, 'import', '2026-11-18 19:16:26'),
(270, 269, 61, 3, 3, 'initial', 'Opening stock (please check)', NULL, 'import', '2026-11-18 19:16:26'),
(271, 270, 61, 3, 3, 'initial', 'Opening stock (please check)', NULL, 'import', '2026-11-18 19:16:26'),
(272, 271, 61, 3, 3, 'initial', 'Opening stock (please check)', NULL, 'import', '2026-11-18 19:16:26'),
(273, 272, 61, 3, 3, 'initial', 'Opening stock (please check)', NULL, 'import', '2026-11-18 19:16:26'),
(274, 273, 61, 3, 3, 'initial', 'Opening stock (please check)', NULL, 'import', '2026-11-18 19:16:26'),
(275, 274, 61, 3, 3, 'initial', 'Opening stock (please check)', NULL, 'import', '2026-11-18 19:16:26'),
(276, 275, 61, 3, 3, 'initial', 'Opening stock (please check)', NULL, 'import', '2026-11-18 19:16:26'),
(277, 276, 61, 3, 3, 'initial', 'Opening stock (please check)', NULL, 'import', '2026-11-18 19:16:26'),
(278, 277, 61, 3, 3, 'initial', 'Opening stock (please check)', NULL, 'import', '2026-11-18 19:16:26'),
(279, 278, 61, 3, 3, 'initial', 'Opening stock (please check)', NULL, 'import', '2026-11-18 19:16:26'),
(280, 279, 61, 3, 3, 'initial', 'Opening stock (please check)', NULL, 'import', '2026-11-18 19:16:26'),
(281, 280, 62, 3, 3, 'initial', 'Opening stock (please check)', NULL, 'import', '2026-11-18 19:16:26'),
(282, 281, 62, 3, 3, 'initial', 'Opening stock (please check)', NULL, 'import', '2026-11-18 19:16:26'),
(283, 282, 62, 3, 3, 'initial', 'Opening stock (please check)', NULL, 'import', '2026-11-18 19:16:26'),
(284, 283, 62, 3, 3, 'initial', 'Opening stock (please check)', NULL, 'import', '2026-11-18 19:16:26'),
(285, 284, 62, 3, 3, 'initial', 'Opening stock (please check)', NULL, 'import', '2026-11-18 19:16:26'),
(286, 285, 63, 3, 3, 'initial', 'Opening stock (please check)', NULL, 'import', '2026-11-18 19:16:26'),
(287, 286, 63, 3, 3, 'initial', 'Opening stock (please check)', NULL, 'import', '2026-11-18 19:16:26'),
(288, 287, 63, 3, 3, 'initial', 'Opening stock (please check)', NULL, 'import', '2026-11-18 19:16:26'),
(289, 288, 63, 3, 3, 'initial', 'Opening stock (please check)', NULL, 'import', '2026-11-18 19:16:26'),
(290, 289, 63, 3, 3, 'initial', 'Opening stock (please check)', NULL, 'import', '2026-11-18 19:16:26'),
(291, 290, 63, 3, 3, 'initial', 'Opening stock (please check)', NULL, 'import', '2026-11-18 19:16:26'),
(292, 291, 63, 3, 3, 'initial', 'Opening stock (please check)', NULL, 'import', '2026-11-18 19:16:26'),
(293, 292, 63, 3, 3, 'initial', 'Opening stock (please check)', NULL, 'import', '2026-11-18 19:16:27'),
(294, 293, 63, 3, 3, 'initial', 'Opening stock (please check)', NULL, 'import', '2026-11-18 19:16:27'),
(295, 294, 63, 3, 3, 'initial', 'Opening stock (please check)', NULL, 'import', '2026-11-18 19:16:27'),
(296, 295, 64, 3, 3, 'initial', 'Opening stock (please check)', NULL, 'import', '2026-11-18 19:16:27'),
(297, 296, 64, 3, 3, 'initial', 'Opening stock (please check)', NULL, 'import', '2026-11-18 19:16:27'),
(298, 297, 64, 3, 3, 'initial', 'Opening stock (please check)', NULL, 'import', '2026-11-18 19:16:27'),
(299, 298, 64, 3, 3, 'initial', 'Opening stock (please check)', NULL, 'import', '2026-11-18 19:16:27'),
(300, 299, 64, 3, 3, 'initial', 'Opening stock (please check)', NULL, 'import', '2026-11-18 19:16:27'),
(301, 300, 65, 3, 3, 'initial', 'Opening stock (please check)', NULL, 'import', '2026-11-18 19:16:27'),
(302, 301, 65, 3, 3, 'initial', 'Opening stock (please check)', NULL, 'import', '2026-11-18 19:16:27'),
(303, 302, 65, 3, 3, 'initial', 'Opening stock (please check)', NULL, 'import', '2026-11-18 19:16:27'),
(304, 303, 65, 3, 3, 'initial', 'Opening stock (please check)', NULL, 'import', '2026-11-18 19:16:27'),
(305, 304, 65, 3, 3, 'initial', 'Opening stock (please check)', NULL, 'import', '2026-11-18 19:16:27'),
(306, 305, 66, 3, 3, 'initial', 'Opening stock (please check)', NULL, 'import', '2026-11-18 19:16:27'),
(307, 306, 66, 3, 3, 'initial', 'Opening stock (please check)', NULL, 'import', '2026-11-18 19:16:27'),
(308, 307, 66, 3, 3, 'initial', 'Opening stock (please check)', NULL, 'import', '2026-11-18 19:16:27'),
(309, 308, 66, 3, 3, 'initial', 'Opening stock (please check)', NULL, 'import', '2026-11-18 19:16:27'),
(310, 309, 66, 3, 3, 'initial', 'Opening stock (please check)', NULL, 'import', '2026-11-18 19:16:27'),
(311, 310, 67, 3, 3, 'initial', 'Opening stock (please check)', NULL, 'import', '2026-11-18 19:16:27'),
(312, 311, 67, 3, 3, 'initial', 'Opening stock (please check)', NULL, 'import', '2026-11-18 19:16:27'),
(313, 312, 67, 3, 3, 'initial', 'Opening stock (please check)', NULL, 'import', '2026-11-18 19:16:27'),
(314, 313, 67, 3, 3, 'initial', 'Opening stock (please check)', NULL, 'import', '2026-11-18 19:16:27'),
(315, 314, 67, 3, 3, 'initial', 'Opening stock (please check)', NULL, 'import', '2026-11-18 19:16:27'),
(316, 315, 68, 10, 10, 'initial', 'Opening stock (please check)', NULL, 'import', '2026-11-18 19:16:27'),
(317, 316, 68, 10, 10, 'initial', 'Opening stock (please check)', NULL, 'import', '2026-11-18 19:16:27'),
(318, 317, 69, 10, 10, 'initial', 'Opening stock (please check)', NULL, 'import', '2026-11-18 19:16:27'),
(319, 318, 70, 10, 10, 'initial', 'Opening stock (please check)', NULL, 'import', '2026-11-18 19:16:27'),
(320, 319, 71, 6, 6, 'initial', 'Opening stock (please check)', NULL, 'import', '2026-11-18 19:16:27'),
(321, 320, 71, 6, 6, 'initial', 'Opening stock (please check)', NULL, 'import', '2026-11-18 19:16:27'),
(322, 321, 71, 6, 6, 'initial', 'Opening stock (please check)', NULL, 'import', '2026-11-18 19:16:27'),
(323, 322, 71, 6, 6, 'initial', 'Opening stock (please check)', NULL, 'import', '2026-11-18 19:16:27'),
(324, 323, 71, 6, 6, 'initial', 'Opening stock (please check)', NULL, 'import', '2026-11-18 19:16:27'),
(325, 324, 71, 6, 6, 'initial', 'Opening stock (please check)', NULL, 'import', '2026-11-18 19:16:27'),
(326, 325, 71, 6, 6, 'initial', 'Opening stock (please check)', NULL, 'import', '2026-11-18 19:16:27'),
(327, 326, 71, 6, 6, 'initial', 'Opening stock (please check)', NULL, 'import', '2026-11-18 19:16:27'),
(328, 327, 71, 6, 6, 'initial', 'Opening stock (please check)', NULL, 'import', '2026-11-18 19:16:27'),
(329, 328, 72, 4, 4, 'initial', 'Opening stock (please check)', NULL, 'import', '2026-11-18 19:16:27'),
(330, 329, 72, 4, 4, 'initial', 'Opening stock (please check)', NULL, 'import', '2026-11-18 19:16:27'),
(331, 330, 72, 4, 4, 'initial', 'Opening stock (please check)', NULL, 'import', '2026-11-18 19:16:27'),
(332, 331, 72, 4, 4, 'initial', 'Opening stock (please check)', NULL, 'import', '2026-11-18 19:16:27'),
(333, 332, 72, 4, 4, 'initial', 'Opening stock (please check)', NULL, 'import', '2026-11-18 19:16:27'),
(334, 333, 72, 4, 4, 'initial', 'Opening stock (please check)', NULL, 'import', '2026-11-18 19:16:27'),
(335, 334, 73, 4, 4, 'initial', 'Opening stock (please check)', NULL, 'import', '2026-11-18 19:16:27'),
(336, 335, 74, 4, 4, 'initial', 'Opening stock (please check)', NULL, 'import', '2026-11-18 19:16:27'),
(337, 336, 75, 3, 3, 'initial', 'Opening stock (please check)', NULL, 'import', '2026-11-18 19:16:27'),
(338, 337, 75, 3, 3, 'initial', 'Opening stock (please check)', NULL, 'import', '2026-11-18 19:16:27'),
(339, 338, 75, 3, 3, 'initial', 'Opening stock (please check)', NULL, 'import', '2026-11-18 19:16:27'),
(340, 339, 76, 3, 3, 'initial', 'Opening stock (please check)', NULL, 'import', '2026-11-18 19:16:27'),
(341, 340, 77, 3, 3, 'initial', 'Opening stock (please check)', NULL, 'import', '2026-11-18 19:16:27'),
(342, 341, 77, 3, 3, 'initial', 'Opening stock (please check)', NULL, 'import', '2026-11-18 19:16:27'),
(343, 342, 77, 3, 3, 'initial', 'Opening stock (please check)', NULL, 'import', '2026-11-18 19:16:27'),
(344, 343, 77, 3, 3, 'initial', 'Opening stock (please check)', NULL, 'import', '2026-11-18 19:16:27'),
(345, 344, 78, 4, 4, 'initial', 'Opening stock (please check)', NULL, 'import', '2026-11-18 19:16:27'),
(346, 345, 79, 3, 3, 'initial', 'Opening stock (please check)', NULL, 'import', '2026-11-18 19:16:27'),
(347, 346, 79, 3, 3, 'initial', 'Opening stock (please check)', NULL, 'import', '2026-11-18 19:16:27'),
(348, 347, 79, 3, 3, 'initial', 'Opening stock (please check)', NULL, 'import', '2026-11-18 19:16:27'),
(349, 348, 79, 3, 3, 'initial', 'Opening stock (please check)', NULL, 'import', '2026-11-18 19:16:27'),
(350, 349, 80, 3, 3, 'initial', 'Opening stock (please check)', NULL, 'import', '2026-11-18 19:16:27'),
(351, 350, 80, 3, 3, 'initial', 'Opening stock (please check)', NULL, 'import', '2026-11-18 19:16:27'),
(352, 351, 80, 3, 3, 'initial', 'Opening stock (please check)', NULL, 'import', '2026-11-18 19:16:27'),
(353, 352, 80, 3, 3, 'initial', 'Opening stock (please check)', NULL, 'import', '2026-11-18 19:16:27'),
(354, 353, 81, 4, 4, 'initial', 'Opening stock (please check)', NULL, 'import', '2026-11-18 19:16:27');

-- --------------------------------------------------------

--
-- Table structure for table `variants`
--

DROP TABLE IF EXISTS `variants`;
CREATE TABLE `variants` (
  `id` int(10) UNSIGNED NOT NULL,
  `product_id` int(11) NOT NULL,
  `size` varchar(40) DEFAULT '',
  `color` varchar(40) DEFAULT '',
  `sku` varchar(80) DEFAULT NULL,
  `stock` int(11) NOT NULL DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `variants`
--

INSERT INTO `variants` (`id`, `product_id`, `size`, `color`, `sku`, `stock`) VALUES
(1, 1, 'S', 'Lavender', 'LV-0101-S', 6),
(2, 1, 'M', 'Lavender', 'LV-0101-M', 12),
(3, 1, 'L', 'Lavender', 'LV-0101-L', 8),
(4, 1, 'XL', 'Lavender', 'LV-0101-XL', 3),
(5, 2, 'S', 'Mint', 'LV-0102-S', 4),
(6, 2, 'M', 'Mint', 'LV-0102-M', 8),
(7, 2, 'L', 'Mint', 'LV-0102-L', 7),
(8, 2, 'XL', 'Mint', 'LV-0102-XL', 2),
(9, 3, 'S', 'Cherry', 'LV-0103-S', 10),
(10, 3, 'M', 'Cherry', 'LV-0103-M', 14),
(11, 3, 'L', 'Cherry', 'LV-0103-L', 11),
(12, 3, 'XL', 'Cherry', 'LV-0103-XL', 6),
(13, 4, '', 'Forest Green', 'LV-0104-FS', 6),
(14, 5, '', 'Blush Pink', 'LV-0105-FS', 3),
(15, 6, 'S', 'Mint', 'LV-0106-S', 5),
(16, 6, 'M', 'Mint', 'LV-0106-M', 9),
(17, 6, 'L', 'Mint', 'LV-0106-L', 8),
(18, 6, 'XL', 'Mint', 'LV-0106-XL', 4),
(19, 7, 'S', 'Lavender', 'LV-0107-S', 0),
(20, 7, 'M', 'Lavender', 'LV-0107-M', 2),
(21, 7, 'L', 'Lavender', 'LV-0107-L', 1),
(22, 7, 'XL', 'Lavender', 'LV-0107-XL', 0),
(23, 8, 'S', 'Cherry Red', 'LV-0108-S', 3),
(24, 8, 'M', 'Cherry Red', 'LV-0108-M', 6),
(25, 8, 'L', 'Cherry Red', 'LV-0108-L', 5),
(26, 8, 'XL', 'Cherry Red', 'LV-0108-XL', 2),
(27, 9, 'S', 'Lavender', 'LV-0109-S', 6),
(28, 9, 'M', 'Lavender', 'LV-0109-M', 8),
(29, 9, 'L', 'Lavender', 'LV-0109-L', 5),
(30, 9, 'XL', 'Lavender', 'LV-0109-XL', 3),
(31, 10, 'S', 'Milk White', 'LV-0110-S', 12),
(32, 10, 'M', 'Milk White', 'LV-0110-M', 15),
(33, 10, 'L', 'Milk White', 'LV-0110-L', 10),
(34, 10, 'XL', 'Milk White', 'LV-0110-XL', 6),
(35, 11, 'S', 'Forest Green', 'LV-0111-S', 8),
(36, 11, 'M', 'Forest Green', 'LV-0111-M', 10),
(37, 11, 'L', 'Forest Green', 'LV-0111-L', 9),
(38, 11, 'XL', 'Forest Green', 'LV-0111-XL', 5),
(39, 12, '', 'Dusty Rose', 'LV-0112-FS', 38),
(40, 13, '', 'Purple', 'LV-0113-FS', 25),
(41, 14, 'S', 'Forest Green', 'LV-0114-S', 9),
(42, 14, 'M', 'Forest Green', 'LV-0114-M', 11),
(43, 14, 'L', 'Forest Green', 'LV-0114-L', 10),
(44, 14, 'XL', 'Forest Green', 'LV-0114-XL', 6),
(45, 15, 'S', 'Cherry', 'LV-0115-S', 0),
(46, 15, 'M', 'Cherry', 'LV-0115-M', 0),
(47, 15, 'L', 'Cherry', 'LV-0115-L', 0),
(48, 15, 'XL', 'Cherry', 'LV-0115-XL', 0),
(49, 16, '', 'Cherry', 'LV-0116-FS', 7),
(50, 17, '', 'Cream', 'LV-0117-FS', 4),
(51, 18, '', 'Gold', 'LV-0118-FS', 28),
(52, 19, '', 'Silver', 'LV-0119-FS', 18),
(53, 20, 'M', 'Mint', 'PL-0201-M-MIN', 5),
(54, 20, 'L', 'Mint', 'PL-0201-L-MIN', 5),
(55, 20, 'XL', 'Mint', 'PL-0201-XL-MIN', 5),
(56, 20, 'XXL', 'Mint', 'PL-0201-XXL-MIN', 5),
(57, 21, 'M', 'Cream', 'PL-0202-M-CRE', 5),
(58, 21, 'L', 'Cream', 'PL-0202-L-CRE', 5),
(59, 21, 'XL', 'Cream', 'PL-0202-XL-CRE', 5),
(60, 21, 'XXL', 'Cream', 'PL-0202-XXL-CRE', 5),
(61, 22, 'M', 'Peach', 'PL-0203-M-PEA', 5),
(62, 22, 'L', 'Peach', 'PL-0203-L-PEA', 5),
(63, 22, 'XL', 'Peach', 'PL-0203-XL-PEA', 5),
(64, 22, 'XXL', 'Peach', 'PL-0203-XXL-PEA', 5),
(65, 23, 'M', 'Sky Blue', 'PL-0204-M-SKY', 5),
(66, 23, 'L', 'Sky Blue', 'PL-0204-L-SKY', 5),
(67, 23, 'XL', 'Sky Blue', 'PL-0204-XL-SKY', 5),
(68, 23, 'XXL', 'Sky Blue', 'PL-0204-XXL-SKY', 5),
(69, 24, 'M', 'Blush Pink', 'PL-0205-M-BLU', 5),
(70, 24, 'L', 'Blush Pink', 'PL-0205-L-BLU', 5),
(71, 24, 'XL', 'Blush Pink', 'PL-0205-XL-BLU', 5),
(72, 24, 'XXL', 'Blush Pink', 'PL-0205-XXL-BLU', 5),
(73, 25, 'M', 'Cream', 'PL-0206-M-CRE', 5),
(74, 25, 'L', 'Cream', 'PL-0206-L-CRE', 5),
(75, 25, 'XL', 'Cream', 'PL-0206-XL-CRE', 5),
(76, 25, 'XXL', 'Cream', 'PL-0206-XXL-CRE', 5),
(77, 26, 'M', 'Cream', 'PL-0207-M-CRE', 5),
(78, 26, 'L', 'Cream', 'PL-0207-L-CRE', 5),
(79, 26, 'XL', 'Cream', 'PL-0207-XL-CRE', 5),
(80, 26, 'XXL', 'Cream', 'PL-0207-XXL-CRE', 5),
(81, 27, 'M', 'Sky Blue', 'PL-0208-M-SKY', 5),
(82, 27, 'L', 'Sky Blue', 'PL-0208-L-SKY', 5),
(83, 27, 'XL', 'Sky Blue', 'PL-0208-XL-SKY', 5),
(84, 27, 'XXL', 'Sky Blue', 'PL-0208-XXL-SKY', 5),
(85, 28, 'M', 'Pastel Multi', 'PL-0209-M-PAS', 5),
(86, 28, 'L', 'Pastel Multi', 'PL-0209-L-PAS', 5),
(87, 28, 'XL', 'Pastel Multi', 'PL-0209-XL-PAS', 5),
(88, 28, 'XXL', 'Pastel Multi', 'PL-0209-XXL-PAS', 5),
(89, 29, 'M', 'Blue White', 'PL-0210-M-BLU', 5),
(90, 29, 'L', 'Blue White', 'PL-0210-L-BLU', 5),
(91, 29, 'XL', 'Blue White', 'PL-0210-XL-BLU', 5),
(92, 29, 'XXL', 'Blue White', 'PL-0210-XXL-BLU', 5),
(93, 30, 'M', 'Sage Green', 'PL-0211-M-SAG', 5),
(94, 30, 'L', 'Sage Green', 'PL-0211-L-SAG', 5),
(95, 30, 'XL', 'Sage Green', 'PL-0211-XL-SAG', 5),
(96, 30, 'XXL', 'Sage Green', 'PL-0211-XXL-SAG', 5),
(97, 31, 'M', 'Cream Lilac', 'PL-0212-M-CRE', 5),
(98, 31, 'L', 'Cream Lilac', 'PL-0212-L-CRE', 5),
(99, 31, 'XL', 'Cream Lilac', 'PL-0212-XL-CRE', 5),
(100, 31, 'XXL', 'Cream Lilac', 'PL-0212-XXL-CRE', 5),
(101, 32, 'M', 'Lavender', 'PL-0213-M-LAV', 5),
(102, 32, 'L', 'Lavender', 'PL-0213-L-LAV', 5),
(103, 32, 'XL', 'Lavender', 'PL-0213-XL-LAV', 5),
(104, 32, 'XXL', 'Lavender', 'PL-0213-XXL-LAV', 5),
(105, 33, 'M', 'Cream', 'PL-0214-M-CRE', 5),
(106, 33, 'L', 'Cream', 'PL-0214-L-CRE', 5),
(107, 33, 'XL', 'Cream', 'PL-0214-XL-CRE', 5),
(108, 33, 'XXL', 'Cream', 'PL-0214-XXL-CRE', 5),
(109, 34, 'M', 'Black', 'PL-0215-M-BLA', 5),
(110, 34, 'L', 'Black', 'PL-0215-L-BLA', 5),
(111, 34, 'XL', 'Black', 'PL-0215-XL-BLA', 5),
(112, 34, 'XXL', 'Black', 'PL-0215-XXL-BLA', 5),
(113, 35, 'M', 'White Blue', 'PL-0216-M-WHI', 5),
(114, 35, 'L', 'White Blue', 'PL-0216-L-WHI', 5),
(115, 35, 'XL', 'White Blue', 'PL-0216-XL-WHI', 5),
(116, 35, 'XXL', 'White Blue', 'PL-0216-XXL-WHI', 5),
(117, 36, 'M', 'Black', 'PL-0217-M-BLA', 5),
(118, 36, 'L', 'Black', 'PL-0217-L-BLA', 5),
(119, 36, 'XL', 'Black', 'PL-0217-XL-BLA', 5),
(120, 36, 'XXL', 'Black', 'PL-0217-XXL-BLA', 5),
(121, 37, 'M', 'Sage Pink', 'PL-0218-M-SAG', 5),
(122, 37, 'L', 'Sage Pink', 'PL-0218-L-SAG', 5),
(123, 37, 'XL', 'Sage Pink', 'PL-0218-XL-SAG', 5),
(124, 37, 'XXL', 'Sage Pink', 'PL-0218-XXL-SAG', 5),
(125, 38, 'M', 'Cream', 'PL-0219-M-CRE', 5),
(126, 38, 'L', 'Cream', 'PL-0219-L-CRE', 5),
(127, 38, 'XL', 'Cream', 'PL-0219-XL-CRE', 5),
(128, 38, 'XXL', 'Cream', 'PL-0219-XXL-CRE', 5),
(129, 39, '', 'Blush', 'PL-0220-BLU', 4),
(130, 39, '', 'Maroon', 'PL-0220-MAR', 4),
(131, 40, '', 'Black', 'PL-0221-BLA', 4),
(132, 40, '', 'Blush', 'PL-0221-BLU', 4),
(133, 41, '', 'Emerald', 'PL-0222-EME', 4),
(134, 42, '', 'Red', 'PL-0223-RED', 3),
(135, 43, '', 'Maroon', 'PL-0224-MAR', 3),
(136, 44, '', 'Red', 'PL-0225-RED', 3),
(137, 45, '', 'Red', 'PL-0226-RED', 3),
(138, 46, '', 'Wine Red', 'PL-0227-WIN', 3),
(139, 47, 'S', 'White Blush', 'PL-0228-S-WHI', 3),
(140, 47, 'M', 'White Blush', 'PL-0228-M-WHI', 3),
(141, 47, 'L', 'White Blush', 'PL-0228-L-WHI', 3),
(142, 48, 'S', 'Black', 'PL-0229-S-BLA', 4),
(143, 48, 'M', 'Black', 'PL-0229-M-BLA', 4),
(144, 48, 'L', 'Black', 'PL-0229-L-BLA', 4),
(145, 49, 'M', 'Beige', 'PL-0230-M-BEI', 3),
(146, 49, 'L', 'Beige', 'PL-0230-L-BEI', 3),
(147, 49, 'XL', 'Beige', 'PL-0230-XL-BEI', 3),
(148, 49, 'XXL', 'Beige', 'PL-0230-XXL-BEI', 3),
(149, 49, 'XXXL', 'Beige', 'PL-0230-XXXL-BEI', 3),
(150, 50, 'M', 'Black', 'PL-0231-M-BLA', 3),
(151, 50, 'L', 'Black', 'PL-0231-L-BLA', 3),
(152, 50, 'XL', 'Black', 'PL-0231-XL-BLA', 3),
(153, 50, 'XXL', 'Black', 'PL-0231-XXL-BLA', 3),
(154, 50, 'XXXL', 'Black', 'PL-0231-XXXL-BLA', 3),
(155, 51, 'M', 'Black', 'PL-0232-M-BLA', 3),
(156, 51, 'L', 'Black', 'PL-0232-L-BLA', 3),
(157, 51, 'XL', 'Black', 'PL-0232-XL-BLA', 3),
(158, 51, 'XXL', 'Black', 'PL-0232-XXL-BLA', 3),
(159, 51, 'XXXL', 'Black', 'PL-0232-XXXL-BLA', 3),
(160, 52, 'M', 'Beige', 'PL-0233-M-BEI', 4),
(161, 52, 'L', 'Beige', 'PL-0233-L-BEI', 4),
(162, 52, 'XL', 'Beige', 'PL-0233-XL-BEI', 4),
(163, 52, 'XXL', 'Beige', 'PL-0233-XXL-BEI', 4),
(164, 52, 'XXXL', 'Beige', 'PL-0233-XXXL-BEI', 4),
(165, 52, 'M', 'Black', 'PL-0233-M-BLA', 4),
(166, 52, 'L', 'Black', 'PL-0233-L-BLA', 4),
(167, 52, 'XL', 'Black', 'PL-0233-XL-BLA', 4),
(168, 52, 'XXL', 'Black', 'PL-0233-XXL-BLA', 4),
(169, 52, 'XXXL', 'Black', 'PL-0233-XXXL-BLA', 4),
(170, 53, 'M', 'Cream', 'PL-0234-M-CRE', 3),
(171, 53, 'L', 'Cream', 'PL-0234-L-CRE', 3),
(172, 53, 'XL', 'Cream', 'PL-0234-XL-CRE', 3),
(173, 53, 'XXL', 'Cream', 'PL-0234-XXL-CRE', 3),
(174, 53, 'XXXL', 'Cream', 'PL-0234-XXXL-CRE', 3),
(175, 54, 'M', 'Beige', 'PL-0235-M-BEI', 3),
(176, 54, 'L', 'Beige', 'PL-0235-L-BEI', 3),
(177, 54, 'XL', 'Beige', 'PL-0235-XL-BEI', 3),
(178, 54, 'XXL', 'Beige', 'PL-0235-XXL-BEI', 3),
(179, 54, 'XXXL', 'Beige', 'PL-0235-XXXL-BEI', 3),
(180, 54, 'M', 'Black', 'PL-0235-M-BLA', 3),
(181, 54, 'L', 'Black', 'PL-0235-L-BLA', 3),
(182, 54, 'XL', 'Black', 'PL-0235-XL-BLA', 3),
(183, 54, 'XXL', 'Black', 'PL-0235-XXL-BLA', 3),
(184, 54, 'XXXL', 'Black', 'PL-0235-XXXL-BLA', 3),
(185, 55, '32', 'Rust', 'PL-0236-32-RUS', 3),
(186, 55, '34', 'Rust', 'PL-0236-34-RUS', 3),
(187, 55, '36', 'Rust', 'PL-0236-36-RUS', 3),
(188, 55, '38', 'Rust', 'PL-0236-38-RUS', 3),
(189, 55, '40', 'Rust', 'PL-0236-40-RUS', 3),
(190, 55, '32', 'Blue', 'PL-0236-32-BLU', 3),
(191, 55, '34', 'Blue', 'PL-0236-34-BLU', 3),
(192, 55, '36', 'Blue', 'PL-0236-36-BLU', 3),
(193, 55, '38', 'Blue', 'PL-0236-38-BLU', 3),
(194, 55, '40', 'Blue', 'PL-0236-40-BLU', 3),
(195, 55, '32', 'Grey', 'PL-0236-32-GRE', 3),
(196, 55, '34', 'Grey', 'PL-0236-34-GRE', 3),
(197, 55, '36', 'Grey', 'PL-0236-36-GRE', 3),
(198, 55, '38', 'Grey', 'PL-0236-38-GRE', 3),
(199, 55, '40', 'Grey', 'PL-0236-40-GRE', 3),
(200, 55, '32', 'Black', 'PL-0236-32-BLA', 3),
(201, 55, '34', 'Black', 'PL-0236-34-BLA', 3),
(202, 55, '36', 'Black', 'PL-0236-36-BLA', 3),
(203, 55, '38', 'Black', 'PL-0236-38-BLA', 3),
(204, 55, '40', 'Black', 'PL-0236-40-BLA', 3),
(205, 55, '32', 'Purple Grey', 'PL-0236-32-PUR', 3),
(206, 55, '34', 'Purple Grey', 'PL-0236-34-PUR', 3),
(207, 55, '36', 'Purple Grey', 'PL-0236-36-PUR', 3),
(208, 55, '38', 'Purple Grey', 'PL-0236-38-PUR', 3),
(209, 55, '40', 'Purple Grey', 'PL-0236-40-PUR', 3),
(210, 55, '32', 'Pink', 'PL-0236-32-PIN', 3),
(211, 55, '34', 'Pink', 'PL-0236-34-PIN', 3),
(212, 55, '36', 'Pink', 'PL-0236-36-PIN', 3),
(213, 55, '38', 'Pink', 'PL-0236-38-PIN', 3),
(214, 55, '40', 'Pink', 'PL-0236-40-PIN', 3),
(215, 56, '32', 'Beige', 'PL-0237-32-BEI', 3),
(216, 56, '34', 'Beige', 'PL-0237-34-BEI', 3),
(217, 56, '36', 'Beige', 'PL-0237-36-BEI', 3),
(218, 56, '38', 'Beige', 'PL-0237-38-BEI', 3),
(219, 56, '40', 'Beige', 'PL-0237-40-BEI', 3),
(220, 56, '32', 'Black', 'PL-0237-32-BLA', 3),
(221, 56, '34', 'Black', 'PL-0237-34-BLA', 3),
(222, 56, '36', 'Black', 'PL-0237-36-BLA', 3),
(223, 56, '38', 'Black', 'PL-0237-38-BLA', 3),
(224, 56, '40', 'Black', 'PL-0237-40-BLA', 3),
(225, 56, '32', 'Grey', 'PL-0237-32-GRE', 3),
(226, 56, '34', 'Grey', 'PL-0237-34-GRE', 3),
(227, 56, '36', 'Grey', 'PL-0237-36-GRE', 3),
(228, 56, '38', 'Grey', 'PL-0237-38-GRE', 3),
(229, 56, '40', 'Grey', 'PL-0237-40-GRE', 3),
(230, 56, '32', 'Brown', 'PL-0237-32-BRO', 3),
(231, 56, '34', 'Brown', 'PL-0237-34-BRO', 3),
(232, 56, '36', 'Brown', 'PL-0237-36-BRO', 3),
(233, 56, '38', 'Brown', 'PL-0237-38-BRO', 3),
(234, 56, '40', 'Brown', 'PL-0237-40-BRO', 3),
(235, 57, '32', 'Ivory', 'PL-0238-32-IVO', 3),
(236, 57, '34', 'Ivory', 'PL-0238-34-IVO', 3),
(237, 57, '36', 'Ivory', 'PL-0238-36-IVO', 3),
(238, 57, '38', 'Ivory', 'PL-0238-38-IVO', 3),
(239, 57, '40', 'Ivory', 'PL-0238-40-IVO', 3),
(240, 58, '32', 'Mauve', 'PL-0239-32-MAU', 3),
(241, 58, '34', 'Mauve', 'PL-0239-34-MAU', 3),
(242, 58, '36', 'Mauve', 'PL-0239-36-MAU', 3),
(243, 58, '38', 'Mauve', 'PL-0239-38-MAU', 3),
(244, 58, '40', 'Mauve', 'PL-0239-40-MAU', 3),
(245, 59, '32', 'Magenta', 'PL-0240-32-MAG', 3),
(246, 59, '34', 'Magenta', 'PL-0240-34-MAG', 3),
(247, 59, '36', 'Magenta', 'PL-0240-36-MAG', 3),
(248, 59, '38', 'Magenta', 'PL-0240-38-MAG', 3),
(249, 59, '40', 'Magenta', 'PL-0240-40-MAG', 3),
(250, 60, '32', 'Blush', 'PL-0241-32-BLU', 3),
(251, 60, '34', 'Blush', 'PL-0241-34-BLU', 3),
(252, 60, '36', 'Blush', 'PL-0241-36-BLU', 3),
(253, 60, '38', 'Blush', 'PL-0241-38-BLU', 3),
(254, 60, '40', 'Blush', 'PL-0241-40-BLU', 3),
(255, 61, '32', 'Grey', 'PL-0242-32-GRE', 3),
(256, 61, '34', 'Grey', 'PL-0242-34-GRE', 3),
(257, 61, '36', 'Grey', 'PL-0242-36-GRE', 3),
(258, 61, '38', 'Grey', 'PL-0242-38-GRE', 3),
(259, 61, '40', 'Grey', 'PL-0242-40-GRE', 3),
(260, 61, '32', 'Purple', 'PL-0242-32-PUR', 3),
(261, 61, '34', 'Purple', 'PL-0242-34-PUR', 3),
(262, 61, '36', 'Purple', 'PL-0242-36-PUR', 3),
(263, 61, '38', 'Purple', 'PL-0242-38-PUR', 3),
(264, 61, '40', 'Purple', 'PL-0242-40-PUR', 3),
(265, 61, '32', 'Beige', 'PL-0242-32-BEI', 3),
(266, 61, '34', 'Beige', 'PL-0242-34-BEI', 3),
(267, 61, '36', 'Beige', 'PL-0242-36-BEI', 3),
(268, 61, '38', 'Beige', 'PL-0242-38-BEI', 3),
(269, 61, '40', 'Beige', 'PL-0242-40-BEI', 3),
(270, 61, '32', 'Black', 'PL-0242-32-BLA', 3),
(271, 61, '34', 'Black', 'PL-0242-34-BLA', 3),
(272, 61, '36', 'Black', 'PL-0242-36-BLA', 3),
(273, 61, '38', 'Black', 'PL-0242-38-BLA', 3),
(274, 61, '40', 'Black', 'PL-0242-40-BLA', 3),
(275, 61, '32', 'Maroon', 'PL-0242-32-MAR', 3),
(276, 61, '34', 'Maroon', 'PL-0242-34-MAR', 3),
(277, 61, '36', 'Maroon', 'PL-0242-36-MAR', 3),
(278, 61, '38', 'Maroon', 'PL-0242-38-MAR', 3),
(279, 61, '40', 'Maroon', 'PL-0242-40-MAR', 3),
(280, 62, '32', 'Light Pink', 'PL-0243-32-LIG', 3),
(281, 62, '34', 'Light Pink', 'PL-0243-34-LIG', 3),
(282, 62, '36', 'Light Pink', 'PL-0243-36-LIG', 3),
(283, 62, '38', 'Light Pink', 'PL-0243-38-LIG', 3),
(284, 62, '40', 'Light Pink', 'PL-0243-40-LIG', 3),
(285, 63, '32', 'Black', 'PL-0244-32-BLA', 3),
(286, 63, '34', 'Black', 'PL-0244-34-BLA', 3),
(287, 63, '36', 'Black', 'PL-0244-36-BLA', 3),
(288, 63, '38', 'Black', 'PL-0244-38-BLA', 3),
(289, 63, '40', 'Black', 'PL-0244-40-BLA', 3),
(290, 63, '32', 'White', 'PL-0244-32-WHI', 3),
(291, 63, '34', 'White', 'PL-0244-34-WHI', 3),
(292, 63, '36', 'White', 'PL-0244-36-WHI', 3),
(293, 63, '38', 'White', 'PL-0244-38-WHI', 3),
(294, 63, '40', 'White', 'PL-0244-40-WHI', 3),
(295, 64, '32', 'Peach', 'PL-0245-32-PEA', 3),
(296, 64, '34', 'Peach', 'PL-0245-34-PEA', 3),
(297, 64, '36', 'Peach', 'PL-0245-36-PEA', 3),
(298, 64, '38', 'Peach', 'PL-0245-38-PEA', 3),
(299, 64, '40', 'Peach', 'PL-0245-40-PEA', 3),
(300, 65, '32', 'Chocolate', 'PL-0246-32-CHO', 3),
(301, 65, '34', 'Chocolate', 'PL-0246-34-CHO', 3),
(302, 65, '36', 'Chocolate', 'PL-0246-36-CHO', 3),
(303, 65, '38', 'Chocolate', 'PL-0246-38-CHO', 3),
(304, 65, '40', 'Chocolate', 'PL-0246-40-CHO', 3),
(305, 66, '32', 'Nude', 'PL-0247-32-NUD', 3),
(306, 66, '34', 'Nude', 'PL-0247-34-NUD', 3),
(307, 66, '36', 'Nude', 'PL-0247-36-NUD', 3),
(308, 66, '38', 'Nude', 'PL-0247-38-NUD', 3),
(309, 66, '40', 'Nude', 'PL-0247-40-NUD', 3),
(310, 67, '32', 'Mint', 'PL-0248-32-MIN', 3),
(311, 67, '34', 'Mint', 'PL-0248-34-MIN', 3),
(312, 67, '36', 'Mint', 'PL-0248-36-MIN', 3),
(313, 67, '38', 'Mint', 'PL-0248-38-MIN', 3),
(314, 67, '40', 'Mint', 'PL-0248-40-MIN', 3),
(315, 68, '', 'Peach', 'PL-0249-PEA', 10),
(316, 68, '', 'Black', 'PL-0249-BLA', 10),
(317, 69, '', 'Lemon', 'PL-0250-LEM', 10),
(318, 70, '', 'Light Pink', 'PL-0251-LIG', 10),
(319, 71, 'M', 'Blue Grey', 'PL-0252-M-BLU', 6),
(320, 71, 'L', 'Blue Grey', 'PL-0252-L-BLU', 6),
(321, 71, 'XL', 'Blue Grey', 'PL-0252-XL-BLU', 6),
(322, 71, 'M', 'Nude', 'PL-0252-M-NUD', 6),
(323, 71, 'L', 'Nude', 'PL-0252-L-NUD', 6),
(324, 71, 'XL', 'Nude', 'PL-0252-XL-NUD', 6),
(325, 71, 'M', 'Navy', 'PL-0252-M-NAV', 6),
(326, 71, 'L', 'Navy', 'PL-0252-L-NAV', 6),
(327, 71, 'XL', 'Navy', 'PL-0252-XL-NAV', 6),
(328, 72, 'L', 'Pink', 'PL-0253-L-PIN', 4),
(329, 72, 'XL', 'Pink', 'PL-0253-XL-PIN', 4),
(330, 72, 'XXL', 'Pink', 'PL-0253-XXL-PIN', 4),
(331, 72, 'L', 'Sky Blue', 'PL-0253-L-SKY', 4),
(332, 72, 'XL', 'Sky Blue', 'PL-0253-XL-SKY', 4),
(333, 72, 'XXL', 'Sky Blue', 'PL-0253-XXL-SKY', 4),
(334, 73, '', 'Multicolour', 'PL-0254-MUL', 4),
(335, 74, '', 'Cream Print', 'PL-0255-CRE', 4),
(336, 75, 'S', 'Ivory', 'PL-0256-S-IVO', 3),
(337, 75, 'M', 'Ivory', 'PL-0256-M-IVO', 3),
(338, 75, 'L', 'Ivory', 'PL-0256-L-IVO', 3),
(339, 76, 'Free', 'Cream Pastel', 'PL-0257-FREE-CRE', 3),
(340, 77, 'S', 'Pink', 'PL-0258-S-PIN', 3),
(341, 77, 'M', 'Pink', 'PL-0258-M-PIN', 3),
(342, 77, 'L', 'Pink', 'PL-0258-L-PIN', 3),
(343, 77, 'XL', 'Pink', 'PL-0258-XL-PIN', 3),
(344, 78, 'Free', 'Royal Blue', 'PL-0259-FREE-ROY', 4),
(345, 79, 'S', 'Brown', 'PL-0260-S-BRO', 3),
(346, 79, 'M', 'Brown', 'PL-0260-M-BRO', 3),
(347, 79, 'L', 'Brown', 'PL-0260-L-BRO', 3),
(348, 79, 'XL', 'Brown', 'PL-0260-XL-BRO', 3),
(349, 80, 'S', 'Black', 'PL-0261-S-BLA', 3),
(350, 80, 'M', 'Black', 'PL-0261-M-BLA', 3),
(351, 80, 'L', 'Black', 'PL-0261-L-BLA', 3),
(352, 80, 'XL', 'Black', 'PL-0261-XL-BLA', 3),
(353, 81, 'Free', 'Sand', 'PL-0262-FREE-SAN', 4);

-- --------------------------------------------------------

--
-- Table structure for table `wishlist`
--

DROP TABLE IF EXISTS `wishlist`;
CREATE TABLE `wishlist` (
  `id` int(10) UNSIGNED NOT NULL,
  `customer_id` int(11) NOT NULL,
  `product_id` int(11) NOT NULL,
  `created_at` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Indexes for dumped tables
--

--
-- Indexes for table `admins`
--
ALTER TABLE `admins`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `username` (`username`);

--
-- Indexes for table `categories`
--
ALTER TABLE `categories`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `slug` (`slug`);

--
-- Indexes for table `customers`
--
ALTER TABLE `customers`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `phone` (`phone`),
  ADD UNIQUE KEY `email` (`email`);

--
-- Indexes for table `newsletter`
--
ALTER TABLE `newsletter`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `email` (`email`);

--
-- Indexes for table `orders`
--
ALTER TABLE `orders`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `order_no` (`order_no`),
  ADD KEY `idx_orders_customer` (`customer_id`);

--
-- Indexes for table `order_items`
--
ALTER TABLE `order_items`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_items_order` (`order_id`);

--
-- Indexes for table `products`
--
ALTER TABLE `products`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `slug` (`slug`);

--
-- Indexes for table `product_images`
--
ALTER TABLE `product_images`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `settings`
--
ALTER TABLE `settings`
  ADD PRIMARY KEY (`skey`);

--
-- Indexes for table `stock_movements`
--
ALTER TABLE `stock_movements`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_moves_product` (`product_id`);

--
-- Indexes for table `variants`
--
ALTER TABLE `variants`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_var_product` (`product_id`);

--
-- Indexes for table `wishlist`
--
ALTER TABLE `wishlist`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `idx_wish` (`customer_id`,`product_id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `admins`
--
ALTER TABLE `admins`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `categories`
--
ALTER TABLE `categories`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=20;

--
-- AUTO_INCREMENT for table `customers`
--
ALTER TABLE `customers`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `newsletter`
--
ALTER TABLE `newsletter`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `orders`
--
ALTER TABLE `orders`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `order_items`
--
ALTER TABLE `order_items`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- AUTO_INCREMENT for table `products`
--
ALTER TABLE `products`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=82;

--
-- AUTO_INCREMENT for table `product_images`
--
ALTER TABLE `product_images`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=13;

--
-- AUTO_INCREMENT for table `stock_movements`
--
ALTER TABLE `stock_movements`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=355;

--
-- AUTO_INCREMENT for table `variants`
--
ALTER TABLE `variants`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=354;

--
-- AUTO_INCREMENT for table `wishlist`
--
ALTER TABLE `wishlist`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT;
-- ---------- Latest updates (Super Admin & admin roles) ----------
ALTER TABLE `admins` ADD COLUMN `role` VARCHAR(10) DEFAULT 'admin', ADD COLUMN `permissions` TEXT NULL, ADD COLUMN `is_active` TINYINT DEFAULT 1;
DELETE FROM `admins` WHERE `username` = 'admin';
INSERT INTO `admins` (`username`, `password`, `name`, `role`, `permissions`, `is_active`, `created_at`) VALUES
('prettylayerss.com', '$2y$10$V8sQKp476sTsfmSJgceQuet1Tm6xEBJFgOPKbQZYxfPVJ6/gI529e', 'Super Admin', 'super', '', 1, '2026-11-19 11:19:16');
UPDATE `settings` SET `svalue` = '10' WHERE `skey` = 'db_version';

COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;

SET FOREIGN_KEY_CHECKS = 1;
