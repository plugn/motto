# phpMyAdmin MySQL-Dump
# version 2.2.5
# http://phpwizard.net/phpMyAdmin/
# http://phpmyadmin.sourceforge.net/ (download page)
#
# Хост: localhost
# Время создания: Апр 21 2005 г., 08:05
# Версия сервера: 4.00.16
# Версия PHP: 4.3.4
# БД : `test`
# --------------------------------------------------------

#
# Структура таблицы `valve_tree_info`
#

CREATE TABLE `valve_tree_info` (
  `id` int(11) NOT NULL default '0',
  `name` varchar(250) NOT NULL default '',
  PRIMARY KEY  (`id`)
) TYPE=MyISAM;

#
# Дамп данных таблицы `valve_tree_info`
#

INSERT INTO `valve_tree_info` VALUES (1, 'food');
INSERT INTO `valve_tree_info` VALUES (2, 'fruit');
INSERT INTO `valve_tree_info` VALUES (3, 'meat');
INSERT INTO `valve_tree_info` VALUES (4, 'red');
INSERT INTO `valve_tree_info` VALUES (5, 'yellow');
INSERT INTO `valve_tree_info` VALUES (6, 'beef');
INSERT INTO `valve_tree_info` VALUES (7, 'pork');
INSERT INTO `valve_tree_info` VALUES (8, 'cherry');
INSERT INTO `valve_tree_info` VALUES (9, 'banana');
# --------------------------------------------------------

#
# Структура таблицы `valve_tree_info_tpl`
#

CREATE TABLE `valve_tree_info_tpl` (
  `id` int(11) NOT NULL default '0',
  `name` varchar(250) NOT NULL default '',
  PRIMARY KEY  (`id`)
) TYPE=MyISAM;

#
# Дамп данных таблицы `valve_tree_info_tpl`
#

INSERT INTO `valve_tree_info_tpl` VALUES (1, 'food');
INSERT INTO `valve_tree_info_tpl` VALUES (2, 'fruit');
INSERT INTO `valve_tree_info_tpl` VALUES (3, 'meat');
INSERT INTO `valve_tree_info_tpl` VALUES (4, 'red');
INSERT INTO `valve_tree_info_tpl` VALUES (5, 'yellow');
INSERT INTO `valve_tree_info_tpl` VALUES (6, 'beef');
INSERT INTO `valve_tree_info_tpl` VALUES (7, 'pork');
INSERT INTO `valve_tree_info_tpl` VALUES (8, 'cherry');
INSERT INTO `valve_tree_info_tpl` VALUES (9, 'banana');
INSERT INTO `valve_tree_info_tpl` VALUES (12, 'Strawberry 2');
INSERT INTO `valve_tree_info_tpl` VALUES (14, 'spiceberry');
# --------------------------------------------------------

#
# Структура таблицы `valve_tree_store`
#

CREATE TABLE `valve_tree_store` (
  `id` int(11) NOT NULL auto_increment,
  `parent_id` int(11) default '0',
  `lft` int(11) NOT NULL default '0',
  `rgt` int(11) NOT NULL default '0',
  PRIMARY KEY  (`id`)
) TYPE=MyISAM;

#
# Дамп данных таблицы `valve_tree_store`
#

INSERT INTO `valve_tree_store` VALUES (1, 0, 1, 18);
INSERT INTO `valve_tree_store` VALUES (2, 1, 2, 11);
INSERT INTO `valve_tree_store` VALUES (3, 1, 12, 17);
INSERT INTO `valve_tree_store` VALUES (4, 2, 3, 6);
INSERT INTO `valve_tree_store` VALUES (5, 2, 7, 10);
INSERT INTO `valve_tree_store` VALUES (6, 3, 13, 14);
INSERT INTO `valve_tree_store` VALUES (7, 3, 15, 16);
INSERT INTO `valve_tree_store` VALUES (8, 4, 4, 5);
INSERT INTO `valve_tree_store` VALUES (9, 5, 8, 9);
# --------------------------------------------------------

#
# Структура таблицы `valve_tree_store_tpl`
#

CREATE TABLE `valve_tree_store_tpl` (
  `id` int(11) NOT NULL auto_increment,
  `parent_id` int(11) default '0',
  `lft` int(11) NOT NULL default '0',
  `rgt` int(11) NOT NULL default '0',
  PRIMARY KEY  (`id`)
) TYPE=MyISAM;

#
# Дамп данных таблицы `valve_tree_store_tpl`
#

INSERT INTO `valve_tree_store_tpl` VALUES (1, 0, 1, 18);
INSERT INTO `valve_tree_store_tpl` VALUES (2, 1, 2, 11);
INSERT INTO `valve_tree_store_tpl` VALUES (3, 1, 12, 17);
INSERT INTO `valve_tree_store_tpl` VALUES (4, 2, 3, 6);
INSERT INTO `valve_tree_store_tpl` VALUES (5, 2, 7, 10);
INSERT INTO `valve_tree_store_tpl` VALUES (6, 3, 13, 14);
INSERT INTO `valve_tree_store_tpl` VALUES (7, 3, 15, 16);
INSERT INTO `valve_tree_store_tpl` VALUES (8, 4, 4, 5);
INSERT INTO `valve_tree_store_tpl` VALUES (9, 5, 8, 9);

