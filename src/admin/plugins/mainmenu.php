<?php

  $menu_admin = array();
  $menu_user  = array(
    array(
     'href'  => "sale_list",
     'title' => "��� �������", ),
    array(
     'href'  => "repair_library",
     'title' => "��������� �������", ),
    array(
     'href'  => "widgets",
     'title' => "�������", ),
  );

  if ($guide->user['login']=='admin') {
    $menu_ext = array (
    array(
     'href'  => "widget_orders",
     'title' => "�������� �������", ),
    array(
     'href'  => "widget_import",
     'title' => "������", ),
    array(
     'href'  => "widget_export",
     'title' => "�������", ),
    array(
     'href'  => "refmodels_list",
     'title' => "���������� �������", ),
    array(
     'href'  => "refcolors_list",
     'title' => "���������� ������", ),
    array(
     'href'  => "refdealers_list",
     'title' => "���������� �������", ),
     );
  } else {
    $menu_ext = array (
    array(
     'href'  => "sale_create",
     'title' => "�������� ����� �������",  ),
    array(
     'href'  => "repair_create",
     'title' =>  "�������� �������", ),
    array(
     'href'  => "repair_delivery",
     'title' => "�������� ������", ),
    array (
     'href'  => "sync",
     'title' => "�������������", ),
     );
  }

  $menu_manage = array(
    array(
     'href'  => "login",
     'title' => ":��������� �����:",
     'qs'    => '?logout=yes',
    ),
  );

  $menu_data = array_merge($menu_user, $menu_ext, $menu_manage);



  $tpf = "plugins/menu.html:menu";
  foreach ($menu_data as $v )  {
      if ($guide->uri_parts[0] == $v['href'])   $_mark = '_Curr';
      else                                      $_mark = '_Item';
      $tpl->ParseOne($v, $tpf.$_mark, $store_to="menu_List", $append=1 );
  }
  echo $tpl->Parse( $tpf );


?>