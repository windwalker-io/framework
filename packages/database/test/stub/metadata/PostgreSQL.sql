DROP SEQUENCE IF EXISTS "ww_categories_seq_id" CASCADE;
CREATE TABLE ww_categories
(
    id          serial                                      NOT NULL PRIMARY KEY,
    parent_id   int           DEFAULT 0                     NOT NULL,
    lft         int           DEFAULT 0                     NOT NULL,
    rgt         int           DEFAULT 0                     NOT NULL,
    level       int DEFAULT 0 NOT NULL,
    path        varchar(1024) DEFAULT ''                    NOT NULL,
    type        varchar(50)   DEFAULT ''                    NOT NULL,
    title       varchar(255)  DEFAULT ''                    NOT NULL,
    alias       varchar(255)  DEFAULT ''                    NOT NULL,
    image       varchar(255)  DEFAULT ''                    NOT NULL,
    description text                                        NOT NULL,
    state       int           DEFAULT 0                     NOT NULL,
    created     timestamp     DEFAULT '1000-01-01 00:00:00' NOT NULL,
    created_by  int DEFAULT 0 NOT NULL,
    modified    timestamp     DEFAULT '1000-01-01 00:00:00' NOT NULL,
    modified_by int DEFAULT 0 NOT NULL,
    language    char(7)       DEFAULT ''                    NOT NULL,
    params      text                                        NOT NULL
);

CREATE UNIQUE INDEX idx_categories_alias
    ON ww_categories (alias);

CREATE UNIQUE INDEX idx_categories_parent_id_level
    ON ww_categories (parent_id, level);

CREATE INDEX idx_categories_created_by
    ON ww_categories (created_by);

CREATE INDEX idx_categories_language
    ON ww_categories (language);

CREATE INDEX idx_categories_lft_rgt
    ON ww_categories (lft, rgt);

CREATE INDEX idx_categories_path
    ON ww_categories (path);


DROP SEQUENCE IF EXISTS "ww_articles_seq_id" CASCADE;
CREATE TABLE ww_articles
(
    id          serial                                       NOT NULL PRIMARY KEY,
    category_id int            DEFAULT 0                     NOT NULL,
    page_id     int            DEFAULT 0                     NOT NULL,
    type        char(15)       DEFAULT 'bar'                 NOT NULL,
    price       decimal(20, 6) DEFAULT 0.0,
    title       varchar(255)   DEFAULT ''                    NOT NULL,
    alias       varchar(255)   DEFAULT ''                    NOT NULL,
    introtext   text                                         NOT NULL,
    state       int            DEFAULT 0                     NOT NULL,
    ordering    int DEFAULT 0 NOT NULL,
    created     timestamp      DEFAULT '1000-01-01 00:00:00' NOT NULL,
    created_by  int DEFAULT 0 NOT NULL,
    language    char(7)        DEFAULT ''                    NOT NULL,
    params      text                                         NOT NULL
);

ALTER TABLE ww_articles
    ADD CONSTRAINT fk_articles_category_id
        FOREIGN KEY (category_id) REFERENCES ww_categories (id) ON DELETE RESTRICT ON UPDATE RESTRICT;

ALTER TABLE ww_articles
    ADD CONSTRAINT fk_articles_category_more
        FOREIGN KEY (page_id, created_by) REFERENCES ww_categories (parent_id, level) ON DELETE RESTRICT ON UPDATE RESTRICT;

ALTER TABLE ww_articles
    ADD CONSTRAINT idx_articles_alias
        UNIQUE (alias);

CREATE INDEX idx_articles_category_id
    ON ww_articles (category_id);

CREATE INDEX idx_articles_created_by
    ON ww_articles (created_by);

CREATE INDEX idx_articles_language
    ON ww_articles (language);

CREATE INDEX idx_articles_page_id
    ON ww_articles (page_id);

CREATE VIEW "ww_articles_view" AS SELECT * FROM "ww_articles";
