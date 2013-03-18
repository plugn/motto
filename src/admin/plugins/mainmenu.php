<?php

  $menu_admin = array();
  $menu_user  = array(
    array(
     'href'  => "sale_list",
     'title' => "Все продажи", ),
    array(
     'href'  => "repair_library",
     'title' => "Картотека ремонта", ),
    array(
     'href'  => "widgets",
     'title' => "Каталог", ),
  );

  if ($guide->user['login']=='admin') {
    $menu_ext = array (
    array(
     'href'  => "widget_orders",
     'title' => "Просмотр заказов", ),
    array(
     'href'  => "widget_import",
     'title' => "Импорт", ),
    array(
     'href'  => "widget_export",
     'title' => "Экспорт", ),
    array(
     'href'  => "refmodels_list",
     'title' => "Справочник моделей", ),
    array(
     'href'  => "refcolors_list",
     'title' => "Справочник цветов", ),
    array(
     'href'  => "refdealers_list",
     'title' => "Справочник дилеров", ),
     );
  } else {
    $menu_ext = array (
    array(
     'href'  => "sale_create",
     'title' => "Оформить новую продажу",  ),
    array(
     'href'  => "repair_create",
     'title' =>  "Оформить приемку", ),
    array(
     'href'  => "repair_delivery",
     'title' => "Оформить выдачу", ),
    array (
     'href'  => "sync",
     'title' => "Синхронизация", ),
     );
  }

  $menu_manage = array(
    array(
     'href'  => "login",
     'title' => ":Завершить Сеанс:",
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