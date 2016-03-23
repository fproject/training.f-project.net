CREATE TABLE IF NOT EXISTS `employee` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `age` int(10) DEFAULT NULL,
  `gender` tinyint(4) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci AUTO_INCREMENT=50 ;

--
-- Dumping data for table `employee`
--

INSERT INTO `employee` (`id`, `name`, `age`, `gender`) VALUES
(1, 'cẩu PTIT', 10, 1),
(2, 'Van Ga`', 1, 0),
(3, 'lê xuân', 210, 1),
(4, 'á đù', 69, 0),
(5, 'Tom', 30, 0),
(6, 'Peter Pan', 30, 0),
(7, 'rerewrewr', 12, 0),
(8, 'OKOKOK', 12, 0),
(9, 'dsds', 1212, 0),
(10, 'Peter Pan111', 99, 1),
(11, 'DungDX', 14124, 1),
(12, 'abc', 20, 0),
(13, 'Chào em gái', 200, 0),
(14, 'KKK', 30, 1),
(15, 'QuyTV111', 30, 1),
(16, 'aaaaa', 12, 0),
(17, 'adaD', 22, 1),
(18, 'Thim'' AnhND kaka', 69, 1),
(19, '', 0, 0),
(20, '', 0, 0),
(21, '', 0, 0),
(22, 'Thim'' AnhND', 69, 1),
(23, 'gafsa', 12, 0),
(24, 'Tom', 30, 0),
(25, 'Tom', 30, 0),
(26, 'Tom', 30, 0),
(27, 'Tom', 30, 0),
(28, 'Tom', 30, 0),
(29, 'Tom', 30, 0),
(30, 'Tuấndog', 12, 1),
(31, 'Nguyễn Quán Cẩu', 19, 1),
(32, 'd', 19, 1),
(33, 'Ngoc', 10, 0),
(34, 'Hello Man', 10, 1),
(35, 'gh', 10, 0),
(36, 'Aleen', 200, 0),
(37, 'T Tung', 35, 1),
(38, '', -2147483648, 0),
(39, '', -2147483648, 0),
(40, '', -2147483648, 0),
(41, '', -2147483648, 0),
(42, '', -2147483648, 0),
(43, '', -2147483648, 0),
(44, '', -2147483648, 0),
(45, '', -2147483648, 0),
(46, '', -2147483648, 0),
(47, '', -2147483648, 0),
(48, '', -2147483648, 0),
(49, '', -2147483648, 0);

-- --------------------------------------------------------

--
-- Table structure for table `f_app_context_data`
--

CREATE TABLE IF NOT EXISTS `f_app_context_data` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `loginUserId` int(11) DEFAULT NULL,
  `xmlData` mediumtext COLLATE utf8_unicode_ci,
  PRIMARY KEY (`id`),
  KEY `fk_app_context_data_user` (`loginUserId`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

--
-- Table structure for table `f_auth_assignment`
--

CREATE TABLE IF NOT EXISTS `f_auth_assignment` (
  `itemname` varchar(64) COLLATE utf8_unicode_ci NOT NULL,
  `userid` varchar(64) COLLATE utf8_unicode_ci NOT NULL,
  `bizrule` text COLLATE utf8_unicode_ci,
  `data` text COLLATE utf8_unicode_ci,
  PRIMARY KEY (`itemname`,`userid`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `f_auth_item`
--

CREATE TABLE IF NOT EXISTS `f_auth_item` (
  `name` varchar(64) COLLATE utf8_unicode_ci NOT NULL,
  `type` int(11) NOT NULL,
  `description` text COLLATE utf8_unicode_ci,
  `bizrule` text COLLATE utf8_unicode_ci,
  `data` text COLLATE utf8_unicode_ci,
  PRIMARY KEY (`name`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `f_auth_item_child`
--

CREATE TABLE IF NOT EXISTS `f_auth_item_child` (
  `parent` varchar(64) COLLATE utf8_unicode_ci NOT NULL,
  `child` varchar(64) COLLATE utf8_unicode_ci NOT NULL,
  PRIMARY KEY (`parent`,`child`),
  KEY `child` (`child`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `f_user`
--

CREATE TABLE IF NOT EXISTS `f_user` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `userName` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `displayName` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `email` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `password` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `activateKey` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `status` tinyint(4) NOT NULL DEFAULT '0',
  `isSuperuser` tinyint(1) NOT NULL DEFAULT '0',
  `lastLoginTime` datetime DEFAULT NULL,
  `createTime` datetime DEFAULT NULL,
  `createUserId` int(11) DEFAULT NULL,
  `updateTime` datetime DEFAULT NULL,
  `updateUserId` int(11) DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `idx_user_username` (`userName`),
  UNIQUE KEY `idx_user_email` (`email`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci AUTO_INCREMENT=2 ;

--
-- Dumping data for table `f_user`
--

INSERT INTO `f_user` (`id`, `userName`, `displayName`, `email`, `password`, `activateKey`, `status`, `isSuperuser`, `lastLoginTime`, `createTime`, `createUserId`, `updateTime`, `updateUserId`) VALUES
(1, 'admin', 'Administrator', 'training@f-project.net', '$2a$13$VGA6cjahQaYfwRefQmadGe1ktoVH/Au2LO.li5KOZQ5U9eMHfPCe2', '$2a$13$iASh6ACLijeGVWWldp9n8eES8FQQtBMXaQjgHF.kJ.Gb/M26URRHK', 1, 1, '2015-01-28 11:07:29', NULL, NULL, NULL, NULL);

-- --------------------------------------------------------

--
-- Table structure for table `f_user_profile`
--

CREATE TABLE IF NOT EXISTS `f_user_profile` (
  `userId` int(11) NOT NULL AUTO_INCREMENT,
  `firstName` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `lastName` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  PRIMARY KEY (`userId`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci AUTO_INCREMENT=2 ;

--
-- Dumping data for table `f_user_profile`
--

INSERT INTO `f_user_profile` (`userId`, `firstName`, `lastName`) VALUES
(1, 'System', 'Administrator');

-- --------------------------------------------------------

--
-- Table structure for table `f_user_profile_field`
--

CREATE TABLE IF NOT EXISTS `f_user_profile_field` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `varName` varchar(50) COLLATE utf8_unicode_ci NOT NULL,
  `title` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `fieldType` varchar(50) COLLATE utf8_unicode_ci NOT NULL,
  `fieldSize` tinyint(4) DEFAULT '0',
  `fieldSizeMin` tinyint(4) DEFAULT '0',
  `required` tinyint(1) DEFAULT '0',
  `match` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `errorMessage` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `otherValidator` text COLLATE utf8_unicode_ci,
  `range` text COLLATE utf8_unicode_ci,
  `default` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `widget` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `widgetParams` text COLLATE utf8_unicode_ci,
  `position` tinyint(4) DEFAULT '0',
  `visible` tinyint(1) DEFAULT '0',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci AUTO_INCREMENT=3 ;

--
-- Dumping data for table `f_user_profile_field`
--

INSERT INTO `f_user_profile_field` (`id`, `varName`, `title`, `fieldType`, `fieldSize`, `fieldSizeMin`, `required`, `match`, `errorMessage`, `otherValidator`, `range`, `default`, `widget`, `widgetParams`, `position`, `visible`) VALUES
(1, 'firstName', 'First Name', 'VARCHAR', 127, 3, 1, NULL, 'Incorrect First Name (length between 3 and 50 characters).', NULL, NULL, NULL, NULL, NULL, 1, 1),
(2, 'lastName', 'Last Name', 'VARCHAR', 127, 3, 1, NULL, 'Incorrect Last Name (length between 3 and 50 characters).', NULL, NULL, NULL, NULL, NULL, 2, 1);

-- --------------------------------------------------------

--
-- Constraints for table `f_app_context_data`
--
ALTER TABLE `f_app_context_data`
  ADD CONSTRAINT `fk_app_context_data_user` FOREIGN KEY (`loginUserId`) REFERENCES `f_user` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `f_user_profile`
--
ALTER TABLE `f_user_profile`
  ADD CONSTRAINT `fk_user_profile_user` FOREIGN KEY (`userId`) REFERENCES `f_user` (`id`) ON DELETE CASCADE;