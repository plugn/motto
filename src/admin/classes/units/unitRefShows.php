<?php

  require_once (dirname(__FILE__)."/unitAbstract.php" );
    require_once (dirname(__FILE__)."/unitUnit.php" );
      require_once (dirname(__FILE__)."/unitRef.php" );

class unitRefShows extends unitRef
{

  var $db_table    = 'shows';
  var $tpf         = 'ref_shows.html';
  var $html_title  = '—еансы';

  function Handle() {
    if ( $this->guide->uri_parts[1]=='time' && $_REQUEST[$this->guide->form_exists_var.'2'] )    {
      $this->timeEdit_save();
      $this->guide->uri_parts[1]='edit'; // small fake for parent handle !
      header ('Location: http://'.$_SERVER['HTTP_HOST']."/".$this->guide->proj_root.implode('/', $this->guide->uri_parts).'/');  die();
    }
    parent::Handle();
  }

  function _Init() {
    $this->tpl->Set( 'HTMLTitle', $this->html_title );
    $this->sql10 = "select s.id as showid, s.id as id, h.name as hall, f.title_rus as film_rus, ".
               " s.date_start as date_start, p.place_name as place_name, ".
               " s.date_stop as date_stop, s.price_word as price_word, s.price_rub as price_rub, s.status as status ".
               " from ".$this->guide->db_pfx."shows as s join ".
               $this->guide->db_pfx."films as f on s.film_id=f.id join ".
               $this->guide->db_pfx."halls as h on s.hall_id=h.id join ".
               $this->guide->db_pfx."place as p on s.place_id=p.place_id ";
  }

  function refList_construct() {  // $this->tpl->Set('TimeCtrl', $this->timeEdit_construct( $id ) );
    // filter: places
    $places_data = $this->getPlaces( array( '..'=>' :: : : : : : : : : : : : : : все площадки : : : : : : : : : : : :: ' ) );
    if ($this->guide->user['role']=='portal') {
      $this->form->getHTML( array( 'data'   => $places_data,
                                   'tpl'    => 'options:Select',
                                   'tplvar' => 'formPlace',
                                   'value'  => $this->guide->uri_parts[2],
                                   '_misc'  => ' onchange="window.location.href=\'/'.$this->guide->proj_root.$this->guide->uri_parts[0].
                                               '/list/\'+this.options[this.selectedIndex].value;" ',
                                    'field'  => '_filtr_place_id',                ) ) ;
      $this->tpl->Parse( $this->tpf.':FormFilter','FormFilter' );
    }

    // List of Shows
    $sql10 = $this->sql10;
    if ($this->guide->user['role']=='kino')     $sql01 = "s.place_id=".$this->db->quote( $this->guide->user['place_id']);
    elseif ($this->guide->uri_parts[2])         $sql01 = "s.place_id=".$this->db->quote( $this->guide->uri_parts[2] );
    else                                        $sql01 = "s.place_id=s.place_id";

    // how  many films to draw at all
    // $sql21 = "select distinct film_id from ".$this->guide->db_pfx."shows as s where ".$sql01." order by s.".$this->tbl->db_id.' desc';
    $sql21 = "select distinct film_id from ".$this->guide->db_pfx."shows as s ".
             "join films as f on s.`film_id` = f.`id` ".
             "where ".$sql01." order by f.`weight` desc";

    // JOIN films AS f ON s.film_id = f.id ORDER BY f.weight DESC
    $films = $this->db->sql2array( $sql21 );
    $this->dbgMsg(' ### how  many films to draw at all '.$sql21.' ; Result : ', $films );

    $group_by = 'group by s.film_id, s.place_id, s.hall_id';
    if ( !empty( $films ) )
    foreach ( $films as $f=>$film ) {
        $sql = $sql10 . " where s.film_id=".$film['film_id']." and ".$sql01.' '.$group_by;
        $rs = $this->db->sql2array( $sql );  $this->dbgMsg('@@@area : '.$sql, $rs);
        if ( !empty($rs) )
          foreach ($rs as $k=>$v) {  //  a little precise
            $rs[$k]['href:edit']   = $this->guide->uri_parts[0].'/edit/'  .$v['showid'];  // $v[$this->tbl->db_id];
            $rs[$k]['href:drop']   = $this->guide->uri_parts[0].'/redrop/'.$v['showid'];  // $v[$this->tbl->db_id];
            $rs[$k]['href:drop.confirm'] = ' onclick="return confirm(\'¬ы уверены в уничтожении выбранной позиции ?\'); " ';
        }    // $this->dbgMsg( ' @@@shows [ '.$sql.' ] :: ', $rs );

        $this->tpl->Set( 'FilmRus', $v['film_rus'] );
        $this->tpl->Loop( $rs, $this->tpf.':List', "List", $append=1, $implode=false, $wrap_empty=true );
    }      // $sql = $sql10 . " where " .$sql01 . " order by s.".$this->tbl->db_id; // $rs = $this->db->sql2array( $sql );

    $this->tpl->Set( 'href:create', $this->guide->uri_parts[0].'/create/' );
    $this->tpl->Parse( $this->tpf.':RefList', "HTML:Body" );
  }

  function refEdit_construct( $id=null ) {  //   $this->picEdit_construct( $id ); // we have pictures section also
    if ( !is_null($id) ) {
      $this->tbl->Init($this->guide->db_pfx.$this->db_table);
      $a = $this->tbl->Load( $id );
      $this->form->getHTML( array('tpl' => 'plain:Hidden',
                                  'tplvar'=>'hdnId',
                                  'field'=>'_fdata_id',
                                  'value'=>$id, ) );
    }

    $this->dbgMsg('### id : ',$id, 1,1);  // $this->tpl->Set('showId', $id);
    $this->dbgMsg('a : ',$a, 1,1);  // $this->tpl->Set('showId', $id);


    require ( 'presets/form/custom.form_exists.php' ); // hidden field id

    // фильмы...
    if ($id) $films_data = array();
    else     $films_data[0] = ' -------------- фильм ----------------  ';
    $this->tbl->Init($this->guide->db_pfx.'films');
    $films = $this->tbl->Load( null, "1=1 order by weight desc, title_rus asc" ); // ...они в африке
    foreach ( $films as $k=>$v )
        $films_data[$v['id']] = $v['title_rus'].' ( '.$v['weight'].')';
    $this->form->getHTML( array( 'data'   => $films_data,
                                 'tpl'    => 'options:Select',
                                 'tplvar' => 'formFilm',
                                 'value'  => ($id?$a['film_id']:null),
                                 'field'  => '_fdata_film_id',
                                 '_misc'  => ' class="w100" ',
                                 ) ) ;


    // площадки
    if ($this->guide->user['role']=='portal' && $this->guide->uri_parts[1]!=='edit')
            $places_data = $this->getPlaces( array( 0=>' -------------- площадка ----------------- ' ) );
    else    $places_data = $this->getPlaces( array() );

    $formPlace = $this->form->getHTML( array( 'data'   => $places_data,
                                 'tpl'    => 'options:Select',
                                 'tplvar' => 'formPlace',
                                 'value'  => ($a['place_id']?$a['place_id']:null),
                                 'field'  => '_fdata_place_id',
                                 '_misc'  => ' class="w100" '.
                                             " onchange=\"showHalls(this.options[this.selectedIndex].value, 'node_form_hall', 'show_halls', {});\"",
                                 ) ) ;

    // залы
    if ($this->guide->uri_parts[1]!=='edit')  $halls_data[0] = ' -------------- зал ----------------  ';
    else                                      $halls_data = array();

    $place_id = $a['place_id'];
    if ( $this->guide->user['role']=='kino') $place_id = $this->guide->user['place_id'];

    if ( $place_id ) { // если площадка известна, то можно выбрать зал
      $this->tbl->Init($this->guide->db_pfx.'halls');
      $halls = $this->tbl->Load( null, "place_id='".$place_id."'" );
      $this->dbgMsg('@halls : ', $halls);

    foreach ( $halls as $k=>$v )
        $halls_data[$v['id']] = $v['name'];
    $this->dbgMsg('@halls_data : ', $halls_data);

    }  //  else  $this->tpl->Set('formHall', ' &nbsp; '  );
    $this->form->getHTML( array( 'data'   => $halls_data,
                                 'tpl'    => 'options:Select',
                                 'tplvar' => 'formHall',
                                 'value'  => ($id?$a['hall_id']:null),
                                 'field'  => '_fdata_hall_id',
                                 '_misc'  => ' class="w100" ',
                               ) ) ;

    $this->form->getHTML( array(  'tpl'       => 'plain:Text',
                                  'tplvar'    => 'formTitleRus',
                                  'field'     => '_fdata_title',
                                  'value'     => ($id?$a['title']:null),
                                  '_misc'     => ' class="w100"',
                               ) );

    $this->form->getHTML( array(  'tpl'       => 'plain:Text',
                                  'tplvar'    => 'formPriceWord',
                                  'field'     => '_fdata_price_word',
                                  'value'     => ($id?$a['price_word']:null),
                                  '_misc'     => ' class="w100"',
                               ) );
    // и статус
    $this->form->getHTML( array( 'data'   => array('published'=>'опубликован',  'closed'=>'закрыт', ), // 'prepared'=>'подготовка', 'new'=>'новый' ),
                                 'tpl'    => 'options:Select',
                                 'tplvar' => 'formStatus',
                                 'value'  => ($id?$a['status']:null),
                                 'field'  => '_fdata_status',
                                 '_misc'  => ' class="w100" ',
                                 ) );

    // даты
    $sql =  "select * from ".$this->guide->db_pfx."shows where ".
            "film_id=" .$this->db->quote( $a['film_id']  )." and ".
            "place_id=".$this->db->quote( $a['place_id'] )." and ".
            "hall_id=" .$this->db->quote( $a['hall_id']  )." order by date_start" ;

    $r = $this->db->sql2array( $sql );
    $this->dbgMsg ( ' triad sql: '.$sql."\n", $r, $pre=1, $htmlqt=0 );
    $rj = 0;
    if ( is_array($r) && !empty($r) )
    foreach ($r as $rk => $rv) {
      $this->tpl->Append('TimeCtrl', $this->timeEdit_construct( $rv['id'] ) );
      $r[$rk]['formDateStart'] = $this->form->getHTML( array(  'tpl'       => 'plain:Text', // 'tplvar'    => 'formDateStart',
                                      'field'     => '_fdata_date_start['.$rv['id'].']',
                                      'value'     => ($rv['date_start']?$rv['date_start']:null),
                                      '_misc'     => ' id="_date_start_id'.$rv['id'].'"',
                                   ) );
      $r[$rk]['formCALDateStart'] = $this->form->getHTML( array(
                                      'tpl'       => 'plain:Calendar0',  // 'tplvar'    => 'formCALDateStart',
                                      // 'field'          => '_fdata_date_start['.$rv['id'].']', // oops, linked to field grown upper
                                      '_calFieldID'    => '_date_start_id'.$rv['id'],
                                      '_calDateFormat' => 'yyyy-mm-dd',   // date format
                                      '_calAlign'      => 'right',        // calendar align
                                   ) );
      $r[$rk]['formDateStop'] = $this->form->getHTML( array(
                                      'tpl'       => 'plain:Text', // 'tplvar'    => 'formDateStop',
                                      'field'     => '_fdata_date_stop['.$rv['id'].']',
                                      'value'     => ($rv['date_stop']?$rv['date_stop']:null),
                                      '_misc'     => ' id="_date_stop_id'.$rv['id'].'"',
                                   ) );
      $r[$rk]['formCALDateStop'] = $this->form->getHTML( array(
                                      'tpl'       => 'plain:Calendar0', // 'tplvar'    => 'formCALDateStop',
                                      // 'field'     => '_fdata_date_stop',
                                      '_calFieldID'    => '_date_stop_id'.$rv['id'],
                                      '_calDateFormat' => 'yyyy-mm-dd',   // date format
                                      '_calAlign'      => 'right',        // calendar align
                                   ) );
      $r[$rk]['trNum'] = $rj++;
    } $this->dbgMsg ( ' triad sql: '.$sql."\n", $r, $pre=1, $htmlqt=0 );

    $this->tpl->Loop( $r, 'ref_shows.html:ShowPeriods', 'ShowPeriods', $add=0, $implode=0, $wrap_empty=true );

  }

  function refEdit_Save() {
    $fdata  = UtilityCore::arrKeysPfxStrip('_fdata_', $_REQUEST );  $this->dbgMsg('RefShows::refEdit_Save() _fdata_ : ',  $fdata  );
    if (!($fdata['film_id'] && $fdata['place_id'] && $fdata['hall_id'] ) )     {
          header ('Location: http://'.$_SERVER['HTTP_HOST']."/".$this->guide->proj_root.implode('/', $this->guide->uri_parts).'/');  die();
    }

    $date_start = $_REQUEST['_fdata_date_start'];  unset($_REQUEST['_fdata_date_start']);
    $date_stop = $_REQUEST['_fdata_date_stop'];   unset($_REQUEST['_fdata_date_stop']);
    if ( is_array($date_start) && !empty($date_start) ) {
        foreach ( $date_start as $id=>$value ) {
              $data[$id]['date_start'] = $value;
              $data[$id]['id']        = $id;
        }
    }
    if ( is_array($date_stop) && !empty($date_stop) )  {
        foreach ( $date_stop as $id=>$value ) {
              $data[$id]['date_stop'] = $value;
              $data[$id]['id']        = $id;
        }
    }
    $this->dbgMsg ( 'RefShows::refEdit_Save(); dates: ', $data , $pre=1, $htmlqt=0 );
    if (is_array($data) && !empty($data) )  {
        $this->tbl->Init($db_table = $this->guide->db_pfx.$this->db_table);

        foreach ( $data as $id => $rs ) {
          $this->tbl->SetData($rs); // $this->tbl->db_protect = true;
          $r = $this->tbl->Save($mode='update'); // $this->tbl->db_protect = false;
        }
    }

    $addata = UtilityCore::arrKeysPfxStrip( '_add_', $_REQUEST  );  $this->dbgMsg('RefShows::refEdit_Save() _addata_ : ', $addata );
    if (is_array($addata) && !empty($addata) )  {
        foreach ( $addata as $field=>$pair )   {
          foreach ($pair as $pk=>$pv)
              $add[$pk][$field] = $pv;
        }

        $this->dbgMsg('RefShows::refEdit_Save() [ add ] : ', $add );
        $this->tbl->Init($db_table = $this->guide->db_pfx.$this->db_table);
        foreach ( $add as $addk => $addv )    {  // $this->dbgMsg('RefShows::refEdit_Save() [addk: '.$addk.'] addv : ', $addv );
          $data = array_merge($fdata, $addv);    // $this->dbgMsg('RefShows::refEdit_Save() [ data ] : ', $data );
          $this->tbl->SetData($data); // $this->tbl->db_protect = true;
          $r = $this->tbl->Save($mode='insert');  // INSERT     $this->dbgMsg('', 'hop1'); // $this->tbl->db_protect = false;
        }
        return; // когда добавл€ем, все уже сохранено здесь
    }

    parent::refEdit_Save();

  }



  function timeEdit_construct( $id ) {   $this->dbgMsg('timeEdit_construct()', '');
    // require ( 'presets/form/custom.form_exists.php' ); // hidden field id
    $this->form->getHTML(         array(
        'tpl'       => 'plain:Hidden',
        'tplvar'    => $this->guide->form_exists_var.'2', // oops!
        'field'     => $this->guide->form_exists_var.'2', // вот такое совпадение
        'value'     => 1,                   ) );


    if (!$id) {
      $result = $this->tpl->Parse( $this->tpf.':NoTimeCtrl', $store_to='' );
      return $result;
    }

    $this->tbl->Init( $this->guide->db_pfx.'show_time' );
    $a = $this->tbl->Load( null, "show_id=".$this->db->quote($id)." order by start_time asc" ); $this->dbgMsg( "show_id=".$this->db->quote($id).'; a : ',$a, 1,1 );

    $this->form->getHTML(  array(
                                'tpl'       => 'plain:Text',
                                'tplvar'    => 'AddTime',
                                'value'     => "--:--",
                                '_misc'     => ' maxlength="5" size="5" id="_add_start_time1" onchange="timePopulate(this,[\'btnTimeAdd\']);" ',
                                'field'     => '_add_start_time[1]',   ) );


    if ( empty($a) ) $a = array();
    else  {
      foreach ($a as $k=>$v) {
        $a[$k]['formTime'] = $this->form->getHTML(  array(
                                    'tpl'       => 'plain:Text',
        //                          'tplvar'    => 'formTime',
                                    'value'     => ($v['start_time']?substr($v['start_time'],0,5):"00:00" ),
                                    '_misc'     => ' maxlength="5" size="5" id="_fdata_start_time'.$v['id'].'"'.
                                                   ' onchange="validateTime(this,[\'btnTimeEdit\']);" ',
                                    'field'     => '_fdata_start_time['.$v['id'].']',   ) );

        $a[$k]['formDrop'] = $this->form->getHTML(  array(
                                        'tpl'       => 'plain:CheckStdLab',
                                        'field'     => '_xtra_drop['.$v['id'].']',
                                        '_desc'     => 'удалить',
                                        '_field_id' => '_xtra_drop'.$v['id'],
                                        '_empty_value' => '0',
                                        'value'     => 1,                   ) );

      }
      $this->tpl->Parse($this->tpf.':BtnEditSave', 'BtnEditSave');
    }
    $this->dbgMsg ( 'Set(show_id) : ', $id , $pre=1, $htmlqt=0 );
    $this->tpl->Set('showid', $id);
    $result = $this->tpl->Loop( $a, $this->tpf.':TimeCtrl', $store_to='', $append=0, $implode=true, $wrap_empty=true  );
    $this->tpl->Free('showid');
    return $result;
  }

  function timeEdit_save() {  $this->dbgMsg( 'timeEdit_save()', '');
    $this->tbl->Init( $this->guide->db_pfx.'show_time' );

    if ($_REQUEST['btnTimeEdit']) {
      // удаление
      // $this->tbl->db_protect = true; // понарошку
      $data_drop   = UtilityCore::arrKeysPfxStrip( '_xtra_', $_REQUEST );
  $this->dbgMsg('@@@ data_drop ', $data_drop);
      if (!empty( $data_drop['drop']))
      //foreach ($data_drop as $field => $data ) {
      foreach ( $data_drop['drop'] as $id=>$v ) {       // $this->dbgMsg('foreach :  '. $id.' => '.$v,'');
          if ($v) {
            $drop_ids[] = $id; // what to drop from db
            $this->tbl->Drop( $id );  $this->dbgMsg('this->tbl->Drop('. $id.' )','');
          }
      }
      // }
      $this->tbl->db_protect = false; // теперь без палева

      // обновление
      $this->tbl->Cleanup( $id_aff=1 );
      $data_update = UtilityCore::arrKeysPfxStrip('_fdata_', $_REQUEST);  // $this->dbgMsg('@@@ data_update ', $data_update);
      if (!empty($data_update))
      foreach ( $data_update as $field => $data ) {
        $this->tbl->Cleanup($id_aff=1);  // !!!
        foreach ( $data as $id=>$value ) {
          if (is_array($drop_ids) &&  in_array($id, $drop_ids) ) continue ; // for faster perfomance
          $this->tbl->SetField( $field, $value );
          $this->tbl->SetField( $this->tbl->db_id, $id );
          $this->tbl->Save( $mode='update' );
        }
      }
    }

    if ($_REQUEST['btnTimeAdd']) {
      // вставка
      $this->tbl->Cleanup( $id_aff=1 );
      $data_insert = UtilityCore::arrKeysPfxStrip( '_add_', $_REQUEST );
      $this->dbgMsg('@@@ data_insert ', $data_insert );
      if ( is_numeric($this->guide->uri_parts[2]) && !empty( $data_insert ) )
      foreach ( $data_insert as $field => $data ) {
        $this->tbl->Cleanup($id_aff=1); // !!!
        foreach ( $data as $id=>$value ) {
          if ( strpos(' '.$value,'-')) continue ; // skip empty field
          $this->tbl->SetField( $field, $value.':00' );
          $this->tbl->SetField( 'show_id', $this->db->quote($this->guide->uri_parts[2]) );
          $this->tbl->Save( $mode='insert' );
        }
      }
    }


  }


  function refItem_drop( $id ) {
    if ( $id && is_numeric($id) ) {  // найти есть ли еще сеансы в этом редакторском сеансе
      if ($this->guide->uri_parts[2]==$id) {
        $sql = "select s1.id as xid, s2.id as zid from ".$this->guide->db_pfx."shows as s1 left join ".
               $this->guide->db_pfx."shows AS s2 on s2.id=".$this->db->quote($id).
               " where s1.film_id = s2.film_id and  s1.hall_id = s2.hall_id and s1.place_id = s2.place_id and s1.id <> s2.id";
        $r = $this->db->sql2array($sql);
        $this->dbgMsg ( ' : ', $r , $pre=1, $htmlqt=0 );
        if ( is_array($r) && !empty($r) ) { // и подставить первый из найденных в реферер дл€ редиректа, см. unitRef::Handle()
           $rfrr = preg_replace('{^(http:.*?/)(\d+)(/?)$}', '${1}'.$r[0]['xid'].'${3}', $_SERVER['HTTP_REFERER']);
           $this->dbgMsg ( $_SERVER['HTTP_REFERER'].' ; rfrr : ', $rfrr , $pre=1, $htmlqt=0 );
           $_SERVER['HTTP_REFERER'] = $rfrr;
        } else {
           $_SERVER['HTTP_REFERER'] = 'http://'.$_SERVER['HTTP_HOST'].'/'.$this->guide->proj_root.'shows/';
        }
      }

      $sql = "delete from ".$this->guide->db_pfx."show_time where show_id=".$this->db->quote($id);
      $r = $this->db->query($sql); $this->dbgMsg ( 'unitRefShows::refItem_drop( '.$id.' ); '.$sql.' : ', $r , $pre=1, $htmlqt=1 );
      parent::refList_drop( $id );
    } else { $this->dbgMsg ( 'unitRefShows::refItem_drop( '.$id.' ) WAS IGNORED BY FALSE CONDITION  ', '' );   }
  }

  function refList_drop( $id ) {
    if ( $id && is_numeric($id) ) { // все сеансы ред-сеанса :
        $this->dbgMsg ( '  **********: ', $a , $pre=1, $htmlqt=0 );
        $sql = "select s1.id as xid, s2.id as zid from ".$this->guide->db_pfx."shows as s1 join ".$this->guide->db_pfx."shows AS s2 on s2.id=".$this->db->quote($id).
               " where s1.film_id = s2.film_id and  s1.hall_id = s2.hall_id and s1.place_id = s2.place_id";
        $r = $this->db->sql2array($sql); $this->dbgMsg ( 'unitRefShows::refList_drop( '.$id.' ); '.$sql.' : ', $r , $pre=1, $htmlqt=1 );
        if ( is_array($r) && !empty($r) )
          foreach ( $r as $k=>$v ) {
            $sql = "delete from ".$this->guide->db_pfx."show_time where show_id=".$this->db->quote($v['xid']);
            $r = $this->db->query( $sql );
            $this->dbgMsg ( 'unitRefShows::refList_drop( '.$id.' ); '.$sql.' : ', $r , $pre=1, $htmlqt=1 );
            parent::refList_drop( $v['xid'] );
          }
    } else { $this->dbgMsg ( 'unitRefShows::refList_drop( '.$id.' ) WAS IGNORED BY FALSE CONDITION  ', '' );   }
    $_SERVER['HTTP_REFERER'] = 'http://'.$_SERVER['HTTP_HOST'].'/'.$this->guide->proj_root.'shows/';
    echo '_SERVER[HTTP_REFERER] : '.$_SERVER['HTTP_REFERER'];
  }




} // EOC { unitRefShows }

?>