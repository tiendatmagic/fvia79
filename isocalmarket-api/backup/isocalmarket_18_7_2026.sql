-- phpMyAdmin SQL Dump
-- version 5.2.2
-- https://www.phpmyadmin.net/
--
-- Máy chủ: localhost:3306
-- Thời gian đã tạo: Th7 18, 2026 lúc 02:15 AM
-- Phiên bản máy phục vụ: 8.0.30
-- Phiên bản PHP: 8.3.28

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+07:00";


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

INSERT INTO `cache` (`key`, `value`, `expiration`) VALUES
('laravel-cache-a75f3f172bfb296f2e10cbfc6dfc1883', 'i:7;', 1784340835),
('laravel-cache-a75f3f172bfb296f2e10cbfc6dfc1883:timer', 'i:1784340835;', 1784340835),
('laravel-cache-d66ede0c8254f3df23930762c99f5420', 'i:2;', 1784340687),
('laravel-cache-d66ede0c8254f3df23930762c99f5420:timer', 'i:1784340687;', 1784340687),
('laravel-cache-usdt_to_vnd_rate', 'd:26278;', 1784341413);

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

INSERT INTO `categories` (`id`, `name`, `slug`, `description`, `created_at`, `updated_at`, `normalized_name`) VALUES
('019f72e4-43ad-7050-be7f-bdacf3df5db3', 'BẢNG GIÁ BM', 'bang-gia-bm', 'Business Manager chất lượng cao, ngâm lâu năm.', '2026-07-17 18:43:09', '2026-07-17 18:43:09', 'bang gia bm'),
('019f72e4-43b1-73f1-b9dc-bc330639f652', 'Via US Live Ads', 'via-us-live-ads', 'Via Mỹ chuyên chạy quảng cáo, ngâm chuẩn local.', '2026-07-17 18:43:10', '2026-07-17 18:43:10', 'via us live ads'),
('019f72e4-43b4-7305-9ddf-6942ebc994ef', 'Via Kháng 902 & Chat Support', 'via-khang-902-chat-support', 'Via đã kháng link 902 và có nút hỗ trợ support trực tuyến.', '2026-07-17 18:43:10', '2026-07-17 18:43:10', 'via khang 902 & chat support'),
('019f72e4-43b7-71aa-b070-04eb5afae057', 'Via Chuyên Set Camp ( Tỉ lệ TỤT , HOLD , DIE , NEED THẤP)', 'via-chuyen-set-camp', 'Dòng via chuyên biệt để lên chiến dịch hạn chế lỗi tối đa.', '2026-07-17 18:43:10', '2026-07-17 18:43:10', 'via chuyen set camp ( ti le tut , hold , die , need thap)'),
('019f72e4-43bb-71d4-80b7-d2fa45976717', 'Ad account Limit 250$', 'ad-account-limit-250', 'Tài khoản quảng cáo limit 250$ trâu bò.', '2026-07-17 18:43:10', '2026-07-17 18:43:10', 'ad account limit 250$'),
('019f72e4-43bf-7016-b10d-43eed06e3827', 'Via Ngoại Live Ads (No2FA)', 'via-ngoai-live-ads-no2fa', 'Via ngoại không cài bảo mật 2 lớp.', '2026-07-17 18:43:10', '2026-07-17 18:43:10', 'via ngoai live ads (no2fa)'),
('019f72e4-43c2-7118-a806-bf5be68cc5fe', 'Via Ngoại Live Ads (2FA)', 'via-ngoai-live-ads-2fa', 'Via ngoại có sẵn bảo mật 2FA và checkpoint mail.', '2026-07-17 18:43:10', '2026-07-17 18:43:10', 'via ngoai live ads (2fa)'),
('019f72e4-43c5-73a6-aedb-be84788308b3', 'Clone Global', 'clone-global', 'Clone ngoại ngâm lâu năm phục vụ spam, seeding.', '2026-07-17 18:43:10', '2026-07-17 18:43:10', 'clone global'),
('019f72e4-43c8-715e-8bcb-78c91037528d', 'Clone Việt Nam', 'clone-viet-nam', 'Clone Việt chất lượng tốt reg tay sạch.', '2026-07-17 18:43:10', '2026-07-17 18:43:10', 'clone viet nam'),
('019f72e4-43cd-731a-8610-92ccfb65dab2', 'Via Ngoại DIE ADS', 'via-ngoai-die-ads', 'Via ngoại bị hạn chế quảng cáo thích hợp seeding, spy.', '2026-07-17 18:43:10', '2026-07-17 18:43:10', 'via ngoai die ads'),
('019f72e4-43d0-70be-87ab-44070500bada', 'Hotmail Live V.V', 'hotmail-live-vv', 'Hộp thư Hotmail/Outlook ngâm lâu năm hỗ trợ API.', '2026-07-17 18:43:10', '2026-07-17 18:43:10', 'hotmail live v.v'),
('019f72e4-43d3-7284-a53d-2c453f22b68d', 'Clone Instagram', 'clone-instagram', 'Tài khoản Instagram ngâm phục vụ marketing.', '2026-07-17 18:43:10', '2026-07-17 18:43:10', 'clone instagram');

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
  `status` enum('pending_payment','completed','failed','expired') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'pending_payment',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Đang đổ dữ liệu cho bảng `deposits`
--

INSERT INTO `deposits` (`id`, `user_id`, `code`, `amount_usdt`, `amount_to_pay`, `amount_vnd`, `tx_hash`, `status`, `created_at`, `updated_at`) VALUES
('019f72e8-56c1-7355-abae-cd3343e1fdeb', '019f72e4-3bd0-72bc-8dc5-40301b68422d', '232', 2.0000, 2.0000, 52558.0000, NULL, 'pending_payment', '2026-07-17 18:47:37', '2026-07-17 18:47:37');

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

--
-- Đang đổ dữ liệu cho bảng `inventory_batches`
--

INSERT INTO `inventory_batches` (`id`, `product_id`, `batch_name`, `keys_pool`, `initial_count`, `sold_count`, `status`, `created_at`, `updated_at`, `normalized_batch_name`) VALUES
('019f72e4-4479-7372-aff2-ae529683ac5d', '019f72e4-43d7-70ee-8184-938face79ad2', 'Lô hàng mẫu #RL07J', '10006379371523|pass_secret_via_3|2FA_SECRET_KEY_XYZ_3|recovery_mail_3@gmail.com|pass_mail\n10006379371524|pass_secret_via_4|2FA_SECRET_KEY_XYZ_4|recovery_mail_4@gmail.com|pass_mail\n10006379371525|pass_secret_via_5|2FA_SECRET_KEY_XYZ_5|recovery_mail_5@gmail.com|pass_mail\n10006379371526|pass_secret_via_6|2FA_SECRET_KEY_XYZ_6|recovery_mail_6@gmail.com|pass_mail\n10006379371527|pass_secret_via_7|2FA_SECRET_KEY_XYZ_7|recovery_mail_7@gmail.com|pass_mail\n10006379371528|pass_secret_via_8|2FA_SECRET_KEY_XYZ_8|recovery_mail_8@gmail.com|pass_mail\n10006379371529|pass_secret_via_9|2FA_SECRET_KEY_XYZ_9|recovery_mail_9@gmail.com|pass_mail\n100063793715210|pass_secret_via_10|2FA_SECRET_KEY_XYZ_10|recovery_mail_10@gmail.com|pass_mail\n100063793715211|pass_secret_via_11|2FA_SECRET_KEY_XYZ_11|recovery_mail_11@gmail.com|pass_mail\n100063793715212|pass_secret_via_12|2FA_SECRET_KEY_XYZ_12|recovery_mail_12@gmail.com|pass_mail\n100063793715213|pass_secret_via_13|2FA_SECRET_KEY_XYZ_13|recovery_mail_13@gmail.com|pass_mail\n100063793715214|pass_secret_via_14|2FA_SECRET_KEY_XYZ_14|recovery_mail_14@gmail.com|pass_mail\n100063793715215|pass_secret_via_15|2FA_SECRET_KEY_XYZ_15|recovery_mail_15@gmail.com|pass_mail', 15, 2, 'active', '2026-07-17 18:43:10', '2026-07-17 18:43:10', 'lo hang mau #rl07j'),
('019f72e4-447d-7137-994a-02ca9ac1c326', '019f72e4-43db-7371-83eb-360819316572', 'Lô hàng mẫu #D0YKB', '10007046147154|pass_secret_via_4|2FA_SECRET_KEY_XYZ_4|recovery_mail_4@gmail.com|pass_mail\n10007046147155|pass_secret_via_5|2FA_SECRET_KEY_XYZ_5|recovery_mail_5@gmail.com|pass_mail\n10007046147156|pass_secret_via_6|2FA_SECRET_KEY_XYZ_6|recovery_mail_6@gmail.com|pass_mail\n10007046147157|pass_secret_via_7|2FA_SECRET_KEY_XYZ_7|recovery_mail_7@gmail.com|pass_mail\n10007046147158|pass_secret_via_8|2FA_SECRET_KEY_XYZ_8|recovery_mail_8@gmail.com|pass_mail\n10007046147159|pass_secret_via_9|2FA_SECRET_KEY_XYZ_9|recovery_mail_9@gmail.com|pass_mail\n100070461471510|pass_secret_via_10|2FA_SECRET_KEY_XYZ_10|recovery_mail_10@gmail.com|pass_mail\n100070461471511|pass_secret_via_11|2FA_SECRET_KEY_XYZ_11|recovery_mail_11@gmail.com|pass_mail\n100070461471512|pass_secret_via_12|2FA_SECRET_KEY_XYZ_12|recovery_mail_12@gmail.com|pass_mail\n100070461471513|pass_secret_via_13|2FA_SECRET_KEY_XYZ_13|recovery_mail_13@gmail.com|pass_mail\n100070461471514|pass_secret_via_14|2FA_SECRET_KEY_XYZ_14|recovery_mail_14@gmail.com|pass_mail\n100070461471515|pass_secret_via_15|2FA_SECRET_KEY_XYZ_15|recovery_mail_15@gmail.com|pass_mail', 15, 3, 'active', '2026-07-17 18:43:10', '2026-07-17 18:43:10', 'lo hang mau #d0ykb'),
('019f72e4-447f-70af-9697-689ecc25ad7c', '019f72e4-43df-72aa-a0ac-fb8c9410ccd8', 'Lô hàng mẫu #FOXVP', '10003813808352|pass_secret_via_2|2FA_SECRET_KEY_XYZ_2|recovery_mail_2@gmail.com|pass_mail\n10003813808353|pass_secret_via_3|2FA_SECRET_KEY_XYZ_3|recovery_mail_3@gmail.com|pass_mail\n10003813808354|pass_secret_via_4|2FA_SECRET_KEY_XYZ_4|recovery_mail_4@gmail.com|pass_mail\n10003813808355|pass_secret_via_5|2FA_SECRET_KEY_XYZ_5|recovery_mail_5@gmail.com|pass_mail\n10003813808356|pass_secret_via_6|2FA_SECRET_KEY_XYZ_6|recovery_mail_6@gmail.com|pass_mail\n10003813808357|pass_secret_via_7|2FA_SECRET_KEY_XYZ_7|recovery_mail_7@gmail.com|pass_mail\n10003813808358|pass_secret_via_8|2FA_SECRET_KEY_XYZ_8|recovery_mail_8@gmail.com|pass_mail\n10003813808359|pass_secret_via_9|2FA_SECRET_KEY_XYZ_9|recovery_mail_9@gmail.com|pass_mail\n100038138083510|pass_secret_via_10|2FA_SECRET_KEY_XYZ_10|recovery_mail_10@gmail.com|pass_mail\n100038138083511|pass_secret_via_11|2FA_SECRET_KEY_XYZ_11|recovery_mail_11@gmail.com|pass_mail\n100038138083512|pass_secret_via_12|2FA_SECRET_KEY_XYZ_12|recovery_mail_12@gmail.com|pass_mail\n100038138083513|pass_secret_via_13|2FA_SECRET_KEY_XYZ_13|recovery_mail_13@gmail.com|pass_mail\n100038138083514|pass_secret_via_14|2FA_SECRET_KEY_XYZ_14|recovery_mail_14@gmail.com|pass_mail\n100038138083515|pass_secret_via_15|2FA_SECRET_KEY_XYZ_15|recovery_mail_15@gmail.com|pass_mail', 15, 1, 'active', '2026-07-17 18:43:10', '2026-07-17 18:43:10', 'lo hang mau #foxvp'),
('019f72e4-4483-71d7-a00d-b9cf65bea2ba', '019f72e4-43e2-7052-8da5-ac795a358b47', 'Lô hàng mẫu #MBZIN', '10003557696171|pass_secret_via_1|2FA_SECRET_KEY_XYZ_1|recovery_mail_1@gmail.com|pass_mail\n10003557696172|pass_secret_via_2|2FA_SECRET_KEY_XYZ_2|recovery_mail_2@gmail.com|pass_mail\n10003557696173|pass_secret_via_3|2FA_SECRET_KEY_XYZ_3|recovery_mail_3@gmail.com|pass_mail\n10003557696174|pass_secret_via_4|2FA_SECRET_KEY_XYZ_4|recovery_mail_4@gmail.com|pass_mail\n10003557696175|pass_secret_via_5|2FA_SECRET_KEY_XYZ_5|recovery_mail_5@gmail.com|pass_mail\n10003557696176|pass_secret_via_6|2FA_SECRET_KEY_XYZ_6|recovery_mail_6@gmail.com|pass_mail\n10003557696177|pass_secret_via_7|2FA_SECRET_KEY_XYZ_7|recovery_mail_7@gmail.com|pass_mail\n10003557696178|pass_secret_via_8|2FA_SECRET_KEY_XYZ_8|recovery_mail_8@gmail.com|pass_mail\n10003557696179|pass_secret_via_9|2FA_SECRET_KEY_XYZ_9|recovery_mail_9@gmail.com|pass_mail\n100035576961710|pass_secret_via_10|2FA_SECRET_KEY_XYZ_10|recovery_mail_10@gmail.com|pass_mail\n100035576961711|pass_secret_via_11|2FA_SECRET_KEY_XYZ_11|recovery_mail_11@gmail.com|pass_mail\n100035576961712|pass_secret_via_12|2FA_SECRET_KEY_XYZ_12|recovery_mail_12@gmail.com|pass_mail\n100035576961713|pass_secret_via_13|2FA_SECRET_KEY_XYZ_13|recovery_mail_13@gmail.com|pass_mail\n100035576961714|pass_secret_via_14|2FA_SECRET_KEY_XYZ_14|recovery_mail_14@gmail.com|pass_mail\n100035576961715|pass_secret_via_15|2FA_SECRET_KEY_XYZ_15|recovery_mail_15@gmail.com|pass_mail', 15, 0, 'active', '2026-07-17 18:43:10', '2026-07-17 18:43:10', 'lo hang mau #mbzin'),
('019f72e4-4486-7230-bb79-d43a91e46ab6', '019f72e4-43e5-70ec-8091-0ac361b45e64', 'Lô hàng mẫu #JFQAY', '10007229042321|pass_secret_via_1|2FA_SECRET_KEY_XYZ_1|recovery_mail_1@gmail.com|pass_mail\n10007229042322|pass_secret_via_2|2FA_SECRET_KEY_XYZ_2|recovery_mail_2@gmail.com|pass_mail\n10007229042323|pass_secret_via_3|2FA_SECRET_KEY_XYZ_3|recovery_mail_3@gmail.com|pass_mail\n10007229042324|pass_secret_via_4|2FA_SECRET_KEY_XYZ_4|recovery_mail_4@gmail.com|pass_mail\n10007229042325|pass_secret_via_5|2FA_SECRET_KEY_XYZ_5|recovery_mail_5@gmail.com|pass_mail\n10007229042326|pass_secret_via_6|2FA_SECRET_KEY_XYZ_6|recovery_mail_6@gmail.com|pass_mail\n10007229042327|pass_secret_via_7|2FA_SECRET_KEY_XYZ_7|recovery_mail_7@gmail.com|pass_mail\n10007229042328|pass_secret_via_8|2FA_SECRET_KEY_XYZ_8|recovery_mail_8@gmail.com|pass_mail\n10007229042329|pass_secret_via_9|2FA_SECRET_KEY_XYZ_9|recovery_mail_9@gmail.com|pass_mail\n100072290423210|pass_secret_via_10|2FA_SECRET_KEY_XYZ_10|recovery_mail_10@gmail.com|pass_mail\n100072290423211|pass_secret_via_11|2FA_SECRET_KEY_XYZ_11|recovery_mail_11@gmail.com|pass_mail\n100072290423212|pass_secret_via_12|2FA_SECRET_KEY_XYZ_12|recovery_mail_12@gmail.com|pass_mail\n100072290423213|pass_secret_via_13|2FA_SECRET_KEY_XYZ_13|recovery_mail_13@gmail.com|pass_mail\n100072290423214|pass_secret_via_14|2FA_SECRET_KEY_XYZ_14|recovery_mail_14@gmail.com|pass_mail\n100072290423215|pass_secret_via_15|2FA_SECRET_KEY_XYZ_15|recovery_mail_15@gmail.com|pass_mail', 15, 0, 'active', '2026-07-17 18:43:10', '2026-07-17 18:43:10', 'lo hang mau #jfqay'),
('019f72e4-4489-7015-ac25-9551c44b419d', '019f72e4-43e7-70ab-8552-23b2eff1a8a1', 'Lô hàng mẫu #04V50', '10002226095515|pass_secret_via_5|2FA_SECRET_KEY_XYZ_5|recovery_mail_5@gmail.com|pass_mail\n10002226095516|pass_secret_via_6|2FA_SECRET_KEY_XYZ_6|recovery_mail_6@gmail.com|pass_mail\n10002226095517|pass_secret_via_7|2FA_SECRET_KEY_XYZ_7|recovery_mail_7@gmail.com|pass_mail\n10002226095518|pass_secret_via_8|2FA_SECRET_KEY_XYZ_8|recovery_mail_8@gmail.com|pass_mail\n10002226095519|pass_secret_via_9|2FA_SECRET_KEY_XYZ_9|recovery_mail_9@gmail.com|pass_mail\n100022260955110|pass_secret_via_10|2FA_SECRET_KEY_XYZ_10|recovery_mail_10@gmail.com|pass_mail\n100022260955111|pass_secret_via_11|2FA_SECRET_KEY_XYZ_11|recovery_mail_11@gmail.com|pass_mail\n100022260955112|pass_secret_via_12|2FA_SECRET_KEY_XYZ_12|recovery_mail_12@gmail.com|pass_mail\n100022260955113|pass_secret_via_13|2FA_SECRET_KEY_XYZ_13|recovery_mail_13@gmail.com|pass_mail\n100022260955114|pass_secret_via_14|2FA_SECRET_KEY_XYZ_14|recovery_mail_14@gmail.com|pass_mail\n100022260955115|pass_secret_via_15|2FA_SECRET_KEY_XYZ_15|recovery_mail_15@gmail.com|pass_mail', 15, 4, 'active', '2026-07-17 18:43:10', '2026-07-17 18:43:11', 'lo hang mau #04v50'),
('019f72e4-448c-71d2-bb6e-edef1fbb836e', '019f72e4-43ea-7095-81a1-39cc912fcdc3', 'Lô hàng mẫu #E1MI5', '10001925420145|pass_secret_via_5|2FA_SECRET_KEY_XYZ_5|recovery_mail_5@gmail.com|pass_mail\n10001925420146|pass_secret_via_6|2FA_SECRET_KEY_XYZ_6|recovery_mail_6@gmail.com|pass_mail\n10001925420147|pass_secret_via_7|2FA_SECRET_KEY_XYZ_7|recovery_mail_7@gmail.com|pass_mail\n10001925420148|pass_secret_via_8|2FA_SECRET_KEY_XYZ_8|recovery_mail_8@gmail.com|pass_mail\n10001925420149|pass_secret_via_9|2FA_SECRET_KEY_XYZ_9|recovery_mail_9@gmail.com|pass_mail\n100019254201410|pass_secret_via_10|2FA_SECRET_KEY_XYZ_10|recovery_mail_10@gmail.com|pass_mail\n100019254201411|pass_secret_via_11|2FA_SECRET_KEY_XYZ_11|recovery_mail_11@gmail.com|pass_mail\n100019254201412|pass_secret_via_12|2FA_SECRET_KEY_XYZ_12|recovery_mail_12@gmail.com|pass_mail\n100019254201413|pass_secret_via_13|2FA_SECRET_KEY_XYZ_13|recovery_mail_13@gmail.com|pass_mail\n100019254201414|pass_secret_via_14|2FA_SECRET_KEY_XYZ_14|recovery_mail_14@gmail.com|pass_mail\n100019254201415|pass_secret_via_15|2FA_SECRET_KEY_XYZ_15|recovery_mail_15@gmail.com|pass_mail', 15, 4, 'active', '2026-07-17 18:43:10', '2026-07-17 18:43:11', 'lo hang mau #e1mi5'),
('019f72e4-448e-7130-a889-2ee9816c9b46', '019f72e4-43ee-711c-a57f-240e239ba9b1', 'Lô hàng mẫu #6AJOQ', '10008631117718|pass_secret_via_8|2FA_SECRET_KEY_XYZ_8|recovery_mail_8@gmail.com|pass_mail\n10008631117719|pass_secret_via_9|2FA_SECRET_KEY_XYZ_9|recovery_mail_9@gmail.com|pass_mail\n100086311177110|pass_secret_via_10|2FA_SECRET_KEY_XYZ_10|recovery_mail_10@gmail.com|pass_mail\n100086311177111|pass_secret_via_11|2FA_SECRET_KEY_XYZ_11|recovery_mail_11@gmail.com|pass_mail\n100086311177112|pass_secret_via_12|2FA_SECRET_KEY_XYZ_12|recovery_mail_12@gmail.com|pass_mail\n100086311177113|pass_secret_via_13|2FA_SECRET_KEY_XYZ_13|recovery_mail_13@gmail.com|pass_mail\n100086311177114|pass_secret_via_14|2FA_SECRET_KEY_XYZ_14|recovery_mail_14@gmail.com|pass_mail\n100086311177115|pass_secret_via_15|2FA_SECRET_KEY_XYZ_15|recovery_mail_15@gmail.com|pass_mail', 15, 7, 'active', '2026-07-17 18:43:10', '2026-07-17 18:43:11', 'lo hang mau #6ajoq'),
('019f72e4-4491-7137-8327-43f89a0d5f24', '019f72e4-43f1-7197-9ee1-5476468c7438', 'Lô hàng mẫu #JLTO7', '10007041784231|pass_secret_via_1|2FA_SECRET_KEY_XYZ_1|recovery_mail_1@gmail.com|pass_mail\n10007041784232|pass_secret_via_2|2FA_SECRET_KEY_XYZ_2|recovery_mail_2@gmail.com|pass_mail\n10007041784233|pass_secret_via_3|2FA_SECRET_KEY_XYZ_3|recovery_mail_3@gmail.com|pass_mail\n10007041784234|pass_secret_via_4|2FA_SECRET_KEY_XYZ_4|recovery_mail_4@gmail.com|pass_mail\n10007041784235|pass_secret_via_5|2FA_SECRET_KEY_XYZ_5|recovery_mail_5@gmail.com|pass_mail\n10007041784236|pass_secret_via_6|2FA_SECRET_KEY_XYZ_6|recovery_mail_6@gmail.com|pass_mail\n10007041784237|pass_secret_via_7|2FA_SECRET_KEY_XYZ_7|recovery_mail_7@gmail.com|pass_mail\n10007041784238|pass_secret_via_8|2FA_SECRET_KEY_XYZ_8|recovery_mail_8@gmail.com|pass_mail\n10007041784239|pass_secret_via_9|2FA_SECRET_KEY_XYZ_9|recovery_mail_9@gmail.com|pass_mail\n100070417842310|pass_secret_via_10|2FA_SECRET_KEY_XYZ_10|recovery_mail_10@gmail.com|pass_mail\n100070417842311|pass_secret_via_11|2FA_SECRET_KEY_XYZ_11|recovery_mail_11@gmail.com|pass_mail\n100070417842312|pass_secret_via_12|2FA_SECRET_KEY_XYZ_12|recovery_mail_12@gmail.com|pass_mail\n100070417842313|pass_secret_via_13|2FA_SECRET_KEY_XYZ_13|recovery_mail_13@gmail.com|pass_mail\n100070417842314|pass_secret_via_14|2FA_SECRET_KEY_XYZ_14|recovery_mail_14@gmail.com|pass_mail\n100070417842315|pass_secret_via_15|2FA_SECRET_KEY_XYZ_15|recovery_mail_15@gmail.com|pass_mail', 15, 0, 'active', '2026-07-17 18:43:10', '2026-07-17 18:43:10', 'lo hang mau #jlto7'),
('019f72e4-4495-7314-ab89-6643addeddcd', '019f72e4-43f3-7029-be1a-f5ae139b89dd', 'Lô hàng mẫu #QKI0D', '10002329407953|pass_secret_via_3|2FA_SECRET_KEY_XYZ_3|recovery_mail_3@gmail.com|pass_mail\n10002329407954|pass_secret_via_4|2FA_SECRET_KEY_XYZ_4|recovery_mail_4@gmail.com|pass_mail\n10002329407955|pass_secret_via_5|2FA_SECRET_KEY_XYZ_5|recovery_mail_5@gmail.com|pass_mail\n10002329407956|pass_secret_via_6|2FA_SECRET_KEY_XYZ_6|recovery_mail_6@gmail.com|pass_mail\n10002329407957|pass_secret_via_7|2FA_SECRET_KEY_XYZ_7|recovery_mail_7@gmail.com|pass_mail\n10002329407958|pass_secret_via_8|2FA_SECRET_KEY_XYZ_8|recovery_mail_8@gmail.com|pass_mail\n10002329407959|pass_secret_via_9|2FA_SECRET_KEY_XYZ_9|recovery_mail_9@gmail.com|pass_mail\n100023294079510|pass_secret_via_10|2FA_SECRET_KEY_XYZ_10|recovery_mail_10@gmail.com|pass_mail\n100023294079511|pass_secret_via_11|2FA_SECRET_KEY_XYZ_11|recovery_mail_11@gmail.com|pass_mail\n100023294079512|pass_secret_via_12|2FA_SECRET_KEY_XYZ_12|recovery_mail_12@gmail.com|pass_mail\n100023294079513|pass_secret_via_13|2FA_SECRET_KEY_XYZ_13|recovery_mail_13@gmail.com|pass_mail\n100023294079514|pass_secret_via_14|2FA_SECRET_KEY_XYZ_14|recovery_mail_14@gmail.com|pass_mail\n100023294079515|pass_secret_via_15|2FA_SECRET_KEY_XYZ_15|recovery_mail_15@gmail.com|pass_mail', 15, 2, 'active', '2026-07-17 18:43:10', '2026-07-17 18:43:10', 'lo hang mau #qki0d'),
('019f72e4-449a-7124-a59f-c6f5b0008c42', '019f72e4-43f6-71ea-ae65-8b350311a1eb', 'Lô hàng mẫu #CLLK8', '10009747684546|pass_secret_via_6|2FA_SECRET_KEY_XYZ_6|recovery_mail_6@gmail.com|pass_mail\n10009747684547|pass_secret_via_7|2FA_SECRET_KEY_XYZ_7|recovery_mail_7@gmail.com|pass_mail\n10009747684548|pass_secret_via_8|2FA_SECRET_KEY_XYZ_8|recovery_mail_8@gmail.com|pass_mail\n10009747684549|pass_secret_via_9|2FA_SECRET_KEY_XYZ_9|recovery_mail_9@gmail.com|pass_mail\n100097476845410|pass_secret_via_10|2FA_SECRET_KEY_XYZ_10|recovery_mail_10@gmail.com|pass_mail\n100097476845411|pass_secret_via_11|2FA_SECRET_KEY_XYZ_11|recovery_mail_11@gmail.com|pass_mail\n100097476845412|pass_secret_via_12|2FA_SECRET_KEY_XYZ_12|recovery_mail_12@gmail.com|pass_mail\n100097476845413|pass_secret_via_13|2FA_SECRET_KEY_XYZ_13|recovery_mail_13@gmail.com|pass_mail\n100097476845414|pass_secret_via_14|2FA_SECRET_KEY_XYZ_14|recovery_mail_14@gmail.com|pass_mail\n100097476845415|pass_secret_via_15|2FA_SECRET_KEY_XYZ_15|recovery_mail_15@gmail.com|pass_mail', 15, 5, 'active', '2026-07-17 18:43:10', '2026-07-17 18:43:10', 'lo hang mau #cllk8'),
('019f72e4-449d-7242-85dd-c6ce8a83d415', '019f72e4-43f9-7193-8e0c-d5d95a5aff9d', 'Lô hàng mẫu #UN9XJ', '10007292760063|pass_secret_via_3|2FA_SECRET_KEY_XYZ_3|recovery_mail_3@gmail.com|pass_mail\n10007292760064|pass_secret_via_4|2FA_SECRET_KEY_XYZ_4|recovery_mail_4@gmail.com|pass_mail\n10007292760065|pass_secret_via_5|2FA_SECRET_KEY_XYZ_5|recovery_mail_5@gmail.com|pass_mail\n10007292760066|pass_secret_via_6|2FA_SECRET_KEY_XYZ_6|recovery_mail_6@gmail.com|pass_mail\n10007292760067|pass_secret_via_7|2FA_SECRET_KEY_XYZ_7|recovery_mail_7@gmail.com|pass_mail\n10007292760068|pass_secret_via_8|2FA_SECRET_KEY_XYZ_8|recovery_mail_8@gmail.com|pass_mail\n10007292760069|pass_secret_via_9|2FA_SECRET_KEY_XYZ_9|recovery_mail_9@gmail.com|pass_mail\n100072927600610|pass_secret_via_10|2FA_SECRET_KEY_XYZ_10|recovery_mail_10@gmail.com|pass_mail\n100072927600611|pass_secret_via_11|2FA_SECRET_KEY_XYZ_11|recovery_mail_11@gmail.com|pass_mail\n100072927600612|pass_secret_via_12|2FA_SECRET_KEY_XYZ_12|recovery_mail_12@gmail.com|pass_mail\n100072927600613|pass_secret_via_13|2FA_SECRET_KEY_XYZ_13|recovery_mail_13@gmail.com|pass_mail\n100072927600614|pass_secret_via_14|2FA_SECRET_KEY_XYZ_14|recovery_mail_14@gmail.com|pass_mail\n100072927600615|pass_secret_via_15|2FA_SECRET_KEY_XYZ_15|recovery_mail_15@gmail.com|pass_mail', 15, 2, 'active', '2026-07-17 18:43:10', '2026-07-17 18:43:10', 'lo hang mau #un9xj'),
('019f72e4-449f-72a7-8ba0-af5c91283751', '019f72e4-43fc-73e2-a2a7-2d18a888b782', 'Lô hàng mẫu #CRVML', '10008614073786|pass_secret_via_6|2FA_SECRET_KEY_XYZ_6|recovery_mail_6@gmail.com|pass_mail\n10008614073787|pass_secret_via_7|2FA_SECRET_KEY_XYZ_7|recovery_mail_7@gmail.com|pass_mail\n10008614073788|pass_secret_via_8|2FA_SECRET_KEY_XYZ_8|recovery_mail_8@gmail.com|pass_mail\n10008614073789|pass_secret_via_9|2FA_SECRET_KEY_XYZ_9|recovery_mail_9@gmail.com|pass_mail\n100086140737810|pass_secret_via_10|2FA_SECRET_KEY_XYZ_10|recovery_mail_10@gmail.com|pass_mail\n100086140737811|pass_secret_via_11|2FA_SECRET_KEY_XYZ_11|recovery_mail_11@gmail.com|pass_mail\n100086140737812|pass_secret_via_12|2FA_SECRET_KEY_XYZ_12|recovery_mail_12@gmail.com|pass_mail\n100086140737813|pass_secret_via_13|2FA_SECRET_KEY_XYZ_13|recovery_mail_13@gmail.com|pass_mail\n100086140737814|pass_secret_via_14|2FA_SECRET_KEY_XYZ_14|recovery_mail_14@gmail.com|pass_mail\n100086140737815|pass_secret_via_15|2FA_SECRET_KEY_XYZ_15|recovery_mail_15@gmail.com|pass_mail', 15, 5, 'active', '2026-07-17 18:43:10', '2026-07-17 18:43:10', 'lo hang mau #crvml'),
('019f72e4-44a3-7280-8aef-b6dc6096c8c2', '019f72e4-4400-705c-aee9-bb3be612fd32', 'Lô hàng mẫu #H5MUT', '10009631915463|pass_secret_via_3|2FA_SECRET_KEY_XYZ_3|recovery_mail_3@gmail.com|pass_mail\n10009631915464|pass_secret_via_4|2FA_SECRET_KEY_XYZ_4|recovery_mail_4@gmail.com|pass_mail\n10009631915465|pass_secret_via_5|2FA_SECRET_KEY_XYZ_5|recovery_mail_5@gmail.com|pass_mail\n10009631915466|pass_secret_via_6|2FA_SECRET_KEY_XYZ_6|recovery_mail_6@gmail.com|pass_mail\n10009631915467|pass_secret_via_7|2FA_SECRET_KEY_XYZ_7|recovery_mail_7@gmail.com|pass_mail\n10009631915468|pass_secret_via_8|2FA_SECRET_KEY_XYZ_8|recovery_mail_8@gmail.com|pass_mail\n10009631915469|pass_secret_via_9|2FA_SECRET_KEY_XYZ_9|recovery_mail_9@gmail.com|pass_mail\n100096319154610|pass_secret_via_10|2FA_SECRET_KEY_XYZ_10|recovery_mail_10@gmail.com|pass_mail\n100096319154611|pass_secret_via_11|2FA_SECRET_KEY_XYZ_11|recovery_mail_11@gmail.com|pass_mail\n100096319154612|pass_secret_via_12|2FA_SECRET_KEY_XYZ_12|recovery_mail_12@gmail.com|pass_mail\n100096319154613|pass_secret_via_13|2FA_SECRET_KEY_XYZ_13|recovery_mail_13@gmail.com|pass_mail\n100096319154614|pass_secret_via_14|2FA_SECRET_KEY_XYZ_14|recovery_mail_14@gmail.com|pass_mail\n100096319154615|pass_secret_via_15|2FA_SECRET_KEY_XYZ_15|recovery_mail_15@gmail.com|pass_mail', 15, 2, 'active', '2026-07-17 18:43:10', '2026-07-17 18:43:10', 'lo hang mau #h5mut'),
('019f72e4-44a9-73f7-9670-f5bf461acc45', '019f72e4-4403-72fe-8377-1169837916dc', 'Lô hàng mẫu #J25EF', '10009642101045|pass_secret_via_5|2FA_SECRET_KEY_XYZ_5|recovery_mail_5@gmail.com|pass_mail\n10009642101046|pass_secret_via_6|2FA_SECRET_KEY_XYZ_6|recovery_mail_6@gmail.com|pass_mail\n10009642101047|pass_secret_via_7|2FA_SECRET_KEY_XYZ_7|recovery_mail_7@gmail.com|pass_mail\n10009642101048|pass_secret_via_8|2FA_SECRET_KEY_XYZ_8|recovery_mail_8@gmail.com|pass_mail\n10009642101049|pass_secret_via_9|2FA_SECRET_KEY_XYZ_9|recovery_mail_9@gmail.com|pass_mail\n100096421010410|pass_secret_via_10|2FA_SECRET_KEY_XYZ_10|recovery_mail_10@gmail.com|pass_mail\n100096421010411|pass_secret_via_11|2FA_SECRET_KEY_XYZ_11|recovery_mail_11@gmail.com|pass_mail\n100096421010412|pass_secret_via_12|2FA_SECRET_KEY_XYZ_12|recovery_mail_12@gmail.com|pass_mail\n100096421010413|pass_secret_via_13|2FA_SECRET_KEY_XYZ_13|recovery_mail_13@gmail.com|pass_mail\n100096421010414|pass_secret_via_14|2FA_SECRET_KEY_XYZ_14|recovery_mail_14@gmail.com|pass_mail\n100096421010415|pass_secret_via_15|2FA_SECRET_KEY_XYZ_15|recovery_mail_15@gmail.com|pass_mail', 15, 4, 'active', '2026-07-17 18:43:10', '2026-07-17 18:43:10', 'lo hang mau #j25ef'),
('019f72e4-44ab-73da-bc47-fad69a63f713', '019f72e4-4406-7165-8320-aefd74d5e260', 'Lô hàng mẫu #SCKF4', '10002239833977|pass_secret_via_7|2FA_SECRET_KEY_XYZ_7|recovery_mail_7@gmail.com|pass_mail\n10002239833978|pass_secret_via_8|2FA_SECRET_KEY_XYZ_8|recovery_mail_8@gmail.com|pass_mail\n10002239833979|pass_secret_via_9|2FA_SECRET_KEY_XYZ_9|recovery_mail_9@gmail.com|pass_mail\n100022398339710|pass_secret_via_10|2FA_SECRET_KEY_XYZ_10|recovery_mail_10@gmail.com|pass_mail\n100022398339711|pass_secret_via_11|2FA_SECRET_KEY_XYZ_11|recovery_mail_11@gmail.com|pass_mail\n100022398339712|pass_secret_via_12|2FA_SECRET_KEY_XYZ_12|recovery_mail_12@gmail.com|pass_mail\n100022398339713|pass_secret_via_13|2FA_SECRET_KEY_XYZ_13|recovery_mail_13@gmail.com|pass_mail\n100022398339714|pass_secret_via_14|2FA_SECRET_KEY_XYZ_14|recovery_mail_14@gmail.com|pass_mail\n100022398339715|pass_secret_via_15|2FA_SECRET_KEY_XYZ_15|recovery_mail_15@gmail.com|pass_mail', 15, 6, 'active', '2026-07-17 18:43:10', '2026-07-17 18:43:11', 'lo hang mau #sckf4'),
('019f72e4-44ae-7205-83e4-7f8524884ea3', '019f72e4-4409-73a5-bc13-8bfb6d440a21', 'Lô hàng mẫu #SUCCR', '10007522013465|pass_secret_via_5|2FA_SECRET_KEY_XYZ_5|recovery_mail_5@gmail.com|pass_mail\n10007522013466|pass_secret_via_6|2FA_SECRET_KEY_XYZ_6|recovery_mail_6@gmail.com|pass_mail\n10007522013467|pass_secret_via_7|2FA_SECRET_KEY_XYZ_7|recovery_mail_7@gmail.com|pass_mail\n10007522013468|pass_secret_via_8|2FA_SECRET_KEY_XYZ_8|recovery_mail_8@gmail.com|pass_mail\n10007522013469|pass_secret_via_9|2FA_SECRET_KEY_XYZ_9|recovery_mail_9@gmail.com|pass_mail\n100075220134610|pass_secret_via_10|2FA_SECRET_KEY_XYZ_10|recovery_mail_10@gmail.com|pass_mail\n100075220134611|pass_secret_via_11|2FA_SECRET_KEY_XYZ_11|recovery_mail_11@gmail.com|pass_mail\n100075220134612|pass_secret_via_12|2FA_SECRET_KEY_XYZ_12|recovery_mail_12@gmail.com|pass_mail\n100075220134613|pass_secret_via_13|2FA_SECRET_KEY_XYZ_13|recovery_mail_13@gmail.com|pass_mail\n100075220134614|pass_secret_via_14|2FA_SECRET_KEY_XYZ_14|recovery_mail_14@gmail.com|pass_mail\n100075220134615|pass_secret_via_15|2FA_SECRET_KEY_XYZ_15|recovery_mail_15@gmail.com|pass_mail', 15, 4, 'active', '2026-07-17 18:43:10', '2026-07-17 18:43:10', 'lo hang mau #succr'),
('019f72e4-44b1-7253-ac88-524c13010e05', '019f72e4-440c-70a0-957f-3f8f8c3e3c2d', 'Lô hàng mẫu #SOBGF', '10001165888625|pass_secret_via_5|2FA_SECRET_KEY_XYZ_5|recovery_mail_5@gmail.com|pass_mail\n10001165888626|pass_secret_via_6|2FA_SECRET_KEY_XYZ_6|recovery_mail_6@gmail.com|pass_mail\n10001165888627|pass_secret_via_7|2FA_SECRET_KEY_XYZ_7|recovery_mail_7@gmail.com|pass_mail\n10001165888628|pass_secret_via_8|2FA_SECRET_KEY_XYZ_8|recovery_mail_8@gmail.com|pass_mail\n10001165888629|pass_secret_via_9|2FA_SECRET_KEY_XYZ_9|recovery_mail_9@gmail.com|pass_mail\n100011658886210|pass_secret_via_10|2FA_SECRET_KEY_XYZ_10|recovery_mail_10@gmail.com|pass_mail\n100011658886211|pass_secret_via_11|2FA_SECRET_KEY_XYZ_11|recovery_mail_11@gmail.com|pass_mail\n100011658886212|pass_secret_via_12|2FA_SECRET_KEY_XYZ_12|recovery_mail_12@gmail.com|pass_mail\n100011658886213|pass_secret_via_13|2FA_SECRET_KEY_XYZ_13|recovery_mail_13@gmail.com|pass_mail\n100011658886214|pass_secret_via_14|2FA_SECRET_KEY_XYZ_14|recovery_mail_14@gmail.com|pass_mail\n100011658886215|pass_secret_via_15|2FA_SECRET_KEY_XYZ_15|recovery_mail_15@gmail.com|pass_mail', 15, 4, 'active', '2026-07-17 18:43:10', '2026-07-17 18:43:11', 'lo hang mau #sobgf'),
('019f72e4-44b5-7094-b957-188c44795a2d', '019f72e4-4410-7044-abaf-be54f5038130', 'Lô hàng mẫu #VPNLD', '10001069804042|pass_secret_via_2|2FA_SECRET_KEY_XYZ_2|recovery_mail_2@gmail.com|pass_mail\n10001069804043|pass_secret_via_3|2FA_SECRET_KEY_XYZ_3|recovery_mail_3@gmail.com|pass_mail\n10001069804044|pass_secret_via_4|2FA_SECRET_KEY_XYZ_4|recovery_mail_4@gmail.com|pass_mail\n10001069804045|pass_secret_via_5|2FA_SECRET_KEY_XYZ_5|recovery_mail_5@gmail.com|pass_mail\n10001069804046|pass_secret_via_6|2FA_SECRET_KEY_XYZ_6|recovery_mail_6@gmail.com|pass_mail\n10001069804047|pass_secret_via_7|2FA_SECRET_KEY_XYZ_7|recovery_mail_7@gmail.com|pass_mail\n10001069804048|pass_secret_via_8|2FA_SECRET_KEY_XYZ_8|recovery_mail_8@gmail.com|pass_mail\n10001069804049|pass_secret_via_9|2FA_SECRET_KEY_XYZ_9|recovery_mail_9@gmail.com|pass_mail\n100010698040410|pass_secret_via_10|2FA_SECRET_KEY_XYZ_10|recovery_mail_10@gmail.com|pass_mail\n100010698040411|pass_secret_via_11|2FA_SECRET_KEY_XYZ_11|recovery_mail_11@gmail.com|pass_mail\n100010698040412|pass_secret_via_12|2FA_SECRET_KEY_XYZ_12|recovery_mail_12@gmail.com|pass_mail\n100010698040413|pass_secret_via_13|2FA_SECRET_KEY_XYZ_13|recovery_mail_13@gmail.com|pass_mail\n100010698040414|pass_secret_via_14|2FA_SECRET_KEY_XYZ_14|recovery_mail_14@gmail.com|pass_mail\n100010698040415|pass_secret_via_15|2FA_SECRET_KEY_XYZ_15|recovery_mail_15@gmail.com|pass_mail', 15, 1, 'active', '2026-07-17 18:43:10', '2026-07-17 18:43:10', 'lo hang mau #vpnld'),
('019f72e4-44b8-71f8-8f1a-ce97e072b464', '019f72e4-4413-7071-bdb9-5b1a25c830d8', 'Lô hàng mẫu #SGJH1', '10002923024074|pass_secret_via_4|2FA_SECRET_KEY_XYZ_4|recovery_mail_4@gmail.com|pass_mail\n10002923024075|pass_secret_via_5|2FA_SECRET_KEY_XYZ_5|recovery_mail_5@gmail.com|pass_mail\n10002923024076|pass_secret_via_6|2FA_SECRET_KEY_XYZ_6|recovery_mail_6@gmail.com|pass_mail\n10002923024077|pass_secret_via_7|2FA_SECRET_KEY_XYZ_7|recovery_mail_7@gmail.com|pass_mail\n10002923024078|pass_secret_via_8|2FA_SECRET_KEY_XYZ_8|recovery_mail_8@gmail.com|pass_mail\n10002923024079|pass_secret_via_9|2FA_SECRET_KEY_XYZ_9|recovery_mail_9@gmail.com|pass_mail\n100029230240710|pass_secret_via_10|2FA_SECRET_KEY_XYZ_10|recovery_mail_10@gmail.com|pass_mail\n100029230240711|pass_secret_via_11|2FA_SECRET_KEY_XYZ_11|recovery_mail_11@gmail.com|pass_mail\n100029230240712|pass_secret_via_12|2FA_SECRET_KEY_XYZ_12|recovery_mail_12@gmail.com|pass_mail\n100029230240713|pass_secret_via_13|2FA_SECRET_KEY_XYZ_13|recovery_mail_13@gmail.com|pass_mail\n100029230240714|pass_secret_via_14|2FA_SECRET_KEY_XYZ_14|recovery_mail_14@gmail.com|pass_mail\n100029230240715|pass_secret_via_15|2FA_SECRET_KEY_XYZ_15|recovery_mail_15@gmail.com|pass_mail', 15, 3, 'active', '2026-07-17 18:43:10', '2026-07-17 18:43:10', 'lo hang mau #sgjh1'),
('019f72e4-44bb-7043-b559-2dd7f6554737', '019f72e4-4416-71fc-aadc-87d6d2896f85', 'Lô hàng mẫu #WXGJU', '10009795373952|pass_secret_via_2|2FA_SECRET_KEY_XYZ_2|recovery_mail_2@gmail.com|pass_mail\n10009795373953|pass_secret_via_3|2FA_SECRET_KEY_XYZ_3|recovery_mail_3@gmail.com|pass_mail\n10009795373954|pass_secret_via_4|2FA_SECRET_KEY_XYZ_4|recovery_mail_4@gmail.com|pass_mail\n10009795373955|pass_secret_via_5|2FA_SECRET_KEY_XYZ_5|recovery_mail_5@gmail.com|pass_mail\n10009795373956|pass_secret_via_6|2FA_SECRET_KEY_XYZ_6|recovery_mail_6@gmail.com|pass_mail\n10009795373957|pass_secret_via_7|2FA_SECRET_KEY_XYZ_7|recovery_mail_7@gmail.com|pass_mail\n10009795373958|pass_secret_via_8|2FA_SECRET_KEY_XYZ_8|recovery_mail_8@gmail.com|pass_mail\n10009795373959|pass_secret_via_9|2FA_SECRET_KEY_XYZ_9|recovery_mail_9@gmail.com|pass_mail\n100097953739510|pass_secret_via_10|2FA_SECRET_KEY_XYZ_10|recovery_mail_10@gmail.com|pass_mail\n100097953739511|pass_secret_via_11|2FA_SECRET_KEY_XYZ_11|recovery_mail_11@gmail.com|pass_mail\n100097953739512|pass_secret_via_12|2FA_SECRET_KEY_XYZ_12|recovery_mail_12@gmail.com|pass_mail\n100097953739513|pass_secret_via_13|2FA_SECRET_KEY_XYZ_13|recovery_mail_13@gmail.com|pass_mail\n100097953739514|pass_secret_via_14|2FA_SECRET_KEY_XYZ_14|recovery_mail_14@gmail.com|pass_mail\n100097953739515|pass_secret_via_15|2FA_SECRET_KEY_XYZ_15|recovery_mail_15@gmail.com|pass_mail', 15, 1, 'active', '2026-07-17 18:43:10', '2026-07-17 18:43:10', 'lo hang mau #wxgju'),
('019f72e4-44be-7198-9e4b-89b8373151ed', '019f72e4-4418-70a4-b2a9-56a47ebd54c1', 'Lô hàng mẫu #BB7LW', '10004166246883|pass_secret_via_3|2FA_SECRET_KEY_XYZ_3|recovery_mail_3@gmail.com|pass_mail\n10004166246884|pass_secret_via_4|2FA_SECRET_KEY_XYZ_4|recovery_mail_4@gmail.com|pass_mail\n10004166246885|pass_secret_via_5|2FA_SECRET_KEY_XYZ_5|recovery_mail_5@gmail.com|pass_mail\n10004166246886|pass_secret_via_6|2FA_SECRET_KEY_XYZ_6|recovery_mail_6@gmail.com|pass_mail\n10004166246887|pass_secret_via_7|2FA_SECRET_KEY_XYZ_7|recovery_mail_7@gmail.com|pass_mail\n10004166246888|pass_secret_via_8|2FA_SECRET_KEY_XYZ_8|recovery_mail_8@gmail.com|pass_mail\n10004166246889|pass_secret_via_9|2FA_SECRET_KEY_XYZ_9|recovery_mail_9@gmail.com|pass_mail\n100041662468810|pass_secret_via_10|2FA_SECRET_KEY_XYZ_10|recovery_mail_10@gmail.com|pass_mail\n100041662468811|pass_secret_via_11|2FA_SECRET_KEY_XYZ_11|recovery_mail_11@gmail.com|pass_mail\n100041662468812|pass_secret_via_12|2FA_SECRET_KEY_XYZ_12|recovery_mail_12@gmail.com|pass_mail\n100041662468813|pass_secret_via_13|2FA_SECRET_KEY_XYZ_13|recovery_mail_13@gmail.com|pass_mail\n100041662468814|pass_secret_via_14|2FA_SECRET_KEY_XYZ_14|recovery_mail_14@gmail.com|pass_mail\n100041662468815|pass_secret_via_15|2FA_SECRET_KEY_XYZ_15|recovery_mail_15@gmail.com|pass_mail', 15, 2, 'active', '2026-07-17 18:43:10', '2026-07-17 18:43:10', 'lo hang mau #bb7lw'),
('019f72e4-44c1-7346-9529-b748a7359fc3', '019f72e4-441b-7207-a5ba-1e321f64e1d1', 'Lô hàng mẫu #UJNUJ', '10003331887652|pass_secret_via_2|2FA_SECRET_KEY_XYZ_2|recovery_mail_2@gmail.com|pass_mail\n10003331887653|pass_secret_via_3|2FA_SECRET_KEY_XYZ_3|recovery_mail_3@gmail.com|pass_mail\n10003331887654|pass_secret_via_4|2FA_SECRET_KEY_XYZ_4|recovery_mail_4@gmail.com|pass_mail\n10003331887655|pass_secret_via_5|2FA_SECRET_KEY_XYZ_5|recovery_mail_5@gmail.com|pass_mail\n10003331887656|pass_secret_via_6|2FA_SECRET_KEY_XYZ_6|recovery_mail_6@gmail.com|pass_mail\n10003331887657|pass_secret_via_7|2FA_SECRET_KEY_XYZ_7|recovery_mail_7@gmail.com|pass_mail\n10003331887658|pass_secret_via_8|2FA_SECRET_KEY_XYZ_8|recovery_mail_8@gmail.com|pass_mail\n10003331887659|pass_secret_via_9|2FA_SECRET_KEY_XYZ_9|recovery_mail_9@gmail.com|pass_mail\n100033318876510|pass_secret_via_10|2FA_SECRET_KEY_XYZ_10|recovery_mail_10@gmail.com|pass_mail\n100033318876511|pass_secret_via_11|2FA_SECRET_KEY_XYZ_11|recovery_mail_11@gmail.com|pass_mail\n100033318876512|pass_secret_via_12|2FA_SECRET_KEY_XYZ_12|recovery_mail_12@gmail.com|pass_mail\n100033318876513|pass_secret_via_13|2FA_SECRET_KEY_XYZ_13|recovery_mail_13@gmail.com|pass_mail\n100033318876514|pass_secret_via_14|2FA_SECRET_KEY_XYZ_14|recovery_mail_14@gmail.com|pass_mail\n100033318876515|pass_secret_via_15|2FA_SECRET_KEY_XYZ_15|recovery_mail_15@gmail.com|pass_mail', 15, 1, 'active', '2026-07-17 18:43:10', '2026-07-17 18:43:10', 'lo hang mau #ujnuj'),
('019f72e4-44c5-7105-903e-9ea49cde2c8d', '019f72e4-441e-70aa-9c27-3a21ede04bd4', 'Lô hàng mẫu #0VGCK', '10001524016121|pass_secret_via_1|2FA_SECRET_KEY_XYZ_1|recovery_mail_1@gmail.com|pass_mail\n10001524016122|pass_secret_via_2|2FA_SECRET_KEY_XYZ_2|recovery_mail_2@gmail.com|pass_mail\n10001524016123|pass_secret_via_3|2FA_SECRET_KEY_XYZ_3|recovery_mail_3@gmail.com|pass_mail\n10001524016124|pass_secret_via_4|2FA_SECRET_KEY_XYZ_4|recovery_mail_4@gmail.com|pass_mail\n10001524016125|pass_secret_via_5|2FA_SECRET_KEY_XYZ_5|recovery_mail_5@gmail.com|pass_mail\n10001524016126|pass_secret_via_6|2FA_SECRET_KEY_XYZ_6|recovery_mail_6@gmail.com|pass_mail\n10001524016127|pass_secret_via_7|2FA_SECRET_KEY_XYZ_7|recovery_mail_7@gmail.com|pass_mail\n10001524016128|pass_secret_via_8|2FA_SECRET_KEY_XYZ_8|recovery_mail_8@gmail.com|pass_mail\n10001524016129|pass_secret_via_9|2FA_SECRET_KEY_XYZ_9|recovery_mail_9@gmail.com|pass_mail\n100015240161210|pass_secret_via_10|2FA_SECRET_KEY_XYZ_10|recovery_mail_10@gmail.com|pass_mail\n100015240161211|pass_secret_via_11|2FA_SECRET_KEY_XYZ_11|recovery_mail_11@gmail.com|pass_mail\n100015240161212|pass_secret_via_12|2FA_SECRET_KEY_XYZ_12|recovery_mail_12@gmail.com|pass_mail\n100015240161213|pass_secret_via_13|2FA_SECRET_KEY_XYZ_13|recovery_mail_13@gmail.com|pass_mail\n100015240161214|pass_secret_via_14|2FA_SECRET_KEY_XYZ_14|recovery_mail_14@gmail.com|pass_mail\n100015240161215|pass_secret_via_15|2FA_SECRET_KEY_XYZ_15|recovery_mail_15@gmail.com|pass_mail', 15, 0, 'active', '2026-07-17 18:43:10', '2026-07-17 18:43:10', 'lo hang mau #0vgck'),
('019f72e4-44c9-71a2-9478-de577b010748', '019f72e4-4422-703f-a5b7-8881ce487ed3', 'Lô hàng mẫu #TSI5Z', '10001696219844|pass_secret_via_4|2FA_SECRET_KEY_XYZ_4|recovery_mail_4@gmail.com|pass_mail\n10001696219845|pass_secret_via_5|2FA_SECRET_KEY_XYZ_5|recovery_mail_5@gmail.com|pass_mail\n10001696219846|pass_secret_via_6|2FA_SECRET_KEY_XYZ_6|recovery_mail_6@gmail.com|pass_mail\n10001696219847|pass_secret_via_7|2FA_SECRET_KEY_XYZ_7|recovery_mail_7@gmail.com|pass_mail\n10001696219848|pass_secret_via_8|2FA_SECRET_KEY_XYZ_8|recovery_mail_8@gmail.com|pass_mail\n10001696219849|pass_secret_via_9|2FA_SECRET_KEY_XYZ_9|recovery_mail_9@gmail.com|pass_mail\n100016962198410|pass_secret_via_10|2FA_SECRET_KEY_XYZ_10|recovery_mail_10@gmail.com|pass_mail\n100016962198411|pass_secret_via_11|2FA_SECRET_KEY_XYZ_11|recovery_mail_11@gmail.com|pass_mail\n100016962198412|pass_secret_via_12|2FA_SECRET_KEY_XYZ_12|recovery_mail_12@gmail.com|pass_mail\n100016962198413|pass_secret_via_13|2FA_SECRET_KEY_XYZ_13|recovery_mail_13@gmail.com|pass_mail\n100016962198414|pass_secret_via_14|2FA_SECRET_KEY_XYZ_14|recovery_mail_14@gmail.com|pass_mail\n100016962198415|pass_secret_via_15|2FA_SECRET_KEY_XYZ_15|recovery_mail_15@gmail.com|pass_mail', 15, 3, 'active', '2026-07-17 18:43:10', '2026-07-17 18:43:10', 'lo hang mau #tsi5z'),
('019f72e4-44cc-7343-9d61-46947bc22040', '019f72e4-4425-70b4-8ae6-83f4b95f6a65', 'Lô hàng mẫu #EP4ZA', '10002403965873|pass_secret_via_3|2FA_SECRET_KEY_XYZ_3|recovery_mail_3@gmail.com|pass_mail\n10002403965874|pass_secret_via_4|2FA_SECRET_KEY_XYZ_4|recovery_mail_4@gmail.com|pass_mail\n10002403965875|pass_secret_via_5|2FA_SECRET_KEY_XYZ_5|recovery_mail_5@gmail.com|pass_mail\n10002403965876|pass_secret_via_6|2FA_SECRET_KEY_XYZ_6|recovery_mail_6@gmail.com|pass_mail\n10002403965877|pass_secret_via_7|2FA_SECRET_KEY_XYZ_7|recovery_mail_7@gmail.com|pass_mail\n10002403965878|pass_secret_via_8|2FA_SECRET_KEY_XYZ_8|recovery_mail_8@gmail.com|pass_mail\n10002403965879|pass_secret_via_9|2FA_SECRET_KEY_XYZ_9|recovery_mail_9@gmail.com|pass_mail\n100024039658710|pass_secret_via_10|2FA_SECRET_KEY_XYZ_10|recovery_mail_10@gmail.com|pass_mail\n100024039658711|pass_secret_via_11|2FA_SECRET_KEY_XYZ_11|recovery_mail_11@gmail.com|pass_mail\n100024039658712|pass_secret_via_12|2FA_SECRET_KEY_XYZ_12|recovery_mail_12@gmail.com|pass_mail\n100024039658713|pass_secret_via_13|2FA_SECRET_KEY_XYZ_13|recovery_mail_13@gmail.com|pass_mail\n100024039658714|pass_secret_via_14|2FA_SECRET_KEY_XYZ_14|recovery_mail_14@gmail.com|pass_mail\n100024039658715|pass_secret_via_15|2FA_SECRET_KEY_XYZ_15|recovery_mail_15@gmail.com|pass_mail', 15, 2, 'active', '2026-07-17 18:43:10', '2026-07-17 18:43:10', 'lo hang mau #ep4za'),
('019f72e4-44cf-7351-b8e9-1dbf3c263be5', '019f72e4-4428-727f-84e0-a4df8c19a68d', 'Lô hàng mẫu #X2KQP', '10009448924133|pass_secret_via_3|2FA_SECRET_KEY_XYZ_3|recovery_mail_3@gmail.com|pass_mail\n10009448924134|pass_secret_via_4|2FA_SECRET_KEY_XYZ_4|recovery_mail_4@gmail.com|pass_mail\n10009448924135|pass_secret_via_5|2FA_SECRET_KEY_XYZ_5|recovery_mail_5@gmail.com|pass_mail\n10009448924136|pass_secret_via_6|2FA_SECRET_KEY_XYZ_6|recovery_mail_6@gmail.com|pass_mail\n10009448924137|pass_secret_via_7|2FA_SECRET_KEY_XYZ_7|recovery_mail_7@gmail.com|pass_mail\n10009448924138|pass_secret_via_8|2FA_SECRET_KEY_XYZ_8|recovery_mail_8@gmail.com|pass_mail\n10009448924139|pass_secret_via_9|2FA_SECRET_KEY_XYZ_9|recovery_mail_9@gmail.com|pass_mail\n100094489241310|pass_secret_via_10|2FA_SECRET_KEY_XYZ_10|recovery_mail_10@gmail.com|pass_mail\n100094489241311|pass_secret_via_11|2FA_SECRET_KEY_XYZ_11|recovery_mail_11@gmail.com|pass_mail\n100094489241312|pass_secret_via_12|2FA_SECRET_KEY_XYZ_12|recovery_mail_12@gmail.com|pass_mail\n100094489241313|pass_secret_via_13|2FA_SECRET_KEY_XYZ_13|recovery_mail_13@gmail.com|pass_mail\n100094489241314|pass_secret_via_14|2FA_SECRET_KEY_XYZ_14|recovery_mail_14@gmail.com|pass_mail\n100094489241315|pass_secret_via_15|2FA_SECRET_KEY_XYZ_15|recovery_mail_15@gmail.com|pass_mail', 15, 2, 'active', '2026-07-17 18:43:10', '2026-07-17 18:43:10', 'lo hang mau #x2kqp'),
('019f72e4-44d2-710a-8aff-558979d3c8a0', '019f72e4-442a-735a-910b-3023b82098af', 'Lô hàng mẫu #LHJ77', '10002654067133|pass_secret_via_3|2FA_SECRET_KEY_XYZ_3|recovery_mail_3@gmail.com|pass_mail\n10002654067134|pass_secret_via_4|2FA_SECRET_KEY_XYZ_4|recovery_mail_4@gmail.com|pass_mail\n10002654067135|pass_secret_via_5|2FA_SECRET_KEY_XYZ_5|recovery_mail_5@gmail.com|pass_mail\n10002654067136|pass_secret_via_6|2FA_SECRET_KEY_XYZ_6|recovery_mail_6@gmail.com|pass_mail\n10002654067137|pass_secret_via_7|2FA_SECRET_KEY_XYZ_7|recovery_mail_7@gmail.com|pass_mail\n10002654067138|pass_secret_via_8|2FA_SECRET_KEY_XYZ_8|recovery_mail_8@gmail.com|pass_mail\n10002654067139|pass_secret_via_9|2FA_SECRET_KEY_XYZ_9|recovery_mail_9@gmail.com|pass_mail\n100026540671310|pass_secret_via_10|2FA_SECRET_KEY_XYZ_10|recovery_mail_10@gmail.com|pass_mail\n100026540671311|pass_secret_via_11|2FA_SECRET_KEY_XYZ_11|recovery_mail_11@gmail.com|pass_mail\n100026540671312|pass_secret_via_12|2FA_SECRET_KEY_XYZ_12|recovery_mail_12@gmail.com|pass_mail\n100026540671313|pass_secret_via_13|2FA_SECRET_KEY_XYZ_13|recovery_mail_13@gmail.com|pass_mail\n100026540671314|pass_secret_via_14|2FA_SECRET_KEY_XYZ_14|recovery_mail_14@gmail.com|pass_mail\n100026540671315|pass_secret_via_15|2FA_SECRET_KEY_XYZ_15|recovery_mail_15@gmail.com|pass_mail', 15, 2, 'active', '2026-07-17 18:43:10', '2026-07-17 18:43:11', 'lo hang mau #lhj77'),
('019f72e4-44d5-70b5-8477-cdbe12bb3a59', '019f72e4-442d-707e-a0fd-48b1bfa2969d', 'Lô hàng mẫu #4HP6P', '10004462697783|pass_secret_via_3|2FA_SECRET_KEY_XYZ_3|recovery_mail_3@gmail.com|pass_mail\n10004462697784|pass_secret_via_4|2FA_SECRET_KEY_XYZ_4|recovery_mail_4@gmail.com|pass_mail\n10004462697785|pass_secret_via_5|2FA_SECRET_KEY_XYZ_5|recovery_mail_5@gmail.com|pass_mail\n10004462697786|pass_secret_via_6|2FA_SECRET_KEY_XYZ_6|recovery_mail_6@gmail.com|pass_mail\n10004462697787|pass_secret_via_7|2FA_SECRET_KEY_XYZ_7|recovery_mail_7@gmail.com|pass_mail\n10004462697788|pass_secret_via_8|2FA_SECRET_KEY_XYZ_8|recovery_mail_8@gmail.com|pass_mail\n10004462697789|pass_secret_via_9|2FA_SECRET_KEY_XYZ_9|recovery_mail_9@gmail.com|pass_mail\n100044626977810|pass_secret_via_10|2FA_SECRET_KEY_XYZ_10|recovery_mail_10@gmail.com|pass_mail\n100044626977811|pass_secret_via_11|2FA_SECRET_KEY_XYZ_11|recovery_mail_11@gmail.com|pass_mail\n100044626977812|pass_secret_via_12|2FA_SECRET_KEY_XYZ_12|recovery_mail_12@gmail.com|pass_mail\n100044626977813|pass_secret_via_13|2FA_SECRET_KEY_XYZ_13|recovery_mail_13@gmail.com|pass_mail\n100044626977814|pass_secret_via_14|2FA_SECRET_KEY_XYZ_14|recovery_mail_14@gmail.com|pass_mail\n100044626977815|pass_secret_via_15|2FA_SECRET_KEY_XYZ_15|recovery_mail_15@gmail.com|pass_mail', 15, 2, 'active', '2026-07-17 18:43:10', '2026-07-17 18:43:10', 'lo hang mau #4hp6p'),
('019f72e4-44d8-705d-9d9e-1826ecefc1cf', '019f72e4-4431-71b9-a604-974c3e11ecfe', 'Lô hàng mẫu #HERR4', '10001322130192|pass_secret_via_2|2FA_SECRET_KEY_XYZ_2|recovery_mail_2@gmail.com|pass_mail\n10001322130193|pass_secret_via_3|2FA_SECRET_KEY_XYZ_3|recovery_mail_3@gmail.com|pass_mail\n10001322130194|pass_secret_via_4|2FA_SECRET_KEY_XYZ_4|recovery_mail_4@gmail.com|pass_mail\n10001322130195|pass_secret_via_5|2FA_SECRET_KEY_XYZ_5|recovery_mail_5@gmail.com|pass_mail\n10001322130196|pass_secret_via_6|2FA_SECRET_KEY_XYZ_6|recovery_mail_6@gmail.com|pass_mail\n10001322130197|pass_secret_via_7|2FA_SECRET_KEY_XYZ_7|recovery_mail_7@gmail.com|pass_mail\n10001322130198|pass_secret_via_8|2FA_SECRET_KEY_XYZ_8|recovery_mail_8@gmail.com|pass_mail\n10001322130199|pass_secret_via_9|2FA_SECRET_KEY_XYZ_9|recovery_mail_9@gmail.com|pass_mail\n100013221301910|pass_secret_via_10|2FA_SECRET_KEY_XYZ_10|recovery_mail_10@gmail.com|pass_mail\n100013221301911|pass_secret_via_11|2FA_SECRET_KEY_XYZ_11|recovery_mail_11@gmail.com|pass_mail\n100013221301912|pass_secret_via_12|2FA_SECRET_KEY_XYZ_12|recovery_mail_12@gmail.com|pass_mail\n100013221301913|pass_secret_via_13|2FA_SECRET_KEY_XYZ_13|recovery_mail_13@gmail.com|pass_mail\n100013221301914|pass_secret_via_14|2FA_SECRET_KEY_XYZ_14|recovery_mail_14@gmail.com|pass_mail\n100013221301915|pass_secret_via_15|2FA_SECRET_KEY_XYZ_15|recovery_mail_15@gmail.com|pass_mail', 15, 1, 'active', '2026-07-17 18:43:10', '2026-07-17 18:43:10', 'lo hang mau #herr4'),
('019f72e4-44db-7036-816d-0a2a379f8beb', '019f72e4-4434-73ff-9fc5-1089de0a3dc3', 'Lô hàng mẫu #CJABI', '10008592853325|pass_secret_via_5|2FA_SECRET_KEY_XYZ_5|recovery_mail_5@gmail.com|pass_mail\n10008592853326|pass_secret_via_6|2FA_SECRET_KEY_XYZ_6|recovery_mail_6@gmail.com|pass_mail\n10008592853327|pass_secret_via_7|2FA_SECRET_KEY_XYZ_7|recovery_mail_7@gmail.com|pass_mail\n10008592853328|pass_secret_via_8|2FA_SECRET_KEY_XYZ_8|recovery_mail_8@gmail.com|pass_mail\n10008592853329|pass_secret_via_9|2FA_SECRET_KEY_XYZ_9|recovery_mail_9@gmail.com|pass_mail\n100085928533210|pass_secret_via_10|2FA_SECRET_KEY_XYZ_10|recovery_mail_10@gmail.com|pass_mail\n100085928533211|pass_secret_via_11|2FA_SECRET_KEY_XYZ_11|recovery_mail_11@gmail.com|pass_mail\n100085928533212|pass_secret_via_12|2FA_SECRET_KEY_XYZ_12|recovery_mail_12@gmail.com|pass_mail\n100085928533213|pass_secret_via_13|2FA_SECRET_KEY_XYZ_13|recovery_mail_13@gmail.com|pass_mail\n100085928533214|pass_secret_via_14|2FA_SECRET_KEY_XYZ_14|recovery_mail_14@gmail.com|pass_mail\n100085928533215|pass_secret_via_15|2FA_SECRET_KEY_XYZ_15|recovery_mail_15@gmail.com|pass_mail', 15, 4, 'active', '2026-07-17 18:43:10', '2026-07-17 18:43:10', 'lo hang mau #cjabi'),
('019f72e4-44de-7367-80a2-adf5087c1772', '019f72e4-4437-725d-9666-11328c3eed2c', 'Lô hàng mẫu #DDDTB', '10002336087963|pass_secret_via_3|2FA_SECRET_KEY_XYZ_3|recovery_mail_3@gmail.com|pass_mail\n10002336087964|pass_secret_via_4|2FA_SECRET_KEY_XYZ_4|recovery_mail_4@gmail.com|pass_mail\n10002336087965|pass_secret_via_5|2FA_SECRET_KEY_XYZ_5|recovery_mail_5@gmail.com|pass_mail\n10002336087966|pass_secret_via_6|2FA_SECRET_KEY_XYZ_6|recovery_mail_6@gmail.com|pass_mail\n10002336087967|pass_secret_via_7|2FA_SECRET_KEY_XYZ_7|recovery_mail_7@gmail.com|pass_mail\n10002336087968|pass_secret_via_8|2FA_SECRET_KEY_XYZ_8|recovery_mail_8@gmail.com|pass_mail\n10002336087969|pass_secret_via_9|2FA_SECRET_KEY_XYZ_9|recovery_mail_9@gmail.com|pass_mail\n100023360879610|pass_secret_via_10|2FA_SECRET_KEY_XYZ_10|recovery_mail_10@gmail.com|pass_mail\n100023360879611|pass_secret_via_11|2FA_SECRET_KEY_XYZ_11|recovery_mail_11@gmail.com|pass_mail\n100023360879612|pass_secret_via_12|2FA_SECRET_KEY_XYZ_12|recovery_mail_12@gmail.com|pass_mail\n100023360879613|pass_secret_via_13|2FA_SECRET_KEY_XYZ_13|recovery_mail_13@gmail.com|pass_mail\n100023360879614|pass_secret_via_14|2FA_SECRET_KEY_XYZ_14|recovery_mail_14@gmail.com|pass_mail\n100023360879615|pass_secret_via_15|2FA_SECRET_KEY_XYZ_15|recovery_mail_15@gmail.com|pass_mail', 15, 2, 'active', '2026-07-17 18:43:10', '2026-07-17 18:43:10', 'lo hang mau #dddtb'),
('019f72e4-44e3-7200-81f0-d40dd595dee9', '019f72e4-443a-72b1-a4fe-c7a7261a075a', 'Lô hàng mẫu #SZKC5', '10001411352123|pass_secret_via_3|2FA_SECRET_KEY_XYZ_3|recovery_mail_3@gmail.com|pass_mail\n10001411352124|pass_secret_via_4|2FA_SECRET_KEY_XYZ_4|recovery_mail_4@gmail.com|pass_mail\n10001411352125|pass_secret_via_5|2FA_SECRET_KEY_XYZ_5|recovery_mail_5@gmail.com|pass_mail\n10001411352126|pass_secret_via_6|2FA_SECRET_KEY_XYZ_6|recovery_mail_6@gmail.com|pass_mail\n10001411352127|pass_secret_via_7|2FA_SECRET_KEY_XYZ_7|recovery_mail_7@gmail.com|pass_mail\n10001411352128|pass_secret_via_8|2FA_SECRET_KEY_XYZ_8|recovery_mail_8@gmail.com|pass_mail\n10001411352129|pass_secret_via_9|2FA_SECRET_KEY_XYZ_9|recovery_mail_9@gmail.com|pass_mail\n100014113521210|pass_secret_via_10|2FA_SECRET_KEY_XYZ_10|recovery_mail_10@gmail.com|pass_mail\n100014113521211|pass_secret_via_11|2FA_SECRET_KEY_XYZ_11|recovery_mail_11@gmail.com|pass_mail\n100014113521212|pass_secret_via_12|2FA_SECRET_KEY_XYZ_12|recovery_mail_12@gmail.com|pass_mail\n100014113521213|pass_secret_via_13|2FA_SECRET_KEY_XYZ_13|recovery_mail_13@gmail.com|pass_mail\n100014113521214|pass_secret_via_14|2FA_SECRET_KEY_XYZ_14|recovery_mail_14@gmail.com|pass_mail\n100014113521215|pass_secret_via_15|2FA_SECRET_KEY_XYZ_15|recovery_mail_15@gmail.com|pass_mail', 15, 2, 'active', '2026-07-17 18:43:10', '2026-07-17 18:43:10', 'lo hang mau #szkc5'),
('019f72e4-44e6-7066-8ec1-ab81974d75f2', '019f72e4-443d-72f7-a3bc-d33495d545fe', 'Lô hàng mẫu #NSZFX', '10002931492147|pass_secret_via_7|2FA_SECRET_KEY_XYZ_7|recovery_mail_7@gmail.com|pass_mail\n10002931492148|pass_secret_via_8|2FA_SECRET_KEY_XYZ_8|recovery_mail_8@gmail.com|pass_mail\n10002931492149|pass_secret_via_9|2FA_SECRET_KEY_XYZ_9|recovery_mail_9@gmail.com|pass_mail\n100029314921410|pass_secret_via_10|2FA_SECRET_KEY_XYZ_10|recovery_mail_10@gmail.com|pass_mail\n100029314921411|pass_secret_via_11|2FA_SECRET_KEY_XYZ_11|recovery_mail_11@gmail.com|pass_mail\n100029314921412|pass_secret_via_12|2FA_SECRET_KEY_XYZ_12|recovery_mail_12@gmail.com|pass_mail\n100029314921413|pass_secret_via_13|2FA_SECRET_KEY_XYZ_13|recovery_mail_13@gmail.com|pass_mail\n100029314921414|pass_secret_via_14|2FA_SECRET_KEY_XYZ_14|recovery_mail_14@gmail.com|pass_mail\n100029314921415|pass_secret_via_15|2FA_SECRET_KEY_XYZ_15|recovery_mail_15@gmail.com|pass_mail', 15, 6, 'active', '2026-07-17 18:43:10', '2026-07-17 18:43:10', 'lo hang mau #nszfx'),
('019f72e4-44e9-72ce-8e9e-1b4a1abbbf10', '019f72e4-4441-7007-92ee-a49e8f5e6edc', 'Lô hàng mẫu #IWK9Q', '10002411378192|pass_secret_via_2|2FA_SECRET_KEY_XYZ_2|recovery_mail_2@gmail.com|pass_mail\n10002411378193|pass_secret_via_3|2FA_SECRET_KEY_XYZ_3|recovery_mail_3@gmail.com|pass_mail\n10002411378194|pass_secret_via_4|2FA_SECRET_KEY_XYZ_4|recovery_mail_4@gmail.com|pass_mail\n10002411378195|pass_secret_via_5|2FA_SECRET_KEY_XYZ_5|recovery_mail_5@gmail.com|pass_mail\n10002411378196|pass_secret_via_6|2FA_SECRET_KEY_XYZ_6|recovery_mail_6@gmail.com|pass_mail\n10002411378197|pass_secret_via_7|2FA_SECRET_KEY_XYZ_7|recovery_mail_7@gmail.com|pass_mail\n10002411378198|pass_secret_via_8|2FA_SECRET_KEY_XYZ_8|recovery_mail_8@gmail.com|pass_mail\n10002411378199|pass_secret_via_9|2FA_SECRET_KEY_XYZ_9|recovery_mail_9@gmail.com|pass_mail\n100024113781910|pass_secret_via_10|2FA_SECRET_KEY_XYZ_10|recovery_mail_10@gmail.com|pass_mail\n100024113781911|pass_secret_via_11|2FA_SECRET_KEY_XYZ_11|recovery_mail_11@gmail.com|pass_mail\n100024113781912|pass_secret_via_12|2FA_SECRET_KEY_XYZ_12|recovery_mail_12@gmail.com|pass_mail\n100024113781913|pass_secret_via_13|2FA_SECRET_KEY_XYZ_13|recovery_mail_13@gmail.com|pass_mail\n100024113781914|pass_secret_via_14|2FA_SECRET_KEY_XYZ_14|recovery_mail_14@gmail.com|pass_mail\n100024113781915|pass_secret_via_15|2FA_SECRET_KEY_XYZ_15|recovery_mail_15@gmail.com|pass_mail', 15, 1, 'active', '2026-07-17 18:43:10', '2026-07-17 18:43:11', 'lo hang mau #iwk9q'),
('019f72e4-44ec-73d1-9717-dd748b7c353a', '019f72e4-4444-71e3-a96f-7d9280b93b51', 'Lô hàng mẫu #QTYNM', '10008068252215|pass_secret_via_5|2FA_SECRET_KEY_XYZ_5|recovery_mail_5@gmail.com|pass_mail\n10008068252216|pass_secret_via_6|2FA_SECRET_KEY_XYZ_6|recovery_mail_6@gmail.com|pass_mail\n10008068252217|pass_secret_via_7|2FA_SECRET_KEY_XYZ_7|recovery_mail_7@gmail.com|pass_mail\n10008068252218|pass_secret_via_8|2FA_SECRET_KEY_XYZ_8|recovery_mail_8@gmail.com|pass_mail\n10008068252219|pass_secret_via_9|2FA_SECRET_KEY_XYZ_9|recovery_mail_9@gmail.com|pass_mail\n100080682522110|pass_secret_via_10|2FA_SECRET_KEY_XYZ_10|recovery_mail_10@gmail.com|pass_mail\n100080682522111|pass_secret_via_11|2FA_SECRET_KEY_XYZ_11|recovery_mail_11@gmail.com|pass_mail\n100080682522112|pass_secret_via_12|2FA_SECRET_KEY_XYZ_12|recovery_mail_12@gmail.com|pass_mail\n100080682522113|pass_secret_via_13|2FA_SECRET_KEY_XYZ_13|recovery_mail_13@gmail.com|pass_mail\n100080682522114|pass_secret_via_14|2FA_SECRET_KEY_XYZ_14|recovery_mail_14@gmail.com|pass_mail\n100080682522115|pass_secret_via_15|2FA_SECRET_KEY_XYZ_15|recovery_mail_15@gmail.com|pass_mail', 15, 4, 'active', '2026-07-17 18:43:10', '2026-07-17 18:43:10', 'lo hang mau #qtynm');
INSERT INTO `inventory_batches` (`id`, `product_id`, `batch_name`, `keys_pool`, `initial_count`, `sold_count`, `status`, `created_at`, `updated_at`, `normalized_batch_name`) VALUES
('019f72e4-44ef-73ca-bcf5-fe78400cf3dc', '019f72e4-4446-725f-bbee-9b324af6cb89', 'Lô hàng mẫu #KENGX', '10004757757791|pass_secret_via_1|2FA_SECRET_KEY_XYZ_1|recovery_mail_1@gmail.com|pass_mail\n10004757757792|pass_secret_via_2|2FA_SECRET_KEY_XYZ_2|recovery_mail_2@gmail.com|pass_mail\n10004757757793|pass_secret_via_3|2FA_SECRET_KEY_XYZ_3|recovery_mail_3@gmail.com|pass_mail\n10004757757794|pass_secret_via_4|2FA_SECRET_KEY_XYZ_4|recovery_mail_4@gmail.com|pass_mail\n10004757757795|pass_secret_via_5|2FA_SECRET_KEY_XYZ_5|recovery_mail_5@gmail.com|pass_mail\n10004757757796|pass_secret_via_6|2FA_SECRET_KEY_XYZ_6|recovery_mail_6@gmail.com|pass_mail\n10004757757797|pass_secret_via_7|2FA_SECRET_KEY_XYZ_7|recovery_mail_7@gmail.com|pass_mail\n10004757757798|pass_secret_via_8|2FA_SECRET_KEY_XYZ_8|recovery_mail_8@gmail.com|pass_mail\n10004757757799|pass_secret_via_9|2FA_SECRET_KEY_XYZ_9|recovery_mail_9@gmail.com|pass_mail\n100047577577910|pass_secret_via_10|2FA_SECRET_KEY_XYZ_10|recovery_mail_10@gmail.com|pass_mail\n100047577577911|pass_secret_via_11|2FA_SECRET_KEY_XYZ_11|recovery_mail_11@gmail.com|pass_mail\n100047577577912|pass_secret_via_12|2FA_SECRET_KEY_XYZ_12|recovery_mail_12@gmail.com|pass_mail\n100047577577913|pass_secret_via_13|2FA_SECRET_KEY_XYZ_13|recovery_mail_13@gmail.com|pass_mail\n100047577577914|pass_secret_via_14|2FA_SECRET_KEY_XYZ_14|recovery_mail_14@gmail.com|pass_mail\n100047577577915|pass_secret_via_15|2FA_SECRET_KEY_XYZ_15|recovery_mail_15@gmail.com|pass_mail', 15, 0, 'active', '2026-07-17 18:43:10', '2026-07-17 18:43:10', 'lo hang mau #kengx'),
('019f72e4-44f2-73c6-93cd-0b391430c9ca', '019f72e4-4449-7341-ac7c-283f59c351ce', 'Lô hàng mẫu #RNSOC', '10005938566723|pass_secret_via_3|2FA_SECRET_KEY_XYZ_3|recovery_mail_3@gmail.com|pass_mail\n10005938566724|pass_secret_via_4|2FA_SECRET_KEY_XYZ_4|recovery_mail_4@gmail.com|pass_mail\n10005938566725|pass_secret_via_5|2FA_SECRET_KEY_XYZ_5|recovery_mail_5@gmail.com|pass_mail\n10005938566726|pass_secret_via_6|2FA_SECRET_KEY_XYZ_6|recovery_mail_6@gmail.com|pass_mail\n10005938566727|pass_secret_via_7|2FA_SECRET_KEY_XYZ_7|recovery_mail_7@gmail.com|pass_mail\n10005938566728|pass_secret_via_8|2FA_SECRET_KEY_XYZ_8|recovery_mail_8@gmail.com|pass_mail\n10005938566729|pass_secret_via_9|2FA_SECRET_KEY_XYZ_9|recovery_mail_9@gmail.com|pass_mail\n100059385667210|pass_secret_via_10|2FA_SECRET_KEY_XYZ_10|recovery_mail_10@gmail.com|pass_mail\n100059385667211|pass_secret_via_11|2FA_SECRET_KEY_XYZ_11|recovery_mail_11@gmail.com|pass_mail\n100059385667212|pass_secret_via_12|2FA_SECRET_KEY_XYZ_12|recovery_mail_12@gmail.com|pass_mail\n100059385667213|pass_secret_via_13|2FA_SECRET_KEY_XYZ_13|recovery_mail_13@gmail.com|pass_mail\n100059385667214|pass_secret_via_14|2FA_SECRET_KEY_XYZ_14|recovery_mail_14@gmail.com|pass_mail\n100059385667215|pass_secret_via_15|2FA_SECRET_KEY_XYZ_15|recovery_mail_15@gmail.com|pass_mail', 15, 2, 'active', '2026-07-17 18:43:10', '2026-07-17 18:43:10', 'lo hang mau #rnsoc'),
('019f72e4-44f5-72d3-8a31-cf0cbf7c3719', '019f72e4-444c-705b-9598-9eed54e41cdc', 'Lô hàng mẫu #RZQGY', '10004644639044|pass_secret_via_4|2FA_SECRET_KEY_XYZ_4|recovery_mail_4@gmail.com|pass_mail\n10004644639045|pass_secret_via_5|2FA_SECRET_KEY_XYZ_5|recovery_mail_5@gmail.com|pass_mail\n10004644639046|pass_secret_via_6|2FA_SECRET_KEY_XYZ_6|recovery_mail_6@gmail.com|pass_mail\n10004644639047|pass_secret_via_7|2FA_SECRET_KEY_XYZ_7|recovery_mail_7@gmail.com|pass_mail\n10004644639048|pass_secret_via_8|2FA_SECRET_KEY_XYZ_8|recovery_mail_8@gmail.com|pass_mail\n10004644639049|pass_secret_via_9|2FA_SECRET_KEY_XYZ_9|recovery_mail_9@gmail.com|pass_mail\n100046446390410|pass_secret_via_10|2FA_SECRET_KEY_XYZ_10|recovery_mail_10@gmail.com|pass_mail\n100046446390411|pass_secret_via_11|2FA_SECRET_KEY_XYZ_11|recovery_mail_11@gmail.com|pass_mail\n100046446390412|pass_secret_via_12|2FA_SECRET_KEY_XYZ_12|recovery_mail_12@gmail.com|pass_mail\n100046446390413|pass_secret_via_13|2FA_SECRET_KEY_XYZ_13|recovery_mail_13@gmail.com|pass_mail\n100046446390414|pass_secret_via_14|2FA_SECRET_KEY_XYZ_14|recovery_mail_14@gmail.com|pass_mail\n100046446390415|pass_secret_via_15|2FA_SECRET_KEY_XYZ_15|recovery_mail_15@gmail.com|pass_mail', 15, 3, 'active', '2026-07-17 18:43:10', '2026-07-17 18:43:10', 'lo hang mau #rzqgy'),
('019f72e4-44f8-73ac-b7d3-bb32155abc72', '019f72e4-4455-73ac-b73e-b9d52ee03871', 'Lô hàng mẫu #RVUPW', '10007591293451|pass_secret_via_1|2FA_SECRET_KEY_XYZ_1|recovery_mail_1@gmail.com|pass_mail\n10007591293452|pass_secret_via_2|2FA_SECRET_KEY_XYZ_2|recovery_mail_2@gmail.com|pass_mail\n10007591293453|pass_secret_via_3|2FA_SECRET_KEY_XYZ_3|recovery_mail_3@gmail.com|pass_mail\n10007591293454|pass_secret_via_4|2FA_SECRET_KEY_XYZ_4|recovery_mail_4@gmail.com|pass_mail\n10007591293455|pass_secret_via_5|2FA_SECRET_KEY_XYZ_5|recovery_mail_5@gmail.com|pass_mail\n10007591293456|pass_secret_via_6|2FA_SECRET_KEY_XYZ_6|recovery_mail_6@gmail.com|pass_mail\n10007591293457|pass_secret_via_7|2FA_SECRET_KEY_XYZ_7|recovery_mail_7@gmail.com|pass_mail\n10007591293458|pass_secret_via_8|2FA_SECRET_KEY_XYZ_8|recovery_mail_8@gmail.com|pass_mail\n10007591293459|pass_secret_via_9|2FA_SECRET_KEY_XYZ_9|recovery_mail_9@gmail.com|pass_mail\n100075912934510|pass_secret_via_10|2FA_SECRET_KEY_XYZ_10|recovery_mail_10@gmail.com|pass_mail\n100075912934511|pass_secret_via_11|2FA_SECRET_KEY_XYZ_11|recovery_mail_11@gmail.com|pass_mail\n100075912934512|pass_secret_via_12|2FA_SECRET_KEY_XYZ_12|recovery_mail_12@gmail.com|pass_mail\n100075912934513|pass_secret_via_13|2FA_SECRET_KEY_XYZ_13|recovery_mail_13@gmail.com|pass_mail\n100075912934514|pass_secret_via_14|2FA_SECRET_KEY_XYZ_14|recovery_mail_14@gmail.com|pass_mail\n100075912934515|pass_secret_via_15|2FA_SECRET_KEY_XYZ_15|recovery_mail_15@gmail.com|pass_mail', 15, 0, 'active', '2026-07-17 18:43:10', '2026-07-17 18:43:10', 'lo hang mau #rvupw'),
('019f72e4-44fb-72e2-81e4-b514e42a4df0', '019f72e4-4458-706d-9f7a-5a7006eb8327', 'Lô hàng mẫu #OZOLZ', '10005026589212|pass_secret_via_2|2FA_SECRET_KEY_XYZ_2|recovery_mail_2@gmail.com|pass_mail\n10005026589213|pass_secret_via_3|2FA_SECRET_KEY_XYZ_3|recovery_mail_3@gmail.com|pass_mail\n10005026589214|pass_secret_via_4|2FA_SECRET_KEY_XYZ_4|recovery_mail_4@gmail.com|pass_mail\n10005026589215|pass_secret_via_5|2FA_SECRET_KEY_XYZ_5|recovery_mail_5@gmail.com|pass_mail\n10005026589216|pass_secret_via_6|2FA_SECRET_KEY_XYZ_6|recovery_mail_6@gmail.com|pass_mail\n10005026589217|pass_secret_via_7|2FA_SECRET_KEY_XYZ_7|recovery_mail_7@gmail.com|pass_mail\n10005026589218|pass_secret_via_8|2FA_SECRET_KEY_XYZ_8|recovery_mail_8@gmail.com|pass_mail\n10005026589219|pass_secret_via_9|2FA_SECRET_KEY_XYZ_9|recovery_mail_9@gmail.com|pass_mail\n100050265892110|pass_secret_via_10|2FA_SECRET_KEY_XYZ_10|recovery_mail_10@gmail.com|pass_mail\n100050265892111|pass_secret_via_11|2FA_SECRET_KEY_XYZ_11|recovery_mail_11@gmail.com|pass_mail\n100050265892112|pass_secret_via_12|2FA_SECRET_KEY_XYZ_12|recovery_mail_12@gmail.com|pass_mail\n100050265892113|pass_secret_via_13|2FA_SECRET_KEY_XYZ_13|recovery_mail_13@gmail.com|pass_mail\n100050265892114|pass_secret_via_14|2FA_SECRET_KEY_XYZ_14|recovery_mail_14@gmail.com|pass_mail\n100050265892115|pass_secret_via_15|2FA_SECRET_KEY_XYZ_15|recovery_mail_15@gmail.com|pass_mail', 15, 1, 'active', '2026-07-17 18:43:10', '2026-07-17 18:43:10', 'lo hang mau #ozolz'),
('019f72e4-4500-70d9-92c7-1995acefa605', '019f72e4-445a-7073-9d49-85032728c58d', 'Lô hàng mẫu #2VA5Y', '10009417227743|pass_secret_via_3|2FA_SECRET_KEY_XYZ_3|recovery_mail_3@gmail.com|pass_mail\n10009417227744|pass_secret_via_4|2FA_SECRET_KEY_XYZ_4|recovery_mail_4@gmail.com|pass_mail\n10009417227745|pass_secret_via_5|2FA_SECRET_KEY_XYZ_5|recovery_mail_5@gmail.com|pass_mail\n10009417227746|pass_secret_via_6|2FA_SECRET_KEY_XYZ_6|recovery_mail_6@gmail.com|pass_mail\n10009417227747|pass_secret_via_7|2FA_SECRET_KEY_XYZ_7|recovery_mail_7@gmail.com|pass_mail\n10009417227748|pass_secret_via_8|2FA_SECRET_KEY_XYZ_8|recovery_mail_8@gmail.com|pass_mail\n10009417227749|pass_secret_via_9|2FA_SECRET_KEY_XYZ_9|recovery_mail_9@gmail.com|pass_mail\n100094172277410|pass_secret_via_10|2FA_SECRET_KEY_XYZ_10|recovery_mail_10@gmail.com|pass_mail\n100094172277411|pass_secret_via_11|2FA_SECRET_KEY_XYZ_11|recovery_mail_11@gmail.com|pass_mail\n100094172277412|pass_secret_via_12|2FA_SECRET_KEY_XYZ_12|recovery_mail_12@gmail.com|pass_mail\n100094172277413|pass_secret_via_13|2FA_SECRET_KEY_XYZ_13|recovery_mail_13@gmail.com|pass_mail\n100094172277414|pass_secret_via_14|2FA_SECRET_KEY_XYZ_14|recovery_mail_14@gmail.com|pass_mail\n100094172277415|pass_secret_via_15|2FA_SECRET_KEY_XYZ_15|recovery_mail_15@gmail.com|pass_mail', 15, 2, 'active', '2026-07-17 18:43:10', '2026-07-17 18:43:10', 'lo hang mau #2va5y'),
('019f72e4-4503-7368-b8c8-560b8bd2bfd6', '019f72e4-445d-7206-b498-00aefdf1d060', 'Lô hàng mẫu #CAH8X', 'mail_seed_account_3@hotmail.com|pass_mail_3|recovery_seed_mail_3@outlook.com\nmail_seed_account_4@hotmail.com|pass_mail_4|recovery_seed_mail_4@outlook.com\nmail_seed_account_5@hotmail.com|pass_mail_5|recovery_seed_mail_5@outlook.com\nmail_seed_account_6@hotmail.com|pass_mail_6|recovery_seed_mail_6@outlook.com\nmail_seed_account_7@hotmail.com|pass_mail_7|recovery_seed_mail_7@outlook.com\nmail_seed_account_8@hotmail.com|pass_mail_8|recovery_seed_mail_8@outlook.com\nmail_seed_account_9@hotmail.com|pass_mail_9|recovery_seed_mail_9@outlook.com\nmail_seed_account_10@hotmail.com|pass_mail_10|recovery_seed_mail_10@outlook.com\nmail_seed_account_11@hotmail.com|pass_mail_11|recovery_seed_mail_11@outlook.com\nmail_seed_account_12@hotmail.com|pass_mail_12|recovery_seed_mail_12@outlook.com\nmail_seed_account_13@hotmail.com|pass_mail_13|recovery_seed_mail_13@outlook.com\nmail_seed_account_14@hotmail.com|pass_mail_14|recovery_seed_mail_14@outlook.com\nmail_seed_account_15@hotmail.com|pass_mail_15|recovery_seed_mail_15@outlook.com', 15, 2, 'active', '2026-07-17 18:43:10', '2026-07-17 18:43:10', 'lo hang mau #cah8x'),
('019f72e4-4507-7220-be87-4a23c426bba3', '019f72e4-4460-70f8-be30-3aa8e9821da6', 'Lô hàng mẫu #BXU3D', 'mail_seed_account_1@hotmail.com|pass_mail_1|recovery_seed_mail_1@outlook.com\nmail_seed_account_2@hotmail.com|pass_mail_2|recovery_seed_mail_2@outlook.com\nmail_seed_account_3@hotmail.com|pass_mail_3|recovery_seed_mail_3@outlook.com\nmail_seed_account_4@hotmail.com|pass_mail_4|recovery_seed_mail_4@outlook.com\nmail_seed_account_5@hotmail.com|pass_mail_5|recovery_seed_mail_5@outlook.com\nmail_seed_account_6@hotmail.com|pass_mail_6|recovery_seed_mail_6@outlook.com\nmail_seed_account_7@hotmail.com|pass_mail_7|recovery_seed_mail_7@outlook.com\nmail_seed_account_8@hotmail.com|pass_mail_8|recovery_seed_mail_8@outlook.com\nmail_seed_account_9@hotmail.com|pass_mail_9|recovery_seed_mail_9@outlook.com\nmail_seed_account_10@hotmail.com|pass_mail_10|recovery_seed_mail_10@outlook.com\nmail_seed_account_11@hotmail.com|pass_mail_11|recovery_seed_mail_11@outlook.com\nmail_seed_account_12@hotmail.com|pass_mail_12|recovery_seed_mail_12@outlook.com\nmail_seed_account_13@hotmail.com|pass_mail_13|recovery_seed_mail_13@outlook.com\nmail_seed_account_14@hotmail.com|pass_mail_14|recovery_seed_mail_14@outlook.com\nmail_seed_account_15@hotmail.com|pass_mail_15|recovery_seed_mail_15@outlook.com', 15, 0, 'active', '2026-07-17 18:43:10', '2026-07-17 18:43:10', 'lo hang mau #bxu3d'),
('019f72e4-450a-7026-b6db-3c22f5c1c018', '019f72e4-4464-72eb-a342-e90ba3fb0d8f', 'Lô hàng mẫu #MUJI4', '10004053829491|pass_secret_via_1|2FA_SECRET_KEY_XYZ_1|recovery_mail_1@gmail.com|pass_mail\n10004053829492|pass_secret_via_2|2FA_SECRET_KEY_XYZ_2|recovery_mail_2@gmail.com|pass_mail\n10004053829493|pass_secret_via_3|2FA_SECRET_KEY_XYZ_3|recovery_mail_3@gmail.com|pass_mail\n10004053829494|pass_secret_via_4|2FA_SECRET_KEY_XYZ_4|recovery_mail_4@gmail.com|pass_mail\n10004053829495|pass_secret_via_5|2FA_SECRET_KEY_XYZ_5|recovery_mail_5@gmail.com|pass_mail\n10004053829496|pass_secret_via_6|2FA_SECRET_KEY_XYZ_6|recovery_mail_6@gmail.com|pass_mail\n10004053829497|pass_secret_via_7|2FA_SECRET_KEY_XYZ_7|recovery_mail_7@gmail.com|pass_mail\n10004053829498|pass_secret_via_8|2FA_SECRET_KEY_XYZ_8|recovery_mail_8@gmail.com|pass_mail\n10004053829499|pass_secret_via_9|2FA_SECRET_KEY_XYZ_9|recovery_mail_9@gmail.com|pass_mail\n100040538294910|pass_secret_via_10|2FA_SECRET_KEY_XYZ_10|recovery_mail_10@gmail.com|pass_mail\n100040538294911|pass_secret_via_11|2FA_SECRET_KEY_XYZ_11|recovery_mail_11@gmail.com|pass_mail\n100040538294912|pass_secret_via_12|2FA_SECRET_KEY_XYZ_12|recovery_mail_12@gmail.com|pass_mail\n100040538294913|pass_secret_via_13|2FA_SECRET_KEY_XYZ_13|recovery_mail_13@gmail.com|pass_mail\n100040538294914|pass_secret_via_14|2FA_SECRET_KEY_XYZ_14|recovery_mail_14@gmail.com|pass_mail\n100040538294915|pass_secret_via_15|2FA_SECRET_KEY_XYZ_15|recovery_mail_15@gmail.com|pass_mail', 15, 0, 'active', '2026-07-17 18:43:10', '2026-07-17 18:43:10', 'lo hang mau #muji4'),
('019f72e4-450d-72cd-8208-051cf85ea2ca', '019f72e4-4467-705a-92e4-430aef43d2b2', 'Lô hàng mẫu #ZDNDQ', '10008896104295|pass_secret_via_5|2FA_SECRET_KEY_XYZ_5|recovery_mail_5@gmail.com|pass_mail\n10008896104296|pass_secret_via_6|2FA_SECRET_KEY_XYZ_6|recovery_mail_6@gmail.com|pass_mail\n10008896104297|pass_secret_via_7|2FA_SECRET_KEY_XYZ_7|recovery_mail_7@gmail.com|pass_mail\n10008896104298|pass_secret_via_8|2FA_SECRET_KEY_XYZ_8|recovery_mail_8@gmail.com|pass_mail\n10008896104299|pass_secret_via_9|2FA_SECRET_KEY_XYZ_9|recovery_mail_9@gmail.com|pass_mail\n100088961042910|pass_secret_via_10|2FA_SECRET_KEY_XYZ_10|recovery_mail_10@gmail.com|pass_mail\n100088961042911|pass_secret_via_11|2FA_SECRET_KEY_XYZ_11|recovery_mail_11@gmail.com|pass_mail\n100088961042912|pass_secret_via_12|2FA_SECRET_KEY_XYZ_12|recovery_mail_12@gmail.com|pass_mail\n100088961042913|pass_secret_via_13|2FA_SECRET_KEY_XYZ_13|recovery_mail_13@gmail.com|pass_mail\n100088961042914|pass_secret_via_14|2FA_SECRET_KEY_XYZ_14|recovery_mail_14@gmail.com|pass_mail\n100088961042915|pass_secret_via_15|2FA_SECRET_KEY_XYZ_15|recovery_mail_15@gmail.com|pass_mail', 15, 4, 'active', '2026-07-17 18:43:10', '2026-07-17 18:43:10', 'lo hang mau #zdndq'),
('019f72e4-4510-7282-ba24-a31fa21288a1', '019f72e4-4469-7093-92cb-2abc5a198d49', 'Lô hàng mẫu #HQFRH', '10006153788472|pass_secret_via_2|2FA_SECRET_KEY_XYZ_2|recovery_mail_2@gmail.com|pass_mail\n10006153788473|pass_secret_via_3|2FA_SECRET_KEY_XYZ_3|recovery_mail_3@gmail.com|pass_mail\n10006153788474|pass_secret_via_4|2FA_SECRET_KEY_XYZ_4|recovery_mail_4@gmail.com|pass_mail\n10006153788475|pass_secret_via_5|2FA_SECRET_KEY_XYZ_5|recovery_mail_5@gmail.com|pass_mail\n10006153788476|pass_secret_via_6|2FA_SECRET_KEY_XYZ_6|recovery_mail_6@gmail.com|pass_mail\n10006153788477|pass_secret_via_7|2FA_SECRET_KEY_XYZ_7|recovery_mail_7@gmail.com|pass_mail\n10006153788478|pass_secret_via_8|2FA_SECRET_KEY_XYZ_8|recovery_mail_8@gmail.com|pass_mail\n10006153788479|pass_secret_via_9|2FA_SECRET_KEY_XYZ_9|recovery_mail_9@gmail.com|pass_mail\n100061537884710|pass_secret_via_10|2FA_SECRET_KEY_XYZ_10|recovery_mail_10@gmail.com|pass_mail\n100061537884711|pass_secret_via_11|2FA_SECRET_KEY_XYZ_11|recovery_mail_11@gmail.com|pass_mail\n100061537884712|pass_secret_via_12|2FA_SECRET_KEY_XYZ_12|recovery_mail_12@gmail.com|pass_mail\n100061537884713|pass_secret_via_13|2FA_SECRET_KEY_XYZ_13|recovery_mail_13@gmail.com|pass_mail\n100061537884714|pass_secret_via_14|2FA_SECRET_KEY_XYZ_14|recovery_mail_14@gmail.com|pass_mail\n100061537884715|pass_secret_via_15|2FA_SECRET_KEY_XYZ_15|recovery_mail_15@gmail.com|pass_mail', 15, 1, 'active', '2026-07-17 18:43:10', '2026-07-17 18:43:10', 'lo hang mau #hqfrh'),
('019f72e4-4513-705a-bab8-207fe956c150', '019f72e4-446c-71b3-a5e8-13ccead641d3', 'Lô hàng mẫu #DDUE5', 'mail_seed_account_3@hotmail.com|pass_mail_3|recovery_seed_mail_3@outlook.com\nmail_seed_account_4@hotmail.com|pass_mail_4|recovery_seed_mail_4@outlook.com\nmail_seed_account_5@hotmail.com|pass_mail_5|recovery_seed_mail_5@outlook.com\nmail_seed_account_6@hotmail.com|pass_mail_6|recovery_seed_mail_6@outlook.com\nmail_seed_account_7@hotmail.com|pass_mail_7|recovery_seed_mail_7@outlook.com\nmail_seed_account_8@hotmail.com|pass_mail_8|recovery_seed_mail_8@outlook.com\nmail_seed_account_9@hotmail.com|pass_mail_9|recovery_seed_mail_9@outlook.com\nmail_seed_account_10@hotmail.com|pass_mail_10|recovery_seed_mail_10@outlook.com\nmail_seed_account_11@hotmail.com|pass_mail_11|recovery_seed_mail_11@outlook.com\nmail_seed_account_12@hotmail.com|pass_mail_12|recovery_seed_mail_12@outlook.com\nmail_seed_account_13@hotmail.com|pass_mail_13|recovery_seed_mail_13@outlook.com\nmail_seed_account_14@hotmail.com|pass_mail_14|recovery_seed_mail_14@outlook.com\nmail_seed_account_15@hotmail.com|pass_mail_15|recovery_seed_mail_15@outlook.com', 15, 2, 'active', '2026-07-17 18:43:10', '2026-07-17 18:43:10', 'lo hang mau #ddue5'),
('019f72e4-4516-711e-bfd4-e8913e6d06cf', '019f72e4-446f-72a9-9218-b4c9c46a2f4b', 'Lô hàng mẫu #OFVOU', 'mail_seed_account_1@hotmail.com|pass_mail_1|recovery_seed_mail_1@outlook.com\nmail_seed_account_2@hotmail.com|pass_mail_2|recovery_seed_mail_2@outlook.com\nmail_seed_account_3@hotmail.com|pass_mail_3|recovery_seed_mail_3@outlook.com\nmail_seed_account_4@hotmail.com|pass_mail_4|recovery_seed_mail_4@outlook.com\nmail_seed_account_5@hotmail.com|pass_mail_5|recovery_seed_mail_5@outlook.com\nmail_seed_account_6@hotmail.com|pass_mail_6|recovery_seed_mail_6@outlook.com\nmail_seed_account_7@hotmail.com|pass_mail_7|recovery_seed_mail_7@outlook.com\nmail_seed_account_8@hotmail.com|pass_mail_8|recovery_seed_mail_8@outlook.com\nmail_seed_account_9@hotmail.com|pass_mail_9|recovery_seed_mail_9@outlook.com\nmail_seed_account_10@hotmail.com|pass_mail_10|recovery_seed_mail_10@outlook.com\nmail_seed_account_11@hotmail.com|pass_mail_11|recovery_seed_mail_11@outlook.com\nmail_seed_account_12@hotmail.com|pass_mail_12|recovery_seed_mail_12@outlook.com\nmail_seed_account_13@hotmail.com|pass_mail_13|recovery_seed_mail_13@outlook.com\nmail_seed_account_14@hotmail.com|pass_mail_14|recovery_seed_mail_14@outlook.com\nmail_seed_account_15@hotmail.com|pass_mail_15|recovery_seed_mail_15@outlook.com', 15, 0, 'active', '2026-07-17 18:43:10', '2026-07-17 18:43:10', 'lo hang mau #ofvou'),
('019f72e4-451a-7185-ba9c-314c3b3bf8db', '019f72e4-4472-7181-af3a-ff03af241587', 'Lô hàng mẫu #IVXTJ', 'ig_user_2|pass_ig_2|2FA_SECRET_XYZ_2|mail_ig_2@example.com\nig_user_3|pass_ig_3|2FA_SECRET_XYZ_3|mail_ig_3@example.com\nig_user_4|pass_ig_4|2FA_SECRET_XYZ_4|mail_ig_4@example.com\nig_user_5|pass_ig_5|2FA_SECRET_XYZ_5|mail_ig_5@example.com\nig_user_6|pass_ig_6|2FA_SECRET_XYZ_6|mail_ig_6@example.com\nig_user_7|pass_ig_7|2FA_SECRET_XYZ_7|mail_ig_7@example.com\nig_user_8|pass_ig_8|2FA_SECRET_XYZ_8|mail_ig_8@example.com\nig_user_9|pass_ig_9|2FA_SECRET_XYZ_9|mail_ig_9@example.com\nig_user_10|pass_ig_10|2FA_SECRET_XYZ_10|mail_ig_10@example.com\nig_user_11|pass_ig_11|2FA_SECRET_XYZ_11|mail_ig_11@example.com\nig_user_12|pass_ig_12|2FA_SECRET_XYZ_12|mail_ig_12@example.com\nig_user_13|pass_ig_13|2FA_SECRET_XYZ_13|mail_ig_13@example.com\nig_user_14|pass_ig_14|2FA_SECRET_XYZ_14|mail_ig_14@example.com\nig_user_15|pass_ig_15|2FA_SECRET_XYZ_15|mail_ig_15@example.com', 15, 1, 'active', '2026-07-17 18:43:10', '2026-07-17 18:43:10', 'lo hang mau #ivxtj'),
('019f72e4-451d-7000-a8dc-5974590243c0', '019f72e4-4475-7260-8404-7952387c6f06', 'Lô hàng mẫu #JLKVD', 'ig_user_4|pass_ig_4|2FA_SECRET_XYZ_4|mail_ig_4@example.com\nig_user_5|pass_ig_5|2FA_SECRET_XYZ_5|mail_ig_5@example.com\nig_user_6|pass_ig_6|2FA_SECRET_XYZ_6|mail_ig_6@example.com\nig_user_7|pass_ig_7|2FA_SECRET_XYZ_7|mail_ig_7@example.com\nig_user_8|pass_ig_8|2FA_SECRET_XYZ_8|mail_ig_8@example.com\nig_user_9|pass_ig_9|2FA_SECRET_XYZ_9|mail_ig_9@example.com\nig_user_10|pass_ig_10|2FA_SECRET_XYZ_10|mail_ig_10@example.com\nig_user_11|pass_ig_11|2FA_SECRET_XYZ_11|mail_ig_11@example.com\nig_user_12|pass_ig_12|2FA_SECRET_XYZ_12|mail_ig_12@example.com\nig_user_13|pass_ig_13|2FA_SECRET_XYZ_13|mail_ig_13@example.com\nig_user_14|pass_ig_14|2FA_SECRET_XYZ_14|mail_ig_14@example.com\nig_user_15|pass_ig_15|2FA_SECRET_XYZ_15|mail_ig_15@example.com', 15, 3, 'active', '2026-07-17 18:43:10', '2026-07-17 18:43:10', 'lo hang mau #jlkvd');

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
(14, '2026_07_18_015100_create_deposits_table', 1);

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `orders`
--

CREATE TABLE `orders` (
  `id` char(36) COLLATE utf8mb4_unicode_ci NOT NULL,
  `user_id` char(36) COLLATE utf8mb4_unicode_ci NOT NULL,
  `order_code` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `total_price` decimal(15,2) NOT NULL,
  `status` enum('pending','paid','cancelled') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'pending',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Đang đổ dữ liệu cho bảng `orders`
--

INSERT INTO `orders` (`id`, `user_id`, `order_code`, `total_price`, `status`, `created_at`, `updated_at`) VALUES
('019f72e4-4522-7031-a694-c67d647e49ed', '019f72e4-42dc-7207-872c-0c72d2e8c777', 'ORD-20260708-SBTVRY', 632000.00, 'paid', '2026-07-08 08:49:10', '2026-07-08 08:49:10'),
('019f72e4-4539-7112-bf46-2cd8bb98932d', '019f72e4-42dc-7207-872c-0c72d2e8c777', 'ORD-20260702-JCYLMV', 403000.00, 'pending', '2026-07-02 10:48:10', '2026-07-02 10:48:10'),
('019f72e4-454c-7167-bb63-b24d789dac1c', '019f72e4-3efb-70f5-8dc4-f584916ea928', 'ORD-20260623-ARJL8F', 718000.00, 'paid', '2026-06-23 13:06:10', '2026-06-23 13:06:10'),
('019f72e4-455e-71c0-8873-635863cd8539', '019f72e4-3c9a-7114-bbd9-de3a4f50ccd8', 'ORD-20260716-RD1C2Z', 312000.00, 'paid', '2026-07-15 23:24:10', '2026-07-15 23:24:10'),
('019f72e4-4568-716d-98ed-d665504889ed', '019f72e4-4088-727c-a54a-88e05338ba4f', 'ORD-20260627-NOQBHH', 594000.00, 'paid', '2026-06-26 18:55:10', '2026-06-26 18:55:10'),
('019f72e4-457a-7271-bf5c-dd30718c71fc', '019f72e4-3e34-70b1-b4e9-4b30694f24ff', 'ORD-20260701-JHQ6QW', 600000.00, 'paid', '2026-07-01 06:48:10', '2026-07-01 06:48:10'),
('019f72e4-4585-707a-9080-b9f095b765ca', '019f72e4-3efb-70f5-8dc4-f584916ea928', 'ORD-20260708-DB3YV0', 192000.00, 'paid', '2026-07-07 17:26:10', '2026-07-07 17:26:10'),
('019f72e4-4590-72ee-aada-d01e4d5ec712', '019f72e4-414f-701e-92e7-59e9943c0f8a', 'ORD-20260704-LXFUWD', 250000.00, 'paid', '2026-07-04 14:27:10', '2026-07-04 14:27:10'),
('019f72e4-459a-731f-8b65-373386698a98', '019f72e4-42dc-7207-872c-0c72d2e8c777', 'ORD-20260712-65CGBN', 528000.00, 'cancelled', '2026-07-12 12:18:10', '2026-07-12 12:18:10'),
('019f72e4-45a4-72eb-8e1a-8cb8e220055b', '019f72e4-43a7-71b6-bdea-e3dec18e4257', 'ORD-20260629-POCSFL', 14000.00, 'paid', '2026-06-28 22:20:10', '2026-06-28 22:20:10'),
('019f72e4-45af-70ed-80b6-df8d78f8f974', '019f72e4-3c9a-7114-bbd9-de3a4f50ccd8', 'ORD-20260708-XQIW2P', 692000.00, 'paid', '2026-07-08 06:01:10', '2026-07-08 06:01:10'),
('019f72e4-45c1-73c5-94f9-452cd3014b26', '019f72e4-3d69-73cd-8850-47d30ec2fbf6', 'ORD-20260619-DVTE64', 800.00, 'paid', '2026-06-18 18:54:10', '2026-06-18 18:54:10'),
('019f72e4-45cc-7346-94c5-3546cfe8485c', '019f72e4-3e34-70b1-b4e9-4b30694f24ff', 'ORD-20260704-HZCKLJ', 20000.00, 'paid', '2026-07-04 05:35:10', '2026-07-04 05:35:10'),
('019f72e4-45d7-71fe-80fa-70dbeba45469', '019f72e4-3efb-70f5-8dc4-f584916ea928', 'ORD-20260618-UM2R5D', 480000.00, 'paid', '2026-06-18 04:48:10', '2026-06-18 04:48:10'),
('019f72e4-45e2-73bc-9b53-fbba4c933772', '019f72e4-42dc-7207-872c-0c72d2e8c777', 'ORD-20260630-HGEWPO', 80000.00, 'paid', '2026-06-30 14:35:10', '2026-06-30 14:35:10'),
('019f72e4-45ec-7322-ab54-602ac9d9722d', '019f72e4-43a7-71b6-bdea-e3dec18e4257', 'ORD-20260621-UMDBUQ', 1300000.00, 'cancelled', '2026-06-21 03:32:10', '2026-06-21 03:32:10'),
('019f72e4-45f6-730c-91ea-776e533502ec', '019f72e4-4088-727c-a54a-88e05338ba4f', 'ORD-20260627-8PR4LM', 360000.00, 'paid', '2026-06-26 20:27:10', '2026-06-26 20:27:10'),
('019f72e4-4607-7116-92e6-3a7b98edb78d', '019f72e4-3d69-73cd-8850-47d30ec2fbf6', 'ORD-20260620-JBFOXK', 312000.00, 'paid', '2026-06-20 14:47:10', '2026-06-20 14:47:10'),
('019f72e4-4611-7052-99ce-ab1f8f124393', '019f72e4-3fc1-7078-934f-32be5ca40e1a', 'ORD-20260704-PCUABS', 5896000.00, 'paid', '2026-07-03 23:16:10', '2026-07-03 23:16:10'),
('019f72e4-4621-73ea-9992-edb128cacb2f', '019f72e4-43a7-71b6-bdea-e3dec18e4257', 'ORD-20260702-EES5HS', 284000.00, 'pending', '2026-07-02 05:23:10', '2026-07-02 05:23:10'),
('019f72e4-4632-70e7-bb3a-f1113b96fb93', '019f72e4-4088-727c-a54a-88e05338ba4f', 'ORD-20260706-QF1Y4D', 472000.00, 'paid', '2026-07-06 02:15:10', '2026-07-06 02:15:10'),
('019f72e4-4641-72c6-9739-f52a3cfb13f6', '019f72e4-43a7-71b6-bdea-e3dec18e4257', 'ORD-20260629-OVG13V', 494000.00, 'paid', '2026-06-28 22:55:10', '2026-06-28 22:55:10'),
('019f72e4-4652-72f3-93ee-86d382d70092', '019f72e4-3d69-73cd-8850-47d30ec2fbf6', 'ORD-20260623-UOJWRN', 360000.00, 'paid', '2026-06-23 07:16:10', '2026-06-23 07:16:10'),
('019f72e4-4663-7288-868c-8b26318f9659', '019f72e4-43a7-71b6-bdea-e3dec18e4257', 'ORD-20260627-OMBKHA', 160000.00, 'pending', '2026-06-27 14:41:10', '2026-06-27 14:41:10'),
('019f72e4-466d-7300-a239-eabb222c3dd2', '019f72e4-4088-727c-a54a-88e05338ba4f', 'ORD-20260618-EA24JW', 694000.00, 'paid', '2026-06-17 20:12:10', '2026-06-17 20:12:10'),
('019f72e4-467e-7288-8b60-90d7be745407', '019f72e4-4216-7277-9a90-0da9d4c8829f', 'ORD-20260702-L57S6F', 432000.00, 'pending', '2026-07-02 03:41:10', '2026-07-02 03:41:10'),
('019f72e4-4688-7211-b530-9f88e9d565c3', '019f72e4-3c9a-7114-bbd9-de3a4f50ccd8', 'ORD-20260717-1QQF4W', 504000.00, 'paid', '2026-07-17 05:12:10', '2026-07-17 05:12:10'),
('019f72e4-4698-702a-b931-1388a41dbfb4', '019f72e4-3c9a-7114-bbd9-de3a4f50ccd8', 'ORD-20260630-XEII2U', 490000.00, 'paid', '2026-06-30 01:13:10', '2026-06-30 01:13:10'),
('019f72e4-46a9-7276-acd1-8a1cb33c2bac', '019f72e4-3e34-70b1-b4e9-4b30694f24ff', 'ORD-20260703-NCYXVU', 980000.00, 'paid', '2026-07-02 21:28:10', '2026-07-02 21:28:10'),
('019f72e4-46ba-721f-a973-c425ca0508b9', '019f72e4-3d69-73cd-8850-47d30ec2fbf6', 'ORD-20260619-SVKXUO', 207000.00, 'paid', '2026-06-18 18:14:10', '2026-06-18 18:14:10'),
('019f72e4-46ca-733f-8827-750bb4794302', '019f72e4-3e34-70b1-b4e9-4b30694f24ff', 'ORD-20260628-VFDLXM', 1232000.00, 'paid', '2026-06-28 09:10:10', '2026-06-28 09:10:10'),
('019f72e4-46db-71e2-8351-ba04a385f943', '019f72e4-42dc-7207-872c-0c72d2e8c777', 'ORD-20260707-8ZGLTU', 180000.00, 'pending', '2026-07-07 14:04:10', '2026-07-07 14:04:10'),
('019f72e4-46e6-716d-bb0a-2229988a7737', '019f72e4-42dc-7207-872c-0c72d2e8c777', 'ORD-20260713-FFB2PC', 126000.00, 'paid', '2026-07-13 10:39:10', '2026-07-13 10:39:10'),
('019f72e4-46f7-72e5-bfea-bd013400b244', '019f72e4-3e34-70b1-b4e9-4b30694f24ff', 'ORD-20260621-TH4HKQ', 1400000.00, 'paid', '2026-06-21 08:34:10', '2026-06-21 08:34:10'),
('019f72e4-4707-7010-a40d-270ea1230cde', '019f72e4-3fc1-7078-934f-32be5ca40e1a', 'ORD-20260619-EDDP7M', 216000.00, 'paid', '2026-06-19 07:24:10', '2026-06-19 07:24:10'),
('019f72e4-4711-7247-9ca1-c2e738f53bb3', '019f72e4-4216-7277-9a90-0da9d4c8829f', 'ORD-20260620-NTRBUQ', 2902000.00, 'paid', '2026-06-20 12:40:10', '2026-06-20 12:40:10'),
('019f72e4-4721-72ca-b9a9-c5ac8226895a', '019f72e4-42dc-7207-872c-0c72d2e8c777', 'ORD-20260702-BOEDGI', 972000.00, 'pending', '2026-07-02 00:29:10', '2026-07-02 00:29:10'),
('019f72e4-4731-725b-a54e-68e391552339', '019f72e4-42dc-7207-872c-0c72d2e8c777', 'ORD-20260619-3QQEWS', 720000.00, 'paid', '2026-06-18 22:56:10', '2026-06-18 22:56:10'),
('019f72e4-4742-712a-8c7b-cce10cfb12ee', '019f72e4-43a7-71b6-bdea-e3dec18e4257', 'ORD-20260710-WILTY3', 288000.00, 'paid', '2026-07-10 09:20:10', '2026-07-10 09:20:10'),
('019f72e4-474c-7316-bb68-c6e2311515ef', '019f72e4-3fc1-7078-934f-32be5ca40e1a', 'ORD-20260710-YRZUWG', 480000.00, 'paid', '2026-07-09 22:59:10', '2026-07-09 22:59:10'),
('019f72e4-4756-7258-b758-93c289902cfc', '019f72e4-42dc-7207-872c-0c72d2e8c777', 'ORD-20260716-MXNE0U', 120000.00, 'pending', '2026-07-16 07:08:10', '2026-07-16 07:08:10'),
('019f72e4-475f-703c-9da0-32b333864174', '019f72e4-3efb-70f5-8dc4-f584916ea928', 'ORD-20260704-KMKKLH', 368000.00, 'paid', '2026-07-04 13:01:10', '2026-07-04 13:01:10'),
('019f72e4-476f-70e1-ba6c-32f55363eb2a', '019f72e4-43a7-71b6-bdea-e3dec18e4257', 'ORD-20260717-OIFDAI', 3100000.00, 'paid', '2026-07-16 22:14:10', '2026-07-16 22:14:10'),
('019f72e4-4780-7221-93a1-741a06040da7', '019f72e4-4088-727c-a54a-88e05338ba4f', 'ORD-20260715-IKMM4N', 228000.00, 'cancelled', '2026-07-15 00:54:10', '2026-07-15 00:54:10'),
('019f72e4-4789-7299-9aad-e0cb646f9a02', '019f72e4-3e34-70b1-b4e9-4b30694f24ff', 'ORD-20260620-HZ2DBT', 300000.00, 'pending', '2026-06-19 19:50:10', '2026-06-19 19:50:10'),
('019f72e4-479a-73d3-805e-9c8637eb4601', '019f72e4-3efb-70f5-8dc4-f584916ea928', 'ORD-20260706-OYMCZQ', 359000.00, 'paid', '2026-07-05 17:29:11', '2026-07-05 17:29:11'),
('019f72e4-47ab-7292-979d-ac9e55881c02', '019f72e4-4088-727c-a54a-88e05338ba4f', 'ORD-20260624-I7G8Q9', 288000.00, 'paid', '2026-06-23 19:39:11', '2026-06-23 19:39:11'),
('019f72e4-47b5-7382-a3bb-bd3ada08153f', '019f72e4-43a7-71b6-bdea-e3dec18e4257', 'ORD-20260710-EGJZCK', 90000.00, 'pending', '2026-07-10 07:45:11', '2026-07-10 07:45:11'),
('019f72e4-47c0-7134-a46f-a69f15544515', '019f72e4-3e34-70b1-b4e9-4b30694f24ff', 'ORD-20260618-2T8BAR', 80000.00, 'pending', '2026-06-17 21:26:11', '2026-06-17 21:26:11'),
('019f72e4-47c9-7100-b4b2-182f082dfbc5', '019f72e4-3c9a-7114-bbd9-de3a4f50ccd8', 'ORD-20260623-JIJFDL', 1332000.00, 'paid', '2026-06-23 13:26:11', '2026-06-23 13:26:11');

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

--
-- Đang đổ dữ liệu cho bảng `order_items`
--

INSERT INTO `order_items` (`id`, `order_id`, `product_id`, `product_name`, `price`, `quantity`, `delivered_keys`, `created_at`, `updated_at`) VALUES
('019f72e4-452d-73ee-b740-3f37e121ddca', '019f72e4-4522-7031-a694-c67d647e49ed', '019f72e4-43f6-71ea-ae65-8b350311a1eb', 'Via Colombia Cổ Kháng 902 Live Ads', 216000.00, 2, '[\"10009747684541|pass_secret_via_1|2FA_SECRET_KEY_XYZ_1|recovery_mail_1@gmail.com|pass_mail\",\"10009747684542|pass_secret_via_2|2FA_SECRET_KEY_XYZ_2|recovery_mail_2@gmail.com|pass_mail\"]', '2026-07-08 08:49:10', '2026-07-08 08:49:10'),
('019f72e4-4535-72ee-9cf4-9cea7b8b74c3', '019f72e4-4522-7031-a694-c67d647e49ed', '019f72e4-4403-72fe-8377-1169837916dc', 'Via Philippines Cổ XMDT Limit 50$', 200000.00, 1, '[\"10009642101041|pass_secret_via_1|2FA_SECRET_KEY_XYZ_1|recovery_mail_1@gmail.com|pass_mail\"]', '2026-07-08 08:49:10', '2026-07-08 08:49:10'),
('019f72e4-4541-70f9-bf9b-1403f31b57a5', '019f72e4-4539-7112-bf46-2cd8bb98932d', '019f72e4-4444-71e3-a96f-7d9280b93b51', 'Via Germany Cổ (2FA)', 200000.00, 2, '[\"10008068252211|pass_secret_via_1|2FA_SECRET_KEY_XYZ_1|recovery_mail_1@gmail.com|pass_mail\",\"10008068252212|pass_secret_via_2|2FA_SECRET_KEY_XYZ_2|recovery_mail_2@gmail.com|pass_mail\"]', '2026-07-02 10:48:10', '2026-07-02 10:48:10'),
('019f72e4-4548-72f7-b975-d1ffaf02f504', '019f72e4-4539-7112-bf46-2cd8bb98932d', '019f72e4-4475-7260-8404-7952387c6f06', 'Clone Instagram US', 3000.00, 1, '[\"ig_user_1|pass_ig_1|2FA_SECRET_XYZ_1|mail_ig_1@example.com\"]', '2026-07-02 10:48:10', '2026-07-02 10:48:10'),
('019f72e4-4553-7254-91c9-6eb8dbedbbba', '019f72e4-454c-7167-bb63-b24d789dac1c', '019f72e4-4400-705c-aee9-bb3be612fd32', 'Via Ngoại Cổ Kháng 902 Die Ads', 119000.00, 2, '[\"10009631915461|pass_secret_via_1|2FA_SECRET_KEY_XYZ_1|recovery_mail_1@gmail.com|pass_mail\",\"10009631915462|pass_secret_via_2|2FA_SECRET_KEY_XYZ_2|recovery_mail_2@gmail.com|pass_mail\"]', '2026-06-23 13:06:10', '2026-06-23 13:06:10'),
('019f72e4-455a-70ec-a664-703dde4caf2e', '019f72e4-454c-7167-bb63-b24d789dac1c', '019f72e4-443d-72f7-a3bc-d33495d545fe', 'Via United Kingdom Cổ (2FA)', 240000.00, 2, '[\"10002931492141|pass_secret_via_1|2FA_SECRET_KEY_XYZ_1|recovery_mail_1@gmail.com|pass_mail\",\"10002931492142|pass_secret_via_2|2FA_SECRET_KEY_XYZ_2|recovery_mail_2@gmail.com|pass_mail\"]', '2026-06-23 13:06:10', '2026-06-23 13:06:10'),
('019f72e4-4565-735f-b5e1-c43bb2141ec3', '019f72e4-455e-71c0-8873-635863cd8539', '019f72e4-4467-705a-92e4-430aef43d2b2', 'Via Ngoại Cổ DIE ADS 3-5K Friend', 156000.00, 2, '[\"10008896104291|pass_secret_via_1|2FA_SECRET_KEY_XYZ_1|recovery_mail_1@gmail.com|pass_mail\",\"10008896104292|pass_secret_via_2|2FA_SECRET_KEY_XYZ_2|recovery_mail_2@gmail.com|pass_mail\"]', '2026-07-15 23:24:10', '2026-07-15 23:24:10'),
('019f72e4-4570-7082-b81d-4fc5d6829242', '019f72e4-4568-716d-98ed-d665504889ed', '019f72e4-43ee-711c-a57f-240e239ba9b1', 'VIA US LIVE ADS KÈM BM1 (2FA )', 450000.00, 1, '[\"10008631117711|pass_secret_via_1|2FA_SECRET_KEY_XYZ_1|recovery_mail_1@gmail.com|pass_mail\"]', '2026-06-26 18:55:10', '2026-06-26 18:55:10'),
('019f72e4-4577-7042-a520-a68da23b262a', '019f72e4-4568-716d-98ed-d665504889ed', '019f72e4-4425-70b4-8ae6-83f4b95f6a65', 'Via Ecuador New (No2FA)', 72000.00, 2, '[\"10002403965871|pass_secret_via_1|2FA_SECRET_KEY_XYZ_1|recovery_mail_1@gmail.com|pass_mail\",\"10002403965872|pass_secret_via_2|2FA_SECRET_KEY_XYZ_2|recovery_mail_2@gmail.com|pass_mail\"]', '2026-06-26 18:55:10', '2026-06-26 18:55:10'),
('019f72e4-4582-7267-945d-614627c097ca', '019f72e4-457a-7271-bf5c-dd30718c71fc', '019f72e4-4409-73a5-bc13-8bfb6d440a21', 'VIA CHUYÊN SET CAMP (tỉ lệ HOLD , Need , Die cực thấp)', 300000.00, 2, '[\"10007522013461|pass_secret_via_1|2FA_SECRET_KEY_XYZ_1|recovery_mail_1@gmail.com|pass_mail\",\"10007522013462|pass_secret_via_2|2FA_SECRET_KEY_XYZ_2|recovery_mail_2@gmail.com|pass_mail\"]', '2026-07-01 06:48:10', '2026-07-01 06:48:10'),
('019f72e4-458c-72db-b45d-8a2f3ab405a2', '019f72e4-4585-707a-9080-b9f095b765ca', '019f72e4-444c-705b-9598-9eed54e41cdc', 'Via Egypt Cổ (2FA)', 96000.00, 2, '[\"10004644639041|pass_secret_via_1|2FA_SECRET_KEY_XYZ_1|recovery_mail_1@gmail.com|pass_mail\",\"10004644639042|pass_secret_via_2|2FA_SECRET_KEY_XYZ_2|recovery_mail_2@gmail.com|pass_mail\"]', '2026-07-07 17:26:10', '2026-07-07 17:26:10'),
('019f72e4-4597-7348-ba97-c9cdddea2e66', '019f72e4-4590-72ee-aada-d01e4d5ec712', '019f72e4-4410-7044-abaf-be54f5038130', 'Full Via Limit 50$ +7 USD đã paid mồi (No2FA)', 250000.00, 1, '[\"10001069804041|pass_secret_via_1|2FA_SECRET_KEY_XYZ_1|recovery_mail_1@gmail.com|pass_mail\"]', '2026-07-04 14:27:10', '2026-07-04 14:27:10'),
('019f72e4-459e-70cc-8e60-d760244556b4', '019f72e4-459a-731f-8b65-373386698a98', '019f72e4-4406-7165-8320-aefd74d5e260', 'Via Chat Support', 144000.00, 2, NULL, '2026-07-12 12:18:10', '2026-07-12 12:18:10'),
('019f72e4-45a1-72fc-a258-e111c5c2e82c', '019f72e4-459a-731f-8b65-373386698a98', '019f72e4-4449-7341-ac7c-283f59c351ce', 'Via Philippines Cổ (2FA)', 120000.00, 2, NULL, '2026-07-12 12:18:10', '2026-07-12 12:18:10'),
('019f72e4-45ab-72fd-bdaa-ac70833435a0', '019f72e4-45a4-72eb-8e1a-8cb8e220055b', '019f72e4-445d-7206-b498-00aefdf1d060', 'Clone Việt Nam Verify Hotmail', 7000.00, 2, '[\"mail_seed_account_1@hotmail.com|pass_mail_1|recovery_seed_mail_1@outlook.com\",\"mail_seed_account_2@hotmail.com|pass_mail_2|recovery_seed_mail_2@outlook.com\"]', '2026-06-28 22:20:10', '2026-06-28 22:20:10'),
('019f72e4-45b6-71d8-b676-8111e46033b4', '019f72e4-45af-70ed-80b6-df8d78f8f974', '019f72e4-43fc-73e2-a2a7-2d18a888b782', 'Via Indonesia Cổ Kháng 902 Live Ads', 216000.00, 2, '[\"10008614073781|pass_secret_via_1|2FA_SECRET_KEY_XYZ_1|recovery_mail_1@gmail.com|pass_mail\",\"10008614073782|pass_secret_via_2|2FA_SECRET_KEY_XYZ_2|recovery_mail_2@gmail.com|pass_mail\"]', '2026-07-08 06:01:10', '2026-07-08 06:01:10'),
('019f72e4-45bd-72c9-a0b1-800709082ab5', '019f72e4-45af-70ed-80b6-df8d78f8f974', '019f72e4-4437-725d-9666-11328c3eed2c', 'Via France (2FA)', 260000.00, 1, '[\"10002336087961|pass_secret_via_1|2FA_SECRET_KEY_XYZ_1|recovery_mail_1@gmail.com|pass_mail\"]', '2026-07-08 06:01:10', '2026-07-08 06:01:10'),
('019f72e4-45c9-708c-96d2-16d2bde4c000', '019f72e4-45c1-73c5-94f9-452cd3014b26', '019f72e4-446c-71b3-a5e8-13ccead641d3', 'Outlook Trusted [GRAPH API]', 400.00, 2, '[\"mail_seed_account_1@hotmail.com|pass_mail_1|recovery_seed_mail_1@outlook.com\",\"mail_seed_account_2@hotmail.com|pass_mail_2|recovery_seed_mail_2@outlook.com\"]', '2026-06-18 18:54:10', '2026-06-18 18:54:10'),
('019f72e4-45d4-7177-94f7-ad7fe782069e', '019f72e4-45cc-7346-94c5-3546cfe8485c', '019f72e4-445a-7073-9d49-85032728c58d', 'Clone Canada', 10000.00, 2, '[\"10009417227741|pass_secret_via_1|2FA_SECRET_KEY_XYZ_1|recovery_mail_1@gmail.com|pass_mail\",\"10009417227742|pass_secret_via_2|2FA_SECRET_KEY_XYZ_2|recovery_mail_2@gmail.com|pass_mail\"]', '2026-07-04 05:35:10', '2026-07-04 05:35:10'),
('019f72e4-45df-71ee-a862-e27698745797', '019f72e4-45d7-71fe-80fa-70dbeba45469', '019f72e4-4418-70a4-b2a9-56a47ebd54c1', 'Full Via Limit 200$', 240000.00, 2, '[\"10004166246881|pass_secret_via_1|2FA_SECRET_KEY_XYZ_1|recovery_mail_1@gmail.com|pass_mail\",\"10004166246882|pass_secret_via_2|2FA_SECRET_KEY_XYZ_2|recovery_mail_2@gmail.com|pass_mail\"]', '2026-06-18 04:48:10', '2026-06-18 04:48:10'),
('019f72e4-45e9-7337-a366-ee6a39390837', '019f72e4-45e2-73bc-9b53-fbba4c933772', '019f72e4-43d7-70ee-8184-938face79ad2', 'BM3 Ngâm Full 3 Tài Khoản', 80000.00, 1, '[\"10006379371521|pass_secret_via_1|2FA_SECRET_KEY_XYZ_1|recovery_mail_1@gmail.com|pass_mail\"]', '2026-06-30 14:35:10', '2026-06-30 14:35:10'),
('019f72e4-45ef-73b7-abaf-c1d5c5e1b73e', '019f72e4-45ec-7322-ab54-602ac9d9722d', '019f72e4-4409-73a5-bc13-8bfb6d440a21', 'VIA CHUYÊN SET CAMP (tỉ lệ HOLD , Need , Die cực thấp)', 300000.00, 2, NULL, '2026-06-21 03:32:10', '2026-06-21 03:32:10'),
('019f72e4-45f3-70f6-92e8-44b00b334117', '019f72e4-45ec-7322-ab54-602ac9d9722d', '019f72e4-441b-7207-a5ba-1e321f64e1d1', 'Via Ngoại Limit 250$ Cổ Ngâm', 350000.00, 2, NULL, '2026-06-21 03:32:10', '2026-06-21 03:32:10'),
('019f72e4-45fc-715c-bbbf-45455ab0b0bb', '019f72e4-45f6-730c-91ea-776e533502ec', '019f72e4-43f9-7193-8e0c-d5d95a5aff9d', 'Via Colombia Cổ Kháng 902 Die Ads', 144000.00, 2, '[\"10007292760061|pass_secret_via_1|2FA_SECRET_KEY_XYZ_1|recovery_mail_1@gmail.com|pass_mail\",\"10007292760062|pass_secret_via_2|2FA_SECRET_KEY_XYZ_2|recovery_mail_2@gmail.com|pass_mail\"]', '2026-06-26 20:27:10', '2026-06-26 20:27:10'),
('019f72e4-4604-7046-8b2b-23254ecc0e43', '019f72e4-45f6-730c-91ea-776e533502ec', '019f72e4-442d-707e-a0fd-48b1bfa2969d', 'Via Philippines Cổ (No2FA)', 72000.00, 1, '[\"10004462697781|pass_secret_via_1|2FA_SECRET_KEY_XYZ_1|recovery_mail_1@gmail.com|pass_mail\"]', '2026-06-26 20:27:10', '2026-06-26 20:27:10'),
('019f72e4-460d-7179-92d2-8dad74745634', '019f72e4-4607-7116-92e6-3a7b98edb78d', '019f72e4-4467-705a-92e4-430aef43d2b2', 'Via Ngoại Cổ DIE ADS 3-5K Friend', 156000.00, 2, '[\"10008896104293|pass_secret_via_3|2FA_SECRET_KEY_XYZ_3|recovery_mail_3@gmail.com|pass_mail\",\"10008896104294|pass_secret_via_4|2FA_SECRET_KEY_XYZ_4|recovery_mail_4@gmail.com|pass_mail\"]', '2026-06-20 14:47:10', '2026-06-20 14:47:10'),
('019f72e4-4618-72a0-814d-74b1f364510f', '019f72e4-4611-7052-99ce-ab1f8f124393', '019f72e4-43db-7371-83eb-360819316572', 'BM5 SCAN Trống 5', 2900000.00, 2, '[\"10007046147151|pass_secret_via_1|2FA_SECRET_KEY_XYZ_1|recovery_mail_1@gmail.com|pass_mail\",\"10007046147152|pass_secret_via_2|2FA_SECRET_KEY_XYZ_2|recovery_mail_2@gmail.com|pass_mail\"]', '2026-07-03 23:16:10', '2026-07-03 23:16:10'),
('019f72e4-461e-71ab-86bc-612d43b79d6a', '019f72e4-4611-7052-99ce-ab1f8f124393', '019f72e4-444c-705b-9598-9eed54e41cdc', 'Via Egypt Cổ (2FA)', 96000.00, 1, '[\"10004644639043|pass_secret_via_3|2FA_SECRET_KEY_XYZ_3|recovery_mail_3@gmail.com|pass_mail\"]', '2026-07-03 23:16:10', '2026-07-03 23:16:10'),
('019f72e4-4628-71ac-a486-00c59cb17874', '019f72e4-4621-73ea-9992-edb128cacb2f', '019f72e4-4437-725d-9666-11328c3eed2c', 'Via France (2FA)', 260000.00, 1, '[\"10002336087962|pass_secret_via_2|2FA_SECRET_KEY_XYZ_2|recovery_mail_2@gmail.com|pass_mail\"]', '2026-07-02 05:23:10', '2026-07-02 05:23:10'),
('019f72e4-462e-72e6-b35a-4d197e53c72a', '019f72e4-4621-73ea-9992-edb128cacb2f', '019f72e4-4469-7093-92cb-2abc5a198d49', 'Via Ngoại New Die Ads', 24000.00, 1, '[\"10006153788471|pass_secret_via_1|2FA_SECRET_KEY_XYZ_1|recovery_mail_1@gmail.com|pass_mail\"]', '2026-07-02 05:23:10', '2026-07-02 05:23:10'),
('019f72e4-4638-73a0-ab3f-485f27aae222', '019f72e4-4632-70e7-bb3a-f1113b96fb93', '019f72e4-4403-72fe-8377-1169837916dc', 'Via Philippines Cổ XMDT Limit 50$', 200000.00, 2, '[\"10009642101042|pass_secret_via_2|2FA_SECRET_KEY_XYZ_2|recovery_mail_2@gmail.com|pass_mail\",\"10009642101043|pass_secret_via_3|2FA_SECRET_KEY_XYZ_3|recovery_mail_3@gmail.com|pass_mail\"]', '2026-07-06 02:15:10', '2026-07-06 02:15:10'),
('019f72e4-463e-7056-9b69-951bfe35ae63', '019f72e4-4632-70e7-bb3a-f1113b96fb93', '019f72e4-442d-707e-a0fd-48b1bfa2969d', 'Via Philippines Cổ (No2FA)', 72000.00, 1, '[\"10004462697782|pass_secret_via_2|2FA_SECRET_KEY_XYZ_2|recovery_mail_2@gmail.com|pass_mail\"]', '2026-07-06 02:15:10', '2026-07-06 02:15:10'),
('019f72e4-4649-72ab-9a59-926d4dbc7c2a', '019f72e4-4641-72c6-9739-f52a3cfb13f6', '019f72e4-441b-7207-a5ba-1e321f64e1d1', 'Via Ngoại Limit 250$ Cổ Ngâm', 350000.00, 1, '[\"10003331887651|pass_secret_via_1|2FA_SECRET_KEY_XYZ_1|recovery_mail_1@gmail.com|pass_mail\"]', '2026-06-28 22:55:10', '2026-06-28 22:55:10'),
('019f72e4-464f-70e3-a502-681ae767f0b8', '019f72e4-4641-72c6-9739-f52a3cfb13f6', '019f72e4-4422-703f-a5b7-8881ce487ed3', 'Via Colombia Cổ (No2FA)', 72000.00, 2, '[\"10001696219841|pass_secret_via_1|2FA_SECRET_KEY_XYZ_1|recovery_mail_1@gmail.com|pass_mail\",\"10001696219842|pass_secret_via_2|2FA_SECRET_KEY_XYZ_2|recovery_mail_2@gmail.com|pass_mail\"]', '2026-06-28 22:55:10', '2026-06-28 22:55:10'),
('019f72e4-4659-73c8-9fd7-92d290e5d3b6', '019f72e4-4652-72f3-93ee-86d382d70092', '019f72e4-43f6-71ea-ae65-8b350311a1eb', 'Via Colombia Cổ Kháng 902 Live Ads', 216000.00, 1, '[\"10009747684543|pass_secret_via_3|2FA_SECRET_KEY_XYZ_3|recovery_mail_3@gmail.com|pass_mail\"]', '2026-06-23 07:16:10', '2026-06-23 07:16:10'),
('019f72e4-4660-7217-af6c-1a8e812cd8f9', '019f72e4-4652-72f3-93ee-86d382d70092', '019f72e4-4406-7165-8320-aefd74d5e260', 'Via Chat Support', 144000.00, 1, '[\"10002239833971|pass_secret_via_1|2FA_SECRET_KEY_XYZ_1|recovery_mail_1@gmail.com|pass_mail\"]', '2026-06-23 07:16:10', '2026-06-23 07:16:10'),
('019f72e4-466a-737d-b6f5-400e112cf3db', '019f72e4-4663-7288-868c-8b26318f9659', '019f72e4-43ea-7095-81a1-39cc912fcdc3', 'BM3 NGÂM 1 THÁNG FULL 3 TÀI KHOẢN', 80000.00, 2, '[\"10001925420141|pass_secret_via_1|2FA_SECRET_KEY_XYZ_1|recovery_mail_1@gmail.com|pass_mail\",\"10001925420142|pass_secret_via_2|2FA_SECRET_KEY_XYZ_2|recovery_mail_2@gmail.com|pass_mail\"]', '2026-06-27 14:41:10', '2026-06-27 14:41:10'),
('019f72e4-4674-71da-9305-7c34093466c7', '019f72e4-466d-7300-a239-eabb222c3dd2', '019f72e4-4413-7071-bdb9-5b1a25c830d8', 'Via Ngoại Limit 50$ Có TKQC Cổ (No2FA)', 156000.00, 2, '[\"10002923024071|pass_secret_via_1|2FA_SECRET_KEY_XYZ_1|recovery_mail_1@gmail.com|pass_mail\",\"10002923024072|pass_secret_via_2|2FA_SECRET_KEY_XYZ_2|recovery_mail_2@gmail.com|pass_mail\"]', '2026-06-17 20:12:10', '2026-06-17 20:12:10'),
('019f72e4-467b-739c-ab77-cf61ca75e50d', '019f72e4-466d-7300-a239-eabb222c3dd2', '019f72e4-443a-72b1-a4fe-c7a7261a075a', 'Via Switzerland Cổ (2FA)', 191000.00, 2, '[\"10001411352121|pass_secret_via_1|2FA_SECRET_KEY_XYZ_1|recovery_mail_1@gmail.com|pass_mail\",\"10001411352122|pass_secret_via_2|2FA_SECRET_KEY_XYZ_2|recovery_mail_2@gmail.com|pass_mail\"]', '2026-06-17 20:12:10', '2026-06-17 20:12:10'),
('019f72e4-4684-73ef-a744-f736fcbe2b4f', '019f72e4-467e-7288-8b60-90d7be745407', '019f72e4-440c-70a0-957f-3f8f8c3e3c2d', 'Full Via Limit 50$ -7 USD đã paid mồi (No2FA)', 216000.00, 2, '[\"10001165888621|pass_secret_via_1|2FA_SECRET_KEY_XYZ_1|recovery_mail_1@gmail.com|pass_mail\",\"10001165888622|pass_secret_via_2|2FA_SECRET_KEY_XYZ_2|recovery_mail_2@gmail.com|pass_mail\"]', '2026-07-02 03:41:10', '2026-07-02 03:41:10'),
('019f72e4-468e-7155-a0da-f3c361877df9', '019f72e4-4688-7211-b530-9f88e9d565c3', '019f72e4-43f6-71ea-ae65-8b350311a1eb', 'Via Colombia Cổ Kháng 902 Live Ads', 216000.00, 2, '[\"10009747684544|pass_secret_via_4|2FA_SECRET_KEY_XYZ_4|recovery_mail_4@gmail.com|pass_mail\",\"10009747684545|pass_secret_via_5|2FA_SECRET_KEY_XYZ_5|recovery_mail_5@gmail.com|pass_mail\"]', '2026-07-17 05:12:10', '2026-07-17 05:12:10'),
('019f72e4-4694-7150-a9eb-9999fcb54aa2', '019f72e4-4688-7211-b530-9f88e9d565c3', '019f72e4-4431-71b9-a604-974c3e11ecfe', 'Via Indonesia Cổ (No2FA)', 72000.00, 1, '[\"10001322130191|pass_secret_via_1|2FA_SECRET_KEY_XYZ_1|recovery_mail_1@gmail.com|pass_mail\"]', '2026-07-17 05:12:10', '2026-07-17 05:12:10'),
('019f72e4-469e-704c-baff-055279d26c12', '019f72e4-4698-702a-b931-1388a41dbfb4', '019f72e4-43e7-70ab-8552-23b2eff1a8a1', 'BM 3 NGÂM FULL 4 TÀI KHOẢN', 90000.00, 1, '[\"10002226095511|pass_secret_via_1|2FA_SECRET_KEY_XYZ_1|recovery_mail_1@gmail.com|pass_mail\"]', '2026-06-30 01:13:10', '2026-06-30 01:13:10'),
('019f72e4-46a5-727b-8947-27ea7421bf06', '019f72e4-4698-702a-b931-1388a41dbfb4', '019f72e4-4444-71e3-a96f-7d9280b93b51', 'Via Germany Cổ (2FA)', 200000.00, 2, '[\"10008068252213|pass_secret_via_3|2FA_SECRET_KEY_XYZ_3|recovery_mail_3@gmail.com|pass_mail\",\"10008068252214|pass_secret_via_4|2FA_SECRET_KEY_XYZ_4|recovery_mail_4@gmail.com|pass_mail\"]', '2026-06-30 01:13:10', '2026-06-30 01:13:10'),
('019f72e4-46af-724b-bfd6-8a6b230e1649', '019f72e4-46a9-7276-acd1-8a1cb33c2bac', '019f72e4-43d7-70ee-8184-938face79ad2', 'BM3 Ngâm Full 3 Tài Khoản', 80000.00, 1, '[\"10006379371522|pass_secret_via_2|2FA_SECRET_KEY_XYZ_2|recovery_mail_2@gmail.com|pass_mail\"]', '2026-07-02 21:28:10', '2026-07-02 21:28:10'),
('019f72e4-46b6-7184-8db0-2a613c057757', '019f72e4-46a9-7276-acd1-8a1cb33c2bac', '019f72e4-43ee-711c-a57f-240e239ba9b1', 'VIA US LIVE ADS KÈM BM1 (2FA )', 450000.00, 2, '[\"10008631117712|pass_secret_via_2|2FA_SECRET_KEY_XYZ_2|recovery_mail_2@gmail.com|pass_mail\",\"10008631117713|pass_secret_via_3|2FA_SECRET_KEY_XYZ_3|recovery_mail_3@gmail.com|pass_mail\"]', '2026-07-02 21:28:10', '2026-07-02 21:28:10'),
('019f72e4-46c0-73c6-ac70-83acda4d78c5', '019f72e4-46ba-721f-a973-c425ca0508b9', '019f72e4-4403-72fe-8377-1169837916dc', 'Via Philippines Cổ XMDT Limit 50$', 200000.00, 1, '[\"10009642101044|pass_secret_via_4|2FA_SECRET_KEY_XYZ_4|recovery_mail_4@gmail.com|pass_mail\"]', '2026-06-18 18:14:10', '2026-06-18 18:14:10'),
('019f72e4-46c7-72c5-928a-293bc1576cd5', '019f72e4-46ba-721f-a973-c425ca0508b9', '019f72e4-4458-706d-9f7a-5a7006eb8327', 'Clone United States', 7000.00, 1, '[\"10005026589211|pass_secret_via_1|2FA_SECRET_KEY_XYZ_1|recovery_mail_1@gmail.com|pass_mail\"]', '2026-06-18 18:14:10', '2026-06-18 18:14:10'),
('019f72e4-46d0-7117-8989-706f47217b22', '019f72e4-46ca-733f-8827-750bb4794302', '019f72e4-43fc-73e2-a2a7-2d18a888b782', 'Via Indonesia Cổ Kháng 902 Live Ads', 216000.00, 2, '[\"10008614073783|pass_secret_via_3|2FA_SECRET_KEY_XYZ_3|recovery_mail_3@gmail.com|pass_mail\",\"10008614073784|pass_secret_via_4|2FA_SECRET_KEY_XYZ_4|recovery_mail_4@gmail.com|pass_mail\"]', '2026-06-28 09:10:10', '2026-06-28 09:10:10'),
('019f72e4-46d6-727f-88e2-51b151e1952c', '019f72e4-46ca-733f-8827-750bb4794302', '019f72e4-4416-71fc-aadc-87d6d2896f85', 'COMBO 1 Account + 9 TKCN 1M1 (TÀI KHOẢN TẠO 2022)', 800000.00, 1, '[\"10009795373951|pass_secret_via_1|2FA_SECRET_KEY_XYZ_1|recovery_mail_1@gmail.com|pass_mail\"]', '2026-06-28 09:10:10', '2026-06-28 09:10:10'),
('019f72e4-46e3-7298-b8a5-e2b12d7a21d6', '019f72e4-46db-71e2-8351-ba04a385f943', '019f72e4-43e7-70ab-8552-23b2eff1a8a1', 'BM 3 NGÂM FULL 4 TÀI KHOẢN', 90000.00, 2, '[\"10002226095512|pass_secret_via_2|2FA_SECRET_KEY_XYZ_2|recovery_mail_2@gmail.com|pass_mail\",\"10002226095513|pass_secret_via_3|2FA_SECRET_KEY_XYZ_3|recovery_mail_3@gmail.com|pass_mail\"]', '2026-07-07 14:04:10', '2026-07-07 14:04:10'),
('019f72e4-46ed-703e-81d6-7cc48d293cbc', '019f72e4-46e6-716d-bb0a-2229988a7737', '019f72e4-4434-73ff-9fc5-1089de0a3dc3', 'Via Bangladesh New (No2FA)', 60000.00, 2, '[\"10008592853321|pass_secret_via_1|2FA_SECRET_KEY_XYZ_1|recovery_mail_1@gmail.com|pass_mail\",\"10008592853322|pass_secret_via_2|2FA_SECRET_KEY_XYZ_2|recovery_mail_2@gmail.com|pass_mail\"]', '2026-07-13 10:39:10', '2026-07-13 10:39:10'),
('019f72e4-46f4-7056-b5f6-27d81d927376', '019f72e4-46e6-716d-bb0a-2229988a7737', '019f72e4-4475-7260-8404-7952387c6f06', 'Clone Instagram US', 3000.00, 2, '[\"ig_user_2|pass_ig_2|2FA_SECRET_XYZ_2|mail_ig_2@example.com\",\"ig_user_3|pass_ig_3|2FA_SECRET_XYZ_3|mail_ig_3@example.com\"]', '2026-07-13 10:39:10', '2026-07-13 10:39:10'),
('019f72e4-46fe-7299-9c41-a0c6e8b24e7d', '019f72e4-46f7-72e5-bfea-bd013400b244', '019f72e4-43f3-7029-be1a-f5ae139b89dd', 'Via United States Cổ chuẩn LOCAL ( 2FA )', 550000.00, 2, '[\"10002329407951|pass_secret_via_1|2FA_SECRET_KEY_XYZ_1|recovery_mail_1@gmail.com|pass_mail\",\"10002329407952|pass_secret_via_2|2FA_SECRET_KEY_XYZ_2|recovery_mail_2@gmail.com|pass_mail\"]', '2026-06-21 08:34:10', '2026-06-21 08:34:10'),
('019f72e4-4704-7046-b480-5d6e3a2bc285', '019f72e4-46f7-72e5-bfea-bd013400b244', '019f72e4-4409-73a5-bc13-8bfb6d440a21', 'VIA CHUYÊN SET CAMP (tỉ lệ HOLD , Need , Die cực thấp)', 300000.00, 1, '[\"10007522013463|pass_secret_via_3|2FA_SECRET_KEY_XYZ_3|recovery_mail_3@gmail.com|pass_mail\"]', '2026-06-21 08:34:10', '2026-06-21 08:34:10'),
('019f72e4-470e-73f9-ad78-668851d608c1', '019f72e4-4707-7010-a40d-270ea1230cde', '019f72e4-43fc-73e2-a2a7-2d18a888b782', 'Via Indonesia Cổ Kháng 902 Live Ads', 216000.00, 1, '[\"10008614073785|pass_secret_via_5|2FA_SECRET_KEY_XYZ_5|recovery_mail_5@gmail.com|pass_mail\"]', '2026-06-19 07:24:10', '2026-06-19 07:24:10'),
('019f72e4-4717-706e-93e0-bbe97d3a281a', '019f72e4-4711-7247-9ca1-c2e738f53bb3', '019f72e4-43db-7371-83eb-360819316572', 'BM5 SCAN Trống 5', 2900000.00, 1, '[\"10007046147153|pass_secret_via_3|2FA_SECRET_KEY_XYZ_3|recovery_mail_3@gmail.com|pass_mail\"]', '2026-06-20 12:40:10', '2026-06-20 12:40:10'),
('019f72e4-471e-72d3-a086-4d10864a5480', '019f72e4-4711-7247-9ca1-c2e738f53bb3', '019f72e4-4472-7181-af3a-ff03af241587', 'Clone Instagram US đã qua reg BM', 2000.00, 1, '[\"ig_user_1|pass_ig_1|2FA_SECRET_XYZ_1|mail_ig_1@example.com\"]', '2026-06-20 12:40:10', '2026-06-20 12:40:10'),
('019f72e4-4727-73ae-a2cb-ae96d207585c', '019f72e4-4721-72ca-b9a9-c5ac8226895a', '019f72e4-43ee-711c-a57f-240e239ba9b1', 'VIA US LIVE ADS KÈM BM1 (2FA )', 450000.00, 2, '[\"10008631117714|pass_secret_via_4|2FA_SECRET_KEY_XYZ_4|recovery_mail_4@gmail.com|pass_mail\",\"10008631117715|pass_secret_via_5|2FA_SECRET_KEY_XYZ_5|recovery_mail_5@gmail.com|pass_mail\"]', '2026-07-02 00:29:10', '2026-07-02 00:29:10'),
('019f72e4-472e-71ec-8397-7a9a64703eb0', '019f72e4-4721-72ca-b9a9-c5ac8226895a', '019f72e4-4422-703f-a5b7-8881ce487ed3', 'Via Colombia Cổ (No2FA)', 72000.00, 1, '[\"10001696219843|pass_secret_via_3|2FA_SECRET_KEY_XYZ_3|recovery_mail_3@gmail.com|pass_mail\"]', '2026-07-02 00:29:10', '2026-07-02 00:29:10'),
('019f72e4-4737-73f8-8ee8-bea44c360139', '019f72e4-4731-725b-a54e-68e391552339', '019f72e4-443d-72f7-a3bc-d33495d545fe', 'Via United Kingdom Cổ (2FA)', 240000.00, 2, '[\"10002931492143|pass_secret_via_3|2FA_SECRET_KEY_XYZ_3|recovery_mail_3@gmail.com|pass_mail\",\"10002931492144|pass_secret_via_4|2FA_SECRET_KEY_XYZ_4|recovery_mail_4@gmail.com|pass_mail\"]', '2026-06-18 22:56:10', '2026-06-18 22:56:10'),
('019f72e4-473f-7203-a486-642945c3faa6', '019f72e4-4731-725b-a54e-68e391552339', '019f72e4-4449-7341-ac7c-283f59c351ce', 'Via Philippines Cổ (2FA)', 120000.00, 2, '[\"10005938566721|pass_secret_via_1|2FA_SECRET_KEY_XYZ_1|recovery_mail_1@gmail.com|pass_mail\",\"10005938566722|pass_secret_via_2|2FA_SECRET_KEY_XYZ_2|recovery_mail_2@gmail.com|pass_mail\"]', '2026-06-18 22:56:10', '2026-06-18 22:56:10'),
('019f72e4-4748-7338-adad-687339df9e77', '019f72e4-4742-712a-8c7b-cce10cfb12ee', '019f72e4-4428-727f-84e0-a4df8c19a68d', 'Via Brazil Cổ (No2FA)', 144000.00, 2, '[\"10009448924131|pass_secret_via_1|2FA_SECRET_KEY_XYZ_1|recovery_mail_1@gmail.com|pass_mail\",\"10009448924132|pass_secret_via_2|2FA_SECRET_KEY_XYZ_2|recovery_mail_2@gmail.com|pass_mail\"]', '2026-07-10 09:20:10', '2026-07-10 09:20:10'),
('019f72e4-4753-700e-9eac-afb832117a32', '019f72e4-474c-7316-bb68-c6e2311515ef', '019f72e4-443d-72f7-a3bc-d33495d545fe', 'Via United Kingdom Cổ (2FA)', 240000.00, 2, '[\"10002931492145|pass_secret_via_5|2FA_SECRET_KEY_XYZ_5|recovery_mail_5@gmail.com|pass_mail\",\"10002931492146|pass_secret_via_6|2FA_SECRET_KEY_XYZ_6|recovery_mail_6@gmail.com|pass_mail\"]', '2026-07-09 22:59:10', '2026-07-09 22:59:10'),
('019f72e4-475c-70be-a221-3a9ca17100ee', '019f72e4-4756-7258-b758-93c289902cfc', '019f72e4-4434-73ff-9fc5-1089de0a3dc3', 'Via Bangladesh New (No2FA)', 60000.00, 2, '[\"10008592853323|pass_secret_via_3|2FA_SECRET_KEY_XYZ_3|recovery_mail_3@gmail.com|pass_mail\",\"10008592853324|pass_secret_via_4|2FA_SECRET_KEY_XYZ_4|recovery_mail_4@gmail.com|pass_mail\"]', '2026-07-16 07:08:10', '2026-07-16 07:08:10'),
('019f72e4-4765-73ab-9100-44e555cc551a', '019f72e4-475f-703c-9da0-32b333864174', '019f72e4-43ea-7095-81a1-39cc912fcdc3', 'BM3 NGÂM 1 THÁNG FULL 3 TÀI KHOẢN', 80000.00, 1, '[\"10001925420143|pass_secret_via_3|2FA_SECRET_KEY_XYZ_3|recovery_mail_3@gmail.com|pass_mail\"]', '2026-07-04 13:01:10', '2026-07-04 13:01:10'),
('019f72e4-476c-731a-8597-a221a4f8c1ff', '019f72e4-475f-703c-9da0-32b333864174', '019f72e4-4406-7165-8320-aefd74d5e260', 'Via Chat Support', 144000.00, 2, '[\"10002239833972|pass_secret_via_2|2FA_SECRET_KEY_XYZ_2|recovery_mail_2@gmail.com|pass_mail\",\"10002239833973|pass_secret_via_3|2FA_SECRET_KEY_XYZ_3|recovery_mail_3@gmail.com|pass_mail\"]', '2026-07-04 13:01:10', '2026-07-04 13:01:10'),
('019f72e4-4776-7251-b93c-0ba86a8f3525', '019f72e4-476f-70e1-ba6c-32f55363eb2a', '019f72e4-43df-72aa-a0ac-fb8c9410ccd8', 'BM5 Trống 3 | Tạo 2018|Kèm Page 6500 Like', 2800000.00, 1, '[\"10003813808351|pass_secret_via_1|2FA_SECRET_KEY_XYZ_1|recovery_mail_1@gmail.com|pass_mail\"]', '2026-07-16 22:14:10', '2026-07-16 22:14:10'),
('019f72e4-477d-70db-94af-57ae69d7ab94', '019f72e4-476f-70e1-ba6c-32f55363eb2a', '019f72e4-4409-73a5-bc13-8bfb6d440a21', 'VIA CHUYÊN SET CAMP (tỉ lệ HOLD , Need , Die cực thấp)', 300000.00, 1, '[\"10007522013464|pass_secret_via_4|2FA_SECRET_KEY_XYZ_4|recovery_mail_4@gmail.com|pass_mail\"]', '2026-07-16 22:14:10', '2026-07-16 22:14:10'),
('019f72e4-4783-7190-9986-c5a9f90cc70e', '019f72e4-4780-7221-93a1-741a06040da7', '019f72e4-4422-703f-a5b7-8881ce487ed3', 'Via Colombia Cổ (No2FA)', 72000.00, 2, NULL, '2026-07-15 00:54:10', '2026-07-15 00:54:10'),
('019f72e4-4786-70fb-a1d3-b8a12ad9834d', '019f72e4-4780-7221-93a1-741a06040da7', '019f72e4-442a-735a-910b-3023b82098af', 'Via Peru New (No2FA)', 84000.00, 1, NULL, '2026-07-15 00:54:10', '2026-07-15 00:54:10'),
('019f72e4-478f-723b-b495-692e8ca94e4b', '019f72e4-4789-7299-9aad-e0cb646f9a02', '019f72e4-4406-7165-8320-aefd74d5e260', 'Via Chat Support', 144000.00, 1, '[\"10002239833974|pass_secret_via_4|2FA_SECRET_KEY_XYZ_4|recovery_mail_4@gmail.com|pass_mail\"]', '2026-06-19 19:50:10', '2026-06-19 19:50:10'),
('019f72e4-4795-71ba-ade6-db9c792ae832', '019f72e4-4789-7299-9aad-e0cb646f9a02', '019f72e4-4413-7071-bdb9-5b1a25c830d8', 'Via Ngoại Limit 50$ Có TKQC Cổ (No2FA)', 156000.00, 1, '[\"10002923024073|pass_secret_via_3|2FA_SECRET_KEY_XYZ_3|recovery_mail_3@gmail.com|pass_mail\"]', '2026-06-19 19:50:10', '2026-06-19 19:50:10'),
('019f72e4-47a2-7016-a6af-0027df640c11', '019f72e4-479a-73d3-805e-9c8637eb4601', '019f72e4-442a-735a-910b-3023b82098af', 'Via Peru New (No2FA)', 84000.00, 2, '[\"10002654067131|pass_secret_via_1|2FA_SECRET_KEY_XYZ_1|recovery_mail_1@gmail.com|pass_mail\",\"10002654067132|pass_secret_via_2|2FA_SECRET_KEY_XYZ_2|recovery_mail_2@gmail.com|pass_mail\"]', '2026-07-05 17:29:11', '2026-07-05 17:29:11'),
('019f72e4-47a8-705d-841c-5a59fa02b6c9', '019f72e4-479a-73d3-805e-9c8637eb4601', '019f72e4-4441-7007-92ee-a49e8f5e6edc', 'Via Poland Cổ (2FA)', 191000.00, 1, '[\"10002411378191|pass_secret_via_1|2FA_SECRET_KEY_XYZ_1|recovery_mail_1@gmail.com|pass_mail\"]', '2026-07-05 17:29:11', '2026-07-05 17:29:11'),
('019f72e4-47b2-7310-aade-9434b83f54cc', '019f72e4-47ab-7292-979d-ac9e55881c02', '019f72e4-4406-7165-8320-aefd74d5e260', 'Via Chat Support', 144000.00, 2, '[\"10002239833975|pass_secret_via_5|2FA_SECRET_KEY_XYZ_5|recovery_mail_5@gmail.com|pass_mail\",\"10002239833976|pass_secret_via_6|2FA_SECRET_KEY_XYZ_6|recovery_mail_6@gmail.com|pass_mail\"]', '2026-06-23 19:39:11', '2026-06-23 19:39:11'),
('019f72e4-47bc-728f-a566-50d00f350e74', '019f72e4-47b5-7382-a3bb-bd3ada08153f', '019f72e4-43e7-70ab-8552-23b2eff1a8a1', 'BM 3 NGÂM FULL 4 TÀI KHOẢN', 90000.00, 1, '[\"10002226095514|pass_secret_via_4|2FA_SECRET_KEY_XYZ_4|recovery_mail_4@gmail.com|pass_mail\"]', '2026-07-10 07:45:11', '2026-07-10 07:45:11'),
('019f72e4-47c7-7108-a45c-2075b9101e25', '019f72e4-47c0-7134-a46f-a69f15544515', '019f72e4-43ea-7095-81a1-39cc912fcdc3', 'BM3 NGÂM 1 THÁNG FULL 3 TÀI KHOẢN', 80000.00, 1, '[\"10001925420144|pass_secret_via_4|2FA_SECRET_KEY_XYZ_4|recovery_mail_4@gmail.com|pass_mail\"]', '2026-06-17 21:26:11', '2026-06-17 21:26:11'),
('019f72e4-47d0-7360-ad1e-dc9cfede20c6', '019f72e4-47c9-7100-b4b2-182f082dfbc5', '019f72e4-43ee-711c-a57f-240e239ba9b1', 'VIA US LIVE ADS KÈM BM1 (2FA )', 450000.00, 2, '[\"10008631117716|pass_secret_via_6|2FA_SECRET_KEY_XYZ_6|recovery_mail_6@gmail.com|pass_mail\",\"10008631117717|pass_secret_via_7|2FA_SECRET_KEY_XYZ_7|recovery_mail_7@gmail.com|pass_mail\"]', '2026-06-23 13:26:11', '2026-06-23 13:26:11'),
('019f72e4-47d7-730e-8a6a-b1712851869e', '019f72e4-47c9-7100-b4b2-182f082dfbc5', '019f72e4-440c-70a0-957f-3f8f8c3e3c2d', 'Full Via Limit 50$ -7 USD đã paid mồi (No2FA)', 216000.00, 2, '[\"10001165888623|pass_secret_via_3|2FA_SECRET_KEY_XYZ_3|recovery_mail_3@gmail.com|pass_mail\",\"10001165888624|pass_secret_via_4|2FA_SECRET_KEY_XYZ_4|recovery_mail_4@gmail.com|pass_mail\"]', '2026-06-23 13:26:11', '2026-06-23 13:26:11');

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
  `category_id` char(36) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `slug` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `description` text COLLATE utf8mb4_unicode_ci,
  `price` decimal(15,2) NOT NULL,
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

INSERT INTO `products` (`id`, `category_id`, `name`, `slug`, `description`, `price`, `status`, `attributes`, `created_at`, `updated_at`, `normalized_name`, `deleted_at`) VALUES
('019f72e4-43d7-70ee-8184-938face79ad2', '019f72e4-43ad-7050-be7f-bdacf3df5db3', 'BM3 Ngâm Full 3 Tài Khoản', 'bm3-ngam-full-3-tkqc', 'BM3 Ngâm Full 3 Tài Khoản chất lượng cao.', 80000.00, 'active', '[{\"label\": \"Loại\", \"value\": \"BM3 Ngâm\"}, {\"label\": \"Số TKQC\", \"value\": \"3 TKQC\"}]', '2026-07-17 18:43:10', '2026-07-17 18:43:10', 'bm3 ngam full 3 tai khoan', NULL),
('019f72e4-43db-7371-83eb-360819316572', '019f72e4-43ad-7050-be7f-bdacf3df5db3', 'BM5 SCAN Trống 5', 'bm5-scan-trong-5', 'BM5 SCAN Trống 5 tài khoản quảng cáo.', 2900000.00, 'active', '[{\"label\": \"Loại\", \"value\": \"BM5 SCAN\"}, {\"label\": \"Trạng thái\", \"value\": \"Trống 5\"}]', '2026-07-17 18:43:10', '2026-07-17 18:43:10', 'bm5 scan trong 5', NULL),
('019f72e4-43df-72aa-a0ac-fb8c9410ccd8', '019f72e4-43ad-7050-be7f-bdacf3df5db3', 'BM5 Trống 3 | Tạo 2018|Kèm Page 6500 Like', 'bm5-trong-3-tao-2018-kem-page-6500-like', 'Sẵn 2 Tài Khoản tổng Ngưỡng 150$|Trống 3 |Kèm Page 6500 Like|', 2800000.00, 'active', '[{\"label\": \"Năm tạo\", \"value\": \"2018\"}, {\"label\": \"Page kèm\", \"value\": \"6500 Like\"}, {\"label\": \"Ngưỡng\", \"value\": \"150$\"}]', '2026-07-17 18:43:10', '2026-07-17 18:43:10', 'bm5 trong 3 | tao 2018|kem page 6500 like', NULL),
('019f72e4-43e2-7052-8da5-ac795a358b47', '019f72e4-43ad-7050-be7f-bdacf3df5db3', 'BM3 XÁC MINH DOANH NGHIỆP NGÂM FULL 3 TÀI KHOẢN(LÊN LIMIT NHANH)', 'bm3-xmdn-ngam-full-3-tkqc', '📌 Đã tạo 3 TKQC 📌 Limit tăng theo chi tiêu cực nhanh 📌 Limit 50$ 📌 BM Verify Business 📌 Created: Jun/2026', 230000.00, 'active', '[{\"label\": \"Limit\", \"value\": \"50$\"}, {\"label\": \"Xác minh\", \"value\": \"Verify Business\"}, {\"label\": \"Created\", \"value\": \"Jun/2026\"}]', '2026-07-17 18:43:10', '2026-07-17 18:43:10', 'bm3 xac minh doanh nghiep ngam full 3 tai khoan(len limit nhanh)', NULL),
('019f72e4-43e5-70ec-8091-0ac361b45e64', '019f72e4-43ad-7050-be7f-bdacf3df5db3', 'BM1 CỔ GỐC SCAN TẠO KÈM PAGE SCAN', 'bm1-co-goc-scan-kem-page', 'BM 1 CỔ GỐC SCAN TẠO TRỐNG 1 + KÈM PAGE SCAN', 400000.00, 'active', '[{\"label\": \"Loại\", \"value\": \"BM1 Cổ gốc\"}, {\"label\": \"Page kèm\", \"value\": \"Page Scan\"}]', '2026-07-17 18:43:10', '2026-07-17 18:43:10', 'bm1 co goc scan tao kem page scan', NULL),
('019f72e4-43e7-70ab-8552-23b2eff1a8a1', '019f72e4-43ad-7050-be7f-bdacf3df5db3', 'BM 3 NGÂM FULL 4 TÀI KHOẢN', 'bm3-ngam-full-4-tkqc', 'BM full 4 Tài Khoản ngâm trâu bò.', 90000.00, 'active', '[{\"label\": \"Loại\", \"value\": \"BM3 ngâm\"}, {\"label\": \"Số TKQC\", \"value\": \"4 TKQC\"}]', '2026-07-17 18:43:10', '2026-07-17 18:43:10', 'bm 3 ngam full 4 tai khoan', NULL),
('019f72e4-43ea-7095-81a1-39cc912fcdc3', '019f72e4-43ad-7050-be7f-bdacf3df5db3', 'BM3 NGÂM 1 THÁNG FULL 3 TÀI KHOẢN', 'bm3-ngam-1-thang-full-3-tkqc', 'Soak BM full 3 account over 1 month.', 80000.00, 'active', '[{\"label\": \"Thời gian ngâm\", \"value\": \"> 1 tháng\"}, {\"label\": \"Số TKQC\", \"value\": \"3 TKQC\"}]', '2026-07-17 18:43:10', '2026-07-17 18:43:10', 'bm3 ngam 1 thang full 3 tai khoan', NULL),
('019f72e4-43ee-711c-a57f-240e239ba9b1', '019f72e4-43b1-73f1-b9dc-bc330639f652', 'VIA US LIVE ADS KÈM BM1 (2FA )', 'via-us-live-ads-kem-bm1-2fa', 'Friend:Random | LIVE ADS | Checkpoint Mail | Created: 2025-2026 | Chưa dính IP Việt|Kèm BM1|', 450000.00, 'active', '[{\"label\": \"Quốc gia\", \"value\": \"US (Mỹ)\"}, {\"label\": \"Kèm theo\", \"value\": \"BM1\"}, {\"label\": \"Bảo mật\", \"value\": \"2FA\"}]', '2026-07-17 18:43:10', '2026-07-17 18:43:10', 'via us live ads kem bm1 (2fa )', NULL),
('019f72e4-43f1-7197-9ee1-5476468c7438', '019f72e4-43b1-73f1-b9dc-bc330639f652', 'Via US LIVE ADS (2FA )', 'via-us-live-ads-2fa', 'Friend:Random | LIVE ADS | Checkpoint Mail | Created: 2025-2026 | Chưa dính IP Việt', 400000.00, 'active', '[{\"label\": \"Quốc gia\", \"value\": \"US (Mỹ)\"}, {\"label\": \"Bảo mật\", \"value\": \"2FA\"}]', '2026-07-17 18:43:10', '2026-07-17 18:43:10', 'via us live ads (2fa )', NULL),
('019f72e4-43f3-7029-be1a-f5ae139b89dd', '019f72e4-43b1-73f1-b9dc-bc330639f652', 'Via United States Cổ chuẩn LOCAL ( 2FA )', 'via-us-co-chuan-local-2fa', 'Friend: 30-5000 | LIVE ADS | Created: 2008-2021 | Chưa dính IP Việt', 550000.00, 'active', '[{\"label\": \"Độ tuổi\", \"value\": \"Cổ (2008-2021)\"}, {\"label\": \"Bảo mật\", \"value\": \"2FA Local US\"}]', '2026-07-17 18:43:10', '2026-07-17 18:43:10', 'via united states co chuan local ( 2fa )', NULL),
('019f72e4-43f6-71ea-ae65-8b350311a1eb', '019f72e4-43b4-7305-9ddf-6942ebc994ef', 'Via Colombia Cổ Kháng 902 Live Ads', 'via-colombia-co-khang-902-live-ads', 'Green tick: 3 Lines | Tích ẩn | Ad account: Live | Created: 2008-2022', 216000.00, 'active', '[{\"label\": \"Quốc gia\", \"value\": \"Colombia\"}, {\"label\": \"Kháng\", \"value\": \"902 Tick xanh\"}, {\"label\": \"TKQC\", \"value\": \"Live Ads\"}]', '2026-07-17 18:43:10', '2026-07-17 18:43:10', 'via colombia co khang 902 live ads', NULL),
('019f72e4-43f9-7193-8e0c-d5d95a5aff9d', '019f72e4-43b4-7305-9ddf-6942ebc994ef', 'Via Colombia Cổ Kháng 902 Die Ads', 'via-colombia-co-khang-902-die-ads', 'Green tick: 3 Lines | Tích ẩn | Ad account: Die | Created: 2008-2022', 144000.00, 'active', '[{\"label\": \"Quốc gia\", \"value\": \"Colombia\"}, {\"label\": \"Kháng\", \"value\": \"902 Tick xanh\"}, {\"label\": \"TKQC\", \"value\": \"Die Ads\"}]', '2026-07-17 18:43:10', '2026-07-17 18:43:10', 'via colombia co khang 902 die ads', NULL),
('019f72e4-43fc-73e2-a2a7-2d18a888b782', '019f72e4-43b4-7305-9ddf-6942ebc994ef', 'Via Indonesia Cổ Kháng 902 Live Ads', 'via-indonesia-co-khang-902-live-ads', 'Green tick: 3 Lines | Tích ẩn | Ad account: Live | Created: 2008-2022', 216000.00, 'active', '[{\"label\": \"Quốc gia\", \"value\": \"Indonesia\"}, {\"label\": \"Kháng\", \"value\": \"902 Tick xanh\"}]', '2026-07-17 18:43:10', '2026-07-17 18:43:10', 'via indonesia co khang 902 live ads', NULL),
('019f72e4-4400-705c-aee9-bb3be612fd32', '019f72e4-43b4-7305-9ddf-6942ebc994ef', 'Via Ngoại Cổ Kháng 902 Die Ads', 'via-ngoai-co-khang-902-die-ads', 'Green tick: 3 Lines | Tích ẩn | Ad account: Die | Created: 2008-2024', 119000.00, 'active', '[{\"label\": \"Quốc gia\", \"value\": \"Ngoại cổ\"}, {\"label\": \"Trạng thái\", \"value\": \"Kháng 902\"}]', '2026-07-17 18:43:10', '2026-07-17 18:43:10', 'via ngoai co khang 902 die ads', NULL),
('019f72e4-4403-72fe-8377-1169837916dc', '019f72e4-43b4-7305-9ddf-6942ebc994ef', 'Via Philippines Cổ XMDT Limit 50$', 'via-philippines-co-xmdt-50', 'Green tick: 2 Lines | Tích ẩn | Created: 2008-2023', 200000.00, 'active', '[{\"label\": \"Quốc gia\", \"value\": \"Philippines\"}, {\"label\": \"Xác minh\", \"value\": \"XMDT 2 Lines\"}]', '2026-07-17 18:43:10', '2026-07-17 18:43:10', 'via philippines co xmdt limit 50$', NULL),
('019f72e4-4406-7165-8320-aefd74d5e260', '019f72e4-43b4-7305-9ddf-6942ebc994ef', 'Via Chat Support', 'via-chat-support-fbook', 'Có thể Chat Support Facebook | Năm tạo: 2025 | Checkpoint Mail', 144000.00, 'active', '[{\"label\": \"Hỗ trợ\", \"value\": \"Chat Support FB\"}, {\"label\": \"Bảo mật\", \"value\": \"2FA\"}]', '2026-07-17 18:43:10', '2026-07-17 18:43:10', 'via chat support', NULL),
('019f72e4-4409-73a5-bc13-8bfb6d440a21', '019f72e4-43b7-71aa-b070-04eb5afae057', 'VIA CHUYÊN SET CAMP (tỉ lệ HOLD , Need , Die cực thấp)', 'via-chuyen-set-camp-hold-low', 'Friend: 30-1000 | LIVE ADS | Checkpoint Mail | Chuyên lên camp voi/sạch.', 300000.00, 'active', '[{\"label\": \"Đặc tính\", \"value\": \"Chuyên Set Camp\"}, {\"label\": \"Tỉ lệ tụt\", \"value\": \"Cực thấp\"}]', '2026-07-17 18:43:10', '2026-07-17 18:43:10', 'via chuyen set camp (ti le hold , need , die cuc thap)', NULL),
('019f72e4-440c-70a0-957f-3f8f8c3e3c2d', '019f72e4-43b7-71aa-b070-04eb5afae057', 'Full Via Limit 50$ -7 USD đã paid mồi (No2FA)', 'full-via-limit-50-paid-moi-no2fa', 'TKQC đã camp mồi, paid mồi | Đã kháng 273 | Tiền tệ: -7 USD | Quốc gia US lách thuế', 216000.00, 'active', '[{\"label\": \"Paid mồi\", \"value\": \"Đã paid mồi\"}, {\"label\": \"Bảo mật\", \"value\": \"No2FA\"}]', '2026-07-17 18:43:10', '2026-07-17 18:43:10', 'full via limit 50$ -7 usd da paid moi (no2fa)', NULL),
('019f72e4-4410-7044-abaf-be54f5038130', '019f72e4-43b7-71aa-b070-04eb5afae057', 'Full Via Limit 50$ +7 USD đã paid mồi (No2FA)', 'full-via-limit-50-plus-7-paid-no2fa', 'TKQC đã camp mồi, paid mồi | Đã kháng 273 | Tiền tệ: +7 USD | Quốc gia US lách thuế', 250000.00, 'active', '[{\"label\": \"Tiền tệ\", \"value\": \"+7 USD\"}, {\"label\": \"Bảo mật\", \"value\": \"No2FA\"}]', '2026-07-17 18:43:10', '2026-07-17 18:43:10', 'full via limit 50$ +7 usd da paid moi (no2fa)', NULL),
('019f72e4-4413-7071-bdb9-5b1a25c830d8', '019f72e4-43b7-71aa-b070-04eb5afae057', 'Via Ngoại Limit 50$ Có TKQC Cổ (No2FA)', 'via-ngoai-limit-50-tkqc-co-no2fa', 'Bao đổi tiền, múi giờ, quốc gia | TKQC Cổ tạo từ: 2010–2024 | Chưa dính IP Việt', 156000.00, 'active', '[{\"label\": \"TKQC\", \"value\": \"Cổ (2010-2024)\"}, {\"label\": \"Bảo mật\", \"value\": \"No2FA\"}]', '2026-07-17 18:43:10', '2026-07-17 18:43:10', 'via ngoai limit 50$ co tkqc co (no2fa)', NULL),
('019f72e4-4416-71fc-aadc-87d6d2896f85', '019f72e4-43b7-71aa-b070-04eb5afae057', 'COMBO 1 Account + 9 TKCN 1M1 (TÀI KHOẢN TẠO 2022)', 'combo-1-acc-9-tkcn-1m1-2022', 'Combo Account random country with 9 personal ad accounts 1m1 creat 2022', 800000.00, 'active', '[{\"label\": \"Loại\", \"value\": \"Combo Account\"}, {\"label\": \"Số TKQC\", \"value\": \"9 TKCN 1M1\"}]', '2026-07-17 18:43:10', '2026-07-17 18:43:10', 'combo 1 account + 9 tkcn 1m1 (tai khoan tao 2022)', NULL),
('019f72e4-4418-70a4-b2a9-56a47ebd54c1', '019f72e4-43bb-71d4-80b7-d2fa45976717', 'Full Via Limit 200$', 'full-via-limit-200-usd', 'Bao đổi tiền, múi giờ, quốc gia | Không bao tụt Limit | TKQC Cổ tạo từ 2010-2023', 240000.00, 'active', '[{\"label\": \"Limit\", \"value\": \"200$\"}, {\"label\": \"Bảo hành\", \"value\": \"Bao đổi tiền, múi giờ\"}]', '2026-07-17 18:43:10', '2026-07-17 18:43:10', 'full via limit 200$', NULL),
('019f72e4-441b-7207-a5ba-1e321f64e1d1', '019f72e4-43bb-71d4-80b7-d2fa45976717', 'Via Ngoại Limit 250$ Cổ Ngâm', 'via-ngoai-limit-250-co-ngam', 'Via Ngoại limit 250$ cổ ngâm trên 6 tháng.', 350000.00, 'active', '[{\"label\": \"Limit\", \"value\": \"250$\"}, {\"label\": \"Trạng thái\", \"value\": \"Cổ ngâm\"}]', '2026-07-17 18:43:10', '2026-07-17 18:43:10', 'via ngoai limit 250$ co ngam', NULL),
('019f72e4-441e-70aa-9c27-3a21ede04bd4', '019f72e4-43bf-7016-b10d-43eed06e3827', 'Via Ngoại Cổ (No2FA)', 'via-ngoai-co-no2fa', 'Friend: 30-1000 | LIVE ADS | Checkpoint Mail | Created: 2008-2023', 40000.00, 'active', '[{\"label\": \"Độ tuổi\", \"value\": \"Cổ (2008-2023)\"}, {\"label\": \"Bảo mật\", \"value\": \"No2FA\"}]', '2026-07-17 18:43:10', '2026-07-17 18:43:10', 'via ngoai co (no2fa)', NULL),
('019f72e4-4422-703f-a5b7-8881ce487ed3', '019f72e4-43bf-7016-b10d-43eed06e3827', 'Via Colombia Cổ (No2FA)', 'via-colombia-co-no2fa', 'Friend: 30-5000 | LIVE ADS | Checkpoint Mail | Created: 2008-2022 | Chưa dính IP Việt', 72000.00, 'active', '[{\"label\": \"Quốc gia\", \"value\": \"Colombia\"}, {\"label\": \"Bảo mật\", \"value\": \"No2FA\"}]', '2026-07-17 18:43:10', '2026-07-17 18:43:10', 'via colombia co (no2fa)', NULL),
('019f72e4-4425-70b4-8ae6-83f4b95f6a65', '019f72e4-43bf-7016-b10d-43eed06e3827', 'Via Ecuador New (No2FA)', 'via-ecuador-new-no2fa', 'Friend: 30-5000 | LIVE ADS | Checkpoint Mail | Created: 2024-2025 | Chưa dính IP Việt', 72000.00, 'active', '[{\"label\": \"Quốc gia\", \"value\": \"Ecuador\"}, {\"label\": \"Bảo mật\", \"value\": \"No2FA\"}]', '2026-07-17 18:43:10', '2026-07-17 18:43:10', 'via ecuador new (no2fa)', NULL),
('019f72e4-4428-727f-84e0-a4df8c19a68d', '019f72e4-43bf-7016-b10d-43eed06e3827', 'Via Brazil Cổ (No2FA)', 'via-brazil-co-no2fa', 'Friend: 30-5000 | LIVE ADS | Checkpoint Mail | Created: 2008-2023 | Chưa dính IP Việt', 144000.00, 'active', '[{\"label\": \"Quốc gia\", \"value\": \"Brazil\"}, {\"label\": \"Bảo mật\", \"value\": \"No2FA\"}]', '2026-07-17 18:43:10', '2026-07-17 18:43:10', 'via brazil co (no2fa)', NULL),
('019f72e4-442a-735a-910b-3023b82098af', '019f72e4-43bf-7016-b10d-43eed06e3827', 'Via Peru New (No2FA)', 'via-peru-new-no2fa', 'Friend: 30-5000 | LIVE ADS | Checkpoint Mail | Created: 2024-2025 | Chưa dính IP Việt', 84000.00, 'active', '[{\"label\": \"Quốc gia\", \"value\": \"Peru\"}, {\"label\": \"Bảo mật\", \"value\": \"No2FA\"}]', '2026-07-17 18:43:10', '2026-07-17 18:43:10', 'via peru new (no2fa)', NULL),
('019f72e4-442d-707e-a0fd-48b1bfa2969d', '019f72e4-43bf-7016-b10d-43eed06e3827', 'Via Philippines Cổ (No2FA)', 'via-philippines-co-no2fa', 'Friend: 30-5000 | LIVE ADS | Checkpoint Mail | Created: 2008-2023 | Chưa dính IP Việt', 72000.00, 'active', '[{\"label\": \"Quốc gia\", \"value\": \"Philippines\"}, {\"label\": \"Bảo mật\", \"value\": \"No2FA\"}]', '2026-07-17 18:43:10', '2026-07-17 18:43:10', 'via philippines co (no2fa)', NULL),
('019f72e4-4431-71b9-a604-974c3e11ecfe', '019f72e4-43bf-7016-b10d-43eed06e3827', 'Via Indonesia Cổ (No2FA)', 'via-indonesia-co-no2fa', 'Friend: 30-5000 | LIVE ADS | Checkpoint Mail | Created: 2008-2023 | Chưa dính IP Việt', 72000.00, 'active', '[{\"label\": \"Quốc gia\", \"value\": \"Indonesia\"}, {\"label\": \"Bảo mật\", \"value\": \"No2FA\"}]', '2026-07-17 18:43:10', '2026-07-17 18:43:10', 'via indonesia co (no2fa)', NULL),
('019f72e4-4434-73ff-9fc5-1089de0a3dc3', '019f72e4-43bf-7016-b10d-43eed06e3827', 'Via Bangladesh New (No2FA)', 'via-bangladesh-new-no2fa', 'Friend: 30-5000 | LIVE ADS | Checkpoint Mail | Created: 2024-2025 | Chưa dính IP Việt', 60000.00, 'active', '[{\"label\": \"Quốc gia\", \"value\": \"Bangladesh\"}, {\"label\": \"Bảo mật\", \"value\": \"No2FA\"}]', '2026-07-17 18:43:10', '2026-07-17 18:43:10', 'via bangladesh new (no2fa)', NULL),
('019f72e4-4437-725d-9666-11328c3eed2c', '019f72e4-43c2-7118-a806-bf5be68cc5fe', 'Via France (2FA)', 'via-france-2fa', 'Friend: 30-1000 | LIVE ADS | Created: 2025-2026 | Chưa dính IP Việt | Checkpoint Mail', 260000.00, 'active', '[{\"label\": \"Quốc gia\", \"value\": \"Pháp (France)\"}, {\"label\": \"Bảo mật\", \"value\": \"2FA\"}]', '2026-07-17 18:43:10', '2026-07-17 18:43:10', 'via france (2fa)', NULL),
('019f72e4-443a-72b1-a4fe-c7a7261a075a', '019f72e4-43c2-7118-a806-bf5be68cc5fe', 'Via Switzerland Cổ (2FA)', 'via-switzerland-co-2fa', 'Friend: 30-5000 | LIVE ADS | Created: 2008-2021 | Chưa dính IP Việt', 191000.00, 'active', '[{\"label\": \"Quốc gia\", \"value\": \"Thụy Sĩ\"}, {\"label\": \"Bảo mật\", \"value\": \"2FA\"}]', '2026-07-17 18:43:10', '2026-07-17 18:43:10', 'via switzerland co (2fa)', NULL),
('019f72e4-443d-72f7-a3bc-d33495d545fe', '019f72e4-43c2-7118-a806-bf5be68cc5fe', 'Via United Kingdom Cổ (2FA)', 'via-uk-co-2fa', 'Friend: 30-5000 | LIVE ADS | Created: 2008-2021 | Chưa dính IP Việt', 240000.00, 'active', '[{\"label\": \"Quốc gia\", \"value\": \"Anh Quốc (UK)\"}, {\"label\": \"Bảo mật\", \"value\": \"2FA\"}]', '2026-07-17 18:43:10', '2026-07-17 18:43:10', 'via united kingdom co (2fa)', NULL),
('019f72e4-4441-7007-92ee-a49e8f5e6edc', '019f72e4-43c2-7118-a806-bf5be68cc5fe', 'Via Poland Cổ (2FA)', 'via-poland-co-2fa', 'Friend: 30-5000 | LIVE ADS | Created: 2008-2021 | Chưa dính IP Việt', 191000.00, 'active', '[{\"label\": \"Quốc gia\", \"value\": \"Ba Lan\"}, {\"label\": \"Bảo mật\", \"value\": \"2FA\"}]', '2026-07-17 18:43:10', '2026-07-17 18:43:10', 'via poland co (2fa)', NULL),
('019f72e4-4444-71e3-a96f-7d9280b93b51', '019f72e4-43c2-7118-a806-bf5be68cc5fe', 'Via Germany Cổ (2FA)', 'via-germany-co-2fa', 'Friend: 30-5000 | LIVE ADS | Created: 2008-2021 | Chưa dính IP Việt', 200000.00, 'active', '[{\"label\": \"Quốc gia\", \"value\": \"Đức (Germany)\"}, {\"label\": \"Bảo mật\", \"value\": \"2FA\"}]', '2026-07-17 18:43:10', '2026-07-17 18:43:10', 'via germany co (2fa)', NULL),
('019f72e4-4446-725f-bbee-9b324af6cb89', '019f72e4-43c2-7118-a806-bf5be68cc5fe', 'Via Indonesia Cổ (2FA)', 'via-indonesia-co-2fa', 'Friend: 30-5000 | LIVE ADS | Created: 2008-2023 | Chưa dính IP Việt', 120000.00, 'active', '[{\"label\": \"Quốc gia\", \"value\": \"Indonesia\"}, {\"label\": \"Bảo mật\", \"value\": \"2FA\"}]', '2026-07-17 18:43:10', '2026-07-17 18:43:10', 'via indonesia co (2fa)', NULL),
('019f72e4-4449-7341-ac7c-283f59c351ce', '019f72e4-43c2-7118-a806-bf5be68cc5fe', 'Via Philippines Cổ (2FA)', 'via-philippines-co-2fa-ads', 'Friend: 30-5000 | LIVE ADS | Created: 2008-2023 | Chưa dính IP Việt', 120000.00, 'active', '[{\"label\": \"Quốc gia\", \"value\": \"Philippines\"}, {\"label\": \"Bảo mật\", \"value\": \"2FA\"}]', '2026-07-17 18:43:10', '2026-07-17 18:43:10', 'via philippines co (2fa)', NULL),
('019f72e4-444c-705b-9598-9eed54e41cdc', '019f72e4-43c2-7118-a806-bf5be68cc5fe', 'Via Egypt Cổ (2FA)', 'via-egypt-co-2fa', 'Friend: 30-5000 | LIVE ADS | Created: 2008-2024 | Chưa dính IP Việt', 96000.00, 'active', '[{\"label\": \"Quốc gia\", \"value\": \"Ai Cập\"}, {\"label\": \"Bảo mật\", \"value\": \"2FA\"}]', '2026-07-17 18:43:10', '2026-07-17 18:43:10', 'via egypt co (2fa)', NULL),
('019f72e4-4455-73ac-b73e-b9d52ee03871', '019f72e4-43c5-73a6-aedb-be84788308b3', 'Clone Ngoại Random Die Ads V2', 'clone-ngoai-random-die-ads-v2', 'Có Avatar + Cover + Info | Created: > 3 month | Very Hotmail | Đã Bật 2FA', 2500.00, 'active', '[{\"label\": \"Loại\", \"value\": \"Clone ngoại\"}, {\"label\": \"Bảo mật\", \"value\": \"Có 2FA\"}, {\"label\": \"Tuổi\", \"value\": \"> 3 tháng\"}]', '2026-07-17 18:43:10', '2026-07-17 18:43:10', 'clone ngoai random die ads v2', NULL),
('019f72e4-4458-706d-9f7a-5a7006eb8327', '019f72e4-43c5-73a6-aedb-be84788308b3', 'Clone United States', 'clone-us-facebook', 'Có Avatar + Cover + Info | Created: > 5 day | Created Ads: > 5 day | Verify Hotmail | Đã bật 2FA', 7000.00, 'active', '[{\"label\": \"Quốc gia\", \"value\": \"Mỹ (US)\"}, {\"label\": \"Xác minh\", \"value\": \"Verify Hotmail\"}]', '2026-07-17 18:43:10', '2026-07-17 18:43:10', 'clone united states', NULL),
('019f72e4-445a-7073-9d49-85032728c58d', '019f72e4-43c5-73a6-aedb-be84788308b3', 'Clone Canada', 'clone-canada-fb', 'Có Avatar + Cover + Info | Created: > 2 month | Created Ads: > 2 month | Verify Hotmail | Đã bật 2FA', 10000.00, 'active', '[{\"label\": \"Quốc gia\", \"value\": \"Canada\"}, {\"label\": \"Tuổi\", \"value\": \"> 2 tháng\"}]', '2026-07-17 18:43:10', '2026-07-17 18:43:10', 'clone canada', NULL),
('019f72e4-445d-7206-b498-00aefdf1d060', '019f72e4-43c8-715e-8bcb-78c91037528d', 'Clone Việt Nam Verify Hotmail', 'clone-vn-verify-hotmail', 'Có Avatar + Cover + Info | Ngày tạo: > 3 month | Đã bật 2FA | Very Hotmail', 7000.00, 'active', '[{\"label\": \"Quốc gia\", \"value\": \"Việt Nam\"}, {\"label\": \"Verify\", \"value\": \"Hotmail\"}, {\"label\": \"Bảo mật\", \"value\": \"2FA\"}]', '2026-07-17 18:43:10', '2026-07-17 18:43:10', 'clone viet nam verify hotmail', NULL),
('019f72e4-4460-70f8-be30-3aa8e9821da6', '019f72e4-43c8-715e-8bcb-78c91037528d', 'Clone Việt Nam Verify Gmail (No2FA)', 'clone-vn-verify-gmail-no2fa', 'Có Avatar + Cover + Info | Ngày tạo: > 2 month | Chưa bật 2FA | Very Gmail | Friend: 0-20', 6000.00, 'active', '[{\"label\": \"Quốc gia\", \"value\": \"Việt Nam\"}, {\"label\": \"Verify\", \"value\": \"Gmail\"}, {\"label\": \"Bảo mật\", \"value\": \"No2FA\"}]', '2026-07-17 18:43:10', '2026-07-17 18:43:10', 'clone viet nam verify gmail (no2fa)', NULL),
('019f72e4-4464-72eb-a342-e90ba3fb0d8f', '019f72e4-43cd-731a-8610-92ccfb65dab2', 'Via United States Cổ DIE ADS', 'via-us-co-die-ads', 'Thích hợp Seeding, Spy | DIE ADS | Friend : 30-5000 | Created: 2010-2021', 239000.00, 'active', '[{\"label\": \"Quốc gia\", \"value\": \"US (Mỹ)\"}, {\"label\": \"TKQC\", \"value\": \"Die Ads\"}]', '2026-07-17 18:43:10', '2026-07-17 18:43:10', 'via united states co die ads', NULL),
('019f72e4-4467-705a-92e4-430aef43d2b2', '019f72e4-43cd-731a-8610-92ccfb65dab2', 'Via Ngoại Cổ DIE ADS 3-5K Friend', 'via-ngoai-co-die-ads-3-5k-friend', 'Thích hợp Seeding | DIE ADS | Friend : 3000-5000 | Năm tạo: 2008-2024', 156000.00, 'active', '[{\"label\": \"Friend\", \"value\": \"3K - 5K\"}, {\"label\": \"TKQC\", \"value\": \"Die Ads\"}]', '2026-07-17 18:43:10', '2026-07-17 18:43:10', 'via ngoai co die ads 3-5k friend', NULL),
('019f72e4-4469-7093-92cb-2abc5a198d49', '019f72e4-43cd-731a-8610-92ccfb65dab2', 'Via Ngoại New Die Ads', 'via-ngoai-new-die-ads', 'Thích hợp Seeding | DIE ADS | Friend : 30-5000 | Checkpoint Mail | Created: 2024-2025', 24000.00, 'active', '[{\"label\": \"Loại\", \"value\": \"Via Ngoại New\"}, {\"label\": \"TKQC\", \"value\": \"Die Ads\"}]', '2026-07-17 18:43:10', '2026-07-17 18:43:10', 'via ngoai new die ads', NULL),
('019f72e4-446c-71b3-a5e8-13ccead641d3', '019f72e4-43d0-70be-87ab-44070500bada', 'Outlook Trusted [GRAPH API]', 'outlook-trusted-graph-api', 'Live for 6-12 months | Không cần very SĐT | Recommend to use Graph API for access', 400.00, 'active', '[{\"label\": \"Hạn dùng\", \"value\": \"6-12 tháng\"}, {\"label\": \"Yêu cầu\", \"value\": \"Graph API\"}]', '2026-07-17 18:43:10', '2026-07-17 18:43:10', 'outlook trusted [graph api]', NULL),
('019f72e4-446f-72a9-9218-b4c9c46a2f4b', '019f72e4-43d0-70be-87ab-44070500bada', 'Hotmail Trusted [GRAPH API]', 'hotmail-trusted-graph-api', 'Live for 6-12 months | Không cần very SĐT | Recommend to use Graph API for access', 400.00, 'active', '[{\"label\": \"Hạn dùng\", \"value\": \"6-12 tháng\"}, {\"label\": \"API\", \"value\": \"Graph API\"}]', '2026-07-17 18:43:10', '2026-07-17 18:43:10', 'hotmail trusted [graph api]', NULL),
('019f72e4-4472-7181-af3a-ff03af241587', '019f72e4-43d3-7284-a53d-2c453f22b68d', 'Clone Instagram US đã qua reg BM', 'clone-instagram-us-reg-bm', 'Reg Android | Verify Phone | Đã Bật 2FA | Name US | Created: > 2 month | Phù hợp spam, fishing...', 2000.00, 'active', '[{\"label\": \"Quốc gia\", \"value\": \"US (Mỹ)\"}, {\"label\": \"Đã Reg\", \"value\": \"BM Facebook\"}]', '2026-07-17 18:43:10', '2026-07-17 18:43:10', 'clone instagram us da qua reg bm', NULL),
('019f72e4-4475-7260-8404-7952387c6f06', '019f72e4-43d3-7284-a53d-2c453f22b68d', 'Clone Instagram US', 'clone-instagram-us-basic', 'Reg Android | Verify Phone | Đã Bật 2FA | Name US | Created: > 15 day', 3000.00, 'active', '[{\"label\": \"Quốc gia\", \"value\": \"US (Mỹ)\"}, {\"label\": \"Tuổi\", \"value\": \"> 15 ngày\"}]', '2026-07-17 18:43:10', '2026-07-17 18:43:10', 'clone instagram us', NULL);

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

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `users`
--

CREATE TABLE `users` (
  `id` char(36) COLLATE utf8mb4_unicode_ci NOT NULL,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `email` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `phone` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `email_verified_at` timestamp NULL DEFAULT NULL,
  `password` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `role` enum('admin','customer') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'customer',
  `balance` decimal(16,4) NOT NULL DEFAULT '0.0000',
  `refresh_token_hash` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `refresh_token_expired_at` timestamp NULL DEFAULT NULL,
  `remember_token` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `google2fa_secret` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `google2fa_enabled` tinyint(1) NOT NULL DEFAULT '0',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `normalized_name` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Đang đổ dữ liệu cho bảng `users`
--

INSERT INTO `users` (`id`, `name`, `email`, `phone`, `email_verified_at`, `password`, `role`, `balance`, `refresh_token_hash`, `refresh_token_expired_at`, `remember_token`, `google2fa_secret`, `google2fa_enabled`, `created_at`, `updated_at`, `normalized_name`) VALUES
('019f72e4-3bd0-72bc-8dc5-40301b68422d', 'admin', 'admin@isocalmarket.com', NULL, NULL, '$2y$10$sw4FhVTNSSkptewxrmudbO7D3L8pzpSRGCIz.74IaoumvQkE0nYfe', 'admin', 0.0000, '6e8ff7b7db7acde36ce082ea0a65b208f190c976c287f7261e8d00cb2e56ed15', '2026-07-24 19:11:04', NULL, NULL, 0, '2026-07-17 18:43:07', '2026-07-17 19:11:04', 'admin'),
('019f72e4-3c9a-7114-bbd9-de3a4f50ccd8', 'Nguyễn Văn A', 'customer1@isocalmarket.com', '0983356596', NULL, '$2y$12$oon25shVXugYAPKQaEp0HuKgmvJfVtmfc/HK5ROqIQ/VTxDX/mjQ.', 'customer', 0.0000, NULL, NULL, NULL, NULL, 0, '2026-07-17 18:43:08', '2026-07-17 19:10:31', 'nguyen van a'),
('019f72e4-3d69-73cd-8850-47d30ec2fbf6', 'Trần Thị B', 'customer2@isocalmarket.com', '0985039248', NULL, '$2y$12$FiH4p9ckeX4cGTgOjKBzoObTBkd6Buo0ScptfgjdLbeJtyX2Tidwq', 'customer', 0.0000, NULL, NULL, NULL, NULL, 0, '2026-07-17 18:43:08', '2026-07-17 19:10:31', 'tran thi b'),
('019f72e4-3e34-70b1-b4e9-4b30694f24ff', 'Lê Văn C', 'customer3@isocalmarket.com', '0989681126', NULL, '$2y$12$YgY6y33aaTG87.plOd/.qOjLgJFTXuRJ.ActXmCVBPQJQqY.HTAHW', 'customer', 0.0000, NULL, NULL, NULL, NULL, 0, '2026-07-17 18:43:08', '2026-07-17 19:10:31', 'le van c'),
('019f72e4-3efb-70f5-8dc4-f584916ea928', 'Phạm Thị D', 'customer4@isocalmarket.com', '0987100896', NULL, '$2y$12$67.yOh9AJhc.JMh.kdUY.u3FmPbWIn5zJPVCAulgATXXmoyKGA7Vi', 'customer', 0.0000, NULL, NULL, NULL, NULL, 0, '2026-07-17 18:43:08', '2026-07-17 19:10:31', 'pham thi d'),
('019f72e4-3fc1-7078-934f-32be5ca40e1a', 'Hoàng Văn E', 'customer5@isocalmarket.com', '0987921388', NULL, '$2y$12$UFXDEIfQ2vO2pCz4Xn11V.mHmpWIROd1tzOIetORjsq5o5ls./CXO', 'customer', 0.0000, NULL, NULL, NULL, NULL, 0, '2026-07-17 18:43:08', '2026-07-17 19:10:31', 'hoang van e'),
('019f72e4-4088-727c-a54a-88e05338ba4f', 'Đỗ Minh F', 'customer6@isocalmarket.com', '0984449965', NULL, '$2y$12$NNX/UCayEnXRZaW.owgfEugwLeZvH/rXQ/Sx0jEZypxo6d5aRDqqG', 'customer', 0.0000, NULL, NULL, NULL, NULL, 0, '2026-07-17 18:43:09', '2026-07-17 19:10:31', 'do minh f'),
('019f72e4-414f-701e-92e7-59e9943c0f8a', 'Vũ Thị G', 'customer7@isocalmarket.com', '0989056744', NULL, '$2y$12$d9aKGolr6GbgQrbtntB7xOaqpxBngpHv3zSuKQgIHmJ.e8bo5zv4i', 'customer', 0.0000, NULL, NULL, NULL, NULL, 0, '2026-07-17 18:43:09', '2026-07-17 19:10:31', 'vu thi g'),
('019f72e4-4216-7277-9a90-0da9d4c8829f', 'Phan Thanh H', 'customer8@isocalmarket.com', '0989176763', NULL, '$2y$12$9IZFCnlMHFmUQz5zH5a5tOyKxgEmy23bgxxIIgJK5gvRs/tJ1sMtq', 'customer', 0.0000, NULL, NULL, NULL, NULL, 0, '2026-07-17 18:43:09', '2026-07-17 19:10:31', 'phan thanh h'),
('019f72e4-42dc-7207-872c-0c72d2e8c777', 'Bùi Văn I', 'customer9@isocalmarket.com', '0983244693', NULL, '$2y$12$kw6CCw3t3Ql.7gSq/iPtWOVjdADsQbDzuEjpbGua6BLagAHb/qHWC', 'customer', 0.0000, NULL, NULL, NULL, NULL, 0, '2026-07-17 18:43:09', '2026-07-17 19:10:31', 'bui van i'),
('019f72e4-43a7-71b6-bdea-e3dec18e4257', 'Ngô Thị K', 'customer10@isocalmarket.com', '0985888277', NULL, '$2y$12$YyHBVziZSOoFNmx9WJODlu912UW5vtuxGc5BOPH6Dw/5309yLoTl.', 'customer', 0.0000, NULL, NULL, NULL, NULL, 0, '2026-07-17 18:43:09', '2026-07-17 19:10:31', 'ngo thi k');

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
  ADD KEY `categories_normalized_name_index` (`normalized_name`);

--
-- Chỉ mục cho bảng `deposits`
--
ALTER TABLE `deposits`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `deposits_tx_hash_unique` (`tx_hash`),
  ADD KEY `deposits_user_id_status_index` (`user_id`,`status`),
  ADD KEY `deposits_created_at_index` (`created_at`),
  ADD KEY `deposits_code_index` (`code`);

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
  ADD KEY `inventory_batches_normalized_batch_name_index` (`normalized_batch_name`);

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
  ADD KEY `orders_created_at_index` (`created_at`);

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
  ADD KEY `products_status_index` (`status`),
  ADD KEY `products_price_index` (`price`),
  ADD KEY `products_category_id_index` (`category_id`),
  ADD KEY `products_normalized_name_index` (`normalized_name`);

--
-- Chỉ mục cho bảng `sessions`
--
ALTER TABLE `sessions`
  ADD PRIMARY KEY (`id`),
  ADD KEY `sessions_user_id_index` (`user_id`),
  ADD KEY `sessions_last_activity_index` (`last_activity`);

--
-- Chỉ mục cho bảng `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `users_email_unique` (`email`),
  ADD KEY `users_name_index` (`name`),
  ADD KEY `users_refresh_token_hash_index` (`refresh_token_hash`),
  ADD KEY `users_normalized_name_index` (`normalized_name`);

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
  MODIFY `id` int UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=15;

--
-- Ràng buộc đối với các bảng kết xuất
--

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
  ADD CONSTRAINT `products_category_id_foreign` FOREIGN KEY (`category_id`) REFERENCES `categories` (`id`) ON DELETE RESTRICT;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
