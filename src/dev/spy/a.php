<?php

 echo "*******";


function Local_check( $file='' ) {
  $result = false;
  if (empty($file)) return false;

  if ( file_exists($file) && is_file($file) ) {
    $result = true;
  }
}

$host_url = $_SERVER['DOCUMENT_ROOT'].'/_notes/';
$cfd = file('tov_db.txt');
$brand = 'Shure';
var_export($cfd);

?>