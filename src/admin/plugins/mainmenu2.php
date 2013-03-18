<?php

$menu_refs = array (
    'refWidgetSale'   => 'Продажа запчастей',
    'refStats'        => 'Статистика',
    'refActions'      => 'Действия',
    'refRefs'         => 'Справка',
    'refSystem'       => 'Система',
    'refDatabase'     => 'Базы',
    'refUploads'      => 'Отгрузки',
    'refTecSale'      => 'Продажа техники',

    'refDealers'      => 'Дилеры',         // !!!!!!
    'refPlans'        => 'Планирование',   // !!!!!!

    'refRepairs'      => 'Ремонт и ТО',

) ;

$menu_items = array (
    'refWidgetSale' => array (
        array(
         'href'  => "widgets",
         'title' => "Каталог",
         'level' => ACCESS_LEVEL_CLIENT ), // + ACCESS_LEVEL_SERVER,
        array(
         'href'  => "widget_orders",
         'title' => "Заказы",
         'level' => ACCESS_LEVEL_CLIENT ), // ACCESS_LEVEL_SERVER,
        array(
         'href'  => "widget_import",
         'title' => "Импорт",
         'level' => 0 ), // ACCESS_LEVEL_SERVER,
        array(
         'href'  => "widget_export",
         'title' => "Экспорт",
         'level' => 0 ), // ACCESS_LEVEL_SERVER,
    ),
    'refStats'      => array (
        array(
         'href'  => "sale_list",
         'title' => "Продажи",
         'level' => 0 ), // ACCESS_LEVEL_CLIENT ), // + ACCESS_LEVEL_SERVER,
        array(
         'href'  => "repair_library",
         'title' => "Ремонт и ТО",
         'level' => 0 ), // ACCESS_LEVEL_CLIENT ), // + ACCESS_LEVEL_SERVER,
        array(
         'href'  => "reports",
         'title' => "Отчеты",
         'level' => 0 ), // ACCESS_LEVEL_SERVER,
    ),
    'refActions'   => array (
        array(
         'href'  => "sale_create",
         'title' => "Продажа",
         'level' => 0 ), // ACCESS_LEVEL_CLIENT,    ),
        array(
         'href'  => "repair_create",
         'title' =>  "Приемка",
         'level' => 0 ), // ACCESS_LEVEL_CLIENT,    ),
        array(
         'href'  => "repair_delivery",
         'title' => "Выдача",
         'level' => 0 ), // ACCESS_LEVEL_CLIENT,    ),
    ),
/*
    'refRefs'   => array (
        array(
         'href'  => "refmodels_list",
         'title' => "Модели",
         'level' => ACCESS_LEVEL_SERVER,    ),
        array(
         'href'  => "refcolors_list",
         'title' => "Цвета",
         'level' => ACCESS_LEVEL_SERVER,    ),
        array(
         'href'  => "refdealers_list",
         'title' => "Дилеры",
         'level' => ACCESS_LEVEL_SERVER,     ),
        ),
*/

    'refDatabase' => array (
        array(
          'href'  => 'dbs_distributor',
          'title' => 'Дистрибьютор',
          'level' => ACCESS_LEVEL_VISOR,     ),
        array(
          'href'  => 'dbs_dealers',
          'title' => 'Дилеры',
          'level' => ACCESS_LEVEL_VISOR     ),
    ),

    'refUploads' => array (
        array (
          'href'  => 'uploads',
          'title' => 'Поступления',
          'level' => ACCESS_LEVEL_VISOR,    ),
        array (
          'href'  => 'uploads_archive',
          'title' => 'Архив',
          'level' => ACCESS_LEVEL_VISOR,    ),
        /*
        array (
          'href'  => 'uploads_import',
          'title' => 'Импорт',
          'level' => ACCESS_LEVEL_VISOR,    ), */

    ),

    'refTecSale' => array (
        array (
         'href'  => "dealinvoice",
         'title' => "Выписки",
         'level' => ACCESS_LEVEL_CLIENT ), // + ACCESS_LEVEL_SERVER,
        array (
         'href'  => "dealstore",
         'title' => "Склад",
         'level' => ACCESS_LEVEL_CLIENT,    ),
        array (
         'href'  => "tecstore",
         'title' => "Склад",
         'level' => ACCESS_LEVEL_SERVER,    ),
        array (
         'href'  => "invoice",
         'title' => "Выписка счета и накладной",
         'level' => ACCESS_LEVEL_SERVER,    ),
    ),

    'refRepairs' => array (
        array (
         'href'  => "support",
         'title' => "ТО",
         'level' => ACCESS_LEVEL_SERVER ), // + ACCESS_LEVEL_CLIENT,
    ),


    'refDealers'   => array (
        array(
         'href'  => "refdealers_list",
         'title' => "Данные по дилерам",
         'level' => ACCESS_LEVEL_SERVER,     ),
    ),

    'refPlans'     => array (
        array(
         'href'  => "uploads_distr",
         'title' => "Отгрузка с фабрики",
         'level' => ACCESS_LEVEL_SERVER,     ),
    ),

    'refSystem' => array (
        array (
         'href'  => "sync",
         'title' => "Синхронизация",
         'level' => 0 ), // ACCESS_LEVEL_CLIENT,    ,
        array(
         'href'  => "login",
         'title' => ":Завершить Сеанс:",
         'qs'    => '?logout=yes',
         'level' => ACCESS_LEVEL_CLIENT + ACCESS_LEVEL_SERVER + ACCESS_LEVEL_VISOR,      ),
    ),



);

    /* обход по рубрикам  */
    $customer_level = $guide->user['role'];
    $tpf = 'html.common.html:MenuRef';

    foreach ( $menu_items as $alias => $ref ) {
      $items = array();
      foreach ( $ref as $k => $item ) {
        $access = $item['level'] & $customer_level;
        if ( !$access ) continue;
        if ( $guide->uri_parts[0] == $item['href'] )   $item['_Curr'] = 1;
        $items[] = $item;
      }
      if ( !empty($items) ) {
          $tpl->Set('ref:title', $menu_refs[ $alias ] );
          $result .= $tpl->Loop( $items, $tpf );
      }
    }

    echo $result;

// echo ' ';



?>