# phpMyAdmin MySQL-Dump
# version 2.2.5
# http://phpwizard.net/phpMyAdmin/
# http://phpmyadmin.sourceforge.net/ (download page)
#
# Хост: localhost
# Время создания: Дек 13 2005 г., 07:09
# Версия сервера: 4.00.16
# Версия PHP: 4.3.4
# БД : `test`
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

    