<pre>
<?php

  require_once (dirname(__FILE__)."/../../../rocket/core/classes/DBAL.php");
  require_once (dirname(__FILE__)."/../../classes/Config.php");
  require_once (dirname(__FILE__)."/../../classes/DBTree.php");

  $conf = &new Config(dirname(__FILE__)."/../../../config_db.php");
  $db = &new DBAL($conf, true);

  $db_tree = &new DBTree ( &$db,  "tree_store",  "tree_info" );

  // $a = $db_tree->getInfo( 52 );
  //var_export ( $a );

  //$a = $db_tree->getInfo( 2, "name" );
  // echo $a . "<br />";

  //$a = $db_tree->getNodeId_LR( null, 18 ); // это по правому индексу
  //$a = $db_tree->getNodeId_LR( 2 ); // это по левому
  // $a = $db_tree->getNodeId( 18 ); // это по  индексу
  // echo $a . "<br />";

  $db_tree->display(1);
  $id = 7;

  // echo "<br />path to node #".$id." ( '".$db_tree->getInfo($id, "name")."' ) : <br />";

  $path = $db_tree->renderPathToNode ( $id, "/", "name" );
  echo "<br />path to node #".$id.' : /'.$path.'<font size=+2>/&gt;'.$db_tree->getInfo($id, "name").'</font>'.BR.HR;

  $newNodeInfo = array("name"=>"sparrowberry");
  // $db_tree->addNode(7, $newNodeInfo );
  // $db_tree->dropNode( 11 );
  // echo BR;
  // $db_tree->moveSubTree( 1, 2 );
  echo BR.HR;
  $db_tree->display(1);

?>
</pre>
