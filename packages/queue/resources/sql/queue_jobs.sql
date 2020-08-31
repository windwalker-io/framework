DROP TABLE IF EXISTS `queue_jobs`;
CREATE TABLE IF NOT EXISTS `queue_jobs` (
  `id` bigint(20) unsigned NOT NULL,
  `channel` varchar(255) NOT NULL DEFAULT '',
  `body` longtext NOT NULL,
  `attempts` tinyint(4) NOT NULL DEFAULT '0',
  `created` datetime DEFAULT NULL,
  `visibility` datetime DEFAULT NULL,
  `reserved` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

ALTER TABLE `queue_jobs`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_queue_jobs_channel` (`channel`);

ALTER TABLE `queue_jobs`
  MODIFY `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT;
