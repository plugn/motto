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

      while (file_exists($this->cfg["path_to"].$name.".".$file_ext))
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

}  // EOC


?>