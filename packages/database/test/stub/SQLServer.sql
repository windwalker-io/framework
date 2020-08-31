create table ww_categories
(
	id int identity,
	title varchar(255),
	ordering int,
	params varchar(max)
);

SET IDENTITY_INSERT ww_categories ON;

INSERT INTO ww_categories (id, title, ordering, params) VALUES (1, 'Foo', 1, '');
INSERT INTO ww_categories (id, title, ordering, params) VALUES (2, 'Bar', 2, '');

SET IDENTITY_INSERT ww_categories OFF;

create table ww_flower
(
	id int identity,
	catid int DEFAULT 0,
	title varchar(255),
	meaning varchar(max),
	ordering int,
	state tinyint,
	params varchar(max)
);

SET IDENTITY_INSERT ww_flower ON;

INSERT INTO ww_flower (id, catid, title, meaning, ordering, state, params) VALUES (1, 2, 'Alstroemeria', 'aspiring', 1, 0, '');
INSERT INTO ww_flower (id, catid, title, meaning, ordering, state, params) VALUES (2, 2, 'Amaryllis', 'dramatic', 2, 0, '');
INSERT INTO ww_flower (id, catid, title, meaning, ordering, state, params) VALUES (3, 1, 'Anemone', 'fragile', 3, 0, '');
INSERT INTO ww_flower (id, catid, title, meaning, ordering, state, params) VALUES (4, 1, 'Apple Blossom', 'promis', 4, 1, '');
INSERT INTO ww_flower (id, catid, title, meaning, ordering, state, params) VALUES (5, 2, 'Aster', 'contentment', 5, 1, '');
INSERT INTO ww_flower (id, catid, title, meaning, ordering, state, params) VALUES (6, 2, 'Azalea', 'abundance', 6, 0, '');
INSERT INTO ww_flower (id, catid, title, meaning, ordering, state, params) VALUES (7, 1, 'Baby''s Breath', 'festivity', 7, 1, '');
INSERT INTO ww_flower (id, catid, title, meaning, ordering, state, params) VALUES (8, 2, 'Bachelor Button', 'anticipation', 8, 0, '');
INSERT INTO ww_flower (id, catid, title, meaning, ordering, state, params) VALUES (9, 2, 'Begonia', 'deep thoughts', 9, 0, '');
INSERT INTO ww_flower (id, catid, title, meaning, ordering, state, params) VALUES (10, 2, 'Black-Eyed Susan', 'encouragement', 10, 0, '');
INSERT INTO ww_flower (id, catid, title, meaning, ordering, state, params) VALUES (11, 1, 'Camellia', 'graciousness', 11, 1, '');
INSERT INTO ww_flower (id, catid, title, meaning, ordering, state, params) VALUES (12, 1, 'Carnation', '', 12, 1, '');
INSERT INTO ww_flower (id, catid, title, meaning, ordering, state, params) VALUES (13, 1, 'pink', 'gratitude', 13, 1, '');
INSERT INTO ww_flower (id, catid, title, meaning, ordering, state, params) VALUES (14, 1, 'red', 'flashy', 14, 1, '');
INSERT INTO ww_flower (id, catid, title, meaning, ordering, state, params) VALUES (15, 1, 'striped', 'refusal', 15, 1, '');
INSERT INTO ww_flower (id, catid, title, meaning, ordering, state, params) VALUES (16, 1, 'white', 'remembrance', 16, 1, '');
INSERT INTO ww_flower (id, catid, title, meaning, ordering, state, params) VALUES (17, 1, 'yellow', 'cheerful', 17, 1, '');
INSERT INTO ww_flower (id, catid, title, meaning, ordering, state, params) VALUES (18, 1, 'Chrysanthemum', '', 18, 0, '');
INSERT INTO ww_flower (id, catid, title, meaning, ordering, state, params) VALUES (19, 2, 'bronze', 'excitement', 19, 1, '');
INSERT INTO ww_flower (id, catid, title, meaning, ordering, state, params) VALUES (20, 1, 'white', 'truth', 20, 0, '');
INSERT INTO ww_flower (id, catid, title, meaning, ordering, state, params) VALUES (21, 1, 'red', 'sharing', 21, 1, '');
INSERT INTO ww_flower (id, catid, title, meaning, ordering, state, params) VALUES (22, 1, 'yellow', 'secret admirer', 22, 0, '');
INSERT INTO ww_flower (id, catid, title, meaning, ordering, state, params) VALUES (23, 1, 'Cosmos', 'peaceful', 23, 0, '');
INSERT INTO ww_flower (id, catid, title, meaning, ordering, state, params) VALUES (24, 1, 'Crocus', 'foresight', 24, 0, '');
INSERT INTO ww_flower (id, catid, title, meaning, ordering, state, params) VALUES (25, 1, 'Daffodil', 'chivalry', 25, 1, '');
INSERT INTO ww_flower (id, catid, title, meaning, ordering, state, params) VALUES (26, 2, 'Delphinium', 'boldness', 26, 0, '');
INSERT INTO ww_flower (id, catid, title, meaning, ordering, state, params) VALUES (27, 2, 'Daisy', 'innocence', 27, 0, '');
INSERT INTO ww_flower (id, catid, title, meaning, ordering, state, params) VALUES (28, 1, 'Freesia', 'spirited', 28, 0, '');
INSERT INTO ww_flower (id, catid, title, meaning, ordering, state, params) VALUES (29, 2, 'Forget-Me-Not', 'remember me forever', 29, 1, '');
INSERT INTO ww_flower (id, catid, title, meaning, ordering, state, params) VALUES (30, 2, 'Gardenia', 'joy', 30, 1, '');
INSERT INTO ww_flower (id, catid, title, meaning, ordering, state, params) VALUES (31, 2, 'Geranium', 'comfort', 31, 1, '');
INSERT INTO ww_flower (id, catid, title, meaning, ordering, state, params) VALUES (32, 2, 'Ginger', 'proud', 32, 1, '');
INSERT INTO ww_flower (id, catid, title, meaning, ordering, state, params) VALUES (33, 2, 'Gladiolus', 'strength of character', 33, 0, '');
INSERT INTO ww_flower (id, catid, title, meaning, ordering, state, params) VALUES (34, 1, 'Heather', 'solitude', 34, 1, '');
INSERT INTO ww_flower (id, catid, title, meaning, ordering, state, params) VALUES (35, 2, 'Hibiscus', 'delicate beauty', 35, 0, '');
INSERT INTO ww_flower (id, catid, title, meaning, ordering, state, params) VALUES (36, 1, 'Holly', 'domestic happiness', 36, 1, '');
INSERT INTO ww_flower (id, catid, title, meaning, ordering, state, params) VALUES (37, 1, 'Hyacinth', 'sincerity', 37, 0, '');
INSERT INTO ww_flower (id, catid, title, meaning, ordering, state, params) VALUES (38, 1, 'Hydrangea', 'perseverance', 38, 1, '');
INSERT INTO ww_flower (id, catid, title, meaning, ordering, state, params) VALUES (39, 2, 'Iris', 'inspiration', 39, 0, '');
INSERT INTO ww_flower (id, catid, title, meaning, ordering, state, params) VALUES (40, 1, 'Ivy', 'fidelity', 40, 0, '');
INSERT INTO ww_flower (id, catid, title, meaning, ordering, state, params) VALUES (41, 1, 'Jasmine', 'grace and elegance', 41, 0, '');
INSERT INTO ww_flower (id, catid, title, meaning, ordering, state, params) VALUES (42, 1, 'Larkspur', 'beautiful spirit', 42, 0, '');
INSERT INTO ww_flower (id, catid, title, meaning, ordering, state, params) VALUES (43, 1, 'Lavender', 'distrust', 43, 0, '');
INSERT INTO ww_flower (id, catid, title, meaning, ordering, state, params) VALUES (44, 1, 'Lilac', 'first love', 44, 1, '');
INSERT INTO ww_flower (id, catid, title, meaning, ordering, state, params) VALUES (45, 1, 'Lily', '', 45, 0, '');
INSERT INTO ww_flower (id, catid, title, meaning, ordering, state, params) VALUES (46, 1, 'Calla', 'regal', 46, 1, '');
INSERT INTO ww_flower (id, catid, title, meaning, ordering, state, params) VALUES (47, 1, 'Casablanca', 'celebration', 47, 1, '');
INSERT INTO ww_flower (id, catid, title, meaning, ordering, state, params) VALUES (48, 2, 'Day', 'enthusiasm', 48, 1, '');
INSERT INTO ww_flower (id, catid, title, meaning, ordering, state, params) VALUES (49, 1, 'Stargazer', 'ambition', 49, 0, '');
INSERT INTO ww_flower (id, catid, title, meaning, ordering, state, params) VALUES (50, 1, 'Lisianthus', 'calming', 50, 1, '');
INSERT INTO ww_flower (id, catid, title, meaning, ordering, state, params) VALUES (51, 2, 'Magnolia', 'dignity', 51, 0, '');
INSERT INTO ww_flower (id, catid, title, meaning, ordering, state, params) VALUES (52, 1, 'Marigold', 'desire for riches', 52, 0, '');
INSERT INTO ww_flower (id, catid, title, meaning, ordering, state, params) VALUES (53, 1, 'Nasturtium', 'patriotism', 53, 0, '');
INSERT INTO ww_flower (id, catid, title, meaning, ordering, state, params) VALUES (54, 2, 'Orange Blossom', 'fertility', 54, 1, '');
INSERT INTO ww_flower (id, catid, title, meaning, ordering, state, params) VALUES (55, 1, 'Orchid', 'delicate beauty', 55, 0, '');
INSERT INTO ww_flower (id, catid, title, meaning, ordering, state, params) VALUES (56, 2, 'Pansy', 'loving thoughts', 56, 0, '');
INSERT INTO ww_flower (id, catid, title, meaning, ordering, state, params) VALUES (57, 1, 'Passion flower', 'passion', 57, 1, '');
INSERT INTO ww_flower (id, catid, title, meaning, ordering, state, params) VALUES (58, 2, 'Peony', 'healing', 58, 1, '');
INSERT INTO ww_flower (id, catid, title, meaning, ordering, state, params) VALUES (59, 2, 'Poppy', 'consolation', 59, 0, '');
INSERT INTO ww_flower (id, catid, title, meaning, ordering, state, params) VALUES (60, 1, 'Queen Anne''s Lace', 'delicate femininity', 60, 0, '');
INSERT INTO ww_flower (id, catid, title, meaning, ordering, state, params) VALUES (61, 1, 'Ranunculus', 'radiant', 61, 1, '');
INSERT INTO ww_flower (id, catid, title, meaning, ordering, state, params) VALUES (62, 1, 'Rhododendron', 'beware', 62, 0, '');
INSERT INTO ww_flower (id, catid, title, meaning, ordering, state, params) VALUES (63, 1, 'Rose', '', 63, 1, '');
INSERT INTO ww_flower (id, catid, title, meaning, ordering, state, params) VALUES (64, 2, 'pink', 'admiration/appreciation', 64, 0, '');
INSERT INTO ww_flower (id, catid, title, meaning, ordering, state, params) VALUES (65, 2, 'red', 'passionate love', 65, 1, '');
INSERT INTO ww_flower (id, catid, title, meaning, ordering, state, params) VALUES (66, 1, 'red & white', 'unity', 66, 1, '');
INSERT INTO ww_flower (id, catid, title, meaning, ordering, state, params) VALUES (67, 1, 'white', 'purity', 67, 1, '');
INSERT INTO ww_flower (id, catid, title, meaning, ordering, state, params) VALUES (68, 1, 'yellow', 'friendship', 68, 1, '');
INSERT INTO ww_flower (id, catid, title, meaning, ordering, state, params) VALUES (69, 2, 'Snapdragon', 'presumptuous', 69, 1, '');
INSERT INTO ww_flower (id, catid, title, meaning, ordering, state, params) VALUES (70, 1, 'Star of Bethlehem', 'hope', 70, 1, '');
INSERT INTO ww_flower (id, catid, title, meaning, ordering, state, params) VALUES (71, 1, 'Stephanotis', 'good luck', 71, 1, '');
INSERT INTO ww_flower (id, catid, title, meaning, ordering, state, params) VALUES (72, 2, 'Statice', 'success', 72, 1, '');
INSERT INTO ww_flower (id, catid, title, meaning, ordering, state, params) VALUES (73, 2, 'Sunflower', 'adoration', 73, 0, '');
INSERT INTO ww_flower (id, catid, title, meaning, ordering, state, params) VALUES (74, 1, 'Sweetpea', 'shyness', 74, 1, '');
INSERT INTO ww_flower (id, catid, title, meaning, ordering, state, params) VALUES (75, 2, 'Tuberose', 'pleasure', 75, 1, '');
INSERT INTO ww_flower (id, catid, title, meaning, ordering, state, params) VALUES (76, 1, 'Tulip', '', 76, 1, '');
INSERT INTO ww_flower (id, catid, title, meaning, ordering, state, params) VALUES (77, 2, 'pink', 'caring', 77, 1, '');
INSERT INTO ww_flower (id, catid, title, meaning, ordering, state, params) VALUES (78, 1, 'purple', 'royalty', 78, 0, '');
INSERT INTO ww_flower (id, catid, title, meaning, ordering, state, params) VALUES (79, 1, 'red', 'declaration of love', 79, 1, '');
INSERT INTO ww_flower (id, catid, title, meaning, ordering, state, params) VALUES (80, 1, 'white', 'forgiveness', 80, 0, '');
INSERT INTO ww_flower (id, catid, title, meaning, ordering, state, params) VALUES (81, 1, 'yellow', 'hopelessly in love', 81, 0, '');
INSERT INTO ww_flower (id, catid, title, meaning, ordering, state, params) VALUES (82, 2, 'Violet', 'faithfulness', 82, 1, '');
INSERT INTO ww_flower (id, catid, title, meaning, ordering, state, params) VALUES (83, 2, 'Wisteria', 'steadfast', 83, 0, '');
INSERT INTO ww_flower (id, catid, title, meaning, ordering, state, params) VALUES (84, 1, 'Yarrow', 'good health', 84, 1, '');
INSERT INTO ww_flower (id, catid, title, meaning, ordering, state, params) VALUES (85, 2, 'Zinnia', 'thoughts of friends', 85, 1, '');

SET IDENTITY_INSERT ww_flower OFF;

create table ww_nestedsets
(
	id int identity,
	parent_id bigint,
	lft int,
	rgt int,
	level bigint,
	title varchar(255),
	alias varchar(255),
	access text,
	path varchar(255)
);

-- SET IDENTITY_INSERT ww_nestedsets ON;
