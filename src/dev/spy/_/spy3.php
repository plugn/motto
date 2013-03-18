
<form action="" method="get">
file_exists ( <input type="text" name="url" value="<?php echo $_REQUEST['url'];?>"/> ) :

<?php

function PicUrl_check( $url='' ) {
  if (empty($url)) return false;
  // $handle = fopen($_REQUEST['url'], "rb");
  echo 'PicUrl_check( '.$url.' ) <br/>';
  $handle = fopen( $url, "rb"); // echo $handle;
  $meta_data = stream_get_meta_data($handle);
  foreach($meta_data['wrapper_data'] as $header) {
    $header = strtolower($header); // echo $header.'<br />';
    if (strpos($header,':')) {
        $_a = explode(':', $header); // var_export($_a);
        if ( strpos(' '.$_a[0],'content-type') &&  strpos(' '.$_a[1], 'image') )
          echo '<br/> &nbsp; <span style="color:maroon;">image detected</span>';
        elseif ( strpos(' '.$_a[0],'content-length'))
          echo '<br/> &nbsp; <span style="color:maroon;">content-length:'.$_a[1].' </span>';
        echo '<br />';
    }
  }
}

$host_url = 'http://media-service.ru/';
$cfd = file('tov_db.txt');
$brand = 'Shure';

if (!empty($cfd))
foreach ( $cfd as $idx => $str ) {
    $strlo = strtolower($str);
    if ( strpos(' '.$str, $brand)) {

        $_mr = array();
        $_m  = explode('~',$str);

        foreach($_m as $mk=>$mv) $_mr[$mk]=trim($mv); // $_m[9] == 'shure'
        echo nl2br(var_export($_mr,1)).'<hr/>';

        $url_tmb = '';
        $url_pic = ''; //
        if( !empty($_mr[13]) )  {
          $url_tmb = $host_url.$_mr[13];
          PicUrl_check($url_tmb);
        }
        if( !empty($_mr[14]) )  {
          $url_pic = $host_url.$_mr[14];
          PicUrl_check($url_pic);
        }

    }


    // пишем $str в  'файл tov_db_'.(++$i).'.txt'
}

?>

<input type="submit" name=">>"/>
</form>

