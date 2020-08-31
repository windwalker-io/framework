DROP TABLE IF EXISTS ww_categories;
CREATE TABLE ww_categories
(
    id          integer NOT NULL PRIMARY KEY AUTOINCREMENT,
    parent_id   integer       DEFAULT 0 NOT NULL,
    lft         int           DEFAULT 0 NOT NULL,
    rgt         int           DEFAULT 0 NOT NULL,
    level       integer       DEFAULT 0 NOT NULL,
    path        varchar(1024) DEFAULT '' NOT NULL,
    type        varchar(50)   DEFAULT '' NOT NULL,
    title       varchar(255)  DEFAULT '' NOT NULL,
    alias       varchar(255)  DEFAULT '' NOT NULL,
    image       varchar(255)  DEFAULT '' NOT NULL,
    description text    NOT NULL,
    state       tinyint(1)    DEFAULT 0 NOT NULL,
    created     datetime      DEFAULT '1000-01-01 00:00:00' NOT NULL,
    created_by  integer       DEFAULT 0 NOT NULL,
    modified    datetime      DEFAULT '1000-01-01 00:00:00' NOT NULL,
    modified_by integer       DEFAULT 0 NOT NULL,
    language    char(7)       DEFAULT '' NOT NULL,
    params      text    NOT NULL
);

CREATE UNIQUE INDEX idx_categories_alias
    ON ww_categories (alias);

CREATE INDEX idx_categories_parent_id_level
    ON ww_categories (parent_id, level);

CREATE INDEX idx_categories_created_by
    ON ww_categories (created_by);

CREATE INDEX idx_categories_language
    ON ww_categories (language);

CREATE INDEX idx_categories_lft_rgt
    ON ww_categories (lft, rgt);

CREATE INDEX idx_categories_path
    ON ww_categories (path);


DROP TABLE IF EXISTS ww_articles;
CREATE TABLE ww_articles
(
    id          integer  NOT NULL PRIMARY KEY AUTOINCREMENT UNIQUE,
    category_id integer        DEFAULT 0 NOT NULL,
    page_id     integer        DEFAULT 0 NOT NULL,
    type        CHAR(15)       DEFAULT 'bar' NOT NULL,
    price       decimal(20, 6) DEFAULT 0.0,
    title       varchar(255)   DEFAULT '' NOT NULL,
    alias       varchar(255)   DEFAULT '' NOT NULL,
    introtext   longtext NOT NULL,
    state       tinyint(1)     DEFAULT 0 NOT NULL,
    ordering    integer        DEFAULT 0 NOT NULL,
    created     datetime       DEFAULT '1000-01-01 00:00:00' NOT NULL,
    created_by  integer        DEFAULT 0 NOT NULL,
    language    char(7)        DEFAULT '' NOT NULL,
    params      text     NOT NULL,

    CONSTRAINT fk_articles_category_id
        FOREIGN KEY (category_id) REFERENCES ww_categories (id)
            ON UPDATE RESTRICT ON DELETE CASCADE,

    CONSTRAINT ww_articles_categories_parent_id_level_fk
        FOREIGN KEY (page_id, category_id) REFERENCES ww_categories (parent_id, level)
            ON UPDATE RESTRICT ON DELETE RESTRICT
);

CREATE UNIQUE INDEX idx_articles_alias
    ON ww_articles (alias);

CREATE INDEX idx_articles_category_id
    ON ww_articles (category_id);

CREATE INDEX idx_articles_created_by
    ON ww_articles (created_by);

CREATE INDEX idx_articles_language
    ON ww_articles (language);

CREATE INDEX idx_articles_page_id
    ON ww_articles (page_id);

CREATE VIEW `ww_articles_view` AS
SELECT *
FROM `ww_articles`;
