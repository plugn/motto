<?php

  // ob_start(); // comment to debug
  $app_include_paths = array_merge(  explode( PATH_SEPARATOR, get_include_path()),
                                       array(
                                             './../../.lib/',
                                             './../../.lib/PEAR/',
                                              './../../core/',
                                              './../../core/classes/',
                                              './../../core/templates/',
                                              './../../themes/default/',
                                          )
  );
  set_include_path( $incpaths=implode(PATH_SEPARATOR, $app_include_paths) ); // echo var_export( explode( PATH_SEPARATOR, get_include_path()), 1);

  require_once ( dirname(__FILE__)."/../../core/const.php" );
  require_once ( dirname(__FILE__)."/../../core/classes/Config.php" );
  require_once ( dirname(__FILE__)."/../../core/classes/DbAdapter.php" );
  require_once ( dirname(__FILE__)."/../../core/classes/CDatabase.php" );
  require_once ( dirname(__FILE__)."/../../core/classes/ThemePlayTE.php" );
  require_once ( dirname(__FILE__)."/../../core/classes/DbUtil.php" );

  require_once 'EAR.php';
  require_once 'PEAR.php';
  require_once 'Net/Socket.php';
  require_once 'Net/URL.php';
  require_once 'Http/Request.php';

  // environment composition
  $_db = &new DbAdapter( dirname(__FILE__)."/../../admin/config_db.php" );
  $db = $_db->StaticFactory(); unset($_db); // var_dump($this->db); // $this->dbutil = &new DbUtil($this->db);
  $db->query( 'set option character set cp1251_koi8' );
  $cfgte = &new Config(dirname(__FILE__)."/config_te.php");
  $_guide_ = array();
  $tpl = &new ThemePlayTE($cfgte, $_guide_ ); // var_export( $cfgte ) ;

  $tpl->Set( 'RuntimeOutput', $output_text );
  $tpl->Set( 'thisURL', "http://www.e1.ru".$_SERVER['SCRIPT_URL'] );

//  echo  ' $_POST : ' .var_export ( $_POST,1 ).BR;
  if (!empty($_POST)) {
      $http_cfg = array(
            'method' => 'POST', // Method to use, GET, POST etc (string)
            'http' => '1.0',   // HTTP Version to use, 1.0 or 1.1 (string)
            'timeout' => 45.0, // Connection timeout in seconds (float)
            'allowRedirects' => true,// Whether to follow redirects or not (bool)
            'maxRedirects' => 3, // Max number of redirects to follow (integer)
            'useBrackets' => true, // Whether to append [] to array variable names (bool)
            'readTimeout' => array(90, 0), // Timeout for reading/writing data over the socket (array (sec, microsec))
      );


      $URL = 'http://www.artlebedev.ru/kovodstvo/pkp/2/';
      $rq = &new HTTP_Request($URL, $http_cfg );
      $rq->addPostData( 'text',    $_REQUEST['text'] );

      $rq_result = $rq->sendRequest();
//  echo ( 'request : '); var_export($rq->_buildRequest(), 1);
      // $this->dbgMsg('response : ', $rq->_response, 1, 1); // $this->dbgMsg('request result : ', $rq_result, 1, 1);
      $html = $rq->getResponseBody();
// echo 'response body : ' . var_export($html, 1);

      $rgxp0 = '<table border="0" width="100%" cellspacing="0" cellpadding="0"><tr valign="top"> <td width="16%"><br /></td> <td width="67%">'.
               '(.*?)'.'</td> <td width="17%"><br /></td> </tr> </table>';
      $rgxp0 = '{'.str_replace(' ','\s*?',$rgxp0).'}ims'; // $this->dbgMsg ( ' rgxp0: ', $rgxp0 , $pre=1, $htmlqt=1 );
      // $rgxp0 = '{'.str_replace('>\s*?< ','><',$rgxp0).'}ims'; // $this->dbgMsg ( ' rgxp0: ', $rgxp0 , $pre=1, $htmlqt=1 );
      $m4r = preg_match($rgxp0, $html, $m4s);  // $this->dbgMsg ( ' m4 : #'.$mdx, $m4s[2] , $pre=1, $htmlqt=1 );
      $mdata = $m4s[1];
      $mdata  = str_replace( 'src="i/', 'src="http://www.artlebedev.ru/kovodstvo/pkp/2/i/', $mdata );
      $mdata  = '<nobr>'.preg_replace( '{<img[^>]*?probel[^>]*?>}ims', '</nobr>${0}'." \r\n<nobr>", $mdata ).'</nobr>';

      $tpl->Set('text', $_REQUEST['text']);
      $tpl->Set('textResult', $mdata);

  }

  // $output_text = ob_get_contents();  ob_end_clean(); // comment to debug
  $tpl->Parse('html.html:Form', 'CONTENT');
  echo $tpl->Parse('html.html:HTML');


?>