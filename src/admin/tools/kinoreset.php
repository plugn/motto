<?php

  require_once 'reset.php';

  $cache_dirs = array( '/disk2/kino/face/', '/disk2/kino/wap/' );

  foreach ($cache_dirs as $dir )
  {  recursive_delete( $dir, $dirs_also=false ); }


?>
