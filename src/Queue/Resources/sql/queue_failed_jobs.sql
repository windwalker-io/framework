CREATE TABLE IF NOT EXISTS `queue_failed_jobs` (
  `id` int(11) unsigned NOT NULL,
  `connection` varchar(255) NOT NULL DEFAULT '',
  `queue` varchar(255) NOT NULL DEFAULT '',
  `body` longtext NOT NULL,
  `exception` longtext NOT NULL,
  `created` datetime NOT NULL DEFAULT '1000-01-01 00:00:00'
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

ALTER TABLE `queue_failed_jobs`
  ADD PRIMARY KEY (`id`);

ALTER TABLE `queue_failed_jobs`
  MODIFY `id` int(11) unsigned NOT NULL AUTO_INCREMENT;
