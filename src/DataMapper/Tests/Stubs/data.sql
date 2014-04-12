
CREATE TABLE IF NOT EXISTS `ww_categories` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `parent_id` int(10) unsigned NOT NULL DEFAULT '0',
  `title` varchar(255) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=78 ;

INSERT INTO `ww_categories` (`id`, `parent_id`, `title`) VALUES
(1, 0, 'ROOT'),
(9, 1, 'Uncategorised'),
(10, 1, 'Uncategorised'),
(11, 1, 'Uncategorised'),
(12, 1, 'Uncategorised'),
(13, 1, 'Uncategorised'),
(14, 1, 'Sample Data-Articles'),
(15, 1, 'Sample Data-Banners'),
(16, 1, 'Sample Data-Contact'),
(17, 1, 'Sample Data-Newsfeeds'),
(18, 1, 'Sample Data-Weblinks'),
(19, 14, 'Joomla!'),
(20, 19, 'Extensions'),
(21, 20, 'Components'),
(22, 20, 'Modules'),
(23, 20, 'Templates'),
(24, 20, 'Languages'),
(25, 20, 'Plugins'),
(26, 14, 'Park Site'),
(27, 26, 'Park Blog'),
(28, 26, 'Photo Gallery'),
(29, 14, 'Fruit Shop Site'),
(30, 29, 'Growers'),
(31, 18, 'Park Links'),
(32, 18, 'Joomla! Specific Links'),
(33, 32, 'Other Resources'),
(34, 16, 'Park Site'),
(35, 16, 'Shop Site'),
(36, 35, 'Staff'),
(37, 35, 'Fruit Encyclopedia'),
(38, 37, 'A'),
(39, 37, 'B'),
(40, 37, 'C'),
(41, 37, 'D'),
(42, 37, 'E'),
(43, 37, 'F'),
(44, 37, 'G'),
(45, 37, 'H'),
(46, 37, 'I'),
(47, 37, 'J'),
(48, 37, 'K'),
(49, 37, 'L'),
(50, 37, 'M'),
(51, 37, 'N'),
(52, 37, 'O'),
(53, 37, 'P'),
(54, 37, 'Q'),
(55, 37, 'R'),
(56, 37, 'S'),
(57, 37, 'T'),
(58, 37, 'U'),
(59, 37, 'V'),
(60, 37, 'W'),
(61, 37, 'X'),
(62, 37, 'Y'),
(63, 37, 'Z'),
(64, 22, 'Content Modules'),
(65, 22, 'User Modules'),
(66, 22, 'Display Modules'),
(67, 22, 'Utility Modules'),
(68, 23, 'Atomic'),
(69, 23, 'Beez 20'),
(70, 23, 'Beez 5'),
(72, 28, 'Animals'),
(73, 28, 'Scenery'),
(75, 22, 'Navigation Modules'),
(76, 29, 'Recipes'),
(77, 1, 'Uncategorised');

CREATE TABLE IF NOT EXISTS `ww_content` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `title` varchar(255) NOT NULL DEFAULT '',
  `catid` int(10) unsigned NOT NULL DEFAULT '0',
  `created` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `created_by` int(10) unsigned NOT NULL DEFAULT '0',
  `access` int(10) unsigned NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  KEY `idx_access` (`access`),
  KEY `idx_catid` (`catid`),
  KEY `idx_createdby` (`created_by`),
  KEY `idx_featured_catid` (`catid`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=71 ;

INSERT INTO `ww_content` (`id`, `title`, `catid`, `created`, `created_by`, `access`) VALUES
(1, 'Administrator Components', 21, '2011-01-01 00:00:01', 144, 1),
(2, 'Archive Module', 64, '2011-01-01 00:00:01', 144, 1),
(3, 'Article Categories Module', 64, '2011-01-01 00:00:01', 144, 1),
(4, 'Articles Category Module', 64, '2011-01-01 00:00:01', 144, 1),
(5, 'Authentication', 25, '2011-01-01 00:00:01', 144, 1),
(6, 'Australian Parks ', 26, '2011-01-01 00:00:01', 144, 1),
(7, 'Banner Module', 66, '2011-01-01 00:00:01', 144, 1),
(8, 'Beginners', 19, '2011-01-01 00:00:01', 144, 1),
(9, 'Contacts', 21, '2011-01-01 00:00:01', 144, 1),
(10, 'Content', 21, '2011-01-01 00:00:01', 144, 1),
(11, 'Cradle Mountain', 73, '2011-01-01 00:00:01', 144, 1),
(12, 'Custom HTML Module', 66, '2011-01-01 00:00:01', 144, 1),
(13, 'Directions', 29, '2011-01-01 00:00:01', 144, 1),
(14, 'Editors', 25, '2011-01-01 00:00:01', 144, 1),
(15, 'Editors-xtd', 25, '2011-01-01 00:00:01', 144, 1),
(16, 'Feed Display', 66, '2011-01-01 00:00:01', 144, 1),
(17, 'First Blog Post', 27, '2011-01-01 00:00:01', 144, 1),
(18, 'Second Blog Post', 27, '2011-01-01 00:00:01', 144, 1),
(19, 'Footer Module', 66, '2011-01-01 00:00:01', 144, 1),
(20, 'Fruit Shop', 29, '2011-01-01 00:00:01', 144, 1),
(21, 'Getting Help', 19, '2011-01-01 00:00:01', 144, 1),
(22, 'Getting Started', 19, '2011-01-01 00:00:01', 144, 1),
(23, 'Happy Orange Orchard', 30, '2011-01-01 00:00:01', 144, 1),
(24, 'Joomla!', 19, '2011-01-01 00:00:01', 144, 1),
(25, 'Koala', 72, '2011-01-01 00:00:01', 144, 1),
(26, 'Language Switcher', 67, '2011-01-01 00:00:01', 144, 1),
(27, 'Latest Articles Module', 64, '2011-01-01 00:00:01', 256, 1),
(28, 'Login Module', 65, '2011-01-01 00:00:01', 144, 1),
(29, 'Menu Module', 75, '2011-01-01 00:00:01', 144, 1),
(30, 'Most Read Content', 64, '2011-01-01 00:00:01', 144, 1),
(31, 'News Flash', 64, '2011-01-01 00:00:01', 144, 1),
(32, 'Options', 19, '2011-01-01 00:00:01', 144, 1),
(33, 'Phyllopteryx', 72, '2011-01-01 00:00:01', 144, 1),
(34, 'Pinnacles', 73, '2011-01-01 00:00:01', 144, 1),
(35, 'Professionals', 19, '2011-01-01 00:00:01', 144, 1),
(36, 'Random Image Module', 66, '2011-01-01 00:00:01', 144, 1),
(37, 'Related Items Module', 64, '2011-01-01 00:00:01', 144, 1),
(38, 'Sample Sites', 19, '2011-01-01 00:00:01', 144, 1),
(39, 'Search', 21, '2011-01-01 00:00:01', 144, 1),
(40, 'Search Module', 67, '2011-01-01 00:00:01', 144, 1),
(41, 'Search ', 25, '2011-01-01 00:00:01', 144, 1),
(42, 'Site Map', 14, '2011-01-01 00:00:01', 144, 1),
(43, 'Spotted Quoll', 72, '2011-01-01 00:00:01', 144, 1),
(44, 'Statistics Module', 67, '2011-01-01 00:00:01', 144, 1),
(45, 'Syndicate Module', 67, '2011-01-01 00:00:01', 144, 1),
(46, 'System', 25, '2011-01-01 00:00:01', 144, 1),
(47, 'The Joomla! Community', 19, '2011-01-01 00:00:01', 144, 1),
(48, 'The Joomla! Project', 19, '2011-01-01 00:00:01', 144, 1),
(49, 'Typography', 23, '2011-01-01 00:00:01', 144, 1),
(50, 'Upgraders', 19, '2011-01-01 00:00:01', 144, 1),
(51, 'User', 25, '2011-01-01 00:00:01', 144, 1),
(52, 'Users', 21, '2011-01-01 00:00:01', 144, 1),
(53, 'Using Joomla!', 19, '2011-01-01 00:00:01', 144, 1),
(54, 'Weblinks', 21, '2011-01-01 00:00:01', 144, 1),
(55, 'Weblinks Module', 66, '2011-01-01 00:00:01', 144, 1),
(56, 'Who''s Online', 65, '2011-01-01 00:00:01', 144, 1),
(57, 'Wobbegone', 72, '2011-01-01 00:00:01', 144, 1),
(58, 'Wonderful Watermelon', 30, '2011-01-01 00:00:01', 144, 1),
(59, 'Wrapper Module', 67, '2011-01-01 00:00:01', 144, 1),
(60, 'News Feeds', 21, '2011-01-01 00:00:01', 144, 1),
(61, 'Breadcrumbs Module', 75, '2011-01-01 00:00:01', 144, 1),
(62, 'Content', 25, '2011-01-01 00:00:01', 144, 1),
(64, 'Blue Mountain Rain Forest', 73, '2011-01-01 00:00:01', 144, 1),
(65, 'Ormiston Pound', 73, '2011-01-01 00:00:01', 144, 1),
(66, 'Latest Users Module', 65, '2011-01-01 00:00:01', 144, 1),
(67, 'What''s New in 1.5?', 9, '2011-01-01 00:00:01', 144, 1),
(68, 'Captcha', 25, '2012-01-17 03:20:45', 144, 1),
(69, 'Quick Icons', 25, '2012-01-17 03:27:39', 144, 1),
(70, 'Smart Search', 67, '2012-01-17 03:42:36', 144, 1);

CREATE TABLE IF NOT EXISTS `ww_content2` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `content_id` varchar(255) NOT NULL DEFAULT '',
  `mark` varchar(255) NOT NULL DEFAULT '',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=71 ;

INSERT INTO `ww_content2` (`id`, `content_id`, `mark`) VALUES
(1, 6, 'flower'),
(2, 7, 'sakura'),
(3, 8, 'rose'),
(4, 9, 'sunflower'),
(5, 10, 'plum');

CREATE TABLE IF NOT EXISTS `ww_tags` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `title` varchar(255) NOT NULL DEFAULT '',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=71 ;

INSERT INTO `ww_tags` (`id`, `title`) VALUES
(1, 'flower'),
(2, 'sakura'),
(3, 'rose'),
(4, 'sunflower'),
(5, 'plum');

CREATE TABLE IF NOT EXISTS `ww_content_tags` (
  `content_id` int(10) unsigned NOT NULL DEFAULT 0,
  `tag_id` int(10) unsigned NOT NULL DEFAULT 0,
  `misc` varchar(255) NOT NULL DEFAULT ''
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=71 ;

INSERT INTO `ww_content_tags` (`content_id`, `tag_id`, `misc`) VALUES
(1, 1, 'aaa'),
(2, 1, 'bbb'),
(3, 2, 'ccc'),
(2, 4, 'ddd'),
(4, 1, 'eee'),
(4, 2, 'fff'),
(4, 3, 'ggg'),
(4, 4, 'hhh'),
(4, 5, 'iii'),
(6, 2, 'jjj'),
(6, 5, 'kkk'),
(7, 1, 'lll'),
(7, 4, 'mmm');


CREATE TABLE IF NOT EXISTS `ww_users` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(255) NOT NULL DEFAULT '',
  `username` varchar(150) NOT NULL DEFAULT '',
  PRIMARY KEY (`id`),
  KEY `idx_name` (`name`),
  KEY `username` (`username`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=145 ;

INSERT INTO `ww_users` (`id`, `name`, `username`) VALUES
(144, 'Super User', 'admin');

CREATE TABLE IF NOT EXISTS `ww_viewlevels` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT COMMENT 'Primary Key',
  `title` varchar(100) NOT NULL DEFAULT '',
  PRIMARY KEY (`id`),
  UNIQUE KEY `idx_assetgroup_title_lookup` (`title`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=5 ;

INSERT INTO `ww_viewlevels` (`id`, `title`) VALUES
(4, 'Customer Access Level (Example)'),
(1, 'Public'),
(2, 'Registered'),
(3, 'Special');
