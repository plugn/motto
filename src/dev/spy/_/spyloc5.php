<form action="" method="get">
file_exists ( <input type="text" name="url" value="<?php echo $_REQUEST['url'];?>"/> ) :

<?php

// error_reporting (8);

function Local_check( $_file='' ) {
  $result = false;
  if (empty($_file)) return false;  // echo 'Local_check( '.$_file.' ) : ';
  if ( $f1=file_exists($_file) && $f2=is_file($_file) ) {
    $result = true;
  }  //echo '['.$f1.' && '.$f2.'] <br/> ';
  return $result;
}

$host_url = $_SERVER['DOCUMENT_ROOT'].'/_notes/';
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
          Local_check($url_tmb);
        } else {
          $picode = strtolower(str_replace('/','',str_replace(' ','',$_mr[4])));
          $protmb = $host_url.'imgproduct/microphone/'.$brand.'/tmb/'.$brand.'.'.$picode.'.jpg';
          echo '<br/> &nbsp; <span style="color:green;">proposal thumbnail :'.$protmb.'</span><br />';
          if( Local_check($protmb) ) {
            echo '<br/> &nbsp; <span style="color:maroon;">proposal thumbnail found :'.$protmb.'</span><br />';
          }
        }
        if( !empty($_mr[14]) )  {
          $url_pic = $host_url.$_mr[14];
          Local_check($url_pic);
        } else {
          $picode = strtolower(str_replace('/','',str_replace(' ','',$_mr[4])));
          $propics = array(
              $host_url.'imgproduct/microphone/'.$brand.'/'.$brand.'.'.$picode.'.big.jpg',
              $host_url.'imgproduct/microphone/'.$brand.'/'.$brand.'.'.$picode.'.big.gif',
              $host_url.'imgproduct/microphone/'.$brand.'/'.$brand.'.'.$picode.'.jpg',
              $host_url.'imgproduct/microphone/'.$brand.'/'.$brand.'.'.$picode.'.gif',
          );
          $is_pic = false;
          $picfnd = array();
          echo '<br/> &nbsp; <span style="color:green;">proposal images :'.implode(', ',$propics).'</span><br />';
          foreach ($propics as $propic) {

            $is_pic = Local_check($propic);
            echo ' Local check (((' . $propic.'))) : '. $is_pic;

            if ( $is_pic ) {
              $picfnd[] = $propic;
              echo '<br/> &nbsp; <span style="color:maroon;">proposal images found {'.count($picfnd).'}: '.implode(', ',$picfnd).'</span><br />';
            }
            // if (PicUrl_check($propic)) { $is_pic = true; $picfnd[]=$propic; }


          }
        }

    }
    // пишем $str в  'файл tov_db_'.(++$i).'.txt'
}

?>

<input type="submit" name=">>"/>
</form>