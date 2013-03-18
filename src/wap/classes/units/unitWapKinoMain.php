<?php

  require_once (dirname(__FILE__)."/unitAbstract.php" );
    require_once (dirname(__FILE__)."/unitUnit.php" );

class unitWapKinoMain extends unitUnit
{
  var $tpf = 'contents.html';
  var $months_by = array (
        1=>'января', 2=>'февраля', 3=>'марта', 4 => 'апреля', 5=>'мая', 6=>'июня',
        7=>'июля',   8=>'августа',  9=>'сентября', 10=>'октября', 11=>'ноября', 12=>'декабря',   );

  function Handle() {
    $this->Init();
    $method = $this->context.'_construct';    $this->dbgMsg ( ' method : ', $method , $pre=0, $htmlqt=0 );
    if ( method_exists($this, $method)) $this->$method();
    // $this->tpl->Parse('contents.html:proto', 'HTML:Body');
  }

  function Init() {
    $this->dbgMsg ( " WAP.Main \r\n this->guide->uri_parts : " , $this->guide->uri_parts, $pre=1, $htmlqt=0 );
    $this->sql10 = "select *, h.name as hall_name, f.description as film_desc, s.id as show_id from ".$this->guide->db_pfx."shows as s".
             " join ".$this->guide->db_pfx."films as f on s.film_id = f.id".
             " join ".$this->guide->db_pfx."place as p on s.place_id=p.place_id".
             " join ".$this->guide->db_pfx."halls as h on s.hall_id=h.id";

    $this->tpl->Load(array('CARD:ID'=>'currency','CARD:Title'=>'Афиша кино'));
    switch ( $this->guide->uri_parts[0] )  {
      case 'place':
        $this->context = 'Place';
        break;
      case 'film':
        $this->context = 'Film';
        break;
        // if ( is_numeric($this->guide->uri_parts[1])) break; // иначе - провалиться в стандартный список фильмов
        // if ( $dtm4 = preg_match('/^\d{4}-\d{2}-\d{2}$/',$this->guide->uri_parts[1]) )   $this->dbgMsg('dtm4 : ', $dtm4);
      default: $this->context = 'Home';
    }
  }

  function Home_construct() {
    $this->tpl->Parse( $this->tpf.':'.$this->context, 'HTML:Body' );
  }

  function Film_construct() {
    if ( is_numeric($id=$this->guide->uri_parts[1]) ) {
        $clauses = array();
        $clauses[] = "s.`date_start`<=NOW() and s.`date_stop`>=NOW()";
        $clauses[] = "s.`status`<>'closed'";
        $clauses[] = "f.id = ".$this->db->quote( $id );
        $sql = $this->sql10." where ".implode(' and ', $clauses);
        $a = $this->db->sql2array( $sql );  $this->dbgMsg(' &&& '.$sql, $a );
        $lp_cfg = array ('lp_tpl'=> $this->tpf.':FilmPlaces', 'lp_saveto' =>  'Places', 'clause' => $clauses[2], 'days_num'=>1 );
        if ( !empty($a) )
          $this->_ShowsSchedule( $lp_cfg );
        $this->tbl->Init( $this->guide->db_pfx.'films');
        $d = $this->tbl->Load( $this->db->quote($id) );  $this->dbgMsg(' :: film data ' , $d );
        $this->tpl->ParseOne( $d, $this->tpf.':FilmSchedule',   'HTML:Body' );
    } else {
        $sql = $this->sql10." where s.`date_start`<=NOW() and s.`date_stop`>=NOW()";
        $a = $this->db->sql2array( $sql ); $this->dbgMsg ( $sql.' : ', $a , $pre=1, $htmlqt=0 );
        $_ids = array(); $r = array();
        foreach ($a as $k=>$v) {
          if ( !in_array($v['film_id'], $_ids) ) {
            $_ids[] = $v['film_id'];
            $r[$k] = $a[$k];
          }
        }
        $this->tpl->Loop( $r, $this->tpf.':'.$this->context.'List', 'HTML:Body' );
    }
  }

  function Place_construct() {
    if ( is_numeric($id=$this->guide->uri_parts[1]) ) {
        // $this->PlaceSchedule($id=$this->guide->uri_parts[1]);

        $clauses = array();
        $clauses[] = "s.`date_start`<=NOW() and s.`date_stop`>=NOW()";
        $clauses[] = "s.`status`<>'closed'";
        $clauses[] = "p.`place_id` = ".$this->db->quote( $id );
        $sql = $this->sql10." where ".implode(' and ', $clauses);
        $a = $this->db->sql2array( $sql );  $this->dbgMsg(' &&& '.$sql, $a );
        $lp_cfg = array ('lp_tpl'=> $this->tpf.':PlaceFilms', 'lp_saveto' =>  'Places', 'clause' => $clauses[2], 'days_num'=>1 );
        if ( !empty($a) )
          $this->_ShowsSchedule( $lp_cfg );


        $this->tbl->Init( $this->guide->db_pfx.'place', $db_id="place_id" );
        $d = $this->tbl->Load( $this->db->quote($id) );  $this->dbgMsg(' :: place data ' , $d );
        if (!empty($d['place_tel']))
            $d['PlaceTel'] = $this->tpl->ParseOne($d, $this->tpf.':PlaceTel', '');
        if (!empty($d['place_address']))
            $d['PlaceAddress'] = $this->tpl->ParseOne($d, $this->tpf.':PlaceAddress', '');

        $this->tpl->ParseOne( $d, $this->tpf.':PlaceSchedule',   'HTML:Body' );

    } else {
        $sql = "select distinct p.place_id, p.place_name from ".$this->guide->db_pfx."shows as s join ".
               $this->guide->db_pfx."place as p on s.place_id=p.place_id ".
               "where s.`date_start`<=NOW() and s.`date_stop`>=NOW() and s.`status`<>'closed'";
        $a = $this->db->sql2array( $sql );

        $this->dbgMsg ( $sql.' : ', $a , $pre=1, $htmlqt=0 );
        $this->tpl->Loop( $a, $this->tpf.':'.$this->context.'List', 'HTML:Body' );
    }
  }

  function _ShowsSchedule( $cfg ) {   // $a = $data;
    $this->dbgMsg ( ' _ShowsSchedule(: ', $cfg, $pre=1, $htmlqt=1 );
    $cfg['days_num']=$cfg['days_num']?$cfg['days_num']:6;  //  $this->dbgMsg ( ' .cfg: ', $cfg , $pre=1, $htmlqt=0 );

    $this->dbgMsg ( '_REQUEST  : ', $_REQUEST , $pre=1, $htmlqt=1 );
    if (isset($_REQUEST['sdsz']))  $cfg['days_num'] = $_REQUEST['sdsz'];

    $dt = date("Y-m-d");   // $date_stop_last = $a[count($a)-1]['date_stop']; $this->dbgMsg(" %% date_stop_last ", $date_stop_last );
    $date_stop_last = date("Y-m-d", mktime(0,0,0, date("m", strtotime($dt)), $cfg['days_num']+date("d",strtotime($dt)), date("Y",strtotime($dt)) ) );
    $this->dbgMsg ( ' _ShowsSchedule(: ['.$date_stop_last.']', $cfg, $pre=1, $htmlqt=1 );

    $today = date("Y-m-d");
    $tomorrow = date( "Y-m-d", mktime( 0, 0, 0, date("m"), 1+date("d"), date("Y")));
    while ( $dt <= $date_stop_last ) {              $this->dbgMsg ( ' cycleDate : ', $dt , $pre=0, $htmlqt=0 );
        $dttime = strtotime($dt);
        if ( $dt==$today ) $dt_rel = 'Сегодня, ';
        elseif ( $dt==$tomorrow ) $dt_rel = 'Завтра, ';
        else $dt_rel = '';
        $dtf = $dt_rel. date("j", $dttime).' '.$this->months_by[date("n", $dttime)];
        $this->tpl->Set('ShowDate', $dtf);  // $this->tpl->Set('ShowDate', $dt);
        $clauses = array();
        $clauses[] = UtilityCore::sqlClausePeriod(0,'DAY', "'".$dt."'");
        $clauses[] = "s.`status`<>'closed'";
        $clauses[] = $cfg['clause'];

        $sql = $this->sql10." where ".implode(' and ', $clauses).
               " group by s.film_id, s.place_id, s.hall_id";  // внимание! группировка по "ред-сеансу" .
               " order by f.weight desc"; // выборка фильм+площадка+сеанс
        $b = $this->db->sql2array( $sql );  $this->dbgMsg(" %%% ShowsSchedule() ".$sql, $b );

        if ( is_array($b) && !empty($b) )     {
            foreach ( $b as $k=>$v ) {
              $this->dbgMsg (':а вот тут мы пишем запрос на время всех реальных сеансов, относящихся к "ред-сеансу":','',$pre=0,$htmlqt=0);
              $sql = "select * from ".$this->guide->db_pfx."shows as s join ".$this->guide->db_pfx."show_time as t on s.id=t.show_id".
              " where s.film_id=".$v['film_id']." and s.place_id=".$v['place_id']." and s.hall_id=".$v['hall_id'].
              " and ".UtilityCore::sqlClausePeriod(0,'DAY', "'".$dt."'")." and s.`status`<>'closed'".
              " order by start_time asc";
              $p = $this->db->sql2array($sql); $this->dbgMsg(' ***** '.$sql." : ", $p, $pre=1, $htmlqt=1 );
              $show_times = array();
              foreach ($p as $pk=>$pv) {
                $p[$pk]['start_time'] = substr( $pv['start_time'],0,5 );
                if ( in_array($p[$pk]['start_time'], $show_times) )    unset( $p[$pk] );
                else                                 $show_times[] = $p[$pk]['start_time']; // подтираем секунды
              }
              $b[$k]['ShowTime'] = $lp = $this->tpl->Loop( $p, $this->tpf.':ShowTime', '',  $add=0, $implode=1 );  $this->dbgMsg('showtime@@@:',$lp);
            }
            $r = $this->tpl->Loop( $b, $cfg['lp_tpl'], $cfg['lp_saveto'],  $add=1 ); // :PlaceFilms vs :FilmPlaces
        }
        $dt =  date( "Y-m-d", mktime( 0, 0, 0, date("m", strtotime($dt)), 1+date("d", strtotime($dt)), date("Y", strtotime($dt)) ) );
    }

  }



} // EOC { unitMain }

?>