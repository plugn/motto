<?php

  require_once (dirname(__FILE__)."/unitAbstract.php" );
    require_once (dirname(__FILE__)."/unitUnit.php" );

    require_once ( "FileUpload.php" );

class unitComPrefs extends unitUnit
{

  var $db_rec      = 'company';
  var $db_ref      = 'com_prefs';
  var $tpf         = 'com_prefs.html';
  var $html_title  = 'Компании';

  var $pic_save_path = 'afisha/kino/_tmp/info_pic/';
  var $refpic_path   = 'images/nfosrv/ico/';
  var $pic_path      = 'images/nfosrv/pic/';

  function Handle()  {
      $this->dbgMsg ( ' $this->guide->tpl_main_wrapper : ', $this->guide->tpl_main_wrapper, $pre=1, $htmlqt=0 );
      $this->_Init();
      $this->dbgMsg ( ' $this->guide->uri_parts : ', $this->guide->uri_parts, $pre=1, $htmlqt=0 );


      if ( $this->guide->uri_parts[1]=='refmix' ) { // контрол рубрик
          if( $_REQUEST[$this->guide->form_exists_var] && $_REQUEST['mode']=='mask_save'  ) {
            $this->RefMask_save(); // сохранить
            header ( 'Location: http://'.$_SERVER['HTTP_HOST']."/".$this->guide->proj_root.implode('/', $this->guide->uri_parts) ); die();
          } else  {
            $this->RefMaskCtrl(); // построить форму
          }
          $this->tpl->Parse('com_prefs.html:Page', 'HTML:Body');

      } elseif ( $this->guide->uri_parts[1]=='refedit' ) {
          if( $_REQUEST[$this->guide->form_exists_var] ) {
            $this->RefEdit_save();
            header ( 'Location: http://'.$_SERVER['HTTP_HOST']."/".
                     $this->guide->proj_root.$this->guide->uri_parts[0].'/'.$this->guide->uri_parts[1] ); die();
          }
          if ( is_numeric($id=$this->guide->uri_parts[2]) ) {
              $this->RefEdit_construct( $id  );
              $this->tpl->Parse('com_prefs.html:RefForm', 'RefForm');
          } else
              $this->RefList_construct();
          $this->tpl->Parse('com_prefs.html:Page', 'HTML:Body');

      } elseif ( $this->guide->uri_parts[1]=='refadd' ) {
          if( $_REQUEST[$this->guide->form_exists_var] && method_exists($this,'RefEdit_save') ) {
            $this->RefEdit_save();
            header ( 'Location: http://'.$_SERVER['HTTP_HOST']."/".$this->guide->proj_root.implode('/', $this->guide->uri_parts) ); die();
          } else {   $this->RefEdit_construct();     }
          $this->tpl->Parse('com_prefs.html:RefForm', 'RefForm');
          $this->tpl->Parse('com_prefs.html:Page', 'HTML:Body');

      } elseif ( $this->guide->uri_parts[1]=='comedit' ) { // add сюда же только без id
          if ( $this->guide->uri_parts[3]=='pic' && is_numeric($coid=$this->guide->uri_parts[2]) ) {
            $this->Pics_save( $coid );
            if ($_REQUEST['_fdata_comref_id']) $_reftail ='/?_filtr_refid='.$_REQUEST['_fdata_comref_id']; else $_reftail='';
            header ( 'Location: http://'.$_SERVER['HTTP_HOST']."/".
                     $this->guide->proj_root.implode('/', array_slice($this->guide->uri_parts,0,3)).$_reftail );
            die();

          } elseif( $_REQUEST[$this->guide->form_exists_var] && method_exists($this,'ComEdit_save') ) {
            $this->ComEdit_save();
            header ( 'Location: http://'.$_SERVER['HTTP_HOST']."/".
                     $this->guide->proj_root.implode('/', $this->guide->uri_parts).'/?_filtr_refid='.$_REQUEST['_fdata_comref_id'] );
            die();
          } else {
            $id = is_numeric( $this->guide->uri_parts[2])?$this->guide->uri_parts[2]:null;
            $this->ComEdit_construct( $id );
            $this->tpl->Parse('com_prefs.html:PageCom', 'HTML:Body');
          }

      } elseif ( $this->guide->uri_parts[1]=='comadd' ) {
          if( $_REQUEST[$this->guide->form_exists_var] && method_exists($this,'ComEdit_save') ) {
            $this->ComEdit_save();
            header ( 'Location: http://'.$_SERVER['HTTP_HOST']."/".
            $this->guide->proj_root.implode('/', $this->guide->uri_parts).'/?_filtr_refid='.$_REQUEST['_fdata_comref_id']  );
            die();
          } else {
            $refid = $_REQUEST['_filtr_refid']?$_REQUEST['_filtr_refid']:null;
            $this->ComEdit_construct( null, $refid );
          }

      } elseif ( $this->guide->uri_parts[1]=='company'  ) {
            $refid = $_REQUEST['_filtr_refid']?$_REQUEST['_filtr_refid']:null;
            $id = is_numeric( $this->guide->uri_parts[2] ) ? $this->guide->uri_parts[2] : null;
            $this->ComList_construct( $id, $refid );
            $this->tpl->Parse('com_prefs.html:PageCom', 'HTML:Body');

      } elseif ( $this->guide->uri_parts[1]=='drop' && is_numeric($this->guide->uri_parts[3]) ) {
            $this->Drop( $id=$this->guide->uri_parts[3], $mode=$this->guide->uri_parts[2] );
            header ('Location: '. $_SERVER['HTTP_REFERER']);   die();

      } else {
          $this->tpl->Parse('com_prefs.html:Dummy', 'HTML:Body');
      }

      $this->tpl->Parse('comref.common.html', "HTML:Html");
  }

  function _Init() {
      // $this->tpl->Set( '_QS_', $_SERVER['QUERY_STRING'] );
      $this->tpl->Set('portal_mode', ($this->guide->GetRole()=='portal'?'on':''));

      $this->dbgMsg ( ' before $this->guide->tpl_main_wrapper: ', $this->guide->tpl_main_wrapper, $pre=0, $htmlqt=0 );
      $this->guide->tpl_main_wrapper = 'comref.common.html';
      $this->dbgMsg ( ' after $this->guide->tpl_main_wrapper: ', $this->guide->tpl_main_wrapper, $pre=0, $htmlqt=0 );

      $this->db_ref = $this->guide->db_pfx.$this->db_ref;
      $this->db_rec = $this->guide->db_pfx.$this->db_rec;
      $this->ru_opts = array(  'title'=>'компания', 'about'=>'определение', 'description'=>'описание', 'address'=>'адрес',
                               'phone'=>'телефон', 'fax'=>'факс', 'email'=>'e-mail',
                               'logo'=>'логотип', 'website'=>'адрес сайта', 'contract_id'=>'контракт', '_link_pics'=>'изображения', );
      $sql = "describe `".$this->guide->db_pfx."company`";
      $r = $this->db->sql2array( $sql ); //  $this->dbgMsg ( $sql . ' // sql2array : ', $r , $pre=1, $htmlqt=0 );
      $_denials = array('id', '_date_from', '_date_to', 'comref_id', 'r2', 'r3', 'active' ); //
      foreach ($r as $k =>$v ) {
          if ( in_array($v['Field'], $_denials) )  unset( $r[$k] );
          else                                     $this->opts[$k] = $v['Field'];
      }
  }

  function RefMaskCtrl() {
    $opts = $this->opts;

    $this->tbl->Init($this->guide->db_pfx."com_prefs");
    $rfs = $this->tbl->Load( null, '1=1 order by id asc' );
    foreach ( $rfs as $ri => $rv ) {
      $odx = 1;
      foreach ( $opts as $k => $opt ) {
          $opts_masks[$k] += ($rv['fmask'] & pow(2,$odx))?(pow(2,$rv['id'])):0;
          $odx++;
      }
    }

    foreach ( $opts as $k => $opt ) {
      $mask_html .= $this->form->getHTML( array('tpl' => 'plain:Hidden',
                                                'field'  => 'editfriend_groupmask_'.$opt,
                                                'value'  => $opts_masks[$k],
                                        ) );
    }

    $this->tpl->Set( 'GroupMasks', $mask_html );      // $this->dbgMsg ( ' mask_html: ', $mask_html , $pre=1, $htmlqt=1 );

    foreach( $rfs as $k => $v )   $grd[$v['id']] = $v['title'];
    $this->form->getHTML( array( 'data'   => $grd,
                                 'tpl'    => 'options:Select',
                                 'tplvar' => 'ListGroups',
                                 'field'  => 'list_groups',
                                 '_misc'  => ' style="width: 250px;" size="8" onchange="groupClicked();"',
                                ) ) ;

    $refs_html = '';
    foreach ( $rfs as $ref ) {
        $refs_html .= $this->form->getHTML( array( 'tpl'     => 'plain:Hidden',
                                    'field'   => 'efg_set_'.$ref['id'].'_name', // $ref_num
                                    'value'   => $ref['tag'],
                                   ) );
        $refs_html .= $this->form->getHTML( array( 'tpl'     => 'plain:Hidden',
                                    'field'   => 'efg_set_'.$ref['id'].'_sort',
                                    'value'   => $ref['id']*5+5 ,
                                   ) );
    }

    $this->tpl->Set( 'RefsHtml', $refs_html );
    require ( 'presets/form/custom.form_exists.php' );        // hidden field id
    $this->tpl->Parse( $this->tpf.':RefCtrl', 'RefCtrl');
  }

  function RefEdit_construct( $id=null ) {
    if ( $id ) { $this->dbgMsg ( '  RefEdit_construct( id=  ', $id , $pre=1, $htmlqt=0 );
      $this->tpl->Set('Page:Title', 'Раздел: редактирование' );
      $this->tbl->Init( $this->db_ref );
      $a = $this->tbl->Load( $id );
      $this->form->getHTML( array('tpl' => 'plain:Hidden',
                                  'tplvar'=>'hdnId',
                                  'field'=>'_fdata_id',
                                  'value'=>$id, ) );
      $this->tpl->Set('btnSaveText', 'Сохранить' );

      if ( $a['ico'] && file_exists($_SERVER['DOCUMENT_ROOT'].$a['ico']) ) // если есть иконка
      {   $this->dbgMsg('', 'a[ico] && file_exists' );
          $this->tpl->Set('HttpPic', '/'.$a['ico']); // URL лого
          $this->form->getHTML(  array( 'tpl'       => 'plain:CheckLab', // чекбокс для удаления загруженного ранее файла
                                        'tplvar'    => 'formFileDrop',
                                        'field'     => '_extra_filedrop',
                                        '_desc'     => 'удалить загруженный ранее файл',
                                        '_field_id' => '_xfile_drop',
                                        'value'     => 1,                   ) );
          $this->tpl->Parse($this->tpf.':Pic', 'RefPic'); // парсим блок с картинкой
      }

    } else {
      $this->tpl->Set( 'Page:Title',  'Новый раздел' );
      $this->tpl->Set( 'btnSaveText', 'Создать'   );
    }

    require ( 'presets/form/custom.form_exists.php' ); // hidden field id
    $this->form->getHTML( array(  'tpl'       => 'plain:Text',
                                  'tplvar'    => 'formTitle',
                                  'field'     => '_fdata_title',
                                  'value'     => ($id?htmlspecialchars($a['title']):null),
                                  //'_misc'     => ' class="w99"',
                               ) );

    $this->form->getHTML( array(  'tpl'       => 'plain:Text',
                                  'tplvar'    => 'formTag',
                                  'field'     => '_fdata_tag',
                                  'value'     => ($id?$a['tag']:null),
                                  // '_misc'     => ' class="w99"',
                               ) );
    //////////////
    $this->form->getHTML( array(  'tpl'       => 'plain:Text',
                                  'tplvar'    => 'formFMask',
                                  'field'     => '_fdata_fmask',
                                  'value'     => ($id?decbin($a['fmask']):null),
                                  '_misc'     => ' class="w100"',
                               ) );


    $this->form->getHTML( array (
                                  'tpl'   => 'plain:Upload',
                                  'tplvar'=> 'formIco',
                                  'field' => '_fdata_ico',
                                  '_misc' => ' onchange="if(this.value){try{this.form._extra_filedrop.checked=\'checked\';'.
                                             'getNode(\'comImg\').className = \'disappear\';}catch(e){}}" ',

                                ) );


    // $this->tpl->Parse('com_prefs.html:NavRefList', 'SubNav' );

  }

  function RefEdit_save() {
    $fdata  = UtilityCore::arrKeysPfxStrip('_fdata_', $_REQUEST );  $this->dbgMsg( 'refEdit_Save() _fdata_ : ',  $fdata  );

    $upcfg = array( 'db_table'=>$this->guide->db_pfx.$this->db_ref, 'form_field'=>'_fdata_ico',
                    'db_field'=>'ico', 'pic_save_path'=> $this->refpic_path, 'pic_width' => 40, );  // 'images/nfosrv/ico/';
    $fdata  = $this->ImageUpload_save( $fdata, $upcfg );

    // $fdata['fmask'] = bindec( $fdata['fmask'] );
    $this->tbl->Init( $this->guide->db_pfx.$this->db_ref );
    $this->tbl->SetData( $fdata, $id_aff=1 );
    $this->tbl->Save();
  }

  function RefList_construct() {
      $this->tpl->Set( 'Page:Title', 'Разделы' );
      $this->tbl->Init( $this->db_ref );
      $r = $this->tbl->Load( null, '1=1' );
      if ( empty($r) ) {   $this->dbgMsg ( ' no result about refs at DB :  ', $r , $pre=1, $htmlqt=0 );
                           return false;
      }
      $this->tpl->Loop($r, $this->tpf.':RefList', $store_to="RefList", $append=0, $implode=false, $wrap_empty=false );
  }


  function RefMask_save() {
    $mdata  = UtilityCore::arrKeysPfxStrip('editfriend_groupmask_', $_REQUEST );  $this->dbgMsg( 'RefMask_Save() _mdata_ : ',  $mdata  );
    $this->tbl->Init($this->guide->db_pfx."com_prefs");
    $rfs = $this->tbl->Load( null, '1=1 order by id asc' );

    $opts = $this->opts;
    foreach ( $rfs as $ri => $rv ) {
      $rfs[$ri]['fmask'] = 0;
      $odx = 1;
      foreach ( $opts as $k => $opt ) {
          $r = (  $mdata[$opt] & pow(2,$rv['id']))?(pow(2,$odx)):0;
          $rfs[$ri]['fmask'] += $r;
          $odx++;
      }   // $this->dbgMsg ( '+'.$r.'; $rfs['.$ri.'][fmask]: ', $rfs[$ri]['fmask'] , $pre=1, $htmlqt=0 );

      $mdt = array('id'=>$rfs[$ri]['id'], 'fmask'=> $rfs[$ri]['fmask']);
      $this->tbl->SetData( $mdt, $id_aff=1 );
      $this->tbl->Save( $mode='update' );
    }
  }

  function Drop($id=null, $mode=null) {
    if ( is_null($id) || !in_array($mode, array('ref','com') ))
    { $this->dbgMsg ( ' unitComPrefs::Drop() invalid arguments ', $a , $pre=0, $htmlqt=0 ); return false; }

    if ( $mode=='ref' ) {
        $tbl = $this->db_ref; // 'com_prefs';
    } elseif ( $mode=='com' ) {
        $tbl = $this->db_rec; //  'company';
        $this->tbl->Init( $tbl );
        $data = $this->tbl->Load( $id );
        if ( $data['logo'] && is_file( $file_pic=$_SERVER['DOCUMENT_ROOT'].$data['logo']) ) {
          unlink( $file_pic );
        }
    }
    $this->tbl->Init( $tbl );
    return $this->tbl->Drop( $id );
  }

  function ComList_construct( $id=null, $refid=null ) {
    $this->tpl->Set( 'Page:Title', 'список компаний' );
    $this->tbl->Init( $this->db_ref );
    $r = $this->tbl->Load();
    $rdata[0] = ' ------- все разделы ------- ';
    foreach ( $r as  $k=>$v ) {
      // $rdata[ $v['id'] ] = $v['title'];
      $r0 = $this->getSizeOfRef( $v['id'] );
      $rdata[$v['id']] = $v['title'].' ('.intval($r0[0]['recnum']).') ';
    }
    $this->form->getHTML(
      array( 'data'   => $rdata,
             'tpl'    => 'options:Select',
             'tplvar' => 'optRefs',
             'field'  => '_filtr_refid',
             'value'  => $_REQUEST['_filtr_refid'],
             '_misc'  => ' style="width: 250px;" onchange="this.form.submit();" ',
      )
    );


    $sql0 = "select c.id as id, c.title as title, p.title as reftitle, p.id as refid from com_prefs as p join company as c on c.comref_id=p.id";
    if ( $id )           { $clause = "c.id=".$this->db->quote($id);     }
    elseif ( $refid  )   { $clause = "p.id=".$this->db->quote($refid);  }
    else                 { $clause = "1=1";                             }
    $data = $this->db->sql2array( $sql0." where ".$clause );
    foreach ($data as $kd=>$vd) $data[$kd]['title'] = htmlspecialchars($data[$kd]['title']); // js+html disasters
    $this->dbgMsg ( 'unit::ComList_construct( '.$id.', '.$refid.')  data : ', $data , $pre=1, $htmlqt=0 );
    $this->tpl->Loop ( $data, 'com_prefs.html:ComList', $store_to="ComList", $append=0, $implode=false, $wrap_empty=true );
  }

  function ComEdit_construct( $id = null, $ref_id = null ) {
    $this->tpl->Set( 'Page:Title', 'Запись об услуге компании' );
    require ( 'presets/form/custom.form_exists.php' ); // форма присутствует
    if ( $id ) { // если указан id
      $this->tbl->Init( $this->db_rec );
      $a = $this->tbl->Load( $id ); // загружаем все записи о компаниях
      $ref_id = $a['comref_id'];
      $this->form->getHTML( array('tpl' => 'plain:Hidden', // скрытое поле id
                                  'tplvar'=>'hdnId',
                                  'field'=>'_fdata_id',
                                  'value'=>$id, ) );
      if ( $a['logo'] && file_exists($_SERVER['DOCUMENT_ROOT'].$a['logo']) ) // если в рекордсете есть данные о лого и файл существует
      {   $this->dbgMsg('', 'inner - a[logo] && file_exists');
          $this->tpl->Set('HttpPic', '/'.$a['logo']); // URL лого
          $this->form->getHTML(  array( 'tpl'       => 'plain:CheckLab', // чекбокс для удаления загруженного ранее файла
                                        'tplvar'    => 'formFileDrop',
                                        'field'     => '_extra_filedrop',
                                        '_desc'     => 'удалить загруженный ранее файл',
                                        '_field_id' => '_xfile_drop'.$id,
                                        'value'     => 1,                   ) );
          $this->tpl->Parse($this->tpf.':Pic', 'Pic'); // парсим блок с картинкой
      }

      $this->tbl->Init( $this->db_ref );
      $ref = $this->tbl->Load( $a['comref_id'] );

      $this->form->getHTML( array( 'tpl'    => 'plain:Submit',
                                   'field'=>'btnSave',
                                   'tplvar' => 'formSubmit',
                                   'value'  => 'Сохранить',   )
                          );

    } else {   // $this->tpl->Set('Page:Title', '' );
      if ($ref_id) {
        $this->tbl->Init( $this->db_ref );
        $ref = $this->tbl->Load( $ref_id ); // $a['comref_id'] );
      }
      $btn_cfg =            array( 'tpl' => 'plain:Submit',
                                   'field'=>'btnSave',
                                   'tplvar' => 'formSubmit',
                                   'value'  => 'Создать',
                                   '_misc'  => ' disabled="disabled"'.
                                               ' onclick="if (!this.form._fdata_title.value ){ alert(\'заполните поле &quot;компания&quot;\');return false;}"', );
      if ( $ref_id?$ref_id:($id?$ref['ref_id']:null)  ) unset($btn_cfg['_misc']);
      $this->form->getHTML( $btn_cfg );
    }

    $com_form = array();     /*  'title' 'about' 'description' 'phone' 'fax' 'email' 'logo'  'website'  'contract_id'   */
    $rdata[0] = ' [ --- выбрать --- ] ';

    $this->tbl->Init( $this->db_ref ); // все рубрики  для селекта
    $rfa = $this->tbl->Load( null, '1=1 order by id asc' );
    foreach ( $rfa as $i=>$r ) {
      // $r0 = $this->getSizeOfRef($r['id']);
      $rdata[$r['id']] = $r['title']; // .' ('.intval($r0[0]['recnum']).') ';
    }

    $com_form['formSelectRef'] = $this->form->getHTML(
      $select_ref=array (
        'tpl'       => 'options:Select',
        'data'      => $rdata,
        'tplvar'    => 'formSelectRef',
        'field'     => '_fdata_comref_id',
        'value'     => ($ref_id?$ref_id:($id?$ref['ref_id']:null)),
        '_misc'     => ' class="w100" onchange="location.href=\'http://www.e1.ru\'+location.pathname+\'?_filtr_refid=\'+this.value;" ',
        // '_misc'     => ' class="w100" onchange="if (this.value==0) this.form.btnSave.disabled=\'disabled\'; else {this.form.btnSave.removeAttribute(\'disabled\');this.form.btnSave.disabled=null;}" ',
      )
    );
    $opts  = $this->opts;

    $this->dbgMsg ( ' opts: ', $opts , $pre=1, $htmlqt=0 );
    foreach ($opts as $k=>$opt ) {   // if ($opt=='logo') continue;
      $com_form[$opt] = array (
        'tpl'       => 'plain:Text',
        'tplvar'    => 'form'.ucfirst($opt),
        'field'     => '_fdata_'.$opt,
        'value'     => ($id?htmlspecialchars($a[$opt]):null),
        '_misc'     => ' class="w100"',
      );
    }

    // $com_form['title']['value'] =  htmlspecialchars($com_form['title']['value']) ;
    $com_form['description']['tpl'] = 'plain:Textarea';
    $com_form['description']['_rows'] = 10;
    $com_form['about']['tpl'] = 'plain:Textarea';
    $com_form['about']['_misc'] = ' style="width:96%;height:3.6em;" ';
    // $com_form['about']['_rows'] = 2;
    $com_form['logo']['tpl']   = 'plain:Upload';
    $com_form['logo']['_misc'] = ' onchange="try{if(this.value){this.form._extra_filedrop.checked=\'checked\';getNode(\'comImg\').className = \'disappear\';}}catch(e){}" ';

    $com_form['price']['tpl']   = 'plain:Textarea';
    $com_form['price']['_misc'] = ' class="mceEditor"';
    $com_form['price']['_cols'] = 65;
    $com_form['price']['_rows'] = 25;

    // $com_form['logo']['_misc'] = ' onchange="alert(\'changed\')" ';


    // $this->dbgMsg ( ' $com_form: ', $com_form , $pre=1, $htmlqt=1 );
    $odx = 1;
    foreach ( $opts as $k => $opt ) {
        if ( empty($ref_id) ||  $ref['fmask'] & pow(2,$odx) )
            $this->form->getHTML( $com_form[ $opt ] );
        $odx++;
    } // $this->tpl->Parse('com_prefs.html:NavComList', 'SubNav' );

    $this->Pics_construct( $id );
    $this->tpl->Parse('com_prefs.html:ComForm', 'ComForm'   );
    $this->tpl->Parse('com_prefs.html:PageCom', 'HTML:Body' );
  }

  function ComEdit_save() {
    $fdata  = UtilityCore::arrKeysPfxStrip('_fdata_', $_REQUEST );  $this->dbgMsg( 'Edit_Save() _fdata_ : ',  $fdata  );
    $upcfg = array( 'db_table'=>$this->guide->db_pfx.$this->db_rec, 'form_field'=>'_fdata_logo',
                    'db_field'=>'logo', 'pic_save_path'=> $this->pic_save_path, 'pic_width' => 100, );
    $fdata  = $this->ImageUpload_save( $fdata, $upcfg );

    $this->tbl->Init( $this->db_rec );
    $this->tbl->SetData( $fdata );
    $r =  $this->tbl->Save();
    $this->dbgMsg ( 'table '.$this->db_rec.',  this->tbl->Save(): ', $r , $pre=1, $htmlqt=0 );

  }

  /*
     ImageUpload_save( $fdata, $upcfg )
     $fdata - "извлеченный" (т.е. без префиксов в именах полей) массив данных формы
     $upcfg  - конфиг-массив, ключи:
       'db_table' - таблица в БД,
       'form_field' - имя поля загрузки в форме,
       'db_field' - имя поля в таблице БД, должно совпадать с извлеченным ключом в форме
  */

  function getSizeOfRef( $refid ) {
      $sql0 = "select count( * ) as recnum from company as c join com_prefs as p on c.comref_id=p.id where p.id=".$this->db->quote($refid);
      $this->dbgMsg ( '###'.$sql0.' :: ', $r0 , $pre=1, $htmlqt=0 );
      $r0 = $this->db->sql2array($sql0);
      return $r0 ;
  }


  function ImageUpload_save( $fdata, $upcfg ) {
    if ( empty($upcfg['_filedrop']) ) $upcfg['_filedrop'] = '_extra_filedrop';
    $this->dbgMsg ( ' _FILES: ', $_FILES , $pre=1, $htmlqt=1 );    // save picture if uploaded :
    if ( !$_FILES[ $upcfg['form_field'] ]['error'] ) { // remove old picture if update - first
      $this->tbl->Init( $upcfg['db_table'] ); $this->dbgMsg('tbl->Load (id : '.$this->tbl->db_id.')',$fdata[$this->tbl->db_id],$pre=1,$htmlqt=0);
      $a = $this->tbl->Load( $fdata[$this->tbl->db_id] );
      $picfile=$_SERVER['DOCUMENT_ROOT'].$a[ $upcfg['db_field'] ]; $this->dbgMsg( '#RS :', $a );
      if ( $a[ $upcfg['db_field'] ] && is_file($picfile) ) {  $this->dbgMsg( '### filedrop: ', $picfile );
          unlink( $picfile ); // ... drop it physically
      }

      $cfg = array( "field_name" => $upcfg['form_field'], "save_path" => $upcfg['pic_save_path'],
                    "exts_whitelist"=>array('jpg','jpeg','gif','jpe','png')  );
      $upload = &new FileUpload( $cfg );
      $_e = $upload->Handle();
      $this->dbgMsg ( ' upload->cfg: ', $upload->cfg , $pre=1, $htmlqt=1 );
      if ($_e) {  trigger_error( "error #".$_e." @upload ".$cfg["field_name"]." log:".var_export($upload->cfg["file_error"],true), E_USER_WARNING); }
      else     { $imgfile = $upload->cfg['path_to'].$upload->cfg['name_to'];
                 @chmod( $imgfile, 0755 );
                 list($o_width, $o_height, $o_type, $o_attr) = getimagesize( $imgfile );
                 $_opts = '';
                 if ( $upcfg['pic_width'] && $upcfg['pic_height']) $_opts .= " -w ".$upcfg['pic_width']." -h ".$upcfg['pic_height'];
                 elseif ( $upcfg['pic_width' ] && $o_width != $upcfg['pic_width']  )  $_opts .= " -w ".$upcfg['pic_width' ];
                 elseif ( $upcfg['pic_height'] && $o_height!= $upcfg['pic_height'] )  $_opts .= " -h ".$upcfg['pic_height'];
                 if ($_opts!='')
                   system( $this->guide->resizer." -f ".$imgfile." -o ".$imgfile.$_opts );
                 $fdata[$upcfg['db_field']] = $upload->cfg["save_path"].$upload->cfg["name_to"];
               }
    } elseif (  $_POST[ $upcfg['_filedrop'] ] ) {  // $_POST['_extra_filedrop']
      $this->dbgMsg('### _extra_filedrop', $_POST[ $upcfg['_filedrop'] ]);

      $this->tbl->Init( $upcfg['db_table'] );
      $a = $this->tbl->Load( $fdata['id'] );
      $picfile = $_SERVER['DOCUMENT_ROOT'].$a[ $upcfg['db_field'] ];   $this->dbgMsg( '### RS picfile : ', $picfile );
      if ( $a[ $upcfg['db_field'] ] && file_exists( $picfile) ) {      $this->dbgMsg( '### filedrop : ', $picfile );
          unlink( $picfile );                 // ... drop it physically
          $fdata[ $upcfg['db_field'] ] = '';  // then clear filename at DB
      }
    }

    // $fdata[ $upcfg['db_field'] ] = ''; // unset( $fdata[ $upcfg['db_field'] ]);
    return $fdata;
  }

  /*  TODO customize
      $fdata['id'] > upcfg['data_id']
      $tbl->db_id   > $upcfg[ 'db_id' ]
      $this->tbl->Init( $upcfg['db_table'] ); > $this->tbl->Init( $upcfg['db_table'], $upcfg[ 'db_id' ] );
  */

  function Pics_construct( $id=null ) {
    if (!$id) {
      $this->tpl->Parse( $this->tpf.':NoPicArea', $store_to='PicArea' );
      return false;
    }

    $this->form->getHTML(  array( 'tpl'       => 'plain:Upload',
                                  'tplvar'    => 'formUpload',
                                  '_misc'     => ' id="_fdata_newpic1"  onchange="fpicPopulate(this);"',
                                  'field'     => '_fdata_newpic1',   ) );
    $this->tbl->Init( $this->guide->db_pfx.'com_stuff' );
    $a = $this->tbl->Load( null, "company_id=".$this->db->quote($id) );  $this->dbgMsg('###a : ',$a, 1,1);

    if ( empty($a) ) $a = array();
    // if ( $a['pic'] && file_exists($_SERVER['DOCUMENT_ROOT'].$a['pic'])) $this->dbgMsg('','a[pic] && file_exists');
      foreach ($a as $k=>$v ) {
        $pic = array();
        $pic['HttpPic']      = $v['pic']; // $this->tpl->Set('HttpPic', '/'.$v['pic']);
        $pic['HttpPicThumb']      = $v['path'].'thumb.'.$v['file']; // $this->tpl->Set('HttpPic', '/'.$v['pic']);
        $this->dbgMsg( $_SERVER['DOCUMENT_ROOT'], $_SERVER['DOCUMENT_ROOT'] );
        $this->dbgMsg( 'file_exists('.$_SERVER['DOCUMENT_ROOT'].$pic['HttpPicThumb'].')', file_exists($_SERVER['DOCUMENT_ROOT'].$pic['HttpPicThumb']));
        $this->dbgMsg( 'file_exists('.$_SERVER['DOCUMENT_ROOT'].$pic['HttpPic'].')', file_exists($_SERVER['DOCUMENT_ROOT'].$pic['HttpPic']));
        if (!file_exists($_SERVER['DOCUMENT_ROOT'].$pic['HttpPicThumb']))    $pic['HttpPicThumb'] = $pic['HttpPic'];
        list($pic['pic_width'], $pic['pic_height'], $o_type, $o_attr) = getimagesize( $_SERVER['DOCUMENT_ROOT'].$pic['HttpPic'] );
        list($pic['tmb_width'], $pic['tmb_height'], $t_type, $t_attr) = getimagesize( $_SERVER['DOCUMENT_ROOT'].$pic['HttpPicThumb'] );
                                $pic['pic_width']  = 40+$pic['pic_width'];
                                $pic['pic_height'] = 80+$pic['pic_height'];
        $pic['HttpPicThumb'] = '/'.$pic['HttpPicThumb'];
        $pic['HttpPic'] = '/'.$pic['HttpPic'];
        $pic['formFileDrop'] = $this->form->getHTML(   array( 'tpl'       => 'plain:CheckLab',        // 'tplvar'    => 'formFileDrop',
                                                              'field'     => '_extra_filedrop['.$v['id'].']',
                                                              '_desc'     => 'удалить',
                                                              '_field_id' => '_xfile_drop'.$v['id'],
                                                              'value'     => 1,                   ) );
        $pic_data[] = $pic;
      }
                        $this->dbgMsg ( ' pic_data : ', $pic_data , $pre=1, $htmlqt=0 );
      $this->tpl->Loop( $pic_data, $this->tpf.':PicF', $store_to='PicF', $append=0, $implode=true, $wrap_empty=false );
      $picarea = $this->tpl->Parse( $this->tpf.':PicArea', $store_to='PicArea' );
      // $this->dbgMsg('###picarea ', htmlspecialchars($picarea) );

  }

  function Pics_Save ( $coid=null ) {
      if(!$coid) return false;

      $upcfg = array( "save_path" => $this->pic_path, "exts_whitelist"=>array('jpg','jpeg','gif','jpe','png')  );
      $upload = &new FileUpload( $upcfg );

      $mp_cfg = array (
        'db_table' => $this->guide->db_pfx.'com_stuff', // собственно таблица в бд
        'db_parent_id'  => 'company_id',
        'parent_id_value'  => $coid,
        'pic_w' => 200,
        //'pic_h' => 100,
        'tmb_w' => 50,
        //'tmb_h' => 50,
        'resizer' => $this->guide->resizer,
        'tbl' => &$this->tbl, // ссылка на объект класса DbUtil
      );

      $upload->MultiPic_Save( $mp_cfg );

  }



} // EOC { unitComPrefs }

?>