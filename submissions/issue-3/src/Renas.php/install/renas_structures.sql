-- phpMyAdmin SQL Dump
-- version 4.9.7
-- https://www.phpmyadmin.net/
--
-- Host: localhost
-- Generation Time: Aug 04, 2022 at 01:24 AM
-- Server version: 10.3.32-MariaDB
-- PHP Version: 7.4.28

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET AUTOCOMMIT = 0;
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `thingfund`
--

-- --------------------------------------------------------

--
-- Table structure for table `renas_abilities`
--

CREATE TABLE `renas_abilities` (
  `name` varchar(255) NOT NULL,
  `attr` varchar(32) DEFAULT NULL,
  `level` tinyint(3) UNSIGNED NOT NULL,
  `data` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL,
  `description` text DEFAULT NULL,
  `grade` int(10) UNSIGNED NOT NULL DEFAULT 0,
  `creatorId` int(10) UNSIGNED DEFAULT NULL,
  `totalShares` varchar(255) NOT NULL DEFAULT '0'
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table `renas_adventures`
--

CREATE TABLE `renas_adventures` (
  `name` varchar(255) NOT NULL,
  `coverImage` text DEFAULT NULL,
  `apCost` int(10) UNSIGNED NOT NULL DEFAULT 1,
  `duration` int(10) UNSIGNED NOT NULL DEFAULT 0,
  `teamMin` int(10) UNSIGNED DEFAULT NULL,
  `teamMax` int(10) UNSIGNED DEFAULT NULL,
  `strengthMin` int(10) UNSIGNED DEFAULT NULL,
  `strengthMax` int(10) UNSIGNED DEFAULT NULL,
  `type` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL,
  `data` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL,
  `loot` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL,
  `probabilityModifier` bigint(20) UNSIGNED NOT NULL DEFAULT 0,
  `lastUpdate` varchar(32) DEFAULT NULL,
  `creatorId` bigint(20) UNSIGNED DEFAULT NULL,
  `totalShares` varchar(255) NOT NULL DEFAULT '0'
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table `renas_adventure_chars`
--

CREATE TABLE `renas_adventure_chars` (
  `charId` bigint(20) UNSIGNED NOT NULL,
  `uid` bigint(20) UNSIGNED DEFAULT NULL,
  `adventureId` bigint(20) UNSIGNED NOT NULL,
  `startTime` varchar(32) NOT NULL DEFAULT '0',
  `endTIme` varchar(32) NOT NULL DEFAULT '0',
  `sealed` tinyint(2) UNSIGNED NOT NULL DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table `renas_adventure_instances`
--

CREATE TABLE `renas_adventure_instances` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `version` varchar(32) DEFAULT NULL,
  `sealed` tinyint(2) UNSIGNED NOT NULL DEFAULT 0,
  `templateName` varchar(255) NOT NULL,
  `ledger` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL,
  `log` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL,
  `startTime` varchar(32) NOT NULL DEFAULT '0',
  `endTime` varchar(32) NOT NULL DEFAULT '0'
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table `renas_adventure_stage`
--

CREATE TABLE `renas_adventure_stage` (
  `editorId` bigint(20) UNSIGNED NOT NULL,
  `stageToken` varchar(255) NOT NULL,
  `name` varchar(255) NOT NULL,
  `coverImage` text DEFAULT NULL,
  `apCost` int(10) UNSIGNED NOT NULL DEFAULT 1,
  `duration` int(10) UNSIGNED NOT NULL DEFAULT 0,
  `teamMin` int(10) UNSIGNED DEFAULT NULL,
  `teamMax` int(10) UNSIGNED DEFAULT NULL,
  `strengthMin` bigint(20) UNSIGNED DEFAULT NULL,
  `strengthMax` bigint(20) UNSIGNED DEFAULT NULL,
  `type` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL,
  `data` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL,
  `loot` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL,
  `probabilityModifier` bigint(20) UNSIGNED NOT NULL DEFAULT 0,
  `lastUpdate` varchar(32) DEFAULT NULL,
  `adventureName` text DEFAULT NULL,
  `adventureDesc` text DEFAULT NULL,
  `adventureProlog` text DEFAULT NULL,
  `adventureEpilog` text DEFAULT NULL,
  `encounterText` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table `renas_adventure_today`
--

CREATE TABLE `renas_adventure_today` (
  `uid` bigint(20) UNSIGNED NOT NULL,
  `lastUpdate` varchar(32) NOT NULL DEFAULT '0',
  `adventureName` varchar(255) NOT NULL,
  `stageData` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table `renas_api_auth`
--

CREATE TABLE `renas_api_auth` (
  `appId` bigint(20) UNSIGNED NOT NULL,
  `name` varchar(255) DEFAULT NULL,
  `remoteAddress` text NOT NULL,
  `remoteAddressRestricted` tinyint(2) UNSIGNED NOT NULL DEFAULT 1,
  `token` varchar(255) NOT NULL,
  `valid` tinyint(2) UNSIGNED NOT NULL DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table `renas_balance_record_flow`
--

CREATE TABLE `renas_balance_record_flow` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `uid` bigint(20) UNSIGNED DEFAULT NULL,
  `address` varchar(255) DEFAULT NULL,
  `action` varchar(32) NOT NULL,
  `amount` bigint(20) NOT NULL DEFAULT 0,
  `type` varchar(255) NOT NULL,
  `transactionId` varchar(255) DEFAULT NULL,
  `timestamp` varchar(32) NOT NULL,
  `lastCheck` varchar(32) NOT NULL DEFAULT '0',
  `status` varchar(32) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table `renas_characters`
--

CREATE TABLE `renas_characters` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `stat` varchar(32) DEFAULT NULL,
  `sortScore` float UNSIGNED NOT NULL DEFAULT 0,
  `lastUpdate` varchar(32) DEFAULT NULL,
  `recoverStart` varchar(32) NOT NULL DEFAULT '0',
  `version` varchar(32) DEFAULT NULL,
  `ownerId` bigint(20) UNSIGNED DEFAULT NULL,
  `creatorId` bigint(20) UNSIGNED DEFAULT NULL,
  `name` varchar(255) NOT NULL,
  `data` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL,
  `portrait` text DEFAULT NULL,
  `bio` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table `renas_character_edit`
--

CREATE TABLE `renas_character_edit` (
  `charId` bigint(20) UNSIGNED NOT NULL,
  `lastUpdate` varchar(32) DEFAULT NULL,
  `version` varchar(32) DEFAULT NULL,
  `ownerId` bigint(20) UNSIGNED DEFAULT NULL,
  `creatorId` bigint(20) UNSIGNED DEFAULT NULL,
  `name` varchar(255) NOT NULL,
  `data` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL,
  `portrait` text DEFAULT NULL,
  `bio` text DEFAULT NULL,
  `stat` varchar(32) DEFAULT NULL,
  `recoverStart` varchar(32) NOT NULL DEFAULT '0'
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table `renas_character_events`
--

CREATE TABLE `renas_character_events` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `uid` bigint(20) UNSIGNED NOT NULL,
  `charId` bigint(20) UNSIGNED NOT NULL,
  `event` varchar(255) NOT NULL,
  `timestamp` varchar(32) NOT NULL,
  `data` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table `renas_character_like`
--

CREATE TABLE `renas_character_like` (
  `uid` bigint(20) UNSIGNED NOT NULL,
  `charId` bigint(20) UNSIGNED NOT NULL,
  `timestamp` varchar(32) NOT NULL DEFAULT '0',
  `cancelled` tinyint(2) UNSIGNED NOT NULL DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table `renas_character_slot`
--

CREATE TABLE `renas_character_slot` (
  `uid` bigint(20) UNSIGNED NOT NULL,
  `slot` int(10) UNSIGNED NOT NULL DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table `renas_character_stage`
--

CREATE TABLE `renas_character_stage` (
  `uid` bigint(20) UNSIGNED NOT NULL,
  `creatorId` bigint(20) UNSIGNED NOT NULL,
  `timestamp` varchar(32) NOT NULL DEFAULT '0',
  `version` varchar(32) DEFAULT NULL,
  `name` varchar(255) NOT NULL,
  `data` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL,
  `portrait` text DEFAULT NULL,
  `bio` text DEFAULT NULL,
  `stat` varchar(32) DEFAULT NULL,
  `recoverStart` varchar(32) NOT NULL DEFAULT '0'
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table `renas_character_upgrade`
--

CREATE TABLE `renas_character_upgrade` (
  `charId` bigint(20) UNSIGNED NOT NULL,
  `ownerId` bigint(20) UNSIGNED NOT NULL,
  `version` varchar(32) NOT NULL,
  `lastUpdate` varchar(32) DEFAULT NULL,
  `data` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table `renas_encounters`
--

CREATE TABLE `renas_encounters` (
  `name` varchar(255) NOT NULL,
  `intensity` int(10) UNSIGNED NOT NULL DEFAULT 0,
  `duration` int(10) UNSIGNED NOT NULL DEFAULT 0,
  `data` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL,
  `loot` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL,
  `probabilityModifier` bigint(20) NOT NULL DEFAULT 0,
  `lastUpdate` varchar(32) DEFAULT NULL,
  `creatorId` bigint(20) UNSIGNED DEFAULT NULL,
  `totalShares` varchar(255) NOT NULL DEFAULT '0'
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table `renas_encounter_stage`
--

CREATE TABLE `renas_encounter_stage` (
  `editorId` bigint(20) UNSIGNED NOT NULL,
  `stageToken` varchar(255) NOT NULL,
  `name` varchar(255) DEFAULT NULL,
  `intensity` int(10) UNSIGNED NOT NULL DEFAULT 0,
  `duration` int(10) UNSIGNED NOT NULL DEFAULT 0,
  `data` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL,
  `loot` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL,
  `probabilityModifier` bigint(20) UNSIGNED NOT NULL DEFAULT 0,
  `adventureEntrance` text DEFAULT NULL,
  `encounterApproach` text DEFAULT NULL,
  `encounterProcess` text DEFAULT NULL,
  `encounterSuccess` text DEFAULT NULL,
  `encounterFailure` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table `renas_epoch_records`
--

CREATE TABLE `renas_epoch_records` (
  `epoch` bigint(20) UNSIGNED NOT NULL,
  `type` varchar(255) NOT NULL,
  `content` varchar(255) NOT NULL,
  `count` bigint(20) NOT NULL,
  `sealed` smallint(5) UNSIGNED NOT NULL DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table `renas_epoch_staking`
--

CREATE TABLE `renas_epoch_staking` (
  `epoch` bigint(20) UNSIGNED NOT NULL,
  `uid` bigint(20) UNSIGNED NOT NULL,
  `type` varchar(255) NOT NULL,
  `content` varchar(255) NOT NULL,
  `shares` varchar(255) NOT NULL DEFAULT '0',
  `sealed` tinyint(3) UNSIGNED NOT NULL DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table `renas_events`
--

CREATE TABLE `renas_events` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `stat` tinyint(3) UNSIGNED NOT NULL DEFAULT 0,
  `name` varchar(255) DEFAULT NULL,
  `startTime` varchar(32) DEFAULT NULL,
  `endTime` varchar(32) DEFAULT NULL,
  `data` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table `renas_event_202201_code`
--

CREATE TABLE `renas_event_202201_code` (
  `redeemerId` bigint(20) UNSIGNED DEFAULT NULL,
  `code` varchar(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table `renas_event_202201_helmet`
--

CREATE TABLE `renas_event_202201_helmet` (
  `redeemerId` bigint(20) UNSIGNED DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table `renas_event_202202_code`
--

CREATE TABLE `renas_event_202202_code` (
  `redeemerId` bigint(20) UNSIGNED DEFAULT NULL,
  `code` varchar(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table `renas_facilities`
--

CREATE TABLE `renas_facilities` (
  `name` varchar(255) NOT NULL,
  `level` int(11) UNSIGNED NOT NULL,
  `image` text DEFAULT NULL,
  `data` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL,
  `lastUpdate` varchar(32) NOT NULL,
  `creatorId` bigint(20) DEFAULT NULL,
  `totalShares` varchar(255) NOT NULL DEFAULT '0'
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table `renas_facility_building`
--

CREATE TABLE `renas_facility_building` (
  `uid` bigint(20) UNSIGNED NOT NULL,
  `facilityName` varchar(255) NOT NULL,
  `facilityLevel` int(11) UNSIGNED NOT NULL,
  `builders` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL,
  `endTime` varchar(32) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table `renas_facility_prepare`
--

CREATE TABLE `renas_facility_prepare` (
  `uid` bigint(20) UNSIGNED NOT NULL,
  `facilityName` varchar(255) NOT NULL,
  `facilityLevel` int(11) UNSIGNED NOT NULL,
  `stageData` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table `renas_facility_stage`
--

CREATE TABLE `renas_facility_stage` (
  `editorId` bigint(20) UNSIGNED NOT NULL,
  `stageToken` varchar(255) NOT NULL,
  `name` varchar(255) DEFAULT NULL,
  `level` int(11) UNSIGNED NOT NULL DEFAULT 1,
  `image` text DEFAULT NULL,
  `data` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL,
  `translation` text DEFAULT NULL,
  `description` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table `renas_features`
--

CREATE TABLE `renas_features` (
  `name` varchar(255) NOT NULL,
  `type` varchar(32) NOT NULL,
  `data` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL,
  `strength` bigint(20) NOT NULL DEFAULT 0,
  `probabilityModifier` bigint(20) NOT NULL DEFAULT 0,
  `lastUpdate` varchar(32) DEFAULT NULL,
  `creatorId` bigint(20) UNSIGNED DEFAULT NULL,
  `totalShares` varchar(255) NOT NULL DEFAULT '0'
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table `renas_feature_index`
--

CREATE TABLE `renas_feature_index` (
  `name` varchar(32) NOT NULL,
  `count` int(10) UNSIGNED NOT NULL DEFAULT 0,
  `probabilityModifier` bigint(20) UNSIGNED NOT NULL DEFAULT 0,
  `strength` bigint(20) UNSIGNED NOT NULL DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table `renas_feature_stage`
--

CREATE TABLE `renas_feature_stage` (
  `editorId` bigint(20) UNSIGNED NOT NULL,
  `stageToken` varchar(255) NOT NULL,
  `name` varchar(255) DEFAULT NULL,
  `type` varchar(32) DEFAULT NULL,
  `availableFeature` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL,
  `addFeature` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL,
  `addAbility` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL,
  `translation` text DEFAULT NULL,
  `description` text DEFAULT NULL,
  `strength` bigint(20) NOT NULL DEFAULT 0,
  `probabilityModifier` bigint(20) NOT NULL DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table `renas_items`
--

CREATE TABLE `renas_items` (
  `name` varchar(255) NOT NULL,
  `icon` varchar(255) DEFAULT NULL,
  `image` text DEFAULT NULL,
  `data` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL,
  `loads` float UNSIGNED NOT NULL DEFAULT 0,
  `strengthEquip` bigint(20) NOT NULL DEFAULT 0,
  `strengthCarry` bigint(20) NOT NULL DEFAULT 0,
  `probabilityModifier` bigint(20) NOT NULL DEFAULT 0,
  `lastUpdate` varchar(32) DEFAULT NULL,
  `creatorId` bigint(20) UNSIGNED DEFAULT NULL,
  `totalShares` varchar(255) NOT NULL DEFAULT '0'
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table `renas_item_stage`
--

CREATE TABLE `renas_item_stage` (
  `editorId` bigint(20) UNSIGNED NOT NULL,
  `stageToken` varchar(255) NOT NULL,
  `name` varchar(255) DEFAULT NULL,
  `icon` varchar(255) DEFAULT NULL,
  `image` text DEFAULT NULL,
  `type` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL,
  `translation` text DEFAULT NULL,
  `description` text DEFAULT NULL,
  `strengthEquip` bigint(20) NOT NULL DEFAULT 0,
  `strengthCarry` bigint(20) NOT NULL DEFAULT 0,
  `loads` float UNSIGNED NOT NULL DEFAULT 0,
  `probabilityModifier` bigint(20) NOT NULL DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table `renas_item_types`
--

CREATE TABLE `renas_item_types` (
  `name` varchar(255) NOT NULL,
  `category` varchar(255) NOT NULL,
  `type` varchar(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table `renas_languages`
--

CREATE TABLE `renas_languages` (
  `protected` tinyint(4) UNSIGNED NOT NULL DEFAULT 0,
  `name` varchar(255) NOT NULL,
  `lang` varchar(32) NOT NULL,
  `content` text NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table `renas_logs`
--

CREATE TABLE `renas_logs` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `uid` bigint(20) UNSIGNED DEFAULT NULL,
  `timestamp` int(10) UNSIGNED NOT NULL,
  `content` longtext NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table `renas_log_epochReward`
--

CREATE TABLE `renas_log_epochReward` (
  `epoch` bigint(20) UNSIGNED NOT NULL,
  `uid` bigint(20) UNSIGNED NOT NULL,
  `type` varchar(255) NOT NULL,
  `content` varchar(255) NOT NULL,
  `reward` varchar(255) NOT NULL DEFAULT '0',
  `timestamp` int(10) UNSIGNED NOT NULL DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table `renas_messages`
--

CREATE TABLE `renas_messages` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `uid` bigint(20) UNSIGNED NOT NULL,
  `type` varchar(32) DEFAULT NULL,
  `message` text NOT NULL,
  `data` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL,
  `timestamp` varchar(32) NOT NULL DEFAULT '0',
  `unread` tinyint(2) UNSIGNED NOT NULL DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table `renas_names`
--

CREATE TABLE `renas_names` (
  `name` varchar(255) NOT NULL,
  `type` varchar(32) NOT NULL,
  `lang` varchar(32) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table `renas_ownership`
--

CREATE TABLE `renas_ownership` (
  `uid` bigint(20) UNSIGNED NOT NULL,
  `type` varchar(255) NOT NULL,
  `content` varchar(255) NOT NULL,
  `shares` varchar(255) NOT NULL DEFAULT '0'
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table `renas_url`
--

CREATE TABLE `renas_url` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `url` text NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table `renas_users`
--

CREATE TABLE `renas_users` (
  `uid` bigint(20) UNSIGNED NOT NULL,
  `username` varchar(255) NOT NULL,
  `avatar` text DEFAULT NULL,
  `cp` varchar(255) NOT NULL DEFAULT '0'
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table `renas_user_adventureUpdate`
--

CREATE TABLE `renas_user_adventureUpdate` (
  `uid` bigint(20) UNSIGNED NOT NULL,
  `lastUpdate` varchar(32) DEFAULT '0'
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table `renas_user_discord`
--

CREATE TABLE `renas_user_discord` (
  `discordId` varchar(255) NOT NULL,
  `uid` bigint(20) UNSIGNED NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table `renas_user_efx`
--

CREATE TABLE `renas_user_efx` (
  `uid` bigint(20) UNSIGNED NOT NULL,
  `type` varchar(32) NOT NULL,
  `source` varchar(255) NOT NULL,
  `data` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL,
  `expire` varchar(32) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table `renas_user_facilities`
--

CREATE TABLE `renas_user_facilities` (
  `uid` bigint(20) NOT NULL,
  `lastUpdate` varchar(32) DEFAULT NULL,
  `name` varchar(255) NOT NULL,
  `level` int(11) NOT NULL DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table `renas_user_faucet`
--

CREATE TABLE `renas_user_faucet` (
  `uid` bigint(20) UNSIGNED NOT NULL,
  `claimed` varchar(255) NOT NULL DEFAULT '0'
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table `renas_user_items`
--

CREATE TABLE `renas_user_items` (
  `uid` bigint(255) UNSIGNED NOT NULL,
  `lastUpdate` varchar(32) DEFAULT NULL,
  `name` varchar(255) NOT NULL,
  `amount` bigint(20) UNSIGNED NOT NULL DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table `renas_user_role`
--

CREATE TABLE `renas_user_role` (
  `uid` bigint(20) UNSIGNED NOT NULL,
  `role` varchar(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table `renas_user_wallet_flow`
--

CREATE TABLE `renas_user_wallet_flow` (
  `address` varchar(255) NOT NULL,
  `uid` bigint(20) UNSIGNED NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- Indexes for dumped tables
--

--
-- Indexes for table `renas_abilities`
--
ALTER TABLE `renas_abilities`
  ADD PRIMARY KEY (`name`),
  ADD UNIQUE KEY `name` (`name`),
  ADD KEY `totalShares` (`totalShares`);

--
-- Indexes for table `renas_adventures`
--
ALTER TABLE `renas_adventures`
  ADD PRIMARY KEY (`name`),
  ADD KEY `creatorId` (`creatorId`),
  ADD KEY `lastUpdate` (`lastUpdate`),
  ADD KEY `totalShares` (`totalShares`),
  ADD KEY `probabilityModifier` (`probabilityModifier`);

--
-- Indexes for table `renas_adventure_chars`
--
ALTER TABLE `renas_adventure_chars`
  ADD UNIQUE KEY `Unique` (`charId`,`adventureId`),
  ADD KEY `adventureId` (`adventureId`) USING BTREE,
  ADD KEY `uid` (`uid`),
  ADD KEY `endTIme` (`endTIme`),
  ADD KEY `startTime` (`startTime`),
  ADD KEY `sealed` (`sealed`),
  ADD KEY `charId` (`charId`);

--
-- Indexes for table `renas_adventure_instances`
--
ALTER TABLE `renas_adventure_instances`
  ADD PRIMARY KEY (`id`),
  ADD KEY `startTime` (`startTime`),
  ADD KEY `templateName` (`templateName`),
  ADD KEY `sealed` (`sealed`),
  ADD KEY `endTime` (`endTime`) USING BTREE,
  ADD KEY `version` (`version`);

--
-- Indexes for table `renas_adventure_stage`
--
ALTER TABLE `renas_adventure_stage`
  ADD UNIQUE KEY `editorId` (`editorId`) USING BTREE,
  ADD UNIQUE KEY `stageToken` (`stageToken`);

--
-- Indexes for table `renas_adventure_today`
--
ALTER TABLE `renas_adventure_today`
  ADD UNIQUE KEY `uid` (`uid`,`adventureName`) USING BTREE,
  ADD KEY `lastUpdate` (`lastUpdate`),
  ADD KEY `adventureId` (`adventureName`);

--
-- Indexes for table `renas_api_auth`
--
ALTER TABLE `renas_api_auth`
  ADD PRIMARY KEY (`appId`);

--
-- Indexes for table `renas_balance_record_flow`
--
ALTER TABLE `renas_balance_record_flow`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `Unique` (`transactionId`) USING BTREE,
  ADD KEY `address` (`address`),
  ADD KEY `type` (`type`),
  ADD KEY `sealed` (`status`),
  ADD KEY `transactionId` (`transactionId`),
  ADD KEY `uid` (`uid`),
  ADD KEY `action` (`action`),
  ADD KEY `lastCheck` (`lastCheck`);

--
-- Indexes for table `renas_characters`
--
ALTER TABLE `renas_characters`
  ADD PRIMARY KEY (`id`),
  ADD KEY `sortScore` (`sortScore`),
  ADD KEY `lastUpdate` (`lastUpdate`),
  ADD KEY `id` (`id`);

--
-- Indexes for table `renas_character_edit`
--
ALTER TABLE `renas_character_edit`
  ADD PRIMARY KEY (`charId`),
  ADD KEY `ownerId` (`ownerId`);

--
-- Indexes for table `renas_character_events`
--
ALTER TABLE `renas_character_events`
  ADD PRIMARY KEY (`id`),
  ADD KEY `uid` (`uid`),
  ADD KEY `charId` (`charId`),
  ADD KEY `event` (`event`),
  ADD KEY `timestamp` (`timestamp`);

--
-- Indexes for table `renas_character_like`
--
ALTER TABLE `renas_character_like`
  ADD UNIQUE KEY `Unique` (`uid`,`charId`),
  ADD KEY `cancelled` (`cancelled`),
  ADD KEY `timestamp` (`timestamp`);

--
-- Indexes for table `renas_character_slot`
--
ALTER TABLE `renas_character_slot`
  ADD PRIMARY KEY (`uid`),
  ADD KEY `uid` (`uid`);

--
-- Indexes for table `renas_character_stage`
--
ALTER TABLE `renas_character_stage`
  ADD PRIMARY KEY (`uid`);

--
-- Indexes for table `renas_character_upgrade`
--
ALTER TABLE `renas_character_upgrade`
  ADD UNIQUE KEY `Unique` (`charId`,`ownerId`,`version`);

--
-- Indexes for table `renas_encounters`
--
ALTER TABLE `renas_encounters`
  ADD PRIMARY KEY (`name`),
  ADD UNIQUE KEY `name` (`name`),
  ADD KEY `probabilityModifier` (`probabilityModifier`),
  ADD KEY `lastUpdate` (`lastUpdate`),
  ADD KEY `creatorId` (`creatorId`),
  ADD KEY `intensity` (`intensity`),
  ADD KEY `totalShares` (`totalShares`);

--
-- Indexes for table `renas_encounter_stage`
--
ALTER TABLE `renas_encounter_stage`
  ADD UNIQUE KEY `editorId` (`editorId`) USING BTREE,
  ADD UNIQUE KEY `stageToken` (`stageToken`) USING BTREE;

--
-- Indexes for table `renas_epoch_records`
--
ALTER TABLE `renas_epoch_records`
  ADD UNIQUE KEY `Unique` (`epoch`,`type`,`content`),
  ADD KEY `sealed` (`sealed`);

--
-- Indexes for table `renas_epoch_staking`
--
ALTER TABLE `renas_epoch_staking`
  ADD UNIQUE KEY `Unique` (`epoch`,`uid`,`type`,`content`),
  ADD KEY `shares` (`shares`),
  ADD KEY `sealed` (`sealed`);

--
-- Indexes for table `renas_events`
--
ALTER TABLE `renas_events`
  ADD PRIMARY KEY (`id`),
  ADD KEY `startTime` (`startTime`),
  ADD KEY `endTime` (`endTime`),
  ADD KEY `stat` (`stat`);

--
-- Indexes for table `renas_event_202201_code`
--
ALTER TABLE `renas_event_202201_code`
  ADD UNIQUE KEY `code` (`code`),
  ADD UNIQUE KEY `redeemerId` (`redeemerId`);

--
-- Indexes for table `renas_event_202201_helmet`
--
ALTER TABLE `renas_event_202201_helmet`
  ADD UNIQUE KEY `redeemerId` (`redeemerId`);

--
-- Indexes for table `renas_event_202202_code`
--
ALTER TABLE `renas_event_202202_code`
  ADD UNIQUE KEY `Unique` (`redeemerId`),
  ADD KEY `redeemerId` (`redeemerId`);

--
-- Indexes for table `renas_facilities`
--
ALTER TABLE `renas_facilities`
  ADD UNIQUE KEY `Unique` (`name`,`level`),
  ADD KEY `creatorId` (`creatorId`),
  ADD KEY `totalShares` (`totalShares`),
  ADD KEY `lastUpdate` (`lastUpdate`);

--
-- Indexes for table `renas_facility_building`
--
ALTER TABLE `renas_facility_building`
  ADD UNIQUE KEY `Unique` (`uid`,`facilityName`,`facilityLevel`),
  ADD KEY `uid` (`uid`),
  ADD KEY `endTime` (`endTime`);

--
-- Indexes for table `renas_facility_prepare`
--
ALTER TABLE `renas_facility_prepare`
  ADD UNIQUE KEY `Unique` (`uid`,`facilityName`,`facilityLevel`),
  ADD KEY `uid` (`uid`),
  ADD KEY `facilityName` (`facilityName`),
  ADD KEY `facilityLevel` (`facilityLevel`);

--
-- Indexes for table `renas_facility_stage`
--
ALTER TABLE `renas_facility_stage`
  ADD UNIQUE KEY `editorId` (`editorId`,`name`,`level`),
  ADD KEY `stageToken` (`stageToken`);

--
-- Indexes for table `renas_features`
--
ALTER TABLE `renas_features`
  ADD UNIQUE KEY `type.name` (`type`,`name`) USING BTREE,
  ADD KEY `probabilityModifier` (`probabilityModifier`),
  ADD KEY `strength` (`strength`),
  ADD KEY `creatorId` (`creatorId`),
  ADD KEY `lastUpdate` (`lastUpdate`),
  ADD KEY `totalShares` (`totalShares`);

--
-- Indexes for table `renas_feature_index`
--
ALTER TABLE `renas_feature_index`
  ADD UNIQUE KEY `name_2` (`name`),
  ADD KEY `name` (`name`);

--
-- Indexes for table `renas_feature_stage`
--
ALTER TABLE `renas_feature_stage`
  ADD UNIQUE KEY `creatorId` (`editorId`) USING BTREE,
  ADD UNIQUE KEY `Unique` (`name`,`type`),
  ADD KEY `stageToken` (`stageToken`) USING BTREE;

--
-- Indexes for table `renas_items`
--
ALTER TABLE `renas_items`
  ADD PRIMARY KEY (`name`),
  ADD UNIQUE KEY `name` (`name`),
  ADD KEY `probabilityModifier` (`probabilityModifier`),
  ADD KEY `creatorId` (`creatorId`),
  ADD KEY `lastUpdate` (`lastUpdate`),
  ADD KEY `strengthCarry` (`strengthCarry`),
  ADD KEY `strengthEquip` (`strengthEquip`) USING BTREE,
  ADD KEY `totalShares` (`totalShares`);

--
-- Indexes for table `renas_item_stage`
--
ALTER TABLE `renas_item_stage`
  ADD UNIQUE KEY `creatorId` (`editorId`) USING BTREE,
  ADD UNIQUE KEY `stageToken` (`stageToken`);

--
-- Indexes for table `renas_item_types`
--
ALTER TABLE `renas_item_types`
  ADD UNIQUE KEY `name.type` (`name`,`category`,`type`) USING BTREE,
  ADD KEY `name` (`name`),
  ADD KEY `type` (`type`),
  ADD KEY `category` (`category`);

--
-- Indexes for table `renas_languages`
--
ALTER TABLE `renas_languages`
  ADD UNIQUE KEY `langCode` (`name`,`lang`);

--
-- Indexes for table `renas_logs`
--
ALTER TABLE `renas_logs`
  ADD PRIMARY KEY (`id`),
  ADD KEY `timestamp` (`timestamp`),
  ADD KEY `id` (`id`);

--
-- Indexes for table `renas_log_epochReward`
--
ALTER TABLE `renas_log_epochReward`
  ADD UNIQUE KEY `Unique` (`epoch`,`uid`,`type`,`content`),
  ADD KEY `timestamp` (`timestamp`);

--
-- Indexes for table `renas_messages`
--
ALTER TABLE `renas_messages`
  ADD PRIMARY KEY (`id`),
  ADD KEY `uid` (`uid`),
  ADD KEY `timestamp` (`timestamp`),
  ADD KEY `unread` (`unread`),
  ADD KEY `id` (`id`),
  ADD KEY `type` (`type`);

--
-- Indexes for table `renas_names`
--
ALTER TABLE `renas_names`
  ADD UNIQUE KEY `Unique` (`name`,`type`,`lang`),
  ADD KEY `lang` (`lang`);

--
-- Indexes for table `renas_ownership`
--
ALTER TABLE `renas_ownership`
  ADD UNIQUE KEY `Unique` (`uid`,`type`,`content`),
  ADD KEY `uid` (`uid`),
  ADD KEY `type` (`type`),
  ADD KEY `id` (`content`),
  ADD KEY `shares` (`shares`);

--
-- Indexes for table `renas_url`
--
ALTER TABLE `renas_url`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `renas_users`
--
ALTER TABLE `renas_users`
  ADD PRIMARY KEY (`uid`),
  ADD KEY `username` (`username`),
  ADD KEY `cp` (`cp`),
  ADD KEY `uid` (`uid`);

--
-- Indexes for table `renas_user_adventureUpdate`
--
ALTER TABLE `renas_user_adventureUpdate`
  ADD PRIMARY KEY (`uid`);

--
-- Indexes for table `renas_user_discord`
--
ALTER TABLE `renas_user_discord`
  ADD PRIMARY KEY (`discordId`),
  ADD UNIQUE KEY `uid` (`uid`),
  ADD KEY `discordId` (`discordId`);

--
-- Indexes for table `renas_user_efx`
--
ALTER TABLE `renas_user_efx`
  ADD UNIQUE KEY `Unique` (`uid`,`type`,`source`),
  ADD KEY `expire` (`expire`);

--
-- Indexes for table `renas_user_facilities`
--
ALTER TABLE `renas_user_facilities`
  ADD UNIQUE KEY `Unique` (`uid`,`name`),
  ADD KEY `lastUpdate` (`lastUpdate`),
  ADD KEY `level` (`level`),
  ADD KEY `uid` (`uid`),
  ADD KEY `name` (`name`);

--
-- Indexes for table `renas_user_faucet`
--
ALTER TABLE `renas_user_faucet`
  ADD PRIMARY KEY (`uid`),
  ADD KEY `uid` (`uid`);

--
-- Indexes for table `renas_user_items`
--
ALTER TABLE `renas_user_items`
  ADD UNIQUE KEY `Unique` (`uid`,`name`),
  ADD KEY `lastUpdate` (`lastUpdate`),
  ADD KEY `itemTpl` (`name`),
  ADD KEY `amount` (`amount`),
  ADD KEY `uid` (`uid`) USING BTREE;

--
-- Indexes for table `renas_user_role`
--
ALTER TABLE `renas_user_role`
  ADD UNIQUE KEY `userRole` (`uid`,`role`);

--
-- Indexes for table `renas_user_wallet_flow`
--
ALTER TABLE `renas_user_wallet_flow`
  ADD PRIMARY KEY (`address`),
  ADD KEY `address` (`address`),
  ADD KEY `uid` (`uid`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `renas_adventure_instances`
--
ALTER TABLE `renas_adventure_instances`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `renas_api_auth`
--
ALTER TABLE `renas_api_auth`
  MODIFY `appId` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `renas_balance_record_flow`
--
ALTER TABLE `renas_balance_record_flow`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `renas_characters`
--
ALTER TABLE `renas_characters`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `renas_character_events`
--
ALTER TABLE `renas_character_events`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `renas_events`
--
ALTER TABLE `renas_events`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `renas_logs`
--
ALTER TABLE `renas_logs`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `renas_messages`
--
ALTER TABLE `renas_messages`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `renas_url`
--
ALTER TABLE `renas_url`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `renas_users`
--
ALTER TABLE `renas_users`
  MODIFY `uid` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
