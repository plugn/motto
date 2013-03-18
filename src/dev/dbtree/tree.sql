
CREATE TABLE `tree_info` (
  `id` int(11) NOT NULL default '0',
  `name` varchar(250) NOT NULL default '',
  PRIMARY KEY  (`id`)
) TYPE=MyISAM;

#
# Дамп данных таблицы `tree_info`
#

INSERT INTO `tree_info` VALUES (1, 'food');
INSERT INTO `tree_info` VALUES (2, 'fruit');
INSERT INTO `tree_info` VALUES (3, 'meat');
INSERT INTO `tree_info` VALUES (4, 'red');
INSERT INTO `tree_info` VALUES (5, 'yellow');
INSERT INTO `tree_info` VALUES (6, 'beef');
INSERT INTO `tree_info` VALUES (7, 'pork');
INSERT INTO `tree_info` VALUES (8, 'cherry');
INSERT INTO `tree_info` VALUES (9, 'banana');
# --------------------------------------------------------

CREATE TABLE `tree_store` (
  `id` int(11) NOT NULL auto_increment,
  `parent_id` int(11) default '0',
  `lft` int(11) NOT NULL default '0',
  `rgt` int(11) NOT NULL default '0',
  PRIMARY KEY  (`id`)
) TYPE=MyISAM;

#
# Дамп данных таблицы `tree_store`
#

INSERT INTO `tree_store` VALUES (1, 0, 1, 18);
INSERT INTO `tree_store` VALUES (2, 1, 2, 11);
INSERT INTO `tree_store` VALUES (3, 1, 12, 17);
INSERT INTO `tree_store` VALUES (4, 2, 3, 6);
INSERT INTO `tree_store` VALUES (5, 2, 7, 10);
INSERT INTO `tree_store` VALUES (6, 3, 13, 14);
INSERT INTO `tree_store` VALUES (7, 3, 15, 16);
INSERT INTO `tree_store` VALUES (8, 4, 4, 5);
INSERT INTO `tree_store` VALUES (9, 5, 8, 9);
# --------------------------------------------------------
