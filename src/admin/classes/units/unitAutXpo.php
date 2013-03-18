<?php

  require_once (dirname(__FILE__)."/unitAbstract.php" );
    require_once (dirname(__FILE__)."/unitUnit.php" );

        require_once 'EAR.php';
        require_once 'PEAR.php';
        require_once 'Net/Socket.php';
        require_once 'Net/URL.php';
        require_once 'Http/Request.php';

class unitAutXpo extends unitUnit
{

  var $import_marks = array( 'Toyota',  'Nissan',   'Mitsubishi',   'Honda',   'Mazda',  'Suzuki', 'Subaru', 'Isuzu', 'Daihatsu', );
  var $http_cfg = array(
            'method' => 'GET', // Method to use, GET, POST etc (string)
            'http' => '1.1',   // HTTP Version to use, 1.0 or 1.1 (string)
            'timeout' => 45.0, // Connection timeout in seconds (float)
            'allowRedirects' => true,// Whether to follow redirects or not (bool)
            'maxRedirects' => 3, // Max number of redirects to follow (integer)
            'useBrackets' => true, // Whether to append [] to array variable names (bool)
            'readTimeout' => array(90, 0), // Timeout for reading/writing data over the socket (array (sec, microsec))
      );

  function Handle() {  // $this->dbgMsg ( ' include paths: ', explode( PATH_SEPARATOR, get_include_path()) , $pre=1, $htmlqt=1 );
      $this->tpl->Set('PageBody', 'wassup');
      $this->PrepareDb();
      $this->Process();
      $this->tpl->Parse('autxpo.html:PageA', 'HTML:Body');

  }

  function PrepareDb() {
     $sql0 = 'truncate table auto_marks';  $this->db->query($sql0);
     $sql1 = 'truncate table auto_models'; $this->db->query($sql1);
  }

  function Process ()
  {   // $this->dbgMsg ( __CLASS__.'::'.__FUNCTION__.'() started ', '' , $pre=1, $htmlqt=0 );
      foreach ($this->import_marks as $mark) {
          $URL= 'http://'.strtolower( $mark ).'.auto.vl.ru/'; // 'http://daihatsu.auto.vl.ru/';
          $html  = $this->SyncRequest(  $URL, array() ); // $this->dbgMsg ( 'SyncRequest(  '.$URL.' ) response : ', $html , $pre=1, $htmlqt=1 );
          $rgxp0 =  'AUTO\.VL\.RU</h3> <table width="100%" border="0">'.'(.*?)'.
          '<table width="100%" class="text"><td width="25%"></td><td width="25%"></td><td width="25%"></td><td width="25%"></td><tr>'.
          '(.*?)'.'(</table>)';
          $rgxp0 = '{'.str_replace(' ','\s*?',$rgxp0).'}ims'; // $this->dbgMsg ( ' rgxp0: ', $rgxp0 , $pre=1, $htmlqt=1 );
          $m4r = preg_match($rgxp0, $html, $m4s);  // $this->dbgMsg ( ' m4 : #'.$mdx, $m4s[2] , $pre=1, $htmlqt=1 );
          $mdata = $m4s[2];
          $mdata = str_replace('</td>','', str_replace('<td>', '', str_replace('<tr>','',str_replace('</tr>', '', $mdata ) ) ) );
          $mdata = strtolower(preg_replace('{<a[^>]*?>\s*?([\'+-_\d\w\s]*?)\s*?</a>}ims', '${1}', $mdata));

          $models = explode("\n", $mdata);
          if ($models[0]=='') unset($models[0]);
          if ($models[$mlast_id=count($models)-1]=='') unset($models[$mlast_id]);

          $this->dbgMsg ( '<h3>'.$mark.'</h3> ', $models , $pre=1, $htmlqt=1 );

          $this->tpl->Append('PageBody', '<h3>'.$mark.'</h3> '. var_export( $models,1 ).BR );
          $this->SaveModelData($mark, $models);
      }
  }


  function SaveModelData($mark, $models) {
        $this->tbl->Init('auto_marks');
        $this->tbl->SetField( 'mark', strtolower($mark) );
        $this->tbl->Save( $mode='insert' );
        $sql = "select * from auto_marks where `mark` LIKE '".strtolower($mark)."'";
        $a = $this->db->sql2array($sql);
        $this->dbgMsg ( $sql.' %%% : ', $a , $pre=1, $htmlqt=0 );
        $mark_id = $a[0]['id'];

        $this->tbl->Init('auto_models');
        foreach ($models as $model) {
            $this->tbl->SetData( array('mark_id'=>$mark_id, 'model'=>$model) );
            $this->tbl->Save( $mode='insert' );
        }
  }


  function SyncRequest ( $URL, $sync_data, $http_cfg=null ) {
      if (is_null($http_cfg)) $http_cfg = $this->http_cfg;
      $rq = &new HTTP_Request($URL, $this->http_cfg );
      $rq_result = $rq->sendRequest();
      /* $this->dbgMsg('request : ', $rq->_buildRequest(), 1, 1);
         $this->dbgMsg('response : ', $rq->_response, 1, 1);
         $this->dbgMsg('request result : ', $rq_result, 0, 0); */
      return  $rq->getResponseBody();
  }

  function Xmlize($html) { // '<tag ></tag>' - is invalid markup for unpaired elements - cannot be processed as well
    $html = preg_replace('/<(br|nobr|hr|img|link|meta|col|input)([^>\/]*?)>/msiSA', "<\${1}\${2}/>", $html);
    $rgxpZ  = "{<(\w+)([^>]*)/>}ism";
    $rgxpX  = "<\${1}\${2}></\${1}>";
    $xhtml  = preg_replace($rgxpZ, $rgxpX, $html);

    return $xhtml;
  }


} // EOC { unitAutXpo }

?>