<?php

/*
 *  окружение
 */
  // добавляем пути к текущему комплекту
  $app_include_paths = array_merge(  explode( PATH_SEPARATOR, get_include_path()),
                                     array(
                                             './../.lib/',
                                             './../.lib/PEAR/',
                                             './../core/',
                                             './../core/classes/',
                                             './../core/templates/',
                                             './../themes/face/',
                                             '/web/relcom/e1.ru/www',
                                          )
  );
  set_include_path( $incpaths=implode(PATH_SEPARATOR, $app_include_paths) );

  require_once("const.php");
  require_once("UtilityCore.php");
  require_once("classes/Config.php");   // это такой класс,не путать с простым конфигом!!!
  require_once("Guide.php");
  require_once("DbAdapter.php");
  require_once("CDatabase.php");
  require_once("DbUtil.php");
  require_once("ThemePlayTE.php");
  require_once("txFormField.php");
  require_once("txCache.php");
  require_once("classes/CmxGuide.php");




?>