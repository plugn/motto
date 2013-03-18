<?php

  $menu_data = array(
    array(
     'href'  => "sale_create",
     'title' => "Оформить новую продажу",  ),
    array(
     'href'  => "sale_list",
     'title' => "Все продажи", ),
    array(
     'href'  => "repair_create",
     'title' =>  "Оформить приемку", ),
    array(
     'href'  => "repair_delivery",
     'title' => "Оформить выдачу", ),
    array(
     'href'  => "repair_library",
     'title' => "Картотека ремонта", ),
    array(
     'href'  => "widget_order",
     'title' => "Заказ запчастей", ),
    array(
     'href'  => "sv_sync",
     'title' => "Синхронизация данных", ),
  );

  $tpf = "plugins/menu.html:menu";

var_export($guide);

?>