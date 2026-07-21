-- phpMyAdmin SQL Dump
-- version 5.2.2
-- https://www.phpmyadmin.net/
--
-- Máy chủ: localhost:3306
-- Thời gian đã tạo: Th7 21, 2026 lúc 07:36 AM
-- Phiên bản máy phục vụ: 8.0.30
-- Phiên bản PHP: 8.3.8

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Cơ sở dữ liệu: `isocalmarket`
--

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `cache`
--

CREATE TABLE `cache` (
  `key` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `value` mediumtext COLLATE utf8mb4_unicode_ci NOT NULL,
  `expiration` bigint NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Đang đổ dữ liệu cho bảng `cache`
--

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `cache_locks`
--

CREATE TABLE `cache_locks` (
  `key` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `owner` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `expiration` bigint NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `categories`
--

CREATE TABLE `categories` (
  `id` char(36) COLLATE utf8mb4_unicode_ci NOT NULL,
  `supplier_id` char(36) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `external_id` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `slug` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `description` text COLLATE utf8mb4_unicode_ci,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `normalized_name` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Đang đổ dữ liệu cho bảng `categories`
--

INSERT INTO `categories` (`id`, `supplier_id`, `external_id`, `name`, `slug`, `description`, `created_at`, `updated_at`, `normalized_name`) VALUES
('019f82af-5c43-710f-85e6-ff318e943418', '019f829a-f708-7093-890d-4950e6ce2e66', '1', 'BM50$', 'ads7979-1-bm50', NULL, '2026-07-20 20:19:18', '2026-07-20 20:19:18', 'bm50$'),
('019f82af-5c5e-7396-95a3-825ffab2f738', '019f829a-f708-7093-890d-4950e6ce2e66', '13', 'BM3 Limit 50$', 'ads7979-13-bm3-limit-50', NULL, '2026-07-20 20:19:18', '2026-07-20 20:19:18', 'bm3 limit 50$'),
('019f82af-5c77-72e6-bc4a-a959ae12bf0f', '019f829a-f708-7093-890d-4950e6ce2e66', '2', 'Via Kháng 902 & Chat Support', 'ads7979-2-via-khang-902-chat-support', NULL, '2026-07-20 20:19:18', '2026-07-20 20:19:18', 'via khang 902 & chat support'),
('019f82af-5cad-7392-9656-9f8fa2545c95', '019f829a-f708-7093-890d-4950e6ce2e66', '3', 'Ad account Limit 50$', 'ads7979-3-ad-account-limit-50', NULL, '2026-07-20 20:19:18', '2026-07-20 20:19:18', 'ad account limit 50$'),
('019f82af-5cbe-73f0-b751-0cadcb5c6b36', '019f829a-f708-7093-890d-4950e6ce2e66', '4', 'Ad account Limit 250$', 'ads7979-4-ad-account-limit-250', NULL, '2026-07-20 20:19:18', '2026-07-20 20:19:18', 'ad account limit 250$'),
('019f82af-5ccf-71a8-8bc3-1ee1121bd7ed', '019f829a-f708-7093-890d-4950e6ce2e66', '5', 'Via Ngoại Live Ads (No2FA)', 'ads7979-5-via-ngoai-live-ads-no2fa', NULL, '2026-07-20 20:19:18', '2026-07-20 20:19:18', 'via ngoai live ads (no2fa)'),
('019f82af-5d6c-71c4-a32c-db49989a6cc3', '019f829a-f708-7093-890d-4950e6ce2e66', '6', 'Via Ngoại Live Ads (2FA)', 'ads7979-6-via-ngoai-live-ads-2fa', NULL, '2026-07-20 20:19:18', '2026-07-20 20:19:18', 'via ngoai live ads (2fa)'),
('019f82af-5dea-7360-936b-3aa19da2dd52', '019f829a-f708-7093-890d-4950e6ce2e66', '9', 'Via Ngoại DIE ADS', 'ads7979-9-via-ngoai-die-ads', NULL, '2026-07-20 20:19:18', '2026-07-20 20:19:18', 'via ngoai die ads'),
('019f82af-5e0e-729a-a8e2-59ef4d9e2997', '019f829a-f708-7093-890d-4950e6ce2e66', '7', 'Clone Global', 'ads7979-7-clone-global', NULL, '2026-07-20 20:19:18', '2026-07-20 20:19:18', 'clone global'),
('019f82af-5e4d-730c-994f-86a322e0e883', '019f829a-f708-7093-890d-4950e6ce2e66', '8', 'Clone Việt Nam', 'ads7979-8-clone-viet-nam', NULL, '2026-07-20 20:19:18', '2026-07-20 20:19:18', 'clone viet nam'),
('019f82af-5e56-7144-8a42-34ec76467805', '019f829a-f708-7093-890d-4950e6ce2e66', '10', 'Hotmail Live V.V', 'ads7979-10-hotmail-live-vv', NULL, '2026-07-20 20:19:18', '2026-07-20 20:19:18', 'hotmail live v.v'),
('019f82af-5e67-7153-9ee1-9c332fc1fd73', '019f829a-f708-7093-890d-4950e6ce2e66', '11', 'Clone Instagram', 'ads7979-11-clone-instagram', NULL, '2026-07-20 20:19:18', '2026-07-20 20:19:18', 'clone instagram');

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `coupons`
--

CREATE TABLE `coupons` (
  `id` char(36) COLLATE utf8mb4_unicode_ci NOT NULL,
  `code` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `type` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'percentage',
  `value` decimal(15,2) NOT NULL DEFAULT '10.00',
  `min_spend` decimal(15,2) NOT NULL DEFAULT '0.00',
  `start_date` datetime NOT NULL,
  `end_date` datetime NOT NULL,
  `usage_limit` int NOT NULL DEFAULT '1',
  `used_count` int NOT NULL DEFAULT '0',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Đang đổ dữ liệu cho bảng `coupons`
--

INSERT INTO `coupons` (`id`, `code`, `type`, `value`, `min_spend`, `start_date`, `end_date`, `usage_limit`, `used_count`, `created_at`, `updated_at`, `deleted_at`) VALUES
('019f7d72-0a49-7030-931e-baad3df17791', '2072026', 'fixed', 10000.00, 0.00, '2026-07-20 00:00:00', '2026-07-27 00:00:00', 1, 1, '2026-07-19 19:54:13', '2026-07-19 20:08:52', '2026-07-19 20:08:52'),
('019f7d84-ad34-706a-9b7f-69698e11dd79', '20720261', 'percentage', 20.00, 500000.00, '2026-07-20 00:00:00', '2026-07-27 00:00:00', 2, 2, '2026-07-19 20:14:34', '2026-07-19 20:47:55', NULL);

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `deposits`
--

CREATE TABLE `deposits` (
  `id` char(36) COLLATE utf8mb4_unicode_ci NOT NULL,
  `user_id` char(36) COLLATE utf8mb4_unicode_ci NOT NULL,
  `code` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `amount_usdt` decimal(16,4) NOT NULL,
  `amount_to_pay` decimal(16,4) NOT NULL,
  `amount_vnd` decimal(16,4) NOT NULL,
  `tx_hash` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `admin_wallet_address` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `status` enum('pending_payment','completed','failed','expired') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'pending_payment',
  `payment_method` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'bep20',
  `payer_binance_id` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `admin_binance_id` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Đang đổ dữ liệu cho bảng `deposits`
--

INSERT INTO `deposits` (`id`, `user_id`, `code`, `amount_usdt`, `amount_to_pay`, `amount_vnd`, `tx_hash`, `admin_wallet_address`, `status`, `payment_method`, `payer_binance_id`, `admin_binance_id`, `created_at`, `updated_at`) VALUES
('019f7d72-53e8-71b5-ac64-cf69cc440163', '019f7d2f-cf44-723f-8c30-13b7ea78dadb', 'GUKXCJ', 100.0000, 100.0000, 2624000.0000, 'manual_approved_by_admin_019f7d72-53e8-71b5-ac64-cf69cc440163', '0xAB4a22a64bBC1Ca0445758C2Ee66a4cB2b644444', 'completed', 'bep20', NULL, NULL, '2026-07-19 19:54:32', '2026-07-19 19:54:45'),
('019f7da5-35a9-707b-b805-a6cde36a32ad', '019f7d2f-cf44-723f-8c30-13b7ea78dadb', 'G6XGUJ', 0.1500, 0.1500, 3941.0000, '443847639186710528', NULL, 'completed', 'binance', NULL, '63596652', '2026-07-19 20:50:07', '2026-07-19 20:59:55'),
('019f7e0c-c918-7033-8977-bafe87a4109c', '019f7d2f-cf44-723f-8c30-13b7ea78dadb', 'BL68PV', 3.0000, 3.0000, 78816.0000, 'manual_approved_by_admin_019f7e0c-c918-7033-8977-bafe87a4109c', '0xAB4a22a64bBC1Ca0445758C2Ee66a4cB2b644444', 'completed', 'bep20', NULL, NULL, '2026-07-19 22:43:14', '2026-07-20 13:55:03'),
('019f814f-f3f1-70a3-bd2d-3bc15e47340f', '019f7d2f-cf44-723f-8c30-13b7ea78dadb', 'QFNTNG', 10000.0000, 10000.0000, 262760000.0000, 'manual_approved_by_admin_019f814f-f3f1-70a3-bd2d-3bc15e47340f', '0xAB4a22a64bBC1Ca0445758C2Ee66a4cB2b644444', 'completed', 'bep20', NULL, NULL, '2026-07-20 13:55:28', '2026-07-20 13:56:54'),
('019f8165-d0e0-71b6-8369-d24e97a121e4', '019f7d2f-cf44-723f-8c30-13b7ea78dadb', 'ZW2CAM', 10.0000, 10.0000, 262750.0000, '22222222222222222222222', '0xAB4a22a64bBC1Ca0445758C2Ee66a4cB2b644444', 'completed', 'bep20', NULL, NULL, '2026-07-20 14:19:21', '2026-07-20 14:19:46'),
('019f8169-0d06-7391-b0a1-373cd71325df', '019f7d2f-cf44-723f-8c30-13b7ea78dadb', 'V2FPWH', 100.0000, 100.0000, 2627500.0000, NULL, '0xAB4a22a64bBC1Ca0445758C2Ee66a4cB2b644444', 'expired', 'bep20', NULL, NULL, '2026-07-20 14:22:53', '2026-07-20 19:15:45'),
('019f8275-52c3-72ca-a490-d5fb768917d0', '019f7d2f-cf44-723f-8c30-13b7ea78dadb', 'RW53BD', 1.0000, 1.0000, 26281.0000, NULL, NULL, 'pending_payment', 'binance', NULL, '63596652', '2026-07-20 19:15:54', '2026-07-20 19:15:54');

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `failed_jobs`
--

CREATE TABLE `failed_jobs` (
  `id` bigint UNSIGNED NOT NULL,
  `uuid` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `connection` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `queue` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `payload` longtext COLLATE utf8mb4_unicode_ci NOT NULL,
  `exception` longtext COLLATE utf8mb4_unicode_ci NOT NULL,
  `failed_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `inventory_batches`
--

CREATE TABLE `inventory_batches` (
  `id` char(36) COLLATE utf8mb4_unicode_ci NOT NULL,
  `product_id` char(36) COLLATE utf8mb4_unicode_ci NOT NULL,
  `batch_name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `keys_pool` longtext COLLATE utf8mb4_unicode_ci,
  `initial_count` int NOT NULL DEFAULT '0',
  `sold_count` int NOT NULL DEFAULT '0',
  `status` enum('active','completed','inactive') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'active',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `normalized_batch_name` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `jobs`
--

CREATE TABLE `jobs` (
  `id` bigint UNSIGNED NOT NULL,
  `queue` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `payload` longtext COLLATE utf8mb4_unicode_ci NOT NULL,
  `attempts` smallint UNSIGNED NOT NULL,
  `reserved_at` int UNSIGNED DEFAULT NULL,
  `available_at` int UNSIGNED NOT NULL,
  `created_at` int UNSIGNED NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `job_batches`
--

CREATE TABLE `job_batches` (
  `id` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `total_jobs` int NOT NULL,
  `pending_jobs` int NOT NULL,
  `failed_jobs` int NOT NULL,
  `failed_job_ids` longtext COLLATE utf8mb4_unicode_ci NOT NULL,
  `options` mediumtext COLLATE utf8mb4_unicode_ci,
  `cancelled_at` int DEFAULT NULL,
  `created_at` int NOT NULL,
  `finished_at` int DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `migrations`
--

CREATE TABLE `migrations` (
  `id` int UNSIGNED NOT NULL,
  `migration` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `batch` int NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Đang đổ dữ liệu cho bảng `migrations`
--

INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES
(1, '0001_01_01_000000_create_users_table', 1),
(2, '0001_01_01_000001_create_cache_table', 1),
(3, '0001_01_01_000002_create_jobs_table', 1),
(4, '2026_07_15_000000_create_categories_table', 1),
(5, '2026_07_15_000001_create_products_table', 1),
(6, '2026_07_15_000002_create_orders_table', 1),
(7, '2026_07_15_000003_create_order_items_table', 1),
(8, '2026_07_15_000004_create_inventory_batches_table', 1),
(9, '2026_07_16_031350_add_profile_fields_to_users_table', 1),
(10, '2026_07_17_114012_add_normalized_columns_to_tables', 1),
(11, '2026_07_17_225500_alter_products_category_id_foreign_restrict', 1),
(12, '2026_07_18_000001_add_soft_deletes_to_products_table', 1),
(13, '2026_07_18_015000_add_balance_to_users_table', 1),
(14, '2026_07_18_015100_create_deposits_table', 1),
(15, '2026_07_18_020000_add_performance_indexes_to_tables', 1),
(16, '2026_07_18_024541_create_system_settings_table', 1),
(17, '2026_07_18_100000_add_api_key_to_users_table', 1),
(18, '2026_07_18_184902_add_admin_wallet_address_to_deposits_table', 1),
(19, '2026_07_18_192800_add_status_to_users_table', 1),
(20, '2026_07_19_041702_add_username_to_users_table', 1),
(21, '2026_07_19_124806_add_binance_payment_fields_to_deposits_and_settings', 1),
(22, '2026_07_19_160908_remove_admin_binance_qr_from_system_settings', 1),
(23, '2026_07_20_000000_create_coupons_table', 2),
(24, '2026_07_20_000001_add_coupon_fields_to_orders_table', 2),
(25, '2026_07_20_010000_add_composite_performance_indexes', 3),
(26, '2026_07_21_000001_add_deposit_reconciliation_index', 4),
(27, '2026_07_21_100000_create_suppliers_and_add_source_fields', 4),
(28, '2026_07_21_200000_add_token_version_to_users_table', 5),
(29, '2026_07_21_200001_encrypt_sensitive_system_settings', 5),
(30, '2026_07_21_200002_add_api_key_hash_to_users_table', 6),
(31, '2026_07_21_062251_encrypt_existing_api_keys', 7);

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `orders`
--

CREATE TABLE `orders` (
  `id` char(36) COLLATE utf8mb4_unicode_ci NOT NULL,
  `user_id` char(36) COLLATE utf8mb4_unicode_ci NOT NULL,
  `order_code` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `total_price` decimal(15,2) NOT NULL,
  `coupon_code` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `discount_amount` decimal(15,2) NOT NULL DEFAULT '0.00',
  `coupon_id` char(36) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `status` enum('pending','paid','cancelled') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'pending',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `order_items`
--

CREATE TABLE `order_items` (
  `id` char(36) COLLATE utf8mb4_unicode_ci NOT NULL,
  `order_id` char(36) COLLATE utf8mb4_unicode_ci NOT NULL,
  `product_id` char(36) COLLATE utf8mb4_unicode_ci NOT NULL,
  `product_name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `price` decimal(15,2) NOT NULL,
  `quantity` int NOT NULL,
  `delivered_keys` text COLLATE utf8mb4_unicode_ci,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `password_reset_tokens`
--

CREATE TABLE `password_reset_tokens` (
  `email` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `token` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `products`
--

CREATE TABLE `products` (
  `id` char(36) COLLATE utf8mb4_unicode_ci NOT NULL,
  `supplier_id` char(36) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `external_id` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `category_id` char(36) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `slug` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `description` text COLLATE utf8mb4_unicode_ci,
  `price` decimal(15,2) NOT NULL,
  `supplier_price_vnd` decimal(15,2) DEFAULT NULL,
  `supplier_stock` int UNSIGNED DEFAULT NULL,
  `supplier_synced_at` timestamp NULL DEFAULT NULL,
  `status` enum('active','inactive') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'active',
  `attributes` json DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `normalized_name` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Đang đổ dữ liệu cho bảng `products`
--

INSERT INTO `products` (`id`, `supplier_id`, `external_id`, `category_id`, `name`, `slug`, `description`, `price`, `supplier_price_vnd`, `supplier_stock`, `supplier_synced_at`, `status`, `attributes`, `created_at`, `updated_at`, `normalized_name`, `deleted_at`) VALUES
('019f82af-5c54-7394-99b8-16412f243f37', '019f829a-f708-7093-890d-4950e6ce2e66', '2', '019f82af-5c43-710f-85e6-ff318e943418', 'BM50$ Ngâm', 'ads7979-2-bm50-ngam', 'Chưa tạo TKQC | Phù hợp kích tut BM3 | Created: 07/2026', 0.76, 20000.00, 0, '2026-07-20 20:51:15', 'active', '[]', '2026-07-20 20:19:18', '2026-07-20 20:51:15', 'bm50$ ngam', NULL),
('019f82af-5c57-734c-915c-59b525cbcc1d', '019f829a-f708-7093-890d-4950e6ce2e66', '327', '019f82af-5c43-710f-85e6-ff318e943418', 'BM50$ Có TK', 'ads7979-327-bm50-co-tk', 'Đã tạo TKQC | Created: 07/2026', 0.76, 20000.00, 0, '2026-07-20 20:51:15', 'active', '[]', '2026-07-20 20:19:18', '2026-07-20 20:51:15', 'bm50$ co tk', NULL),
('019f82af-5c5c-7112-af75-b81299facf9b', '019f829a-f708-7093-890d-4950e6ce2e66', '3', '019f82af-5c43-710f-85e6-ff318e943418', 'BM50$ Ngâm Kháng', 'ads7979-3-bm50-ngam-khang', 'Chưa tạo TKQC |Phù hợp cầm TKCN | Phù hợp kích tut BM3 | Created: 07/2026', 0.95, 25000.00, 0, '2026-07-20 20:51:15', 'active', '[]', '2026-07-20 20:19:18', '2026-07-20 20:51:15', 'bm50$ ngam khang', NULL),
('019f82af-5c62-71b1-90a5-7f62f0955065', '019f829a-f708-7093-890d-4950e6ce2e66', '469', '019f82af-5c5e-7396-95a3-825ffab2f738', 'BM3 Không Tạo Được Tài Khoản (Cầm Đối Tác, Dòng 2, Page)', 'ads7979-469-bm3-khong-tao-duoc-tai-khoan-cam-doi-tac-dong-2-page', 'Phù hợp dùng cầm TKCN dòng 2, đối tác, cầm Page | Created:  Jun/2026', 0.57, 15000.00, 0, '2026-07-20 20:51:15', 'active', '[]', '2026-07-20 20:19:18', '2026-07-20 20:51:15', 'bm3 khong tao duoc tai khoan (cam doi tac, dong 2, page)', NULL),
('019f82af-5c66-7166-abe4-1693699b16a6', '019f829a-f708-7093-890d-4950e6ce2e66', '5', '019f82af-5c5e-7396-95a3-825ffab2f738', 'BM3 Trống 3 Slot', 'ads7979-5-bm3-trong-3-slot', 'Chưa tạo TKQC | Limit tăng theo chi tiêu cực nhanh | Created:  Jun/2026', 0.95, 25000.00, 0, '2026-07-20 20:51:15', 'active', '[]', '2026-07-20 20:19:18', '2026-07-20 20:51:15', 'bm3 trong 3 slot', NULL),
('019f82af-5c6b-7359-8aa5-7fd2c7eba814', '019f829a-f708-7093-890d-4950e6ce2e66', '7', '019f82af-5c5e-7396-95a3-825ffab2f738', 'BM3 Ngâm Full 3', 'ads7979-7-bm3-ngam-full-3', 'Đã tạo 3 TKQC | Limit 50$ | Limit tăng theo chi tiêu cực nhanh | Created:  Jul/2026', 1.52, 40000.00, 761, '2026-07-20 20:51:15', 'active', '[]', '2026-07-20 20:19:18', '2026-07-20 20:51:15', 'bm3 ngam full 3', NULL),
('019f82af-5c6f-7185-8f4d-be9f0e7078f7', '019f829a-f708-7093-890d-4950e6ce2e66', '517', '019f82af-5c5e-7396-95a3-825ffab2f738', 'BM3 Ngâm Full 4', 'ads7979-517-bm3-ngam-full-4', 'Đã tạo 4 TKQC | Limit 50$ | Limit tăng theo chi tiêu cực nhanh | Created:  Jul/2026', 1.90, 50000.00, 0, '2026-07-20 20:51:15', 'active', '[]', '2026-07-20 20:19:18', '2026-07-20 20:51:15', 'bm3 ngam full 4', NULL),
('019f82af-5c72-7147-ada8-96348a726fd4', '019f829a-f708-7093-890d-4950e6ce2e66', '364', '019f82af-5c5e-7396-95a3-825ffab2f738', 'BM3 XÁC MINH DOANH NGHIỆP FULL 3', 'ads7979-364-bm3-xac-minh-doanh-nghiep-full-3', 'Đã tạo 3 TKQC | Limit 50$ | Limit tăng theo chi tiêu cực nhanh | Created: Jul/2026', 8.37, 220000.00, 29, '2026-07-20 20:51:15', 'active', '[]', '2026-07-20 20:19:18', '2026-07-20 20:51:15', 'bm3 xac minh doanh nghiep full 3', NULL),
('019f82af-5c75-71cc-8dfe-f14ec086fccc', '019f829a-f708-7093-890d-4950e6ce2e66', '1305', '019f82af-5c5e-7396-95a3-825ffab2f738', 'BM3 XÁC MINH DOANH NGHIỆP FULL 4', 'ads7979-1305-bm3-xac-minh-doanh-nghiep-full-4', 'Đã tạo 4 TKQC | Limit 50$ | Limit tăng theo chi tiêu cực nhanh | Created: Jul/2026', 9.51, 250000.00, 177, '2026-07-20 20:51:15', 'active', '[]', '2026-07-20 20:19:18', '2026-07-20 20:51:15', 'bm3 xac minh doanh nghiep full 4', NULL),
('019f82af-5c7d-71d0-8482-57a7531c8eed', '019f829a-f708-7093-890d-4950e6ce2e66', '22', '019f82af-5c77-72e6-bc4a-a959ae12bf0f', 'Via Chat Support', 'ads7979-22-via-chat-support', 'Có thể Chat Support Facebook | Created: 2025 | Checkpoint Mail', 4.56, 120000.00, 53, '2026-07-20 20:51:15', 'active', '[]', '2026-07-20 20:19:18', '2026-07-20 20:51:15', 'via chat support', NULL),
('019f82af-5c82-73a9-a87e-3d967539ea71', '019f829a-f708-7093-890d-4950e6ce2e66', '12', '019f82af-5c77-72e6-bc4a-a959ae12bf0f', 'Via Ngoại Cổ XMDT Die Ads', 'ads7979-12-via-ngoai-co-xmdt-die-ads', 'Green tick: 2 Lines | Tích ẩn | Ad account: Die | Created: 2008-2024', 2.66, 70000.00, 0, '2026-07-20 20:51:15', 'active', '[]', '2026-07-20 20:19:18', '2026-07-20 20:51:15', 'via ngoai co xmdt die ads', NULL),
('019f82af-5c86-70bb-8b01-0250345fdec5', '019f829a-f708-7093-890d-4950e6ce2e66', '10', '019f82af-5c77-72e6-bc4a-a959ae12bf0f', 'Via Ngoại Cổ XMDT Limit 50$', 'ads7979-10-via-ngoai-co-xmdt-limit-50', 'Green tick: 2 Lines | Tích ẩn | Created: 2008-2024', 4.56, 120000.00, 0, '2026-07-20 20:51:15', 'active', '[]', '2026-07-20 20:19:18', '2026-07-20 20:51:15', 'via ngoai co xmdt limit 50$', NULL),
('019f82af-5c8b-7386-adff-8fe3738153e0', '019f829a-f708-7093-890d-4950e6ce2e66', '11', '019f82af-5c77-72e6-bc4a-a959ae12bf0f', 'Via Philippines Cổ XMDT Limit 50$', 'ads7979-11-via-philippines-co-xmdt-limit-50', 'Green tick: 2 Lines | Tích ẩn | Created: 2008-2023', 6.09, 160000.00, 175, '2026-07-20 20:51:15', 'active', '[]', '2026-07-20 20:19:18', '2026-07-20 20:51:15', 'via philippines co xmdt limit 50$', NULL),
('019f82af-5c91-720e-974d-447239c97c4f', '019f829a-f708-7093-890d-4950e6ce2e66', '13', '019f82af-5c77-72e6-bc4a-a959ae12bf0f', 'Via Colombia Cổ XMDT Limit 50$', 'ads7979-13-via-colombia-co-xmdt-limit-50', 'Green tick: 2 Lines | Tích ẩn | Created: 2008-2023', 6.09, 160000.00, 0, '2026-07-20 20:51:15', 'active', '[]', '2026-07-20 20:19:18', '2026-07-20 20:51:15', 'via colombia co xmdt limit 50$', NULL),
('019f82af-5c96-723a-b806-2b49b423e27a', '019f829a-f708-7093-890d-4950e6ce2e66', '14', '019f82af-5c77-72e6-bc4a-a959ae12bf0f', 'Via Indonesia Cổ Kháng 902 Die Ads', 'ads7979-14-via-indonesia-co-khang-902-die-ads', 'Green tick: 3 Lines | Tích ẩn | Ad account: Die | Created: 2008-2023', 4.56, 120000.00, 52, '2026-07-20 20:51:15', 'active', '[]', '2026-07-20 20:19:18', '2026-07-20 20:51:15', 'via indonesia co khang 902 die ads', NULL),
('019f82af-5c9c-73e1-9d0c-2a993b7b91cb', '019f829a-f708-7093-890d-4950e6ce2e66', '2430', '019f82af-5c77-72e6-bc4a-a959ae12bf0f', 'Via Indonesia Cổ Kháng 902 Live Ads', 'ads7979-2430-via-indonesia-co-khang-902-live-ads', 'Green tick: 3 Lines | Tích ẩn | Ad account: Live | Created: 2008-2022', 6.85, 180000.00, 59, '2026-07-20 20:51:15', 'active', '[]', '2026-07-20 20:19:18', '2026-07-20 20:51:15', 'via indonesia co khang 902 live ads', NULL),
('019f82af-5ca1-7113-95f9-39b47b17e333', '019f829a-f708-7093-890d-4950e6ce2e66', '2692', '019f82af-5c77-72e6-bc4a-a959ae12bf0f', 'Via Colombia Cổ Kháng 902 Die Ads', 'ads7979-2692-via-colombia-co-khang-902-die-ads', 'Green tick: 3 Lines | Tích ẩn | Ad account: Die | Created: 2008-2022', 4.56, 120000.00, 293, '2026-07-20 20:51:15', 'active', '[]', '2026-07-20 20:19:18', '2026-07-20 20:51:15', 'via colombia co khang 902 die ads', NULL),
('019f82af-5ca6-703d-95cd-31d64992d59e', '019f829a-f708-7093-890d-4950e6ce2e66', '2693', '019f82af-5c77-72e6-bc4a-a959ae12bf0f', 'Via Colombia Cổ Kháng 902 Live Ads', 'ads7979-2693-via-colombia-co-khang-902-live-ads', 'Green tick: 3 Lines | Tích ẩn | Ad account: Live | Created: 2008-2022', 6.85, 180000.00, 97, '2026-07-20 20:51:15', 'active', '[]', '2026-07-20 20:19:18', '2026-07-20 20:51:15', 'via colombia co khang 902 live ads', NULL),
('019f82af-5caa-7206-99ae-945ddb81ab88', '019f829a-f708-7093-890d-4950e6ce2e66', '8', '019f82af-5c77-72e6-bc4a-a959ae12bf0f', 'Via Ngoại Cổ Kháng 902 Die Ads', 'ads7979-8-via-ngoai-co-khang-902-die-ads', 'Green tick: 3 Lines | Tích ẩn | Ad account: Die | Created: 2008-2024', 3.77, 99000.00, 151, '2026-07-20 20:51:15', 'active', '[]', '2026-07-20 20:19:18', '2026-07-20 20:51:15', 'via ngoai co khang 902 die ads', NULL),
('019f82af-5cb2-73e5-83a4-719b1a67e72a', '019f829a-f708-7093-890d-4950e6ce2e66', '23', '019f82af-5cad-7392-9656-9f8fa2545c95', 'Via Ngoại Limit 50$ Có TKQC Cổ', 'ads7979-23-via-ngoai-limit-50-co-tkqc-co', 'Bao đổi tiền, múi giờ, quốc gia | TKQC Cổ tạo từ: 2010–2024', 5.70, 150000.00, 101, '2026-07-20 20:51:15', 'active', '[]', '2026-07-20 20:19:18', '2026-07-20 20:51:15', 'via ngoai limit 50$ co tkqc co', NULL),
('019f82af-5cb6-7256-ba25-d9a759529690', '019f829a-f708-7093-890d-4950e6ce2e66', '16', '019f82af-5cad-7392-9656-9f8fa2545c95', 'Via Ngoại Limit 50$ Có TKQC Cổ (No2FA)', 'ads7979-16-via-ngoai-limit-50-co-tkqc-co-no2fa', 'Bao đổi tiền, múi giờ, quốc gia | TKQC Cổ tạo từ: 2010–2024 | Chưa dính IP Việt', 4.94, 130000.00, 2, '2026-07-20 20:51:15', 'active', '[]', '2026-07-20 20:19:18', '2026-07-20 20:51:15', 'via ngoai limit 50$ co tkqc co (no2fa)', NULL),
('019f82af-5cbb-725d-89e6-5dfcaeda4629', '019f829a-f708-7093-890d-4950e6ce2e66', '24', '019f82af-5cad-7392-9656-9f8fa2545c95', 'Full Via Limit 50$ +7 USD đã paid mồi (No2FA)', 'ads7979-24-full-via-limit-50-7-usd-da-paid-moi-no2fa', 'TKQC đã camp mồi, paid mồi | Đã kháng 273 | Tiền tệ: +7 USD | Quốc gia US lách thuế', 6.85, 180000.00, 0, '2026-07-20 20:51:15', 'active', '[]', '2026-07-20 20:19:18', '2026-07-20 20:51:15', 'full via limit 50$ +7 usd da paid moi (no2fa)', NULL),
('019f82af-5cc2-706e-9164-a9d62218db9f', '019f829a-f708-7093-890d-4950e6ce2e66', '2736', '019f82af-5cbe-73f0-b751-0cadcb5c6b36', 'Full Via Limit 200$', 'ads7979-2736-full-via-limit-200', 'Bao đổi tiền, múi giờ, quốc gia | Không bao tụt Limit | TKQC Cổ tạo từ: 2010-2023', 7.61, 200000.00, 0, '2026-07-20 20:51:15', 'active', '[]', '2026-07-20 20:19:18', '2026-07-20 20:51:15', 'full via limit 200$', NULL),
('019f82af-5cc7-70f4-846f-b3f9c979923d', '019f829a-f708-7093-890d-4950e6ce2e66', '802', '019f82af-5cbe-73f0-b751-0cadcb5c6b36', 'Full Via Limit 250$', 'ads7979-802-full-via-limit-250', 'Bao đổi tiền, múi giờ, quốc gia | Bao tụt Limit | TKQC Cổ tạo từ: 2010-2023', 22.82, 600000.00, 0, '2026-07-20 20:51:15', 'active', '[]', '2026-07-20 20:19:18', '2026-07-20 20:51:15', 'full via limit 250$', NULL),
('019f82af-5ccc-72dd-b16f-46d1e72701f8', '019f829a-f708-7093-890d-4950e6ce2e66', '804', '019f82af-5cbe-73f0-b751-0cadcb5c6b36', 'Full Via Limit 350$', 'ads7979-804-full-via-limit-350', 'Bao đổi tiền, múi giờ, quốc gia | Không bao tụt Limit | TKQC Cổ tạo từ: 2010-2019', 7.61, 200000.00, 0, '2026-07-20 20:51:15', 'active', '[]', '2026-07-20 20:19:18', '2026-07-20 20:51:15', 'full via limit 350$', NULL),
('019f82af-5cd4-70a0-993e-de9cb1a6547c', '019f829a-f708-7093-890d-4950e6ce2e66', '29', '019f82af-5ccf-71a8-8bc3-1ee1121bd7ed', 'Via Ngoại New (No2FA)', 'ads7979-29-via-ngoai-new-no2fa', 'Friend: 0-5000 | LIVE ADS  | Checkpoint Mail | Created: 2024-2025', 0.57, 15000.00, 3, '2026-07-20 20:51:15', 'active', '[]', '2026-07-20 20:19:18', '2026-07-20 20:51:15', 'via ngoai new (no2fa)', NULL),
('019f82af-5cd8-70a7-af11-7b91279a97a1', '019f829a-f708-7093-890d-4950e6ce2e66', '20', '019f82af-5ccf-71a8-8bc3-1ee1121bd7ed', 'Via Ngoại Cổ 0-29 Friend (No2FA)', 'ads7979-20-via-ngoai-co-0-29-friend-no2fa', 'Friend: 0-29 | LIVE ADS  | Checkpoint Mail | Created: 2008-2024', 0.95, 25000.00, 112, '2026-07-20 20:51:15', 'active', '[]', '2026-07-20 20:19:18', '2026-07-20 20:51:15', 'via ngoai co 0-29 friend (no2fa)', NULL),
('019f82af-5cdd-7052-82f8-83fd71a36b1c', '019f829a-f708-7093-890d-4950e6ce2e66', '19', '019f82af-5ccf-71a8-8bc3-1ee1121bd7ed', 'Via Ngoại Cổ (No2FA)', 'ads7979-19-via-ngoai-co-no2fa', 'Friend: 30-1000 | LIVE ADS  | Checkpoint Mail | Created: 2008-2023', 1.33, 35000.00, 1017, '2026-07-20 20:51:15', 'active', '[]', '2026-07-20 20:19:18', '2026-07-20 20:51:15', 'via ngoai co (no2fa)', NULL),
('019f82af-5ce2-7270-93c2-8098e0cb3adb', '019f829a-f708-7093-890d-4950e6ce2e66', '30', '019f82af-5ccf-71a8-8bc3-1ee1121bd7ed', 'Via Ngoại Cổ 500-3K Friend (No2FA)', 'ads7979-30-via-ngoai-co-500-3k-friend-no2fa', 'Friend: 500-3000 | LIVE ADS  | Checkpoint Mail | Created: 2008-2024', 2.85, 75000.00, 0, '2026-07-20 20:51:15', 'active', '[]', '2026-07-20 20:19:18', '2026-07-20 20:51:15', 'via ngoai co 500-3k friend (no2fa)', NULL),
('019f82af-5ce6-704d-bd85-1b9f6b5e1828', '019f829a-f708-7093-890d-4950e6ce2e66', '31', '019f82af-5ccf-71a8-8bc3-1ee1121bd7ed', 'Via Ngoại Cổ 3-5K Friend (No2FA)', 'ads7979-31-via-ngoai-co-3-5k-friend-no2fa', 'Friend: 3000-5000 | LIVE ADS  | Checkpoint Mail | Created: 2008-2024', 3.80, 100000.00, 0, '2026-07-20 20:51:15', 'active', '[]', '2026-07-20 20:19:18', '2026-07-20 20:51:15', 'via ngoai co 3-5k friend (no2fa)', NULL),
('019f82af-5cea-7082-a30e-2b18b0309da2', '019f829a-f708-7093-890d-4950e6ce2e66', '32', '019f82af-5ccf-71a8-8bc3-1ee1121bd7ed', 'Via India New (No2FA)', 'ads7979-32-via-india-new-no2fa', 'Friend: 30-5000 | LIVE ADS  | Checkpoint Mail | Created: 2024-2025 | Chưa dính IP Việt', 1.90, 50000.00, 53, '2026-07-20 20:51:15', 'active', '[]', '2026-07-20 20:19:18', '2026-07-20 20:51:15', 'via india new (no2fa)', NULL),
('019f82af-5cef-7231-8530-a13b1371d7e9', '019f829a-f708-7093-890d-4950e6ce2e66', '34', '019f82af-5ccf-71a8-8bc3-1ee1121bd7ed', 'Via Pakistan New (No2FA)', 'ads7979-34-via-pakistan-new-no2fa', 'Friend: 30-5000 | LIVE ADS  | Checkpoint Mail | Created: 2024-2025 | Chưa dính IP Việt', 1.90, 50000.00, 0, '2026-07-20 20:51:15', 'active', '[]', '2026-07-20 20:19:18', '2026-07-20 20:51:15', 'via pakistan new (no2fa)', NULL),
('019f82af-5cf4-72dc-ae2c-41bc3f75e32a', '019f829a-f708-7093-890d-4950e6ce2e66', '35', '019f82af-5ccf-71a8-8bc3-1ee1121bd7ed', 'Via Bangladesh New (No2FA)', 'ads7979-35-via-bangladesh-new-no2fa', 'Friend: 30-5000 | LIVE ADS  | Checkpoint Mail | Created: 2024-2025 | Chưa dính IP Việt', 1.90, 50000.00, 518, '2026-07-20 20:51:15', 'active', '[]', '2026-07-20 20:19:18', '2026-07-20 20:51:15', 'via bangladesh new (no2fa)', NULL),
('019f82af-5cf8-73e9-bdcc-b5ff17f8bc43', '019f829a-f708-7093-890d-4950e6ce2e66', '36', '019f82af-5ccf-71a8-8bc3-1ee1121bd7ed', 'Via Indonesia New (No2FA)', 'ads7979-36-via-indonesia-new-no2fa', 'Friend: 30-5000 | LIVE ADS  | Checkpoint Mail | Created: 2024-2025 | Chưa dính IP Việt', 1.90, 50000.00, 72, '2026-07-20 20:51:15', 'active', '[]', '2026-07-20 20:19:18', '2026-07-20 20:51:15', 'via indonesia new (no2fa)', NULL),
('019f82af-5cfc-71dc-bbbb-6289ad370468', '019f829a-f708-7093-890d-4950e6ce2e66', '37', '019f82af-5ccf-71a8-8bc3-1ee1121bd7ed', 'Via Indonesia Cổ (No2FA)', 'ads7979-37-via-indonesia-co-no2fa', 'Friend: 30-5000 | LIVE ADS  | Checkpoint Mail | Created: 2008-2023 | Chưa dính IP Việt', 2.28, 60000.00, 350, '2026-07-20 20:51:15', 'active', '[]', '2026-07-20 20:19:18', '2026-07-20 20:51:15', 'via indonesia co (no2fa)', NULL),
('019f82af-5d01-7155-9316-ebb015f6d704', '019f829a-f708-7093-890d-4950e6ce2e66', '5527', '019f82af-5ccf-71a8-8bc3-1ee1121bd7ed', 'Via Indonesia Cổ 500-3K Friend (No2FA)', 'ads7979-5527-via-indonesia-co-500-3k-friend-no2fa', 'Friend: 500-3000 | LIVE ADS  | Checkpoint Mail | Created: 2008-2023 | Chưa dính IP Việt', 3.42, 90000.00, 15, '2026-07-20 20:51:15', 'active', '[]', '2026-07-20 20:19:18', '2026-07-20 20:51:15', 'via indonesia co 500-3k friend (no2fa)', NULL),
('019f82af-5d06-7042-ae43-935e05d708f0', '019f829a-f708-7093-890d-4950e6ce2e66', '5528', '019f82af-5ccf-71a8-8bc3-1ee1121bd7ed', 'Via Indonesia Cổ 3-5K Friend (No2FA)', 'ads7979-5528-via-indonesia-co-3-5k-friend-no2fa', 'Friend: 3000-5000 | LIVE ADS  | Checkpoint Mail | Created: 2008-2023 | Chưa dính IP Việt', 4.94, 130000.00, 1, '2026-07-20 20:51:15', 'active', '[]', '2026-07-20 20:19:18', '2026-07-20 20:51:15', 'via indonesia co 3-5k friend (no2fa)', NULL),
('019f82af-5d0c-7052-88d8-f482bf3dabe2', '019f829a-f708-7093-890d-4950e6ce2e66', '38', '019f82af-5ccf-71a8-8bc3-1ee1121bd7ed', 'Via Philippines New (No2FA)', 'ads7979-38-via-philippines-new-no2fa', 'Friend: 30-5000 | LIVE ADS  | Checkpoint Mail | Created: 2024-2025 | Chưa dính IP Việt', 1.90, 50000.00, 3, '2026-07-20 20:51:15', 'active', '[]', '2026-07-20 20:19:18', '2026-07-20 20:51:15', 'via philippines new (no2fa)', NULL),
('019f82af-5d11-73b5-b368-e78e1eb6af9d', '019f829a-f708-7093-890d-4950e6ce2e66', '39', '019f82af-5ccf-71a8-8bc3-1ee1121bd7ed', 'Via Philippines Cổ (No2FA)', 'ads7979-39-via-philippines-co-no2fa', 'Friend: 30-5000 | LIVE ADS  | Checkpoint Mail | Created: 2008-2023 | Chưa dính IP Việt', 2.28, 60000.00, 1152, '2026-07-20 20:51:15', 'active', '[]', '2026-07-20 20:19:18', '2026-07-20 20:51:15', 'via philippines co (no2fa)', NULL),
('019f82af-5d15-7086-88fe-2e4fbed58871', '019f829a-f708-7093-890d-4950e6ce2e66', '782', '019f82af-5ccf-71a8-8bc3-1ee1121bd7ed', 'Via Philippines Cổ 500-3K Friend (No2FA)', 'ads7979-782-via-philippines-co-500-3k-friend-no2fa', 'Friend: 500-5000 | LIVE ADS  | Checkpoint Mail | Created: 2008-2023 | Chưa dính IP Việt', 3.42, 90000.00, 99, '2026-07-20 20:51:15', 'active', '[]', '2026-07-20 20:19:18', '2026-07-20 20:51:15', 'via philippines co 500-3k friend (no2fa)', NULL),
('019f82af-5d19-72c8-a71e-11b3a65761fc', '019f829a-f708-7093-890d-4950e6ce2e66', '783', '019f82af-5ccf-71a8-8bc3-1ee1121bd7ed', 'Via Philippines Cổ 3-5K Friend (No2FA)', 'ads7979-783-via-philippines-co-3-5k-friend-no2fa', 'Friend: 3000-5000 | LIVE ADS  | Checkpoint Mail | Created: 2008-2023 | Chưa dính IP Việt', 4.94, 130000.00, 0, '2026-07-20 20:51:15', 'active', '[]', '2026-07-20 20:19:18', '2026-07-20 20:51:15', 'via philippines co 3-5k friend (no2fa)', NULL),
('019f82af-5d1d-729d-9fb9-318d70629bf7', '019f829a-f708-7093-890d-4950e6ce2e66', '21', '019f82af-5ccf-71a8-8bc3-1ee1121bd7ed', 'Via Saudi Arabia New (No2FA)', 'ads7979-21-via-saudi-arabia-new-no2fa', 'Friend: 30-5000 | LIVE ADS  | Checkpoint Mail | Created: 2024-2025 | Chưa dính IP Việt', 1.90, 50000.00, 1, '2026-07-20 20:51:15', 'active', '[]', '2026-07-20 20:19:18', '2026-07-20 20:51:15', 'via saudi arabia new (no2fa)', NULL),
('019f82af-5d21-713a-b47a-79f83f877aa2', '019f829a-f708-7093-890d-4950e6ce2e66', '40', '019f82af-5ccf-71a8-8bc3-1ee1121bd7ed', 'Via Algeria New (No2FA)', 'ads7979-40-via-algeria-new-no2fa', 'Friend: 30-5000 | LIVE ADS  | Checkpoint Mail | Created: 2024-2025 | Chưa dính IP Việt', 1.90, 50000.00, 0, '2026-07-20 20:51:15', 'active', '[]', '2026-07-20 20:19:18', '2026-07-20 20:51:15', 'via algeria new (no2fa)', NULL),
('019f82af-5d28-705c-aca7-43dcbd6fcd3d', '019f829a-f708-7093-890d-4950e6ce2e66', '41', '019f82af-5ccf-71a8-8bc3-1ee1121bd7ed', 'Via Congo New (No2FA)', 'ads7979-41-via-congo-new-no2fa', 'Friend: 30-5000 | LIVE ADS  | Checkpoint Mail | Created: 2024-2025 | Chưa dính IP Việt', 1.90, 50000.00, 100, '2026-07-20 20:51:15', 'active', '[]', '2026-07-20 20:19:18', '2026-07-20 20:51:15', 'via congo new (no2fa)', NULL),
('019f82af-5d2f-73e7-aaa7-c4a0acee2218', '019f829a-f708-7093-890d-4950e6ce2e66', '102', '019f82af-5ccf-71a8-8bc3-1ee1121bd7ed', 'Via Nigeria New (No2FA)', 'ads7979-102-via-nigeria-new-no2fa', 'Friend: 30-5000 | LIVE ADS  | Checkpoint Mail | Created: 2024-2025 | Chưa dính IP Việt', 1.90, 50000.00, 0, '2026-07-20 20:51:15', 'active', '[]', '2026-07-20 20:19:18', '2026-07-20 20:51:15', 'via nigeria new (no2fa)', NULL),
('019f82af-5d34-732f-8bc8-db41a916c876', '019f829a-f708-7093-890d-4950e6ce2e66', '103', '019f82af-5ccf-71a8-8bc3-1ee1121bd7ed', 'Via Nigeria Cổ (No2FA)', 'ads7979-103-via-nigeria-co-no2fa', 'Friend: 30-5000 | LIVE ADS  | Checkpoint Mail | Created: 2008-2023 | Chưa dính IP Việt', 2.28, 60000.00, 0, '2026-07-20 20:51:15', 'active', '[]', '2026-07-20 20:19:18', '2026-07-20 20:51:15', 'via nigeria co (no2fa)', NULL),
('019f82af-5d3a-7255-a696-c088bc08e046', '019f829a-f708-7093-890d-4950e6ce2e66', '42', '019f82af-5ccf-71a8-8bc3-1ee1121bd7ed', 'Via Angola New (No2FA)', 'ads7979-42-via-angola-new-no2fa', 'Friend: 30-5000 | LIVE ADS  | Checkpoint Mail | Created: 2024-2025 | Chưa dính IP Việt', 1.90, 50000.00, 2, '2026-07-20 20:51:15', 'active', '[]', '2026-07-20 20:19:18', '2026-07-20 20:51:15', 'via angola new (no2fa)', NULL),
('019f82af-5d40-73e2-833b-2afcc0819372', '019f829a-f708-7093-890d-4950e6ce2e66', '43', '019f82af-5ccf-71a8-8bc3-1ee1121bd7ed', 'Via Egypt New (No2FA)', 'ads7979-43-via-egypt-new-no2fa', 'Friend: 30-5000 | LIVE ADS  | Checkpoint Mail | Created: 2024-2025 | Chưa dính IP Việt', 1.90, 50000.00, 63, '2026-07-20 20:51:15', 'active', '[]', '2026-07-20 20:19:18', '2026-07-20 20:51:15', 'via egypt new (no2fa)', NULL),
('019f82af-5d44-737a-8f0a-6fafa0b745ba', '019f829a-f708-7093-890d-4950e6ce2e66', '44', '019f82af-5ccf-71a8-8bc3-1ee1121bd7ed', 'Via Egypt Cổ (No2FA)', 'ads7979-44-via-egypt-co-no2fa', 'Friend: 30-5000 | LIVE ADS  | Checkpoint Mail | Created: 2008-2023 | Chưa dính IP Việt', 2.28, 60000.00, 2, '2026-07-20 20:51:15', 'active', '[]', '2026-07-20 20:19:18', '2026-07-20 20:51:15', 'via egypt co (no2fa)', NULL),
('019f82af-5d49-72ce-a3f3-d95431bac142', '019f829a-f708-7093-890d-4950e6ce2e66', '45', '019f82af-5ccf-71a8-8bc3-1ee1121bd7ed', 'Via Madagascar New (No2FA)', 'ads7979-45-via-madagascar-new-no2fa', 'Friend: 30-5000 | LIVE ADS  | Checkpoint Mail | Created: 2024-2025 | Chưa dính IP Việt', 1.90, 50000.00, 0, '2026-07-20 20:51:15', 'active', '[]', '2026-07-20 20:19:18', '2026-07-20 20:51:15', 'via madagascar new (no2fa)', NULL),
('019f82af-5d4e-70e8-8fe0-3f94733c7161', '019f829a-f708-7093-890d-4950e6ce2e66', '55', '019f82af-5ccf-71a8-8bc3-1ee1121bd7ed', 'Via Timor Leste New (No2FA)', 'ads7979-55-via-timor-leste-new-no2fa', 'Friend: 30-5000 | LIVE ADS  | Checkpoint Mail | Created: 2024-2025 | Chưa dính IP Việt', 1.90, 50000.00, 11, '2026-07-20 20:51:15', 'active', '[]', '2026-07-20 20:19:18', '2026-07-20 20:51:15', 'via timor leste new (no2fa)', NULL),
('019f82af-5d53-7379-bdaa-71337702a10b', '019f829a-f708-7093-890d-4950e6ce2e66', '46', '019f82af-5ccf-71a8-8bc3-1ee1121bd7ed', 'Via Brazil New (No2FA)', 'ads7979-46-via-brazil-new-no2fa', 'Friend: 30-5000 | LIVE ADS  | Checkpoint Mail | Created: 2024-2025 | Chưa dính IP Việt', 3.04, 80000.00, 0, '2026-07-20 20:51:15', 'active', '[]', '2026-07-20 20:19:18', '2026-07-20 20:51:15', 'via brazil new (no2fa)', NULL),
('019f82af-5d57-7241-8ab9-44fa4ac719c5', '019f829a-f708-7093-890d-4950e6ce2e66', '47', '019f82af-5ccf-71a8-8bc3-1ee1121bd7ed', 'Via Brazil Cổ (No2FA)', 'ads7979-47-via-brazil-co-no2fa', 'Friend: 30-5000 | LIVE ADS  | Checkpoint Mail | Created: 2008-2023 | Chưa dính IP Việt', 4.56, 120000.00, 108, '2026-07-20 20:51:15', 'active', '[]', '2026-07-20 20:19:18', '2026-07-20 20:51:15', 'via brazil co (no2fa)', NULL),
('019f82af-5d5c-7011-8bec-6fbcb93e0188', '019f829a-f708-7093-890d-4950e6ce2e66', '48', '019f82af-5ccf-71a8-8bc3-1ee1121bd7ed', 'Via Peru New (No2FA)', 'ads7979-48-via-peru-new-no2fa', 'Friend: 30-5000 | LIVE ADS  | Checkpoint Mail | Created: 2024-2025 | Chưa dính IP Việt', 2.66, 70000.00, 111, '2026-07-20 20:51:15', 'active', '[]', '2026-07-20 20:19:18', '2026-07-20 20:51:15', 'via peru new (no2fa)', NULL),
('019f82af-5d60-733a-9093-08d38acf0477', '019f829a-f708-7093-890d-4950e6ce2e66', '49', '019f82af-5ccf-71a8-8bc3-1ee1121bd7ed', 'Via Venezuela New (No2FA)', 'ads7979-49-via-venezuela-new-no2fa', 'Friend: 30-5000 | LIVE ADS  | Checkpoint Mail | Created: 2024-2025 | Chưa dính IP Việt', 2.28, 60000.00, 0, '2026-07-20 20:51:15', 'active', '[]', '2026-07-20 20:19:18', '2026-07-20 20:51:15', 'via venezuela new (no2fa)', NULL),
('019f82af-5d65-7181-957f-03ce4597aaf7', '019f829a-f708-7093-890d-4950e6ce2e66', '52', '019f82af-5ccf-71a8-8bc3-1ee1121bd7ed', 'Via Ecuador New (No2FA)', 'ads7979-52-via-ecuador-new-no2fa', 'Friend: 30-5000 | LIVE ADS  | Checkpoint Mail | Created: 2024-2025 | Chưa dính IP Việt', 2.28, 60000.00, 3, '2026-07-20 20:51:15', 'active', '[]', '2026-07-20 20:19:18', '2026-07-20 20:51:15', 'via ecuador new (no2fa)', NULL),
('019f82af-5d69-72d6-b3f3-b17c618ae475', '019f829a-f708-7093-890d-4950e6ce2e66', '54', '019f82af-5ccf-71a8-8bc3-1ee1121bd7ed', 'Via Colombia Cổ (No2FA)', 'ads7979-54-via-colombia-co-no2fa', 'Friend: 30-5000 | LIVE ADS  | Checkpoint Mail | Created: 2008-2022 | Chưa dính IP Việt', 2.28, 60000.00, 3032, '2026-07-20 20:51:15', 'active', '[]', '2026-07-20 20:19:18', '2026-07-20 20:51:15', 'via colombia co (no2fa)', NULL),
('019f82af-5d70-72ab-b736-a1f40fad8ce2', '019f829a-f708-7093-890d-4950e6ce2e66', '15', '019f82af-5d6c-71c4-a32c-db49989a6cc3', 'Via Ngoại New', 'ads7979-15-via-ngoai-new', 'Friend: 30-1000 | LIVE ADS | Created: 2025-2026 | Chưa dính IP Việt | Checkpoint Mail', 0.95, 25000.00, 4474, '2026-07-20 20:51:15', 'active', '[]', '2026-07-20 20:19:18', '2026-07-20 20:51:15', 'via ngoai new', NULL),
('019f82af-5d74-73c8-9feb-49ea1222bd5f', '019f829a-f708-7093-890d-4950e6ce2e66', '56', '019f82af-5d6c-71c4-a32c-db49989a6cc3', 'Via Ngoại Cổ', 'ads7979-56-via-ngoai-co', 'Friend: 30-5000 | LIVE ADS  | Created: 2008-2024 | Chưa dính IP Việt', 2.28, 60000.00, 0, '2026-07-20 20:51:15', 'active', '[]', '2026-07-20 20:19:18', '2026-07-20 20:51:15', 'via ngoai co', NULL),
('019f82af-5d78-72cd-a7df-cf15e142d6a8', '019f829a-f708-7093-890d-4950e6ce2e66', '58', '019f82af-5d6c-71c4-a32c-db49989a6cc3', 'Via Ngoại Cổ 3-5K Friend', 'ads7979-58-via-ngoai-co-3-5k-friend', 'Friend: 3000-5000 | LIVE ADS  | Created: 2008-2024 | Chưa dính IP Việt', 6.09, 160000.00, 0, '2026-07-20 20:51:15', 'active', '[]', '2026-07-20 20:19:18', '2026-07-20 20:51:15', 'via ngoai co 3-5k friend', NULL),
('019f82af-5d7c-7131-a05b-8328f587d607', '019f829a-f708-7093-890d-4950e6ce2e66', '61', '019f82af-5d6c-71c4-a32c-db49989a6cc3', 'Via Nepal New', 'ads7979-61-via-nepal-new', 'Friend: 30-5000 | LIVE ADS | Created: 2024-2025 | Chưa dính IP Việt', 2.66, 70000.00, 0, '2026-07-20 20:51:15', 'active', '[]', '2026-07-20 20:19:18', '2026-07-20 20:51:15', 'via nepal new', NULL),
('019f82af-5d7f-7126-8818-bb4257798bb2', '019f829a-f708-7093-890d-4950e6ce2e66', '62', '019f82af-5d6c-71c4-a32c-db49989a6cc3', 'Via Nepal Cổ', 'ads7979-62-via-nepal-co', 'Friend: 30-5000 | LIVE ADS | Created: 2008-2024 | Chưa dính IP Việt', 3.42, 90000.00, 0, '2026-07-20 20:51:15', 'active', '[]', '2026-07-20 20:19:18', '2026-07-20 20:51:15', 'via nepal co', NULL),
('019f82af-5d83-72fb-a7f4-db241031fd80', '019f829a-f708-7093-890d-4950e6ce2e66', '101', '019f82af-5d6c-71c4-a32c-db49989a6cc3', 'Via Egypt Cổ', 'ads7979-101-via-egypt-co', 'Friend: 30-5000 | LIVE ADS | Created: 2008-2024 | Chưa dính IP Việt', 3.04, 80000.00, 0, '2026-07-20 20:51:15', 'active', '[]', '2026-07-20 20:19:18', '2026-07-20 20:51:15', 'via egypt co', NULL),
('019f82af-5d88-703a-8b22-95efd185b5fd', '019f829a-f708-7093-890d-4950e6ce2e66', '63', '019f82af-5d6c-71c4-a32c-db49989a6cc3', 'Via Philippines Cổ', 'ads7979-63-via-philippines-co', 'Friend: 30-5000 | LIVE ADS | Created: 2008-2023 | Chưa dính IP Việt', 3.80, 100000.00, 18, '2026-07-20 20:51:15', 'active', '[]', '2026-07-20 20:19:18', '2026-07-20 20:51:15', 'via philippines co', NULL),
('019f82af-5d90-70cc-9bd6-4e325ae612dd', '019f829a-f708-7093-890d-4950e6ce2e66', '64', '019f82af-5d6c-71c4-a32c-db49989a6cc3', 'Via Philippines Cổ 500-3K Friend', 'ads7979-64-via-philippines-co-500-3k-friend', 'Friend: 500-3000 | LIVE ADS | Created: 2008-2023 | Chưa dính IP Việt', 5.70, 150000.00, 449, '2026-07-20 20:51:15', 'active', '[]', '2026-07-20 20:19:18', '2026-07-20 20:51:15', 'via philippines co 500-3k friend', NULL),
('019f82af-5d95-7023-916b-bd050bd4625d', '019f829a-f708-7093-890d-4950e6ce2e66', '65', '019f82af-5d6c-71c4-a32c-db49989a6cc3', 'Via Philippines Cổ 3K-5K Friend', 'ads7979-65-via-philippines-co-3k-5k-friend', 'Friend: 3000-5000 | LIVE ADS | Created: 2008-2023 | Chưa dính IP Việt', 7.23, 190000.00, 102, '2026-07-20 20:51:15', 'active', '[]', '2026-07-20 20:19:18', '2026-07-20 20:51:15', 'via philippines co 3k-5k friend', NULL),
('019f82af-5d9a-71ee-a68c-f2ad40c83012', '019f829a-f708-7093-890d-4950e6ce2e66', '66', '019f82af-5d6c-71c4-a32c-db49989a6cc3', 'Via Indonesia Cổ', 'ads7979-66-via-indonesia-co', 'Friend: 30-5000 | LIVE ADS | Created: 2008-2023 | Chưa dính IP Việt', 3.80, 100000.00, 2088, '2026-07-20 20:51:15', 'active', '[]', '2026-07-20 20:19:18', '2026-07-20 20:51:15', 'via indonesia co', NULL),
('019f82af-5d9e-73bf-938f-4609339c5890', '019f829a-f708-7093-890d-4950e6ce2e66', '67', '019f82af-5d6c-71c4-a32c-db49989a6cc3', 'Via Indonesia Cổ 500-3K Friend', 'ads7979-67-via-indonesia-co-500-3k-friend', 'Friend: 500-3000 | LIVE ADS | Created: 2008-2022 | Chưa dính IP Việt', 5.70, 150000.00, 904, '2026-07-20 20:51:15', 'active', '[]', '2026-07-20 20:19:18', '2026-07-20 20:51:15', 'via indonesia co 500-3k friend', NULL),
('019f82af-5da2-70ef-8814-b29d95655644', '019f829a-f708-7093-890d-4950e6ce2e66', '68', '019f82af-5d6c-71c4-a32c-db49989a6cc3', 'Via Indonesia Cổ 3-5K Friend', 'ads7979-68-via-indonesia-co-3-5k-friend', 'Friend: 3000-5000 | LIVE ADS | Created: 2008-2022 | Chưa dính IP Việt', 7.23, 190000.00, 74, '2026-07-20 20:51:15', 'active', '[]', '2026-07-20 20:19:18', '2026-07-20 20:51:15', 'via indonesia co 3-5k friend', NULL),
('019f82af-5da6-7171-8c85-75baab2f8866', '019f829a-f708-7093-890d-4950e6ce2e66', '69', '019f82af-5d6c-71c4-a32c-db49989a6cc3', 'Via Thailand Cổ', 'ads7979-69-via-thailand-co', 'Friend: 30-5000 | LIVE ADS | Created: 2008-2022 | Chưa dính IP Việt', 5.70, 150000.00, 0, '2026-07-20 20:51:15', 'active', '[]', '2026-07-20 20:19:18', '2026-07-20 20:51:15', 'via thailand co', NULL),
('019f82af-5daa-71d7-a170-54d65f9b8647', '019f829a-f708-7093-890d-4950e6ce2e66', '2735', '019f82af-5d6c-71c4-a32c-db49989a6cc3', 'Via Peru Cổ', 'ads7979-2735-via-peru-co', 'Friend: 30-5000 | LIVE ADS | Created: 2008-2022 | Chưa dính IP Việt', 3.80, 100000.00, 0, '2026-07-20 20:51:15', 'active', '[]', '2026-07-20 20:19:18', '2026-07-20 20:51:15', 'via peru co', NULL),
('019f82af-5dad-72ad-8d90-d800a31d2983', '019f829a-f708-7093-890d-4950e6ce2e66', '70', '019f82af-5d6c-71c4-a32c-db49989a6cc3', 'Via Colombia Cổ', 'ads7979-70-via-colombia-co', 'Friend: 30-5000 | LIVE ADS | Created: 2008-2023 | Chưa dính IP Việt', 3.80, 100000.00, 242, '2026-07-20 20:51:15', 'active', '[]', '2026-07-20 20:19:18', '2026-07-20 20:51:15', 'via colombia co', NULL),
('019f82af-5db1-7375-a95f-b6f99b878aec', '019f829a-f708-7093-890d-4950e6ce2e66', '2690', '019f82af-5d6c-71c4-a32c-db49989a6cc3', 'Via Colombia Cổ 500-3K Friend', 'ads7979-2690-via-colombia-co-500-3k-friend', 'Friend: 500-3000 | LIVE ADS | Created: 2008-2023 | Chưa dính IP Việt', 5.70, 150000.00, 0, '2026-07-20 20:51:15', 'active', '[]', '2026-07-20 20:19:18', '2026-07-20 20:51:15', 'via colombia co 500-3k friend', NULL),
('019f82af-5db4-7233-9a47-d31623d6c113', '019f829a-f708-7093-890d-4950e6ce2e66', '2691', '019f82af-5d6c-71c4-a32c-db49989a6cc3', 'Via Colombia Cổ 3-5K Friend', 'ads7979-2691-via-colombia-co-3-5k-friend', 'Friend: 3000-5000 | LIVE ADS | Created: 2008-2023 | Chưa dính IP Việt', 7.23, 190000.00, 1, '2026-07-20 20:51:15', 'active', '[]', '2026-07-20 20:19:18', '2026-07-20 20:51:15', 'via colombia co 3-5k friend', NULL),
('019f82af-5db9-706d-bb65-81714314d1c0', '019f829a-f708-7093-890d-4950e6ce2e66', '71', '019f82af-5d6c-71c4-a32c-db49989a6cc3', 'Via Brazil Cổ', 'ads7979-71-via-brazil-co', 'Friend: 30-5000 | LIVE ADS | Created: 2008-2023 | Chưa dính IP Việt', 5.70, 150000.00, 0, '2026-07-20 20:51:15', 'active', '[]', '2026-07-20 20:19:18', '2026-07-20 20:51:15', 'via brazil co', NULL),
('019f82af-5dbc-7296-b6d0-cf05288ffd5c', '019f829a-f708-7093-890d-4950e6ce2e66', '72', '019f82af-5d6c-71c4-a32c-db49989a6cc3', 'Via Germany Cổ', 'ads7979-72-via-germany-co', 'Friend: 30-5000 | LIVE ADS | Created: 2008-2021 | Chưa dính IP Việt', 6.05, 159000.00, 0, '2026-07-20 20:51:15', 'active', '[]', '2026-07-20 20:19:18', '2026-07-20 20:51:15', 'via germany co', NULL),
('019f82af-5dc0-7367-a2eb-d8612edba60a', '019f829a-f708-7093-890d-4950e6ce2e66', '6732', '019f82af-5d6c-71c4-a32c-db49989a6cc3', 'Via Spain Cổ', 'ads7979-6732-via-spain-co', 'Friend: 30-5000 | LIVE ADS | Created: 2008-2021 | Chưa dính IP Việt', 6.05, 159000.00, 0, '2026-07-20 20:51:15', 'active', '[]', '2026-07-20 20:19:18', '2026-07-20 20:51:15', 'via spain co', NULL),
('019f82af-5dc4-7077-b495-20d2288bc187', '019f829a-f708-7093-890d-4950e6ce2e66', '3104', '019f82af-5d6c-71c4-a32c-db49989a6cc3', 'Via Bulgari Cổ', 'ads7979-3104-via-bulgari-co', 'Friend: 30-5000 | LIVE ADS | Created: 2008-2021 | Chưa dính IP Việt', 6.05, 159000.00, 0, '2026-07-20 20:51:15', 'active', '[]', '2026-07-20 20:19:18', '2026-07-20 20:51:15', 'via bulgari co', NULL),
('019f82af-5dc9-71dd-99e6-c6d9983c92eb', '019f829a-f708-7093-890d-4950e6ce2e66', '3103', '019f82af-5d6c-71c4-a32c-db49989a6cc3', 'Via Switzerland Cổ', 'ads7979-3103-via-switzerland-co', 'Friend: 30-5000 | LIVE ADS | Created: 2008-2021 | Chưa dính IP Việt', 6.05, 159000.00, 0, '2026-07-20 20:51:15', 'active', '[]', '2026-07-20 20:19:18', '2026-07-20 20:51:15', 'via switzerland co', NULL),
('019f82af-5dcd-73cf-b259-dc5227183384', '019f829a-f708-7093-890d-4950e6ce2e66', '792', '019f82af-5d6c-71c4-a32c-db49989a6cc3', 'Via Poland Cổ', 'ads7979-792-via-poland-co', 'Friend: 30-5000 | LIVE ADS | Created: 2008-2021 | Chưa dính IP Việt', 6.05, 159000.00, 225, '2026-07-20 20:51:15', 'active', '[]', '2026-07-20 20:19:18', '2026-07-20 20:51:15', 'via poland co', NULL),
('019f82af-5dd0-7092-a7ee-2eb384b8cf10', '019f829a-f708-7093-890d-4950e6ce2e66', '6730', '019f82af-5d6c-71c4-a32c-db49989a6cc3', 'Via Turkey Cổ', 'ads7979-6730-via-turkey-co', 'Friend: 30-5000 | LIVE ADS | Created: 2008-2021 | Chưa dính IP Việt', 6.05, 159000.00, 0, '2026-07-20 20:51:15', 'active', '[]', '2026-07-20 20:19:18', '2026-07-20 20:51:15', 'via turkey co', NULL),
('019f82af-5dd3-71a2-88c5-e8970244bcb1', '019f829a-f708-7093-890d-4950e6ce2e66', '73', '019f82af-5d6c-71c4-a32c-db49989a6cc3', 'Via Slovakia', 'ads7979-73-via-slovakia', 'Friend: 30-5000 | LIVE ADS | Created: 2008-2021 | Chưa dính IP Việt', 6.05, 159000.00, 549, '2026-07-20 20:51:15', 'active', '[]', '2026-07-20 20:19:18', '2026-07-20 20:51:15', 'via slovakia', NULL),
('019f82af-5dd7-7148-b6ac-17397f7ee476', '019f829a-f708-7093-890d-4950e6ce2e66', '74', '019f82af-5d6c-71c4-a32c-db49989a6cc3', 'Via Czech Republic Cổ', 'ads7979-74-via-czech-republic-co', 'Friend: 30-5000 | LIVE ADS | Created: 2008-2021 | Chưa dính IP Việt', 6.05, 159000.00, 41, '2026-07-20 20:51:15', 'active', '[]', '2026-07-20 20:19:18', '2026-07-20 20:51:15', 'via czech republic co', NULL),
('019f82af-5ddc-70ef-8089-316fa5672286', '019f829a-f708-7093-890d-4950e6ce2e66', '796', '019f82af-5d6c-71c4-a32c-db49989a6cc3', 'Via Hungary Cổ', 'ads7979-796-via-hungary-co', 'Friend: 30-5000 | LIVE ADS | Created: 2008-2021 | Chưa dính IP Việt', 6.05, 159000.00, 0, '2026-07-20 20:51:15', 'active', '[]', '2026-07-20 20:19:18', '2026-07-20 20:51:15', 'via hungary co', NULL),
('019f82af-5de0-701b-91b9-61296e5417ca', '019f829a-f708-7093-890d-4950e6ce2e66', '100', '019f82af-5d6c-71c4-a32c-db49989a6cc3', 'Via Austria Cổ', 'ads7979-100-via-austria-co', 'Friend: 30-5000 | LIVE ADS | Created: 2008-2021 | Chưa dính IP Việt', 6.05, 159000.00, 0, '2026-07-20 20:51:15', 'active', '[]', '2026-07-20 20:19:18', '2026-07-20 20:51:15', 'via austria co', NULL),
('019f82af-5de3-71f4-bb54-b322495dbbb6', '019f829a-f708-7093-890d-4950e6ce2e66', '805', '019f82af-5d6c-71c4-a32c-db49989a6cc3', 'Via United Kingdom Cổ', 'ads7979-805-via-united-kingdom-co', 'Friend: 30-5000 | LIVE ADS | Created: 2008-2021 | Chưa dính IP Việt', 7.61, 200000.00, 0, '2026-07-20 20:51:15', 'active', '[]', '2026-07-20 20:19:18', '2026-07-20 20:51:15', 'via united kingdom co', NULL),
('019f82af-5de7-704c-adba-7e1e4bdaf6c0', '019f829a-f708-7093-890d-4950e6ce2e66', '75', '019f82af-5d6c-71c4-a32c-db49989a6cc3', 'Via United States Cổ', 'ads7979-75-via-united-states-co', 'Friend: 30-5000 | LIVE ADS | Created: 2008-2021 | Chưa dính IP Việt', 11.41, 300000.00, 88, '2026-07-20 20:51:15', 'active', '[]', '2026-07-20 20:19:18', '2026-07-20 20:51:15', 'via united states co', NULL),
('019f82af-5ded-70dc-be58-ad0b46ae9215', '019f829a-f708-7093-890d-4950e6ce2e66', '76', '019f82af-5dea-7360-936b-3aa19da2dd52', 'Via Ngoại New DIE ADS (No2FA)', 'ads7979-76-via-ngoai-new-die-ads-no2fa', 'Thích hợp Seeding | DIE ADS | Friend : 0-5000 | Checkpoint Mail | Created: 2024-2025', 0.57, 15000.00, 0, '2026-07-20 20:51:15', 'active', '[]', '2026-07-20 20:19:18', '2026-07-20 20:51:15', 'via ngoai new die ads (no2fa)', NULL),
('019f82af-5df2-704a-b7f7-3aad8b96f86f', '019f829a-f708-7093-890d-4950e6ce2e66', '18', '019f82af-5dea-7360-936b-3aa19da2dd52', 'Via Ngoại New Die Ads', 'ads7979-18-via-ngoai-new-die-ads', 'Thích hợp Seeding | DIE ADS | Friend : 30-5000 | Checkpoint Mail | Created: 2024-2025', 0.76, 20000.00, 783, '2026-07-20 20:51:15', 'active', '[]', '2026-07-20 20:19:18', '2026-07-20 20:51:15', 'via ngoai new die ads', NULL),
('019f82af-5df7-736f-8780-9e184051748e', '019f829a-f708-7093-890d-4950e6ce2e66', '9', '019f82af-5dea-7360-936b-3aa19da2dd52', 'Via Ngoại Cổ DIE ADS (No2FA)', 'ads7979-9-via-ngoai-co-die-ads-no2fa', 'Thích hợp Seeding | DIE ADS | Friend : 0-5000 | Checkpoint Mail | Created: 2008-2024', 0.95, 25000.00, 84, '2026-07-20 20:51:15', 'active', '[]', '2026-07-20 20:19:18', '2026-07-20 20:51:15', 'via ngoai co die ads (no2fa)', NULL),
('019f82af-5dfc-7036-9461-b72ccea41a89', '019f829a-f708-7093-890d-4950e6ce2e66', '17', '019f82af-5dea-7360-936b-3aa19da2dd52', 'Via Ngoại Cổ DIE ADS', 'ads7979-17-via-ngoai-co-die-ads', 'Thích hợp Seeding | DIE ADS | Friend : 30-1000 | Created: 2008-2024', 1.90, 50000.00, 684, '2026-07-20 20:51:15', 'active', '[]', '2026-07-20 20:19:18', '2026-07-20 20:51:15', 'via ngoai co die ads', NULL),
('019f82af-5e01-7368-a7dc-83242bcb38ca', '019f829a-f708-7093-890d-4950e6ce2e66', '77', '019f82af-5dea-7360-936b-3aa19da2dd52', 'Via Ngoại Cổ DIE ADS 1-3K Friend', 'ads7979-77-via-ngoai-co-die-ads-1-3k-friend', 'Thích hợp Seeding | DIE ADS | Friend : 1000-3000 | Năm tạo: 2008-2024', 3.80, 100000.00, 0, '2026-07-20 20:51:15', 'active', '[]', '2026-07-20 20:19:18', '2026-07-20 20:51:15', 'via ngoai co die ads 1-3k friend', NULL),
('019f82af-5e06-7138-b289-5d0de5719ccb', '019f829a-f708-7093-890d-4950e6ce2e66', '78', '019f82af-5dea-7360-936b-3aa19da2dd52', 'Via Ngoại Cổ DIE ADS 3-5K Friend', 'ads7979-78-via-ngoai-co-die-ads-3-5k-friend', 'Thích hợp Seeding | DIE ADS | Friend : 3000-5000 | Năm tạo: 2008-2024', 4.94, 130000.00, 4, '2026-07-20 20:51:15', 'active', '[]', '2026-07-20 20:19:18', '2026-07-20 20:51:15', 'via ngoai co die ads 3-5k friend', NULL),
('019f82af-5e0c-7056-bb52-6b9ad2be5dbe', '019f829a-f708-7093-890d-4950e6ce2e66', '578', '019f82af-5dea-7360-936b-3aa19da2dd52', 'Via United States Cổ DIE ADS', 'ads7979-578-via-united-states-co-die-ads', 'Thích hợp Seeding, Spy | DIE ADS | Friend : 30-5000 | Created: 2010-2021', 7.57, 199000.00, 129, '2026-07-20 20:51:15', 'active', '[]', '2026-07-20 20:19:18', '2026-07-20 20:51:15', 'via united states co die ads', NULL),
('019f82af-5e13-7061-a3ea-c27dc30f8c62', '019f829a-f708-7093-890d-4950e6ce2e66', '79', '019f82af-5e0e-729a-a8e2-59ef4d9e2997', 'Clone Ngoại Random', 'ads7979-79-clone-ngoai-random', 'Có Avatar + Cover + Info | Created: > 2 day | Created Ads: > 2 day | Verify Hotmail | Đã bật 2FA', 0.19, 5000.00, 0, '2026-07-20 20:51:15', 'active', '[]', '2026-07-20 20:19:18', '2026-07-20 20:51:15', 'clone ngoai random', NULL),
('019f82af-5e18-736d-8ccd-b6bd13aba48f', '019f829a-f708-7093-890d-4950e6ce2e66', '80', '019f82af-5e0e-729a-a8e2-59ef4d9e2997', 'Clone Ngoại Random Die Ads', 'ads7979-80-clone-ngoai-random-die-ads', 'Có Avatar + Cover + Info | Created: > 3 month | Very Hotmail | Đã Bật 2FA', 0.06, 1500.00, 0, '2026-07-20 20:51:15', 'active', '[]', '2026-07-20 20:19:18', '2026-07-20 20:51:15', 'clone ngoai random die ads', NULL),
('019f82af-5e1d-708e-ad4c-8ccc8681fa4e', '019f829a-f708-7093-890d-4950e6ce2e66', '81', '019f82af-5e0e-729a-a8e2-59ef4d9e2997', 'Clone Ngoại Random 1-30 Friend', 'ads7979-81-clone-ngoai-random-1-30-friend', 'Có Avatar + Cover + Info | Created: > 1 month | Very Hotmail | Đã Bật 2FA | Friend: 1-30', 0.23, 6000.00, 0, '2026-07-20 20:51:15', 'active', '[]', '2026-07-20 20:19:18', '2026-07-20 20:51:15', 'clone ngoai random 1-30 friend', NULL),
('019f82af-5e22-7062-ae8e-7cb64f67e698', '019f829a-f708-7093-890d-4950e6ce2e66', '82', '019f82af-5e0e-729a-a8e2-59ef4d9e2997', 'Clone Thailand', 'ads7979-82-clone-thailand', 'Có Avatar + Cover + Info | Created: > 2 month | Created Ads: > 2 month | Verify Hotmail | Đã bật 2FA', 0.30, 8000.00, 0, '2026-07-20 20:51:15', 'active', '[]', '2026-07-20 20:19:18', '2026-07-20 20:51:15', 'clone thailand', NULL),
('019f82af-5e27-73c6-9e74-5d19f918bae9', '019f829a-f708-7093-890d-4950e6ce2e66', '83', '019f82af-5e0e-729a-a8e2-59ef4d9e2997', 'Clone Hongkong', 'ads7979-83-clone-hongkong', 'Có Avatar + Cover + Info | Created: > 2 month | Created Ads: > 2 month | Verify Hotmail | Đã bật 2FA', 0.30, 8000.00, 0, '2026-07-20 20:51:15', 'active', '[]', '2026-07-20 20:19:18', '2026-07-20 20:51:15', 'clone hongkong', NULL),
('019f82af-5e2b-7297-a295-4043c5de8013', '019f829a-f708-7093-890d-4950e6ce2e66', '84', '019f82af-5e0e-729a-a8e2-59ef4d9e2997', 'Clone Singapore', 'ads7979-84-clone-singapore', 'Có Avatar + Cover + Info | Created: > 2 month | Created Ads: > 2 month | Verify Hotmail | Đã bật 2FA', 0.30, 8000.00, 0, '2026-07-20 20:51:15', 'active', '[]', '2026-07-20 20:19:18', '2026-07-20 20:51:15', 'clone singapore', NULL),
('019f82af-5e30-73cf-97c8-c379a04ee167', '019f829a-f708-7093-890d-4950e6ce2e66', '85', '019f82af-5e0e-729a-a8e2-59ef4d9e2997', 'Clone Taiwan', 'ads7979-85-clone-taiwan', 'Có Avatar + Cover + Info | Created: > 2 month | Created Ads: > 2 month | Verify Hotmail | Đã bật 2FA', 0.30, 8000.00, 0, '2026-07-20 20:51:15', 'active', '[]', '2026-07-20 20:19:18', '2026-07-20 20:51:15', 'clone taiwan', NULL),
('019f82af-5e35-72e2-8aa7-911e88cdc393', '019f829a-f708-7093-890d-4950e6ce2e66', '86', '019f82af-5e0e-729a-a8e2-59ef4d9e2997', 'Clone Ukraine', 'ads7979-86-clone-ukraine', 'Có Avatar + Cover + Info | Created: > 2 month | Created Ads: > 2 month | Verify Hotmail | Đã bật 2FA', 0.30, 8000.00, 0, '2026-07-20 20:51:15', 'active', '[]', '2026-07-20 20:19:18', '2026-07-20 20:51:15', 'clone ukraine', NULL),
('019f82af-5e39-70a3-9115-fabfca9ded3f', '019f829a-f708-7093-890d-4950e6ce2e66', '87', '019f82af-5e0e-729a-a8e2-59ef4d9e2997', 'Clone Germany', 'ads7979-87-clone-germany', 'Có Avatar + Cover + Info | Created: > 2 month | Created Ads: > 2 month | Verify Hotmail | Đã bật 2FA', 0.30, 8000.00, 0, '2026-07-20 20:51:15', 'active', '[]', '2026-07-20 20:19:18', '2026-07-20 20:51:15', 'clone germany', NULL),
('019f82af-5e3d-7397-87d2-50d59da8dd69', '019f829a-f708-7093-890d-4950e6ce2e66', '88', '019f82af-5e0e-729a-a8e2-59ef4d9e2997', 'Clone France', 'ads7979-88-clone-france', 'Có Avatar + Cover + Info | Created: > 2 month | Created Ads: > 2 month | Verify Hotmail | Đã bật 2FA', 0.30, 8000.00, 0, '2026-07-20 20:51:15', 'active', '[]', '2026-07-20 20:19:18', '2026-07-20 20:51:15', 'clone france', NULL),
('019f82af-5e40-71f1-b7ee-ed68bce180e7', '019f829a-f708-7093-890d-4950e6ce2e66', '89', '019f82af-5e0e-729a-a8e2-59ef4d9e2997', 'Clone Canada', 'ads7979-89-clone-canada', 'Có Avatar + Cover + Info | Created: > 2 month | Created Ads: > 2 month | Verify Hotmail | Đã bật 2FA', 0.30, 8000.00, 0, '2026-07-20 20:51:15', 'active', '[]', '2026-07-20 20:19:18', '2026-07-20 20:51:15', 'clone canada', NULL),
('019f82af-5e43-7167-b716-768b2b521324', '019f829a-f708-7093-890d-4950e6ce2e66', '90', '019f82af-5e0e-729a-a8e2-59ef4d9e2997', 'Clone Australia', 'ads7979-90-clone-australia', 'Có Avatar + Cover + Info | Created: > 2 month | Created Ads: > 2 month | Verify Hotmail | Đã bật 2FA', 0.30, 8000.00, 0, '2026-07-20 20:51:15', 'active', '[]', '2026-07-20 20:19:18', '2026-07-20 20:51:15', 'clone australia', NULL),
('019f82af-5e47-7338-bdcc-c0cd6ca11e9b', '019f829a-f708-7093-890d-4950e6ce2e66', '2688', '019f82af-5e0e-729a-a8e2-59ef4d9e2997', 'Clone United Kingdom', 'ads7979-2688-clone-united-kingdom', 'Có Avatar + Cover + Info | Created: > 2 month | Created Ads: > 2 month | Verify Hotmail | Đã bật 2FA', 0.30, 8000.00, 0, '2026-07-20 20:51:15', 'active', '[]', '2026-07-20 20:19:18', '2026-07-20 20:51:15', 'clone united kingdom', NULL),
('019f82af-5e4a-7007-9f1c-6f2f968befd9', '019f829a-f708-7093-890d-4950e6ce2e66', '91', '019f82af-5e0e-729a-a8e2-59ef4d9e2997', 'Clone United States', 'ads7979-91-clone-united-states', 'Có Avatar + Cover + Info | Created: > 5 day | Created Ads: > 5 day | Verify Hotmail | Đã bật 2FA', 0.23, 6000.00, 0, '2026-07-20 20:51:15', 'active', '[]', '2026-07-20 20:19:18', '2026-07-20 20:51:15', 'clone united states', NULL),
('019f82af-5e50-73e3-bcad-d1a749cc715b', '019f829a-f708-7093-890d-4950e6ce2e66', '92', '019f82af-5e4d-730c-994f-86a322e0e883', 'Clone Việt Nam Verify Gmail (No2FA)', 'ads7979-92-clone-viet-nam-verify-gmail-no2fa', 'Có Avatar + Cover + Info | Ngày tạo: > 2 month | Chưa bật 2FA | Very Gmail | Friend: 0-20', 0.19, 5000.00, 178, '2026-07-20 20:51:15', 'active', '[]', '2026-07-20 20:19:18', '2026-07-20 20:51:15', 'clone viet nam verify gmail (no2fa)', NULL),
('019f82af-5e54-7312-abf9-cb982d5fa23f', '019f829a-f708-7093-890d-4950e6ce2e66', '93', '019f82af-5e4d-730c-994f-86a322e0e883', 'Clone Việt Nam Verify Hotmail', 'ads7979-93-clone-viet-nam-verify-hotmail', 'Có Avatar + Cover + Info | Ngày tạo: > 3 month | Đã bật 2FA | Very Hotmail', 0.23, 6000.00, 2891, '2026-07-20 20:51:15', 'active', '[]', '2026-07-20 20:19:18', '2026-07-20 20:51:15', 'clone viet nam verify hotmail', NULL),
('019f82af-5e5b-71f0-862c-8d15f88b57bb', '019f829a-f708-7093-890d-4950e6ce2e66', '94', '019f82af-5e56-7144-8a42-34ec76467805', 'Hotmail Trusted [GRAPH API]', 'ads7979-94-hotmail-trusted-graph-api', 'Live for 6-12 months | Không cần very SĐT | Recommend to use Graph API for access', 0.01, 300.00, 6968, '2026-07-20 20:51:15', 'active', '[]', '2026-07-20 20:19:18', '2026-07-20 20:51:15', 'hotmail trusted [graph api]', NULL),
('019f82af-5e5f-713e-b6b1-4ab72a40a0a1', '019f829a-f708-7093-890d-4950e6ce2e66', '95', '019f82af-5e56-7144-8a42-34ec76467805', 'Outlook Trusted [GRAPH API]', 'ads7979-95-outlook-trusted-graph-api', 'Live for 6-12 months | Không cần very SĐT | Recommend to use Graph API for access', 0.01, 300.00, 5899, '2026-07-20 20:51:15', 'active', '[]', '2026-07-20 20:19:18', '2026-07-20 20:51:15', 'outlook trusted [graph api]', NULL),
('019f82af-5e64-70cd-a368-e0d623110e87', '019f829a-f708-7093-890d-4950e6ce2e66', '8326', '019f82af-5e56-7144-8a42-34ec76467805', 'Hotmail Live Vĩnh Viễn', 'ads7979-8326-hotmail-live-vinh-vien', 'Live vĩnh viễn | Đã thêm mail khôi phục Fviainboxes | Recommend to use Graph API for access', 0.04, 1000.00, 5681, '2026-07-20 20:51:15', 'active', '[]', '2026-07-20 20:19:18', '2026-07-20 20:51:15', 'hotmail live vinh vien', NULL),
('019f82af-5e6c-7202-932e-f9f1747f0cfa', '019f829a-f708-7093-890d-4950e6ce2e66', '96', '019f82af-5e67-7153-9ee1-9c332fc1fd73', 'Clone Instagram US', 'ads7979-96-clone-instagram-us', 'Reg Android | Verify Phone | Đã Bật 2FA | Name US | Created: > 15 day', 0.06, 1500.00, 0, '2026-07-20 20:51:15', 'active', '[]', '2026-07-20 20:19:18', '2026-07-20 20:51:15', 'clone instagram us', NULL),
('019f82af-5e71-7287-94df-018124a1d274', '019f829a-f708-7093-890d-4950e6ce2e66', '97', '019f82af-5e67-7153-9ee1-9c332fc1fd73', 'Clone Instagram US đã qua reg BM', 'ads7979-97-clone-instagram-us-da-qua-reg-bm', 'Reg Android | Verify Phone | Đã Bật 2FA | Name US | Created: > 2 month | Phù hợp spam, fishing...', 0.03, 800.00, 0, '2026-07-20 20:51:15', 'active', '[]', '2026-07-20 20:19:18', '2026-07-20 20:51:15', 'clone instagram us da qua reg bm', NULL);

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `sessions`
--

CREATE TABLE `sessions` (
  `id` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `user_id` char(36) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `ip_address` varchar(45) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `user_agent` text COLLATE utf8mb4_unicode_ci,
  `payload` longtext COLLATE utf8mb4_unicode_ci NOT NULL,
  `last_activity` int NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Đang đổ dữ liệu cho bảng `sessions`
--

INSERT INTO `sessions` (`id`, `user_id`, `ip_address`, `user_agent`, `payload`, `last_activity`) VALUES
('CjPGIYWYG1V0b84yOuu03eJmY4Os11QppdOo7fcE', NULL, '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Safari/537.36', 'eyJfdG9rZW4iOiJyR3VMYndxN2d5TEdDOHM5UlJNU3ZIRkNWMHplRGJOR1VuV1BUWEdKIiwiX3ByZXZpb3VzIjp7InVybCI6Imh0dHA6XC9cL2xvY2FsaG9zdDo4MDAwIiwicm91dGUiOm51bGx9LCJfZmxhc2giOnsib2xkIjpbXSwibmV3IjpbXX19', 1784522068);

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `suppliers`
--

CREATE TABLE `suppliers` (
  `id` char(36) COLLATE utf8mb4_unicode_ci NOT NULL,
  `code` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `base_url` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `api_key` text COLLATE utf8mb4_unicode_ci,
  `enabled` tinyint(1) NOT NULL DEFAULT '1',
  `auto_sync` tinyint(1) NOT NULL DEFAULT '0',
  `sync_interval_minutes` smallint UNSIGNED NOT NULL DEFAULT '30',
  `price_adjustment_type` enum('fixed','percent') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'fixed',
  `price_adjustment_value` decimal(15,2) NOT NULL DEFAULT '0.00',
  `last_synced_at` timestamp NULL DEFAULT NULL,
  `last_sync_status` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `last_sync_error` text COLLATE utf8mb4_unicode_ci,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Đang đổ dữ liệu cho bảng `suppliers`
--

INSERT INTO `suppliers` (`id`, `code`, `name`, `base_url`, `api_key`, `enabled`, `auto_sync`, `sync_interval_minutes`, `price_adjustment_type`, `price_adjustment_value`, `last_synced_at`, `last_sync_status`, `last_sync_error`, `created_at`, `updated_at`) VALUES
('019f829a-f708-7093-890d-4950e6ce2e66', 'ads7979', 'ADS7979', 'https://ads7979.com', 'eyJpdiI6IlhMVGUxVXRocXM4SnRiTGxWa2JqOUE9PSIsInZhbHVlIjoiUXh0STh4dHZpTkFwenNPb0FCTU43bjl0STFxOVZoRzVTNVQrYWdVdEIzNGVTRHh4bDlqaXdXWjJuZmpNOXhXdiIsIm1hYyI6ImQxNmRlMDg3ODE5OTViMDA0MmIyMzc0MzAyM2I3MzZhZmQyODNlZjlmODVlN2M2NTJlMmJkMzYyMjk2YTUxNGQiLCJ0YWciOiIifQ==', 1, 1, 30, 'fixed', 0.00, '2026-07-20 20:51:15', 'success', NULL, '2026-07-20 19:57:01', '2026-07-20 20:51:15');

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `system_settings`
--

CREATE TABLE `system_settings` (
  `key` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `value` text COLLATE utf8mb4_unicode_ci,
  `description` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Đang đổ dữ liệu cho bảng `system_settings`
--

INSERT INTO `system_settings` (`key`, `value`, `description`, `created_at`, `updated_at`) VALUES
('admin_binance_id', '63596652', 'Binance ID nhận tiền nạp của admin', '2026-07-19 18:41:52', '2026-07-19 18:44:07'),
('admin_wallet_address', '0xAB4a22a64bBC1Ca0445758C2Ee66a4cB2b644444', 'Địa chỉ ví USDT nhận tiền nạp của admin (BEP20)', '2026-07-19 18:41:54', '2026-07-19 18:44:07'),
('binance_api_key', 'eyJpdiI6IlhxNy9iZU4yajlTbU5rVFhDR0Rac1E9PSIsInZhbHVlIjoiTDdDeTRtbXFPZ2g2N2VLRzYySHJMVDZjZTNITElDeGxaQWFyMjJjelNLMHUzeFFkTGE1d1k2N2l0Sys0TWdVYStOd1RzOE1OZUUzbC9LMzArQzJETS9RSUtQaHh2QW03SjFsN0NETTZ2V2c9IiwibWFjIjoiMmQ3MjE5YmI1ZTgwNWUwN2M2NGRiZDg2ODRiOWU3OTQ0MDQ2YzQ4NTQzNGMyNDEyZGUzZjY4NWMwNWNmZTRkNiIsInRhZyI6IiJ9', 'Binance API Key của admin để đối soát nạp tiền qua Binance ID', '2026-07-19 18:41:52', '2026-07-19 18:44:07'),
('binance_api_secret', 'eyJpdiI6IlA0NjZnRDY4dy9lMXV6akFxSFBhREE9PSIsInZhbHVlIjoiMldGbjdWMzFmdlpkOU5yR2NJb21TZW93NmpqUWVDQzQzRDQ3N3k1RDBDeHNvYnJpb0d3Sk8vbWpuQ2lRWTVVaU9XK243ZWZCYytGdW5vZXFGMTVySEFFYm9MUUNWREJnR013czh4REVjRkU9IiwibWFjIjoiNGExNjIwZTUyNzJhZjhiYzQ3MGEzNzA0MTU0ODZjYzRkZWM2NTg4ZTJkZjZhMDFhNjFlMzkyYWY4ZDZiMDc3ZiIsInRhZyI6IiJ9', 'Binance API Secret của admin để đối soát nạp tiền qua Binance ID', '2026-07-19 18:41:52', '2026-07-19 18:44:07'),
('scanner_api_key', 'eyJpdiI6IkxhSWVyWlFndVVKVmxQazFWSlZQZ1E9PSIsInZhbHVlIjoiQXVocnlkcWtzTC9mSkZxRGJVdHM4VXBIQ2w2MDR4OUtXRWJ0bXB0VjJzUVMwQlM3L2JHMWxzMDJHSTVuNEI4TjNrSERJRWVvN0pmdE1YbUZiZWw1RWE5eDU4Sm9menhvamlKZHRQQ01XbHBGQUNaNlJPQ0hRTnpYdjd3cnlMd1dsVVlDb3RaVTlkUGxUUGszQTNIRVJmbjRmV200V0dCdlRmNUx2dkNnRHJZRG0vbjUydm1od2dqRG1xVFJCd05OZ2l0YzllQS9FakVRODRrV253SEs3ZG1VeXVMeFk0VUE4R0RnaWNtMHNoK0lNdmUzZDdwNTk4SUFpeTdXOXFLT3VFbDdPeUVGUk45R3ZZanN4anl6MjBnMFdVeGZDRitvM2thblFPUTZDcElqbmo0NnJ1bnZwRW1TeUFQMFlQSm9GYzQ2TkJvQ1dkUzN5emFLYUJ2ZjR0L1EyTk9SdWpaeENKYzFsdHhmL244QytIcGRya1llUFJBUnk1OTl0WlRQREZIc1hpVytvdjMxT1FCRXl0eXR2YmxEcStVNFBRVDJSQW9xQ2lhRG9DdWYwWkZaNS9ZR3h1NGc5U0hGdXlweiIsIm1hYyI6ImQxZjc3ZjIzMzlkZjE4MTA4Y2U2MzVkZDg0MmEzMDk3YTJlOTczNjg0ZWYzZDVkMmNjNzZiOTE2MjE4ZWVhNWQiLCJ0YWciOiIifQ==', 'API Key của Moralis để đối soát giao dịch nạp tiền tự động', '2026-07-19 18:41:54', '2026-07-19 18:44:07'),
('token_cleanup_days', '7', 'Số ngày lưu trữ login tokens trước khi tự động dọn dẹp', '2026-07-19 18:41:54', '2026-07-19 18:41:54'),
('usdt_exchange_rate', '0', '0 = dynamic CoinGecko USDT/VND rate', '2026-07-19 18:41:54', '2026-07-20 20:08:29');

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `users`
--

CREATE TABLE `users` (
  `id` char(36) COLLATE utf8mb4_unicode_ci NOT NULL,
  `username` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `email` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `phone` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `email_verified_at` timestamp NULL DEFAULT NULL,
  `password` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `role` enum('admin','customer') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'customer',
  `balance` decimal(16,4) NOT NULL DEFAULT '0.0000',
  `refresh_token_hash` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `refresh_token_expired_at` timestamp NULL DEFAULT NULL,
  `token_version` bigint UNSIGNED NOT NULL DEFAULT '0',
  `remember_token` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `api_key` text COLLATE utf8mb4_unicode_ci,
  `api_key_hash` varchar(64) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `google2fa_secret` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `google2fa_enabled` tinyint(1) NOT NULL DEFAULT '0',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `normalized_name` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `status` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'active'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Đang đổ dữ liệu cho bảng `users`
--

INSERT INTO `users` (`id`, `username`, `name`, `email`, `phone`, `email_verified_at`, `password`, `role`, `balance`, `refresh_token_hash`, `refresh_token_expired_at`, `token_version`, `remember_token`, `api_key`, `api_key_hash`, `google2fa_secret`, `google2fa_enabled`, `created_at`, `updated_at`, `normalized_name`, `status`) VALUES
('019f7d2f-cf44-723f-8c30-13b7ea78dadb', 'admin', 'admin', 'admin@isocalmarket.com', NULL, NULL, '$2y$10$Yvj0V0FiS12COpg8OsohsOrYG79yTmmh33oL9UoXC2RAt4WFZE72m', 'admin', 2367.0000, '292a7228fed064f3083659224f30950b5241258c8d813aed4ab7f8c9005740a5', '2026-07-27 23:25:31', 0, NULL, 'eyJpdiI6ImwrdmV4NzF0Z2JZWTZmbWRZdWZxVmc9PSIsInZhbHVlIjoiR3lqZElXN1VkU1ZDcmd0U2JaM2F3c2dsaGEvUnVDSUhzcUtvRlVBazczQT0iLCJtYWMiOiIyNzU5OGFlMmJmNjEyZWM4ODY4NDQwZGIzNDJiYmQyNjRhOGFlZGRlYWYyNzg4NzFhNWY1ZDFhM2I3N2UwNTRiIiwidGFnIjoiIn0=', '8a8024e2d2f76c602807b036b49b36593148c48041301a11ec975a622d95ef31', 'J24CQ7Z2LYV52XWV', 0, '2026-07-19 18:41:53', '2026-07-20 23:25:31', 'admin', 'active'),
('019f7d2f-d010-7399-a970-38f57e67c89e', 'customer1', 'Nguyễn Văn A', 'customer1@isocalmarket.com', '0989515047', NULL, '$2y$12$gA.ROgFGrxrxW.ieuYhosevxZnqzonie2qcC8o1AkCWJHp5OZb1ai', 'customer', 0.0000, NULL, NULL, 0, NULL, NULL, NULL, NULL, 0, '2026-07-19 18:41:53', '2026-07-19 18:41:53', 'nguyen van a', 'active');

--
-- Chỉ mục cho các bảng đã đổ
--

--
-- Chỉ mục cho bảng `cache`
--
ALTER TABLE `cache`
  ADD PRIMARY KEY (`key`),
  ADD KEY `cache_expiration_index` (`expiration`);

--
-- Chỉ mục cho bảng `cache_locks`
--
ALTER TABLE `cache_locks`
  ADD PRIMARY KEY (`key`),
  ADD KEY `cache_locks_expiration_index` (`expiration`);

--
-- Chỉ mục cho bảng `categories`
--
ALTER TABLE `categories`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `categories_slug_unique` (`slug`),
  ADD UNIQUE KEY `categories_supplier_id_external_id_unique` (`supplier_id`,`external_id`),
  ADD KEY `categories_normalized_name_index` (`normalized_name`);

--
-- Chỉ mục cho bảng `coupons`
--
ALTER TABLE `coupons`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `coupons_code_unique` (`code`),
  ADD KEY `coupons_code_index` (`code`),
  ADD KEY `coupons_start_date_index` (`start_date`),
  ADD KEY `coupons_end_date_index` (`end_date`);

--
-- Chỉ mục cho bảng `deposits`
--
ALTER TABLE `deposits`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `deposits_tx_hash_unique` (`tx_hash`),
  ADD KEY `deposits_user_id_status_index` (`user_id`,`status`),
  ADD KEY `deposits_created_at_index` (`created_at`),
  ADD KEY `deposits_code_index` (`code`),
  ADD KEY `deposits_payer_binance_id_payment_method_index` (`payer_binance_id`,`payment_method`),
  ADD KEY `deposits_status_amount_to_pay_index` (`status`,`amount_to_pay`);

--
-- Chỉ mục cho bảng `failed_jobs`
--
ALTER TABLE `failed_jobs`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `failed_jobs_uuid_unique` (`uuid`),
  ADD KEY `failed_jobs_connection_queue_failed_at_index` (`connection`,`queue`,`failed_at`);

--
-- Chỉ mục cho bảng `inventory_batches`
--
ALTER TABLE `inventory_batches`
  ADD PRIMARY KEY (`id`),
  ADD KEY `inventory_batches_product_id_status_index` (`product_id`,`status`),
  ADD KEY `inventory_batches_normalized_batch_name_index` (`normalized_batch_name`),
  ADD KEY `inventory_batches_created_at_index` (`created_at`),
  ADD KEY `inv_batches_product_status_idx` (`product_id`,`status`);

--
-- Chỉ mục cho bảng `jobs`
--
ALTER TABLE `jobs`
  ADD PRIMARY KEY (`id`),
  ADD KEY `jobs_queue_index` (`queue`);

--
-- Chỉ mục cho bảng `job_batches`
--
ALTER TABLE `job_batches`
  ADD PRIMARY KEY (`id`);

--
-- Chỉ mục cho bảng `migrations`
--
ALTER TABLE `migrations`
  ADD PRIMARY KEY (`id`);

--
-- Chỉ mục cho bảng `orders`
--
ALTER TABLE `orders`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `orders_order_code_unique` (`order_code`),
  ADD KEY `orders_user_id_index` (`user_id`),
  ADD KEY `orders_status_index` (`status`),
  ADD KEY `orders_created_at_index` (`created_at`),
  ADD KEY `orders_coupon_id_foreign` (`coupon_id`),
  ADD KEY `orders_user_status_created_idx` (`user_id`,`status`,`created_at`),
  ADD KEY `orders_status_created_idx` (`status`,`created_at`);

--
-- Chỉ mục cho bảng `order_items`
--
ALTER TABLE `order_items`
  ADD PRIMARY KEY (`id`),
  ADD KEY `order_items_order_id_index` (`order_id`),
  ADD KEY `order_items_product_id_index` (`product_id`);

--
-- Chỉ mục cho bảng `password_reset_tokens`
--
ALTER TABLE `password_reset_tokens`
  ADD PRIMARY KEY (`email`);

--
-- Chỉ mục cho bảng `products`
--
ALTER TABLE `products`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `products_slug_unique` (`slug`),
  ADD UNIQUE KEY `products_supplier_id_external_id_unique` (`supplier_id`,`external_id`),
  ADD KEY `products_status_index` (`status`),
  ADD KEY `products_price_index` (`price`),
  ADD KEY `products_category_id_index` (`category_id`),
  ADD KEY `products_normalized_name_index` (`normalized_name`),
  ADD KEY `products_deleted_at_index` (`deleted_at`),
  ADD KEY `products_created_at_index` (`created_at`),
  ADD KEY `products_cat_status_created_idx` (`category_id`,`status`,`created_at`),
  ADD KEY `products_status_created_idx` (`status`,`created_at`),
  ADD KEY `products_supplier_id_status_index` (`supplier_id`,`status`);

--
-- Chỉ mục cho bảng `sessions`
--
ALTER TABLE `sessions`
  ADD PRIMARY KEY (`id`),
  ADD KEY `sessions_user_id_index` (`user_id`),
  ADD KEY `sessions_last_activity_index` (`last_activity`);

--
-- Chỉ mục cho bảng `suppliers`
--
ALTER TABLE `suppliers`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `suppliers_code_unique` (`code`);

--
-- Chỉ mục cho bảng `system_settings`
--
ALTER TABLE `system_settings`
  ADD PRIMARY KEY (`key`);

--
-- Chỉ mục cho bảng `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `users_email_unique` (`email`),
  ADD UNIQUE KEY `users_username_unique` (`username`),
  ADD UNIQUE KEY `users_api_key_hash_unique` (`api_key_hash`),
  ADD KEY `users_name_index` (`name`),
  ADD KEY `users_refresh_token_hash_index` (`refresh_token_hash`),
  ADD KEY `users_normalized_name_index` (`normalized_name`),
  ADD KEY `users_status_index` (`status`);

--
-- AUTO_INCREMENT cho các bảng đã đổ
--

--
-- AUTO_INCREMENT cho bảng `failed_jobs`
--
ALTER TABLE `failed_jobs`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT cho bảng `jobs`
--
ALTER TABLE `jobs`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT cho bảng `migrations`
--
ALTER TABLE `migrations`
  MODIFY `id` int UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=32;

--
-- Ràng buộc đối với các bảng kết xuất
--

--
-- Ràng buộc cho bảng `categories`
--
ALTER TABLE `categories`
  ADD CONSTRAINT `categories_supplier_id_foreign` FOREIGN KEY (`supplier_id`) REFERENCES `suppliers` (`id`) ON DELETE SET NULL;

--
-- Ràng buộc cho bảng `deposits`
--
ALTER TABLE `deposits`
  ADD CONSTRAINT `deposits_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE RESTRICT;

--
-- Ràng buộc cho bảng `inventory_batches`
--
ALTER TABLE `inventory_batches`
  ADD CONSTRAINT `inventory_batches_product_id_foreign` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`) ON DELETE CASCADE;

--
-- Ràng buộc cho bảng `orders`
--
ALTER TABLE `orders`
  ADD CONSTRAINT `orders_coupon_id_foreign` FOREIGN KEY (`coupon_id`) REFERENCES `coupons` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `orders_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Ràng buộc cho bảng `order_items`
--
ALTER TABLE `order_items`
  ADD CONSTRAINT `order_items_order_id_foreign` FOREIGN KEY (`order_id`) REFERENCES `orders` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `order_items_product_id_foreign` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`) ON DELETE RESTRICT;

--
-- Ràng buộc cho bảng `products`
--
ALTER TABLE `products`
  ADD CONSTRAINT `products_category_id_foreign` FOREIGN KEY (`category_id`) REFERENCES `categories` (`id`) ON DELETE RESTRICT,
  ADD CONSTRAINT `products_supplier_id_foreign` FOREIGN KEY (`supplier_id`) REFERENCES `suppliers` (`id`) ON DELETE SET NULL;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
