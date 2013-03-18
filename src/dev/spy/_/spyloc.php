<form action="" method="get">
file_exists ( <input type="text" name="url" value="<?php echo $_REQUEST['url'];?>"/> ) :

<?php

$global_count = 0;
$record_count = 0;

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
$brand = 'Shure';// $brand = 'AKG';

if (!empty($cfd))
foreach ( $cfd as $idx => $str ) {
    $strlo = strtolower($str);
    $record_found = false;
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
          $protmb = 'imgproduct/microphone/'.$brand.'/tmb/'.$brand.'.'.$picode.'.jpg'; // тамбнейлы - только .jpg
          // echo '<br/> &nbsp; <span style="color:green;">proposal thumbnail :'.$protmb.'</span><br />';
          $is_tmb = Local_check($host_url.$protmb);
          if( $is_tmb ) {
            $record_found = true;
            $global_count++;
            echo '<br/> &nbsp; <span style="color:maroon;">thumbnail found : '.$protmb.'</span><br />';
            echo 'image : <img title="/_notes/'.$protmb.'" alt="/_notes/'.$protmb.'" src="/_notes/'.$protmb.'"/><br/>';
          }
        }
        if( !empty($_mr[14]) )  {
          $url_pic = $host_url.$_mr[14];
          Local_check($url_pic);
        } else {
          $picode = strtolower(str_replace('/','',str_replace(' ','',$_mr[4])));
          $propics = array(

              'imgproduct/microphone/'.$brand.'/'.$brand.'.'.$picode.'.jpg',
              'imgproduct/microphone/'.$brand.'/'.$brand.'.'.$picode.'.JPG',

              'imgproduct/microphone/'.$brand.'/'.$brand.'.'.$picode.'.jpeg',
              'imgproduct/microphone/'.$brand.'/'.$brand.'.'.$picode.'.JPEG',

              'imgproduct/microphone/'.$brand.'/'.$brand.'.'.$picode.'.jpe',
              'imgproduct/microphone/'.$brand.'/'.$brand.'.'.$picode.'.JPE',

              'imgproduct/microphone/'.$brand.'/'.$brand.'.'.$picode.'.gif',
              'imgproduct/microphone/'.$brand.'/'.$brand.'.'.$picode.'.GIF',


              'imgproduct/microphone/'.$brand.'/'.$brand.'.'.$picode.'.big.jpg',
              'imgproduct/microphone/'.$brand.'/'.$brand.'.'.$picode.'.big.JPG',

              'imgproduct/microphone/'.$brand.'/'.$brand.'.'.$picode.'.big.jpeg',
              'imgproduct/microphone/'.$brand.'/'.$brand.'.'.$picode.'.big.JPEG',

              'imgproduct/microphone/'.$brand.'/'.$brand.'.'.$picode.'.big.jpe',
              'imgproduct/microphone/'.$brand.'/'.$brand.'.'.$picode.'.big.JPE',

              'imgproduct/microphone/'.$brand.'/'.$brand.'.'.$picode.'.big.gif',
              'imgproduct/microphone/'.$brand.'/'.$brand.'.'.$picode.'.big.GIF',
          );
          $is_pic = false;
          $picfnd = array();
          // echo '<br/> &nbsp; <span style="color:green;">proposal images :'.implode(', ',$propics).'</span><br />';
          foreach ($propics as $propic) {

            $is_pic = Local_check($host_url.$propic);
            // echo ' Local check { /' . $propic.' } : '. $is_pic. '<br />';

            if ( $is_pic ) {
              $record_found = true;
              $global_count++;
              $picfnd[] = $propic;
              echo 'image : <img title="/_notes/'.$propic.'" alt="/_notes/'.$propic.'" src="/_notes/'.$propic.'"/><br/>';
            }
            // if (PicUrl_check($propic)) { $is_pic = true; $picfnd[]=$propic; }
          }
          if (!empty($picfnd))
              echo '<br/> &nbsp; <span style="color:maroon;">images found {'.count($picfnd).'}: '.implode(', ',$picfnd).'</span><br />';


        }

    }


    if ($record_found) $record_count++;

    // пишем $str в  'файл tov_db_'.(++$i).'.txt'
}
echo '<br/><em>Подобрано '.$global_count.' изображений для '.$record_count. ' позиций брэнда "'.$brand.'"'.'</em>.<br />'
?>

<input type="submit" name=">>"/>
</form>