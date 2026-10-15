-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Oct 15, 2026 at 01:24 PM
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
-- Database: `cyberarena`
--
CREATE DATABASE IF NOT EXISTS `cyberarena` DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci;
USE `cyberarena`;

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` int(11) NOT NULL,
  `username` varchar(50) DEFAULT NULL,
  `email` varchar(100) DEFAULT NULL,
  `password` varchar(100) DEFAULT NULL,
  `role` varchar(20) DEFAULT NULL,
  `gender` varchar(10) DEFAULT NULL,
  `team` varchar(50) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `username`, `email`, `password`, `role`, `gender`, `team`) VALUES
(1, 'admin', 'admin@cyberarena.com', 'admin123', 'ADMIN', 'Male', 'Cyber Arena'),
(2, '', '', '', 'Player', '', 'Alpha Squad'),
(3, 'batman', 'batman123@gmail.com', 'batman', 'Coach', 'Male', 'Phantom Team');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;
--
-- Database: `cyberarena_ml`
--
CREATE DATABASE IF NOT EXISTS `cyberarena_ml` DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci;
USE `cyberarena_ml`;

-- --------------------------------------------------------

--
-- Table structure for table `games`
--

CREATE TABLE `games` (
  `id` int(11) NOT NULL,
  `match_id` int(11) NOT NULL,
  `game_number` int(11) NOT NULL,
  `game_duration` int(11) DEFAULT 0,
  `game_result` varchar(10) DEFAULT NULL,
  `team1_id` int(11) DEFAULT NULL,
  `team2_id` int(11) DEFAULT NULL,
  `winner_id` int(11) DEFAULT NULL,
  `team1_ban1` int(11) DEFAULT NULL,
  `team1_ban2` int(11) DEFAULT NULL,
  `team1_ban3` int(11) DEFAULT NULL,
  `team1_ban4` int(11) DEFAULT NULL,
  `team1_ban5` int(11) DEFAULT NULL,
  `team2_ban1` int(11) DEFAULT NULL,
  `team2_ban2` int(11) DEFAULT NULL,
  `team2_ban3` int(11) DEFAULT NULL,
  `team2_ban4` int(11) DEFAULT NULL,
  `team2_ban5` int(11) DEFAULT NULL,
  `team1_pick1` int(11) DEFAULT NULL,
  `team1_pick2` int(11) DEFAULT NULL,
  `team1_pick3` int(11) DEFAULT NULL,
  `team1_pick4` int(11) DEFAULT NULL,
  `team1_pick5` int(11) DEFAULT NULL,
  `team2_pick1` int(11) DEFAULT NULL,
  `team2_pick2` int(11) DEFAULT NULL,
  `team2_pick3` int(11) DEFAULT NULL,
  `team2_pick4` int(11) DEFAULT NULL,
  `team2_pick5` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `games`
--

INSERT INTO `games` (`id`, `match_id`, `game_number`, `game_duration`, `game_result`, `team1_id`, `team2_id`, `winner_id`, `team1_ban1`, `team1_ban2`, `team1_ban3`, `team1_ban4`, `team1_ban5`, `team2_ban1`, `team2_ban2`, `team2_ban3`, `team2_ban4`, `team2_ban5`, `team1_pick1`, `team1_pick2`, `team1_pick3`, `team1_pick4`, `team1_pick5`, `team2_pick1`, `team2_pick2`, `team2_pick3`, `team2_pick4`, `team2_pick5`) VALUES
(4, 24, 1, 0, NULL, 10, 12, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(5, 23, 1, 0, NULL, 8, 9, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL);

-- --------------------------------------------------------

--
-- Table structure for table `heroes`
--

CREATE TABLE `heroes` (
  `id` int(11) NOT NULL,
  `hero_name` varchar(50) NOT NULL,
  `hero_role` varchar(30) NOT NULL,
  `hero_specialty` varchar(100) DEFAULT NULL,
  `hero_image` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `heroes`
--

INSERT INTO `heroes` (`id`, `hero_name`, `hero_role`, `hero_specialty`, `hero_image`) VALUES
(1, 'Tigreal', 'Tank', 'Crowd Control/Initiator', NULL),
(2, 'Franco', 'Tank', 'Poke/Initiator', NULL),
(3, 'Atlas', 'Tank', 'Crowd Control/Initiator', NULL),
(4, 'Johnson', 'Tank', 'Initiator/Global', NULL),
(5, 'Hylos', 'Tank', 'Regen/Durable', NULL),
(6, 'Grock', 'Tank', 'Damage/Poke', NULL),
(7, 'Belerick', 'Tank', 'Reflect/Durable', NULL),
(8, 'Minotaur', 'Tank', 'Heal/Crowd Control', NULL),
(9, 'Ling', 'Assassin', 'Burst/Mobility', NULL),
(10, 'Hayabusa', 'Assassin', 'Burst/Chase', NULL),
(11, 'Gusion', 'Assassin', 'Burst/Combo', NULL),
(12, 'Fanny', 'Assassin', 'Mobility/Burst', NULL),
(13, 'Lancelot', 'Assassin', 'Burst/Mobility', NULL),
(14, 'Karina', 'Assassin', 'Burst/Reset', NULL),
(15, 'Selena', 'Assassin', 'Poke/Burst', NULL),
(16, 'Natalia', 'Assassin', 'Stealth/Burst', NULL),
(17, 'Kagura', 'Mage', 'Poke/Mobility', NULL),
(18, 'Lunox', 'Mage', 'Burst/Sustain', NULL),
(19, 'Pharsa', 'Mage', 'Poke/Global', NULL),
(20, 'Eudora', 'Mage', 'Burst/Stun', NULL),
(21, 'Aurora', 'Mage', 'Crowd Control/Burst', NULL),
(22, 'Vale', 'Mage', 'Crowd Control/Poke', NULL),
(23, 'Valir', 'Mage', 'Poke/Disengage', NULL),
(24, 'Cecilion', 'Mage', 'Late Game/Scaling', NULL),
(25, 'Claude', 'Marksman', 'DPS/Mobility', NULL),
(26, 'Granger', 'Marksman', 'Burst/Mobility', NULL),
(27, 'Wanwan', 'Marksman', 'Mobility/True Damage', NULL),
(28, 'Karrie', 'Marksman', 'Tank Killer/DPS', NULL),
(29, 'Brody', 'Marksman', 'Burst/DPS', NULL),
(30, 'Beatrix', 'Marksman', 'Versatile/DPS', NULL),
(31, 'Popol and Kupa', 'Marksman', 'Poke/Vision', NULL),
(32, 'Lesley', 'Marksman', 'Burst/Stealth', NULL),
(33, 'Chou', 'Fighter', 'Crowd Control/Mobility', NULL),
(34, 'Paquito', 'Fighter', 'Burst/Mobility', NULL),
(35, 'Yu Zhong', 'Fighter', 'Sustain/DPS', NULL),
(36, 'X.Borg', 'Fighter', 'DPS/Burst', NULL),
(37, 'Thamuz', 'Fighter', 'Sustain/DPS', NULL),
(38, 'Alpha', 'Fighter', 'Burst/DPS', NULL),
(39, 'Bane', 'Fighter', 'Poke/DPS', NULL),
(40, 'Ruby', 'Fighter', 'Crowd Control/Lifesteal', NULL),
(41, 'Angela', 'Support', 'Heal/Shield/Global', NULL),
(42, 'Rafaela', 'Support', 'Heal/Movement Speed', NULL),
(43, 'Estes', 'Support', 'Heal/Regen', NULL),
(44, 'Mathilda', 'Support', 'Mobility/Shield', NULL),
(45, 'Carmilla', 'Support', 'Crowd Control/Link', NULL),
(46, 'Diggie', 'Support', 'Crowd Control/Anti-CC', NULL),
(47, 'Kaja', 'Support', 'Initiator/Suppression', NULL),
(48, 'Faramis', 'Support', 'Heal/Revive', NULL);

-- --------------------------------------------------------

--
-- Table structure for table `hero_stats`
--

CREATE TABLE `hero_stats` (
  `id` int(11) NOT NULL,
  `player_id` int(11) NOT NULL,
  `hero_name` varchar(50) NOT NULL,
  `matches` int(11) DEFAULT 0,
  `wins` int(11) DEFAULT 0,
  `kills` int(11) DEFAULT 0,
  `deaths` int(11) DEFAULT 0,
  `assists` int(11) DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `matches`
--

CREATE TABLE `matches` (
  `id` int(11) NOT NULL,
  `tournament_id` int(11) NOT NULL,
  `round` varchar(30) NOT NULL,
  `match_number` int(11) NOT NULL,
  `bracket_position` int(11) DEFAULT NULL,
  `team1_id` int(11) DEFAULT NULL,
  `team2_id` int(11) DEFAULT NULL,
  `winner_id` int(11) DEFAULT NULL,
  `team1_score` int(11) DEFAULT 0,
  `team2_score` int(11) DEFAULT 0,
  `best_of` int(11) DEFAULT 3,
  `schedule` datetime DEFAULT NULL,
  `actual_start` datetime DEFAULT NULL,
  `actual_end` datetime DEFAULT NULL,
  `status` varchar(20) DEFAULT 'Scheduled',
  `next_match_id` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `matches`
--

INSERT INTO `matches` (`id`, `tournament_id`, `round`, `match_number`, `bracket_position`, `team1_id`, `team2_id`, `winner_id`, `team1_score`, `team2_score`, `best_of`, `schedule`, `actual_start`, `actual_end`, `status`, `next_match_id`) VALUES
(23, 8, 'Winners Round 1', 1, 0, 8, 9, 9, 0, 1, 1, NULL, NULL, '2026-03-27 12:34:43', 'Completed', 0),
(24, 8, 'Winners Round 1', 2, 1, 10, 12, 12, 0, 1, 1, NULL, NULL, '2026-03-27 12:35:01', 'Completed', 0),
(25, 8, 'Winners Round 2', 3, 0, NULL, NULL, NULL, 0, 0, 1, NULL, NULL, NULL, 'Scheduled', NULL),
(26, 8, 'Winners Finals', 7, 0, NULL, NULL, NULL, 0, 0, 1, NULL, NULL, NULL, 'Scheduled', 0),
(27, 8, 'Losers Round 1', 4, 0, NULL, NULL, NULL, 0, 0, 1, NULL, NULL, NULL, 'Scheduled', 0),
(28, 8, 'Losers Round 1', 5, 1, NULL, NULL, NULL, 0, 0, 1, NULL, NULL, NULL, 'Scheduled', 0),
(29, 8, 'Losers Round 2', 6, 0, NULL, NULL, NULL, 0, 0, 1, NULL, NULL, NULL, 'Scheduled', NULL),
(30, 8, 'Losers Finals', 8, 0, NULL, NULL, NULL, 0, 0, 1, NULL, NULL, NULL, 'Scheduled', 0),
(31, 8, 'Grand Finals', 9, 0, NULL, NULL, NULL, 0, 0, 3, NULL, NULL, NULL, 'Scheduled', 0),
(32, 8, 'Grand Finals (Bracket Reset)', 10, 0, NULL, NULL, NULL, 0, 0, 3, NULL, NULL, NULL, 'Scheduled', NULL);

-- --------------------------------------------------------

--
-- Table structure for table `player_game_stats`
--

CREATE TABLE `player_game_stats` (
  `id` int(11) NOT NULL,
  `game_id` int(11) NOT NULL,
  `player_id` int(11) NOT NULL,
  `team_id` int(11) NOT NULL,
  `hero_id` int(11) DEFAULT NULL,
  `hero_name` varchar(50) DEFAULT NULL,
  `kills` int(11) DEFAULT 0,
  `deaths` int(11) DEFAULT 0,
  `assists` int(11) DEFAULT 0,
  `cs` int(11) DEFAULT 0,
  `gold` int(11) DEFAULT 0,
  `level` int(11) DEFAULT 1,
  `damage_dealt` int(11) DEFAULT 0,
  `damage_taken` int(11) DEFAULT 0,
  `healing_done` int(11) DEFAULT 0,
  `vision_score` int(11) DEFAULT 0,
  `is_mvp` tinyint(1) DEFAULT 0,
  `has_maniac` tinyint(1) DEFAULT 0,
  `has_savage` tinyint(1) DEFAULT 0,
  `triple_kills` int(11) DEFAULT 0,
  `quadra_kills` int(11) DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `player_game_stats`
--

INSERT INTO `player_game_stats` (`id`, `game_id`, `player_id`, `team_id`, `hero_id`, `hero_name`, `kills`, `deaths`, `assists`, `cs`, `gold`, `level`, `damage_dealt`, `damage_taken`, `healing_done`, `vision_score`, `is_mvp`, `has_maniac`, `has_savage`, `triple_kills`, `quadra_kills`) VALUES
(2, 4, 5, 12, NULL, 'Chou', 12, 0, 3, 0, 0, 0, 0, 0, 0, 0, 1, 0, 0, 0, 0);

-- --------------------------------------------------------

--
-- Table structure for table `player_stats`
--

CREATE TABLE `player_stats` (
  `player_id` int(11) NOT NULL,
  `total_matches` int(11) DEFAULT 0,
  `wins` int(11) DEFAULT 0,
  `losses` int(11) DEFAULT 0,
  `total_kills` int(11) DEFAULT 0,
  `total_deaths` int(11) DEFAULT 0,
  `total_assists` int(11) DEFAULT 0,
  `mvps` int(11) DEFAULT 0,
  `maniacs` int(11) DEFAULT 0,
  `savages` int(11) DEFAULT 0,
  `triple_kills` int(11) DEFAULT 0,
  `quadra_kills` int(11) DEFAULT 0,
  `current_win_streak` int(11) DEFAULT 0,
  `current_loss_streak` int(11) DEFAULT 0,
  `last_five_games` varchar(10) DEFAULT NULL,
  `last_ten_games` varchar(10) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `player_stats`
--

INSERT INTO `player_stats` (`player_id`, `total_matches`, `wins`, `losses`, `total_kills`, `total_deaths`, `total_assists`, `mvps`, `maniacs`, `savages`, `triple_kills`, `quadra_kills`, `current_win_streak`, `current_loss_streak`, `last_five_games`, `last_ten_games`) VALUES
(5, 5, 0, 5, 59, 0, 14, 0, 0, 0, 0, 0, 0, 5, 'LLLLL', NULL);

-- --------------------------------------------------------

--
-- Table structure for table `teams`
--

CREATE TABLE `teams` (
  `id` int(11) NOT NULL,
  `team_name` varchar(100) NOT NULL,
  `team_abbreviation` varchar(10) DEFAULT NULL,
  `team_logo` varchar(255) DEFAULT NULL,
  `team_rank` varchar(30) DEFAULT 'Unranked',
  `approval_status` varchar(20) DEFAULT 'Pending',
  `rejection_reason` varchar(255) DEFAULT NULL,
  `wins` int(11) DEFAULT 0,
  `losses` int(11) DEFAULT 0,
  `points` int(11) DEFAULT 0,
  `captain_id` int(11) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `teams`
--

INSERT INTO `teams` (`id`, `team_name`, `team_abbreviation`, `team_logo`, `team_rank`, `approval_status`, `rejection_reason`, `wins`, `losses`, `points`, `captain_id`, `created_at`) VALUES
(7, 'The Phoenix Army', 'PHX', NULL, 'Mythical Glory', 'Approved', NULL, 1, 0, 0, NULL, '2026-03-17 05:18:22'),
(8, 'Blacklist International', 'BLK', NULL, 'Mythical Glory', 'Approved', NULL, 0, 3, 0, 6, '2026-03-17 05:18:22'),
(9, 'ECHO Philippines', 'ECHO', NULL, 'Mythic', 'Approved', NULL, 2, 1, 0, NULL, '2026-03-17 05:18:22'),
(10, 'ONIC Philippines', 'ONIC', NULL, 'Mythical Glory', 'Approved', NULL, 1, 3, 0, NULL, '2026-03-17 05:18:22'),
(11, 'RSG Philippines', 'RSG', NULL, 'Mythic', 'Approved', NULL, 0, 2, 0, NULL, '2026-03-17 05:18:22'),
(12, 'Pastil', 'PSTL', NULL, 'Mythical Glory', 'Approved', NULL, 5, 0, 0, 3, '2026-03-17 08:28:29'),
(13, 'Tapsilog', 'TPSLG', NULL, 'Legend', 'Approved', NULL, 0, 0, 0, 7, '2026-03-28 05:07:57');

-- --------------------------------------------------------

--
-- Table structure for table `team_members`
--

CREATE TABLE `team_members` (
  `id` int(11) NOT NULL,
  `team_id` int(11) NOT NULL,
  `player_id` int(11) NOT NULL,
  `team_role` varchar(30) DEFAULT NULL,
  `is_starter` tinyint(1) DEFAULT 0,
  `joined_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `team_members`
--

INSERT INTO `team_members` (`id`, `team_id`, `player_id`, `team_role`, `is_starter`, `joined_at`) VALUES
(7, 12, 1, 'Jungle', 0, '2026-03-28 05:11:01');

-- --------------------------------------------------------

--
-- Table structure for table `tournaments`
--

CREATE TABLE `tournaments` (
  `id` int(11) NOT NULL,
  `tournament_name` varchar(100) NOT NULL,
  `tournament_type` varchar(30) NOT NULL,
  `tournament_format` varchar(20) NOT NULL,
  `ml_version` varchar(20) DEFAULT NULL,
  `ban_format` varchar(20) DEFAULT NULL,
  `match_duration` int(11) DEFAULT NULL,
  `max_teams` int(11) DEFAULT NULL,
  `registered_teams` int(11) DEFAULT 0,
  `min_rank` varchar(30) DEFAULT NULL,
  `prize_pool` decimal(10,2) DEFAULT NULL,
  `start_date` date DEFAULT NULL,
  `end_date` date DEFAULT NULL,
  `status` varchar(20) DEFAULT 'Upcoming',
  `created_by` int(11) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `tournaments`
--

INSERT INTO `tournaments` (`id`, `tournament_name`, `tournament_type`, `tournament_format`, `ml_version`, `ban_format`, `match_duration`, `max_teams`, `registered_teams`, `min_rank`, `prize_pool`, `start_date`, `end_date`, `status`, `created_by`, `created_at`) VALUES
(8, 'asdasd', 'Double Elimination', 'Bo1', 'Patch 1.9.20', '10 bans (5-5)', 60, 4, 4, NULL, 500000.00, '2026-03-22', '2026-03-23', 'Ongoing', 2, '2026-03-22 02:06:08');

-- --------------------------------------------------------

--
-- Table structure for table `tournament_teams`
--

CREATE TABLE `tournament_teams` (
  `id` int(11) NOT NULL,
  `tournament_id` int(11) NOT NULL,
  `team_id` int(11) NOT NULL,
  `seed_number` int(11) DEFAULT NULL,
  `group_name` varchar(10) DEFAULT NULL,
  `status` varchar(20) DEFAULT 'Registered',
  `registered_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `tournament_teams`
--

INSERT INTO `tournament_teams` (`id`, `tournament_id`, `team_id`, `seed_number`, `group_name`, `status`, `registered_at`) VALUES
(45, 8, 8, NULL, NULL, 'Registered', '2026-03-22 02:06:26'),
(46, 8, 9, NULL, NULL, 'Registered', '2026-03-22 02:06:30'),
(47, 8, 10, NULL, NULL, 'Registered', '2026-03-22 02:06:30'),
(48, 8, 12, NULL, NULL, 'Registered', '2026-03-22 02:06:31');

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` int(11) NOT NULL,
  `username` varchar(50) NOT NULL,
  `password` varchar(50) NOT NULL,
  `email` varchar(100) NOT NULL,
  `contact` varchar(20) NOT NULL,
  `gender` varchar(10) NOT NULL,
  `role` varchar(20) NOT NULL,
  `is_active` tinyint(1) DEFAULT 1,
  `last_login` datetime DEFAULT NULL,
  `full_name` varchar(100) NOT NULL,
  `ml_account_id` varchar(50) DEFAULT NULL,
  `ml_ign` varchar(100) DEFAULT NULL,
  `ml_server_id` varchar(20) DEFAULT NULL,
  `ml_rank` varchar(30) DEFAULT NULL,
  `ml_main_role` varchar(30) DEFAULT NULL,
  `ml_main_hero` varchar(100) DEFAULT NULL,
  `team_role` varchar(30) DEFAULT NULL,
  `team_id` int(11) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `reset_token` varchar(255) DEFAULT NULL,
  `reset_token_expiry` datetime DEFAULT NULL,
  `password_changed_at` datetime DEFAULT NULL,
  `email_verified` tinyint(1) DEFAULT 0,
  `verification_token` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `username`, `password`, `email`, `contact`, `gender`, `role`, `is_active`, `last_login`, `full_name`, `ml_account_id`, `ml_ign`, `ml_server_id`, `ml_rank`, `ml_main_role`, `ml_main_hero`, `team_role`, `team_id`, `created_at`, `reset_token`, `reset_token_expiry`, `password_changed_at`, `email_verified`, `verification_token`) VALUES
(1, 'player1', 'player123', 'player@email.com', '09123456789', 'Male', 'player', 1, '2026-03-26 09:44:25', 'Juan Dela Cruz', NULL, 'ProPlayer', NULL, 'Epic', NULL, NULL, NULL, 12, '2026-03-16 08:14:53', NULL, NULL, NULL, 0, NULL),
(2, 'admin', 'admin123', 'longboy.jomari@ncst.edu.ph', '09123456789', 'Male', 'admin', 1, '2026-05-11 16:33:19', 'System Administrator', '598563214', 'Admin', '100337', 'Mythic', 'Assassin', 'Layla', NULL, 0, '2026-03-17 03:35:58', '317425909f864ac5831d759ee697a328', '2026-03-22 13:20:32', '2026-03-28 13:13:09', 0, NULL),
(3, 'hatdog', 'hatdog123', 'cheese@gmail.com', '09874561230', 'Male', 'captain', 1, '2026-03-28 13:10:18', 'hatdog cheese', NULL, 'asdads', NULL, 'Grandmaster', 'Assassin', 'Bane', NULL, 12, '2026-03-17 07:56:08', NULL, NULL, NULL, 0, NULL),
(5, 'player2', 'asdasd', 'player2@gmail.com', '096523148984', 'Male', 'player', 1, '2026-03-28 13:11:30', 'bimbimbambam', '0325415878', 'Abdol', '20331', 'Legend', 'Fighter', 'Chou', NULL, NULL, '2026-03-17 09:10:51', NULL, NULL, NULL, 0, NULL),
(6, 'mamamo', 'asdasd', 'mamamo.blue@gmail.com', '09874454', 'Male', 'captain', 1, '2026-03-26 09:14:38', 'mama mo blue', '1231233632', 'mamamo', '100032', 'Warrior', 'Tank', 'Alpha', NULL, 8, '2026-03-26 01:13:59', NULL, NULL, NULL, 0, NULL),
(7, 'player', 'asdasd', 'asdasd@gmail.com', '0968722263', 'Male', 'captain', 1, '2026-03-28 13:01:03', 'mamamo blue', '3168553', 'mamamoblue', '103232', 'Legend', 'Assassin', 'Gusion', NULL, 13, '2026-03-28 05:00:28', NULL, NULL, NULL, 0, NULL);

--
-- Indexes for dumped tables
--

--
-- Indexes for table `games`
--
ALTER TABLE `games`
  ADD PRIMARY KEY (`id`),
  ADD KEY `match_id` (`match_id`),
  ADD KEY `team1_id` (`team1_id`),
  ADD KEY `team2_id` (`team2_id`),
  ADD KEY `winner_id` (`winner_id`);

--
-- Indexes for table `heroes`
--
ALTER TABLE `heroes`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `unique_hero` (`hero_name`);

--
-- Indexes for table `hero_stats`
--
ALTER TABLE `hero_stats`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `unique_player_hero` (`player_id`,`hero_name`);

--
-- Indexes for table `matches`
--
ALTER TABLE `matches`
  ADD PRIMARY KEY (`id`),
  ADD KEY `tournament_id` (`tournament_id`),
  ADD KEY `team1_id` (`team1_id`),
  ADD KEY `team2_id` (`team2_id`),
  ADD KEY `winner_id` (`winner_id`);

--
-- Indexes for table `player_game_stats`
--
ALTER TABLE `player_game_stats`
  ADD PRIMARY KEY (`id`),
  ADD KEY `game_id` (`game_id`),
  ADD KEY `player_id` (`player_id`),
  ADD KEY `team_id` (`team_id`),
  ADD KEY `hero_id` (`hero_id`);

--
-- Indexes for table `player_stats`
--
ALTER TABLE `player_stats`
  ADD PRIMARY KEY (`player_id`);

--
-- Indexes for table `teams`
--
ALTER TABLE `teams`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `team_name` (`team_name`),
  ADD UNIQUE KEY `team_abbreviation` (`team_abbreviation`),
  ADD UNIQUE KEY `captain_id` (`captain_id`),
  ADD KEY `idx_approval_status` (`approval_status`);

--
-- Indexes for table `team_members`
--
ALTER TABLE `team_members`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `player_id` (`player_id`),
  ADD KEY `team_id` (`team_id`);

--
-- Indexes for table `tournaments`
--
ALTER TABLE `tournaments`
  ADD PRIMARY KEY (`id`),
  ADD KEY `created_by` (`created_by`);

--
-- Indexes for table `tournament_teams`
--
ALTER TABLE `tournament_teams`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `unique_tournament_team` (`tournament_id`,`team_id`),
  ADD KEY `team_id` (`team_id`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `username` (`username`),
  ADD UNIQUE KEY `unique_ml_account` (`ml_account_id`,`ml_server_id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `games`
--
ALTER TABLE `games`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT for table `heroes`
--
ALTER TABLE `heroes`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=49;

--
-- AUTO_INCREMENT for table `hero_stats`
--
ALTER TABLE `hero_stats`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `matches`
--
ALTER TABLE `matches`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=39;

--
-- AUTO_INCREMENT for table `player_game_stats`
--
ALTER TABLE `player_game_stats`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `teams`
--
ALTER TABLE `teams`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=14;

--
-- AUTO_INCREMENT for table `team_members`
--
ALTER TABLE `team_members`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- AUTO_INCREMENT for table `tournaments`
--
ALTER TABLE `tournaments`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

--
-- AUTO_INCREMENT for table `tournament_teams`
--
ALTER TABLE `tournament_teams`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=51;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `games`
--
ALTER TABLE `games`
  ADD CONSTRAINT `games_ibfk_1` FOREIGN KEY (`match_id`) REFERENCES `matches` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `games_ibfk_2` FOREIGN KEY (`team1_id`) REFERENCES `teams` (`id`),
  ADD CONSTRAINT `games_ibfk_3` FOREIGN KEY (`team2_id`) REFERENCES `teams` (`id`),
  ADD CONSTRAINT `games_ibfk_4` FOREIGN KEY (`winner_id`) REFERENCES `teams` (`id`);

--
-- Constraints for table `hero_stats`
--
ALTER TABLE `hero_stats`
  ADD CONSTRAINT `hero_stats_ibfk_1` FOREIGN KEY (`player_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `matches`
--
ALTER TABLE `matches`
  ADD CONSTRAINT `matches_ibfk_1` FOREIGN KEY (`tournament_id`) REFERENCES `tournaments` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `matches_ibfk_2` FOREIGN KEY (`team1_id`) REFERENCES `teams` (`id`),
  ADD CONSTRAINT `matches_ibfk_3` FOREIGN KEY (`team2_id`) REFERENCES `teams` (`id`),
  ADD CONSTRAINT `matches_ibfk_4` FOREIGN KEY (`winner_id`) REFERENCES `teams` (`id`);

--
-- Constraints for table `player_game_stats`
--
ALTER TABLE `player_game_stats`
  ADD CONSTRAINT `player_game_stats_ibfk_1` FOREIGN KEY (`game_id`) REFERENCES `games` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `player_game_stats_ibfk_2` FOREIGN KEY (`player_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `player_game_stats_ibfk_3` FOREIGN KEY (`team_id`) REFERENCES `teams` (`id`),
  ADD CONSTRAINT `player_game_stats_ibfk_4` FOREIGN KEY (`hero_id`) REFERENCES `heroes` (`id`);

--
-- Constraints for table `player_stats`
--
ALTER TABLE `player_stats`
  ADD CONSTRAINT `player_stats_ibfk_1` FOREIGN KEY (`player_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `teams`
--
ALTER TABLE `teams`
  ADD CONSTRAINT `teams_ibfk_1` FOREIGN KEY (`captain_id`) REFERENCES `users` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `team_members`
--
ALTER TABLE `team_members`
  ADD CONSTRAINT `team_members_ibfk_1` FOREIGN KEY (`team_id`) REFERENCES `teams` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `team_members_ibfk_2` FOREIGN KEY (`player_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `tournaments`
--
ALTER TABLE `tournaments`
  ADD CONSTRAINT `tournaments_ibfk_1` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`);

--
-- Constraints for table `tournament_teams`
--
ALTER TABLE `tournament_teams`
  ADD CONSTRAINT `tournament_teams_ibfk_1` FOREIGN KEY (`tournament_id`) REFERENCES `tournaments` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `tournament_teams_ibfk_2` FOREIGN KEY (`team_id`) REFERENCES `teams` (`id`) ON DELETE CASCADE;
--
-- Database: `phpmyadmin`
--
CREATE DATABASE IF NOT EXISTS `phpmyadmin` DEFAULT CHARACTER SET utf8 COLLATE utf8_bin;
USE `phpmyadmin`;

-- --------------------------------------------------------

--
-- Table structure for table `pma__bookmark`
--

CREATE TABLE `pma__bookmark` (
  `id` int(10) UNSIGNED NOT NULL,
  `dbase` varchar(255) NOT NULL DEFAULT '',
  `user` varchar(255) NOT NULL DEFAULT '',
  `label` varchar(255) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL DEFAULT '',
  `query` text NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_bin COMMENT='Bookmarks';

-- --------------------------------------------------------

--
-- Table structure for table `pma__central_columns`
--

CREATE TABLE `pma__central_columns` (
  `db_name` varchar(64) NOT NULL,
  `col_name` varchar(64) NOT NULL,
  `col_type` varchar(64) NOT NULL,
  `col_length` text DEFAULT NULL,
  `col_collation` varchar(64) NOT NULL,
  `col_isNull` tinyint(1) NOT NULL,
  `col_extra` varchar(255) DEFAULT '',
  `col_default` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_bin COMMENT='Central list of columns';

-- --------------------------------------------------------

--
-- Table structure for table `pma__column_info`
--

CREATE TABLE `pma__column_info` (
  `id` int(5) UNSIGNED NOT NULL,
  `db_name` varchar(64) NOT NULL DEFAULT '',
  `table_name` varchar(64) NOT NULL DEFAULT '',
  `column_name` varchar(64) NOT NULL DEFAULT '',
  `comment` varchar(255) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL DEFAULT '',
  `mimetype` varchar(255) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL DEFAULT '',
  `transformation` varchar(255) NOT NULL DEFAULT '',
  `transformation_options` varchar(255) NOT NULL DEFAULT '',
  `input_transformation` varchar(255) NOT NULL DEFAULT '',
  `input_transformation_options` varchar(255) NOT NULL DEFAULT ''
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_bin COMMENT='Column information for phpMyAdmin';

-- --------------------------------------------------------

--
-- Table structure for table `pma__designer_settings`
--

CREATE TABLE `pma__designer_settings` (
  `username` varchar(64) NOT NULL,
  `settings_data` text NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_bin COMMENT='Settings related to Designer';

-- --------------------------------------------------------

--
-- Table structure for table `pma__export_templates`
--

CREATE TABLE `pma__export_templates` (
  `id` int(5) UNSIGNED NOT NULL,
  `username` varchar(64) NOT NULL,
  `export_type` varchar(10) NOT NULL,
  `template_name` varchar(64) NOT NULL,
  `template_data` text NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_bin COMMENT='Saved export templates';

-- --------------------------------------------------------

--
-- Table structure for table `pma__favorite`
--

CREATE TABLE `pma__favorite` (
  `username` varchar(64) NOT NULL,
  `tables` text NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_bin COMMENT='Favorite tables';

-- --------------------------------------------------------

--
-- Table structure for table `pma__history`
--

CREATE TABLE `pma__history` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `username` varchar(64) NOT NULL DEFAULT '',
  `db` varchar(64) NOT NULL DEFAULT '',
  `table` varchar(64) NOT NULL DEFAULT '',
  `timevalue` timestamp NOT NULL DEFAULT current_timestamp(),
  `sqlquery` text NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_bin COMMENT='SQL history for phpMyAdmin';

-- --------------------------------------------------------

--
-- Table structure for table `pma__navigationhiding`
--

CREATE TABLE `pma__navigationhiding` (
  `username` varchar(64) NOT NULL,
  `item_name` varchar(64) NOT NULL,
  `item_type` varchar(64) NOT NULL,
  `db_name` varchar(64) NOT NULL,
  `table_name` varchar(64) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_bin COMMENT='Hidden items of navigation tree';

-- --------------------------------------------------------

--
-- Table structure for table `pma__pdf_pages`
--

CREATE TABLE `pma__pdf_pages` (
  `db_name` varchar(64) NOT NULL DEFAULT '',
  `page_nr` int(10) UNSIGNED NOT NULL,
  `page_descr` varchar(50) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL DEFAULT ''
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_bin COMMENT='PDF relation pages for phpMyAdmin';

-- --------------------------------------------------------

--
-- Table structure for table `pma__recent`
--

CREATE TABLE `pma__recent` (
  `username` varchar(64) NOT NULL,
  `tables` text NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_bin COMMENT='Recently accessed tables';

--
-- Dumping data for table `pma__recent`
--

INSERT INTO `pma__recent` (`username`, `tables`) VALUES
('root', '[{\"db\":\"cyberarena_ml\",\"table\":\"users\"},{\"db\":\"cyberarena_ml\",\"table\":\"team_members\"},{\"db\":\"cyberarena_ml\",\"table\":\"tournaments\"}]');

-- --------------------------------------------------------

--
-- Table structure for table `pma__relation`
--

CREATE TABLE `pma__relation` (
  `master_db` varchar(64) NOT NULL DEFAULT '',
  `master_table` varchar(64) NOT NULL DEFAULT '',
  `master_field` varchar(64) NOT NULL DEFAULT '',
  `foreign_db` varchar(64) NOT NULL DEFAULT '',
  `foreign_table` varchar(64) NOT NULL DEFAULT '',
  `foreign_field` varchar(64) NOT NULL DEFAULT ''
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_bin COMMENT='Relation table';

-- --------------------------------------------------------

--
-- Table structure for table `pma__savedsearches`
--

CREATE TABLE `pma__savedsearches` (
  `id` int(5) UNSIGNED NOT NULL,
  `username` varchar(64) NOT NULL DEFAULT '',
  `db_name` varchar(64) NOT NULL DEFAULT '',
  `search_name` varchar(64) NOT NULL DEFAULT '',
  `search_data` text NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_bin COMMENT='Saved searches';

-- --------------------------------------------------------

--
-- Table structure for table `pma__table_coords`
--

CREATE TABLE `pma__table_coords` (
  `db_name` varchar(64) NOT NULL DEFAULT '',
  `table_name` varchar(64) NOT NULL DEFAULT '',
  `pdf_page_number` int(11) NOT NULL DEFAULT 0,
  `x` float UNSIGNED NOT NULL DEFAULT 0,
  `y` float UNSIGNED NOT NULL DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_bin COMMENT='Table coordinates for phpMyAdmin PDF output';

-- --------------------------------------------------------

--
-- Table structure for table `pma__table_info`
--

CREATE TABLE `pma__table_info` (
  `db_name` varchar(64) NOT NULL DEFAULT '',
  `table_name` varchar(64) NOT NULL DEFAULT '',
  `display_field` varchar(64) NOT NULL DEFAULT ''
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_bin COMMENT='Table information for phpMyAdmin';

-- --------------------------------------------------------

--
-- Table structure for table `pma__table_uiprefs`
--

CREATE TABLE `pma__table_uiprefs` (
  `username` varchar(64) NOT NULL,
  `db_name` varchar(64) NOT NULL,
  `table_name` varchar(64) NOT NULL,
  `prefs` text NOT NULL,
  `last_update` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_bin COMMENT='Tables'' UI preferences';

-- --------------------------------------------------------

--
-- Table structure for table `pma__tracking`
--

CREATE TABLE `pma__tracking` (
  `db_name` varchar(64) NOT NULL,
  `table_name` varchar(64) NOT NULL,
  `version` int(10) UNSIGNED NOT NULL,
  `date_created` datetime NOT NULL,
  `date_updated` datetime NOT NULL,
  `schema_snapshot` text NOT NULL,
  `schema_sql` text DEFAULT NULL,
  `data_sql` longtext DEFAULT NULL,
  `tracking` set('UPDATE','REPLACE','INSERT','DELETE','TRUNCATE','CREATE DATABASE','ALTER DATABASE','DROP DATABASE','CREATE TABLE','ALTER TABLE','RENAME TABLE','DROP TABLE','CREATE INDEX','DROP INDEX','CREATE VIEW','ALTER VIEW','DROP VIEW') DEFAULT NULL,
  `tracking_active` int(1) UNSIGNED NOT NULL DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_bin COMMENT='Database changes tracking for phpMyAdmin';

-- --------------------------------------------------------

--
-- Table structure for table `pma__userconfig`
--

CREATE TABLE `pma__userconfig` (
  `username` varchar(64) NOT NULL,
  `timevalue` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `config_data` text NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_bin COMMENT='User preferences storage for phpMyAdmin';

--
-- Dumping data for table `pma__userconfig`
--

INSERT INTO `pma__userconfig` (`username`, `timevalue`, `config_data`) VALUES
('root', '2026-09-07 00:32:01', '{\"Console\\/Mode\":\"collapse\"}');

-- --------------------------------------------------------

--
-- Table structure for table `pma__usergroups`
--

CREATE TABLE `pma__usergroups` (
  `usergroup` varchar(64) NOT NULL,
  `tab` varchar(64) NOT NULL,
  `allowed` enum('Y','N') NOT NULL DEFAULT 'N'
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_bin COMMENT='User groups with configured menu items';

-- --------------------------------------------------------

--
-- Table structure for table `pma__users`
--

CREATE TABLE `pma__users` (
  `username` varchar(64) NOT NULL,
  `usergroup` varchar(64) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_bin COMMENT='Users and their assignments to user groups';

--
-- Indexes for dumped tables
--

--
-- Indexes for table `pma__bookmark`
--
ALTER TABLE `pma__bookmark`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `pma__central_columns`
--
ALTER TABLE `pma__central_columns`
  ADD PRIMARY KEY (`db_name`,`col_name`);

--
-- Indexes for table `pma__column_info`
--
ALTER TABLE `pma__column_info`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `db_name` (`db_name`,`table_name`,`column_name`);

--
-- Indexes for table `pma__designer_settings`
--
ALTER TABLE `pma__designer_settings`
  ADD PRIMARY KEY (`username`);

--
-- Indexes for table `pma__export_templates`
--
ALTER TABLE `pma__export_templates`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `u_user_type_template` (`username`,`export_type`,`template_name`);

--
-- Indexes for table `pma__favorite`
--
ALTER TABLE `pma__favorite`
  ADD PRIMARY KEY (`username`);

--
-- Indexes for table `pma__history`
--
ALTER TABLE `pma__history`
  ADD PRIMARY KEY (`id`),
  ADD KEY `username` (`username`,`db`,`table`,`timevalue`);

--
-- Indexes for table `pma__navigationhiding`
--
ALTER TABLE `pma__navigationhiding`
  ADD PRIMARY KEY (`username`,`item_name`,`item_type`,`db_name`,`table_name`);

--
-- Indexes for table `pma__pdf_pages`
--
ALTER TABLE `pma__pdf_pages`
  ADD PRIMARY KEY (`page_nr`),
  ADD KEY `db_name` (`db_name`);

--
-- Indexes for table `pma__recent`
--
ALTER TABLE `pma__recent`
  ADD PRIMARY KEY (`username`);

--
-- Indexes for table `pma__relation`
--
ALTER TABLE `pma__relation`
  ADD PRIMARY KEY (`master_db`,`master_table`,`master_field`),
  ADD KEY `foreign_field` (`foreign_db`,`foreign_table`);

--
-- Indexes for table `pma__savedsearches`
--
ALTER TABLE `pma__savedsearches`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `u_savedsearches_username_dbname` (`username`,`db_name`,`search_name`);

--
-- Indexes for table `pma__table_coords`
--
ALTER TABLE `pma__table_coords`
  ADD PRIMARY KEY (`db_name`,`table_name`,`pdf_page_number`);

--
-- Indexes for table `pma__table_info`
--
ALTER TABLE `pma__table_info`
  ADD PRIMARY KEY (`db_name`,`table_name`);

--
-- Indexes for table `pma__table_uiprefs`
--
ALTER TABLE `pma__table_uiprefs`
  ADD PRIMARY KEY (`username`,`db_name`,`table_name`);

--
-- Indexes for table `pma__tracking`
--
ALTER TABLE `pma__tracking`
  ADD PRIMARY KEY (`db_name`,`table_name`,`version`);

--
-- Indexes for table `pma__userconfig`
--
ALTER TABLE `pma__userconfig`
  ADD PRIMARY KEY (`username`);

--
-- Indexes for table `pma__usergroups`
--
ALTER TABLE `pma__usergroups`
  ADD PRIMARY KEY (`usergroup`,`tab`,`allowed`);

--
-- Indexes for table `pma__users`
--
ALTER TABLE `pma__users`
  ADD PRIMARY KEY (`username`,`usergroup`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `pma__bookmark`
--
ALTER TABLE `pma__bookmark`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `pma__column_info`
--
ALTER TABLE `pma__column_info`
  MODIFY `id` int(5) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `pma__export_templates`
--
ALTER TABLE `pma__export_templates`
  MODIFY `id` int(5) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `pma__history`
--
ALTER TABLE `pma__history`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `pma__pdf_pages`
--
ALTER TABLE `pma__pdf_pages`
  MODIFY `page_nr` int(10) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `pma__savedsearches`
--
ALTER TABLE `pma__savedsearches`
  MODIFY `id` int(5) UNSIGNED NOT NULL AUTO_INCREMENT;
--
-- Database: `pos_system`
--
CREATE DATABASE IF NOT EXISTS `pos_system` DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci;
USE `pos_system`;

-- --------------------------------------------------------

--
-- Table structure for table `activity_logs`
--

CREATE TABLE `activity_logs` (
  `id` int(11) NOT NULL,
  `user_id` int(11) DEFAULT NULL,
  `action` text DEFAULT NULL,
  `ip_address` varchar(45) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `activity_logs`
--

INSERT INTO `activity_logs` (`id`, `user_id`, `action`, `ip_address`, `created_at`) VALUES
(1, 1, 'Access denied: users.php - User: admin', '::1', '2026-08-31 05:12:22'),
(2, 1, 'Access denied: roles.php - User: admin', '::1', '2026-08-31 05:12:23'),
(3, 1, 'Access denied: roles.php - User: admin', '::1', '2026-08-31 05:12:24'),
(4, 1, 'Access denied: users.php - User: admin', '::1', '2026-08-31 05:12:24'),
(5, 1, 'Access denied: roles.php - User: admin', '::1', '2026-08-31 05:12:25'),
(6, 1, 'Access denied: users.php - User: admin', '::1', '2026-08-31 05:12:48'),
(7, 1, 'Access denied: roles.php - User: admin', '::1', '2026-08-31 05:12:49'),
(8, 1, 'Access denied: users.php - User: admin', '::1', '2026-08-31 05:14:31'),
(9, 1, 'Access denied: roles.php - User: admin', '::1', '2026-08-31 05:14:32'),
(10, 1, 'Access denied: users.php - User: admin', '::1', '2026-08-31 05:14:33'),
(11, 1, 'Access denied: users.php - User: admin', '::1', '2026-08-31 05:18:31'),
(12, 1, 'Access denied: roles.php - User: admin', '::1', '2026-08-31 05:18:32'),
(13, 1, 'User logged in: admin', '::1', '2026-08-31 05:20:11'),
(14, 1, 'Access denied: employees.php - User: admin', '::1', '2026-08-31 06:12:11'),
(15, 1, 'User logged in: admin', '::1', '2026-08-31 06:16:05'),
(19, 1, 'User logged in: admin', '::1', '2026-08-31 06:27:54'),
(20, 1, 'Access denied: leaves.php - User: admin', '::1', '2026-08-31 06:28:02'),
(21, 1, 'Updated permissions for role ID: 3', '::1', '2026-08-31 06:29:18'),
(36, 1, 'User logged in: admin', '::1', '2026-08-31 06:29:59'),
(37, 1, 'Updated permissions for role ID: 5', '::1', '2026-08-31 06:31:13'),
(38, 1, 'Updated permissions for role ID: 3', '::1', '2026-08-31 06:32:22'),
(54, 1, 'User logged in: admin', '::1', '2026-08-31 06:33:01'),
(55, 1, 'Access denied: employees.php - User: admin', '::1', '2026-08-31 06:33:30'),
(56, 1, 'Access denied: leaves.php - User: admin', '::1', '2026-08-31 06:33:30'),
(57, 1, 'User logged in: admin', '::1', '2026-08-31 10:48:44'),
(58, 1, 'Deleted user: testuser (ID: 3) with all related records', '::1', '2026-08-31 10:53:06'),
(59, 1, 'Deleted user: user1 (ID: 5) with all related records', '::1', '2026-08-31 10:53:14'),
(60, 1, 'Access denied: employees.php - User: admin', '::1', '2026-08-31 11:08:17'),
(61, 1, 'Access denied: employees.php - User: admin', '::1', '2026-08-31 11:08:26'),
(62, 1, 'Access denied: employees.php - User: admin', '::1', '2026-08-31 11:08:31'),
(63, 1, 'Access denied: leaves.php - User: admin', '::1', '2026-08-31 11:08:32'),
(64, 1, 'Access denied: employees.php - User: admin', '::1', '2026-08-31 11:08:32'),
(65, 1, 'Access denied: leaves.php - User: admin', '::1', '2026-08-31 11:08:34'),
(66, 1, 'Access denied: employees.php - User: admin', '::1', '2026-08-31 11:08:35'),
(67, 1, 'Access denied: employees.php - User: admin', '::1', '2026-08-31 11:08:43'),
(68, 1, 'Access denied: leaves.php - User: admin', '::1', '2026-08-31 11:08:43'),
(69, 1, 'Access denied: employees.php - User: admin', '::1', '2026-08-31 11:08:44'),
(70, 1, 'Access denied: employees.php - User: admin', '::1', '2026-08-31 11:10:07'),
(71, 1, 'Access denied: leaves.php - User: admin', '::1', '2026-08-31 11:10:08'),
(72, 1, 'User logged in: admin', '::1', '2026-08-31 11:10:49'),
(73, 1, 'Access denied: employees.php - User: admin', '::1', '2026-08-31 11:10:52'),
(74, 1, 'Access denied: employees.php - User: admin', '::1', '2026-08-31 11:10:53'),
(75, 1, 'Deleted user: user (ID: 6) with all related records', '::1', '2026-08-31 11:25:04'),
(76, 1, 'Created user: user with employee record', '::1', '2026-08-31 11:26:22'),
(77, 7, 'User logged in: user', '::1', '2026-08-31 11:33:15'),
(78, 7, 'Access denied: sales.php - User: user', '::1', '2026-08-31 11:33:18'),
(79, 7, 'Access denied: sales.php - User: user', '::1', '2026-08-31 11:33:19'),
(80, 7, 'Access denied: sales.php - User: user', '::1', '2026-08-31 11:33:19'),
(81, 7, 'Access denied: sales.php - User: user', '::1', '2026-08-31 11:33:24'),
(82, 7, 'Access denied: sales.php - User: user', '::1', '2026-08-31 11:33:26'),
(83, 7, 'Access denied: sales.php - User: user', '::1', '2026-08-31 11:33:26'),
(84, 7, 'Access denied: sales.php - User: user', '::1', '2026-08-31 11:33:26'),
(85, 7, 'Access denied: sales.php - User: user', '::1', '2026-08-31 11:33:26'),
(86, 7, 'Access denied: sales.php - User: user', '::1', '2026-08-31 11:33:27'),
(87, 1, 'User logged in: admin', '::1', '2026-08-31 11:33:31'),
(88, 1, 'Updated user ID: 7 and employee record', '::1', '2026-08-31 11:35:50'),
(89, 7, 'User logged in: user', '::1', '2026-08-31 11:36:01'),
(90, 7, 'Recorded attendance for user ID: 7 - Status: present', '::1', '2026-08-31 11:36:14'),
(91, 1, 'User logged in: admin', '::1', '2026-08-31 11:36:57'),
(92, 1, 'Updated permissions for role ID: 2', '::1', '2026-08-31 11:37:22'),
(93, 1, 'User logged in: admin', '::1', '2026-08-31 11:37:28'),
(94, 7, 'User logged in: user', '::1', '2026-08-31 11:37:39'),
(95, 7, 'Access denied: roles.php - User: user', '::1', '2026-08-31 11:37:43'),
(96, 7, 'Access denied: roles.php - User: user', '::1', '2026-08-31 11:37:44'),
(97, 7, 'Access denied: roles.php - User: user', '::1', '2026-08-31 11:37:45'),
(98, 7, 'Access denied: roles.php - User: user', '::1', '2026-08-31 11:37:45'),
(99, 7, 'Access denied: roles.php - User: user', '::1', '2026-08-31 11:37:55'),
(100, 1, 'User logged in: admin', '::1', '2026-08-31 11:38:12'),
(101, 1, 'Budget approved for requisition ID: 1', '::1', '2026-08-31 12:40:29'),
(102, 1, 'Budget rejectd for requisition ID: 2', '::1', '2026-08-31 12:54:19'),
(103, 1, 'User logged in: admin', '::1', '2026-08-31 13:15:08'),
(104, 1, 'Created requisition #REQ-20260831-7481', '::1', '2026-08-31 13:20:11'),
(105, 1, 'Created requisition #REQ-20260831-1717', '::1', '2026-08-31 13:20:51'),
(106, 1, 'Created requisition #REQ-20260831-6982', '::1', '2026-08-31 13:23:15'),
(107, 1, 'Created requisition #REQ-20260831-4037', '::1', '2026-08-31 13:23:40'),
(108, 1, 'Created requisition #REQ-20260831-5770', '::1', '2026-08-31 13:25:28'),
(109, 1, 'Created requisition #REQ-20260831-5447', '::1', '2026-08-31 13:26:33'),
(110, 1, 'Added supplier: Evo Logistic Hub', '::1', '2026-08-31 13:31:31'),
(111, 1, 'Created RFQ #RFQ-20260831-7611', '::1', '2026-08-31 13:35:30'),
(112, 1, 'Added supplier: Evo Logistic Hub', '::1', '2026-08-31 13:36:13'),
(113, 1, 'Added quotation #QUOT-20260831-4618', '::1', '2026-08-31 13:53:13'),
(114, 1, 'Evaluated quotation ID: 1', '::1', '2026-08-31 13:53:53'),
(115, 1, 'Evaluated quotation ID: 1', '::1', '2026-08-31 13:54:01'),
(116, 1, 'User logged in: admin', '::1', '2026-08-31 14:02:06'),
(117, 1, 'Created PO #PO-20260831-7268', '::1', '2026-08-31 14:03:04'),
(118, 1, 'PO #1 approved by finance', '::1', '2026-08-31 14:04:16'),
(119, 1, 'Added quotation #QUOT-20260831-6393', '::1', '2026-08-31 14:18:55'),
(120, 1, 'User logged in: admin', '::1', '2026-08-31 14:23:24'),
(121, 1, 'User logged in: admin', '::1', '2026-08-31 14:23:47'),
(122, 1, 'Created invoice #INV-2026-001 - Match: mismatch', '::1', '2026-08-31 14:26:38'),
(123, 1, 'User logged in: admin', '::1', '2026-09-01 01:13:46'),
(124, 1, 'Added quotation #QUOT-20260901-3194', '::1', '2026-09-01 01:14:58'),
(125, 1, 'Evaluated quotation ID: 3 - Status: accepted', '::1', '2026-09-01 01:15:56'),
(126, 1, 'Added quotation #QUOT-20260901-2832', '::1', '2026-09-01 01:16:57'),
(127, 1, 'Evaluated quotation ID: 4 - Status: accepted', '::1', '2026-09-01 01:17:04'),
(128, 1, 'Created PO #PO-20260901-7115', '::1', '2026-09-01 01:17:39'),
(129, 1, 'PO #2 approved by finance', '::1', '2026-09-01 01:17:54'),
(130, 1, 'Created PO #PO-20260901-1271', '::1', '2026-09-01 01:36:57'),
(131, 1, 'PO #3 approved by finance', '::1', '2026-09-01 01:37:01'),
(132, 1, 'Received goods for PO #3 - GRN #GRN-20260901-6342', '::1', '2026-09-01 01:37:12'),
(133, 1, 'Created invoice #INV-2026-002 - Match: mismatch', '::1', '2026-09-01 01:38:25'),
(134, 1, 'Force matched invoice #3', '::1', '2026-09-01 01:44:41'),
(135, 1, 'Created invoice #INV-2026-003 - Match: mismatch', '::1', '2026-09-01 01:45:07'),
(136, 1, 'Marked invoice #3 as paid', '::1', '2026-09-01 01:45:29'),
(137, 1, 'Force matched invoice #7', '::1', '2026-09-01 01:50:33'),
(138, 1, 'PO #2 closed after payment', '::1', '2026-09-01 01:57:19'),
(139, 1, 'Processed payment #PAY-20260901-4716', '::1', '2026-09-01 01:57:19'),
(140, 1, 'Force matched invoice #1', '::1', '2026-09-01 01:57:31'),
(141, 1, 'User logged in: admin', '::1', '2026-09-01 01:58:12'),
(142, 1, 'PO #1 closed after payment', '::1', '2026-09-01 02:01:28'),
(143, 1, 'Processed payment #PAY-20260901-8327', '::1', '2026-09-01 02:01:28'),
(144, 1, 'Created budget #BUD-2026-001 - ₱1,000,000.00', '::1', '2026-09-01 02:06:08'),
(145, 1, 'Added expense #EXP-20260901-3529 - ₱14,200.00', '::1', '2026-09-01 02:06:43'),
(146, 1, 'User logged in: admin', '::1', '2026-10-13 10:14:46'),
(147, 1, 'User logged out', '::1', '2026-10-13 10:16:46'),
(148, 1, 'User logged in: admin', '::1', '2026-10-13 10:17:56'),
(149, 1, 'Supplier portal: created account \'sup004\' for Evo Logistic Hub', '::1', '2026-10-13 10:25:52'),
(150, 8, 'User logged in: sup004', '::1', '2026-10-13 10:29:50'),
(151, 8, 'User logged in: sup004', '::1', '2026-10-13 10:31:10'),
(152, 8, 'User logged out', '::1', '2026-10-13 10:41:43');

-- --------------------------------------------------------

--
-- Table structure for table `attendance`
--

CREATE TABLE `attendance` (
  `id` int(11) NOT NULL,
  `user_id` int(11) DEFAULT NULL,
  `date` date DEFAULT curdate(),
  `time_in` time DEFAULT NULL,
  `time_out` time DEFAULT NULL,
  `status` enum('present','absent','late','half-day') DEFAULT 'present',
  `notes` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `attendance`
--

INSERT INTO `attendance` (`id`, `user_id`, `date`, `time_in`, `time_out`, `status`, `notes`) VALUES
(1, 7, '2026-08-31', '13:36:14', NULL, 'present', 'hatdog');

-- --------------------------------------------------------

--
-- Table structure for table `backup_logs`
--

CREATE TABLE `backup_logs` (
  `id` int(11) NOT NULL,
  `backup_file` varchar(255) DEFAULT NULL,
  `backup_size` int(11) DEFAULT NULL,
  `created_by` int(11) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `budgets`
--

CREATE TABLE `budgets` (
  `id` int(11) NOT NULL,
  `budget_code` varchar(50) NOT NULL,
  `department` varchar(50) DEFAULT NULL,
  `category` varchar(50) DEFAULT NULL,
  `allocated_amount` decimal(12,2) DEFAULT NULL,
  `used_amount` decimal(12,2) DEFAULT 0.00,
  `fiscal_year` year(4) DEFAULT NULL,
  `status` enum('pending_approval','active','closed','rejected') NOT NULL DEFAULT 'pending_approval',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `created_by` int(11) DEFAULT NULL,
  `approved_by` int(11) DEFAULT NULL,
  `approved_at` datetime DEFAULT NULL,
  `rejection_reason` varchar(500) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `budgets`
--

INSERT INTO `budgets` (`id`, `budget_code`, `department`, `category`, `allocated_amount`, `used_amount`, `fiscal_year`, `status`, `created_at`, `created_by`, `approved_by`, `approved_at`, `rejection_reason`) VALUES
(1, 'BUD-2026-001', 'IT', 'Equipment', 1000000.00, 0.00, '2026', 'active', '2026-09-01 02:06:08', NULL, NULL, NULL, NULL);

-- --------------------------------------------------------

--
-- Table structure for table `customers`
--

CREATE TABLE `customers` (
  `id` int(11) NOT NULL,
  `customer_code` varchar(50) NOT NULL,
  `first_name` varchar(50) NOT NULL,
  `last_name` varchar(50) NOT NULL,
  `email` varchar(100) DEFAULT NULL,
  `phone` varchar(20) DEFAULT NULL,
  `address` text DEFAULT NULL,
  `company` varchar(100) DEFAULT NULL,
  `tax_id` varchar(50) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `email_logs`
--

CREATE TABLE `email_logs` (
  `id` int(11) NOT NULL,
  `recipient` varchar(150) NOT NULL,
  `subject` varchar(200) NOT NULL,
  `body` mediumtext DEFAULT NULL,
  `event_type` varchar(40) NOT NULL,
  `ref_table` varchar(40) DEFAULT NULL,
  `ref_id` int(11) DEFAULT NULL,
  `status` enum('pending','sent','failed') NOT NULL DEFAULT 'pending',
  `error_message` varchar(255) DEFAULT NULL,
  `attempts` int(11) NOT NULL DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `last_attempt_at` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `email_logs`
--

INSERT INTO `email_logs` (`id`, `recipient`, `subject`, `body`, `event_type`, `ref_table`, `ref_id`, `status`, `error_message`, `attempts`, `created_at`, `last_attempt_at`) VALUES
(1, 'lachicamiguel051@gmail.com', 'Your supplier portal account is ready', '<div style=\"font-family:Arial,sans-serif;max-width:560px\"><h2>Your supplier portal account is ready</h2><p>Hello Miguel Lachica, a supplier portal account was created for Evo Logistic Hub.</p><p>Your username is sup004. Use the link below within 72 hours to choose your password.</p><p><a href=\"http://localhost/pos-system/set_password.php?token=4074e28ba74ead1f79f7bb519c93484d23ee756285dc1cd9350f5afac53eff4c\">Set my password</a> (you will be asked to log in)</p><p style=\"color:#888;font-size:12px\">Automated message. It never contains passwords.</p></div>', 'general', NULL, NULL, 'sent', NULL, 1, '2026-10-13 10:25:52', '2026-10-13 18:25:52');

-- --------------------------------------------------------

--
-- Table structure for table `employees`
--

CREATE TABLE `employees` (
  `id` int(11) NOT NULL,
  `employee_id` varchar(50) NOT NULL,
  `first_name` varchar(50) NOT NULL,
  `last_name` varchar(50) NOT NULL,
  `middle_name` varchar(50) DEFAULT NULL,
  `position` varchar(50) DEFAULT NULL,
  `department` varchar(50) DEFAULT NULL,
  `email` varchar(100) DEFAULT NULL,
  `phone` varchar(20) DEFAULT NULL,
  `address` text DEFAULT NULL,
  `hire_date` date DEFAULT NULL,
  `salary` decimal(10,2) DEFAULT NULL,
  `status` enum('active','on_leave','terminated') DEFAULT 'active',
  `termination_reason` varchar(500) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `user_id` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `employees`
--

INSERT INTO `employees` (`id`, `employee_id`, `first_name`, `last_name`, `middle_name`, `position`, `department`, `email`, `phone`, `address`, `hire_date`, `salary`, `status`, `termination_reason`, `created_at`, `user_id`) VALUES
(1, 'EMP-20260831-6065', 'Marry Juana Shabu', '', NULL, 'Staff', 'Sales', 'marryjuana7@gmail.com', '09874665458', 'asdasdasdasd st.', '2007-01-01', 2000.00, 'active', NULL, '2026-08-31 11:26:22', 7);

-- --------------------------------------------------------

--
-- Table structure for table `employee_recurring_items`
--

CREATE TABLE `employee_recurring_items` (
  `id` int(11) NOT NULL,
  `employee_id` int(11) NOT NULL,
  `kind` enum('earning','deduction') NOT NULL,
  `item_name` varchar(80) NOT NULL,
  `amount` decimal(12,2) NOT NULL,
  `effective_from` date NOT NULL,
  `effective_to` date DEFAULT NULL,
  `documentation` varchar(255) DEFAULT NULL,
  `is_active` tinyint(1) NOT NULL DEFAULT 1,
  `created_by` int(11) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `employee_salary`
--

CREATE TABLE `employee_salary` (
  `id` int(11) NOT NULL,
  `employee_id` int(11) NOT NULL,
  `salary_type` enum('monthly','daily','hourly') NOT NULL DEFAULT 'monthly',
  `basic_salary` decimal(12,2) NOT NULL,
  `pay_schedule` enum('weekly','semi_monthly','monthly') NOT NULL DEFAULT 'semi_monthly',
  `effective_from` date NOT NULL,
  `applies_sss` tinyint(1) NOT NULL DEFAULT 1,
  `applies_philhealth` tinyint(1) NOT NULL DEFAULT 1,
  `applies_pagibig` tinyint(1) NOT NULL DEFAULT 1,
  `applies_tax` tinyint(1) NOT NULL DEFAULT 1,
  `ot_eligible` tinyint(1) NOT NULL DEFAULT 1,
  `notes` varchar(255) DEFAULT NULL,
  `created_by` int(11) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `expenses`
--

CREATE TABLE `expenses` (
  `id` int(11) NOT NULL,
  `expense_number` varchar(50) NOT NULL,
  `category` varchar(50) DEFAULT NULL,
  `description` text DEFAULT NULL,
  `amount` decimal(10,2) DEFAULT NULL,
  `expense_date` date DEFAULT NULL,
  `payment_method` varchar(50) DEFAULT NULL,
  `receipt_file` varchar(255) DEFAULT NULL,
  `created_by` int(11) DEFAULT NULL,
  `status` enum('pending','approved','rejected') DEFAULT 'pending',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `expenses`
--

INSERT INTO `expenses` (`id`, `expense_number`, `category`, `description`, `amount`, `expense_date`, `payment_method`, `receipt_file`, `created_by`, `status`, `created_at`) VALUES
(1, 'EXP-20260901-3529', 'Salaries', 'shaod', 14200.00, '2026-09-01', 'Cash', NULL, 1, 'approved', '2026-09-01 02:06:43');

-- --------------------------------------------------------

--
-- Table structure for table `goods_receipts`
--

CREATE TABLE `goods_receipts` (
  `id` int(11) NOT NULL,
  `grn_number` varchar(50) NOT NULL,
  `po_id` int(11) DEFAULT NULL,
  `received_by` int(11) DEFAULT NULL,
  `receipt_date` date DEFAULT curdate(),
  `delivery_note` varchar(50) DEFAULT NULL,
  `status` enum('pending','completed','rejected') DEFAULT 'pending',
  `notes` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `goods_receipts`
--

INSERT INTO `goods_receipts` (`id`, `grn_number`, `po_id`, `received_by`, `receipt_date`, `delivery_note`, `status`, `notes`, `created_at`) VALUES
(1, 'GRN-TEST-20260831-8605', 1, 1, '2026-08-31', NULL, 'completed', NULL, '2026-08-31 14:16:04'),
(2, 'GRN-FINAL-20260901-1071', 2, 1, '2026-09-01', NULL, 'completed', NULL, '2026-09-01 01:23:45'),
(3, 'GRN-20260901-6342', 3, 1, '2026-09-01', '1234545', 'completed', 'aaaaaa', '2026-09-01 01:37:12');

-- --------------------------------------------------------

--
-- Table structure for table `gr_items`
--

CREATE TABLE `gr_items` (
  `id` int(11) NOT NULL,
  `goods_receipt_id` int(11) DEFAULT NULL,
  `po_item_id` int(11) DEFAULT NULL,
  `received_quantity` int(11) DEFAULT NULL,
  `accepted_quantity` int(11) DEFAULT NULL,
  `rejected_quantity` int(11) DEFAULT NULL,
  `remarks` text DEFAULT NULL,
  `damaged_quantity` int(11) NOT NULL DEFAULT 0,
  `missing_quantity` int(11) NOT NULL DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `gr_items`
--

INSERT INTO `gr_items` (`id`, `goods_receipt_id`, `po_item_id`, `received_quantity`, `accepted_quantity`, `rejected_quantity`, `remarks`, `damaged_quantity`, `missing_quantity`) VALUES
(1, 1, 1, 5, 5, 0, NULL, 0, 0),
(2, 2, 2, 5, 5, 0, NULL, 0, 0),
(3, 3, 3, 5, 5, 0, 'sdds', 0, 0);

-- --------------------------------------------------------

--
-- Table structure for table `invoices`
--

CREATE TABLE `invoices` (
  `id` int(11) NOT NULL,
  `invoice_number` varchar(50) NOT NULL,
  `po_id` int(11) DEFAULT NULL,
  `supplier_id` int(11) DEFAULT NULL,
  `invoice_date` date DEFAULT NULL,
  `total_amount` decimal(12,2) DEFAULT NULL,
  `tax_amount` decimal(10,2) DEFAULT 0.00,
  `net_amount` decimal(12,2) DEFAULT NULL,
  `status` enum('received','matched','pending_payment','paid','rejected') DEFAULT 'received',
  `match_status` enum('pending','matched','mismatch') DEFAULT 'pending',
  `received_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `paid_at` timestamp NULL DEFAULT NULL,
  `notes` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `invoices`
--

INSERT INTO `invoices` (`id`, `invoice_number`, `po_id`, `supplier_id`, `invoice_date`, `total_amount`, `tax_amount`, `net_amount`, `status`, `match_status`, `received_at`, `paid_at`, `notes`) VALUES
(1, 'INV-2026-001', 1, 5, '2026-08-31', 145654.00, 145.00, 145799.00, 'paid', 'matched', '2026-08-31 14:26:38', '2026-09-01 02:01:28', 'sadfsdfsdf | Force matched: '),
(3, 'INV-2026-002', 3, 1, '2026-09-01', 14011234.00, 10000.00, 14021234.00, 'paid', 'matched', '2026-09-01 01:38:25', '2026-09-01 01:45:29', 'asdasd | Force matched: '),
(7, 'INV-2026-003', 2, 1, '2026-09-01', 123369.00, 123213.00, 246582.00, 'paid', 'matched', '2026-09-01 01:45:07', '2026-09-01 01:57:19', 'ASDASD | Force matched: ');

-- --------------------------------------------------------

--
-- Table structure for table `job_applications`
--

CREATE TABLE `job_applications` (
  `id` int(11) NOT NULL,
  `job_id` int(11) NOT NULL,
  `applicant_name` varchar(150) NOT NULL,
  `email` varchar(150) NOT NULL,
  `phone` varchar(30) DEFAULT NULL,
  `cover_letter` text DEFAULT NULL,
  `resume_path` varchar(255) NOT NULL,
  `status` enum('pending','shortlisted','interview','rejected','hired') NOT NULL DEFAULT 'pending',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NULL DEFAULT NULL ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `job_postings`
--

CREATE TABLE `job_postings` (
  `id` int(11) NOT NULL,
  `title` varchar(150) NOT NULL,
  `department` varchar(100) DEFAULT NULL,
  `employment_type` varchar(50) NOT NULL DEFAULT 'Full-time',
  `location` varchar(150) DEFAULT NULL,
  `description` text DEFAULT NULL,
  `requirements` text DEFAULT NULL,
  `status` enum('draft','published','closed') NOT NULL DEFAULT 'draft',
  `created_by` int(11) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NULL DEFAULT NULL ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `job_postings`
--

INSERT INTO `job_postings` (`id`, `title`, `department`, `employment_type`, `location`, `description`, `requirements`, `status`, `created_by`, `created_at`, `updated_at`) VALUES
(1, 'dsaasdsda', 'Finance', 'Part-time', 'Imus', 'asdasd', 'asdasd', 'published', 1, '2026-10-13 10:20:21', NULL);

-- --------------------------------------------------------

--
-- Table structure for table `leaves`
--

CREATE TABLE `leaves` (
  `id` int(11) NOT NULL,
  `employee_id` int(11) DEFAULT NULL,
  `leave_type` enum('sick','vacation','emergency','maternity','paternity','bereavement') DEFAULT NULL,
  `start_date` date DEFAULT NULL,
  `end_date` date DEFAULT NULL,
  `reason` text DEFAULT NULL,
  `status` enum('pending','approved','rejected') DEFAULT 'pending',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `notifications`
--

CREATE TABLE `notifications` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `title` varchar(150) NOT NULL,
  `message` varchar(500) NOT NULL,
  `link` varchar(255) DEFAULT NULL,
  `is_read` tinyint(1) NOT NULL DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `password_tokens`
--

CREATE TABLE `password_tokens` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `token_hash` char(64) NOT NULL,
  `purpose` enum('activate','reset') NOT NULL DEFAULT 'activate',
  `expires_at` datetime NOT NULL,
  `used_at` datetime DEFAULT NULL,
  `created_by` int(11) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `password_tokens`
--

INSERT INTO `password_tokens` (`id`, `user_id`, `token_hash`, `purpose`, `expires_at`, `used_at`, `created_by`, `created_at`) VALUES
(1, 8, '07470588e8884e4dc24de402bac976e5fab0b935e31441d419025c734d919055', 'activate', '2026-10-16 18:25:48', '2026-10-13 18:29:44', 1, '2026-10-13 10:25:48');

-- --------------------------------------------------------

--
-- Table structure for table `payments`
--

CREATE TABLE `payments` (
  `id` int(11) NOT NULL,
  `payment_number` varchar(50) NOT NULL,
  `invoice_id` int(11) DEFAULT NULL,
  `amount` decimal(12,2) DEFAULT NULL,
  `payment_date` date DEFAULT NULL,
  `payment_method` enum('bank_transfer','cheque','virtual_card','cash') DEFAULT 'bank_transfer',
  `reference_number` varchar(100) DEFAULT NULL,
  `notes` text DEFAULT NULL,
  `status` enum('scheduled','processing','completed','failed') DEFAULT 'scheduled',
  `processed_by` int(11) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `payments`
--

INSERT INTO `payments` (`id`, `payment_number`, `invoice_id`, `amount`, `payment_date`, `payment_method`, `reference_number`, `notes`, `status`, `processed_by`, `created_at`) VALUES
(1, 'PAY-20260901-4716', 7, 246582.00, '2026-09-01', 'cash', '', NULL, 'completed', 1, '2026-09-01 01:57:19'),
(2, 'PAY-20260901-8327', 1, 145799.00, '2026-09-01', 'cash', '', NULL, 'completed', 1, '2026-09-01 02:01:28');

-- --------------------------------------------------------

--
-- Table structure for table `payroll_adjustments`
--

CREATE TABLE `payroll_adjustments` (
  `id` int(11) NOT NULL,
  `run_id` int(11) NOT NULL,
  `item_id` int(11) NOT NULL,
  `adj_type` enum('earning','deduction') NOT NULL,
  `description` varchar(150) NOT NULL,
  `amount` decimal(14,2) NOT NULL,
  `reason` varchar(500) NOT NULL,
  `status` enum('pending','approved','rejected') NOT NULL DEFAULT 'pending',
  `requested_by` int(11) DEFAULT NULL,
  `requested_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `decided_by` int(11) DEFAULT NULL,
  `decided_at` datetime DEFAULT NULL,
  `decision_note` varchar(500) DEFAULT NULL,
  `net_before` decimal(14,2) DEFAULT NULL,
  `net_after` decimal(14,2) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `payroll_approvals`
--

CREATE TABLE `payroll_approvals` (
  `id` int(11) NOT NULL,
  `run_id` int(11) NOT NULL,
  `action` varchar(40) NOT NULL,
  `from_status` varchar(30) DEFAULT NULL,
  `to_status` varchar(30) DEFAULT NULL,
  `user_id` int(11) DEFAULT NULL,
  `remarks` varchar(500) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `payroll_deductions`
--

CREATE TABLE `payroll_deductions` (
  `id` int(11) NOT NULL,
  `item_id` int(11) NOT NULL,
  `deduction_type` varchar(40) NOT NULL,
  `description` varchar(150) NOT NULL,
  `amount` decimal(14,2) NOT NULL,
  `source` enum('auto','manual','adjustment') NOT NULL DEFAULT 'auto',
  `adjustment_id` int(11) DEFAULT NULL,
  `created_by` int(11) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `payroll_earnings`
--

CREATE TABLE `payroll_earnings` (
  `id` int(11) NOT NULL,
  `item_id` int(11) NOT NULL,
  `earning_type` varchar(40) NOT NULL,
  `description` varchar(150) NOT NULL,
  `amount` decimal(14,2) NOT NULL,
  `source` enum('auto','manual','adjustment') NOT NULL DEFAULT 'auto',
  `adjustment_id` int(11) DEFAULT NULL,
  `created_by` int(11) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `payroll_items`
--

CREATE TABLE `payroll_items` (
  `id` int(11) NOT NULL,
  `run_id` int(11) NOT NULL,
  `employee_id` int(11) NOT NULL,
  `salary_id` int(11) DEFAULT NULL,
  `salary_type` enum('monthly','daily','hourly') NOT NULL DEFAULT 'monthly',
  `basic_salary` decimal(12,2) NOT NULL DEFAULT 0.00,
  `monthly_basic` decimal(14,2) NOT NULL DEFAULT 0.00,
  `daily_equiv` decimal(14,4) NOT NULL DEFAULT 0.0000,
  `hourly_equiv` decimal(14,4) NOT NULL DEFAULT 0.0000,
  `scheduled_days` decimal(6,2) NOT NULL DEFAULT 0.00,
  `days_present` decimal(6,2) NOT NULL DEFAULT 0.00,
  `paid_leave_days` decimal(6,2) NOT NULL DEFAULT 0.00,
  `unpaid_leave_days` decimal(6,2) NOT NULL DEFAULT 0.00,
  `absent_days` decimal(6,2) NOT NULL DEFAULT 0.00,
  `late_minutes` int(11) NOT NULL DEFAULT 0,
  `undertime_minutes` int(11) NOT NULL DEFAULT 0,
  `ot_hours` decimal(7,2) NOT NULL DEFAULT 0.00,
  `detected_json` text DEFAULT NULL,
  `issues` text DEFAULT NULL,
  `validated` tinyint(1) NOT NULL DEFAULT 0,
  `validated_by` int(11) DEFAULT NULL,
  `validated_at` datetime DEFAULT NULL,
  `validation_note` varchar(255) DEFAULT NULL,
  `basic_pay` decimal(14,2) NOT NULL DEFAULT 0.00,
  `gross_pay` decimal(14,2) NOT NULL DEFAULT 0.00,
  `total_deductions` decimal(14,2) NOT NULL DEFAULT 0.00,
  `net_pay` decimal(14,2) NOT NULL DEFAULT 0.00,
  `calculated_at` datetime DEFAULT NULL,
  `amount_paid` decimal(14,2) NOT NULL DEFAULT 0.00,
  `payment_status` enum('unpaid','partial','paid') NOT NULL DEFAULT 'unpaid',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `payroll_payments`
--

CREATE TABLE `payroll_payments` (
  `id` int(11) NOT NULL,
  `run_id` int(11) NOT NULL,
  `item_id` int(11) NOT NULL,
  `amount` decimal(14,2) NOT NULL,
  `payment_method` enum('cash','bank_transfer','ewallet','cheque','other') NOT NULL DEFAULT 'bank_transfer',
  `reference_no` varchar(80) DEFAULT NULL,
  `paid_date` date NOT NULL,
  `notes` varchar(255) DEFAULT NULL,
  `idem_key` varchar(80) DEFAULT NULL,
  `recorded_by` int(11) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `payroll_periods`
--

CREATE TABLE `payroll_periods` (
  `id` int(11) NOT NULL,
  `period_name` varchar(100) NOT NULL,
  `start_date` date NOT NULL,
  `end_date` date NOT NULL,
  `pay_date` date NOT NULL,
  `pay_schedule` enum('weekly','semi_monthly','monthly') NOT NULL DEFAULT 'semi_monthly',
  `deadline_date` date DEFAULT NULL,
  `notes` varchar(255) DEFAULT NULL,
  `created_by` int(11) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `payroll_rules`
--

CREATE TABLE `payroll_rules` (
  `id` int(11) NOT NULL,
  `rule_type` enum('sss','philhealth','pagibig','tax') NOT NULL,
  `label` varchar(100) NOT NULL,
  `effective_from` date NOT NULL,
  `effective_to` date DEFAULT NULL,
  `range_from` decimal(14,2) NOT NULL DEFAULT 0.00,
  `range_to` decimal(14,2) DEFAULT NULL,
  `rate_percent` decimal(8,4) NOT NULL DEFAULT 0.0000,
  `fixed_amount` decimal(14,2) NOT NULL DEFAULT 0.00,
  `min_base` decimal(14,2) DEFAULT NULL,
  `max_base` decimal(14,2) DEFAULT NULL,
  `notes` varchar(255) DEFAULT NULL,
  `is_active` tinyint(1) NOT NULL DEFAULT 1,
  `created_by` int(11) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `payroll_rules`
--

INSERT INTO `payroll_rules` (`id`, `rule_type`, `label`, `effective_from`, `effective_to`, `range_from`, `range_to`, `rate_percent`, `fixed_amount`, `min_base`, `max_base`, `notes`, `is_active`, `created_by`, `created_at`) VALUES
(1, 'sss', 'SSS employee share (sample)', '2025-01-01', NULL, 0.00, NULL, 5.0000, 0.00, 5000.00, 35000.00, 'SAMPLE VALUE for demo - verify against the current official table before real use', 1, NULL, '2026-10-13 10:14:47'),
(2, 'philhealth', 'PhilHealth employee share (sample)', '2025-01-01', NULL, 0.00, NULL, 2.5000, 0.00, 10000.00, 100000.00, 'SAMPLE VALUE for demo - verify against the current official table before real use', 1, NULL, '2026-10-13 10:14:47'),
(3, 'pagibig', 'Pag-IBIG employee share (sample)', '2025-01-01', NULL, 0.00, NULL, 2.0000, 0.00, NULL, 10000.00, 'SAMPLE VALUE for demo - verify against the current official table before real use', 1, NULL, '2026-10-13 10:14:47'),
(4, 'tax', 'Withholding tax - monthly bracket (sample)', '2025-01-01', NULL, 0.00, 20833.33, 0.0000, 0.00, NULL, NULL, 'SAMPLE VALUE for demo - verify against the current official table before real use', 1, NULL, '2026-10-13 10:14:47'),
(5, 'tax', 'Withholding tax - monthly bracket (sample)', '2025-01-01', NULL, 20833.33, 33333.33, 15.0000, 0.00, NULL, NULL, 'SAMPLE VALUE for demo - verify against the current official table before real use', 1, NULL, '2026-10-13 10:14:47'),
(6, 'tax', 'Withholding tax - monthly bracket (sample)', '2025-01-01', NULL, 33333.33, 66666.67, 20.0000, 1875.00, NULL, NULL, 'SAMPLE VALUE for demo - verify against the current official table before real use', 1, NULL, '2026-10-13 10:14:47'),
(7, 'tax', 'Withholding tax - monthly bracket (sample)', '2025-01-01', NULL, 66666.67, 166666.67, 25.0000, 8541.67, NULL, NULL, 'SAMPLE VALUE for demo - verify against the current official table before real use', 1, NULL, '2026-10-13 10:14:47'),
(8, 'tax', 'Withholding tax - monthly bracket (sample)', '2025-01-01', NULL, 166666.67, 666666.67, 30.0000, 33541.67, NULL, NULL, 'SAMPLE VALUE for demo - verify against the current official table before real use', 1, NULL, '2026-10-13 10:14:47'),
(9, 'tax', 'Withholding tax - monthly bracket (sample)', '2025-01-01', NULL, 666666.67, NULL, 35.0000, 183541.67, NULL, NULL, 'SAMPLE VALUE for demo - verify against the current official table before real use', 1, NULL, '2026-10-13 10:14:47');

-- --------------------------------------------------------

--
-- Table structure for table `payroll_runs`
--

CREATE TABLE `payroll_runs` (
  `id` int(11) NOT NULL,
  `period_id` int(11) NOT NULL,
  `status` enum('draft','for_review','for_approval','approved','finalized','partially_paid','paid','cancelled') NOT NULL DEFAULT 'draft',
  `prepared_by` int(11) DEFAULT NULL,
  `submitted_at` datetime DEFAULT NULL,
  `reviewed_by` int(11) DEFAULT NULL,
  `reviewed_at` datetime DEFAULT NULL,
  `review_note` varchar(500) DEFAULT NULL,
  `approved_by` int(11) DEFAULT NULL,
  `approved_at` datetime DEFAULT NULL,
  `approval_note` varchar(500) DEFAULT NULL,
  `finalized_by` int(11) DEFAULT NULL,
  `finalized_at` datetime DEFAULT NULL,
  `cancelled_by` int(11) DEFAULT NULL,
  `cancelled_at` datetime DEFAULT NULL,
  `cancel_reason` varchar(500) DEFAULT NULL,
  `total_gross` decimal(14,2) NOT NULL DEFAULT 0.00,
  `total_deductions` decimal(14,2) NOT NULL DEFAULT 0.00,
  `total_net` decimal(14,2) NOT NULL DEFAULT 0.00,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `payroll_settings`
--

CREATE TABLE `payroll_settings` (
  `setting_key` varchar(60) NOT NULL,
  `setting_value` varchar(255) NOT NULL DEFAULT '',
  `updated_by` int(11) DEFAULT NULL,
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `payroll_settings`
--

INSERT INTO `payroll_settings` (`setting_key`, `setting_value`, `updated_by`, `updated_at`) VALUES
('company_address', '', NULL, '2026-10-13 10:14:47'),
('company_name', 'NEXCORE', NULL, '2026-10-13 10:14:47'),
('days_per_month_factor', '26.08', NULL, '2026-10-13 10:14:47'),
('grace_minutes', '5', NULL, '2026-10-13 10:14:47'),
('notify_adjustment_approved', '1', NULL, '2026-10-13 10:14:47'),
('notify_finalized', '1', NULL, '2026-10-13 10:14:47'),
('notify_payment_recorded', '1', NULL, '2026-10-13 10:14:47'),
('notify_payslip_available', '1', NULL, '2026-10-13 10:14:47'),
('ot_multiplier', '1.25', NULL, '2026-10-13 10:14:47'),
('shift_end', '18:00', NULL, '2026-10-13 10:14:47'),
('shift_start', '09:00', NULL, '2026-10-13 10:14:47'),
('standard_hours_per_day', '8', NULL, '2026-10-13 10:14:47'),
('unpaid_leave_types', '', NULL, '2026-10-13 10:14:47'),
('working_weekdays', '1,2,3,4,5,6', NULL, '2026-10-13 10:14:47');

-- --------------------------------------------------------

--
-- Table structure for table `payslips`
--

CREATE TABLE `payslips` (
  `id` int(11) NOT NULL,
  `item_id` int(11) NOT NULL,
  `run_id` int(11) NOT NULL,
  `employee_id` int(11) NOT NULL,
  `reference_no` varchar(40) NOT NULL,
  `revision` int(11) NOT NULL DEFAULT 0,
  `generated_by` int(11) DEFAULT NULL,
  `generated_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `email_status` enum('pending','sent','failed','skipped') NOT NULL DEFAULT 'pending',
  `emailed_at` datetime DEFAULT NULL,
  `last_viewed_at` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `permissions`
--

CREATE TABLE `permissions` (
  `id` int(11) NOT NULL,
  `permission_name` varchar(100) NOT NULL,
  `module` varchar(50) DEFAULT NULL,
  `description` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `permissions`
--

INSERT INTO `permissions` (`id`, `permission_name`, `module`, `description`) VALUES
(1, 'view_users', 'users', NULL),
(2, 'create_users', 'users', NULL),
(3, 'edit_users', 'users', NULL),
(4, 'delete_users', 'users', NULL),
(5, 'view_roles', 'roles', NULL),
(6, 'manage_roles', 'roles', NULL),
(7, 'view_attendance', 'attendance', NULL),
(8, 'manage_attendance', 'attendance', NULL),
(9, 'view_products', 'products', NULL),
(10, 'manage_products', 'products', NULL),
(11, 'process_sales', 'sales', NULL),
(12, 'view_sales', 'sales', NULL),
(13, 'view_reports', 'reports', NULL),
(14, 'view_requisitions', 'procurement', NULL),
(15, 'create_requisitions', 'procurement', NULL),
(16, 'approve_requisitions', 'procurement', NULL),
(17, 'view_rfqs', 'procurement', NULL),
(18, 'manage_rfqs', 'procurement', NULL),
(19, 'view_suppliers', 'procurement', NULL),
(20, 'manage_suppliers', 'procurement', NULL),
(21, 'view_pos', 'procurement', NULL),
(22, 'manage_pos', 'procurement', NULL),
(23, 'view_invoices', 'procurement', NULL),
(24, 'manage_payments', 'procurement', NULL),
(25, 'view_performance', 'procurement', NULL),
(26, 'view_returns', 'sales', NULL),
(27, 'manage_returns', 'sales', NULL),
(28, 'view_expenses', 'finance', NULL),
(29, 'manage_expenses', 'finance', NULL),
(30, 'view_budget', 'finance', NULL),
(31, 'manage_budget', 'finance', NULL),
(34, 'view_user_accounts', 'hrm', 'View employee user accounts'),
(35, 'manage_user_accounts', 'hrm', 'Manage user accounts'),
(36, 'archive_users', 'hrm', 'Archive and restore accounts'),
(37, 'permanently_delete_users', 'hrm', 'Permanently delete archived accounts'),
(38, 'adjust_inventory', 'inventory', 'Authorized stock adjustments'),
(39, 'receive_goods', 'procurement', 'Receive goods into inventory'),
(40, 'approve_purchase_orders', 'procurement', 'Approve purchase orders'),
(41, 'manage_invoices', 'procurement', 'Manage supplier invoices'),
(42, 'approve_user_account_actions', 'hrm', 'Approve or reject account deactivation requests'),
(43, 'approve_expenses', 'finance', 'Approve or reject expense requests'),
(44, 'approve_budgets', 'finance', 'Approve or reject budget requests'),
(45, 'manage_job_postings', 'hrm', 'Create and manage job postings'),
(46, 'view_job_applications', 'hrm', 'View job applications'),
(47, 'approve_stock_adjustments', 'inventory', 'Approve or reject stock adjustment requests'),
(48, 'view_payroll', 'payroll', 'View payroll periods, runs, payslips and payroll history'),
(49, 'manage_payroll_setup', 'payroll', 'Manage employee salary setup and recurring allowances/deductions'),
(50, 'process_payroll', 'payroll', 'Create payroll periods/runs, validate records, calculate and submit payroll'),
(51, 'review_payroll', 'payroll', 'Review submitted payroll runs'),
(52, 'approve_payroll', 'payroll', 'Approve and finalize payroll runs and corrections'),
(53, 'record_payroll_payments', 'payroll', 'Record payroll payments'),
(54, 'configure_payroll', 'payroll', 'Configure contribution/tax rules and payroll settings'),
(55, 'access_supplier_portal', 'supplier_portal', 'Use the supplier portal (suppliers only)'),
(56, 'manage_supplier_accounts', 'procurement', 'Create and manage supplier portal accounts');

-- --------------------------------------------------------

--
-- Table structure for table `po_change_requests`
--

CREATE TABLE `po_change_requests` (
  `id` int(11) NOT NULL,
  `po_id` int(11) NOT NULL,
  `supplier_id` int(11) NOT NULL,
  `requested_by` int(11) DEFAULT NULL,
  `new_delivery_date` date DEFAULT NULL,
  `new_payment_terms` varchar(50) DEFAULT NULL,
  `items_json` text DEFAULT NULL,
  `reason` varchar(500) NOT NULL,
  `status` enum('pending','approved','rejected','withdrawn') NOT NULL DEFAULT 'pending',
  `decided_by` int(11) DEFAULT NULL,
  `decided_at` datetime DEFAULT NULL,
  `decision_note` varchar(500) DEFAULT NULL,
  `revision_no` int(11) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `po_items`
--

CREATE TABLE `po_items` (
  `id` int(11) NOT NULL,
  `po_id` int(11) DEFAULT NULL,
  `product_id` int(11) DEFAULT NULL,
  `item_description` text DEFAULT NULL,
  `quantity` int(11) DEFAULT NULL,
  `unit_price` decimal(10,2) DEFAULT NULL,
  `total` decimal(10,2) DEFAULT NULL,
  `received_quantity` int(11) DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `po_items`
--

INSERT INTO `po_items` (`id`, `po_id`, `product_id`, `item_description`, `quantity`, `unit_price`, `total`, `received_quantity`) VALUES
(1, 1, NULL, 'Test Item', 5, 1423.00, 7115.00, 0),
(2, 2, NULL, 'Test Item', 5, 1423.00, 7115.00, 0),
(3, 3, NULL, 'Test Item', 5, 1423.00, 7115.00, 5);

-- --------------------------------------------------------

--
-- Table structure for table `po_revisions`
--

CREATE TABLE `po_revisions` (
  `id` int(11) NOT NULL,
  `po_id` int(11) NOT NULL,
  `revision_no` int(11) NOT NULL,
  `change_request_id` int(11) DEFAULT NULL,
  `before_json` mediumtext DEFAULT NULL,
  `after_json` mediumtext DEFAULT NULL,
  `summary` varchar(600) DEFAULT NULL,
  `approved_by` int(11) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `po_shipments`
--

CREATE TABLE `po_shipments` (
  `id` int(11) NOT NULL,
  `po_id` int(11) NOT NULL,
  `supplier_id` int(11) NOT NULL,
  `carrier` varchar(100) NOT NULL,
  `tracking_no` varchar(100) DEFAULT NULL,
  `ship_date` date NOT NULL,
  `expected_arrival` date DEFAULT NULL,
  `remarks` varchar(255) DEFAULT NULL,
  `created_by` int(11) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `products`
--

CREATE TABLE `products` (
  `id` int(11) NOT NULL,
  `product_code` varchar(50) NOT NULL,
  `product_name` varchar(100) NOT NULL,
  `description` text DEFAULT NULL,
  `price` decimal(10,2) NOT NULL,
  `cost` decimal(10,2) DEFAULT NULL,
  `stock_quantity` int(11) DEFAULT 0,
  `category` varchar(50) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `low_stock_threshold` int(11) DEFAULT 5
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `purchase_orders`
--

CREATE TABLE `purchase_orders` (
  `id` int(11) NOT NULL,
  `po_number` varchar(50) NOT NULL,
  `requisition_id` int(11) DEFAULT NULL,
  `supplier_id` int(11) DEFAULT NULL,
  `quotation_id` int(11) DEFAULT NULL,
  `total_amount` decimal(12,2) DEFAULT NULL,
  `tax` decimal(10,2) DEFAULT 0.00,
  `shipping_cost` decimal(10,2) DEFAULT 0.00,
  `grand_total` decimal(12,2) DEFAULT NULL,
  `payment_terms` varchar(50) DEFAULT NULL,
  `delivery_date` date DEFAULT NULL,
  `status` enum('draft','pending_approval','approved','sent','acknowledged','partially_received','fulfilled','closed','cancelled','rejected') DEFAULT 'draft',
  `finance_approver_id` int(11) DEFAULT NULL,
  `procurement_officer_id` int(11) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `approved_at` timestamp NULL DEFAULT NULL,
  `sent_at` timestamp NULL DEFAULT NULL,
  `acknowledged_at` timestamp NULL DEFAULT NULL,
  `acknowledgement_note` varchar(255) DEFAULT NULL,
  `supplier_response` enum('pending','confirmed','rejected') NOT NULL DEFAULT 'pending',
  `supplier_remarks` varchar(500) DEFAULT NULL,
  `supplier_responded_at` datetime DEFAULT NULL,
  `expected_delivery_date` date DEFAULT NULL,
  `delivery_status` enum('not_started','preparing','shipped','delivered') NOT NULL DEFAULT 'not_started',
  `delivery_note` varchar(255) DEFAULT NULL,
  `delivery_updated_at` datetime DEFAULT NULL,
  `revision_no` int(11) NOT NULL DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `purchase_orders`
--

INSERT INTO `purchase_orders` (`id`, `po_number`, `requisition_id`, `supplier_id`, `quotation_id`, `total_amount`, `tax`, `shipping_cost`, `grand_total`, `payment_terms`, `delivery_date`, `status`, `finance_approver_id`, `procurement_officer_id`, `created_at`, `approved_at`, `sent_at`, `acknowledged_at`, `acknowledgement_note`, `supplier_response`, `supplier_remarks`, `supplier_responded_at`, `expected_delivery_date`, `delivery_status`, `delivery_note`, `delivery_updated_at`, `revision_no`) VALUES
(1, 'PO-20260831-7268', 1, 5, 1, 1700000.00, 145.00, 123.00, 1700268.00, 'COD', '2026-09-29', 'closed', 1, 1, '2026-08-31 14:03:04', '2026-08-31 14:04:16', NULL, NULL, NULL, 'pending', NULL, NULL, NULL, 'not_started', NULL, NULL, 0),
(2, 'PO-20260901-7115', 1, 1, 4, 123123.00, 123.00, 123.00, 123369.00, 'COD', '2026-09-29', 'closed', 1, 1, '2026-09-01 01:17:39', '2026-09-01 01:17:54', NULL, NULL, NULL, 'pending', NULL, NULL, NULL, 'not_started', NULL, NULL, 0),
(3, 'PO-20260901-1271', 1, 1, 2, 14000000.00, 10000.00, 1234.00, 14011234.00, 'COD', '2026-09-29', 'fulfilled', 1, 1, '2026-09-01 01:36:57', '2026-09-01 01:37:01', NULL, NULL, NULL, 'pending', NULL, NULL, NULL, 'not_started', NULL, NULL, 0);

-- --------------------------------------------------------

--
-- Table structure for table `quotations`
--

CREATE TABLE `quotations` (
  `id` int(11) NOT NULL,
  `rfq_id` int(11) DEFAULT NULL,
  `supplier_id` int(11) DEFAULT NULL,
  `quotation_number` varchar(50) DEFAULT NULL,
  `total_amount` decimal(12,2) DEFAULT NULL,
  `currency` varchar(10) DEFAULT 'PHP',
  `delivery_lead_time` int(11) DEFAULT NULL,
  `validity_days` int(11) DEFAULT 30,
  `payment_terms` varchar(50) DEFAULT NULL,
  `technical_compliance` decimal(5,2) DEFAULT 0.00,
  `commercial_score` decimal(5,2) DEFAULT 0.00,
  `status` enum('received','evaluating','accepted','rejected') DEFAULT 'received',
  `submitted_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `notes` text DEFAULT NULL,
  `warranty` varchar(150) DEFAULT NULL,
  `submitted_via` enum('internal','portal') NOT NULL DEFAULT 'internal',
  `revision_no` int(11) NOT NULL DEFAULT 0,
  `updated_at` datetime DEFAULT NULL,
  `submitted_by` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `quotations`
--

INSERT INTO `quotations` (`id`, `rfq_id`, `supplier_id`, `quotation_number`, `total_amount`, `currency`, `delivery_lead_time`, `validity_days`, `payment_terms`, `technical_compliance`, `commercial_score`, `status`, `submitted_at`, `notes`, `warranty`, `submitted_via`, `revision_no`, `updated_at`, `submitted_by`) VALUES
(1, 1, 5, 'QUOT-20260831-4618', 1700000.00, 'PHP', 4, 30, 'Net 30', 12.00, 22.00, 'accepted', '2026-08-31 13:53:13', 'asdasdasd', NULL, 'internal', 0, NULL, NULL),
(2, 1, 1, 'QUOT-20260831-6393', 14000000.00, 'PHP', 7, 30, 'Net 30', 85.00, 90.00, 'accepted', '2026-08-31 14:18:55', 'asdasdasd', NULL, 'internal', 0, NULL, NULL),
(3, 1, 1, 'QUOT-20260901-3194', 123123123.00, 'PHP', 7, 30, 'Net 30', 11.00, 11.00, 'accepted', '2026-09-01 01:14:58', 'asdasdasd', NULL, 'internal', 0, NULL, NULL),
(4, 1, 1, 'QUOT-20260901-2832', 123123.00, 'PHP', 7, 30, 'Net 30', 11.00, 11.00, 'accepted', '2026-09-01 01:16:57', 'asdasdasd', NULL, 'internal', 0, NULL, NULL);

-- --------------------------------------------------------

--
-- Table structure for table `quotation_items`
--

CREATE TABLE `quotation_items` (
  `id` int(11) NOT NULL,
  `quotation_id` int(11) DEFAULT NULL,
  `item_description` text DEFAULT NULL,
  `quantity` int(11) DEFAULT NULL,
  `unit_price` decimal(10,2) DEFAULT NULL,
  `total` decimal(10,2) DEFAULT NULL,
  `lead_time` int(11) DEFAULT NULL,
  `remarks` text DEFAULT NULL,
  `available_quantity` int(11) DEFAULT NULL,
  `rfq_item_id` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `quotation_items`
--

INSERT INTO `quotation_items` (`id`, `quotation_id`, `item_description`, `quantity`, `unit_price`, `total`, `lead_time`, `remarks`, `available_quantity`, `rfq_item_id`) VALUES
(1, 1, 'Test Item', 5, 1423.00, 7115.00, 7, '', NULL, NULL),
(2, 2, 'Test Item', 5, 1423.00, 7115.00, 7, '', NULL, NULL),
(3, 3, 'Test Item', 5, 1423.00, 7115.00, 7, '', NULL, NULL),
(4, 4, 'Test Item', 5, 1423.00, 7115.00, 7, '', NULL, NULL);

-- --------------------------------------------------------

--
-- Table structure for table `receiving_discrepancies`
--

CREATE TABLE `receiving_discrepancies` (
  `id` int(11) NOT NULL,
  `po_id` int(11) NOT NULL,
  `po_item_id` int(11) DEFAULT NULL,
  `grn_id` int(11) DEFAULT NULL,
  `supplier_id` int(11) NOT NULL,
  `disc_type` enum('rejected','damaged','missing') NOT NULL,
  `quantity` int(11) NOT NULL,
  `description` varchar(500) NOT NULL,
  `status` enum('open','supplier_notified','awaiting_replacement','resolved') NOT NULL DEFAULT 'open',
  `resolution` enum('replacement','credit_note','refund','return_to_supplier','short_close') DEFAULT NULL,
  `resolution_amount` decimal(12,2) DEFAULT NULL,
  `resolution_note` varchar(500) DEFAULT NULL,
  `supplier_response` varchar(500) DEFAULT NULL,
  `supplier_responded_at` datetime DEFAULT NULL,
  `created_by` int(11) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `notified_at` datetime DEFAULT NULL,
  `resolved_by` int(11) DEFAULT NULL,
  `resolved_at` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `requisitions`
--

CREATE TABLE `requisitions` (
  `id` int(11) NOT NULL,
  `req_number` varchar(50) NOT NULL,
  `requester_id` int(11) DEFAULT NULL,
  `department` varchar(100) DEFAULT NULL,
  `cost_centre` varchar(50) DEFAULT NULL,
  `description` text DEFAULT NULL,
  `estimated_value` decimal(12,2) DEFAULT NULL,
  `urgency` enum('low','medium','high','critical') DEFAULT 'medium',
  `status` enum('draft','pending_budget','budget_approved','budget_rejected','sourcing','completed') DEFAULT 'draft',
  `budget_owner_id` int(11) DEFAULT NULL,
  `finance_remarks` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `budget_code` varchar(50) DEFAULT NULL,
  `budget_used` decimal(12,2) DEFAULT 0.00
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `requisitions`
--

INSERT INTO `requisitions` (`id`, `req_number`, `requester_id`, `department`, `cost_centre`, `description`, `estimated_value`, `urgency`, `status`, `budget_owner_id`, `finance_remarks`, `created_at`, `updated_at`, `budget_code`, `budget_used`) VALUES
(1, 'REQ-TEST-20260831-1932', 1, 'IT', 'IT-001', 'Test requisition', 1000.00, 'medium', 'completed', NULL, 'asdasd', '2026-08-31 12:39:23', '2026-08-31 14:03:04', NULL, 0.00),
(2, 'REQ-TEST-20260831-6129', 1, 'IT', 'IT-001', 'Test from debug', 1000.00, 'medium', 'budget_rejected', NULL, '', '2026-08-31 12:52:25', '2026-08-31 12:54:19', NULL, 0.00),
(3, 'REQ-TEST-20260831-5418', 1, 'IT', 'IT-001', 'Test requisition from test form', 1500.00, 'medium', 'budget_rejected', NULL, '', '2026-08-31 13:00:33', '2026-08-31 13:20:24', NULL, 0.00),
(4, 'REQ-TEST-20260831-7612', 1, 'Finance', 'FN-001', 'Test requisition from test form', 100.00, 'medium', 'budget_rejected', NULL, '', '2026-08-31 13:01:23', '2026-08-31 13:20:28', NULL, 0.00),
(5, 'REQ-SIMPLE-20260831-3592', 1, 'IT', 'IT-001', 'Test requisition', 1000.00, 'medium', 'budget_rejected', NULL, '', '2026-08-31 13:18:30', '2026-08-31 13:20:32', NULL, 0.00),
(6, 'REQ-SIMPLE-20260831-8935', 1, 'asdasd', 'asdasd', 'asdasd', 1000.00, 'medium', 'budget_approved', NULL, '', '2026-08-31 13:18:53', '2026-08-31 13:20:36', NULL, 0.00),
(7, 'REQ-20260831-7481', 1, 'IT', 'IT-001', 'asdasd', 1000000.00, 'medium', 'budget_rejected', NULL, '', '2026-08-31 13:20:11', '2026-08-31 13:20:40', NULL, 0.00),
(8, 'REQ-20260831-1717', 1, 'IT', 'IT-001', 'asdasdasd', 1000000.00, 'medium', 'budget_approved', NULL, '', '2026-08-31 13:20:51', '2026-08-31 13:47:20', NULL, 0.00),
(9, 'REQ-20260831-6982', 1, 'Finance', 'FN-001', 'asdasd', 100.00, 'medium', 'budget_approved', NULL, '', '2026-08-31 13:23:15', '2026-08-31 13:47:17', NULL, 0.00),
(10, 'REQ-20260831-4037', 1, 'Finance', 'FN-001', 'asdasdasd', 100.00, 'medium', 'budget_approved', NULL, '', '2026-08-31 13:23:40', '2026-08-31 13:47:15', NULL, 0.00),
(11, 'REQ-20260831-5770', 1, 'Finance', 'FN-001', 'asdasd', 100.00, 'medium', 'budget_approved', NULL, '', '2026-08-31 13:25:28', '2026-08-31 13:47:13', NULL, 0.00),
(12, 'REQ-20260831-5447', 1, 'Finance', 'FN-001', 'adadad', 100.00, 'medium', 'budget_approved', NULL, '', '2026-08-31 13:26:33', '2026-08-31 13:47:10', NULL, 0.00);

-- --------------------------------------------------------

--
-- Table structure for table `requisition_items`
--

CREATE TABLE `requisition_items` (
  `id` int(11) NOT NULL,
  `requisition_id` int(11) DEFAULT NULL,
  `item_description` text DEFAULT NULL,
  `quantity` int(11) DEFAULT NULL,
  `unit` varchar(20) DEFAULT NULL,
  `estimated_price` decimal(10,2) DEFAULT NULL,
  `total` decimal(10,2) DEFAULT NULL,
  `specifications` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `requisition_items`
--

INSERT INTO `requisition_items` (`id`, `requisition_id`, `item_description`, `quantity`, `unit`, `estimated_price`, `total`, `specifications`) VALUES
(1, 1, 'Test Item', 5, 'pcs', 200.00, 1000.00, NULL),
(2, 3, 'Test Item', 5, 'pcs', 300.00, 1500.00, NULL),
(3, 4, 'ekyboard', 100, 'set', 1232.00, 123200.00, NULL),
(4, 5, 'Test Item', 5, 'pcs', 200.00, 1000.00, NULL),
(5, 6, 'Test Itemasdasd', 5, 'pcs', 200.00, 1000.00, NULL),
(6, 7, 'keyboard', 1000, 'set', 100.00, 100000.00, NULL),
(7, 8, 'keyboard', 1000, 'set', 100.00, 100000.00, NULL),
(8, 9, 'ekyboard', 100, 'set', 1232.00, 123200.00, NULL),
(9, 10, 'ekyboard', 100, 'set', 1232.00, 123200.00, NULL),
(10, 11, 'ekyboard', 100, 'set', 1232.00, 123200.00, NULL),
(11, 12, 'ekyboard', 100, 'set', 1232.00, 123200.00, NULL);

-- --------------------------------------------------------

--
-- Table structure for table `returns`
--

CREATE TABLE `returns` (
  `id` int(11) NOT NULL,
  `return_number` varchar(50) NOT NULL,
  `sale_id` int(11) DEFAULT NULL,
  `reason` enum('defective','wrong_item','damaged','customer_request','other') DEFAULT NULL,
  `status` enum('pending','approved','rejected') DEFAULT 'pending',
  `created_by` int(11) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `return_items`
--

CREATE TABLE `return_items` (
  `id` int(11) NOT NULL,
  `return_id` int(11) DEFAULT NULL,
  `sales_item_id` int(11) DEFAULT NULL,
  `quantity` int(11) DEFAULT NULL,
  `refund_amount` decimal(10,2) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `rfqs`
--

CREATE TABLE `rfqs` (
  `id` int(11) NOT NULL,
  `rfq_number` varchar(50) NOT NULL,
  `requisition_id` int(11) DEFAULT NULL,
  `title` varchar(200) DEFAULT NULL,
  `description` text DEFAULT NULL,
  `submission_deadline` datetime DEFAULT NULL,
  `status` enum('draft','sent','evaluating','closed','awarded') DEFAULT 'draft',
  `procurement_officer_id` int(11) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `selected_quotation_id` int(11) DEFAULT NULL,
  `selected_supplier_id` int(11) DEFAULT NULL,
  `selection_reason` varchar(500) DEFAULT NULL,
  `selected_by` int(11) DEFAULT NULL,
  `selected_at` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `rfqs`
--

INSERT INTO `rfqs` (`id`, `rfq_number`, `requisition_id`, `title`, `description`, `submission_deadline`, `status`, `procurement_officer_id`, `created_at`, `selected_quotation_id`, `selected_supplier_id`, `selection_reason`, `selected_by`, `selected_at`) VALUES
(1, 'RFQ-20260831-7611', 1, 'hatdog', 'asdasd', '2026-10-21 00:00:00', 'awarded', 1, '2026-08-31 13:35:30', NULL, NULL, NULL, NULL, NULL);

-- --------------------------------------------------------

--
-- Table structure for table `rfq_suppliers`
--

CREATE TABLE `rfq_suppliers` (
  `id` int(11) NOT NULL,
  `rfq_id` int(11) DEFAULT NULL,
  `supplier_id` int(11) DEFAULT NULL,
  `invited_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `responded_at` timestamp NULL DEFAULT NULL,
  `status` enum('invited','responded','no_response') DEFAULT 'invited'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `rfq_suppliers`
--

INSERT INTO `rfq_suppliers` (`id`, `rfq_id`, `supplier_id`, `invited_at`, `responded_at`, `status`) VALUES
(1, 1, 1, '2026-08-31 13:35:30', '2026-09-01 01:16:57', 'responded'),
(2, 1, 2, '2026-08-31 13:35:30', NULL, 'invited'),
(3, 1, 3, '2026-08-31 13:35:30', NULL, 'invited');

-- --------------------------------------------------------

--
-- Table structure for table `roles`
--

CREATE TABLE `roles` (
  `id` int(11) NOT NULL,
  `role_name` varchar(50) NOT NULL,
  `description` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `roles`
--

INSERT INTO `roles` (`id`, `role_name`, `description`) VALUES
(1, 'Admin', 'Full system access'),
(2, 'HRM', 'Human Resource Management - handles attendance'),
(3, 'Cashier', 'Handles POS transactions'),
(4, 'Inventory', 'Manages products and stock'),
(5, 'Finance', 'Manages financial reports'),
(6, 'Procurement', 'Manages requisitions, RFQs, suppliers, purchase orders and receiving'),
(7, 'Supplier', 'External supplier - portal access only');

-- --------------------------------------------------------

--
-- Table structure for table `role_permissions`
--

CREATE TABLE `role_permissions` (
  `id` int(11) NOT NULL,
  `role_id` int(11) DEFAULT NULL,
  `permission_id` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `role_permissions`
--

INSERT INTO `role_permissions` (`id`, `role_id`, `permission_id`) VALUES
(94, 1, 16),
(95, 1, 15),
(96, 1, 2),
(97, 1, 4),
(98, 1, 3),
(99, 1, 8),
(100, 1, 31),
(101, 1, 29),
(102, 1, 24),
(103, 1, 22),
(104, 1, 10),
(105, 1, 27),
(106, 1, 18),
(107, 1, 6),
(108, 1, 20),
(109, 1, 11),
(110, 1, 7),
(111, 1, 30),
(112, 1, 28),
(113, 1, 23),
(114, 1, 25),
(115, 1, 21),
(116, 1, 9),
(117, 1, 13),
(118, 1, 14),
(119, 1, 26),
(120, 1, 17),
(121, 1, 5),
(122, 1, 12),
(123, 1, 19),
(124, 1, 1),
(128, 5, 28),
(129, 5, 29),
(130, 5, 30),
(131, 5, 31),
(132, 5, 13),
(133, 5, 12),
(134, 5, 26),
(135, 3, 21),
(136, 3, 22),
(137, 3, 23),
(138, 3, 24),
(139, 3, 25),
(140, 3, 9),
(141, 3, 12),
(142, 3, 26),
(143, 1, 7),
(145, 2, 7),
(146, 2, 1),
(147, 2, 2),
(148, 2, 3),
(149, 2, 4),
(150, 1, 38),
(151, 1, 40),
(152, 1, 16),
(153, 1, 36),
(154, 1, 15),
(155, 1, 2),
(156, 1, 4),
(157, 1, 3),
(158, 1, 8),
(159, 1, 31),
(160, 1, 29),
(161, 1, 41),
(162, 1, 24),
(163, 1, 22),
(164, 1, 10),
(165, 1, 27),
(166, 1, 18),
(167, 1, 6),
(168, 1, 20),
(169, 1, 35),
(170, 1, 37),
(171, 1, 11),
(172, 1, 39),
(173, 1, 7),
(174, 1, 30),
(175, 1, 28),
(176, 1, 23),
(177, 1, 25),
(178, 1, 21),
(179, 1, 9),
(180, 1, 13),
(181, 1, 14),
(182, 1, 26),
(183, 1, 17),
(184, 1, 5),
(185, 1, 12),
(186, 1, 19),
(187, 1, 1),
(188, 1, 34),
(213, 2, 36),
(214, 2, 8),
(215, 2, 35),
(216, 2, 37),
(217, 2, 7),
(218, 2, 34),
(220, 3, 27),
(221, 3, 11),
(222, 3, 9),
(223, 3, 26),
(224, 3, 12),
(227, 4, 38),
(228, 4, 10),
(229, 4, 39),
(230, 4, 9),
(234, 5, 40),
(235, 5, 31),
(236, 5, 29),
(237, 5, 41),
(238, 5, 24),
(239, 5, 30),
(240, 5, 28),
(241, 5, 23),
(242, 5, 25),
(243, 5, 13),
(249, 6, 16),
(250, 6, 15),
(251, 6, 22),
(252, 6, 18),
(253, 6, 20),
(254, 6, 39),
(255, 6, 23),
(256, 6, 21),
(257, 6, 14),
(258, 6, 17),
(259, 6, 19),
(264, 1, 42),
(265, 2, 42),
(267, 1, 44),
(268, 1, 43),
(269, 5, 44),
(270, 5, 43),
(274, 1, 45),
(275, 1, 46),
(276, 2, 45),
(277, 2, 46),
(281, 1, 47),
(282, 4, 47),
(284, 5, 16),
(285, 5, 14),
(287, 1, 48),
(288, 1, 49),
(289, 1, 50),
(290, 1, 51),
(291, 1, 52),
(292, 1, 53),
(293, 1, 54),
(294, 2, 49),
(295, 2, 50),
(296, 2, 51),
(297, 2, 48),
(301, 5, 52),
(302, 5, 53),
(303, 5, 51),
(304, 5, 48),
(308, 7, 55),
(309, 1, 56),
(310, 6, 56);

-- --------------------------------------------------------

--
-- Table structure for table `sales`
--

CREATE TABLE `sales` (
  `id` int(11) NOT NULL,
  `invoice_number` varchar(50) NOT NULL,
  `user_id` int(11) DEFAULT NULL,
  `customer_name` varchar(100) DEFAULT NULL,
  `total_amount` decimal(10,2) DEFAULT NULL,
  `discount` decimal(10,2) DEFAULT 0.00,
  `tax` decimal(10,2) DEFAULT 0.00,
  `payment_method` varchar(50) DEFAULT NULL,
  `status` varchar(20) DEFAULT 'completed',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `customer_id` int(11) DEFAULT NULL,
  `return_id` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `sales_items`
--

CREATE TABLE `sales_items` (
  `id` int(11) NOT NULL,
  `sale_id` int(11) DEFAULT NULL,
  `product_id` int(11) DEFAULT NULL,
  `quantity` int(11) DEFAULT NULL,
  `unit_price` decimal(10,2) DEFAULT NULL,
  `subtotal` decimal(10,2) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `stock_adjustment_requests`
--

CREATE TABLE `stock_adjustment_requests` (
  `id` int(11) NOT NULL,
  `product_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `type` enum('in','out') NOT NULL,
  `quantity` int(11) NOT NULL,
  `reference` varchar(100) DEFAULT NULL,
  `reason` varchar(500) NOT NULL,
  `status` enum('pending','approved','rejected') NOT NULL DEFAULT 'pending',
  `reviewed_by` int(11) DEFAULT NULL,
  `reviewed_at` datetime DEFAULT NULL,
  `reviewer_note` varchar(500) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `stock_movements`
--

CREATE TABLE `stock_movements` (
  `id` int(11) NOT NULL,
  `product_id` int(11) DEFAULT NULL,
  `user_id` int(11) DEFAULT NULL,
  `type` enum('in','out') DEFAULT NULL,
  `quantity` int(11) DEFAULT NULL,
  `reference` varchar(100) DEFAULT NULL,
  `notes` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `suppliers`
--

CREATE TABLE `suppliers` (
  `id` int(11) NOT NULL,
  `supplier_code` varchar(50) NOT NULL,
  `company_name` varchar(100) NOT NULL,
  `contact_person` varchar(100) DEFAULT NULL,
  `email` varchar(100) DEFAULT NULL,
  `phone` varchar(20) DEFAULT NULL,
  `address` text DEFAULT NULL,
  `tax_id` varchar(50) DEFAULT NULL,
  `payment_terms` varchar(50) DEFAULT 'Net 30',
  `lead_time_days` int(11) DEFAULT 7,
  `rating` decimal(3,2) DEFAULT 0.00,
  `is_active` tinyint(1) DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `suppliers`
--

INSERT INTO `suppliers` (`id`, `supplier_code`, `company_name`, `contact_person`, `email`, `phone`, `address`, `tax_id`, `payment_terms`, `lead_time_days`, `rating`, `is_active`, `created_at`) VALUES
(1, 'SUP001', 'Office Depot PH', 'Maria Santos', 'maria@officedepot.ph', '02-8123-4567', NULL, NULL, 'Net 30', 5, 0.00, 1, '2026-08-23 11:43:10'),
(2, 'SUP002', 'Tech Solutions Inc.', 'John Cruz', 'john@techsolutions.com', '02-8765-4321', NULL, NULL, 'Net 15', 3, 0.00, 1, '2026-08-23 11:43:10'),
(3, 'SUP003', 'Global Logistics Corp', 'Anna Reyes', 'anna@globallogistics.com', '02-8987-6543', NULL, NULL, 'Net 45', 10, 0.00, 1, '2026-08-23 11:43:10'),
(5, 'SUP-004', 'Evo Logistic Hub', 'Mng. Cannor', 'cannor@gmail.com', '09874665458', 'tagay city', '14005', 'COD', 7, 0.00, 1, '2026-08-31 13:36:12');

-- --------------------------------------------------------

--
-- Table structure for table `supplier_accounts`
--

CREATE TABLE `supplier_accounts` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `supplier_id` int(11) NOT NULL,
  `created_by` int(11) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `supplier_accounts`
--

INSERT INTO `supplier_accounts` (`id`, `user_id`, `supplier_id`, `created_by`, `created_at`) VALUES
(1, 8, 5, 1, '2026-10-13 10:25:48');

-- --------------------------------------------------------

--
-- Table structure for table `supplier_documents`
--

CREATE TABLE `supplier_documents` (
  `id` int(11) NOT NULL,
  `supplier_id` int(11) NOT NULL,
  `po_id` int(11) DEFAULT NULL,
  `rfq_id` int(11) DEFAULT NULL,
  `discrepancy_id` int(11) DEFAULT NULL,
  `doc_type` varchar(30) NOT NULL DEFAULT 'other',
  `original_name` varchar(190) NOT NULL,
  `stored_name` varchar(80) NOT NULL,
  `mime` varchar(100) DEFAULT NULL,
  `size_bytes` int(11) NOT NULL DEFAULT 0,
  `uploaded_by` int(11) DEFAULT NULL,
  `uploaded_by_type` enum('supplier','internal') NOT NULL DEFAULT 'supplier',
  `note` varchar(255) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `supplier_performance`
--

CREATE TABLE `supplier_performance` (
  `id` int(11) NOT NULL,
  `po_id` int(11) DEFAULT NULL,
  `supplier_id` int(11) DEFAULT NULL,
  `otif_score` decimal(5,2) DEFAULT NULL,
  `quality_score` decimal(5,2) DEFAULT NULL,
  `responsiveness_score` decimal(5,2) DEFAULT NULL,
  `overall_rating` decimal(3,2) DEFAULT NULL,
  `comments` text DEFAULT NULL,
  `evaluated_by` int(11) DEFAULT NULL,
  `evaluation_date` date DEFAULT curdate()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `supplier_po_events`
--

CREATE TABLE `supplier_po_events` (
  `id` int(11) NOT NULL,
  `po_id` int(11) NOT NULL,
  `supplier_id` int(11) DEFAULT NULL,
  `actor_user_id` int(11) DEFAULT NULL,
  `actor_type` enum('supplier','procurement','system') NOT NULL DEFAULT 'system',
  `event_type` varchar(40) NOT NULL,
  `note` varchar(500) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `system_settings`
--

CREATE TABLE `system_settings` (
  `id` int(11) NOT NULL,
  `site_name` varchar(100) DEFAULT 'POS System',
  `timezone` varchar(50) DEFAULT 'Asia/Manila',
  `currency` varchar(10) DEFAULT 'PHP',
  `tax_rate` decimal(5,2) DEFAULT 12.00,
  `po_threshold` decimal(12,2) DEFAULT 50000.00,
  `low_stock_alert` int(11) DEFAULT 10,
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `system_settings`
--

INSERT INTO `system_settings` (`id`, `site_name`, `timezone`, `currency`, `tax_rate`, `po_threshold`, `low_stock_alert`, `updated_at`) VALUES
(1, 'POS System', 'Asia/Manila', 'PHP', 12.00, 50000.00, 10, '2026-08-23 13:12:26');

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` int(11) NOT NULL,
  `username` varchar(50) NOT NULL,
  `password` varchar(255) NOT NULL,
  `email` varchar(100) NOT NULL,
  `full_name` varchar(100) NOT NULL,
  `role_id` int(11) DEFAULT NULL,
  `is_active` tinyint(1) DEFAULT 1,
  `account_status` enum('active','deactivated','archived') NOT NULL DEFAULT 'active',
  `deactivation_reason` varchar(500) DEFAULT NULL,
  `deactivated_at` datetime DEFAULT NULL,
  `deactivated_by` int(11) DEFAULT NULL,
  `archived_at` datetime DEFAULT NULL,
  `archived_by` int(11) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `username`, `password`, `email`, `full_name`, `role_id`, `is_active`, `account_status`, `deactivation_reason`, `deactivated_at`, `deactivated_by`, `archived_at`, `archived_by`, `created_at`) VALUES
(1, 'admin', '$2y$10$2HKGFOmHySIAybPM7.0tS.nUom2h/Mf8I1P4zUDHSc8LfSys3mQYe', 'admin@pos.com', 'System Administrator', 1, 1, 'active', NULL, NULL, NULL, NULL, NULL, '2026-08-23 11:36:57'),
(7, 'user', '$2y$10$dalsz394UwWzPRlRTi2LW.15mlY2rmotpfVtdnZbT0TRc/xJyKrZG', 'marryjuana7@gmail.com', 'Marry Juana Shabu', 2, 1, 'active', NULL, NULL, NULL, NULL, NULL, '2026-08-31 11:26:22'),
(8, 'sup004', '$2y$10$sesDQeu5FgfFYaXrIu/0c.ev/375.POicL2DpHiYTI1LQsMvxWYdy', 'lachicamiguel051@gmail.com', 'Miguel Lachica', 7, 1, 'active', NULL, NULL, NULL, NULL, NULL, '2026-10-13 10:25:48');

-- --------------------------------------------------------

--
-- Table structure for table `user_action_requests`
--

CREATE TABLE `user_action_requests` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `action` enum('deactivate') NOT NULL,
  `reason` varchar(500) NOT NULL,
  `requested_by` int(11) NOT NULL,
  `status` enum('pending','approved','rejected') NOT NULL DEFAULT 'pending',
  `reviewed_by` int(11) DEFAULT NULL,
  `reviewed_at` datetime DEFAULT NULL,
  `reviewer_note` varchar(500) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `user_status_history`
--

CREATE TABLE `user_status_history` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `action` enum('deactivated','reactivated','archived','restored','permanently_deleted') NOT NULL,
  `reason` varchar(500) DEFAULT NULL,
  `performed_by` int(11) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Indexes for dumped tables
--

--
-- Indexes for table `activity_logs`
--
ALTER TABLE `activity_logs`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `attendance`
--
ALTER TABLE `attendance`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `backup_logs`
--
ALTER TABLE `backup_logs`
  ADD PRIMARY KEY (`id`),
  ADD KEY `created_by` (`created_by`);

--
-- Indexes for table `budgets`
--
ALTER TABLE `budgets`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `budget_code` (`budget_code`);

--
-- Indexes for table `customers`
--
ALTER TABLE `customers`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `customer_code` (`customer_code`);

--
-- Indexes for table `email_logs`
--
ALTER TABLE `email_logs`
  ADD PRIMARY KEY (`id`),
  ADD KEY `status` (`status`),
  ADD KEY `ref` (`ref_table`,`ref_id`);

--
-- Indexes for table `employees`
--
ALTER TABLE `employees`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `employee_id` (`employee_id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `employee_recurring_items`
--
ALTER TABLE `employee_recurring_items`
  ADD PRIMARY KEY (`id`),
  ADD KEY `emp_kind` (`employee_id`,`kind`);

--
-- Indexes for table `employee_salary`
--
ALTER TABLE `employee_salary`
  ADD PRIMARY KEY (`id`),
  ADD KEY `emp_effective` (`employee_id`,`effective_from`);

--
-- Indexes for table `expenses`
--
ALTER TABLE `expenses`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `expense_number` (`expense_number`),
  ADD KEY `created_by` (`created_by`);

--
-- Indexes for table `goods_receipts`
--
ALTER TABLE `goods_receipts`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `grn_number` (`grn_number`),
  ADD KEY `po_id` (`po_id`),
  ADD KEY `received_by` (`received_by`);

--
-- Indexes for table `gr_items`
--
ALTER TABLE `gr_items`
  ADD PRIMARY KEY (`id`),
  ADD KEY `goods_receipt_id` (`goods_receipt_id`),
  ADD KEY `po_item_id` (`po_item_id`);

--
-- Indexes for table `invoices`
--
ALTER TABLE `invoices`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `invoice_number` (`invoice_number`),
  ADD KEY `po_id` (`po_id`),
  ADD KEY `supplier_id` (`supplier_id`);

--
-- Indexes for table `job_applications`
--
ALTER TABLE `job_applications`
  ADD PRIMARY KEY (`id`),
  ADD KEY `job_id` (`job_id`),
  ADD KEY `status` (`status`);

--
-- Indexes for table `job_postings`
--
ALTER TABLE `job_postings`
  ADD PRIMARY KEY (`id`),
  ADD KEY `status` (`status`),
  ADD KEY `created_by` (`created_by`);

--
-- Indexes for table `leaves`
--
ALTER TABLE `leaves`
  ADD PRIMARY KEY (`id`),
  ADD KEY `employee_id` (`employee_id`);

--
-- Indexes for table `notifications`
--
ALTER TABLE `notifications`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`),
  ADD KEY `is_read` (`is_read`);

--
-- Indexes for table `password_tokens`
--
ALTER TABLE `password_tokens`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `uniq_hash` (`token_hash`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `payments`
--
ALTER TABLE `payments`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `payment_number` (`payment_number`),
  ADD KEY `invoice_id` (`invoice_id`),
  ADD KEY `processed_by` (`processed_by`);

--
-- Indexes for table `payroll_adjustments`
--
ALTER TABLE `payroll_adjustments`
  ADD PRIMARY KEY (`id`),
  ADD KEY `run_id` (`run_id`),
  ADD KEY `status` (`status`);

--
-- Indexes for table `payroll_approvals`
--
ALTER TABLE `payroll_approvals`
  ADD PRIMARY KEY (`id`),
  ADD KEY `run_id` (`run_id`);

--
-- Indexes for table `payroll_deductions`
--
ALTER TABLE `payroll_deductions`
  ADD PRIMARY KEY (`id`),
  ADD KEY `item_id` (`item_id`);

--
-- Indexes for table `payroll_earnings`
--
ALTER TABLE `payroll_earnings`
  ADD PRIMARY KEY (`id`),
  ADD KEY `item_id` (`item_id`);

--
-- Indexes for table `payroll_items`
--
ALTER TABLE `payroll_items`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `uniq_run_emp` (`run_id`,`employee_id`);

--
-- Indexes for table `payroll_payments`
--
ALTER TABLE `payroll_payments`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `uniq_idem` (`idem_key`),
  ADD KEY `item_id` (`item_id`),
  ADD KEY `run_id` (`run_id`);

--
-- Indexes for table `payroll_periods`
--
ALTER TABLE `payroll_periods`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `uniq_period` (`start_date`,`end_date`,`pay_schedule`);

--
-- Indexes for table `payroll_rules`
--
ALTER TABLE `payroll_rules`
  ADD PRIMARY KEY (`id`),
  ADD KEY `rule_lookup` (`rule_type`,`effective_from`);

--
-- Indexes for table `payroll_runs`
--
ALTER TABLE `payroll_runs`
  ADD PRIMARY KEY (`id`),
  ADD KEY `period_id` (`period_id`),
  ADD KEY `status` (`status`);

--
-- Indexes for table `payroll_settings`
--
ALTER TABLE `payroll_settings`
  ADD PRIMARY KEY (`setting_key`);

--
-- Indexes for table `payslips`
--
ALTER TABLE `payslips`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `uniq_item` (`item_id`),
  ADD UNIQUE KEY `uniq_ref` (`reference_no`),
  ADD KEY `employee_id` (`employee_id`);

--
-- Indexes for table `permissions`
--
ALTER TABLE `permissions`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `permission_name` (`permission_name`);

--
-- Indexes for table `po_change_requests`
--
ALTER TABLE `po_change_requests`
  ADD PRIMARY KEY (`id`),
  ADD KEY `po_id` (`po_id`),
  ADD KEY `status` (`status`);

--
-- Indexes for table `po_items`
--
ALTER TABLE `po_items`
  ADD PRIMARY KEY (`id`),
  ADD KEY `po_id` (`po_id`);

--
-- Indexes for table `po_revisions`
--
ALTER TABLE `po_revisions`
  ADD PRIMARY KEY (`id`),
  ADD KEY `po_id` (`po_id`);

--
-- Indexes for table `po_shipments`
--
ALTER TABLE `po_shipments`
  ADD PRIMARY KEY (`id`),
  ADD KEY `po_id` (`po_id`);

--
-- Indexes for table `products`
--
ALTER TABLE `products`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `product_code` (`product_code`);

--
-- Indexes for table `purchase_orders`
--
ALTER TABLE `purchase_orders`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `po_number` (`po_number`),
  ADD KEY `requisition_id` (`requisition_id`),
  ADD KEY `supplier_id` (`supplier_id`),
  ADD KEY `quotation_id` (`quotation_id`),
  ADD KEY `finance_approver_id` (`finance_approver_id`),
  ADD KEY `procurement_officer_id` (`procurement_officer_id`);

--
-- Indexes for table `quotations`
--
ALTER TABLE `quotations`
  ADD PRIMARY KEY (`id`),
  ADD KEY `rfq_id` (`rfq_id`),
  ADD KEY `supplier_id` (`supplier_id`);

--
-- Indexes for table `quotation_items`
--
ALTER TABLE `quotation_items`
  ADD PRIMARY KEY (`id`),
  ADD KEY `quotation_id` (`quotation_id`);

--
-- Indexes for table `receiving_discrepancies`
--
ALTER TABLE `receiving_discrepancies`
  ADD PRIMARY KEY (`id`),
  ADD KEY `po_id` (`po_id`),
  ADD KEY `status` (`status`),
  ADD KEY `supplier_id` (`supplier_id`);

--
-- Indexes for table `requisitions`
--
ALTER TABLE `requisitions`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `req_number` (`req_number`),
  ADD KEY `requester_id` (`requester_id`),
  ADD KEY `budget_owner_id` (`budget_owner_id`);

--
-- Indexes for table `requisition_items`
--
ALTER TABLE `requisition_items`
  ADD PRIMARY KEY (`id`),
  ADD KEY `requisition_id` (`requisition_id`);

--
-- Indexes for table `returns`
--
ALTER TABLE `returns`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `return_number` (`return_number`),
  ADD KEY `sale_id` (`sale_id`),
  ADD KEY `created_by` (`created_by`);

--
-- Indexes for table `return_items`
--
ALTER TABLE `return_items`
  ADD PRIMARY KEY (`id`),
  ADD KEY `return_id` (`return_id`),
  ADD KEY `sales_item_id` (`sales_item_id`);

--
-- Indexes for table `rfqs`
--
ALTER TABLE `rfqs`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `rfq_number` (`rfq_number`),
  ADD KEY `requisition_id` (`requisition_id`),
  ADD KEY `procurement_officer_id` (`procurement_officer_id`);

--
-- Indexes for table `rfq_suppliers`
--
ALTER TABLE `rfq_suppliers`
  ADD PRIMARY KEY (`id`),
  ADD KEY `rfq_id` (`rfq_id`),
  ADD KEY `supplier_id` (`supplier_id`);

--
-- Indexes for table `roles`
--
ALTER TABLE `roles`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `role_name` (`role_name`);

--
-- Indexes for table `role_permissions`
--
ALTER TABLE `role_permissions`
  ADD PRIMARY KEY (`id`),
  ADD KEY `role_id` (`role_id`),
  ADD KEY `permission_id` (`permission_id`);

--
-- Indexes for table `sales`
--
ALTER TABLE `sales`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `invoice_number` (`invoice_number`),
  ADD KEY `user_id` (`user_id`),
  ADD KEY `customer_id` (`customer_id`);

--
-- Indexes for table `sales_items`
--
ALTER TABLE `sales_items`
  ADD PRIMARY KEY (`id`),
  ADD KEY `sale_id` (`sale_id`),
  ADD KEY `product_id` (`product_id`);

--
-- Indexes for table `stock_adjustment_requests`
--
ALTER TABLE `stock_adjustment_requests`
  ADD PRIMARY KEY (`id`),
  ADD KEY `product_id` (`product_id`),
  ADD KEY `user_id` (`user_id`),
  ADD KEY `status` (`status`);

--
-- Indexes for table `stock_movements`
--
ALTER TABLE `stock_movements`
  ADD PRIMARY KEY (`id`),
  ADD KEY `product_id` (`product_id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `suppliers`
--
ALTER TABLE `suppliers`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `supplier_code` (`supplier_code`);

--
-- Indexes for table `supplier_accounts`
--
ALTER TABLE `supplier_accounts`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `uniq_user` (`user_id`),
  ADD KEY `supplier_id` (`supplier_id`);

--
-- Indexes for table `supplier_documents`
--
ALTER TABLE `supplier_documents`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `uniq_stored` (`stored_name`),
  ADD KEY `supplier_id` (`supplier_id`),
  ADD KEY `po_id` (`po_id`);

--
-- Indexes for table `supplier_performance`
--
ALTER TABLE `supplier_performance`
  ADD PRIMARY KEY (`id`),
  ADD KEY `po_id` (`po_id`),
  ADD KEY `supplier_id` (`supplier_id`),
  ADD KEY `evaluated_by` (`evaluated_by`);

--
-- Indexes for table `supplier_po_events`
--
ALTER TABLE `supplier_po_events`
  ADD PRIMARY KEY (`id`),
  ADD KEY `po_id` (`po_id`);

--
-- Indexes for table `system_settings`
--
ALTER TABLE `system_settings`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `username` (`username`),
  ADD UNIQUE KEY `email` (`email`);

--
-- Indexes for table `user_action_requests`
--
ALTER TABLE `user_action_requests`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`),
  ADD KEY `requested_by` (`requested_by`),
  ADD KEY `status` (`status`);

--
-- Indexes for table `user_status_history`
--
ALTER TABLE `user_status_history`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`),
  ADD KEY `performed_by` (`performed_by`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `activity_logs`
--
ALTER TABLE `activity_logs`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=153;

--
-- AUTO_INCREMENT for table `attendance`
--
ALTER TABLE `attendance`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `backup_logs`
--
ALTER TABLE `backup_logs`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `budgets`
--
ALTER TABLE `budgets`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `customers`
--
ALTER TABLE `customers`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `email_logs`
--
ALTER TABLE `email_logs`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `employees`
--
ALTER TABLE `employees`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `employee_recurring_items`
--
ALTER TABLE `employee_recurring_items`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `employee_salary`
--
ALTER TABLE `employee_salary`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `expenses`
--
ALTER TABLE `expenses`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `goods_receipts`
--
ALTER TABLE `goods_receipts`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `gr_items`
--
ALTER TABLE `gr_items`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `invoices`
--
ALTER TABLE `invoices`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- AUTO_INCREMENT for table `job_applications`
--
ALTER TABLE `job_applications`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `job_postings`
--
ALTER TABLE `job_postings`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `leaves`
--
ALTER TABLE `leaves`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `notifications`
--
ALTER TABLE `notifications`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `password_tokens`
--
ALTER TABLE `password_tokens`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `payments`
--
ALTER TABLE `payments`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `payroll_adjustments`
--
ALTER TABLE `payroll_adjustments`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `payroll_approvals`
--
ALTER TABLE `payroll_approvals`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `payroll_deductions`
--
ALTER TABLE `payroll_deductions`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `payroll_earnings`
--
ALTER TABLE `payroll_earnings`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `payroll_items`
--
ALTER TABLE `payroll_items`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `payroll_payments`
--
ALTER TABLE `payroll_payments`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `payroll_periods`
--
ALTER TABLE `payroll_periods`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `payroll_rules`
--
ALTER TABLE `payroll_rules`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=10;

--
-- AUTO_INCREMENT for table `payroll_runs`
--
ALTER TABLE `payroll_runs`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `payslips`
--
ALTER TABLE `payslips`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `permissions`
--
ALTER TABLE `permissions`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=57;

--
-- AUTO_INCREMENT for table `po_change_requests`
--
ALTER TABLE `po_change_requests`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `po_items`
--
ALTER TABLE `po_items`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `po_revisions`
--
ALTER TABLE `po_revisions`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `po_shipments`
--
ALTER TABLE `po_shipments`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `products`
--
ALTER TABLE `products`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `purchase_orders`
--
ALTER TABLE `purchase_orders`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `quotations`
--
ALTER TABLE `quotations`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `quotation_items`
--
ALTER TABLE `quotation_items`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `receiving_discrepancies`
--
ALTER TABLE `receiving_discrepancies`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `requisitions`
--
ALTER TABLE `requisitions`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=13;

--
-- AUTO_INCREMENT for table `requisition_items`
--
ALTER TABLE `requisition_items`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=12;

--
-- AUTO_INCREMENT for table `returns`
--
ALTER TABLE `returns`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `return_items`
--
ALTER TABLE `return_items`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `rfqs`
--
ALTER TABLE `rfqs`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `rfq_suppliers`
--
ALTER TABLE `rfq_suppliers`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `roles`
--
ALTER TABLE `roles`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- AUTO_INCREMENT for table `role_permissions`
--
ALTER TABLE `role_permissions`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=311;

--
-- AUTO_INCREMENT for table `sales`
--
ALTER TABLE `sales`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `sales_items`
--
ALTER TABLE `sales_items`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `stock_adjustment_requests`
--
ALTER TABLE `stock_adjustment_requests`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `stock_movements`
--
ALTER TABLE `stock_movements`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `suppliers`
--
ALTER TABLE `suppliers`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `supplier_accounts`
--
ALTER TABLE `supplier_accounts`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `supplier_documents`
--
ALTER TABLE `supplier_documents`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `supplier_performance`
--
ALTER TABLE `supplier_performance`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `supplier_po_events`
--
ALTER TABLE `supplier_po_events`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `system_settings`
--
ALTER TABLE `system_settings`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- AUTO_INCREMENT for table `user_action_requests`
--
ALTER TABLE `user_action_requests`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `user_status_history`
--
ALTER TABLE `user_status_history`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `activity_logs`
--
ALTER TABLE `activity_logs`
  ADD CONSTRAINT `activity_logs_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`);

--
-- Constraints for table `attendance`
--
ALTER TABLE `attendance`
  ADD CONSTRAINT `attendance_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`);

--
-- Constraints for table `backup_logs`
--
ALTER TABLE `backup_logs`
  ADD CONSTRAINT `backup_logs_ibfk_1` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`);

--
-- Constraints for table `employees`
--
ALTER TABLE `employees`
  ADD CONSTRAINT `employees_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `expenses`
--
ALTER TABLE `expenses`
  ADD CONSTRAINT `expenses_ibfk_1` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`);

--
-- Constraints for table `goods_receipts`
--
ALTER TABLE `goods_receipts`
  ADD CONSTRAINT `goods_receipts_ibfk_1` FOREIGN KEY (`po_id`) REFERENCES `purchase_orders` (`id`),
  ADD CONSTRAINT `goods_receipts_ibfk_2` FOREIGN KEY (`received_by`) REFERENCES `users` (`id`);

--
-- Constraints for table `gr_items`
--
ALTER TABLE `gr_items`
  ADD CONSTRAINT `gr_items_ibfk_1` FOREIGN KEY (`goods_receipt_id`) REFERENCES `goods_receipts` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `gr_items_ibfk_2` FOREIGN KEY (`po_item_id`) REFERENCES `po_items` (`id`);

--
-- Constraints for table `invoices`
--
ALTER TABLE `invoices`
  ADD CONSTRAINT `invoices_ibfk_1` FOREIGN KEY (`po_id`) REFERENCES `purchase_orders` (`id`),
  ADD CONSTRAINT `invoices_ibfk_2` FOREIGN KEY (`supplier_id`) REFERENCES `suppliers` (`id`);

--
-- Constraints for table `leaves`
--
ALTER TABLE `leaves`
  ADD CONSTRAINT `leaves_ibfk_1` FOREIGN KEY (`employee_id`) REFERENCES `employees` (`id`);

--
-- Constraints for table `payments`
--
ALTER TABLE `payments`
  ADD CONSTRAINT `payments_ibfk_1` FOREIGN KEY (`invoice_id`) REFERENCES `invoices` (`id`),
  ADD CONSTRAINT `payments_ibfk_2` FOREIGN KEY (`processed_by`) REFERENCES `users` (`id`);

--
-- Constraints for table `po_items`
--
ALTER TABLE `po_items`
  ADD CONSTRAINT `po_items_ibfk_1` FOREIGN KEY (`po_id`) REFERENCES `purchase_orders` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `purchase_orders`
--
ALTER TABLE `purchase_orders`
  ADD CONSTRAINT `purchase_orders_ibfk_1` FOREIGN KEY (`requisition_id`) REFERENCES `requisitions` (`id`),
  ADD CONSTRAINT `purchase_orders_ibfk_2` FOREIGN KEY (`supplier_id`) REFERENCES `suppliers` (`id`),
  ADD CONSTRAINT `purchase_orders_ibfk_3` FOREIGN KEY (`quotation_id`) REFERENCES `quotations` (`id`),
  ADD CONSTRAINT `purchase_orders_ibfk_4` FOREIGN KEY (`finance_approver_id`) REFERENCES `users` (`id`),
  ADD CONSTRAINT `purchase_orders_ibfk_5` FOREIGN KEY (`procurement_officer_id`) REFERENCES `users` (`id`);

--
-- Constraints for table `quotations`
--
ALTER TABLE `quotations`
  ADD CONSTRAINT `quotations_ibfk_1` FOREIGN KEY (`rfq_id`) REFERENCES `rfqs` (`id`),
  ADD CONSTRAINT `quotations_ibfk_2` FOREIGN KEY (`supplier_id`) REFERENCES `suppliers` (`id`);

--
-- Constraints for table `quotation_items`
--
ALTER TABLE `quotation_items`
  ADD CONSTRAINT `quotation_items_ibfk_1` FOREIGN KEY (`quotation_id`) REFERENCES `quotations` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `requisitions`
--
ALTER TABLE `requisitions`
  ADD CONSTRAINT `requisitions_ibfk_1` FOREIGN KEY (`requester_id`) REFERENCES `users` (`id`),
  ADD CONSTRAINT `requisitions_ibfk_2` FOREIGN KEY (`budget_owner_id`) REFERENCES `users` (`id`);

--
-- Constraints for table `requisition_items`
--
ALTER TABLE `requisition_items`
  ADD CONSTRAINT `requisition_items_ibfk_1` FOREIGN KEY (`requisition_id`) REFERENCES `requisitions` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `returns`
--
ALTER TABLE `returns`
  ADD CONSTRAINT `returns_ibfk_1` FOREIGN KEY (`sale_id`) REFERENCES `sales` (`id`),
  ADD CONSTRAINT `returns_ibfk_2` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`);

--
-- Constraints for table `return_items`
--
ALTER TABLE `return_items`
  ADD CONSTRAINT `return_items_ibfk_1` FOREIGN KEY (`return_id`) REFERENCES `returns` (`id`),
  ADD CONSTRAINT `return_items_ibfk_2` FOREIGN KEY (`sales_item_id`) REFERENCES `sales_items` (`id`);

--
-- Constraints for table `rfqs`
--
ALTER TABLE `rfqs`
  ADD CONSTRAINT `rfqs_ibfk_1` FOREIGN KEY (`requisition_id`) REFERENCES `requisitions` (`id`),
  ADD CONSTRAINT `rfqs_ibfk_2` FOREIGN KEY (`procurement_officer_id`) REFERENCES `users` (`id`);

--
-- Constraints for table `rfq_suppliers`
--
ALTER TABLE `rfq_suppliers`
  ADD CONSTRAINT `rfq_suppliers_ibfk_1` FOREIGN KEY (`rfq_id`) REFERENCES `rfqs` (`id`),
  ADD CONSTRAINT `rfq_suppliers_ibfk_2` FOREIGN KEY (`supplier_id`) REFERENCES `suppliers` (`id`);

--
-- Constraints for table `role_permissions`
--
ALTER TABLE `role_permissions`
  ADD CONSTRAINT `role_permissions_ibfk_1` FOREIGN KEY (`role_id`) REFERENCES `roles` (`id`),
  ADD CONSTRAINT `role_permissions_ibfk_2` FOREIGN KEY (`permission_id`) REFERENCES `permissions` (`id`);

--
-- Constraints for table `sales`
--
ALTER TABLE `sales`
  ADD CONSTRAINT `sales_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`),
  ADD CONSTRAINT `sales_ibfk_2` FOREIGN KEY (`customer_id`) REFERENCES `customers` (`id`);

--
-- Constraints for table `sales_items`
--
ALTER TABLE `sales_items`
  ADD CONSTRAINT `sales_items_ibfk_1` FOREIGN KEY (`sale_id`) REFERENCES `sales` (`id`),
  ADD CONSTRAINT `sales_items_ibfk_2` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`);

--
-- Constraints for table `stock_movements`
--
ALTER TABLE `stock_movements`
  ADD CONSTRAINT `stock_movements_ibfk_1` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`),
  ADD CONSTRAINT `stock_movements_ibfk_2` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`);

--
-- Constraints for table `supplier_performance`
--
ALTER TABLE `supplier_performance`
  ADD CONSTRAINT `supplier_performance_ibfk_1` FOREIGN KEY (`po_id`) REFERENCES `purchase_orders` (`id`),
  ADD CONSTRAINT `supplier_performance_ibfk_2` FOREIGN KEY (`supplier_id`) REFERENCES `suppliers` (`id`),
  ADD CONSTRAINT `supplier_performance_ibfk_3` FOREIGN KEY (`evaluated_by`) REFERENCES `users` (`id`);
--
-- Database: `test`
--
CREATE DATABASE IF NOT EXISTS `test` DEFAULT CHARACTER SET latin1 COLLATE latin1_swedish_ci;
USE `test`;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
