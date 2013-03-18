<?php

class CmxGuide extends Guide {

  function _Init() {
    $this->uri = $_REQUEST["page"]; // "rel_path", a part of URI [RFC-2616]

    $_db = &new DbAdapter( $this->proj_root_os.'/config_db.php' );
    $this->db_pfx = $_db->db_prefix;
    $this->db = $_db->StaticFactory(); unset($_db); // var_dump($this->db); // $this->dbutil = &new DbUtil($this->db);
    $this->db->query('set option character set cp1251_koi8', $this->db->link );
    $cfg_te = &new Config( $this->proj_root_os.'/config_te.php' );
    $this->tpl = &new ThemePlayTE( $cfg_te, $this );
    $this->form = &new txFormField ($this);
  }

  function Handle()  {
    $this->uri_parts = $this->_UriParse();    // в $this->uri отрезаются крайние и двойные слэши  // $this->user$_SERVER['PHP_AUTH_USER']
    ob_start();                 // $this->dbgMsg ( ' berfore $this->tpl_main_wrapper : ', $this->tpl_main_wrapper, $pre=0, $htmlqt=0 );
            $this->routines[] = $this->_UnitDetect(); // var_export($this->routines);
            $this->TplDomainInit();                   // штатные переменные домена
            $this->Perform();   // $this->dbgMsg ( ' after   $this->tpl_main_wrapper : ', $this->tpl_main_wrapper, $pre=0, $htmlqt=0 );
    $this->Finalize();


    if ( $this->tpl->Get("HTML:Body") && !$this->tpl->Get("HTML:Html"))
        $this->tpl->Parse($this->tpl_main_wrapper, "HTML:Html");
    if ($this->tpl->Get("HTML:Html"))
        echo $this->tpl->Get("HTML:Html"); // var_export($this->tpl->domain);
    else header("HTTP/1.1 204 No Content");

  }


  function GetRole( $user_id=null ) {// роль (уровень привилегий пользователя), moreabout: core/const
      if ( is_null($user_id) ) // user_id - либо id, либо login
          $user = & $this->user;
      else   {
          if ( is_numeric($user_id) )  $sql = "select * from ".$this->db_pfx."place where place_id=".$user_id;
          else                         $sql = "select * from ".$this->db_pfx."place where place_login='".$user_id."'";
          $a = $this->db->sql2array($sql);
          $user = $a[0];
      }
      /*  baaaad, baaad,  baaaaad..... */
      if ( empty($user) )     return 'portal';
      else                    return 'kino' ;
  }



  function Auth () {
    if ( !$this->auth_required ) return true;

    if ( $_SESSION[$this->auth_var] )  {
        $a = $this->db->sql2array($sql = "select * from ".$this->db_pfx."place ".
                                  "where place_login!='' and place_login='".$_SESSION[$this->auth_var.'_login']."'");  //$this->dbgMsg ($sql,$a);
        if (!empty($a)) {
          $this->user = $a[0];
          $this->user['role'] = 'kino';
          $this->user['login'] = $a[0]['place_login'];
          $this->client_sys_id = $a[0]['place_id'];
          $this->client_sys_name = $a[0]['place_name'];
          $this->client_sys_address = $a[0]['place_address'];
        } else {
          $this->user['role'] = 'portal';
          $this->user['login'] = $this->user['name'] = $this->client_sys_name = $_SESSION[$this->auth_var.'_login'];;
          $this->user['id'] = $this->client_sys_id = 0;
          $this->client_sys_address = ' г.Екатеринбург, ул.Валека, 13 ';
        }
    }   // echo '`'.$_SERVER['PHP_AUTH_USER'].'`/'.$this->user['role'].' @ ['.date("M,d H:i:s").'] <b>'.$this->uri.BR.'</b> '.BR;

    if (empty($this->user))      return false;
    else                         return true;
  }

  function dbgMsg($desc, $_var, $prewrap=1, $htmlquot=0 ) {
    return UtilityCore::dbgMsg( $desc, $_var, $prewrap=1, $htmlquot=0 );
  }




} // EOC { CmxGuide }

?>