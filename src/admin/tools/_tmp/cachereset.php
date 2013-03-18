<?php

  /* cache reset Utility for kino afisha */

  function recursive_delete( $dir, $dirs_also=false )
  {
     if (is_dir($dir)) {
        if ($dh = opendir($dir)) {
            while (($file = readdir($dh)) !== false ) {
               if( $file != "." && $file != ".." )
               {
                 if( is_dir( $dir . $file ) &&  $dirs_also)
                 {
                     recursive_delete( $dir . $file . "/", $dirs_also );
                     rmdir( $dir . $file );
                 }
                 else
                 {
                     unlink( $dir . $file );
                 }
               }
            }
            closedir($dh);
        }
     }
  }

  $cache_dirs = array( '/disk2/kino/face/', '/disk2/kino/wap/' );

  foreach ($cache_dirs as $dir )
  {  recursive_delete( $dir . $file . "/", $dirs_also=false ); }


?>