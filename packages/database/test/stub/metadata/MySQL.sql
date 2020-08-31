DROP TABLE IF EXISTS ww_categories;
CREATE TABLE ww_categories
(
    id          int(11) UNSIGNED NOT NULL PRIMARY KEY AUTO_INCREMENT,
    parent_id   int(11) UNSIGNED DEFAULT 0                     NOT NULL COMMENT 'Parent ID',
    lft         int              DEFAULT 0                     NOT NULL COMMENT 'Left Index',
    rgt         int              DEFAULT 0                     NOT NULL COMMENT 'Right key',
    level       int(11) UNSIGNED DEFAULT 0                     NOT NULL COMMENT 'Nested Level',
    path        varchar(1024)    DEFAULT ''                    NOT NULL COMMENT 'Alias Path',
    type        varchar(50)      DEFAULT ''                    NOT NULL COMMENT 'Content Type',
    title       varchar(255)     DEFAULT ''                    NOT NULL COMMENT 'Title',
    alias       varchar(255)     DEFAULT ''                    NOT NULL COMMENT 'Alias',
    image       varchar(255)     DEFAULT ''                    NOT NULL COMMENT 'Main Image',
    description text                                           NOT NULL COMMENT 'Description Text',
    state       tinyint(1)       DEFAULT 0                     NOT NULL COMMENT '0: unpublished, 1:published',
    created     datetime         DEFAULT '1000-01-01 00:00:00' NOT NULL COMMENT 'Created Date',
    created_by  int(11) UNSIGNED DEFAULT 0                     NOT NULL COMMENT 'Author',
    modified    datetime         DEFAULT '1000-01-01 00:00:00' NOT NULL COMMENT 'Modified Date',
    modified_by int(11) UNSIGNED DEFAULT 0                     NOT NULL COMMENT 'Modified User',
    language    char(7)          DEFAULT ''                    NOT NULL COMMENT 'Language',
    params      text                                           NOT NULL COMMENT 'Params'
)
    COLLATE = utf8mb4_unicode_ci;

CREATE UNIQUE INDEX idx_categories_alias
    ON ww_categories (alias(150));

CREATE INDEX idx_categories_parent_id_level
    ON ww_categories (parent_id, level);

CREATE INDEX idx_categories_created_by
    ON ww_categories (created_by);

CREATE INDEX idx_categories_language
    ON ww_categories (language);

CREATE INDEX idx_categories_lft_rgt
    ON ww_categories (lft, rgt);

CREATE INDEX idx_categories_path
    ON ww_categories (path(150));


DROP TABLE IF EXISTS ww_articles;
CREATE TABLE ww_articles
(
    id          int(11) UNSIGNED NOT NULL AUTO_INCREMENT COMMENT 'Primary Index' PRIMARY KEY,
    category_id int(11) UNSIGNED DEFAULT 0                     NOT NULL COMMENT 'Category ID',
    page_id     int(11) UNSIGNED DEFAULT 0                     NOT NULL COMMENT 'Page ID',
    type        enum('foo','bar','yoo') DEFAULT 'bar'          NOT NULL,
    price       decimal(20,6) UNSIGNED DEFAULT 0.0,
    title       varchar(255)     DEFAULT ''                    NOT NULL COMMENT 'Title',
    alias       varchar(255)     DEFAULT ''                    NOT NULL COMMENT 'Alias',
    introtext   longtext                                       NOT NULL COMMENT 'Intro Text',
    state       tinyint(1)       DEFAULT 0                     NOT NULL COMMENT '0: unpublished, 1:published',
    ordering    int(11) UNSIGNED DEFAULT 0                     NOT NULL COMMENT 'Ordering',
    created     datetime         DEFAULT '1000-01-01 00:00:00' NOT NULL COMMENT 'Created Date',
    created_by  int(11) UNSIGNED DEFAULT 0                     NOT NULL COMMENT 'Author',
    language    char(7)          DEFAULT ''                    NOT NULL COMMENT 'Language',
    params      text                                           NOT NULL COMMENT 'Params'
)
    COLLATE = utf8mb4_unicode_ci;

ALTER TABLE ww_articles ADD CONSTRAINT fk_articles_category_id
    FOREIGN KEY (category_id) REFERENCES ww_categories(id) ON DELETE RESTRICT ON UPDATE RESTRICT;

ALTER TABLE ww_articles ADD CONSTRAINT fk_articles_category_more
    FOREIGN KEY (page_id, created_by) REFERENCES ww_categories(parent_id, level) ON DELETE RESTRICT ON UPDATE RESTRICT;

CREATE UNIQUE INDEX idx_articles_alias
    ON ww_articles (alias(150));

CREATE INDEX idx_articles_category_id
    ON ww_articles (category_id);

CREATE INDEX idx_articles_created_by
    ON ww_articles (created_by);

CREATE INDEX idx_articles_language
    ON ww_articles (language);

CREATE INDEX idx_articles_page_id
    ON ww_articles (page_id);

CREATE VIEW `ww_articles_view` AS SELECT * FROM `ww_articles`;
