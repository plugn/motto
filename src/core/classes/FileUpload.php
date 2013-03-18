<?php
/**
 *
 *  @author Max Dolgov
 *  @version 0.1
 *
 *  @description
 *  file upload utility
 *
 *  @usage
 *  $cfg = array("field_name" => '?', "save_path"  => '?', );
 *
 *  $upload = &new FileUpload(&$cfg);
 *     $_e = $upload->Handle();
 *     if ($_e)
 *        trigger_error(   "error #".$_e." @ upload ".$cfg["name"].
 *        " log : ".var_export($upload->cfg["file_error"], true), E_USER_WARNING);
 *
 */

// require_once (dirname(__FILE__)."/UtilityCore.php");

class FileUpload
{
  var $cfg;
  // construct'em all
  function FileUpload( &$cfg )
  {
    $this->cfg  = &$cfg;

    $this->msg["errno"][0] = "UPLOAD_ERR_OK : #0 <br />"."<i>There is no error, the file uploaded with success.</i>";
    $this->msg["errno"][1] = "UPLOAD_ERR_INI_SIZE : #1 <br />".
                             "<i>The uploaded file exceeds the upload_max_filesize directive in php.ini. </i>";
    $this->msg["errno"][2] = "UPLOAD_ERR_FORM_SIZE : #2 <br />".
                             "<i>The uploaded file exceeds the MAX_FILE_SIZE in the html form.</i>";
    $this->msg["errno"][3] = "UPLOAD_ERR_PARTIAL  : #3 <br />"."<i>The uploaded file was only partially uploaded.</i>";
    $this->msg["errno"][4] = "UPLOAD_ERR_NO_FILE : #4 <br />"."<i>No file was uploaded.</i>";

  }


  //  типовой обработчик
  function  Handle()
  {
    $error = $_FILES[$this->cfg["field_name"]]['error'];
    if ($error)  { $this->cfg["file_error"][] = $error; return false; }

    $this->cfg["file_size"] = $_FILES[$this->cfg["field_name"]]['size'];
    $this->cfg["file_tmp"]  = $_FILES[$this->cfg["field_name"]]['tmp_name'];

    // you have to =save/set/restore= _SERVER["DOCUMENT_ROOT"] in outer scope if smth goes wrong
    $this->cfg["path_to"] = $_SERVER["DOCUMENT_ROOT"].$this->cfg["save_path"];
    $this->cfg["name_to"] = $this->FileNameUniq($_FILES[$this->cfg["field_name"]]["name"] );

    // echo "preparing : <pre>";var_export($this->cfg) ; echo "</pre>".BR;

    if ( is_array($this->cfg["exts_whitelist"]) && !empty($this->cfg["exts_whitelist"]) ) {
      if ( !in_array(strtolower($this->file_ext),$this->cfg["exts_whitelist"]) )
          $this->cfg["file_error"][] = 'file extension {'.$this->file_ext.'} disabled';
    }
    if (empty($this->cfg["file_error"]))
      $fwd = $this->_Upload();
    else { return true; }/* die( ' extensions... '.var_export($this->cfg["file_error"],1) ); } */

    // echo " after : <pre>";var_export($_FILES) ; echo "</pre>".BR;
    if (!$fwd) $this->cfg["file_error"][] = "_Upload(".$this->cfg["file_tmp"]." to ". $this->cfg["path_to"].$this->cfg["name_to"].") failed";
    return (!$fwd);

  }

  function FileNameUniq ( $filename )
  {
    // фаза 1. подготовка и транслитерация
    //$path_to_parts = pathinfo($path_to);
      $file_parts = pathinfo($filename);    // var_export($file_parts);
      $file_w_ext  = $file_parts["basename"];
      $this->file_ext = $file_ext = $file_parts["extension"];
      $file_no_ext = substr($file_w_ext, 0, strlen($file_w_ext)-strlen($file_ext));
      $name = UtilityCore::Translit($file_no_ext);//.".".$file_ext;

    // фаза 2. проверка на существование файла с таким именем с директории
      $_name = $name;
      $c = 1;
      $exst = file_exists($this->cfg["path_to"].$name.".".$file_ext);

      while( file_exists( $this->cfg["path_to"].$name.".".$file_ext ) )
      {
        if ($name == $_name) $name = $_name.$c;
        else $name = $_name.(++$c);
      }
      $result = $name.".".$file_ext;

    return $result;
  }


  function _Upload ()
  {
    $a = move_uploaded_file($this->cfg["file_tmp"], $this->cfg["path_to"].$this->cfg["name_to"]);
    return  $a;
  }

    /*  необходимые внешние данные :
        _REQUEST : '_extra_filedrop' =>   array (    2 => '1',    4 => '1',  ), // чекбоксы: db_table id удаляемых файлов
        _FILES непустой
        в БД должна быть таблица $cfg['db_table'] с полями 'pic', 'path', 'file', $cfg['db_parent_id']
        $cfg = array (
          'db_table' => $this->guide->db_pfx.'film_pics', // собственно таблица в бд
          'db_parent_id'  => 'film_id',
          'parent_id_value'  => 123,
          'pic_w' => 150,
          'pic_h' => 100,
          'tmb_w' => 50,
          'tmb_h' => 50,
          'resizer' => $this->guide->resizer,
          'tbl' => &$this->tbl, // ссылка на объект класса DbUtil
        )
    */

  function MultiPic_Save( $cfg ) {
    $tbl = $cfg['tbl'];
    if (!is_object($tbl)) { UtilityCore::dbgMsg('MultiPic_Save($cfg) : $cfg[\'tbl\']',' is void instead of being a DbUtil object'); return false; }
    if (!empty( $_REQUEST['_extra_filedrop']))
    {
        foreach ($_REQUEST['_extra_filedrop'] as $pos_id => $whether_remove )  {
            if ( $whether_remove )  {   UtilityCore::dbgMsg('_extra_filedrop #', $pos_id);
              $tbl->Init( $cfg['db_table'] );
              $a = $tbl->Load( $pos_id );
              $picfile = $_SERVER['DOCUMENT_ROOT'].$a['pic'];  UtilityCore::dbgMsg( '### RS picfile : ', $picfile );
              $tnpic   = $_SERVER['DOCUMENT_ROOT'].$a['path'].'thumb.'.$a['file']; UtilityCore::dbgMsg( '### tnpic : ', $tnpic );
              if ( $a['pic'] && is_file($picfile) ) { unlink( $picfile ); UtilityCore::dbgMsg('### drop picfile : ', $picfile); }
              if ( is_file($tnpic) ) { unlink( $tnpic ); UtilityCore::dbgMsg('### drop thumbnail : ', $tnpic);  }
              $tbl->Drop($pos_id);
            }
        }
    }

    if ( !empty($_FILES) )  // if uploaded, save file and store position at DB
      foreach ( $_FILES as $field=>$data ) {
        // *** save files ***
        if ($data['error']) { UtilityCore::dbgMsg('upload: http-post error#'.$data['error'], ''); continue; }
        $up_cfg = array("field_name" => $field, "save_path"  => $this->cfg["save_path"], "exts_whitelist"=>$this->cfg["exts_whitelist"]);
        $this->cfg = $up_cfg; //  $upload = &new FileUpload( $cfg );
        $_e = $this->Handle();
        if ($_e) {  trigger_error(   "error #".$_e." @ upload ".$cfg["name"]." log : ".var_export($this->cfg["file_error"], true), E_USER_WARNING); }
        else     {  $imgfile = $this->cfg['path_to'].$this->cfg['name_to'];
                    @chmod( $imgfile, 0644 );
                    $width = $cfg['pic_w'];
                    $thumb_width = $cfg['tmb_w'];
                    $thumb_file = $this->cfg['path_to'].'thumb.'.$this->cfg["name_to"];
                    list($o_width, $o_height, $o_type, $o_attr) = getimagesize( $imgfile );

                    $_opts = '';
                    if ( $cfg['pic_w'] && $cfg['pic_h']) $_opts .= " -w ".$cfg['pic_w']." -h ".$cfg['pic_h'];
                    elseif ( $cfg['pic_w'] && $o_width != $cfg['pic_w'] )  $_opts .= " -w ".$cfg['pic_w'];
                    elseif ( $cfg['pic_h'] && $o_height!= $cfg['pic_h'] )  $_opts .= " -h ".$cfg['pic_h'];
                    if ($_opts!='') system( $_cmd = $cfg['resizer']." -f ".$imgfile." -o ".$imgfile.$_opts ); echo $_cmd;
                    $_opts = '';
                    if ( $cfg['tmb_w'] && $cfg['tmb_h']) $_opts .= " -w ".$cfg['tmb_w']." -h ".$cfg['tmb_h'];
                    elseif ( $cfg['tmb_w'] && $o_width != $cfg['tmb_w'] )  $_opts .= " -w ".$cfg['tmb_w'];
                    elseif ( $cfg['tmb_h'] && $o_height!= $cfg['tmb_h'] )  $_opts .= " -h ".$cfg['tmb_h'];
                    if ($_opts!='') system( $_cmd = $cfg['resizer']." -f ".$imgfile." -o ".$thumb_file.$_opts ); echo $_cmd;


                    $pic_data = array( $cfg['db_parent_id'] => $cfg['parent_id_value'],
                                       'path' => $this->cfg["save_path"],
                                       'file' => $this->cfg["name_to"],
                                       'pic'  => $this->cfg["save_path"].$this->cfg["name_to"] );
                    // *** store in DB ***
                    $tbl->Init( $cfg['db_table'] ); // UtilityCore::dbgMsg(refEdit_Save() _fdata_ : ', $fdata );
                    $tbl->SetData( $pic_data );
                    $r = $tbl->Save( $mode='insert' );  // INSERT     $this->dbgMsg('', 'hop1');
        }
      }
  }


}  // EOC

?>