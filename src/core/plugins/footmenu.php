<?php

/* $guide, $db
*/

$data[] = array('menu_href'=>'about',   'menu_title'=>'� ��������');
$data[] = array('menu_href'=>'news',    'menu_title'=>'�������');
$data[] = array('menu_href'=>'catalog', 'menu_title'=>'�������&nbsp;���������');
$data[] = array('menu_href'=>'partners', 'menu_title'=>'���������');
$data[] = array('menu_href'=>'qa', 'menu_title'=>'������� � ������');
$data[] = array('menu_href'=>'contacts', 'menu_title'=>'��������');

/*  foreach  ($data as $k=>$v) {
  if ($guide->uri == $v['menu_href']) $data[$k]['mark']='curr';
  else $data[$k]['mark']='item';
} */

// echo '### plugin executed. ### '; var_export($data);
  $tpf = "plugins/common.menu.html:footmenu";
	foreach ( $data as $k=>$v ) {
	  $tpl->Load( $v );

	  if ($guide->uri_parts[0] == $v['menu_href']) {
      $_mark = '_curr';
      if ($v['menu_href']!= $unit->nav_data[count($unit->nav_data)-1]['href'])
        $_mark = '_sub';                          }
    else   $_mark = '_item';

	  $items[] = $tpl->Parse( $tpf.$_mark );
	  $tpl->Free( array_keys($v) ); // ����� ���������� ������ �� ���������� ��� ������
	}
	  $r = implode($tpl->Parse( $tpf."_delim"), $items);
	  echo $r;

/* <a href=""></a> | <strong><a href="">�������&nbsp;���������</a></strong> | <a href="">���������</a> |
   <a href="">������� � ������</a> | <a href="">��������</a>  */


?>