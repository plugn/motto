<?php

  error_reporting (E_ALL & ~E_NOTICE);
  // прошиваем локали, чтобы у нас всё работало с case-sensitivity
  setlocale(LC_CTYPE, array("ru_RU.CP1251","ru_SU.CP1251","ru_RU.KOI8-r","ru_RU","russian","ru_SU","ru"));
  header('P3P: CP="NOI ADM DEV PSAi COM NAV OUR OTRo STP IND DEM"');  // p3p: 3rd-party cookies IE secure



  // ob_start("ob_gzhandler");



  require('environment.php');

  $cfg = dirname(__FILE__)."/config.php";
  $guide = &new CmxGuide( $cfg );

  session_name( $guide->session_name );
  session_set_cookie_params ( $guide->session_ttl );
  session_start();

  $guide->Handle();




  // ob_end_flush();


?>