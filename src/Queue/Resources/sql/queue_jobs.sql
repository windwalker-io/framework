CREATE TABLE IF NOT EXISTS `queue_jobs` (
  `id` bigint(20) unsigned NOT NULL,
  `queue` varchar(255) NOT NULL DEFAULT '',
  `body` longtext NOT NULL,
  `attempts` tinyint(4) unsigned NOT NULL DEFAULT '0',
  `created` datetime NOT NULL DEFAULT '1000-01-01 00:00:00',
  `visibility` datetime NOT NULL DEFAULT '1000-01-01 00:00:00',
  `reserved` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

ALTER TABLE `queue_jobs`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_queue_jobs_queue` (`queue`);

ALTER TABLE `queue_jobs`
  MODIFY `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT;
