-- phpMyAdmin SQL Dump
-- version 5.2.3
-- https://www.phpmyadmin.net/
--
-- Host: db
-- Generation Time: Mar 21, 2026 at 08:27 AM
-- Server version: 10.4.34-MariaDB-1:10.4.34+maria~ubu2004
-- PHP Version: 8.3.26

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `jsca_erp`
--

-- --------------------------------------------------------

--
-- Table structure for table `account_groups`
--

CREATE TABLE `account_groups` (
  `G_Name` varchar(10) NOT NULL,
  `Acc_Name` varchar(200) NOT NULL,
  `Acc_Type` varchar(100) NOT NULL,
  `YesNo` varchar(20) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

--
-- Dumping data for table `account_groups`
--

INSERT INTO `account_groups` (`G_Name`, `Acc_Name`, `Acc_Type`, `YesNo`) VALUES
('G1', 'ACCOUNTS PAYABLE', 'Libilities', 'No'),
('G10', 'DEPOSITS (Assets)', 'Assets', 'No'),
('G11', 'SALES TAX', 'Libilities', 'No'),
('G12', 'EXPENDITURE', 'Expenses', 'No'),
('G13', 'EXPENSES (Trading A/C)', 'Expenses', 'No'),
('G14', 'EXPENSES (Profit & Loss A/C)', 'Expenses', 'No'),
('G15', 'FIXED ASSETS', 'Assets', 'No'),
('G16', 'INCOME (Revenue)', 'Income', 'No'),
('G17', 'INVESTMENTS', 'Assets', 'No'),
('G18', 'LOANS & ADVANCES (Assets)', 'Assets', 'No'),
('G19', 'LOAN (Liabilities)', 'Libilities', 'No'),
('G2', 'ACCOUNTS RECEIVABLE', 'Assets', 'No'),
('G20', 'MFG. & TDG. EXPENSES', 'Expenses', 'No'),
('G21', 'MISC. EXPENSES (Assets)', 'Expenses', 'No'),
('G22', 'PROVISIONS', 'Liabilities', 'No'),
('G23', 'PURCHASE ACCOUNT', 'Expenses', 'No'),
('G24', 'RESERVES & SURPLUS', 'Liabilities', 'No'),
('G25', 'SALES ACCOUNT', 'Income', 'No'),
('G26', 'SECURED LOANS', 'Liabilities', 'No'),
('G27', 'OPENING STOCK', 'Assets', 'No'),
('G28', 'SUNDRY CREDITORS', 'Liabilities', 'No'),
('G29', 'SUNDRY DEBTORS', 'Assets', 'No'),
('G3', 'ADMN. EXPENSES', 'Expenses', 'No'),
('G30', 'SUSPENSE ACCOUNT', 'Expenses', 'No'),
('G31', 'UNSECURED LOANS', 'Liabilities', 'No'),
('G32', 'PURCHASE RETURNS', 'Expenses', 'No'),
('G33', 'SALES RETURNS', 'Income', 'No'),
('G34', 'WITHDRAWAL', 'Expenses', 'No'),
('G35', 'ADDITIONAL CAPITAL', 'Liabilities', 'No'),
('G36', 'CLOSING STOCK', 'Assets', 'No'),
('G4', 'BANK ACCOUNT', 'Assets', 'No'),
('G5', 'BANK OCC A/C', 'Liabilities', 'No'),
('G6', 'CAPITAL ACCOUNT', 'Liabilities', 'No'),
('G7', 'CASH A/C', 'Assets', 'No'),
('G8', 'ASSETS', 'Assets', 'No'),
('G9', 'LIABILITIES', 'Liabilities', 'No');

-- --------------------------------------------------------

--
-- Table structure for table `audit_logs`
--

CREATE TABLE `audit_logs` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `user_id` int(10) UNSIGNED DEFAULT NULL,
  `action` varchar(100) NOT NULL,
  `module` varchar(50) NOT NULL,
  `record_id` int(10) UNSIGNED DEFAULT NULL,
  `old_data` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`old_data`)),
  `new_data` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`new_data`)),
  `ip_address` varchar(45) DEFAULT NULL,
  `user_agent` varchar(255) DEFAULT NULL,
  `created_at` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `audit_logs`
--

INSERT INTO `audit_logs` (`id`, `user_id`, `action`, `module`, `record_id`, `old_data`, `new_data`, `ip_address`, `user_agent`, `created_at`) VALUES
(1, 1, 'LOGIN', 'auth', 1, NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36', '2026-03-10 11:43:14'),
(2, 1, 'LOGIN', 'auth', 1, NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36', '2026-03-10 11:47:21'),
(3, 1, 'LOGIN', 'auth', 1, NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36', '2026-03-10 12:08:22'),
(4, 1, 'LOGOUT', 'auth', 1, NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36', '2026-03-10 12:23:29'),
(5, 1, 'LOGIN', 'auth', 1, NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36', '2026-03-10 12:23:37'),
(6, 1, 'LOGOUT', 'auth', 1, NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36', '2026-03-10 13:05:05'),
(7, 1, 'LOGIN', 'auth', 1, NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36', '2026-03-10 13:05:34'),
(8, 1, 'LOGIN', 'auth', 1, NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36', '2026-03-13 11:14:31'),
(9, 1, 'LOGOUT', 'auth', 1, NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36', '2026-03-13 12:53:59'),
(10, 1, 'LOGIN', 'auth', 1, NULL, NULL, '172.19.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36', '2026-03-14 06:14:27'),
(11, 1, 'LOGIN', 'auth', 1, NULL, NULL, '172.19.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36', '2026-03-14 09:35:08'),
(12, 1, 'LOGIN', 'auth', 1, NULL, NULL, '172.19.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36', '2026-03-14 11:50:10'),
(13, 1, 'CREATE', 'players', 1, NULL, '{\"jsca_player_id\":\"JSCA-P-2026-00001\",\"full_name\":\"rowhit\",\"date_of_birth\":\"2026-03-14\",\"gender\":\"Male\",\"age_category\":\"U14\",\"district_id\":\"2\",\"role\":\"Batsman\",\"batting_style\":\"Right-hand\",\"bowling_style\":\"Right-arm Fast\",\"aadhaar_number\":\"312312312312\",\"phone\":\"6206086679\",\"email\":\"Ghgsa@gmail.com\",\"address\":\"ranchi\",\"guardian_name\":\"rk\",\"guardian_phone\":\"4234234234234\",\"photo_path\":\"uploads\\/players\\/1773491034_cf0c9ab476d0593b60dc.jpg\",\"registered_by\":\"1\",\"created_at\":\"2026-03-14 12:23:54\"}', '172.19.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36', '2026-03-14 12:23:54'),
(14, 1, 'LOGIN', 'auth', 1, NULL, NULL, '172.19.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36', '2026-03-16 04:27:01'),
(15, 1, 'LOGIN', 'auth', 1, NULL, NULL, '172.19.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36', '2026-03-16 11:54:04'),
(16, 1, 'LOGIN', 'auth', 1, NULL, NULL, '172.19.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36', '2026-03-17 07:09:33'),
(17, 1, 'LOGIN', 'auth', 1, NULL, NULL, '172.19.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36', '2026-03-17 09:22:07'),
(18, 1, 'LOGIN', 'auth', 1, NULL, NULL, '172.19.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36', '2026-03-17 13:07:00'),
(19, 1, 'CREATE', 'players', 2, NULL, '{\"jsca_player_id\":\"JSCA-P-2026-00002\",\"full_name\":\"riya\",\"date_of_birth\":\"2026-03-17\",\"gender\":\"Female\",\"age_category\":\"U14\",\"district_id\":\"3\",\"role\":\"All-rounder\",\"batting_style\":\"Right-hand\",\"bowling_style\":\"N\\/A\",\"aadhaar_number\":\"342222222222\",\"phone\":\"1222222222124\",\"email\":\"Ghgsa@gmail.com\",\"address\":\"dasdasdasdasdas\",\"guardian_name\":\"rjk\",\"guardian_phone\":\"5345335345345\",\"photo_path\":null,\"registered_by\":\"1\",\"created_at\":\"2026-03-17 13:22:29\"}', '172.19.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36', '2026-03-17 13:22:29'),
(20, 1, 'CREATE', 'players', 3, NULL, '{\"jsca_player_id\":\"JSCA-P-2026-00003\",\"full_name\":\"sukuna\",\"date_of_birth\":\"2002-01-17\",\"gender\":\"Male\",\"age_category\":\"Senior\",\"district_id\":\"8\",\"role\":\"Batsman\",\"batting_style\":\"Right-hand\",\"bowling_style\":\"N\\/A\",\"aadhaar_number\":\"123123123123\",\"phone\":\"6206086679\",\"email\":\"d@gmail.com\",\"address\":\"dasdasd, asdasdasdasd, Jharkhand, PIN: 321231\",\"guardian_name\":\"dasdasd\",\"guardian_phone\":\"6206086679\",\"photo_path\":null,\"registered_by\":\"1\",\"created_at\":\"2026-03-17 13:30:26\"}', '172.19.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36', '2026-03-17 13:30:26'),
(21, 1, 'CREATE', 'coaches', 1, NULL, '{\"jsca_coach_id\":\"JSCA-C-2026-0001\",\"full_name\":\"gojo\",\"date_of_birth\":\"2026-03-04\",\"gender\":\"Male\",\"phone\":\"6206086679\",\"email\":\"admin@school.com\",\"address\":\"ranhdi adnkasld dasjda \",\"district_id\":\"4\",\"specialization\":\"General\",\"level\":\"Head Coach\",\"bcci_coach_id\":\"312312312\",\"aadhaar_number\":\"312333333333\",\"experience_years\":2,\"previous_teams\":\"dasdas adsdas \",\"achievements\":\"dasdas das asdasdsad\",\"photo_path\":\"uploads\\/coaches\\/1773754374_94305e75ff7fd306bd0b.jpg\",\"registered_by\":\"1\",\"created_at\":\"2026-03-17 13:32:54\"}', '172.19.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36', '2026-03-17 13:32:54'),
(22, 1, 'LOGIN', 'auth', 1, NULL, NULL, '172.18.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Safari/537.36', '2026-03-21 04:26:55');

-- --------------------------------------------------------

--
-- Table structure for table `batting_stats`
--

CREATE TABLE `batting_stats` (
  `id` int(10) UNSIGNED NOT NULL,
  `fixture_id` int(10) UNSIGNED NOT NULL,
  `player_id` int(10) UNSIGNED NOT NULL,
  `team_id` int(10) UNSIGNED NOT NULL,
  `innings` tinyint(4) DEFAULT 1,
  `runs` int(11) DEFAULT 0,
  `balls_faced` int(11) DEFAULT 0,
  `fours` int(11) DEFAULT 0,
  `sixes` int(11) DEFAULT 0,
  `dismissal` enum('b','c','lbw','run out','hit wicket','retired hurt','not out','c&b','stumped') DEFAULT 'not out',
  `bowler_id` int(10) UNSIGNED DEFAULT NULL,
  `fielder_id` int(10) UNSIGNED DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `bowling_stats`
--

CREATE TABLE `bowling_stats` (
  `id` int(10) UNSIGNED NOT NULL,
  `fixture_id` int(10) UNSIGNED NOT NULL,
  `player_id` int(10) UNSIGNED NOT NULL,
  `team_id` int(10) UNSIGNED NOT NULL,
  `innings` tinyint(4) DEFAULT 1,
  `overs` decimal(4,1) DEFAULT 0.0,
  `maidens` int(11) DEFAULT 0,
  `runs_conceded` int(11) DEFAULT 0,
  `wickets` int(11) DEFAULT 0,
  `wides` int(11) DEFAULT 0,
  `no_balls` int(11) DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `coaches`
--

CREATE TABLE `coaches` (
  `id` int(10) UNSIGNED NOT NULL,
  `jsca_coach_id` varchar(30) NOT NULL,
  `full_name` varchar(100) NOT NULL,
  `date_of_birth` date NOT NULL,
  `gender` enum('Male','Female','Other') NOT NULL,
  `phone` varchar(15) DEFAULT NULL,
  `email` varchar(100) DEFAULT NULL,
  `address` text DEFAULT NULL,
  `district_id` int(10) UNSIGNED DEFAULT NULL,
  `photo_path` varchar(255) DEFAULT NULL,
  `specialization` enum('Batting','Bowling','Fielding','Wicket-keeping','Fitness','General') NOT NULL DEFAULT 'General',
  `level` enum('Assistant','Head Coach','Bowling Coach','Batting Coach','Fielding Coach','Fitness Trainer','NCA Level 1','NCA Level 2','NCA Level 3') NOT NULL DEFAULT 'Assistant',
  `bcci_coach_id` varchar(50) DEFAULT NULL COMMENT 'BCCI issued coach ID',
  `aadhaar_number` varchar(12) DEFAULT NULL,
  `aadhaar_verified` tinyint(1) NOT NULL DEFAULT 0,
  `experience_years` tinyint(3) UNSIGNED NOT NULL DEFAULT 0,
  `previous_teams` text DEFAULT NULL COMMENT 'Comma separated or JSON',
  `achievements` text DEFAULT NULL,
  `status` enum('Active','Inactive','Suspended') NOT NULL DEFAULT 'Active',
  `registered_by` int(10) UNSIGNED DEFAULT NULL,
  `created_at` datetime NOT NULL DEFAULT current_timestamp(),
  `updated_at` datetime NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `coaches`
--

INSERT INTO `coaches` (`id`, `jsca_coach_id`, `full_name`, `date_of_birth`, `gender`, `phone`, `email`, `address`, `district_id`, `photo_path`, `specialization`, `level`, `bcci_coach_id`, `aadhaar_number`, `aadhaar_verified`, `experience_years`, `previous_teams`, `achievements`, `status`, `registered_by`, `created_at`, `updated_at`) VALUES
(1, 'JSCA-C-2026-0001', 'gojo', '2026-03-04', 'Male', '6206086679', 'admin@school.com', 'ranhdi adnkasld dasjda ', 4, 'uploads/coaches/1773754374_94305e75ff7fd306bd0b.jpg', 'General', 'Head Coach', '312312312', '312333333333', 0, 2, 'dasdas adsdas ', 'dasdas das asdasdsad', 'Active', 1, '2026-03-17 13:32:54', '2026-03-17 13:32:54');

-- --------------------------------------------------------

--
-- Table structure for table `coach_documents`
--

CREATE TABLE `coach_documents` (
  `id` int(10) UNSIGNED NOT NULL,
  `coach_id` int(10) UNSIGNED NOT NULL,
  `doc_type` enum('aadhaar_front','aadhaar_back','coaching_certificate','bcci_certificate','nca_certificate','medical_fitness','police_verification','photo','other') NOT NULL,
  `label` varchar(100) DEFAULT NULL,
  `file_path` varchar(255) NOT NULL,
  `file_name` varchar(150) NOT NULL,
  `mime_type` varchar(80) DEFAULT NULL,
  `verified` tinyint(1) NOT NULL DEFAULT 0,
  `verified_by` int(10) UNSIGNED DEFAULT NULL,
  `verified_at` datetime DEFAULT NULL,
  `uploaded_by` int(10) UNSIGNED DEFAULT NULL,
  `created_at` datetime NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `districts`
--

CREATE TABLE `districts` (
  `id` int(10) UNSIGNED NOT NULL,
  `name` varchar(100) NOT NULL,
  `zone` enum('North','South','East','West','Central') NOT NULL,
  `code` varchar(5) NOT NULL,
  `lat` decimal(9,6) DEFAULT NULL,
  `lng` decimal(9,6) DEFAULT NULL,
  `is_active` tinyint(1) DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `districts`
--

INSERT INTO `districts` (`id`, `name`, `zone`, `code`, `lat`, `lng`, `is_active`) VALUES
(1, 'Ranchi', 'South', 'RCH', 23.344100, 85.309600, 1),
(2, 'Dhanbad', 'East', 'DHN', 23.795700, 86.430400, 1),
(3, 'Bokaro', 'East', 'BKR', 23.669300, 85.990700, 1),
(4, 'Jamshedpur', 'East', 'JMP', 22.804600, 86.202900, 1),
(5, 'Hazaribagh', 'North', 'HZB', 23.997500, 85.363700, 1),
(6, 'Giridih', 'North', 'GRD', 24.186800, 86.305700, 1),
(7, 'Deoghar', 'West', 'DGR', 24.486300, 86.692400, 1),
(8, 'Dumka', 'West', 'DMK', 24.267100, 87.246400, 1),
(9, 'Chatra', 'North', 'CHT', 24.200600, 84.872900, 1),
(10, 'Koderma', 'North', 'KDR', 24.464700, 85.596500, 1),
(11, 'Lohardaga', 'South', 'LHD', 23.435700, 84.685200, 1),
(12, 'Gumla', 'South', 'GML', 23.045100, 84.539200, 1),
(13, 'Simdega', 'South', 'SMD', 22.609200, 84.503100, 1),
(14, 'Pakur', 'West', 'PKR', 24.634400, 87.835700, 1),
(15, 'Godda', 'West', 'GDA', 24.829600, 87.210800, 1),
(16, 'Sahebganj', 'West', 'SHB', 25.241100, 87.636200, 1),
(17, 'Jamtara', 'West', 'JMT', 23.961400, 86.801600, 1),
(18, 'Palamu', 'Central', 'PLM', 24.029100, 84.073400, 1),
(19, 'Garhwa', 'Central', 'GRW', 24.168000, 83.804100, 1),
(20, 'Latehar', 'Central', 'LTR', 23.744900, 84.493700, 1),
(21, 'Khunti', 'South', 'KHT', 23.071700, 85.279700, 1),
(22, 'West Singhbhum', 'East', 'WSB', 22.567200, 85.511500, 1),
(23, 'Seraikela', 'East', 'SKL', 22.582700, 85.998700, 1),
(24, 'Ramgarh', 'North', 'RMG', 23.630400, 85.517700, 1);

-- --------------------------------------------------------

--
-- Table structure for table `fixtures`
--

CREATE TABLE `fixtures` (
  `id` int(10) UNSIGNED NOT NULL,
  `tournament_id` int(10) UNSIGNED NOT NULL,
  `match_number` varchar(10) NOT NULL,
  `stage` varchar(50) DEFAULT 'League',
  `zone` varchar(20) DEFAULT NULL,
  `match_date` date NOT NULL,
  `match_time` time NOT NULL,
  `team_a_id` int(10) UNSIGNED NOT NULL,
  `team_b_id` int(10) UNSIGNED NOT NULL,
  `venue_id` int(10) UNSIGNED NOT NULL,
  `is_day_night` tinyint(1) DEFAULT 0,
  `umpire1_id` int(10) UNSIGNED DEFAULT NULL,
  `umpire2_id` int(10) UNSIGNED DEFAULT NULL,
  `scorer_id` int(10) UNSIGNED DEFAULT NULL,
  `referee_id` int(10) UNSIGNED DEFAULT NULL,
  `status` enum('Scheduled','Live','Completed','Abandoned','Postponed') DEFAULT 'Scheduled',
  `winner_team_id` int(10) UNSIGNED DEFAULT NULL,
  `result_summary` text DEFAULT NULL,
  `crichieros_id` varchar(50) DEFAULT NULL,
  `youtube_url` varchar(255) DEFAULT NULL,
  `created_by` int(10) UNSIGNED DEFAULT NULL,
  `created_at` datetime DEFAULT current_timestamp(),
  `updated_at` datetime DEFAULT NULL ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `ledger_heads`
--

CREATE TABLE `ledger_heads` (
  `id` int(11) NOT NULL,
  `group_id` varchar(10) NOT NULL,
  `name` varchar(100) NOT NULL,
  `opening_balance` decimal(12,2) DEFAULT 0.00,
  `balance_type` enum('Dr','Cr') DEFAULT 'Dr',
  `created_at` datetime DEFAULT NULL,
  `updated_at` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

--
-- Dumping data for table `ledger_heads`
--

INSERT INTO `ledger_heads` (`id`, `group_id`, `name`, `opening_balance`, `balance_type`, `created_at`, `updated_at`) VALUES
(1, 'G1', 'XYZ ', 5401.12, 'Dr', '2026-03-21 08:24:23', NULL);

-- --------------------------------------------------------

--
-- Table structure for table `live_matches`
--

CREATE TABLE `live_matches` (
  `id` int(10) UNSIGNED NOT NULL,
  `team_a_id` int(10) UNSIGNED DEFAULT NULL,
  `team_b_id` int(10) UNSIGNED DEFAULT NULL,
  `team_a_custom` varchar(100) DEFAULT NULL COMMENT 'Custom team name if not in teams table',
  `team_b_custom` varchar(100) DEFAULT NULL COMMENT 'Custom team name if not in teams table',
  `team_a_score` varchar(50) DEFAULT NULL COMMENT 'e.g. 145/6 (18 ov)',
  `team_b_score` varchar(50) DEFAULT NULL,
  `venue` varchar(150) DEFAULT NULL,
  `tournament_name` varchar(150) DEFAULT NULL,
  `match_type` enum('T20','ODI','Test','T10','Other') NOT NULL DEFAULT 'T20',
  `status` enum('live','completed','abandoned') NOT NULL DEFAULT 'live',
  `notes` varchar(255) DEFAULT NULL,
  `created_by` int(10) UNSIGNED DEFAULT NULL,
  `created_at` datetime NOT NULL DEFAULT current_timestamp(),
  `updated_at` datetime NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `match_scorecards`
--

CREATE TABLE `match_scorecards` (
  `id` int(10) UNSIGNED NOT NULL,
  `fixture_id` int(10) UNSIGNED NOT NULL,
  `toss_winner_id` int(10) UNSIGNED DEFAULT NULL,
  `toss_decision` enum('Bat','Bowl') DEFAULT NULL,
  `team_a_score` varchar(20) DEFAULT NULL,
  `team_b_score` varchar(20) DEFAULT NULL,
  `team_a_overs` decimal(4,1) DEFAULT NULL,
  `team_b_overs` decimal(4,1) DEFAULT NULL,
  `player_of_match` int(10) UNSIGNED DEFAULT NULL,
  `source` enum('Manual','CricHeroes','API') DEFAULT 'Manual',
  `notes` text DEFAULT NULL,
  `created_at` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `officials`
--

CREATE TABLE `officials` (
  `id` int(10) UNSIGNED NOT NULL,
  `jsca_official_id` varchar(20) NOT NULL,
  `full_name` varchar(100) NOT NULL,
  `role` enum('Umpire','Scorer','Match Referee','Ground Staff','TV Umpire') NOT NULL,
  `grade` enum('A','B','C','D','Panel') DEFAULT 'C',
  `district_id` int(10) UNSIGNED DEFAULT NULL,
  `phone` varchar(15) DEFAULT NULL,
  `email` varchar(150) DEFAULT NULL,
  `bank_name` varchar(100) DEFAULT NULL,
  `bank_account` varchar(20) DEFAULT NULL,
  `bank_ifsc` varchar(11) DEFAULT NULL,
  `fee_per_match` decimal(8,2) DEFAULT 500.00,
  `is_active` tinyint(1) DEFAULT 1,
  `created_at` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `payment_vouchers`
--

CREATE TABLE `payment_vouchers` (
  `id` int(10) UNSIGNED NOT NULL,
  `voucher_number` varchar(20) NOT NULL,
  `fixture_id` int(10) UNSIGNED DEFAULT NULL,
  `tournament_id` int(10) UNSIGNED DEFAULT NULL,
  `official_id` int(10) UNSIGNED DEFAULT NULL,
  `payee_name` varchar(100) NOT NULL,
  `payee_type` enum('Umpire','Scorer','Referee','Vendor','Player','Staff','Other') NOT NULL,
  `amount` decimal(10,2) NOT NULL,
  `description` text DEFAULT NULL,
  `bank_account` varchar(20) DEFAULT NULL,
  `bank_ifsc` varchar(11) DEFAULT NULL,
  `bank_name` varchar(100) DEFAULT NULL,
  `status` enum('Draft','Pending Approval','Approved','Paid','Rejected','Cancelled') DEFAULT 'Draft',
  `created_by` int(10) UNSIGNED DEFAULT NULL,
  `approved_by` int(10) UNSIGNED DEFAULT NULL,
  `approved_at` datetime DEFAULT NULL,
  `paid_at` datetime DEFAULT NULL,
  `payment_ref` varchar(100) DEFAULT NULL,
  `payment_mode` enum('NEFT','RTGS','UPI','Cash','Cheque') DEFAULT 'NEFT',
  `receipt_path` varchar(255) DEFAULT NULL,
  `remarks` text DEFAULT NULL,
  `created_at` datetime DEFAULT current_timestamp(),
  `updated_at` datetime DEFAULT NULL ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `players`
--

CREATE TABLE `players` (
  `id` int(10) UNSIGNED NOT NULL,
  `jsca_player_id` varchar(20) NOT NULL,
  `full_name` varchar(100) NOT NULL,
  `date_of_birth` date NOT NULL,
  `gender` enum('Male','Female','Other') DEFAULT 'Male',
  `age_category` enum('U14','U16','U19','Senior','Masters') NOT NULL,
  `district_id` int(10) UNSIGNED NOT NULL,
  `role` enum('Batsman','Bowler','All-rounder','Wicket-keeper') NOT NULL,
  `batting_style` enum('Right-hand','Left-hand') DEFAULT NULL,
  `bowling_style` enum('Right-arm Fast','Right-arm Medium','Right-arm Off-spin','Right-arm Leg-spin','Left-arm Fast','Left-arm Medium','Left-arm Orthodox','Left-arm Chinaman','N/A') DEFAULT 'N/A',
  `aadhaar_number` varchar(12) DEFAULT NULL,
  `aadhaar_verified` tinyint(1) DEFAULT 0,
  `digilocker_id` varchar(50) DEFAULT NULL,
  `photo_path` varchar(255) DEFAULT NULL,
  `address` text DEFAULT NULL,
  `guardian_name` varchar(100) DEFAULT NULL,
  `guardian_phone` varchar(15) DEFAULT NULL,
  `email` varchar(150) DEFAULT NULL,
  `phone` varchar(15) DEFAULT NULL,
  `status` enum('Active','Inactive','Suspended','Retired') DEFAULT 'Active',
  `selection_pool` enum('District','State','None') DEFAULT 'None',
  `registered_by` int(10) UNSIGNED DEFAULT NULL,
  `created_at` datetime DEFAULT current_timestamp(),
  `updated_at` datetime DEFAULT NULL ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `players`
--

INSERT INTO `players` (`id`, `jsca_player_id`, `full_name`, `date_of_birth`, `gender`, `age_category`, `district_id`, `role`, `batting_style`, `bowling_style`, `aadhaar_number`, `aadhaar_verified`, `digilocker_id`, `photo_path`, `address`, `guardian_name`, `guardian_phone`, `email`, `phone`, `status`, `selection_pool`, `registered_by`, `created_at`, `updated_at`) VALUES
(1, 'JSCA-P-2026-00001', 'rowhit', '2026-03-14', 'Male', 'U14', 2, 'Batsman', 'Right-hand', 'Right-arm Fast', '312312312312', 0, NULL, 'uploads/players/1773491034_cf0c9ab476d0593b60dc.jpg', 'ranchi', 'rk', '4234234234234', 'Ghgsa@gmail.com', '6206086679', 'Active', 'None', 1, '2026-03-14 12:23:54', NULL),
(2, 'JSCA-P-2026-00002', 'riya', '2026-03-17', 'Female', 'U14', 3, 'All-rounder', 'Right-hand', 'N/A', '342222222222', 0, NULL, NULL, 'dasdasdasdasdas', 'rjk', '5345335345345', 'Ghgsa@gmail.com', '1222222222124', 'Active', 'None', 1, '2026-03-17 13:22:29', NULL),
(3, 'JSCA-P-2026-00003', 'sukuna', '2002-01-17', 'Male', 'Senior', 8, 'Batsman', 'Right-hand', 'N/A', '123123123123', 0, NULL, NULL, 'dasdasd, asdasdasdasd, Jharkhand, PIN: 321231', 'dasdasd', '6206086679', 'd@gmail.com', '6206086679', 'Active', 'None', 1, '2026-03-17 13:30:26', NULL);

-- --------------------------------------------------------

--
-- Table structure for table `player_career_stats`
--

CREATE TABLE `player_career_stats` (
  `id` int(10) UNSIGNED NOT NULL,
  `player_id` int(10) UNSIGNED NOT NULL,
  `matches` int(11) DEFAULT 0,
  `runs` int(11) DEFAULT 0,
  `highest_score` int(11) DEFAULT 0,
  `batting_avg` decimal(6,2) DEFAULT 0.00,
  `strike_rate` decimal(6,2) DEFAULT 0.00,
  `fifties` int(11) DEFAULT 0,
  `hundreds` int(11) DEFAULT 0,
  `wickets` int(11) DEFAULT 0,
  `best_bowling` varchar(10) DEFAULT NULL,
  `bowling_avg` decimal(6,2) DEFAULT 0.00,
  `economy` decimal(5,2) DEFAULT 0.00,
  `catches` int(11) DEFAULT 0,
  `last_updated` datetime DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `player_career_stats`
--

INSERT INTO `player_career_stats` (`id`, `player_id`, `matches`, `runs`, `highest_score`, `batting_avg`, `strike_rate`, `fifties`, `hundreds`, `wickets`, `best_bowling`, `bowling_avg`, `economy`, `catches`, `last_updated`) VALUES
(1, 1, 0, 0, 0, 0.00, 0.00, 0, 0, 0, NULL, 0.00, 0.00, 0, '2026-03-14 12:23:54'),
(2, 2, 0, 0, 0, 0.00, 0.00, 0, 0, 0, NULL, 0.00, 0.00, 0, '2026-03-17 13:22:29'),
(3, 3, 0, 0, 0, 0.00, 0.00, 0, 0, 0, NULL, 0.00, 0.00, 0, '2026-03-17 13:30:26');

-- --------------------------------------------------------

--
-- Table structure for table `player_documents`
--

CREATE TABLE `player_documents` (
  `id` int(10) UNSIGNED NOT NULL,
  `player_id` int(10) UNSIGNED NOT NULL,
  `doc_type` enum('aadhaar_front','aadhaar_back','birth_certificate','school_certificate','noc','medical_fitness','photo','other') NOT NULL,
  `label` varchar(100) DEFAULT NULL COMMENT 'Custom label for other type',
  `file_path` varchar(255) NOT NULL,
  `file_name` varchar(150) NOT NULL,
  `mime_type` varchar(80) DEFAULT NULL,
  `verified` tinyint(1) NOT NULL DEFAULT 0,
  `verified_by` int(10) UNSIGNED DEFAULT NULL,
  `verified_at` datetime DEFAULT NULL,
  `uploaded_by` int(10) UNSIGNED DEFAULT NULL,
  `created_at` datetime NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `roles`
--

CREATE TABLE `roles` (
  `id` int(10) UNSIGNED NOT NULL,
  `name` varchar(50) NOT NULL,
  `permissions` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`permissions`)),
  `created_at` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `roles`
--

INSERT INTO `roles` (`id`, `name`, `permissions`, `created_at`) VALUES
(1, 'superadmin', '[\"all\"]', '2026-03-09 18:15:08'),
(2, 'admin', '[\"players\",\"fixtures\",\"finance\",\"tournaments\",\"officials\",\"reports\"]', '2026-03-09 18:15:08'),
(3, 'selector', '[\"players.view\",\"analytics.view\",\"tournaments.view\"]', '2026-03-09 18:15:08'),
(4, 'accounts', '[\"finance.view\",\"finance.approve\",\"reports.finance\"]', '2026-03-09 18:15:08'),
(5, 'umpire', '[\"fixtures.view\",\"matches.view\"]', '2026-03-09 18:15:08'),
(6, 'data_entry', '[\"players.create\",\"matches.score\",\"fixtures.view\"]', '2026-03-09 18:15:08');

-- --------------------------------------------------------

--
-- Table structure for table `teams`
--

CREATE TABLE `teams` (
  `id` int(10) UNSIGNED NOT NULL,
  `tournament_id` int(10) UNSIGNED NOT NULL,
  `district_id` int(10) UNSIGNED NOT NULL,
  `name` varchar(150) NOT NULL,
  `zone` enum('North','South','East','West','Central','None') DEFAULT 'None',
  `captain_id` int(10) UNSIGNED DEFAULT NULL,
  `vice_captain_id` int(10) UNSIGNED DEFAULT NULL,
  `status` enum('Registered','Confirmed','Withdrawn') DEFAULT 'Registered',
  `created_at` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `team_coaches`
--

CREATE TABLE `team_coaches` (
  `id` int(10) UNSIGNED NOT NULL,
  `team_id` int(10) UNSIGNED NOT NULL,
  `coach_id` int(10) UNSIGNED NOT NULL,
  `role` varchar(80) NOT NULL DEFAULT 'Head Coach',
  `from_date` date DEFAULT NULL,
  `to_date` date DEFAULT NULL,
  `is_current` tinyint(1) NOT NULL DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `team_documents`
--

CREATE TABLE `team_documents` (
  `id` int(10) UNSIGNED NOT NULL,
  `team_id` int(10) UNSIGNED NOT NULL,
  `doc_type` enum('registration_form','affiliation_certificate','noc','player_consent','insurance','other') NOT NULL,
  `label` varchar(100) DEFAULT NULL,
  `file_path` varchar(255) NOT NULL,
  `file_name` varchar(150) NOT NULL,
  `mime_type` varchar(80) DEFAULT NULL,
  `verified` tinyint(1) NOT NULL DEFAULT 0,
  `verified_by` int(10) UNSIGNED DEFAULT NULL,
  `verified_at` datetime DEFAULT NULL,
  `uploaded_by` int(10) UNSIGNED DEFAULT NULL,
  `created_at` datetime NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `team_players`
--

CREATE TABLE `team_players` (
  `id` int(10) UNSIGNED NOT NULL,
  `team_id` int(10) UNSIGNED NOT NULL,
  `player_id` int(10) UNSIGNED NOT NULL,
  `jersey_number` int(11) DEFAULT NULL,
  `is_captain` tinyint(1) DEFAULT 0,
  `is_vice_captain` tinyint(1) DEFAULT 0,
  `is_wk` tinyint(1) DEFAULT 0,
  `created_at` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `tournaments`
--

CREATE TABLE `tournaments` (
  `id` int(10) UNSIGNED NOT NULL,
  `name` varchar(200) NOT NULL,
  `season` varchar(10) NOT NULL,
  `age_category` enum('U14','U16','U19','Senior','Masters','Women') NOT NULL,
  `gender` enum('Male','Female','Mixed') DEFAULT 'Male',
  `format` enum('T10','T20','ODI-40','ODI-50','Test','Custom') NOT NULL,
  `overs` int(11) DEFAULT 20,
  `structure` enum('Round Robin','Knockout','Group+Knockout','League+Playoffs','Zonal') NOT NULL,
  `is_zonal` tinyint(1) DEFAULT 0,
  `start_date` date DEFAULT NULL,
  `end_date` date DEFAULT NULL,
  `registration_deadline` date DEFAULT NULL,
  `total_teams` int(11) DEFAULT 0,
  `total_matches` int(11) DEFAULT 0,
  `status` enum('Draft','Registration','Fixture Ready','Ongoing','Completed','Cancelled') DEFAULT 'Draft',
  `prize_pool` decimal(12,2) DEFAULT 0.00,
  `travel_constraint` enum('Minimize','Zonal','Centralized','None') DEFAULT 'None',
  `created_by` int(10) UNSIGNED DEFAULT NULL,
  `created_at` datetime DEFAULT current_timestamp(),
  `updated_at` datetime DEFAULT NULL ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `tournament_budgets`
--

CREATE TABLE `tournament_budgets` (
  `id` int(10) UNSIGNED NOT NULL,
  `tournament_id` int(10) UNSIGNED NOT NULL,
  `total_budget` decimal(12,2) DEFAULT 0.00,
  `allocated` decimal(12,2) DEFAULT 0.00,
  `spent` decimal(12,2) DEFAULT 0.00,
  `updated_at` datetime DEFAULT NULL ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `tournament_documents`
--

CREATE TABLE `tournament_documents` (
  `id` int(10) UNSIGNED NOT NULL,
  `tournament_id` int(10) UNSIGNED NOT NULL,
  `doc_type` enum('approval_letter','bcci_sanction','insurance','schedule','rules_regulations','sponsorship_agreement','other') NOT NULL,
  `label` varchar(100) DEFAULT NULL,
  `file_path` varchar(255) NOT NULL,
  `file_name` varchar(150) NOT NULL,
  `mime_type` varchar(80) DEFAULT NULL,
  `verified` tinyint(1) NOT NULL DEFAULT 0,
  `verified_by` int(10) UNSIGNED DEFAULT NULL,
  `verified_at` datetime DEFAULT NULL,
  `uploaded_by` int(10) UNSIGNED DEFAULT NULL,
  `created_at` datetime NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` int(10) UNSIGNED NOT NULL,
  `role_id` int(10) UNSIGNED NOT NULL,
  `full_name` varchar(100) NOT NULL,
  `email` varchar(150) NOT NULL,
  `phone` varchar(15) DEFAULT NULL,
  `password_hash` varchar(255) NOT NULL,
  `is_active` tinyint(1) DEFAULT 1,
  `last_login` datetime DEFAULT NULL,
  `reset_token` varchar(100) DEFAULT NULL,
  `reset_expires` datetime DEFAULT NULL,
  `created_at` datetime DEFAULT current_timestamp(),
  `updated_at` datetime DEFAULT NULL ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `role_id`, `full_name`, `email`, `phone`, `password_hash`, `is_active`, `last_login`, `reset_token`, `reset_expires`, `created_at`, `updated_at`) VALUES
(1, 1, 'JSCA Super Admin', 'admin@jsca.in', '9000000001', '$2y$10$GIe68NrFKjr14UXXAUCqHufkk6gBxgZWYD9OxpcIMvhAkfyIPl.Vi', 1, '2026-03-21 04:26:55', NULL, NULL, '2026-03-09 18:15:09', '2026-03-21 04:26:55');

-- --------------------------------------------------------

--
-- Table structure for table `venues`
--

CREATE TABLE `venues` (
  `id` int(10) UNSIGNED NOT NULL,
  `name` varchar(150) NOT NULL,
  `district_id` int(10) UNSIGNED NOT NULL,
  `capacity` int(11) DEFAULT 0,
  `has_floodlights` tinyint(1) DEFAULT 0,
  `has_scoreboard` tinyint(1) DEFAULT 0,
  `has_dressing` tinyint(1) DEFAULT 0,
  `pitch_type` enum('Grass','Turf','Concrete','Red-soil') DEFAULT 'Grass',
  `contact_person` varchar(100) DEFAULT NULL,
  `contact_phone` varchar(15) DEFAULT NULL,
  `address` text DEFAULT NULL,
  `lat` decimal(9,6) DEFAULT NULL,
  `lng` decimal(9,6) DEFAULT NULL,
  `is_active` tinyint(1) DEFAULT 1,
  `created_at` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `venues`
--

INSERT INTO `venues` (`id`, `name`, `district_id`, `capacity`, `has_floodlights`, `has_scoreboard`, `has_dressing`, `pitch_type`, `contact_person`, `contact_phone`, `address`, `lat`, `lng`, `is_active`, `created_at`) VALUES
(1, 'JSCA International Stadium', 1, 45000, 1, 0, 0, 'Grass', NULL, NULL, NULL, NULL, NULL, 1, '2026-03-09 18:15:09'),
(2, 'Keenan Stadium', 4, 25000, 1, 0, 0, 'Grass', NULL, NULL, NULL, NULL, NULL, 1, '2026-03-09 18:15:09'),
(3, 'Bokaro Steel City Ground', 3, 8000, 0, 0, 0, 'Grass', NULL, NULL, NULL, NULL, NULL, 1, '2026-03-09 18:15:09'),
(4, 'Dhanbad District Ground', 2, 5000, 0, 0, 0, 'Grass', NULL, NULL, NULL, NULL, NULL, 1, '2026-03-09 18:15:09'),
(5, 'Hazaribagh Ground', 5, 4000, 0, 0, 0, 'Grass', NULL, NULL, NULL, NULL, NULL, 1, '2026-03-09 18:15:09'),
(6, 'Deoghar Cricket Ground', 7, 3500, 0, 0, 0, 'Grass', NULL, NULL, NULL, NULL, NULL, 1, '2026-03-09 18:15:09'),
(7, 'Giridih Sports Complex', 6, 3000, 0, 0, 0, 'Grass', NULL, NULL, NULL, NULL, NULL, 1, '2026-03-09 18:15:09'),
(8, 'Dumka Stadium', 8, 4500, 1, 0, 0, 'Grass', NULL, NULL, NULL, NULL, NULL, 1, '2026-03-09 18:15:09');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `account_groups`
--
ALTER TABLE `account_groups`
  ADD PRIMARY KEY (`G_Name`);

--
-- Indexes for table `audit_logs`
--
ALTER TABLE `audit_logs`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_module_record` (`module`,`record_id`),
  ADD KEY `idx_user` (`user_id`);

--
-- Indexes for table `batting_stats`
--
ALTER TABLE `batting_stats`
  ADD PRIMARY KEY (`id`),
  ADD KEY `fixture_id` (`fixture_id`),
  ADD KEY `team_id` (`team_id`),
  ADD KEY `idx_player_fixture` (`player_id`,`fixture_id`);

--
-- Indexes for table `bowling_stats`
--
ALTER TABLE `bowling_stats`
  ADD PRIMARY KEY (`id`),
  ADD KEY `fixture_id` (`fixture_id`),
  ADD KEY `player_id` (`player_id`),
  ADD KEY `team_id` (`team_id`);

--
-- Indexes for table `coaches`
--
ALTER TABLE `coaches`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `jsca_coach_id` (`jsca_coach_id`),
  ADD KEY `idx_status` (`status`),
  ADD KEY `idx_district` (`district_id`);

--
-- Indexes for table `coach_documents`
--
ALTER TABLE `coach_documents`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_coach` (`coach_id`);

--
-- Indexes for table `districts`
--
ALTER TABLE `districts`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `code` (`code`);

--
-- Indexes for table `fixtures`
--
ALTER TABLE `fixtures`
  ADD PRIMARY KEY (`id`),
  ADD KEY `team_a_id` (`team_a_id`),
  ADD KEY `team_b_id` (`team_b_id`),
  ADD KEY `venue_id` (`venue_id`),
  ADD KEY `umpire1_id` (`umpire1_id`),
  ADD KEY `umpire2_id` (`umpire2_id`),
  ADD KEY `scorer_id` (`scorer_id`),
  ADD KEY `referee_id` (`referee_id`),
  ADD KEY `idx_tournament_date` (`tournament_id`,`match_date`);

--
-- Indexes for table `ledger_heads`
--
ALTER TABLE `ledger_heads`
  ADD PRIMARY KEY (`id`),
  ADD KEY `group_id` (`group_id`);

--
-- Indexes for table `live_matches`
--
ALTER TABLE `live_matches`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_status` (`status`),
  ADD KEY `fk_lm_team_a` (`team_a_id`),
  ADD KEY `fk_lm_team_b` (`team_b_id`);

--
-- Indexes for table `match_scorecards`
--
ALTER TABLE `match_scorecards`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `fixture_id` (`fixture_id`),
  ADD KEY `player_of_match` (`player_of_match`);

--
-- Indexes for table `officials`
--
ALTER TABLE `officials`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `jsca_official_id` (`jsca_official_id`),
  ADD KEY `district_id` (`district_id`);

--
-- Indexes for table `payment_vouchers`
--
ALTER TABLE `payment_vouchers`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `voucher_number` (`voucher_number`),
  ADD KEY `fixture_id` (`fixture_id`),
  ADD KEY `official_id` (`official_id`),
  ADD KEY `created_by` (`created_by`),
  ADD KEY `approved_by` (`approved_by`),
  ADD KEY `idx_status` (`status`),
  ADD KEY `idx_tournament` (`tournament_id`);

--
-- Indexes for table `players`
--
ALTER TABLE `players`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `jsca_player_id` (`jsca_player_id`),
  ADD KEY `registered_by` (`registered_by`),
  ADD KEY `idx_age_category` (`age_category`),
  ADD KEY `idx_district` (`district_id`),
  ADD KEY `idx_status` (`status`);

--
-- Indexes for table `player_career_stats`
--
ALTER TABLE `player_career_stats`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `player_id` (`player_id`);

--
-- Indexes for table `player_documents`
--
ALTER TABLE `player_documents`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_player` (`player_id`);

--
-- Indexes for table `roles`
--
ALTER TABLE `roles`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `name` (`name`);

--
-- Indexes for table `teams`
--
ALTER TABLE `teams`
  ADD PRIMARY KEY (`id`),
  ADD KEY `tournament_id` (`tournament_id`),
  ADD KEY `district_id` (`district_id`),
  ADD KEY `captain_id` (`captain_id`),
  ADD KEY `vice_captain_id` (`vice_captain_id`);

--
-- Indexes for table `team_coaches`
--
ALTER TABLE `team_coaches`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `uq_team_coach` (`team_id`,`coach_id`,`is_current`),
  ADD KEY `fk_tc_coach` (`coach_id`);

--
-- Indexes for table `team_documents`
--
ALTER TABLE `team_documents`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_team` (`team_id`);

--
-- Indexes for table `team_players`
--
ALTER TABLE `team_players`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `uq_team_player` (`team_id`,`player_id`),
  ADD KEY `player_id` (`player_id`);

--
-- Indexes for table `tournaments`
--
ALTER TABLE `tournaments`
  ADD PRIMARY KEY (`id`),
  ADD KEY `created_by` (`created_by`);

--
-- Indexes for table `tournament_budgets`
--
ALTER TABLE `tournament_budgets`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `tournament_id` (`tournament_id`);

--
-- Indexes for table `tournament_documents`
--
ALTER TABLE `tournament_documents`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_tournament` (`tournament_id`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `email` (`email`),
  ADD KEY `role_id` (`role_id`);

--
-- Indexes for table `venues`
--
ALTER TABLE `venues`
  ADD PRIMARY KEY (`id`),
  ADD KEY `district_id` (`district_id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `audit_logs`
--
ALTER TABLE `audit_logs`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=23;

--
-- AUTO_INCREMENT for table `batting_stats`
--
ALTER TABLE `batting_stats`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `bowling_stats`
--
ALTER TABLE `bowling_stats`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `coaches`
--
ALTER TABLE `coaches`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `coach_documents`
--
ALTER TABLE `coach_documents`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `districts`
--
ALTER TABLE `districts`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=25;

--
-- AUTO_INCREMENT for table `fixtures`
--
ALTER TABLE `fixtures`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `ledger_heads`
--
ALTER TABLE `ledger_heads`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `live_matches`
--
ALTER TABLE `live_matches`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `match_scorecards`
--
ALTER TABLE `match_scorecards`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `officials`
--
ALTER TABLE `officials`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `payment_vouchers`
--
ALTER TABLE `payment_vouchers`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `players`
--
ALTER TABLE `players`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `player_career_stats`
--
ALTER TABLE `player_career_stats`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `player_documents`
--
ALTER TABLE `player_documents`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `roles`
--
ALTER TABLE `roles`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT for table `teams`
--
ALTER TABLE `teams`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `team_coaches`
--
ALTER TABLE `team_coaches`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `team_documents`
--
ALTER TABLE `team_documents`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `team_players`
--
ALTER TABLE `team_players`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `tournaments`
--
ALTER TABLE `tournaments`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `tournament_budgets`
--
ALTER TABLE `tournament_budgets`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `tournament_documents`
--
ALTER TABLE `tournament_documents`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `venues`
--
ALTER TABLE `venues`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `batting_stats`
--
ALTER TABLE `batting_stats`
  ADD CONSTRAINT `batting_stats_ibfk_1` FOREIGN KEY (`fixture_id`) REFERENCES `fixtures` (`id`),
  ADD CONSTRAINT `batting_stats_ibfk_2` FOREIGN KEY (`player_id`) REFERENCES `players` (`id`),
  ADD CONSTRAINT `batting_stats_ibfk_3` FOREIGN KEY (`team_id`) REFERENCES `teams` (`id`);

--
-- Constraints for table `bowling_stats`
--
ALTER TABLE `bowling_stats`
  ADD CONSTRAINT `bowling_stats_ibfk_1` FOREIGN KEY (`fixture_id`) REFERENCES `fixtures` (`id`),
  ADD CONSTRAINT `bowling_stats_ibfk_2` FOREIGN KEY (`player_id`) REFERENCES `players` (`id`),
  ADD CONSTRAINT `bowling_stats_ibfk_3` FOREIGN KEY (`team_id`) REFERENCES `teams` (`id`);

--
-- Constraints for table `coach_documents`
--
ALTER TABLE `coach_documents`
  ADD CONSTRAINT `fk_cd_coach` FOREIGN KEY (`coach_id`) REFERENCES `coaches` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `fixtures`
--
ALTER TABLE `fixtures`
  ADD CONSTRAINT `fixtures_ibfk_1` FOREIGN KEY (`tournament_id`) REFERENCES `tournaments` (`id`),
  ADD CONSTRAINT `fixtures_ibfk_2` FOREIGN KEY (`team_a_id`) REFERENCES `teams` (`id`),
  ADD CONSTRAINT `fixtures_ibfk_3` FOREIGN KEY (`team_b_id`) REFERENCES `teams` (`id`),
  ADD CONSTRAINT `fixtures_ibfk_4` FOREIGN KEY (`venue_id`) REFERENCES `venues` (`id`),
  ADD CONSTRAINT `fixtures_ibfk_5` FOREIGN KEY (`umpire1_id`) REFERENCES `officials` (`id`),
  ADD CONSTRAINT `fixtures_ibfk_6` FOREIGN KEY (`umpire2_id`) REFERENCES `officials` (`id`),
  ADD CONSTRAINT `fixtures_ibfk_7` FOREIGN KEY (`scorer_id`) REFERENCES `officials` (`id`),
  ADD CONSTRAINT `fixtures_ibfk_8` FOREIGN KEY (`referee_id`) REFERENCES `officials` (`id`);

--
-- Constraints for table `ledger_heads`
--
ALTER TABLE `ledger_heads`
  ADD CONSTRAINT `ledger_heads_ibfk_1` FOREIGN KEY (`group_id`) REFERENCES `account_groups` (`G_Name`);

--
-- Constraints for table `live_matches`
--
ALTER TABLE `live_matches`
  ADD CONSTRAINT `fk_lm_team_a` FOREIGN KEY (`team_a_id`) REFERENCES `teams` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `fk_lm_team_b` FOREIGN KEY (`team_b_id`) REFERENCES `teams` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `match_scorecards`
--
ALTER TABLE `match_scorecards`
  ADD CONSTRAINT `match_scorecards_ibfk_1` FOREIGN KEY (`fixture_id`) REFERENCES `fixtures` (`id`),
  ADD CONSTRAINT `match_scorecards_ibfk_2` FOREIGN KEY (`player_of_match`) REFERENCES `players` (`id`);

--
-- Constraints for table `officials`
--
ALTER TABLE `officials`
  ADD CONSTRAINT `officials_ibfk_1` FOREIGN KEY (`district_id`) REFERENCES `districts` (`id`);

--
-- Constraints for table `payment_vouchers`
--
ALTER TABLE `payment_vouchers`
  ADD CONSTRAINT `payment_vouchers_ibfk_1` FOREIGN KEY (`fixture_id`) REFERENCES `fixtures` (`id`),
  ADD CONSTRAINT `payment_vouchers_ibfk_2` FOREIGN KEY (`tournament_id`) REFERENCES `tournaments` (`id`),
  ADD CONSTRAINT `payment_vouchers_ibfk_3` FOREIGN KEY (`official_id`) REFERENCES `officials` (`id`),
  ADD CONSTRAINT `payment_vouchers_ibfk_4` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`),
  ADD CONSTRAINT `payment_vouchers_ibfk_5` FOREIGN KEY (`approved_by`) REFERENCES `users` (`id`);

--
-- Constraints for table `players`
--
ALTER TABLE `players`
  ADD CONSTRAINT `players_ibfk_1` FOREIGN KEY (`district_id`) REFERENCES `districts` (`id`),
  ADD CONSTRAINT `players_ibfk_2` FOREIGN KEY (`registered_by`) REFERENCES `users` (`id`);

--
-- Constraints for table `player_career_stats`
--
ALTER TABLE `player_career_stats`
  ADD CONSTRAINT `player_career_stats_ibfk_1` FOREIGN KEY (`player_id`) REFERENCES `players` (`id`);

--
-- Constraints for table `player_documents`
--
ALTER TABLE `player_documents`
  ADD CONSTRAINT `fk_pd_player` FOREIGN KEY (`player_id`) REFERENCES `players` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `teams`
--
ALTER TABLE `teams`
  ADD CONSTRAINT `teams_ibfk_1` FOREIGN KEY (`tournament_id`) REFERENCES `tournaments` (`id`),
  ADD CONSTRAINT `teams_ibfk_2` FOREIGN KEY (`district_id`) REFERENCES `districts` (`id`),
  ADD CONSTRAINT `teams_ibfk_3` FOREIGN KEY (`captain_id`) REFERENCES `players` (`id`),
  ADD CONSTRAINT `teams_ibfk_4` FOREIGN KEY (`vice_captain_id`) REFERENCES `players` (`id`);

--
-- Constraints for table `team_coaches`
--
ALTER TABLE `team_coaches`
  ADD CONSTRAINT `fk_tc_coach` FOREIGN KEY (`coach_id`) REFERENCES `coaches` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `team_documents`
--
ALTER TABLE `team_documents`
  ADD CONSTRAINT `fk_td_team` FOREIGN KEY (`team_id`) REFERENCES `teams` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `team_players`
--
ALTER TABLE `team_players`
  ADD CONSTRAINT `team_players_ibfk_1` FOREIGN KEY (`team_id`) REFERENCES `teams` (`id`),
  ADD CONSTRAINT `team_players_ibfk_2` FOREIGN KEY (`player_id`) REFERENCES `players` (`id`);

--
-- Constraints for table `tournaments`
--
ALTER TABLE `tournaments`
  ADD CONSTRAINT `tournaments_ibfk_1` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`);

--
-- Constraints for table `tournament_budgets`
--
ALTER TABLE `tournament_budgets`
  ADD CONSTRAINT `tournament_budgets_ibfk_1` FOREIGN KEY (`tournament_id`) REFERENCES `tournaments` (`id`);

--
-- Constraints for table `tournament_documents`
--
ALTER TABLE `tournament_documents`
  ADD CONSTRAINT `fk_trd_tournament` FOREIGN KEY (`tournament_id`) REFERENCES `tournaments` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `users`
--
ALTER TABLE `users`
  ADD CONSTRAINT `users_ibfk_1` FOREIGN KEY (`role_id`) REFERENCES `roles` (`id`);

--
-- Constraints for table `venues`
--
ALTER TABLE `venues`
  ADD CONSTRAINT `venues_ibfk_1` FOREIGN KEY (`district_id`) REFERENCES `districts` (`id`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
