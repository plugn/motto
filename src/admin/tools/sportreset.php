<?php

  require_once ('reset.php');

  $cache_dirs = array( '/disk2/kino/nfosrv/health/sport/',  );

  foreach ($cache_dirs as $dir )
  {  recursive_delete( $dir, $dirs_also=false ); }


?>
