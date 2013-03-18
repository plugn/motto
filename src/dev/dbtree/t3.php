<?php

  ob_start(); // comment to debug

  require_once (dirname(__FILE__)."/../../core/const.php");
  require_once (dirname(__FILE__)."/../../core/classes/Config.php");
  require_once (dirname(__FILE__)."/../../core/classes/DbAdapter.php");
  require_once (dirname(__FILE__)."/../../core/classes/CDatabase.php");
  require_once (dirname(__FILE__)."/../../core/classes/ThemePlayTE.php");
  require_once (dirname(__FILE__)."/../../core/classes/DbUtil.php");
  require_once (dirname(__FILE__)."/../classes/DBTree.php");
  require_once (dirname(__FILE__)."/../classes/DBTreeX.php");
  /* добавляем пути к текущему комплекту
  $app_include_paths = array_merge(  explode( PATH_SEPARATOR, get_include_path()),
                                      dirname(__FILE__),
                                      array(  '/../../core/templates/', ));
  set_include_path( $incpaths=implode(PATH_SEPARATOR, $app_include_paths) );  */

  $_db = &new DbAdapter( dirname(__FILE__)."/../../admin/config_db.php" );
  $db = $_db->StaticFactory(); unset($_db); // var_dump($this->db); // $this->dbutil = &new DbUtil($this->db);
  $db->query( 'set option character set cp1251_koi8' );

  $cfgte = &new Config(dirname(__FILE__)."/config_te.php");
  $_guide_ = array();
  $tpl = &new ThemePlayTE($cfgte, $_guide_ ); // var_export( $cfgte ) ;

  $db_tree = &new DBTreeX ( $db,  "tree_store",  "tree_info", array('treeX'=>20, 'treeY'=>300) );
  $db_tree->tpl = &$tpl;
  $db_tree->tpf = 'tree.html';
  if ($_REQUEST['addwhat'] || isset($_REQUEST['drop']) ) {
      // echo BR.'_REQUEST : ';var_export($_REQUEST); // 'addwhat' => 'tagtag', 'addto' => 'beef', 'addto_id' => '6',
      if ($_REQUEST['proc_mode']=='add')
          $db_tree->addNode( $_REQUEST['addto_id'], array( "name" => $_REQUEST['addwhat']) );
      if ($_REQUEST['proc_mode']=='edit' && $_REQUEST['addto_id'])
          $db_tree->setInfo( $_REQUEST['addto_id'], array( "name" => $_REQUEST['addwhat']) );
      if ( $_REQUEST['drop'] && is_numeric($_REQUEST['drop']) )
          $db_tree->dropNode($_REQUEST['drop']);

      if (!headers_sent())
          header ("Location: http://e1.ru".$_SERVER['SCRIPT_URL']);
      else echo BR.'can not redirect to http://e1.ru'.$_SERVER['SCRIPT_URL']. ' because of output started earlier ^^ '.BR;
  }

  if (isset($_REQUEST['itemId']) && isset($_REQUEST['bindTo']) ) {
      $db_tree->moveSubTree( $_REQUEST['itemId'], $_REQUEST['bindTo'] );
      if (!headers_sent())
          header ("Location: http://".$_SERVER['HTTP_HOST'].$_SERVER['SCRIPT_URL']);
      else echo BR.'can not redirect to'.$_SERVER['HTTP_HOST'].$_SERVER['SCRIPT_URL']. ' because of output started earlier ^^ '.BR;
  }


  $tpl->Set( 'thisURL', "http://www.e1.ru".$_SERVER['SCRIPT_URL'] );
  $tpl->Set( 'TreeBody', $db_tree->display(1));
  $id = 8;
  $path = $db_tree->renderPathToNode( $id, "/", "name" );
  $tpl->Set( 'pathToNode', $id.' : /'.$path.'<font size="+1">/&gt;'.$db_tree->getInfo($id, "name").'</font>' );

  $output_text = ob_get_contents();  ob_end_clean(); // comment to debug
  $tpl->Set( 'RuntimeOutput', $output_text );


  echo $tpl->Parse('html.html');
  // $newNodeInfo = array("name"=>"sparrowberry");
  // $db_tree->addNode(6, $newNodeInfo );
  // $db_tree->dropNode( 11 );
  // echo BR;
  // $db_tree->moveSubTree( 12, 5 );
  // echo BR.HR;
  // $db_tree->display(1);

?>
