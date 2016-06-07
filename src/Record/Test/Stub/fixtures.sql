CREATE TABLE IF NOT EXISTS `articles` (
  `id` int(11) unsigned NOT NULL COMMENT 'Primary Key',
  `catid` int(11) unsigned NOT NULL COMMENT 'Category ID',
  `title` varchar(255) NOT NULL COMMENT 'Title',
  `alias` varchar(255) NOT NULL COMMENT 'Alias',
  `state` tinyint(4) NOT NULL COMMENT '0: unpublished, 1:published',
  `ordering` int(11) unsigned NOT NULL COMMENT 'Ordering',
  `created` datetime NOT NULL COMMENT 'Created Date',
  `language` char(7) NOT NULL COMMENT 'Language'
) ENGINE=InnoDB AUTO_INCREMENT=31 DEFAULT CHARSET=utf8;

INSERT INTO `articles` (`id`, `catid`, `title`, `alias`, `state`, `ordering`, `created`, `language`) VALUES
(1, 2, 'Inventore suscipit', 'inventore-suscipit', 0, 7, '2000-12-14 01:53:02', 'zh-TW'),
(2, 2, 'Reiciendis recusandae', 'reiciendis-recusandae', 1, 8, '1976-09-12 00:32:01', 'ja-JP'),
(3, 2, 'Illo', 'illo', 0, 9, '1972-06-24 07:21:26', 'ja-JP'),
(4, 2, 'Cumque aut nostrum', 'cumque-aut-nostrum', 0, 10, '2001-04-11 11:46:38', '*'),
(5, 2, 'Ipsam reprehenderit', 'ipsam-reprehenderit', 1, 11, '1970-10-21 05:31:09', 'en-GB'),
(6, 2, 'Vero quia', 'vero-quia', 1, 12, '1994-04-06 21:06:49', '*'),
(7, 2, 'Ut', 'ut', 0, 13, '1975-06-14 20:12:00', 'en-GB'),
(8, 2, 'Mollitia molestiae', 'mollitia-molestiae', 1, 14, '1972-09-13 20:10:21', '*'),
(9, 2, 'Fugit quo', 'fugit-quo', 0, 15, '2009-10-07 21:26:50', 'ja-JP'),
(10, 3, 'Nulla', 'nulla', 0, 7, '1991-03-09 11:33:17', 'ja-JP'),
(11, 3, 'Ut qui sed', 'ut-qui-sed', 1, 8, '2001-09-02 03:52:20', 'en-GB'),
(12, 3, 'Quidem sequi', 'quidem-sequi', 1, 9, '2006-05-22 08:10:31', 'ja-JP'),
(13, 3, 'Corporis est totam', 'corporis-est-totam', 1, 10, '1982-09-04 21:02:55', 'en-GB'),
(14, 3, 'Fugit fuga', 'fugit-fuga', 1, 11, '2016-02-13 20:00:44', '*'),
(15, 3, 'Dolore voluptatem omnis', 'dolore-voluptatem-omnis', 0, 12, '1992-06-30 07:18:47', 'en-GB'),
(16, 3, 'Labore dolorem', 'labore-dolorem', 1, 13, '1985-05-31 23:55:20', 'zh-TW'),
(17, 3, 'Sint ullam', 'sint-ullam', 1, 14, '1985-03-08 02:46:10', '*'),
(18, 3, 'Perspiciatis aut', 'perspiciatis-aut', 1, 15, '2014-05-02 01:11:29', '*'),
(19, 4, 'Et', 'et', 1, 7, '1974-01-20 13:03:37', 'ja-JP'),
(20, 4, 'Rerum tempore', 'rerum-tempore', 0, 8, '2007-03-22 19:52:25', 'en-GB'),
(21, 4, 'Sed officiis', 'sed-officiis', 1, 9, '2012-10-23 10:55:33', 'zh-TW'),
(22, 4, 'Vel cumque', 'vel-cumque', 0, 10, '2010-09-28 18:58:12', 'ja-JP'),
(23, 4, 'Dolores hic', 'dolores-hic', 1, 11, '1986-10-02 14:09:46', '*'),
(24, 4, 'Ad non', 'ad-non', 1, 12, '1982-12-07 19:45:48', '*'),
(25, 4, 'Aut nisi nisi', 'aut-nisi-nisi', 0, 13, '2004-03-02 02:18:24', 'en-GB'),
(26, 4, 'Non incidunt perferendis', 'non-incidunt-perferendis', 0, 14, '2004-07-28 17:26:11', 'en-GB'),
(27, 4, 'Consectetur placeat eligendi', 'consectetur-placeat-eligendi', 0, 15, '1995-07-10 09:44:59', 'ja-JP'),
(28, 5, 'Dolorem', 'dolorem', 0, 7, '1973-11-24 12:52:02', 'ja-JP'),
(29, 5, 'Assumenda alias', 'assumenda-alias', 0, 8, '2011-06-16 15:56:17', 'zh-TW'),
(30, 5, 'Ut placeat', 'ut-placeat', 0, 9, '1999-03-30 11:20:17', '*');

ALTER TABLE `articles` ADD PRIMARY KEY (`id`);
ALTER TABLE `articles` MODIFY `id` int(11) unsigned NOT NULL AUTO_INCREMENT COMMENT 'Primary Key',AUTO_INCREMENT=31;