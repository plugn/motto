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
         echo "del: ".$dir.$file."<br>";
                 }
               }
            }
            closedir($dh);
        }
     }
  }
?>