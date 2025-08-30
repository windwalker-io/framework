CREATE TABLE `books`
(
    `id`      int(11) UNSIGNED NOT NULL,
    `title`   varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '',
    `created` datetime                                DEFAULT NULL,
    `updated` datetime                                DEFAULT NULL,
    `version` int(11) NOT NULL DEFAULT 0,
    `hash`    varchar(255) NOT NULL DEFAULT ''
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

ALTER TABLE `books`
    ADD PRIMARY KEY (`id`),
    ADD KEY `updated` (`updated`),
    ADD KEY `version` (`version`),
    ADD KEY `hash` (`hash`);

INSERT INTO `books` (`id`, `title`, `created`, `updated`, `version`, `hash`) VALUES
(1, 'Book 1', '2024-01-01 00:00:00', '2024-01-01 00:00:00', 1, 'hash1'),
(2, 'Book 2', '2024-01-02 00:00:00', '2024-01-02 00:00:00', 1, 'hash2'),
(3, 'Book 3', '2024-01-03 00:00:00', '2024-01-03 00:00:00', 1, 'hash3'),
(4, 'Book 4', '2024-01-04 00:00:00', '2024-01-04 00:00:00', 1, 'hash4'),
(5, 'Book 5', '2024-01-05 00:00:00', '2024-01-05 00:00:00', 1, 'hash5'),
(6, 'Book 6', '2024-01-06 00:00:00', '2024-01-06 00:00:00', 1, 'hash6'),
(7, 'Book 7', '2024-01-07 00:00:00', '2024-01-07 00:00:00', 1, 'hash7'),
(8, 'Book 8', '2024-01-08 00:00:00', '2024-01-08 00:00:00', 1, 'hash8'),
(9, 'Book 9', '2024-01-09 00:00:00', '2024-01-09 00:00:00', 1, 'hash9'),
(10, 'Book 10', '2024-01-10 00:00:00', '2024-01-10 00:00:00', 1, 'hash10');
