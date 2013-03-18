<?php

class UtilityCore
{
/*
 *
 * @copyright jetstyle.ru
 * @author    Max L Dolgov <max at jetstyle dot ru>
 * @description
 *   &Born ( $_class, $params = array() )
 * @usage
 *   UtilityCore::Born("ArrayIterator", array(100, "fd fd d f"));
 *
 */


  var $_class, $params, $obj;

  function &Born ( $_class, $params, $err_supress = false )
  {
    $params_blank = implode(",", array_fill( 0, count($params), "null" ));
      $cmd = '$obj = &new '.$_class.'( '.$params_blank.' ) ;';
      if (!$err_supress) eval ( $cmd ); else @eval( $cmd );
    call_user_func_array ( array( &$obj, $_class ), @ $params );
    return $obj;
  }

  // identical to &Born() but returns A COPY
  function cBorn ( $_class, $params = array() )
  {
    $params_blank = implode(",", array_fill( 0, count($params), "null" ));
      $cmd = '$obj = &new '.$_class.'( '.$params_blank.' ) ;';
      eval ($cmd); // try '@' before in case of problems
    call_user_func_array ( array( &$obj, $_class ), @ $params );
    return $obj;
  }

  function getLocalPath( $FILE = __FILE__, $docRootDir = null )
  {
    $currDir = dirname( $FILE );
    if (is_null($docRootDir)) $docRootDir = $_SERVER["DOCUMENT_ROOT"];

    $currDir = str_replace("\\", "/", $currDir);
    $docRootDir = str_replace( "\\", "/", $docRootDir );
    $thePattern = "{"."(".preg_quote($docRootDir).")"."(.*)" ."}i";
    preg_match($thePattern, $currDir, $matches);
    // echo '<br /><br />'; echo "preg_match ( ".$thePattern.", ".$currDir.", matches) : "; var_export($matches);

    return isset($matches[2]) ? $matches[2] : false ;
  }

  function IncludeBuffered($filename, $ext="php")
  {
    $filename .= ".".$ext;
    ob_start();

    if (file_exists($filename))
        $__fullfilename = $filename;
    elseif (file_exists($_SERVER["DOCUMENT_ROOT"].$filename))
        $__fullfilename = $_SERVER["DOCUMENT_ROOT"].$filename;
    else
      return false; // die("404")  ;
    ob_start();
      include($__fullfilename);
      $output = ob_get_contents();
      if ($output===false)
         trigger_error("Problems (file: ".__FILE__.", line: ".__LINE__."): ".ob_get_contents(),
                        E_USER_WARNING);
    ob_end_clean();

    return  $output;
  }


  // primitive php-template parsing
  function txParse($tplname, &$domain )
  {
    if (!is_array($domain)) return false; else extract($domain);  // into local scope
    ob_start();
      eval( "?>" . file_get_contents($tplname). "<?php" );
      $content = ob_get_contents();
    ob_end_clean();

    return $content;
  }

/* usage: assign template scope vars to cfg before
 *      echo $this->txParse ( DOC_ROOT.PROJ_TPL_DIR."design.tpl.html", $cfg );  */

  // (с)pixel-apes.com
  function Translit ($text)
  {
    // Ќпж¬заимноќднозначный“ранслит
        $NpjMacros = array( "кс" => "x",
                            "вики" => "wiki", "вака" => "wacko", "швака" => "shwacko",
                            "веб" => "web", "ланс" => "lance", "кукуц" => "kukutz", "мендокуси" => "mendokusee",
                            "€ремко" => "iaremko", "николай" => "nikolai", "алексей" => "aleksey",
                            "анатолий" => "anatoly", "нпж" => "npj",
                          );
        $NpjLettersFrom = "абвгдезиклмнопрстуфцы";
        $NpjLettersTo   = "abvgdeziklmnoprstufcy";
        $NpjConsonant = "бвгджзйклмнпрстфхцчшщ";
        $NpjVowel = "аеЄиоуыэю€";
        $NpjBiLetters = array(
        "й" => "jj", "Є" => "jo", "ж" => "zh", "х" => "kh", "ч" => "ch",
        "ш" => "sh", "щ" => "shh", "э" => "je", "ю" => "ju", "€" => "ja",
        "ъ" => "", "ь" => "",
                              );

      $NpjCaps  = "јЅ¬√ƒ≈®∆«»… ЋћЌќѕ–—“”‘’÷„Ўў№ЏџЁёя";
      $NpjSmall = "абвгдеЄжзийклмнопрстуфхцчшщьъыэю€";


      $tag = $text;
      //insert _ between words
      $tag = preg_replace( "/\s+/ms", "_", $tag );
      $tag = str_replace( "::", "_", $tag );
      $tag = str_replace( "@", "_", $tag );
      $tag = preg_replace( "/\_+/ms", "_", $tag );

      $tag = strtolower( $tag );
      $tag = strtr( $tag, $NpjCaps, $NpjSmall );
      $tag = strtr( $tag, $NpjMacros );
      $tag = strtr( $tag, $NpjLettersFrom, $NpjLettersTo );
      $tag = strtr( $tag, $NpjBiLetters );

      $tag = preg_replace("/[^a-z0-9\-_]+/mi", "", $tag);

      return $tag;
  }

  // if all values of an array are empty and !=='0'
  function arrayClear($a) {   //static $r;
    foreach ( $a as $v) {   // keys existence must be ignored
      if ( is_array($v))                  arrayClear( $v);
      elseif ( !empty($v) || $v==='0' )   return false;
    }
    return true;
  }

  // (c) donald at design dot net
  function randstr($length = 16) {
    mt_srand((double)microtime()*10000);
    for($i=0;$i<$length;$i++) {
    $x = mt_rand(1,3);
    $str .= (($x == 1) ? chr(mt_rand(48,57)) : (($x == 2) ? chr(mt_rand(65,90)) : chr(mt_rand(97,122))));
    }
    return $str;
  }

  // !! requires <http> class
  function getURLContent( $url, $useragent='Quirk Spy v.1' ) {
    $fp = http::http_fopen($url, $useragent);
    if (!$fp)
      return trigger_error( $errstr ." #".$errno );
    else {
      $data = '';
      while (!feof($fp) && $response = fread($fp, 1024))
        $data .= $response;
      fclose($fp);
    }
    return $data;
  }


  function arrKeysPfxStrip($prefix, $arr_from) {
    foreach ($arr_from as $k=>$v)
      if (strpos(' '.$k, $prefix)==1)
        $r[str_replace($prefix, '', $k)] = $v;
    return $r;
  }

  function isExpired( $date, $period ) { // period: "-7 day", "+4 week", "next Thursday" - see strtotime() args
      $unixstamp = strtotime ( ((intval($period)<0)?'-':'+').$period,  strtotime( $date ) );
      $now       = strtotime ( 'now' ); // echo 'isExpired( '.$date.','.$period.' ) = '. ($now - $unixstamp).'<br />';
      if ( $now - $unixstamp > -1 )  return true;
      else                           return false;
  }

  function dbgMsg($desc, $_var, $prewrap=1, $htmlquot=0 ) {
    if (!$prewrap) {
      if ($htmlquot)
        $_var = htmlspecialchars($_var);
      $out = BR.$desc.$_var.BR;
    }
    else {
      $_var = var_export($_var,1);
      if ($htmlquot) $_var = htmlspecialchars($_var);
      $out = BR.$desc.'<pre>'.$_var.'</pre>'.BR;
    }
      echo $out; //      return $out;
  }

  // Simple function to replicate PHP 5 behaviour
  function microtime_float()
  {
     list($usec, $sec) = explode(" ", microtime());
     return ((float)$usec + (float)$sec);
  }

  // SQL date clause routine
  function sqlClausePeriod( $period=null, $unit=null, $key_date='NOW()', $wkdy_dif = 6 ) { // wkdy_dif на сколько дней от даты показывать по умолчанию
    $period_count = (!is_null( $period )) ? $period : $wkdy_dif ;
    $period_unit =  (!is_null($unit))?$unit:'DAY'; // DAY | MONTH | YEAR, etc.
    $period = ( ($period_count<0)?(-1*$period_count):$period_count ) ." ".$period_unit;
    $period_sign = ($period_count<0)?'-':'+';  // $this->dbgMsg('### period_count :: ', $period_count); $this->dbgMsg('### period :: ', $period);
    $clause = " ( ".$key_date." >= s.`date_start` and ".$key_date." <= s.`date_stop` or ".
              $key_date.$period_sign."INTERVAL ".$period." >= s.`date_start` and ".
              $key_date.$period_sign."INTERVAL ".$period." <= s.`date_stop` or ".
              "s.`date_start` >= ".$key_date." and s.`date_start` <= ".$key_date.$period_sign."INTERVAL ".$period." or ".
              "s.`date_stop`  >= ".$key_date." and s.`date_stop`  <= ".$key_date.$period_sign."INTERVAL ".$period." ) ";
    return $clause;
  }


  /* chrys at mytechjournal dot com; recursively delete files from a starting directory. */
  function recursive_delete( $dir, $dirs_also=false )
  {
     if (is_dir($dir)) {
        if ($dh = opendir($dir)) {
            while (($file = readdir($dh)) !== false ) {
               if( $file != "." && $file != ".." )
               {
                 if( is_dir( $dir . $file ) &&  $dirs_also)
                 {
                     UtilityCore::recursive_delete( $dir . $file . "/", $dirs_also );
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


  function QS_Render( $_RQ = null, $ignore_page_var=true ) {
    if ( is_null($_RQ) || !is_array($_RQ) ) $_RQ = $_GET;
    if ($ignore_page_var) unset ( $_RQ['page'] );
    $_qs = '';
    $_qsar = array();
    if ( !empty($_RQ) ) {
      foreach ( $_RQ as $k => $v )    $_qsar[] = $k.'='.urlencode($v);
      $_qs = implode('&', $_qsar );
    }

    return $_qs;

  }


  function SSIParse ( $htmlsrc, $markup=true ) {
    $ptrn = preg_quote('<!--').'\s*?'.preg_quote('#include').'\s*?'.'virtual'.'\s*?'.preg_quote('=').'\s*?'.
    '[\'"]?'.'(.*?)'.'[\'"]?'.'\s*?'.preg_quote('-->');
    $result = preg_replace( '{'.$ptrn.'}ims',
           ($markup?'<!-- [# ${1} : -->':'').
           '<?php $parts=parse_url("${1}");parse_str($parts["query"]);include($_SERVER["DOCUMENT_ROOT"].$parts["path"]); ?>'.
           ($markup?'<!-- / ${1} #] -->':''),
           $htmlsrc );
    global $memcached;
    ob_start();
    eval ("?>".$result."<?php ");
    $result = ob_get_contents();
    ob_end_clean();

    return $result;
  }


}  // EOC { UtilityCore}
?>