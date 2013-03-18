<?php

  require_once (dirname(__FILE__)."/unitAbstract.php" );
    require_once (dirname(__FILE__)."/unitUnit.php" );

class unitLogin extends unitUnit
{

  var $passwd_file = '/web/relcom/eol/etc/passwd.eol';
  var $user_passwd_file = '/web/relcom/e1.ru/www/afisha/events/etc/passwd';
  var $group_file  = '/web/relcom/eol/etc/group.eol';
  var $group = Array('eolAdmin','afishaAdmin');

  function Handle() {
    $this->dbgMsg('session_name():', session_name() );
    $this->dbgMsg('guide->session_name:', $this->guide->session_name);
    $this->dbgMsg('_session : ', $_SESSION);
    $this->dbgMsg('_REQUEST : ', $_REQUEST);
    $this->dbgMsg('_POST : ', $_POST);
    $this->dbgMsg("_REQUEST[{$this->guide->form_exists_var}]", $_REQUEST[$this->guide->form_exists_var]);
    $this->dbgMsg('_REQUEST[_fdata_login] : ', $_REQUEST['_fdata_login']);
    $this->dbgMsg('LOGIN COND : ', ($_REQUEST[$this->guide->form_exists_var] && $_REQUEST['_fdata_login']));

    if ( $_REQUEST[$this->guide->form_exists_var] && $_REQUEST['_fdata_login'])
      $this->Login_process();
    elseif ($_GET['logout']=='yes') {
      if (isset($_COOKIE[session_name()])) setcookie(session_name(), '', time()-42000, '/');
      session_destroy();
      $loc = "Location: http://".$_SERVER['HTTP_HOST'].'/'.$this->guide->proj_root;
      header($loc);die();
    }
   // else
    $this->Login_construct();

    $this->tpl->Parse('login.html', 'HTML:Body' );
    $this->tpl->Parse('xhtml.html', 'HTML:Html' );
  }

  function Login_construct() {

    $this->form->getHTML( array(  'tpl'       => 'plain:Hidden',
                                  'tplvar'    => '__form_present',
                                  'field'     => '__form_present',
                                  'value'     => 1,                   ) );

    $this->form->getHTML( array(  'tpl'       => 'plain:Text',
                                  'tplvar'    => 'txtLogin',
                                  'field'     => '_fdata_login',
                                  'value'     => $_REQUEST['login'],
                                  '_misc'     => '',     ) );

    $this->form->getHTML( array(  'tpl'       => 'plain:Password',
                                  'tplvar'    => 'txtPwd',
                                  'field'     => '_fdata_password',
                                  'value'     => '',
                                  '_misc'     => ' style="color:#c0c0c0;" ',     ) );

  }

  function Login_process() {
    $fdata = UtilityCore::arrKeysPfxStrip('_fdata_', $_REQUEST); // $this->dbgMsg( BR.'tblExec.fdata: ', $fdata, 1);

    $this->tbl->Init( $this->guide->db_pfx.'place' );
    if ( count($a = $this->tbl->Load(null,"place_login='".$fdata['login']."' and place_passwd='".$fdata['password']."'")) )
    {
      $_SESSION[$this->guide->auth_var] = 1;
// die('##### tbl->Load'.BR.var_export($a,1));
      $_SESSION[$this->guide->auth_var.'_login']   = $a[0]['place_login'];
      $_SESSION[$this->guide->auth_var.'_user_id'] = $a[0]['place_id'];
      $this->guide->user = $a[0];
      $this->guide->user['role'] = 'kino'; // $this->guide->getRole();

      /* if ( $this->guide->user['role']==ACCESS_LEVEL_SERVER ) $loc = "Location: http://".$_SERVER['HTTP_HOST'].'/'.$this->guide->proj_root.'tecstore/'; */
      $loc = "Location: http://".$_SERVER['HTTP_HOST'].'/'.$this->guide->proj_root.
               (($this->guide->uri || $this->guide->uri_parts[0]!='login')?$this->guide->uri:'');
      // $this->dbgMsg('uriparts:', $this->guide->uri_parts);  die('['.$this->guide->proj_root.']loc:::'.$loc);
      header($loc);die();
    }

    $pwd_auth = $this->Login_passwd( $fdata['login'], $fdata['password'] );  $this->dbgMsg('@@@ pwd_auth : ', $pwd_auth );
    if ( !empty($pwd_auth) ) {
      $_SESSION[$this->guide->auth_var] = 1;


      $_SESSION[$this->guide->auth_var.'_login']   = $fdata['login'];
      $_SESSION[$this->guide->auth_var.'_user_id'] = 0;
      $this->guide->user = array( 'id'=>0, 'login' => $fdata['login'] );
      $this->guide->user['role'] = 'portal';

//$this->dbgMsg( '##### pwd_auth', $fdata );   $this->dbgMsg( '##### _SESSION', $_SESSION ); die();

      $loc = "Location: http://".$_SERVER['HTTP_HOST'].'/'.$this->guide->proj_root.
               (($this->guide->uri || $this->guide->uri_parts[0]!='login')?$this->guide->uri:'');
      header($loc);die();
    }

    $this->dbgMsg('auth RS  : ', $a);
    $this->dbgMsg('auth USER: ', $this->guide->user);
    $this->dbgMsg('_SESSION  : ', $_SESSION);

  }



  function Login_passwd( $login, $passwd ) {
$this->dbgMsg('@@@ login: ', $login );

      $auth = '';
      $strings = file( $this->passwd_file );
// $this->dbgMsg('@@@ strings: ', $strings );
      foreach ( $strings as $str ) {
          $str = rtrim( $str );
          list ( $name, $crypt_passwd ) = explode(":", $str, 2);
          if ( ($name==$login) && (strcmp(crypt( $passwd, $crypt_passwd ), $crypt_passwd ) == 0) )
                  $valid_passwd = 1;
      }
      $strings = file( $this->user_passwd_file );
      foreach ( $strings as $str ) {
          $str = rtrim ( $str );
          list ( $name, $crypt_passwd ) = explode(":", $str, 2);
          if ( ($name==$login) && (strcmp(crypt( $passwd, $crypt_passwd ), $crypt_passwd ) == 0) )
          $valid_passwd = 1;
      }
$this->dbgMsg('@@@ valid_passwd: ', $valid_passwd );
      if ( $valid_passwd) {
          $strings = file( $this->group_file );
          foreach ( $strings as $str ) {
              $str = rtrim( $str );
              list ( $name, $users ) = explode(":", $str, 2);  //if ( $name == $group )
              if(in_array($name,$this->group)) {
                  $data = explode(' ', $users );
                  foreach ( $data as $cur_login )
                      if ( $cur_login == $login )
                              $auth = $login;

              }
          }
      }
      return $auth;
  }




} // EOC { unitLogin }

?>