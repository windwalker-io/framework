CREATE TABLE IF NOT EXISTS windwalker_sessions
(
    id   varbinary(192) DEFAULT '' NOT NULL,
    data text NOT NULL,
    time int  NULL    DEFAULT 0
);
ALTER TABLE `windwalker_sessions`
    ADD PRIMARY KEY (`id`);
CREATE INDEX idx_windwalker_sessions_id
    ON windwalker_sessions (id);
