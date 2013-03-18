<?php

  // ob_start(); // comment to debug

  require_once (dirname(__FILE__)."/../../core/const.php");
  require_once (dirname(__FILE__)."/../../core/classes/Config.php");
  require_once (dirname(__FILE__)."/../../core/classes/DbAdapter.php");
  require_once (dirname(__FILE__)."/../../core/classes/CDatabase.php");
  require_once (dirname(__FILE__)."/../../core/classes/ThemePlayTE.php");
  require_once (dirname(__FILE__)."/../../core/classes/DbUtil.php");
  // require_once (dirname(__FILE__)."/../classes/DBTree.php");
  // require_once (dirname(__FILE__)."/../classes/DBTreeX.php");

  // environment composition
  $_db = &new DbAdapter( dirname(__FILE__)."/../../admin/config_db.php" );
  $db = $_db->StaticFactory(); unset($_db); // var_dump($this->db); // $this->dbutil = &new DbUtil($this->db);
  $db->query( 'set option character set cp1251_koi8' );
  $cfgte = &new Config(dirname(__FILE__)."/config_te.php");
  $_guide_ = array();
  $tpl = &new ThemePlayTE($cfgte, $_guide_ ); // var_export( $cfgte ) ;


  // $output_text = ob_get_contents();  ob_end_clean(); // comment to debug
  $tpl->Set( 'RuntimeOutput', $output_text );


  echo $tpl->Parse('html.html');


?>