
<form action="" method="get">
file_exists ( <input type="text" name="url" value="<?php echo $_REQUEST['url'];?>"/> ) :

<?php

$cfd = file('tov_db.txt');
$brand = 'shure';
//*
if (!empty($cfd))
foreach ( $cfd as $idx => $str ) {
    $strlo = strtolower($str);
    if ( strpos(' '.$strlo, $brand)) {

        $_mr= array();
        $_m = explode('~',$strlo);
        foreach($_m as $mk=>$mv) $_mr[$mk]=trim($mv); // $_m[9] == 'shure'
        var_export($_mr);

// */
        /*
        прочая логика
        */

        $handle = fopen($_REQUEST['url'], "rb");
        $meta_data = stream_get_meta_data($handle);
        foreach($meta_data['wrapper_data'] as $header) {
          $header = strtolower($header);
          echo $header.'<br />';
          if (strpos($header,':')) {
              $_a = explode(':', $header);
              // var_export($_a);
              if ( strpos(' '.$_a[0],'content-type') &&  strpos(' '.$_a[1], 'image') )
                echo '<br/> &nbsp; <span style="color:maroon;">image detected</span><br/>';
              elseif ( strpos(' '.$_a[0],'content-length'))
                echo '<br/> &nbsp; <span style="color:maroon;">content-length:'.$_a[1].' </span><br/>';
          }
        }
        echo $handle;
    }
    // пишем $str в  'файл tov_db_'.(++$i).'.txt'
}

?>

<input type="submit" name=">>"/>
</form>

