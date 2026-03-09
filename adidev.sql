-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Mar 08, 2026 at 09:54 AM
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
-- Database: `20260307_adidev`
--

-- --------------------------------------------------------

--
-- Table structure for table `audit_logs`
--

CREATE TABLE `audit_logs` (
  `id` int(11) NOT NULL,
  `table_name` varchar(100) NOT NULL,
  `record_id` int(11) NOT NULL,
  `action` enum('INSERT','UPDATE','DELETE') NOT NULL,
  `old_data` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`old_data`)),
  `new_data` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`new_data`)),
  `changed_by` int(11) DEFAULT NULL,
  `changed_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `ip_address` varchar(45) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `bulk_inquiries`
--

CREATE TABLE `bulk_inquiries` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `inquiry_number` varchar(50) NOT NULL,
  `products` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL CHECK (json_valid(`products`)),
  `business_name` varchar(255) DEFAULT NULL,
  `business_type` varchar(100) DEFAULT NULL,
  `expected_monthly_volume` int(11) DEFAULT NULL,
  `target_price` decimal(10,2) DEFAULT NULL,
  `status` enum('pending','contacted','quoted','converted','closed') DEFAULT 'pending',
  `assigned_to` int(11) DEFAULT NULL,
  `notes` text DEFAULT NULL,
  `last_contacted_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `bulk_pricing_tiers`
--

CREATE TABLE `bulk_pricing_tiers` (
  `id` int(11) NOT NULL,
  `product_id` int(11) DEFAULT NULL,
  `variant_id` int(11) DEFAULT NULL,
  `sub_category_id` int(11) DEFAULT NULL,
  `tier_name` varchar(100) DEFAULT NULL,
  `tier_level` int(11) DEFAULT 1,
  `min_quantity` int(11) NOT NULL,
  `max_quantity` int(11) DEFAULT NULL,
  `quantity_range` varchar(50) GENERATED ALWAYS AS (concat(`min_quantity`,'-',ifnull(`max_quantity`,'+'))) STORED,
  `price_per_piece` decimal(10,2) NOT NULL,
  `discount_percentage` decimal(5,2) DEFAULT NULL,
  `discount_amount` decimal(10,2) DEFAULT NULL,
  `savings_text` varchar(255) DEFAULT NULL,
  `requires_min_order_value` tinyint(1) DEFAULT 0,
  `min_order_value` decimal(10,2) DEFAULT NULL,
  `is_active` tinyint(1) DEFAULT 1,
  `is_featured` tinyint(1) DEFAULT 0,
  `applies_to` enum('all','registered_only','wholesale_only') DEFAULT 'all',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `valid_from` date DEFAULT NULL,
  `valid_until` date DEFAULT NULL,
  `user_group_ids` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL COMMENT 'Specific customer groups this tier applies to' CHECK (json_valid(`user_group_ids`)),
  `priority` int(11) DEFAULT 0
) ;

-- --------------------------------------------------------

--
-- Table structure for table `bulk_quotations`
--

CREATE TABLE `bulk_quotations` (
  `id` int(11) NOT NULL,
  `inquiry_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `created_by` int(11) NOT NULL,
  `quotation_number` varchar(50) NOT NULL,
  `valid_until` date NOT NULL,
  `products` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL CHECK (json_valid(`products`)),
  `subtotal` decimal(10,2) NOT NULL,
  `discount_amount` decimal(10,2) DEFAULT 0.00,
  `tax_amount` decimal(10,2) DEFAULT 0.00,
  `shipping_amount` decimal(10,2) DEFAULT 0.00,
  `grand_total` decimal(10,2) NOT NULL,
  `payment_terms` varchar(500) DEFAULT NULL,
  `delivery_terms` varchar(500) DEFAULT NULL,
  `special_conditions` text DEFAULT NULL,
  `status` enum('draft','sent','viewed','accepted','rejected','expired') DEFAULT 'draft',
  `accepted_at` timestamp NULL DEFAULT NULL,
  `converted_to_order_id` int(11) DEFAULT NULL,
  `sent_at` timestamp NULL DEFAULT NULL,
  `viewed_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `carts`
--

CREATE TABLE `carts` (
  `id` int(11) NOT NULL,
  `user_id` int(11) DEFAULT NULL,
  `session_id` varchar(255) DEFAULT NULL,
  `status` enum('active','abandoned','converted','merged') DEFAULT 'active',
  `total_items` int(11) DEFAULT 0,
  `total_quantity` int(11) DEFAULT 0,
  `subtotal` decimal(10,2) DEFAULT 0.00,
  `discount_amount` decimal(10,2) DEFAULT 0.00,
  `shipping_amount` decimal(10,2) DEFAULT 0.00,
  `tax_amount` decimal(10,2) DEFAULT 0.00,
  `grand_total` decimal(10,2) DEFAULT 0.00,
  `coupon_code` varchar(50) DEFAULT NULL,
  `coupon_discount` decimal(10,2) DEFAULT 0.00,
  `is_bulk_order` tinyint(1) DEFAULT 0,
  `bulk_discount_applied` decimal(10,2) DEFAULT 0.00,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `expired_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `cart_items`
--

CREATE TABLE `cart_items` (
  `id` int(11) NOT NULL,
  `cart_id` int(11) NOT NULL,
  `product_id` int(11) NOT NULL,
  `quantity` int(11) NOT NULL CHECK (`quantity` > 0),
  `unit_price` decimal(10,2) NOT NULL,
  `total_price` decimal(10,2) NOT NULL,
  `is_bulk_pricing_applied` tinyint(1) DEFAULT 0,
  `bulk_tier_id` int(11) DEFAULT NULL,
  `bulk_min_quantity` int(11) DEFAULT NULL,
  `regular_price` decimal(10,2) DEFAULT NULL,
  `savings_amount` decimal(10,2) DEFAULT NULL,
  `savings_percentage` decimal(5,2) DEFAULT NULL,
  `product_name` varchar(255) NOT NULL,
  `product_sku` varchar(100) DEFAULT NULL,
  `product_size` varchar(100) DEFAULT NULL,
  `product_image` varchar(500) DEFAULT NULL,
  `custom_message` text DEFAULT NULL,
  `customization_details` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`customization_details`)),
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `coupons`
--

CREATE TABLE `coupons` (
  `id` int(11) NOT NULL,
  `coupon_code` varchar(50) NOT NULL,
  `description` text DEFAULT NULL,
  `discount_type` enum('percentage','fixed') NOT NULL,
  `discount_value` decimal(10,2) NOT NULL,
  `max_discount_amount` decimal(10,2) DEFAULT NULL,
  `applies_to` enum('all','category','sub_category','product') DEFAULT 'all',
  `applies_to_ids` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`applies_to_ids`)),
  `min_order_amount` decimal(10,2) DEFAULT 0.00,
  `min_quantity` int(11) DEFAULT 1,
  `is_for_bulk_orders_only` tinyint(1) DEFAULT 0,
  `per_user_limit` int(11) DEFAULT 1,
  `total_usage_limit` int(11) DEFAULT NULL,
  `current_usage` int(11) DEFAULT 0,
  `valid_from` datetime NOT NULL,
  `valid_to` datetime NOT NULL,
  `is_active` tinyint(1) DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `coupon_usage`
--

CREATE TABLE `coupon_usage` (
  `id` int(11) NOT NULL,
  `coupon_id` int(11) NOT NULL,
  `user_id` int(11) DEFAULT NULL,
  `cart_id` int(11) DEFAULT NULL,
  `used_at` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `fraud_analysis_logs`
--

CREATE TABLE `fraud_analysis_logs` (
  `id` int(11) NOT NULL,
  `order_id` int(11) NOT NULL,
  `risk_score` int(11) DEFAULT NULL,
  `risk_level` varchar(50) DEFAULT NULL,
  `flags` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`flags`)),
  `recommended_action` varchar(50) DEFAULT NULL,
  `created_at` datetime DEFAULT NULL,
  `reviewed_at` datetime DEFAULT NULL,
  `reviewed_by` int(11) DEFAULT NULL,
  `action_taken` varchar(50) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `invoices`
--

CREATE TABLE `invoices` (
  `id` int(11) NOT NULL,
  `order_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `invoice_number` varchar(100) NOT NULL,
  `invoice_type` enum('original','proforma','credit_note','debit_note') DEFAULT 'original',
  `invoice_date` date NOT NULL,
  `due_date` date DEFAULT NULL,
  `subtotal` decimal(10,2) NOT NULL,
  `discount_total` decimal(10,2) DEFAULT 0.00,
  `tax_total` decimal(10,2) DEFAULT 0.00,
  `shipping_total` decimal(10,2) DEFAULT 0.00,
  `grand_total` decimal(10,2) NOT NULL,
  `paid_amount` decimal(10,2) DEFAULT 0.00,
  `balance_due` decimal(10,2) GENERATED ALWAYS AS (`grand_total` - `paid_amount`) STORED,
  `gst_details` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`gst_details`)),
  `place_of_supply` varchar(100) DEFAULT NULL,
  `pdf_url` varchar(500) DEFAULT NULL,
  `is_email_sent` tinyint(1) DEFAULT 0,
  `email_sent_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `main_categories`
--

CREATE TABLE `main_categories` (
  `id` int(11) NOT NULL,
  `name` varchar(100) NOT NULL,
  `slug` varchar(100) NOT NULL,
  `description` text DEFAULT NULL,
  `category_code` enum('HAND','TEX','FMCG') NOT NULL,
  `icon` varchar(255) DEFAULT NULL,
  `banner_image` varchar(500) DEFAULT NULL,
  `thumbnail_image` varchar(500) DEFAULT NULL,
  `meta_title` varchar(255) DEFAULT NULL,
  `meta_description` text DEFAULT NULL,
  `meta_keywords` text DEFAULT NULL,
  `is_active` tinyint(1) DEFAULT 1,
  `show_in_menu` tinyint(1) DEFAULT 1,
  `sort_order` int(11) DEFAULT 0,
  `default_min_bulk_quantity` int(11) DEFAULT 10,
  `default_bulk_pricing_model` enum('fixed','tiered','range') DEFAULT 'fixed',
  `total_sub_categories` int(11) DEFAULT 0,
  `total_products` int(11) DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `notifications`
--

CREATE TABLE `notifications` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `type` enum('order_confirmed','order_shipped','order_delivered','bulk_price_available','price_drop','back_in_stock','wishlist_offer','payment_received','invoice_generated') NOT NULL,
  `title` varchar(255) NOT NULL,
  `message` text NOT NULL,
  `action_url` varchar(500) DEFAULT NULL,
  `image_url` varchar(500) DEFAULT NULL,
  `data` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`data`)),
  `is_read` tinyint(1) DEFAULT 0,
  `is_archived` tinyint(1) DEFAULT 0,
  `read_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `orders`
--

CREATE TABLE `orders` (
  `id` int(11) NOT NULL,
  `order_number` varchar(50) NOT NULL,
  `user_id` int(11) NOT NULL,
  `status` enum('pending','payment_received','processing','confirmed','packed','shipped','out_for_delivery','delivered','cancelled','refunded','returned','on_hold') DEFAULT 'pending',
  `order_type` enum('regular','bulk','wholesale','sample') DEFAULT 'regular',
  `is_bulk_order` tinyint(1) DEFAULT 0,
  `customer_name` varchar(200) NOT NULL,
  `customer_email` varchar(255) NOT NULL,
  `customer_phone` varchar(20) NOT NULL,
  `customer_gst` varchar(50) DEFAULT NULL,
  `shipping_address_id` int(11) DEFAULT NULL,
  `billing_address_id` int(11) DEFAULT NULL,
  `shipping_address` text NOT NULL,
  `billing_address` text NOT NULL,
  `total_items` int(11) NOT NULL,
  `total_quantity` int(11) NOT NULL,
  `subtotal` decimal(10,2) NOT NULL,
  `discount_amount` decimal(10,2) DEFAULT 0.00,
  `bulk_discount_amount` decimal(10,2) DEFAULT 0.00,
  `coupon_discount_amount` decimal(10,2) DEFAULT 0.00,
  `shipping_amount` decimal(10,2) DEFAULT 0.00,
  `tax_amount` decimal(10,2) DEFAULT 0.00,
  `grand_total` decimal(10,2) NOT NULL,
  `amount_paid` decimal(10,2) DEFAULT 0.00,
  `amount_due` decimal(10,2) GENERATED ALWAYS AS (`grand_total` - `amount_paid`) STORED,
  `payment_method` enum('cod','bank_transfer','card','upi','wallet','razorpay','paytm') DEFAULT 'cod',
  `payment_status` enum('pending','paid','failed','refunded') DEFAULT 'pending',
  `transaction_id` varchar(255) DEFAULT NULL,
  `payment_details` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`payment_details`)),
  `invoice_number` varchar(50) DEFAULT NULL,
  `invoice_generated_at` timestamp NULL DEFAULT NULL,
  `invoice_url` varchar(500) DEFAULT NULL,
  `bulk_order_contract_signed` tinyint(1) DEFAULT 0,
  `bulk_order_contract_url` varchar(500) DEFAULT NULL,
  `expected_dispatch_date` date DEFAULT NULL,
  `order_date` timestamp NOT NULL DEFAULT current_timestamp(),
  `confirmed_at` timestamp NULL DEFAULT NULL,
  `processed_at` timestamp NULL DEFAULT NULL,
  `packed_at` timestamp NULL DEFAULT NULL,
  `shipped_at` timestamp NULL DEFAULT NULL,
  `delivered_at` timestamp NULL DEFAULT NULL,
  `cancelled_at` timestamp NULL DEFAULT NULL,
  `cancelled_reason` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `order_items`
--

CREATE TABLE `order_items` (
  `id` int(11) NOT NULL,
  `order_id` int(11) NOT NULL,
  `product_id` int(11) NOT NULL,
  `product_code` varchar(50) NOT NULL,
  `product_name` varchar(255) NOT NULL,
  `product_description` text DEFAULT NULL,
  `product_size` varchar(100) DEFAULT NULL,
  `product_image` varchar(500) DEFAULT NULL,
  `category_name` varchar(255) DEFAULT NULL,
  `sub_category_name` varchar(255) DEFAULT NULL,
  `quantity` int(11) NOT NULL,
  `unit_price` decimal(10,2) NOT NULL,
  `total_price` decimal(10,2) NOT NULL,
  `is_bulk_item` tinyint(1) DEFAULT 0,
  `bulk_tier_applied` varchar(100) DEFAULT NULL,
  `bulk_min_quantity` int(11) DEFAULT NULL,
  `regular_price` decimal(10,2) DEFAULT NULL,
  `savings_amount` decimal(10,2) DEFAULT NULL,
  `item_discount` decimal(10,2) DEFAULT 0.00,
  `bulk_discount` decimal(10,2) DEFAULT 0.00,
  `coupon_discount` decimal(10,2) DEFAULT 0.00,
  `status` enum('pending','confirmed','packed','shipped','delivered','cancelled','returned') DEFAULT 'pending',
  `is_return_requested` tinyint(1) DEFAULT 0,
  `return_reason` text DEFAULT NULL,
  `return_approved_at` timestamp NULL DEFAULT NULL,
  `return_completed_at` timestamp NULL DEFAULT NULL,
  `replacement_order_id` int(11) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `order_status_history`
--

CREATE TABLE `order_status_history` (
  `id` int(11) NOT NULL,
  `order_id` int(11) NOT NULL,
  `old_status` varchar(50) DEFAULT NULL,
  `new_status` varchar(50) NOT NULL,
  `changed_by` int(11) DEFAULT NULL,
  `changed_by_type` enum('system','admin','customer') DEFAULT 'system',
  `notes` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `order_tracking`
--

CREATE TABLE `order_tracking` (
  `id` int(11) NOT NULL,
  `order_id` int(11) NOT NULL,
  `order_item_id` int(11) DEFAULT NULL,
  `status` varchar(100) NOT NULL,
  `location` varchar(255) DEFAULT NULL,
  `description` text DEFAULT NULL,
  `courier_name` varchar(100) DEFAULT NULL,
  `tracking_number` varchar(100) DEFAULT NULL,
  `tracking_url` varchar(500) DEFAULT NULL,
  `status_date` timestamp NOT NULL DEFAULT current_timestamp(),
  `expected_delivery_date` date DEFAULT NULL,
  `actual_delivery_date` date DEFAULT NULL,
  `updated_by` int(11) DEFAULT NULL,
  `updated_by_type` enum('system','admin','customer','courier') DEFAULT 'system',
  `notes` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `payments`
--

CREATE TABLE `payments` (
  `id` int(11) NOT NULL,
  `order_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `payment_method` varchar(50) NOT NULL,
  `payment_status` enum('pending','success','failed','refunded') DEFAULT 'pending',
  `amount` decimal(10,2) NOT NULL,
  `refunded_amount` decimal(10,2) DEFAULT 0.00,
  `transaction_fee` decimal(10,2) DEFAULT 0.00,
  `net_amount` decimal(10,2) GENERATED ALWAYS AS (`amount` - `refunded_amount` - `transaction_fee`) STORED,
  `transaction_id` varchar(255) DEFAULT NULL,
  `gateway_transaction_id` varchar(255) DEFAULT NULL,
  `bank_transaction_id` varchar(255) DEFAULT NULL,
  `payment_reference` varchar(255) DEFAULT NULL,
  `gateway_response` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`gateway_response`)),
  `gateway_status_code` varchar(50) DEFAULT NULL,
  `refund_reason` text DEFAULT NULL,
  `refund_initiated_by` int(11) DEFAULT NULL,
  `refund_processed_at` timestamp NULL DEFAULT NULL,
  `payment_date` timestamp NOT NULL DEFAULT current_timestamp(),
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `pincode_inquiries`
--

CREATE TABLE `pincode_inquiries` (
  `id` int(11) NOT NULL,
  `pincode` varchar(10) NOT NULL,
  `product_id` int(11) DEFAULT NULL,
  `ip_address` varchar(45) DEFAULT NULL,
  `user_agent` text DEFAULT NULL,
  `created_at` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `price_history`
--

CREATE TABLE `price_history` (
  `id` int(11) NOT NULL,
  `product_id` int(11) NOT NULL,
  `variant_id` int(11) DEFAULT NULL,
  `old_price` decimal(10,2) DEFAULT NULL,
  `new_price` decimal(10,2) NOT NULL,
  `price_type` enum('retail','wholesale','bulk') DEFAULT 'retail',
  `changed_by` int(11) DEFAULT NULL,
  `reason` varchar(255) DEFAULT NULL,
  `effective_from` timestamp NOT NULL DEFAULT current_timestamp(),
  `effective_to` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `products`
--

CREATE TABLE `products` (
  `id` int(11) NOT NULL,
  `sub_category_id` int(11) NOT NULL,
  `product_code` varchar(50) NOT NULL,
  `name` varchar(255) NOT NULL,
  `slug` varchar(255) NOT NULL,
  `short_description` varchar(500) DEFAULT NULL,
  `description` text DEFAULT NULL,
  `product_type` enum('simple','variable','bundle') DEFAULT 'simple',
  `has_variants` tinyint(1) DEFAULT 0,
  `size` varchar(100) DEFAULT NULL,
  `weight` decimal(10,2) DEFAULT NULL,
  `dimensions` varchar(100) DEFAULT NULL,
  `material` varchar(255) DEFAULT NULL,
  `color` varchar(100) DEFAULT NULL,
  `base_retail_price` decimal(10,2) DEFAULT NULL,
  `base_wholesale_price` decimal(10,2) DEFAULT NULL,
  `cost_price` decimal(10,2) DEFAULT NULL,
  `mrp` decimal(10,2) DEFAULT NULL,
  `selling_mode` enum('both','single_only','bulk_only') DEFAULT 'both',
  `min_order_quantity` int(11) DEFAULT 1,
  `max_order_quantity` int(11) DEFAULT NULL,
  `bulk_min_quantity` int(11) DEFAULT 10,
  `is_bulk_only` tinyint(1) DEFAULT 0,
  `stock_quantity` int(11) DEFAULT 0,
  `low_stock_threshold` int(11) DEFAULT 5,
  `track_inventory` tinyint(1) DEFAULT 1,
  `allow_backorder` tinyint(1) DEFAULT 0,
  `shipping_class` varchar(100) DEFAULT NULL,
  `shipping_weight` decimal(10,2) DEFAULT NULL,
  `free_shipping` tinyint(1) DEFAULT 0,
  `tax_class` varchar(100) DEFAULT NULL,
  `gst_rate` decimal(5,2) DEFAULT NULL,
  `main_image` varchar(500) DEFAULT NULL,
  `hover_image` varchar(500) DEFAULT NULL,
  `video_url` varchar(500) DEFAULT NULL,
  `meta_title` varchar(255) DEFAULT NULL,
  `meta_description` text DEFAULT NULL,
  `meta_keywords` text DEFAULT NULL,
  `is_active` tinyint(1) DEFAULT 1,
  `is_featured` tinyint(1) DEFAULT 0,
  `is_new` tinyint(1) DEFAULT 0,
  `is_on_sale` tinyint(1) DEFAULT 0,
  `is_trending` tinyint(1) DEFAULT 0,
  `is_bulk_item` tinyint(1) DEFAULT 0,
  `bulk_pricing_model` enum('fixed','tiered','range') DEFAULT 'fixed',
  `has_tiered_pricing` tinyint(1) DEFAULT 0,
  `total_sold` int(11) DEFAULT 0,
  `total_revenue` decimal(10,2) DEFAULT 0.00,
  `average_rating` decimal(3,2) DEFAULT 0.00,
  `review_count` int(11) DEFAULT 0,
  `view_count` int(11) DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `canonical_url` varchar(500) DEFAULT NULL,
  `schema_markup` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`schema_markup`)),
  `search_keywords` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `product_images`
--

CREATE TABLE `product_images` (
  `id` int(11) NOT NULL,
  `product_id` int(11) NOT NULL,
  `variant_id` int(11) DEFAULT NULL,
  `image_url` varchar(500) NOT NULL,
  `thumbnail_url` varchar(500) DEFAULT NULL,
  `medium_url` varchar(500) DEFAULT NULL,
  `large_url` varchar(500) DEFAULT NULL,
  `image_type` enum('main','gallery','hover','zoom','variant') DEFAULT 'gallery',
  `alt_text` varchar(255) DEFAULT NULL,
  `title` varchar(255) DEFAULT NULL,
  `caption` text DEFAULT NULL,
  `file_name` varchar(255) DEFAULT NULL,
  `file_size` int(11) DEFAULT NULL,
  `mime_type` varchar(100) DEFAULT NULL,
  `width` int(11) DEFAULT NULL,
  `height` int(11) DEFAULT NULL,
  `sort_order` int(11) DEFAULT 0,
  `is_primary` tinyint(1) DEFAULT 0,
  `is_active` tinyint(1) DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `product_reviews`
--

CREATE TABLE `product_reviews` (
  `id` int(11) NOT NULL,
  `product_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `order_id` int(11) DEFAULT NULL,
  `order_item_id` int(11) DEFAULT NULL,
  `rating` int(11) NOT NULL CHECK (`rating` >= 1 and `rating` <= 5),
  `title` varchar(255) DEFAULT NULL,
  `review` text DEFAULT NULL,
  `pros` text DEFAULT NULL,
  `cons` text DEFAULT NULL,
  `images` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`images`)),
  `video_url` varchar(500) DEFAULT NULL,
  `is_verified_purchase` tinyint(1) DEFAULT 0,
  `is_anonymous` tinyint(1) DEFAULT 0,
  `status` enum('pending','approved','rejected') DEFAULT 'pending',
  `helpful_count` int(11) DEFAULT 0,
  `not_helpful_count` int(11) DEFAULT 0,
  `admin_response` text DEFAULT NULL,
  `admin_responded_at` timestamp NULL DEFAULT NULL,
  `admin_id` int(11) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `product_shipping_restrictions`
--

CREATE TABLE `product_shipping_restrictions` (
  `id` int(11) NOT NULL,
  `product_id` int(11) NOT NULL,
  `pincode` varchar(10) NOT NULL,
  `is_serviceable` tinyint(1) DEFAULT 1,
  `delivery_days` int(11) DEFAULT NULL,
  `additional_charge` decimal(10,2) DEFAULT 0.00,
  `created_at` datetime DEFAULT NULL,
  `updated_at` datetime DEFAULT NULL ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `product_variants`
--

CREATE TABLE `product_variants` (
  `id` int(11) NOT NULL,
  `product_id` int(11) NOT NULL,
  `sku` varchar(100) NOT NULL,
  `variant_code` varchar(50) DEFAULT NULL,
  `attributes` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL CHECK (json_valid(`attributes`)),
  `size` varchar(100) DEFAULT NULL,
  `color` varchar(100) DEFAULT NULL,
  `material` varchar(255) DEFAULT NULL,
  `pattern` varchar(100) DEFAULT NULL,
  `style` varchar(100) DEFAULT NULL,
  `image` varchar(500) DEFAULT NULL,
  `gallery_images` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`gallery_images`)),
  `retail_price` decimal(10,2) NOT NULL,
  `wholesale_price` decimal(10,2) DEFAULT NULL,
  `cost_price` decimal(10,2) DEFAULT NULL,
  `mrp` decimal(10,2) DEFAULT NULL,
  `price_adjustment` decimal(10,2) DEFAULT 0.00,
  `is_bulk_only` tinyint(1) DEFAULT 0,
  `bulk_min_quantity` int(11) DEFAULT 10,
  `stock_quantity` int(11) DEFAULT 0,
  `low_stock_threshold` int(11) DEFAULT 5,
  `track_inventory` tinyint(1) DEFAULT 1,
  `weight` decimal(10,2) DEFAULT NULL,
  `dimensions` varchar(100) DEFAULT NULL,
  `is_active` tinyint(1) DEFAULT 1,
  `is_default` tinyint(1) DEFAULT 0,
  `total_sold` int(11) DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `return_requests`
--

CREATE TABLE `return_requests` (
  `id` int(11) NOT NULL,
  `order_id` int(11) NOT NULL,
  `order_item_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `return_reason` varchar(255) NOT NULL,
  `return_quantity` int(11) NOT NULL,
  `return_amount` decimal(10,2) DEFAULT NULL,
  `request_status` enum('pending','approved','rejected','completed') DEFAULT 'pending',
  `pickup_address_id` int(11) DEFAULT NULL,
  `pickup_scheduled` date DEFAULT NULL,
  `pickup_status` varchar(50) DEFAULT NULL,
  `refund_transaction_id` varchar(255) DEFAULT NULL,
  `notes` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `review_helpfulness`
--

CREATE TABLE `review_helpfulness` (
  `id` int(11) NOT NULL,
  `review_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `is_helpful` tinyint(1) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `serviceable_pincodes`
--

CREATE TABLE `serviceable_pincodes` (
  `id` int(11) NOT NULL,
  `pincode` varchar(10) NOT NULL,
  `city` varchar(100) DEFAULT NULL,
  `state` varchar(100) DEFAULT NULL,
  `region` varchar(100) DEFAULT NULL,
  `delivery_days` int(11) DEFAULT 5,
  `cod_available` tinyint(1) DEFAULT 1,
  `is_active` tinyint(1) DEFAULT 1,
  `created_at` datetime DEFAULT NULL,
  `updated_at` datetime DEFAULT NULL ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `sub_categories`
--

CREATE TABLE `sub_categories` (
  `id` int(11) NOT NULL,
  `main_category_id` int(11) NOT NULL,
  `name` varchar(255) NOT NULL,
  `slug` varchar(255) NOT NULL,
  `description` text DEFAULT NULL,
  `category_code` varchar(10) DEFAULT NULL,
  `full_category_code` varchar(20) DEFAULT NULL,
  `icon` varchar(255) DEFAULT NULL,
  `image` varchar(500) DEFAULT NULL,
  `banner_image` varchar(500) DEFAULT NULL,
  `parent_id` int(11) DEFAULT NULL,
  `level` int(11) DEFAULT 1,
  `path` varchar(500) DEFAULT NULL,
  `min_order_quantity` int(11) DEFAULT 1,
  `allows_single_purchase` tinyint(1) DEFAULT 1,
  `allows_bulk_purchase` tinyint(1) DEFAULT 1,
  `default_bulk_min_quantity` int(11) DEFAULT 10,
  `bulk_pricing_model` enum('fixed','tiered','range') DEFAULT 'fixed',
  `bulk_tier_description` varchar(255) DEFAULT NULL,
  `meta_title` varchar(255) DEFAULT NULL,
  `meta_description` text DEFAULT NULL,
  `meta_keywords` text DEFAULT NULL,
  `is_active` tinyint(1) DEFAULT 1,
  `show_in_menu` tinyint(1) DEFAULT 1,
  `is_featured` tinyint(1) DEFAULT 0,
  `sort_order` int(11) DEFAULT 0,
  `total_products` int(11) DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `support_tickets`
--

CREATE TABLE `support_tickets` (
  `id` int(11) NOT NULL,
  `ticket_number` varchar(50) NOT NULL,
  `user_id` int(11) DEFAULT NULL,
  `guest_email` varchar(255) DEFAULT NULL,
  `guest_name` varchar(255) DEFAULT NULL,
  `order_id` int(11) DEFAULT NULL,
  `subject` varchar(500) NOT NULL,
  `category` enum('order_issue','payment_problem','product_query','shipping_delivery','return_refund','technical_support','bulk_inquiry','account_issue','feedback','other') DEFAULT 'other',
  `priority` enum('low','medium','high','urgent') DEFAULT 'medium',
  `status` enum('open','in_progress','awaiting_reply','resolved','closed') DEFAULT 'open',
  `created_at` datetime DEFAULT NULL,
  `updated_at` datetime DEFAULT NULL ON UPDATE current_timestamp(),
  `closed_at` datetime DEFAULT NULL,
  `first_response_at` datetime DEFAULT NULL,
  `resolved_at` datetime DEFAULT NULL,
  `assigned_to` int(11) DEFAULT NULL,
  `assigned_at` datetime DEFAULT NULL,
  `ip_address` varchar(45) DEFAULT NULL,
  `user_agent` text DEFAULT NULL,
  `source` enum('website','mobile_app','email','chatbot','phone') DEFAULT 'website',
  `language` varchar(10) DEFAULT 'en',
  `sla_deadline` datetime DEFAULT NULL,
  `sla_breached` tinyint(1) DEFAULT 0,
  `rating` tinyint(1) DEFAULT NULL COMMENT '1-5 stars',
  `feedback` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` int(11) NOT NULL,
  `user_type` enum('customer','wholesaler','retailer','admin') DEFAULT 'customer',
  `first_name` varchar(100) NOT NULL,
  `last_name` varchar(100) NOT NULL,
  `display_name` varchar(200) DEFAULT NULL,
  `email` varchar(255) NOT NULL,
  `phone` varchar(20) NOT NULL,
  `alternate_phone` varchar(20) DEFAULT NULL,
  `password_hash` varchar(255) NOT NULL,
  `remember_token` varchar(100) DEFAULT NULL,
  `token_expiry` varchar(255) DEFAULT NULL,
  `email_verified_at` timestamp NULL DEFAULT NULL,
  `phone_verified_at` timestamp NULL DEFAULT NULL,
  `business_name` varchar(255) DEFAULT NULL,
  `gst_number` varchar(50) DEFAULT NULL,
  `pan_number` varchar(50) DEFAULT NULL,
  `business_type` enum('individual','partnership','pvt_ltd','llp','other') DEFAULT NULL,
  `year_of_establishment` year(4) DEFAULT NULL,
  `is_active` tinyint(1) DEFAULT 1,
  `is_blocked` tinyint(1) DEFAULT 0,
  `account_balance` decimal(10,2) DEFAULT 0.00,
  `prefers_bulk_pricing` tinyint(1) DEFAULT 0,
  `default_bulk_min_quantity` int(11) DEFAULT 10,
  `last_login_at` timestamp NULL DEFAULT NULL,
  `last_login_ip` varchar(45) DEFAULT NULL,
  `registered_from_ip` varchar(45) DEFAULT NULL,
  `referral_code` varchar(50) DEFAULT NULL,
  `referred_by` int(11) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `user_addresses`
--

CREATE TABLE `user_addresses` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `address_type` enum('home','work','business','other') DEFAULT 'home',
  `is_default` tinyint(1) DEFAULT 0,
  `is_billing_address` tinyint(1) DEFAULT 0,
  `is_shipping_address` tinyint(1) DEFAULT 1,
  `name` varchar(200) DEFAULT NULL,
  `phone` varchar(20) DEFAULT NULL,
  `alternate_phone` varchar(20) DEFAULT NULL,
  `address_line1` varchar(255) NOT NULL,
  `address_line2` varchar(255) DEFAULT NULL,
  `landmark` varchar(255) DEFAULT NULL,
  `city` varchar(100) NOT NULL,
  `state` varchar(100) NOT NULL,
  `pincode` varchar(20) NOT NULL,
  `country` varchar(100) DEFAULT 'India',
  `company_name` varchar(255) DEFAULT NULL,
  `gst_number` varchar(50) DEFAULT NULL,
  `delivery_instructions` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `user_groups`
--

CREATE TABLE `user_groups` (
  `id` int(11) NOT NULL,
  `group_name` varchar(100) NOT NULL,
  `group_type` enum('regular','wholesale','retailer','distributor') DEFAULT 'regular',
  `min_purchase_amount` decimal(10,2) DEFAULT 0.00,
  `discount_percentage` decimal(5,2) DEFAULT 0.00,
  `requires_approval` tinyint(1) DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `user_group_members`
--

CREATE TABLE `user_group_members` (
  `user_id` int(11) NOT NULL,
  `group_id` int(11) NOT NULL,
  `approved_by` int(11) DEFAULT NULL,
  `approved_at` timestamp NULL DEFAULT NULL,
  `expires_at` date DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `user_wishlist`
--

CREATE TABLE `user_wishlist` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `product_id` int(11) NOT NULL,
  `quantity_wanted` int(11) DEFAULT 1,
  `priority` enum('high','medium','low') DEFAULT 'medium',
  `notes` text DEFAULT NULL,
  `notify_when_in_stock` tinyint(1) DEFAULT 0,
  `notify_when_price_drops` tinyint(1) DEFAULT 0,
  `target_price` decimal(10,2) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `shared_with` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL COMMENT 'Share wishlist with other users' CHECK (json_valid(`shared_with`)),
  `is_public` tinyint(1) DEFAULT 0,
  `expiry_date` date DEFAULT NULL COMMENT 'For event-based wishlists'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Indexes for dumped tables
--

--
-- Indexes for table `audit_logs`
--
ALTER TABLE `audit_logs`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_audit_record` (`table_name`,`record_id`),
  ADD KEY `idx_audit_time` (`changed_at`);

--
-- Indexes for table `bulk_inquiries`
--
ALTER TABLE `bulk_inquiries`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `inquiry_number` (`inquiry_number`),
  ADD KEY `assigned_to` (`assigned_to`),
  ADD KEY `idx_user` (`user_id`),
  ADD KEY `idx_status` (`status`);

--
-- Indexes for table `bulk_pricing_tiers`
--
ALTER TABLE `bulk_pricing_tiers`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `unique_product_tier_range` (`product_id`,`min_quantity`),
  ADD UNIQUE KEY `unique_variant_tier_range` (`variant_id`,`min_quantity`),
  ADD KEY `idx_product` (`product_id`),
  ADD KEY `idx_variant` (`variant_id`),
  ADD KEY `idx_sub_category` (`sub_category_id`),
  ADD KEY `idx_min_quantity` (`min_quantity`),
  ADD KEY `idx_is_active` (`is_active`);

--
-- Indexes for table `bulk_quotations`
--
ALTER TABLE `bulk_quotations`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `quotation_number` (`quotation_number`),
  ADD KEY `created_by` (`created_by`),
  ADD KEY `converted_to_order_id` (`converted_to_order_id`),
  ADD KEY `idx_inquiry` (`inquiry_id`),
  ADD KEY `idx_user` (`user_id`),
  ADD KEY `idx_status` (`status`);

--
-- Indexes for table `carts`
--
ALTER TABLE `carts`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_user` (`user_id`),
  ADD KEY `idx_session` (`session_id`),
  ADD KEY `idx_status` (`status`);

--
-- Indexes for table `cart_items`
--
ALTER TABLE `cart_items`
  ADD PRIMARY KEY (`id`),
  ADD KEY `bulk_tier_id` (`bulk_tier_id`),
  ADD KEY `idx_cart` (`cart_id`),
  ADD KEY `idx_product` (`product_id`);

--
-- Indexes for table `coupons`
--
ALTER TABLE `coupons`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `coupon_code` (`coupon_code`),
  ADD KEY `idx_coupon_code` (`coupon_code`),
  ADD KEY `idx_validity` (`valid_from`,`valid_to`);

--
-- Indexes for table `coupon_usage`
--
ALTER TABLE `coupon_usage`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_coupon` (`coupon_id`),
  ADD KEY `idx_user` (`user_id`),
  ADD KEY `idx_used_at` (`used_at`);

--
-- Indexes for table `fraud_analysis_logs`
--
ALTER TABLE `fraud_analysis_logs`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_order` (`order_id`),
  ADD KEY `idx_risk` (`risk_level`);

--
-- Indexes for table `invoices`
--
ALTER TABLE `invoices`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `invoice_number` (`invoice_number`),
  ADD KEY `user_id` (`user_id`),
  ADD KEY `idx_order` (`order_id`),
  ADD KEY `idx_invoice_number` (`invoice_number`);

--
-- Indexes for table `main_categories`
--
ALTER TABLE `main_categories`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `slug` (`slug`),
  ADD KEY `idx_slug` (`slug`),
  ADD KEY `idx_is_active` (`is_active`),
  ADD KEY `idx_category_code` (`category_code`);
ALTER TABLE `main_categories` ADD FULLTEXT KEY `idx_search` (`name`,`description`);

--
-- Indexes for table `notifications`
--
ALTER TABLE `notifications`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_user` (`user_id`),
  ADD KEY `idx_user_read` (`user_id`,`is_read`);

--
-- Indexes for table `orders`
--
ALTER TABLE `orders`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `order_number` (`order_number`),
  ADD KEY `idx_order_number` (`order_number`),
  ADD KEY `idx_user` (`user_id`),
  ADD KEY `idx_status` (`status`),
  ADD KEY `idx_order_date` (`order_date`),
  ADD KEY `idx_payment_status` (`payment_status`);

--
-- Indexes for table `order_items`
--
ALTER TABLE `order_items`
  ADD PRIMARY KEY (`id`),
  ADD KEY `replacement_order_id` (`replacement_order_id`),
  ADD KEY `idx_order` (`order_id`),
  ADD KEY `idx_product` (`product_id`),
  ADD KEY `idx_status` (`status`);

--
-- Indexes for table `order_status_history`
--
ALTER TABLE `order_status_history`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_order_history` (`order_id`,`created_at`);

--
-- Indexes for table `order_tracking`
--
ALTER TABLE `order_tracking`
  ADD PRIMARY KEY (`id`),
  ADD KEY `order_item_id` (`order_item_id`),
  ADD KEY `updated_by` (`updated_by`),
  ADD KEY `idx_order` (`order_id`),
  ADD KEY `idx_tracking_number` (`tracking_number`);

--
-- Indexes for table `payments`
--
ALTER TABLE `payments`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`),
  ADD KEY `refund_initiated_by` (`refund_initiated_by`),
  ADD KEY `idx_order` (`order_id`),
  ADD KEY `idx_status` (`payment_status`);

--
-- Indexes for table `pincode_inquiries`
--
ALTER TABLE `pincode_inquiries`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_pincode` (`pincode`),
  ADD KEY `idx_created` (`created_at`);

--
-- Indexes for table `price_history`
--
ALTER TABLE `price_history`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_price_product` (`product_id`,`effective_from`);

--
-- Indexes for table `products`
--
ALTER TABLE `products`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `product_code` (`product_code`),
  ADD UNIQUE KEY `slug` (`slug`),
  ADD KEY `idx_sub_category` (`sub_category_id`),
  ADD KEY `idx_product_code` (`product_code`),
  ADD KEY `idx_slug` (`slug`),
  ADD KEY `idx_is_active` (`is_active`),
  ADD KEY `idx_featured` (`is_featured`),
  ADD KEY `idx_product_type` (`product_type`),
  ADD KEY `idx_selling_mode` (`selling_mode`);
ALTER TABLE `products` ADD FULLTEXT KEY `idx_search` (`name`,`short_description`,`description`);

--
-- Indexes for table `product_images`
--
ALTER TABLE `product_images`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_product` (`product_id`),
  ADD KEY `idx_variant` (`variant_id`),
  ADD KEY `idx_is_primary` (`is_primary`),
  ADD KEY `idx_sort_order` (`sort_order`);

--
-- Indexes for table `product_reviews`
--
ALTER TABLE `product_reviews`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `unique_user_product_review` (`user_id`,`product_id`),
  ADD KEY `order_id` (`order_id`),
  ADD KEY `order_item_id` (`order_item_id`),
  ADD KEY `admin_id` (`admin_id`),
  ADD KEY `idx_product` (`product_id`),
  ADD KEY `idx_status` (`status`),
  ADD KEY `idx_rating` (`rating`);

--
-- Indexes for table `product_shipping_restrictions`
--
ALTER TABLE `product_shipping_restrictions`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `product_pincode` (`product_id`,`pincode`);

--
-- Indexes for table `product_variants`
--
ALTER TABLE `product_variants`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `sku` (`sku`),
  ADD UNIQUE KEY `unique_product_variant` (`product_id`,`attributes`(255)),
  ADD KEY `idx_product` (`product_id`),
  ADD KEY `idx_sku` (`sku`),
  ADD KEY `idx_is_active` (`is_active`),
  ADD KEY `idx_is_default` (`is_default`),
  ADD KEY `idx_size` (`size`),
  ADD KEY `idx_color` (`color`);

--
-- Indexes for table `return_requests`
--
ALTER TABLE `return_requests`
  ADD PRIMARY KEY (`id`),
  ADD KEY `order_id` (`order_id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `review_helpfulness`
--
ALTER TABLE `review_helpfulness`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `unique_user_review_vote` (`user_id`,`review_id`),
  ADD KEY `review_id` (`review_id`);

--
-- Indexes for table `serviceable_pincodes`
--
ALTER TABLE `serviceable_pincodes`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `pincode` (`pincode`),
  ADD KEY `idx_pincode_active` (`pincode`,`is_active`);

--
-- Indexes for table `sub_categories`
--
ALTER TABLE `sub_categories`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `slug` (`slug`),
  ADD KEY `idx_main_category` (`main_category_id`),
  ADD KEY `idx_slug` (`slug`),
  ADD KEY `idx_parent` (`parent_id`),
  ADD KEY `idx_is_active` (`is_active`),
  ADD KEY `idx_level` (`level`);
ALTER TABLE `sub_categories` ADD FULLTEXT KEY `idx_search` (`name`,`description`);

--
-- Indexes for table `support_tickets`
--
ALTER TABLE `support_tickets`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_user` (`user_id`),
  ADD KEY `idx_order` (`order_id`),
  ADD KEY `idx_status` (`status`),
  ADD KEY `idx_priority` (`priority`),
  ADD KEY `idx_assigned` (`assigned_to`),
  ADD KEY `idx_created` (`created_at`),
  ADD KEY `idx_category` (`category`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `email` (`email`),
  ADD UNIQUE KEY `phone` (`phone`),
  ADD KEY `referred_by` (`referred_by`),
  ADD KEY `idx_email` (`email`),
  ADD KEY `idx_phone` (`phone`),
  ADD KEY `idx_user_type` (`user_type`),
  ADD KEY `idx_business_type` (`business_type`);

--
-- Indexes for table `user_addresses`
--
ALTER TABLE `user_addresses`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_user_default` (`user_id`,`is_default`),
  ADD KEY `idx_pincode` (`pincode`);

--
-- Indexes for table `user_groups`
--
ALTER TABLE `user_groups`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `user_group_members`
--
ALTER TABLE `user_group_members`
  ADD PRIMARY KEY (`user_id`,`group_id`),
  ADD KEY `group_id` (`group_id`);

--
-- Indexes for table `user_wishlist`
--
ALTER TABLE `user_wishlist`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `unique_user_product` (`user_id`,`product_id`),
  ADD KEY `idx_user` (`user_id`),
  ADD KEY `idx_product` (`product_id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `audit_logs`
--
ALTER TABLE `audit_logs`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `bulk_inquiries`
--
ALTER TABLE `bulk_inquiries`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `bulk_pricing_tiers`
--
ALTER TABLE `bulk_pricing_tiers`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `bulk_quotations`
--
ALTER TABLE `bulk_quotations`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `carts`
--
ALTER TABLE `carts`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `cart_items`
--
ALTER TABLE `cart_items`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `coupons`
--
ALTER TABLE `coupons`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `coupon_usage`
--
ALTER TABLE `coupon_usage`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `fraud_analysis_logs`
--
ALTER TABLE `fraud_analysis_logs`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `invoices`
--
ALTER TABLE `invoices`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `main_categories`
--
ALTER TABLE `main_categories`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `notifications`
--
ALTER TABLE `notifications`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `orders`
--
ALTER TABLE `orders`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `order_items`
--
ALTER TABLE `order_items`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `order_status_history`
--
ALTER TABLE `order_status_history`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `order_tracking`
--
ALTER TABLE `order_tracking`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `payments`
--
ALTER TABLE `payments`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `pincode_inquiries`
--
ALTER TABLE `pincode_inquiries`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `price_history`
--
ALTER TABLE `price_history`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `products`
--
ALTER TABLE `products`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `product_images`
--
ALTER TABLE `product_images`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `product_reviews`
--
ALTER TABLE `product_reviews`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `product_shipping_restrictions`
--
ALTER TABLE `product_shipping_restrictions`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `product_variants`
--
ALTER TABLE `product_variants`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `return_requests`
--
ALTER TABLE `return_requests`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `review_helpfulness`
--
ALTER TABLE `review_helpfulness`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `serviceable_pincodes`
--
ALTER TABLE `serviceable_pincodes`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `sub_categories`
--
ALTER TABLE `sub_categories`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `support_tickets`
--
ALTER TABLE `support_tickets`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `user_addresses`
--
ALTER TABLE `user_addresses`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `user_groups`
--
ALTER TABLE `user_groups`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `user_wishlist`
--
ALTER TABLE `user_wishlist`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `bulk_inquiries`
--
ALTER TABLE `bulk_inquiries`
  ADD CONSTRAINT `bulk_inquiries_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `bulk_inquiries_ibfk_2` FOREIGN KEY (`assigned_to`) REFERENCES `users` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `bulk_pricing_tiers`
--
ALTER TABLE `bulk_pricing_tiers`
  ADD CONSTRAINT `bulk_pricing_tiers_ibfk_1` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `bulk_pricing_tiers_ibfk_2` FOREIGN KEY (`variant_id`) REFERENCES `product_variants` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `bulk_pricing_tiers_ibfk_3` FOREIGN KEY (`sub_category_id`) REFERENCES `sub_categories` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `bulk_quotations`
--
ALTER TABLE `bulk_quotations`
  ADD CONSTRAINT `bulk_quotations_ibfk_1` FOREIGN KEY (`inquiry_id`) REFERENCES `bulk_inquiries` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `bulk_quotations_ibfk_2` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `bulk_quotations_ibfk_3` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `bulk_quotations_ibfk_4` FOREIGN KEY (`converted_to_order_id`) REFERENCES `orders` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `carts`
--
ALTER TABLE `carts`
  ADD CONSTRAINT `carts_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `cart_items`
--
ALTER TABLE `cart_items`
  ADD CONSTRAINT `cart_items_ibfk_1` FOREIGN KEY (`cart_id`) REFERENCES `carts` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `cart_items_ibfk_2` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `cart_items_ibfk_3` FOREIGN KEY (`bulk_tier_id`) REFERENCES `bulk_pricing_tiers` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `invoices`
--
ALTER TABLE `invoices`
  ADD CONSTRAINT `invoices_ibfk_1` FOREIGN KEY (`order_id`) REFERENCES `orders` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `invoices_ibfk_2` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `notifications`
--
ALTER TABLE `notifications`
  ADD CONSTRAINT `notifications_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `orders`
--
ALTER TABLE `orders`
  ADD CONSTRAINT `orders_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `order_items`
--
ALTER TABLE `order_items`
  ADD CONSTRAINT `order_items_ibfk_1` FOREIGN KEY (`order_id`) REFERENCES `orders` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `order_items_ibfk_2` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `order_items_ibfk_3` FOREIGN KEY (`replacement_order_id`) REFERENCES `orders` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `order_status_history`
--
ALTER TABLE `order_status_history`
  ADD CONSTRAINT `order_status_history_ibfk_1` FOREIGN KEY (`order_id`) REFERENCES `orders` (`id`);

--
-- Constraints for table `order_tracking`
--
ALTER TABLE `order_tracking`
  ADD CONSTRAINT `order_tracking_ibfk_1` FOREIGN KEY (`order_id`) REFERENCES `orders` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `order_tracking_ibfk_2` FOREIGN KEY (`order_item_id`) REFERENCES `order_items` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `order_tracking_ibfk_3` FOREIGN KEY (`updated_by`) REFERENCES `users` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `payments`
--
ALTER TABLE `payments`
  ADD CONSTRAINT `payments_ibfk_1` FOREIGN KEY (`order_id`) REFERENCES `orders` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `payments_ibfk_2` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `payments_ibfk_3` FOREIGN KEY (`refund_initiated_by`) REFERENCES `users` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `products`
--
ALTER TABLE `products`
  ADD CONSTRAINT `products_ibfk_1` FOREIGN KEY (`sub_category_id`) REFERENCES `sub_categories` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `product_images`
--
ALTER TABLE `product_images`
  ADD CONSTRAINT `product_images_ibfk_1` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `product_images_ibfk_2` FOREIGN KEY (`variant_id`) REFERENCES `product_variants` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `product_reviews`
--
ALTER TABLE `product_reviews`
  ADD CONSTRAINT `product_reviews_ibfk_1` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `product_reviews_ibfk_2` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `product_reviews_ibfk_3` FOREIGN KEY (`order_id`) REFERENCES `orders` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `product_reviews_ibfk_4` FOREIGN KEY (`order_item_id`) REFERENCES `order_items` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `product_reviews_ibfk_5` FOREIGN KEY (`admin_id`) REFERENCES `users` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `product_variants`
--
ALTER TABLE `product_variants`
  ADD CONSTRAINT `product_variants_ibfk_1` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `return_requests`
--
ALTER TABLE `return_requests`
  ADD CONSTRAINT `return_requests_ibfk_1` FOREIGN KEY (`order_id`) REFERENCES `orders` (`id`),
  ADD CONSTRAINT `return_requests_ibfk_2` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`);

--
-- Constraints for table `review_helpfulness`
--
ALTER TABLE `review_helpfulness`
  ADD CONSTRAINT `review_helpfulness_ibfk_1` FOREIGN KEY (`review_id`) REFERENCES `product_reviews` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `review_helpfulness_ibfk_2` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `sub_categories`
--
ALTER TABLE `sub_categories`
  ADD CONSTRAINT `sub_categories_ibfk_1` FOREIGN KEY (`main_category_id`) REFERENCES `main_categories` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `sub_categories_ibfk_2` FOREIGN KEY (`parent_id`) REFERENCES `sub_categories` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `support_tickets`
--
ALTER TABLE `support_tickets`
  ADD CONSTRAINT `fk_tickets_order` FOREIGN KEY (`order_id`) REFERENCES `orders` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `fk_tickets_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `users`
--
ALTER TABLE `users`
  ADD CONSTRAINT `users_ibfk_1` FOREIGN KEY (`referred_by`) REFERENCES `users` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `user_addresses`
--
ALTER TABLE `user_addresses`
  ADD CONSTRAINT `user_addresses_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `user_group_members`
--
ALTER TABLE `user_group_members`
  ADD CONSTRAINT `user_group_members_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`),
  ADD CONSTRAINT `user_group_members_ibfk_2` FOREIGN KEY (`group_id`) REFERENCES `user_groups` (`id`);

--
-- Constraints for table `user_wishlist`
--
ALTER TABLE `user_wishlist`
  ADD CONSTRAINT `user_wishlist_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `user_wishlist_ibfk_2` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
