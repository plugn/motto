<?php

  require_once (dirname(__FILE__)."/unitAbstract.php" );
    require_once (dirname(__FILE__)."/unitUnit.php" );

class unitMain extends unitUnit
{
  var $_intervals = array (   // sql date intervals mapping
        'd0'  =>  '0 DAY',
        'd1'  =>  '1 DAY',
        'd7'  =>  '7 DAY',
        'm1'  =>  '1 MONTH',
        'm3'  =>  '3 MONTH',
        'm6'  =>  '6 MONTH',
        'y1' =>   '1 YEAR',  );
  var $months = array (
        1=>'������', 2=>'�������', 3=>'����', 4 => '������', 5=>'���', 6=>'����',
        7=>'����',   8=>'������',  9=>'��������', 10=>'�������', 11=>'������', 12=>'�������',   );
  var $months_by = array (
        1=>'������', 2=>'�������', 3=>'�����', 4 => '������', 5=>'���', 6=>'����',
        7=>'����',   8=>'�������',  9=>'��������', 10=>'�������', 11=>'������', 12=>'�������',   );
  var $wkdays      = array ( 0=>'�����������', 1=>'�����������', 2=>'�������', 3=>'�����', 4=>'�������', 5=>'�������', 6=>'�������', );
  var $wkdays_tiny = array ( 0=>'��', 1=>'��', 2=>'��', 3=>'��', 4=>'��', 5=>'��', 6=>'��', );
  var $tpf = 'proto.main.html';

  function Handle() {
    $this->dbgMsg( 'uri:'.$this->guide->uri.'; uri_parts', $this->guide->uri_parts );
    $this->Init();
    $method = $this->context.'_construct';
    $method_save = $this->context.'_save';
    $method_drop = $this->context.'_drop';  $this->dbgMsg('_REQUEST : ', $_REQUEST );
    if( $_REQUEST[$this->guide->form_exists_var] && method_exists($this,$method_save) ) {
      $this->$method_save();
      header ('Location: http://'.$_SERVER['HTTP_HOST']."/".$this->guide->proj_root.$this->guide->uri_parts[0].'/'.$this->guide->uri_parts[1]);die();
    }
    elseif (  method_exists( $this, $method ) )
      $this->$method();
    $this->dbgMsg('method_exists( $this, '.$method .' ) ',  method_exists($this, $method ));
    $this->tpl->Parse( $this->tpf.':HTMLLeft',  'HTML:Left'  );
    $this->tpl->Parse( $this->tpf.':HTMLRight', 'HTML:Right' );
    return;
  }

  function Init() {
    $this->tpl->Set( 'HTML:Title', '����� ���� - ' );
    $this->tpl->Set( 'HeadTitle', '������' );
    if ( $dtm4=preg_match( $ptrn = '/^(\d{4})-(\d{2})-(\d{2})$/', $key_date = $this->guide->uri_parts[1], $m4s) ) { // $this->dbgMsg('dtm4 : ', $dtm4);
            $this->key_date = $key_date;
            $this->tpl->Set( 'ViewDate',
             strtolower($this->months[ intval($m4s[2]) ].', '.intval($m4s[3]).', '.$this->wkdays[ date("w",strtotime($m4s[0]) )] ) );
    } else  $this->tpl->Set( 'ViewDate',  strtolower($this->months[date("n")]).', '. date("j").', '. $this->wkdays[ date("w") ]);

    $this->WeekNav(); // ��������� �� ������

    if ( $dtm4=preg_match( $ptrn = '/^\d{4}-\d{2}-\d{2}$/', $key_date = $this->guide->uri_parts[1]) ) {
             $clause_period = "'".$key_date."' >= s.`date_start` and '".$key_date."' <= s.`date_stop`";
    } else { $clause_period = UtilityCore::sqlClausePeriod(); }
    $clauses = array();
    $clauses[] = $this->clause_period = $clause_period;
    $clauses[] = $this->clause_status = "s.`status`<>'closed'";
    $this->fl_clauses = $clauses;

    $this->sql10 = "select *, h.name as hall_name, f.description as film_desc, s.id as show_id from ".$this->guide->db_pfx."shows as s".
             " join ".$this->guide->db_pfx."films as f on s.film_id = f.id".
             " join ".$this->guide->db_pfx."place as p on s.place_id=p.place_id".
             " join ".$this->guide->db_pfx."halls as h on s.hall_id=h.id";
    $this->Calendar();
    $this->frameFilms_construct();
    $this->framePlaces_construct();
    switch ( $this->guide->uri_parts[0] )  {
      case 'show':
        $this->context = 'Show';
        break;
      case 'place':
        $this->context = 'Place';
        break;
      case 'film':
        $this->context = 'Film';
        if ( is_numeric($this->guide->uri_parts[1])) break; // ����� - ����������� � ����������� ������ �������
        // if ( $dtm4 = preg_match('/^\d{4}-\d{2}-\d{2}$/',$this->guide->uri_parts[1]) )   $this->dbgMsg('dtm4 : ', $dtm4);
      default: $this->context = 'FilmList';
    }
  }

  function CalendarData($month=null, $year=null) { // RESULT SHOULD BE A CACHED HTML-BLOCK
    if (is_null($year))  $year  = date('Y');
    if (is_null($month)) $month = date('n');

    // RENDER
    exec("cal ".$month." ". $year, $cal);  $this->dbgMsg( ' ### calendar '.$month." ". $year." :: ", $cal );
    $rxp[] = array_fill(0,7,' ');
    for ($k=2; $k < count($cal); $k++ ) {
      $xpl = explode(' ', trim( preg_replace('{\s+}i', ' ', $cal[$k]) ) );
      if (count($xpl) && $xpl[0]!=='' ) { // $this->dbgMsg(' xploded row', $xpl, 1,1 );
        if ( $wsp=7-count($xpl) )  {
          if ($k==2) $rx = array_merge( array_fill(0, $wsp, ' ' ), $xpl );
          else       $rx = array_merge( $xpl, array_fill(count($xpl)+1, $wsp, ' ' )  );
        }
        else $rx = $xpl;
        $rxp[] = $rx;
      }
    }

    for ($j=1; $j < count($rxp); $j++ ) {
       for ($d=0; $d<7; $d++) {
          if ($d===0) $rxp[$j-1][6] = $rxp[$j][0];
          else $rxp[$j][$d-1] = $rxp[$j][$d];
       }
    }
    $rxp[$j-1][$d-1]=' ';
    foreach ($rxp as $j=>$r) { // $this->dbgMsg( "str_replace(' ','',implode('',".$rxp[$j].") )",  str_replace(' ','',implode('',$rxp[$j]) )  );
       if ( !str_replace(' ','',implode('',$rxp[$j]) ) ) unset($rxp[$j]); // NEED FOR TESTS
    }  // $this->dbgMsg(' exploded calendar : ', $rxp, 1,1 );

    foreach ( $rxp as $i=>$arr ) {
      foreach ( $arr as $k=>$day) {
        $clr = array();
        if ( is_numeric($day) )  { // $this->dbgMsg( 'CalDay is_numeric( '.$day.' ) ',  'YES' );
            $Mm = $month<10?('0'.$month):$month;
            $Dd = $day<10?('0'.$day):$day;
            $key_date = $year.'-'.$Mm.'-'.$Dd; // $href_date = $year.'/'.$Mm.'/'.$Dd;
            $clauses = array();
            $clauses[] = "'".$key_date."' >= s.`date_start` and '".$key_date."' <= s.`date_stop`";
            $clauses[] = "s.`status`<>'closed'"; //      $this->dbgMsg('', $clauses );
            $sql = $this->sql10." where ".implode(' and ', $clauses);
            $a = $this->db->sql2array( $sql ); //    $this->dbgMsg(" cal Day :: ".$sql, $a ); // ������� �����+��������+�����

            if ( $key_date == $this->guide->uri_parts[1] ) {
                $clr['tpl'] = '_Curr';
                $clr['Misc'] = ' style="color:#333;"';
            // } elseif ( !empty($a) ) {   // ��� -  �� �������� � �������
            } elseif ( !empty($a) && date("Y-m-d") <= $key_date ) {
                $clr['tpl'] = '_Href';
                $clr['DayHref']= 'film/'.$key_date.'/';
                if ( $k>4 ) $clr['Misc'] = ' style="color:#990000;"';
            } else {
                $clr['tpl'] = '_Item';
            }
        }
        $clr['DayNum']=$day;
        $rxp[$i][$k] = $this->tpl->ParseOne( $clr, $this->tpf.':CalDay'.$clr['tpl'], '' );
      }  // $rxp[$i]['trMisc'] =  ' style="'.(($i%2==1)?'background:#eee;"':'"');
      $rxp[$i]['tdMisc'] =  ' style="text-align:right;background-color:#ffffff;color:#ccc;"';
    }  // $this->dbgMsg('EXPANDED calendar : ', $rxp, 1,1 );
    return $rxp;
  }

  function Calendar ($month=null, $year=null)  // RESULT SHOULD BE A CACHED HTML-BLOCK
  {
    $routine_start = microtime(true);

    if (is_null($year))  $year  = date('Y');
    if (is_null($month)) $month = date('n');
    $rxp = $this->CalendarData($month, $year);
    // VIEW
    $this->tpl->Set('capYear', $year );
    $this->tpl->Set('capMonth', $this->months[$month] );
    $this->tpl->Set('capMisc', ' style="background:#ccc;"' );
    $this->tpl->Loop( $rxp, $this->tpf.':Cal', 'Cal', $add=0, $implode=0, $wrap_empty=1  );

    $dt1=date( "Y-n-j");  // whether show next month
    $dt2=date( "Y-n-j", mktime( 0, 0, 0, 1+$month, -8, $year ));
    $this->dbgMsg( "+++ ", $dt1.' > '.$dt2.'  ? ' ); // �� ������ �� ���������� ��� ������: 1-� ����� ����� 1 ����, ����� 7 ���� = ����� 8 ����
    if ( $dt1 > $dt2 )
    {   // $this->dbgMsg( ' m4s[3]>15: ', '#' );
        $month2 = date( "n", mktime( 0, 0, 0, 1+$month, 1, $year ) );
        $year2 =  date( "Y", mktime( 0, 0, 0, 1+$month, 1, $year ) );  echo '$month2, $year2 :: '." $month2, $year2 ".BR;
        $rxp2 = $this->CalendarData( $month2, $year2 );
        // VIEW
        $this->tpl->Set('capYear', $year2 );
        $this->tpl->Set('capMonth', $this->months[$month2] );
        $this->tpl->Set('capMisc', ' style="background:#ccc;align:right;"' );
        $this->tpl->Loop( $rxp2, $this->tpf.':Cal', 'Cal', $add=1, $implode=0, $wrap_empty=1  );
    }
    $this->tpl->Set('barTitle', '��������� �����');
    $this->tpl->Parse($this->tpf.':CalWrap', 'frameCal');
    $routine_end = microtime(true);    $this->dbgMsg ( 'calendar rendering time (sec) : ', ($routine_end - $routine_start) , $pre=0, $htmlqt=0 );
  }

  function WeekNav() {
      $p = array(); $j = 0;
      $dt = date("Y-m-d");
      $date_stop_last  =  date( "Y-m-d", mktime( 0, 0, 0, date("m", strtotime($dt)), 6+date("d", strtotime($dt)), date("Y", strtotime($dt)) ) );
      while ( $dt <= $date_stop_last ) { //   $this->dbgMsg ( ' weekNav : ', $dt , $pre=0, $htmlqt=0 );
        $wdco = date( "w",strtotime($dt));
        $p[$j]['wdCo'] = $wdco;
        $p[$j]['wdTn'] = strtolower( $this->wkdays_tiny[ $wdco ] );  // $this->tpl->Set('ShowDate', $dt);
        $p[$j]['dtTn'] = date("d.m", strtotime($dt));  // "#eaeaea"

        if ( $dt==$this->key_date ) $p[$j]['_Curr'] = 1;
        $p[$j]['tdMisc'] = ($wdco=='0' || $wdco=='6')?(' style="background-color:#ffd9d9;'.$tdmisx.'"'):
                           (' style="background-color:#ffffe6;'.$tdmisx.'"');
        $p[$j]['href'] = 'film/'.$dt.'/';
        $dt =  date( "Y-m-d", mktime( 0, 0, 0, date("m", strtotime($dt)), 1+date("d", strtotime($dt)), date("Y", strtotime($dt)) ) );
        $j++;
      }
      $this->dbgMsg ( 'week nav(); data : ', $p , $pre=1, $qt=1 );
      $this->tpl->Loop( $p, $this->tpf.':WeekNav', 'WeekNav', $add=0, $implode=0, $wrap_empty=1 );
  }

  // frame : ��������� �� ��������
  function framePlaces_construct() { // $this->tbl->Init( $this->guide->db_pfx.'place' );  $a = $this->tbl->Load( null, '1=1' );
    $this->tpl->Set( 'Caption', '����������' );
    $this->tpl->Set( 'capMisc', ' style="background:#ccc;"' );

    $sql0 = "select p.place_id, place_name as name, s.id as show_id from ".$this->guide->db_pfx."shows as s".
           " join ".$this->guide->db_pfx."place as p on s.place_id=p.place_id";

    if ( $dtm4=preg_match( $ptrn = '/^\d{4}-\d{2}-\d{2}$/', $key_date = $this->guide->uri_parts[1]) ) { // $this->dbgMsg('dtm4 : ', $dtm4);
             $clause_period = "'".$key_date."' >= s.`date_start` and '".$key_date."' <= s.`date_stop`";
    } else { $clause_period = UtilityCore::sqlClausePeriod(); }
    $clauses = array();
    $clauses[] = $clause_period;
    $clauses[] = "s.`status`<>'closed'";
    $sql = $sql0." where ".implode(' and ', $clauses)." order by s.`date_stop` asc"; // ������� �����+��������+�����
    // $sql  = 'select *, place_name as name from '.$this->guide->db_pfx.'place';
    $a = $this->db->sql2array( $sql );
    $this->dbgMsg(" %%% framePlaces_construct() ".$sql, $a );
    $_ids = array();
    foreach ($a  as $k=>$v) {
      if ( in_array($v['place_id'], $_ids) ) { unset( $a[$k] ); }
      else                                   { $_ids[]=$v['place_id']; $a[$k]['href'] = 'place/'.$v['place_id'].'/'; }
    }
    $this->tpl->Set('barTitle', '����������');
                $this->tpl->Parse($this->tpf.':barOrange', 'barPlaces');
    $places = $this->tpl->Loop( $a, $this->tpf.':ListPlain', 'framePlaces', $add=0, $implode=0, $wrap_empty=1 );
    // $this->dbgMsg('films dump : ', $a ); $this->dbgMsg('films : ', $places );
  }

  function frameFilms_construct()  { // $this->tbl->Init( $this->guide->db_pfx.'films' );    $a = $this->tbl->Load( null, '1=1' );
    $this->tpl->Set('Caption', '������' );
    $this->tpl->Set('capMisc', ' style="background:#ccc;"' );

    $sql1 = "select f.title_rus as name, f.id as film_id from ".$this->guide->db_pfx."shows as s".
            " join ".$this->guide->db_pfx."films as f on s.film_id=f.id";

    if ( $dtm4=preg_match( $ptrn = '/^\d{4}-\d{2}-\d{2}$/', $key_date = $this->guide->uri_parts[1]) ) { // $this->dbgMsg('dtm4 : ', $dtm4);
             $clause_period = "'".$key_date."' >= s.`date_start` and '".$key_date."' <= s.`date_stop`";
    } else { $clause_period = UtilityCore::sqlClausePeriod(); }
    $clauses = array();
    $clauses[] = $clause_period;
    $clauses[] = "s.`status`<>'closed'";
    $sql = $sql1." where ".implode(' and ', $clauses)." order by s.`date_stop` asc"; // ������� �����+��������+�����
    // $sql  = 'select *, place_name as name from '.$this->guide->db_pfx.'place';
    $a = $this->db->sql2array( $sql );
    $this->dbgMsg(" %%% frameFilms_construct() ".$sql, $a );
    $_ids = array();
    foreach ($a  as $k=>$v) {
      if ( in_array($v['film_id'], $_ids) ) { unset( $a[$k] ); }
      else                                  { $_ids[]=$v['film_id']; $a[$k]['href'] = 'film/'.$v['film_id'].'/'; }
    }
          $this->tpl->Set( 'barTitle', '������' );
                $this->tpl->Parse($this->tpf.':barOrange', 'barFilms');

    $films = $this->tpl->Loop( $a, $this->tpf.':ListPlain', 'frameFilms', $add=0, $implode=0, $wrap_empty=1 );
  }

  function _FilmPlaces( $a, $tpl_place ) {
    foreach ($a as $k=>$v) $show_ids[] = $v['show_id']; $this->dbgMsg( ' show_ids: ', $show_ids , $pre=1, $htmlqt=0 );
    $film_ids = array();
    foreach ($a as $k=>$v) {
      $film_id = $v['film_id'];
      if (!in_array($film_id, $film_ids)) // ���� id ������ ��� � ���������
      {
        $film_ids[]=$film_id; // ��������� id ������ � ���������
        $r[$film_id] = $a[$k];
        $this->tbl->Init( $this->guide->db_pfx.'film_pics' ); // �����������
        $rs = $this->tbl->Load(null, 'film_id='.$this->db->quote($film_id).' order by '.$this->tbl->db_id.' limit 0,1 ' );
        $r[$film_id]['pic'] = $rs[0]['pic'];
        // �������� ����� �������
        $sql = $this->sql10." where f.id=".$this->db->quote($film_id)." and".
                            " s.id in (".implode(', ', $show_ids).")". // ���������� ������� ���������� �� ��������� ������
                            " group by s.film_id, s.place_id, s.hall_id";  // ��������! ����������� �� "���-������"
        $sh =  $this->db->sql2array( $sql );
        $this->dbgMsg ( '�������� ����� �������. sql: '.$sql." : ", $sh , $pre=1, $htmlqt=0 );

        foreach ( $sh as $sk => $sv ) { // ������ ������ �� ����� ������; ������ �� ����� ���� �������� �������, ����������� � "���-������"
          $sql = "select * from ".$this->guide->db_pfx."shows as s join ".$this->guide->db_pfx."show_time as t on s.id=t.show_id".
                 " where s.film_id=".$sv['film_id']." and s.place_id=".$sv['place_id']." and s.hall_id=".$sv['hall_id'].
                 ( empty($this->fl_clauses)?'':' and '.implode(' and ', $this->fl_clauses) ).
                 " order by start_time asc";
          $p = $this->db->sql2array($sql); $this->dbgMsg(' *** ����� ����������� � ���-������ '.$sql." : ", $p, $pre=1, $htmlqt=1 );
          $show_times = array();
          foreach ($p as $pk=>$pv) {
            $p[$pk]['start_time'] = substr( $pv['start_time'],0,5 );
            if ( in_array($p[$pk]['start_time'], $show_times) )    unset( $p[$pk] );
            else                                 $show_times[] = $p[$pk]['start_time']; // ��������� �������
          }

          $sh[$sk]['ShowTime'] = $this->tpl->Loop( $p, $this->tpf.':ShowTime', '',  $add=0, $implode=1);
          $sh[$sk]['place_name'] = $sv['place_name'];
          $sh[$sk]['hall_name'] =  trim($sv['hall_name']);
          $sh[$sk]['place_id'] = $sv['place_id'];
        }
        $r[$film_id]['Places'] = $this->tpl->Loop( $sh, $tpl_place, '',  $add=0, $implode=0, $wrap_empty=1 );
      }
    }
    return $r;
  }

  function FilmList_construct() { $this->dbgMsg('FilmList_construct : ', '' );
    $this->tpl->Parse( $this->tpf.':BarDate', 'BarDate');
    /* ������� ������ �� ������� :   ������� �������, ������� �������� � ������� �� ��������� ������
    ���� ����� ������� ������ "�������", ������: +/- ���������� ����
    (� �������� �������� ������ "����", "�����" � ������� �� �������)
    ��������� �������� - ����� ������� ������, ��������� ����������� � ������� */
    $sql = $this->sql10." where ".implode(' and ', $this->fl_clauses).' order by weight desc';
    $a = $this->db->sql2array( $sql );  $this->dbgMsg(' &&& '.$sql, $a );
    $r = $this->_FilmPlaces( $a, $tpl_place=$this->tpf.':Places' );  $this->dbgMsg(' @@@ : ', $r, 1,1 );
    foreach ($r  as $k => $v)
      if ( $v['premier']==1 ) $r[$k]['PremierLabel'] = $this->tpl->Parse( $this->tpf.':PremierLbl' );
    $this->tpl->Loop( $r,$this->tpf.':MainList', 'CONTENT',  $add=0 );
    $this->tpl->Parse( $this->tpf.':Main', 'HTML:Body' );
  }

  function Film_construct() {  $this->dbgMsg( ' Film_construct(), uri_parts : ', $this->guide->uri_parts );
    $this->tpl->Set( 'HeadTitle', '�����' );
    $film_id = $this->guide->uri_parts[1];
    /* �� ���� �� �����, ������ ������� ��������� � ����� ���� ���-������ �� ���� � <��������� ������>
       ��� ���������� ��������� ������ - ������ ���� ��������. ��� ������ ���� ���������� ������    */
    $clauses = array();
    $clauses[] = UtilityCore::sqlClausePeriod();
    $clauses[] = "s.`status`<>'closed'";
    $clauses[] = "f.id=".$this->db->quote( $film_id );
    $sql = $this->sql10." where ".implode(' and ', $clauses)." order by s.`date_stop` asc"; // ������� �����+��������+�����
    $a = $this->db->sql2array( $sql ); // $this->dbgMsg(" %%% Film_construct() ".$sql, $a );
    $lp_cfg = array ('lp_tpl' => $this->tpf.':FilmPlaces', 'lp_saveto'=>'Places', 'clause' => $clauses[2] );
    if ( !empty($a) )
      $this->_ShowsSchedule( $lp_cfg );

    $this->tbl->Init($this->guide->db_pfx.'films' );
    $d = $this->tbl->Load( mysql_real_escape_string( $film_id ) );
    // pics
    $this->tbl->Init( $this->guide->db_pfx.'film_pics' ); // �����������
    $pic_data = $this->tbl->Load(null, 'film_id='.$film_id.' order by '.$this->tbl->db_id.' limit 0,10 ' ); $this->dbgMsg('::pic_data ', $pic_data);
    foreach ( $pic_data as $k=>$v ) {
        $pic_data[$k]['HttpPic']      = $v['pic']; // $this->tpl->Set('HttpPic', '/'.$v['pic']);
        $pic_data[$k]['HttpPicThumb']      = $v['path'].'thumb.'.$v['file']; // $this->tpl->Set('HttpPic', '/'.$v['pic']);
        if (!file_exists($_SERVER['DOCUMENT_ROOT'].$pic_data[$k]['HttpPicThumb']))    $pic_data[$k]['HttpPicThumb'] = $pic_data[$k]['HttpPic'];
        list($o_width, $o_height, $o_type, $o_attr) = $isz = getimagesize( $_SERVER['DOCUMENT_ROOT'].$pic_data[$k]['HttpPic'] );
        list($t_width, $t_height, $t_type, $t_attr) = $tsz = getimagesize( $_SERVER['DOCUMENT_ROOT'].$pic_data[$k]['HttpPicThumb'] );
        $this->dbgMsg ( ' pic: ', $_SERVER['DOCUMENT_ROOT'].$pic_data[$k]['HttpPic'] ); $this->dbgMsg ( 'imgsz : ', $imgsz , $pre=1, $htmlqt=0 );
        $pic_data[$k]['pic_width']  = $o_width+40; $pic_data[$k]['pic_height'] = $o_height+80;
        $pic_data[$k]['tmb_width']  = $t_width; $pic_data[$k]['tmb_height'] = $t_height;
        $pic_data[$k]['HttpPicThumb'] = '/'.$pic_data[$k]['HttpPicThumb']; $pic_data[$k]['HttpPic'] = '/'.$pic_data[$k]['HttpPic'];
    }

    $this->dbgMsg ( ' pic_data : ', $pic_data , $pre=1, $htmlqt=0 );
    $this->tpl->Set('colNum', count($pic_data));
    $d['Pics'] = $this->tpl->Loop( $pic_data, $this->tpf.':FilmPic', '', $add=0, $implode=0, $wrap_empty=0 );
    if ( $d['premier']==1 )
      $d['PremierLabel'] = $this->tpl->Parse( $this->tpf.':PremierLabel' );
    $this->dbgMsg ( ' d:: ', $d , $pre=1, $htmlqt=0 );

    $this->tpl->ParseOne( $d, $this->tpf.':Film',  'CONTENT' );
    $this->tpl->Parse( $this->tpf.':Main', 'HTML:Body' ); // $this->dbgMsg ( ' Get(HTML:Title) : ', $this->tpl->Get('HTML:Title'));
    $this->tpl->Set( 'HTML:Title', '&quot;'.$d['title_rus'].'&quot; - '.$this->tpl->Get('HTML:Title') );
  }

  function Place_construct() {  $this->dbgMsg ( 'Place_construct() : ', '', $pre=0, $htmlqt=0 );
    $this->tpl->Set( 'HeadTitle', '���������' );
    $clauses = array();
    $clauses[] = UtilityCore::sqlClausePeriod();
    $clauses[] = "s.`status`<>'closed'";
    $clauses[] = "p.`place_id` = ".$this->db->quote( $place_id = $this->guide->uri_parts[1] );
    $sql = $this->sql10." where ".implode(' and ', $clauses)." order by s.`date_stop` asc";
    $a = $this->db->sql2array( $sql );  $this->dbgMsg(' &&& '.$sql, $a );
    $lp_cfg = array ('lp_tpl'=> $this->tpf.':PlaceFilms', 'lp_saveto' =>  'Places', 'clause' => $clauses[2] );
    if ( !empty($a) )
      $this->_ShowsSchedule( $lp_cfg );

    $this->tbl->Init( $this->guide->db_pfx.'place', $db_id="place_id" );
    $d = $this->tbl->Load( mysql_real_escape_string( $place_id ) );  $this->dbgMsg(' :: place data ' , $d );
    if ( $d['pic'] )  $this->tpl->ParseOne( $d, $this->tpf.':PlacePic',  'PlacePic' );

    $this->tpl->ParseOne( $d, $this->tpf.':Place',  'CONTENT' );
    $this->tpl->Parse( $this->tpf.':Main', 'HTML:Body' );
    $this->tpl->Set( 'HTML:Title', $d['place_name'].' - '.$this->tpl->Get('HTML:Title') );
  }

  function _ShowsSchedule( $cfg ) {
    $cfg['days_num']=$cfg['days_num']?$cfg['days_num']:6;
    if (isset($_REQUEST['sdsz']))  $cfg['days_num'] = $_REQUEST['sdsz']; $this->dbgMsg ( ' _ShowsSchedule(: ', $cfg, $pre=1, $htmlqt=1 );

    $dt = date("Y-m-d");   // $date_stop_last = $a[count($a)-1]['date_stop']; $this->dbgMsg(" %% date_stop_last ", $date_stop_last );
    $date_stop_last=date("Y-m-d", mktime(0,0,0, date("m", strtotime($dt)), $cfg['days_num']+date("d",strtotime($dt)), date("Y",strtotime($dt)) ) );
    $today = date("Y-m-d");
    $tomorrow = date( "Y-m-d", mktime( 0, 0, 0, date("m"), 1+date("d"), date("Y")));
    while ( $dt <= $date_stop_last ) {    // $this->dbgMsg ( ' cycleDate : ', $dt , $pre=0, $htmlqt=0 );
        $dttime = strtotime($dt);
        if ( $dt==$today ) $dt_rel = '�������, ';
        elseif ( $dt==$tomorrow ) $dt_rel = '������, ';
        else $dt_rel = '';
        $dtf = $dt_rel. date("j", $dttime).' '.$this->months_by[date("n", $dttime)];
        $this->tpl->Set('ShowDate', $dtf);  // $this->tpl->Set('ShowDate', $dt);
        $clauses = array();
        $clauses[] = UtilityCore::sqlClausePeriod(0,'DAY', "'".$dt."'");
        $clauses[] = "s.`status`<>'closed'";
        $clauses[] = $cfg['clause'];
        $sql = $this->sql10." where ".implode(' and ', $clauses).
               " group by s.film_id, s.place_id, s.hall_id";  // ��������! ����������� �� "���-������" .
               " order by f.weight desc"; // ������� �����+��������+�����
        $b = $this->db->sql2array( $sql );  $this->dbgMsg(" %%% ShowsSchedule() ".$sql, $b );

        if ( is_array($b) && !empty($b) )     {
            foreach ( $b as $k=>$v ) { // $this->dbgMsg (':����� ����������� � "���-������":','',$pre=0,$htmlqt=0);
              $sql = "select * from ".$this->guide->db_pfx."shows as s join ".$this->guide->db_pfx."show_time as t on s.id=t.show_id".
              " where s.film_id=".$v['film_id']." and s.place_id=".$v['place_id']." and s.hall_id=".$v['hall_id'].
              " and ".UtilityCore::sqlClausePeriod(0,'DAY', "'".$dt."'")." and s.`status`<>'closed'".
              " order by start_time asc";
              $p = $this->db->sql2array($sql); $this->dbgMsg(' ***** '.$sql." : ", $p, $pre=1, $htmlqt=1 );
              $show_times = array();
              foreach ($p as $pk=>$pv) {
                $p[$pk]['start_time'] = substr( $pv['start_time'],0,5 );
                if ( in_array($p[$pk]['start_time'], $show_times) )    unset( $p[$pk] );
                else                                 $show_times[] = $p[$pk]['start_time']; // ��������� �������
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