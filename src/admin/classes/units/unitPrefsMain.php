<?php

  require_once (dirname(__FILE__)."/unitAbstract.php" );
    require_once (dirname(__FILE__)."/unitUnit.php" );

    require_once ( "FileUpload.php" );


class unitPrefsMain extends unitUnit
{

  var $db_table    = 'place';

  function Handle() {
    if (!$this->HasAccess()) {  header('Location: http://'.$_SERVER['HTTP_HOST']."/".$this->guide->proj_root);die(); }

    $this->Init();
    $method = $this->context.'_construct';
    $method_save = $this->context.'_save';
    $method_drop = $this->context.'_drop';

    $this->dbgMsg( '_REQUEST : ', $_REQUEST );
    // $this->dbgMsg('%%% method_save : ', $method_save. '_POST[this->guide->form_exists_var] ['.$_REQUEST[$this->guide->form_exists_var].']');
    if( $_REQUEST[$this->guide->form_exists_var] && method_exists($this,$method_save) ) {
      $this->$method_save();
      header( 'Location: http://'.$_SERVER['HTTP_HOST']."/".$this->guide->proj_root.implode('/', $this->guide->uri_parts) ); die();
    }
    elseif ( $this->guide->uri_parts[2]=='drop' && is_numeric($this->guide->uri_parts[3]) && method_exists($this,$method_drop) ) {
      $this->$method_drop($this->guide->uri_parts[3]);
      header( 'Location: http://'.$_SERVER['HTTP_HOST']."/".$this->guide->proj_root.$this->guide->uri_parts[0].'/'.$this->guide->uri_parts[1]);die();
    } else $this->$method();
  }

  function Init() {
    $this->tpl->Set( 'HTMLTitle', 'Настройки' );
    switch ( $this->guide->uri_parts[1] )  {
      case 'film_editors':
        $this->context = 'filmEditors';
        break;
      case 'cinemas':
        $this->context = 'moviePlaces';
        break;
      case 'place':
        $this->context = 'Place';
        break;
      default: $this->context = 'prefList';
    }
  }

  function prefList_construct() {
    $r = $this->tpl->Parse( 'prefs_main.html:Main', 'HTML:Body' );  $this->dbgMsg(" *** ", $r, 1,1 );
  }

  function filmEditors_construct() {
    require ( 'presets/form/custom.form_exists.php' ); // hidden field id

    $this->tbl->Init( $this->guide->db_pfx.'place', 'place_id' );
    $a = $this->tbl->Load(null, "1=1 order by place_id asc" );
    foreach ($a as $k=>$v ) {
        $a[$k]['trStyle']  = ($k%2==1)?' style="background:#eee; " ':'';
        $a[$k]['formCheck'] = $this->form->getHTML(  array(
                                        'tpl'       => 'plain:CheckStd',
                                        'field'     => '_fdata_film_editor['.$v['place_id'].']',
                                        '_empty_value' => '0',
                                        '_misc'     => $v['film_editor']?' checked="checked"':'',
                                        'value'     => 1,                   ) );
    }
    $this->dbgMsg('KinoUsers ', $a);
    $this->tpl->Loop($a, 'prefs_main.html:KinoUsers', 'HTML:Body', $append=0, $implode=0, $wrap_empty=true );
  }

  function filmEditors_save() {  // $this->dbgMsg(' qqqqq filmEditors_save()', '');
      $a = UtilityCore::arrKeysPfxStrip( '_fdata_', $_REQUEST );
      $this->tbl->Init( $this->guide->db_pfx.'place', $db_id='place_id' );

      foreach ( $a['film_editor'] as $id=>$value ) {
        $this->tbl->SetData ( array(  $this->tbl->db_id  => $id,
                                      'film_editor'      => $value,    ) );
        $this->tbl->Save('update');
      }
  }

  function moviePlaces_construct() {
    require ( 'presets/form/custom.form_exists.php' ); // hidden field id
    $this->tbl->Init( $this->guide->db_pfx.'place', 'place_id' );
    $a = $this->tbl->Load(null, "1=1 order by place_id asc" );

    foreach ($a as $k=>$v ) {
        $a[$k]['trStyle']   = ($k%2==1)?' style="background:#ddd; " ':'';
        $a[$k]['txtStyle']  = ($k%2==1)?' style="background:#ddd;border:1px dotted;" ':' style="background:#fff;border:1px dotted;" ';
        $a[$k]['formName'] = $this->form->getHTML(  array(
                                        'tpl'       => 'plain:Text',
                                        'field'     => '_fdata_place_name['.$v[$this->tbl->db_id].']',
                                        '_empty_value' => '0',
                                        '_misc'     =>  ' size="20" '.$a[$k]['txtStyle'].' class="w100"'.
                                                        ' id="_place_'.$v[$this->tbl->db_id].'"',
                                        'value'     => htmlspecialchars($v['place_name']),                   ) );
        $a[$k]['formCheck'] = $this->form->getHTML(  array(
                                        'tpl'       => 'plain:CheckStd',
                                        'field'     => '_fdata_film_editor['.$v[$this->tbl->db_id].']',
                                        '_empty_value' => '0',
                                        '_misc'     => $v['film_editor']?' checked="checked"':'',
                                        'value'     => 1,                   ) );
        $plcnm[] = '_place_'.$v[$this->tbl->db_id];
        $plcva[] = '_pva'.$v[ $this->tbl->db_id ];
        $pva[] = $a[$k]['spanMisc'] = ' id="_pva'.$v[ $this->tbl->db_id ].'"';
    }

    $this->dbgMsg('MoviePlaces', $a);
    $this->tpl->Set( 'formHdrMisc', " onsubmit=\"return validateIsEmpty(['".implode("','",$plcnm)."'], ['".implode("','",$plcva)."']);\" " );
    $this->tpl->Loop($a, 'prefs_main.html:MoviePlaces', 'HTML:Body', $append=0, $implode=0, $wrap_empty=true );

  }

  function moviePlaces_drop( $id ) {
    if ($id) {
      $this->tbl->Init($this->guide->db_pfx.$this->db_table, $db_id='place_id');
      // $this->tbl->db_protect = 1;
      return $this->tbl->Drop( $id );
    }
  }

  function moviePlaces_save() {
    $this->dbgMsg('moviePlaces_save()  :::: ', '');
    $update = UtilityCore::arrKeysPfxStrip('_fdata_', $_REQUEST); $this->dbgMsg('@@@ update ', $update );
    // $this->tbl->db_protect = 1;
    if (!empty($update)) {
      $this->tbl->Init( $this->guide->db_pfx.'place', $db_id='place_id' );
      foreach ( $update as $field => $data ) {
        $this->tbl->Cleanup($id_aff=1);  // !!!
        foreach ( $data as $id => $value ) {
          $this->tbl->SetField( $field, $value );
          $this->tbl->SetField( $this->tbl->db_id, $id );
          $this->tbl->Save( $mode='update' );
        }
      }
    }

    $add = UtilityCore::arrKeysPfxStrip('_add_', $_REQUEST);   $this->dbgMsg('@@@ add ', $add );
    if (!empty($add)) {
      $this->tbl->Init( $this->guide->db_pfx.'place', $db_id='place_id' );
      foreach ( $add as $field => $data ) {
        foreach ( $data as $id=>$value ) {
          if (trim($value)==='') continue;
          $this->tbl->SetField( $field, $value );
          $this->tbl->Save( $mode='insert' );
        }
      }
    }
  }

  function Place_construct() {
    $this->tpl->Set( 'Page:Title', 'Информация о кинотеатре' );

    if ( $place_id = $this->guide->uri_parts[2]) {
      require ( 'presets/form/custom.form_exists.php' ); // hidden field id
      $this->tbl->Init( $this->guide->db_pfx.'place', 'place_id' );
      $a = $this->tbl->Load( $this->db->quote( $place_id ) );   $this->dbgMsg ( 'Place_construct('.$place_id.') : ', $a , $pre=1, $htmlqt=1 );

      $this->form->getHTML(  array( 'tpl'       => 'plain:Hidden',
                                    'field'     =>  '_fdata_place_id', // '_fdata_place_name',
                                    'tplvar'    => 'placeID', // 'place_name',
                                    'value'     => $place_id, ) ); // 'place_name'
      // текстовые поля
      $a_text = array ( 'place_name', 'place_URL', 'place_email', 'place_login', 'place_passwd' );
      foreach ( $a_text as $k ) {
        $this->form->getHTML(  array( 'tpl'       => 'plain:Text',
                                      'field'     =>  '_fdata_'.$k, // '_fdata_place_name',
                                      'tplvar'    => $k, // 'place_name',
                                      '_misc'     =>  ' size="20" class="w100"',
                                      'value'     => htmlspecialchars($a[$k]), ) ); // 'place_name'
      }
      // текстареа
      $a_area = array ( 'place_contact', 'place_tel', 'place_contacttel', 'place_address' ); //, 'place_comment'
      foreach ( $a_area as $k ) {
        $this->form->getHTML(  array( 'tpl'       => 'plain:Textarea',
                                      'field'     =>  '_fdata_'.$k, // '_fdata_place_name',
                                      'tplvar'    => $k, // 'place_name',
                                      '_cols'     => 25,
                                      '_rows'     => 2,
                                      '_misc'     =>  ' class="w100"',
                                      'value'     => htmlspecialchars($a[$k]), ) ); // 'place_name'
      }
      $this->form->getHTML(  array( 'tpl'       => 'plain:Textarea',
                                    'field'     =>  '_fdata_place_comment',
                                    'tplvar'    => 'place_comment',
                                    '_cols'     => 60,
                                    '_rows'     => 8,
                                    '_misc'     =>  ' class="w100"',
                                    'value'     => htmlspecialchars($a['place_comment']), ) ); // 'place_name'


      $this->tbl->Init($this->guide->db_pfx.$this->db_table, 'place_id' );
      $a = $this->tbl->Load( $place_id );   $this->dbgMsg('a : ',$a, 1,1);
      if ( $a['pic'] && is_file($_SERVER['DOCUMENT_ROOT'].$a['pic']) )
      {
          $this->dbgMsg('', 'inner - a[pic] && file_exists');
          $this->tpl->Set('HttpPic', '/'.$a['pic']);
          $this->form->getHTML(  array( 'tpl'       => 'plain:CheckLab',
                                        'tplvar'    => 'formFileDrop',
                                        'field'     => '_extra_filedrop',
                                        '_desc'     => 'удалить загруженный ранее файл',
                                        '_field_id' => '_xfile_drop'.$id,
                                        'value'     => 1,                   ) );
          $this->tpl->Parse('prefs_main.html:Pic', 'Pic');
      }

          $this->form->getHTML(  array( 'tpl' => 'plain:Upload',
                                        'tplvar'    => 'formUpload',
                                        '_misc'     => ' onchange="try { ce=document.getElementById(\'_xfile_drop'.$id.'\'); if (ce) ce.checked=\'checked\'; } catch(e) { alert(\'error: \'+e); } " ',
                                        'field'     => '_fdata_filedata',   ) );


      if ( $a['pic2'] && is_file($_SERVER['DOCUMENT_ROOT'].$a['pic2']) )
      {
          $this->dbgMsg('', 'inner - a[pic] && file_exists');
          $this->tpl->Set('HttpPic', '/'.$a['pic2']);
          $this->form->getHTML(  array( 'tpl'       => 'plain:CheckLab',
                                        'tplvar'    => 'formFileDrop',
                                        'field'     => '_extra_filedrop2',
                                        '_desc'     => 'удалить загруженный ранее файл',
                                        '_field_id' => '_xfile_drop'.$id.'_t2',
                                        'value'     => 1,                   ) );
          $this->tpl->Parse('prefs_main.html:Pic', 'Pic2');
      }

          $this->form->getHTML(  array( 'tpl' => 'plain:Upload',
                                        'tplvar'    => 'formUpload2',
                                        '_misc'     => ' onchange="try { ce=document.getElementById(\'_xfile_drop'.$id.'_t2\'); if (ce) ce.checked=\'checked\'; } catch(e) { alert(\'error: \'+e); } " ',
                                        'field'     => '_fdata_filedata2',   ) );



      $this->tpl->Parse( 'prefs_main.html:Place', 'HTML:Body' );
    }
  }

  function Place_save() {
    $this->tbl->Init( $this->guide->db_pfx.'place', 'place_id' );
    $update = UtilityCore::arrKeysPfxStrip( '_fdata_', $_REQUEST );
    $this->dbgMsg('@@@ _FILES ', $_FILES );
    // 1. pic
    $cfg =array(  'field_name' => '_fdata_filedata', // форма: поле аплоада
                  'db_table'   => $this->guide->db_pfx.'place',
                  'db_id'      => 'place_id',
                  'save_path'  => 'afisha/kino/_tmp/pic/', // директория аплоада
                  'db_fullpath'=> 'pic',            // поле в бд: полный путь к файлу
                  'db_filename'=> '',               // поле в бд: имя файла
                  'db_filepath'=> '',               // поле в бд: каталог файла
                  'resize_w'   => 50,
                  'opt_drop'   => '_extra_filedrop',
               );
    $pic = $this->UploadImg( $cfg ) ;
    if ( $pic!==false ) $update[ $cfg['db_fullpath']] = $pic;
    unset ( $update['filedata'] );

    // pic2
    $cfg2 =array(  'field_name' => '_fdata_filedata2', // форма: поле аплоада
                  'db_table'   => $this->guide->db_pfx.'place',
                  'db_id'      => 'place_id',
                  'save_path'  => 'afisha/kino/_tmp/pic/', // директория аплоада
                  'db_fullpath'=> 'pic2',            // поле в бд: полный путь к файлу
                  'resize_w'   => 150,
                  'opt_drop'   => '_extra_filedrop2',
               );
    $pic2 = $this->UploadImg( $cfg2 ) ;
    if ( $pic2!==false ) $update[ $cfg2['db_fullpath']] = $pic2;
    unset ( $update['filedata2'] );
    $this->dbgMsg('@@@ update ', $update );
    $this->tbl->SetData( $update, $id_aff=1 );
    $this->tbl->SetField( $this->tbl->db_id, $update['place_id'] );
    $this->tbl->Save( $mode="update" );   // echo 'Place_save **** '; // die();
  }


  function UploadImg( $cfg ) {    // remove old picture if update
  /*  $cfg =array(  'field_name' => '_fdata_filedata', // форма: поле аплоада
                  'form_pfx'   => '_fdata_',
                  'save_path'  => 'afisha/kino/_tmp/pic/', // директория аплоада
                  'db_table'   => '',
                  'db_id'      => 'id',
                  'db_fullpath'=> 'pic',            // поле в бд: полный путь к файлу
                  'db_filename'=> '',               // поле в бд: имя файла
                  'db_filepath'=> '',               // поле в бд: каталог файла
                  'resize_w'   => 250,
                  'opt_drop'   => '_extra_filedrop',
               ); */
    $file_saved = false;

    // save picture if uploaded
    if ( $_FILES[ $cfg['field_name'] ]['name']  && !$_FILES[ $cfg['field_name'] ]['error'] ) { // remove old picture if update
      $this->tbl->Init( $cfg['db_table'],   $cfg['db_id'] );
      $a = $this->tbl->Load( $this->db->quote( $this->guide->uri_parts[2] ) );
      $picfile = $_SERVER['DOCUMENT_ROOT'].$a[$cfg['db_fullpath']];       $this->dbgMsg('### RS picfile : ', $picfile);
      if ( $a[ $cfg['db_fullpath'] ] && is_file($picfile) ) {    $this->dbgMsg('### picfile : ', $picfile);
          unlink( $picfile ); // ... drop it physically
      }
      // $cfg = array( "field_name" => $cfg['field_name'], "save_path"  => $cfg['save_path'], );
      $upload = &new FileUpload( $cfg );
      $_e = $upload->Handle();
      if ($_e) {  trigger_error(   "error #".$_e." @ upload ".$cfg["name"]." log : ".var_export($upload->cfg["file_error"], true), E_USER_WARNING); }
      else     {  $imgfile = $upload->cfg['path_to'].$upload->cfg['name_to'];
                  @chmod( $imgfile, 0755 );
                  $rsz_opts = ( $cfg['resize_w']?' -w '.$cfg['resize_w']:'' ) . ( $cfg['resize_h']?' -h '.$cfg['resize_h']:'' );
                  if ($rsz_opts)
                    system($this->guide->resizer." -f ".$imgfile." -o ".$imgfile . $rsz_opts ); // " -w ".$width );
                  $file_saved = $upload->cfg["save_path"].$upload->cfg["name_to"];
               }
    } elseif ( $_REQUEST[$cfg['opt_drop']] ) {
      $this->dbgMsg('### opt_drop: ', $cfg['opt_drop']);
      $this->tbl->Init( $cfg['db_table'],   $cfg['db_id'] );
      $a = $this->tbl->Load( $this->db->quote( $this->guide->uri_parts[2] ) );
      $picfile = $_SERVER['DOCUMENT_ROOT'].$a[ $cfg['db_fullpath'] ]; $this->dbgMsg('### RS picfile : ', $picfile);
      if ( $a[$cfg['db_fullpath']] && is_file( $picfile) ) {   $this->dbgMsg('### picfile : ', $picfile);
          unlink( $picfile ); // ... drop it physically
          $file_saved = '';
          // $fdata[ $cfg['db_fullpath'] ] = ''; // then clear filename at DB
      }
    }

    // unset( $fdata[ substr($cfg['field_name'],strlen($cfg[ 'field_pfx'])) ] ); // cut '_fdata_' off  and
    $this->dbgMsg('### picfile file_saved ', $file_saved );
    return $file_saved;
  }

  function HasAccess( $user=null ) {
    if ( is_null($user) ) $user = $this->guide->user;
    if ($user['role']=='portal')  return true;
    return false;
  }



} // EOC { unitPrefsMain }

?>