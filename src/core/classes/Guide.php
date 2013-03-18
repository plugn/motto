<?php

  require_once ("nocache.php");

class Guide extends Config
{
  var $routines = array();

  function Guide( $config ) { //   echo ' guide::guide() ';
    $this->Config( $config );
    $this->_Init();
  }

  function _Init() {
    $this->_tmr_start = UtilityCore::microtime_float();
    $this->uri = $_REQUEST["page"]; // "rel_path", a part of URI [RFC-2616]

    $_db = &new DbAdapter( $this->proj_root_os.'/config_db.php' );
    $this->db_pfx = $_db->db_prefix;
    $this->db = $_db->StaticFactory(); unset($_db); // var_dump($this->db); // $this->dbutil = &new DbUtil($this->db);
    $cfg_te = &new Config( $this->proj_root_os.'/config_te.php' );
    $this->tpl = &new ThemePlayTE( $cfg_te, $this );
    $this->form = &new txFormField ($this);
  }

  function Handle()  {
    // cache preface routine. если есть в кэше отдать контент и завершение
    $this->uri_hash = base64_encode( str_replace('page=index.php&page=', $this->uri, $_SERVER['QUERY_STRING']) ) ;
    if ( $this->tpl_caching && txCache::CacheCheck( $this->proj_cache_dir.$this->uri_hash )) {
        ob_start();
            $this->dbgMsg ( '['.date("H-i-s").'] cache : '.$this->uri_hash, '' , $pre=0, $htmlqt=0 );
            $_end = UtilityCore::microtime_float(); $_start = $this->_tmr_start;
            $this->dbgMsg ( 'Handle() end (sec) : ', ($_end - $_start),0,0);
            $this->Finalize();
            $this->Output();
        return;
    }
    // request needs processing
    $this->uri_parts = $this->_UriParse();    // в $this->uri отрезаются крайние и двойные слэши
    ob_start();
            $this->routines[] = $this->_UnitDetect(); // var_export($this->routines);
            $this->TplDomainInit();                   // штатные переменные домена
            $this->Perform();
    $this->Finalize(); /*
    $this->debug_text = ob_get_contents();  ob_end_clean();
    $this->Debug($this->debug_text); */

    if ( $this->tpl->Get("HTML:Body") && !$this->tpl->Get("HTML:Html"))
        $this->tpl->Parse($this->tpl_main_wrapper, "HTML:Html");
    if ($this->tpl->Get("HTML:Html"))
        echo $this->tpl->Get("HTML:Html"); // var_export($this->tpl->domain);
    else header("HTTP/1.1 204 No Content");

  }

  function Perform() {
    $_start = UtilityCore::microtime_float();

    foreach ($this->routines  as $rt )  {
      if (is_file($f = $this->proj_root_os.$rt['rt_path']))  require_once($f);
      else                                                   trigger_error( BR.'cannot open file: '.$f.BR );
      $this->unit = &UtilityCore::Born('unit'.$rt['rt_name'],
                                 array( $this ) );  //  ссылкой -- пошел!!!
      $this->unit->Handle();
    } // unset ($this->unit->guide);    // $this->unit->dbgMsg('', $this->unit);
    $_end = UtilityCore::microtime_float(); $this->dbgMsg ( 'UNIT time (sec) : ', ($_end.' - '.$_start.' = '.($_end - $_start)) , $pre=0, $htmlqt=0 );
  }

  function _UriParse() {
    // 1. -  a) kill slashdupes. b) kill leading slash c) kill trailing slash
    $this->uri = preg_replace('{/+}i', '/', $this->uri);
    $this->uri = preg_replace('{^/}', '', $this->uri);
    $this->uri = preg_replace('{/$}', '', $this->uri);
    $uri_parts = explode( "/", $this->uri );   // echo 'uri parts array: '; var_export($uri_parts);die();
    return $uri_parts;
  }

  function _UnitDetect( $uri_parts = null ) {    // var_export($uri_parts); die();
    // echo 'uri parts: ';    var_export($uri_parts);

    if (is_null($uri_parts))   $uri_parts = $this->uri_parts;
    if ($uri_parts[0] == "")   $m_unit = $this->units_map['/'];  // корневой URI
    else
      if ( array_key_exists($uri_parts[0], $this->units_map))
            $m_unit = $this->units_map[$uri_parts[0]];
      else  $m_unit = $this->units_map['404'];

    if (!$this->Auth()) $m_unit = $this->auth_uri;

    $m_unit = strtoupper(substr($m_unit, 0, 1)).substr($m_unit, 1);
    $class_file = $this->proj_unit_dir.'unit'.$m_unit.'.php';

    if (is_file($this->proj_root_os.'/'.$class_file)) {
      $this->routine = array(  'rt_name' => $m_unit,
                               'rt_path' => '/'.$class_file,
                            );
      $this->dbgMsg('Unit Capped: '.$m_unit.BR.$class_file.' <b>found</b>.', '', $pre=0, $htmlqt=0 ); // echo
      return $this->routine;
    } else {
      $this->dbgMsg('Unit Capped: '.$m_unit.BR.$class_file.' <b>NOT found</b>.', '', $pre=0, $htmlqt=0 ); // echo
      // echo 'Unit Capped: '.$m_unit.BR.$class_file.' <b>NOT found</b>.'; // die();
      return false;
    }

  }

  function Auth () {
    if ( !$this->auth_required ) return true;

    if ( $_SESSION[$this->auth_var] )  {
      $a = $this->db->sql2array($sql = "select * from ".$this->db_pfx."dealers ".
                                "where id='".$_SESSION[$this->auth_var.'_user_id']."'");
      // echo 'auth sql :: '.$sql.BR.var_export($a,1); die();
      if (count($a)) {
        $this->user = $a[0];
        $this->user['role'] = $this->getRole();
        $this->client_sys_id    = $this->user['client_id']; // 'WTX';
        $this->client_sys_name  = $this->user['name'];      // 'ООО &laquo;Мототехника&raquo;';
        $this->client_address   = $this->user['address'];   // 'г.Москва, пр.Мира, д.76';
      }
    }
    if (empty($this->user))      return false;
    else                         return true;
  }

  function GetRole( $user_id=null ) { // роль (уровень привилегий пользователя), moreabout: core/const
      if ( is_null($user_id) ) // user_id - либо id, либо login
          $user = & $this->user;
      else   {
          if ( is_numeric($user_id) )  $sql = "select * from ".$this->db_pfx."dealers where id=".$user_id;
          else                         $sql = "select * from ".$this->db_pfx."dealers where login='".$user_id."'";
          $a = $this->db->sql2array($sql);
          $user = $a[0];
      }

      if ( empty($user) )                                           return ACCESS_LEVEL_GUEST;
      if ( in_array($user['login'], array_keys($this->roles))  )    return $this->roles[ $user['login'] ] ;
      else                                                          return ACCESS_LEVEL_CLIENT ;
  }


  function TplDomainInit() {
    $this->tpl->Set("/", "/".$this->proj_root  );
    $this->tpl->Set("/uri/", $this->uri  );
    foreach ($this->uri_parts as $i=>$part)
      $this->tpl->Set("/uri:".$i."/", $part  );       // patch for some cases :
    $this->tpl->Set("/theme/", $this->http_tpl_dir ); // $this->tpl->Set("/theme/", '/'.$this->http_tpl_dir );
    $this->tpl->Set("today", date("Y-m-d")  );
    $this->tpl->Set("user:id",   $this->user['id'] );
    $this->tpl->Set("user:name", $this->user['login'] );

    $this->tpl->Set("/QS/", UtilityCore::QS_Render() ) ;
    $this->tpl->Set("/404/", $this->token404 ) ;
  }

  function Debug($out) {
    if(!$this->debug_to_file)
      echo $out;
    else {
      $fp = fopen( $this->debug_to_file ,"w");
      fputs( $fp, "\r\n". $out ."\r\n" );
      fclose($fp);
    }
  }

  function Finalize() {
    $this->debug_text = ob_get_contents();  ob_end_clean();
    $this->debug_text =  ' user: '.$_SESSION[$this->auth_var.'_login']. $this->debug_text;
    if ($this->debug )
    {
      if ( empty($this->debug_privelege)
            ||
           is_array($this->debug_privelege) && in_array($_SESSION[$this->auth_var.'_login'],$this->debug_privelege)
         )
      $this->Debug($this->debug_text);
    }
  }

  function Redirect( $http_to ) { // echo ' REDIRECTED TO : [ '.$http_to.' ]. ';
    $this->Finalize();
    header ("Location: ".$http_to);
    die();
  }

  function Output( $html=false ) {
      if ( $this->tpl_caching ) {
          $tfile = $this->proj_cache_dir.$this->uri_hash;
          $cache_exists = txCache::CacheCheck( $tfile );
          if ( !$cache_exists && $html )  {
              txCache::CachePut( $tfile, $html );
          }
          if (!$html && $cache_exists ) {
              $html = txCache::CacheOut( $tfile );
          }
      } else {
          if (!html) die('emergency: empty result');
      }

      // 404 http response header
      if ($strpr=strpos(' '.$html, $this->token404 )) header("HTTP/1.1 404 Not Found"); // header("Status: 404 Not Found");
      // echo '<!-- token:'.$this->token404.' presence strpr:'.var_export($strpr,1).'-->';

      if ($this->tpl_ssi) $html = UtilityCore::SSIParse( $html, $markup=true );
      echo $html;
      // die('** * ');
  }

} // EOC { Guide }

?>