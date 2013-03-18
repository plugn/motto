<?php

  require_once (dirname(__FILE__)."/unitAbstract.php" );
    require_once (dirname(__FILE__)."/unitUnit.php" );
      require_once (dirname(__FILE__)."/unitRef.php" );

      require_once ( "FileUpload.php" );


class unitRefHalls extends unitRef
{

  // customize here :
  var $db_table    = 'halls';
  var $tpf         = 'ref_halls.html';
  var $html_title  = '«алы';
  var $pic_save_path = 'afisha/kino/_tmp/pic/'; // 'admin/_cmx/_tmp/pic/';

  function _Init() {
    $this->tpl->Set('HTMLTitle', $this->html_title);

  }

  function refEdit_construct( $id=null ) {
    if ($id)
    {
      $this->tbl->Init($this->guide->db_pfx.$this->db_table);
      $a = $this->tbl->Load( $id );
      $this->dbgMsg('a : ',$a, 1,1);
      $place_id = $a['place_id'];
      $this->form->getHTML( array('tpl' => 'plain:Hidden',
                                  'tplvar'=>'hdnId',
                                  'field'=>'_fdata_id',
                                  'value'=>$id, ) );
      if ( $a['pic'] && file_exists($_SERVER['DOCUMENT_ROOT'].$a['pic']) )
      {
          $this->dbgMsg('', 'inner - a[pic] && file_exists');
          $this->tpl->Set('HttpPic', '/'.$a['pic']);
          $this->form->getHTML(  array( 'tpl'       => 'plain:CheckLab',
                                        'tplvar'    => 'formFileDrop',
                                        'field'     => '_extra_filedrop',
                                        '_desc'     => 'удалить загруженный ранее файл',
                                        '_field_id' => '_xfile_drop'.$id,
                                        'value'     => 1,                   ) );
          $this->tpl->Parse($this->tpf.':Pic', 'Pic');
      }

    }

    require ('presets/form/custom.form_exists.php'); // hidden field id
    // $src_arr = $this->guide->user['role']=='portal'?array(0=>' ------- '):array();
    $src_arr = array();
    $places_data = $this->getPlaces( $src_arr );
    foreach ($places_data as $pk=>$pv) {
      if ( $this->guide->uri_parts[1]=='create' && $this->guide->uri_parts[2]=='for' && is_numeric($this->guide->uri_parts[3]) )
        $place_id=$this->guide->uri_parts[3];
    }

    $this->form->getHTML( array(  'data'   => $places_data,
                                  'tpl'    => 'options:Select',
                                  'tplvar' => 'formPlace',
                                  'value'  => $place_id,
                                  'field'  => '_fdata_place_id',                ) ) ;

    $this->form->getHTML( array(  'tpl'       => 'plain:Text',
                                  'tplvar'    => 'formName',
                                  'field'     => '_fdata_name',
                                  'value'     => ($id?$a['name']:null),
                                  '_misc'     => ' id="_name" size="50" class="w100"',     ) );

    $this->form->getHTML( array(  'tpl'       => 'plain:Textarea',
                                  'tplvar'    => 'formDesc',
                                  'field'     => '_fdata_description',
                                  'value'     => ($id?$a['description']:null),
                                  '_cols'     => 40,
                                  '_rows'     => 6,                   ) );

    $this->form->getHTML(  array( 'tpl'       => 'plain:Upload',
                                  'tplvar'    => 'formUpload',
                                  '_misc'     => ' onchange="try { ce=document.getElementById(\'_xfile_drop'.$id.'\'); if (ce) ce.checked=\'checked\'; } catch(e) { alert(\'error: \'+e); } " ',
                                  'field'     => '_fdata_filedata',   ) );

  }

  function AdjustData($fdata) {    // remove old picture if update

    // save picture if uploaded
    if ( !$_FILES['_fdata_filedata']['error'] ) { // remove old picture if update
      $this->tbl->Init($this->guide->db_pfx.$this->db_table);

      $a = $this->tbl->Load( $fdata['id'] );
      $picfile = $_SERVER['DOCUMENT_ROOT'].$a['pic'];   $this->dbgMsg('### RS picfile : ', $picfile);
      if ( $a['pic'] && is_file($picfile) ) {           $this->dbgMsg('### picfile unlink: ', $picfile);
          unlink( $picfile ); // ... drop it physically
      }

      $cfg = array("field_name" => '_fdata_filedata', "save_path"  => $this->pic_save_path,
                   "exts_whitelist" => array('jpg','jpeg','gif','jpe','png') );
      $upload = &new FileUpload( $cfg );
      $_e = $upload->Handle();
      if ($_e) {  trigger_error(   "error #".$_e." @ upload ".$cfg["name"]." log : ".var_export($upload->cfg["file_error"], true), E_USER_WARNING); }
      else     {  $imgfile = $upload->cfg['path_to'].$upload->cfg['name_to'];
                  @chmod( $imgfile, 0644 );
                  $width = 250;
                  system($this->guide->resizer." -f ".$imgfile." -o ".$imgfile." -w ".$width );

                  $fdata['pic'] = $upload->cfg["save_path"].$upload->cfg["name_to"];
               }
    } elseif ( $_POST['_extra_filedrop']) {
      $this->dbgMsg('###', '_extra_filedrop');
      $this->tbl->Init($this->guide->db_pfx.$this->db_table);
      $a = $this->tbl->Load( $fdata['id'] );
      $picfile = $_SERVER['DOCUMENT_ROOT'].$a['pic']; $this->dbgMsg('### RS picfile : ', $picfile);
      if ( $a['pic'] && is_file( $picfile) ) {   $this->dbgMsg('### picfile unlink: ', $picfile);
          unlink( $picfile ); // ... drop it physically
          $fdata['pic'] = ''; // then clear filename at DB
      }
    }

    unset( $fdata['filedata']);
    return $fdata;
  }

  function refList_construct() {
    $places_data = $this->getPlaces( array('..'=>' ------------ выберите кинотеатр ------------ ') );

    if ($this->guide->user['role']=='portal') {
        $this->form->getHTML( array(  'data'   => $places_data,
                                      'tpl'    => 'options:Select',
                                      'tplvar' => 'formPlace',
                                      'value'  => $this->guide->uri_parts[2],
                                      '_misc'  => ' onchange="window.location.href=\'/'.$this->guide->proj_root.$this->guide->uri_parts[0].'/list/\'+this.options[this.selectedIndex].value;" ',
                                      'field'  => '_filtr_place_id',                ) ) ;

        $this->tpl->Parse('ref_halls.html:FormFilter','FormFilter');
    }

    $this->tbl->Init($this->guide->db_pfx.$this->db_table);
    if ($this->guide->user['role']=='kino')  $rs = $this->tbl->Load(null, 'place_id='.$this->guide->user['place_id'].' order by '.$this->tbl->db_id );
    elseif ($this->guide->uri_parts[2])      $rs = $this->tbl->Load(null, "place_id='".$this->guide->uri_parts[2]."' order by ".$this->tbl->db_id );
    else   {
                                             $place_id = array_slice( array_keys($places_data), 1 , 1 );
                                             $rs = $this->tbl->Load(null, "place_id='".$place_id."' order by ".$this->tbl->db_id );
                                             $this->dbgMsg( 'places_data', $place_id); // $this->dbgMsg( 'rs : ', $rs );
    }

    /* DONE. занесение залов модератором портала */
    $this->tpl->Set('href:create', $this->guide->uri_parts[0].'/create/for/'.$this->guide->uri_parts[2]);
    $this->dbgMsg ( 'list href: ', $rs , $pre=1, $htmlqt=0 );

    foreach ($rs as $k=>$v) {  //  a little precise
      $rs[$k]['href:edit']   = $this->guide->uri_parts[0].'/edit/'  .$v['id'];
      $_q = $this->db->sql2array($_sqlC='SELECT count(*) as rws FROM `shows` WHERE `hall_id` = '.$this->db->quote($v['id']));
      $this->dbgMsg ( 'зал '.$v['name'].' : ', $_q , $pre=1, $htmlqt=0 );
      if ( !empty($_q[0]['rws']) ) {
        $rs[$k]['href:drop'] = "#";
        $rs[$k]['href:drop.confirm'] = ' onclick="alert(\'”далить зал невозможно: он используетс€ в расписании! \');this.blur();return false;" ';
      } else {
        $rs[$k]['href:drop']   = $this->guide->uri_parts[0].'/drop/'  .$v['id'];
        $rs[$k]['href:drop.confirm'] = ' onclick="return confirm(\'¬ы уверены в уничтожении выбранной позиции ?\'); " ';
      }
    }

    $this->dbgMsg('rs : ', $rs, 1, 1);
    $this->tpl->Loop($rs, $this->tpf.':List', "HTML:Body",$append=0, $implode=false, $wrap_empty=true);
  }

  function refItem_drop ( $id ) {
     $sql2 = 'delete from halls where id='.$this->db->quote($id);
     $r2 = $this->db->query( $sql2 ); $this->dbgMsg ( '  '. $sql2, $r2, $pre=1, $htmlqt=0 );
  }



} // EOC { unitRefColors }

?>