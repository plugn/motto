<?php

  require_once (dirname(__FILE__)."/unitAbstract.php" );
    require_once (dirname(__FILE__)."/unitUnit.php" );
      require_once (dirname(__FILE__)."/unitRef.php" );

      require_once ( "FileUpload.php" );

class unitRefFilms extends unitRef
{

  // customize here :
  var $db_table    = 'films';
  var $tpf         = 'ref_films.html';
  var $html_title  = 'Фильмы';
  var $pic_save_path = 'afisha/kino/_tmp/pic/';  // admin/_cmx/_tmp/pic/';

  function Handle() {
    if (!$this->HasAccess()) {  header('Location: http://'.$_SERVER['HTTP_HOST']."/".$this->guide->proj_root);die(); }
    if ( $this->guide->uri_parts[1]=='pic' )    {
      $this->picEdit_save();
      $this->guide->uri_parts[1]='edit'; // small fake for parent handle !
      header ('Location: http://'.$_SERVER['HTTP_HOST']."/".$this->guide->proj_root.implode('/', $this->guide->uri_parts).'/');  die();
    }
    parent::Handle();
  }

  function _Init() {
    $this->tpl->Set('HTMLTitle', $this->html_title);    // $this->tpl->Set('CurrOperation', '');
    $this->flvtpl = '<object width="362" height="300">'.
                    '<param name="movie" value="http://vd.reborn.ru/mounting/videostore/external_player.swf'.
                    '?filename=[---vrid---]"> </param><param name="wmode" value="transparent"></param> '.
                    '<embed src="http://vd.reborn.ru/mounting/videostore/external_player.swf'.
                    '?filename=[---vrid---]" type="application/x-shockwave-flash" wmode="transparent" width="362" height="300"> '.
                    '</embed> </object>';
  }

  function refEdit_construct( $id=null ) {
    $this->picEdit_construct( $id ); // we have pictures section also

    if ($id) {
      $this->tbl->Init($this->guide->db_pfx.$this->db_table);
      $a = $this->tbl->Load( $id );
      $this->form->getHTML( array('tpl' => 'plain:Hidden',
                                  'tplvar'=>'hdnId',
                                  'field'=>'_fdata_id',
                                  'value'=>$id, ) );
    }  $this->dbgMsg('a : ',$a, 1,1);

    $this->tpl->Set('filmId', $id);

    require ('presets/form/custom.form_exists.php'); // hidden field id

    $this->form->getHTML( array(  'tpl'       => 'plain:Text',
                                  'tplvar'    => 'formTitle',
                                  'field'     => '_fdata_title',
                                  'value'     => ($id?htmlspecialchars($a['title']):null),
                                  '_misc'     => ' class="w100"',     ) );

    $this->form->getHTML( array(  'tpl'       => 'plain:Text',
                                  'tplvar'    => 'formTitleRus',
                                  'field'     => '_fdata_title_rus',
                                  'value'     => ($id?htmlspecialchars($a['title_rus']):null),
                                  '_misc'     => ' id="title_rus" class="w100"',     ) );

    $this->form->getHTML(  array( 'tpl'       => 'plain:CheckStdLab',
                                  'tplvar'    => 'formPremier',
                                  'field'     => '_fdata_premier',
                                  '_desc'     => 'премьера',
                                  '_field_id' => '_fdata_id'.$id,
                                  '_empty_value' => '0',
                                  '_misc'     => ($a['premier']?' checked="checked" ':null),
                                  'value'     => 1,                   ) );

    $this->form->getHTML( array(  'tpl'       => 'plain:Textarea',
                                  'tplvar'    => 'formAnnounce',
                                  'field'     => '_fdata_announce',
                                  'value'     => ($id?$a['announce']:null),
                                  '_cols'     => 40,
                                  '_rows'     => 4,
                                  '_misc'     => ' class="w100"',     ) );

    $this->form->getHTML( array(  'tpl'       => 'plain:Textarea',
                                  'tplvar'    => 'formDesc',
                                  'field'     => '_fdata_description',
                                  'value'     => ($id?$a['description']:null),
                                  '_cols'     => 40,
                                  '_rows'     => 8,
                                  '_misc'     => ' class="w100"',     ) );

    $this->form->getHTML( array(  'tpl'       => 'plain:Textarea',
                                  'tplvar'    => 'formRole',
                                  'field'     => '_fdata_role',
                                  'value'     => ($id?$a['role']:null),
                                  '_cols'     => 40,
                                  '_rows'     => 3,
                                  '_misc'     => ' class="w100"',     ) );

    $this->form->getHTML( array(  'tpl'       => 'plain:Text',
                                  'tplvar'    => 'formDuration',
                                  'field'     => '_fdata_duration',
                                  'value'     => ($id?$a['duration']:null),
                                  '_misc'     => ' class="w100"',     ) );

    $this->form->getHTML( array(  'tpl'       => 'plain:Text',
                                  'tplvar'    => 'formYear',
                                  'field'     => '_fdata_year',
                                  'value'     => ($id?$a['year']:null),
                                  '_misc'     => ' class="w100"',     ) );

    $this->form->getHTML( array(  'tpl'       => 'plain:Text',
                                  'tplvar'    => 'formCountry',
                                  'field'     => '_fdata_country',
                                  'value'     => ($id?htmlspecialchars($a['country']):null),
                                  '_misc'     => ' class="w100"',     ) );

    $this->form->getHTML( array(  'data'   => array( 0=>'скрыт', 1=>'открыт' ),
                                  'tpl'    => 'options:Select',
                                  'tplvar' => 'formStatus',
                                  'value'  => ($id?$a['status']:null),
                                  'field'  => '_fdata_status',    ) ) ;

    $this->form->getHTML( array(  'tpl'       => 'plain:Text',
                                  'tplvar'    => 'formWeight',
                                  'field'     => '_fdata_weight',
                                  'value'     => ($id?$a['weight']:'25'),
                                  '_misc'     => ' class="w100"',     ) );

    // film-weight only for portal moderator available
    if ( $this->guide->user['role']=='portal' ) $this->tpl->Parse( $this->tpf.':FilmWeight_ROW', 'FilmWeight_ROW' );


    $this->form->getHTML( array(  'tpl'       => 'plain:Text',
                                  'tplvar'    => 'formDirector',
                                  'field'     => '_fdata_director',
                                  'value'     => ($id?htmlspecialchars($a['director']):null),
                                  '_misc'     => ' class="w100"',     ) );

    $this->form->getHTML( array(  'tpl'       => 'plain:Text',
                                  'tplvar'    => 'formComposer',
                                  'field'     => '_fdata_composer',
                                  'value'     => ($id?htmlspecialchars($a['composer']):null),
                                  '_misc'     => ' class="w100"',     ) );

    $this->form->getHTML( array(  'tpl'       => 'plain:Text',
                                  'tplvar'    => 'formGenre',
                                  'field'     => '_fdata_genre',
                                  'value'     => ($id?htmlspecialchars($a['genre']):null),
                                  '_misc'     => ' class="w100"',     ) );

    //
    $this->form->getHTML( array(  'tpl'       => 'plain:Text',
                                  'tplvar'    => 'formVideo',
                                  'field'     => '_fdata_video',
                                  'value'     => ($id?htmlspecialchars($a['video']):''),
                                  '_misc'     => ' id="fvideo" class="w100"',     ) );

    if ( $ppm = preg_match('{(\[video\])([-0-9a-zA-Z]*?)(\[/video\])}i',$a['video'],$m4) ) {
        $flvcode  = str_replace( '[---vrid---]',$m4['2'], $this->flvtpl );
        $vdr = array ('VideoReady'=>1, 'VideoPlayer'=>$flvcode, 'videoCode'=>$m4[0] );
        $this->tpl->Load($vdr);

    }



  }

  function picEdit_construct( $id=null ) {
    if (!$id) {
      $this->tpl->Parse( $this->tpf.':NoPicArea', $store_to='PicArea' );
      return false;
    }

    $this->form->getHTML(  array( 'tpl'       => 'plain:Upload',
                                  'tplvar'    => 'formUpload',
                                  '_misc'     => ' id="_fdata_newpic1"  onchange="fpicPopulate(this);"',
                                  'field'     => '_fdata_newpic1',   ) );
    $this->tbl->Init( $this->guide->db_pfx.'film_pics' );
    $a = $this->tbl->Load( null, "film_id='".$id."'" );
    $this->dbgMsg('###a : ',$a, 1,1);

    if ( empty($a) ) $a = array();
    // if ( $a['pic'] && file_exists($_SERVER['DOCUMENT_ROOT'].$a['pic']) ) $this->dbgMsg('', 'inner - a[pic] && file_exists');
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
      $this->tpl->Loop( $pic_data, $this->tpf.':Pic', $store_to='Pic', $append=0, $implode=true, $wrap_empty=false );
      $picarea = $this->tpl->Parse( $this->tpf.':PicArea', $store_to='PicArea' );
      // $this->dbgMsg('###picarea ', htmlspecialchars($picarea) );

  }

  function picEdit_save() { // $this->dbgMsg( ' picEdit_save();', '' );
    /* __form_present' => '1',   '_extra_filedrop' =>   array (    2 => '1',    4 => '1',  ), */
    if ( $_REQUEST['btnPicSave'] &&  !empty( $_REQUEST['_extra_filedrop'] ) )
    {
        foreach ($_REQUEST['_extra_filedrop'] as $pos_id => $whether_remove )  {
            if ( $whether_remove )  {    $this->dbgMsg('_extra_filedrop #', $pos_id);
              $this->tbl->Init($this->guide->db_pfx.'film_pics');
              $a = $this->tbl->Load( $pos_id );
              $picfile = $_SERVER['DOCUMENT_ROOT'].$a['pic']; $this->dbgMsg( '### RS picfile : ', $picfile );
              $tnpic   = $_SERVER['DOCUMENT_ROOT'].$a['path'].'thumb.'.$a['file'];  $this->dbgMsg( '### tnpic : ', $tnpic );
              if ( $a['pic'] && is_file($picfile) ) { unlink( $picfile ); $this->dbgMsg('### drop picfile : ', $picfile); }
              if ( is_file($tnpic) ) { unlink( $tnpic ); $this->dbgMsg('### drop thumbnail : ', $tnpic);  }
              $this->tbl->Drop($pos_id);
            }
        }
    }

    if ( !empty($_FILES) )  // if uploaded, save file and store position at DB
      foreach ( $_FILES as $field=>$data ) {
        // *** save files ***
        if ($data['error']) { $this->dbgMsg('upload error#'.$data['error'], ''); continue; }
        $cfg = array("field_name" => $field, "save_path"  => $this->pic_save_path, "exts_whitelist"=>array('jpg','jpeg','gif','jpe','png'));
        $upload = &new FileUpload( $cfg );
        $_e = $upload->Handle();
        if ($_e) {  trigger_error(   "error #".$_e." @ upload ".$cfg["name"]." log : ".var_export($upload->cfg["file_error"], true), E_USER_WARNING); }
        else     {  $imgfile = $upload->cfg['path_to'].$upload->cfg['name_to'];
                    @chmod( $imgfile, 0644 );
                    $width = 150;
                    $thumb_width = 100;
                    $thumb_file = $upload->cfg['path_to'].'thumb.'.$upload->cfg["name_to"];

                    system( $this->guide->resizer." -f ".$imgfile." -o ".$thumb_file." -w ".$thumb_width );

                    list($o_width, $o_height, $o_type, $o_attr) = getimagesize( $imgfile );
                    if ( $o_width!=$width )
                      system( $this->guide->resizer." -f ".$imgfile." -o ".$imgfile." -w ".$width );

                    $this->dbgMsg('###'.$this->guide->resizer." -f ".$imgfile." -o ".$thumb_file." -w ".$thumb_width.BR,'');
                    $this->dbgMsg('###'.$this->guide->resizer." -f ".$imgfile." -o ".$imgfile." -w ".$width.BR, '');

                    $pic_data = array( 'film_id' => $this->guide->uri_parts[2],
                                       'path'    => $upload->cfg["save_path"],
                                       'file'    => $upload->cfg["name_to"],
                                       'pic'     => $upload->cfg["save_path"].$upload->cfg["name_to"] );
        // *** store in DB ***
                    $this->tbl->Init( $this->guide->db_pfx.'film_pics' ); // $this->dbgMsg('refEdit_Save() _fdata_ : ', $fdata );
                    $this->tbl->SetData( $pic_data );
                    $r = $this->tbl->Save( $mode='insert' );  // INSERT     $this->dbgMsg('', 'hop1');
        }
      }
  }

  function HasAccess( $user=null ) {
    if ( is_null($user) ) $user = $this->guide->user;
    if ($user['role']=='kino' && $user['film_editor'] || $user['role']=='portal')  return true;
    return false;
  }


  function refItem_drop ( $id ) {
      $sql1 = 'delete from shows where film_id='.$this->db->quote($id);
      $r1 = $this->db->query( $sql1 ); $this->dbgMsg ( '  '. $sql1, $r1, $pre=1, $htmlqt=0 );
      $sql2 = 'delete from films where id='.$this->db->quote($id);
      $r2 = $this->db->query( $sql2 ); $this->dbgMsg ( '  '. $sql2, $r2, $pre=1, $htmlqt=0 );
  }


  function refList_construct() {
    // order ctrl
    $o = array(
        array('orderTxt'=>'по алфавиту','orderQS'=>'_sortby=title','_Curr'=>0 ),
        array('orderTxt'=>'по весу','orderQS'=>'_sortby=weight','_Curr'=>0 ),
        array('orderTxt'=>'по новизне','orderQS'=>'','_Curr'=>1 ),
    );
    $order_by = ' order by `id` desc';
    if ($_REQUEST['_sortby'])    {
      if ($_REQUEST['_sortby']=='title')      {
          $order_by = ' order by `title_rus` asc, `weight` desc';
          $o = array(
             array('orderTxt'=>'по алфавиту','orderQS'=>'_sortby=title','_Curr'=>1 ),
             array('orderTxt'=>'по весу','orderQS'=>'_sortby=weight','_Curr'=>0 ),
             array('orderTxt'=>'по новизне','orderQS'=>'','_Curr'=>0 ),
          );
      }
      elseif ($_REQUEST['_sortby']=='weight') {
        $order_by = ' order by `weight` desc, `title_rus` asc';
          $o = array(
             array('orderTxt'=>'по алфавиту','orderQS'=>'_sortby=title','_Curr'=>0 ),
             array('orderTxt'=>'по весу','orderQS'=>'_sortby=weight','_Curr'=>1 ),
             array('orderTxt'=>'по новизне','orderQS'=>'','_Curr'=>0 ),
          );

      }
    }
    if (!empty($o)) $this->tpl->Loop($o, $this->tpf.':OrderCtrl', 'OrderCtrl', $add=0, $implode=1, $wrap_empty=0);


    $this->tbl->Init($this->guide->db_pfx.$this->db_table);
    $rs = $this->tbl->Load(null, '1=1'.$order_by ); // '.$this->tbl->db_id );
    $this->tpl->Set('href:create', $this->guide->uri_parts[0].'/create/'.$v[$this->tbl->db_id]);

    foreach ($rs as $k=>$v) {  //  a little precise
      $rs[$k]['href:edit']   = $this->guide->uri_parts[0].'/edit/'  .$v[$this->tbl->db_id];
      $rs[$k]['href:drop']   = $this->guide->uri_parts[0].'/drop/'  .$v[$this->tbl->db_id];
      $rs[$k]['href:drop.confirm'] = ' onclick="return confirm(\'Вы уверены в уничтожении выбранной позиции ?\'); " ';
    }
    // delete privelege only for portal moderator available:     // $this->dbgMsg('rs : ', $rs, 1, 1);
    if ( $this->guide->user['role']=='portal' )      $this->tpl->Loop($rs, $this->tpf.':List', "HTML:Body");
    else                                             $this->tpl->Loop($rs, $this->tpf.':List0', "HTML:Body");

  }




} // EOC { unitRefFilms }

?>