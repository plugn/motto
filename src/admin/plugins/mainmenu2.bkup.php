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
) ;

$menu_items = array (
    'refWidgetSale' => array (
        array(
         'href'  => "widgets",
         'title' => "Каталог",
         'level' => ACCESS_LEVEL_CLIENT + ACCESS_LEVEL_SERVER,      ),
        array(
         'href'  => "widget_orders",
         'title' => "Заказы",
         'level' => ACCESS_LEVEL_SERVER,    ),
        array(
         'href'  => "widget_import",
         'title' => "Импорт",
         'level' => ACCESS_LEVEL_SERVER,    ),
        array(
         'href'  => "widget_export",
         'title' => "Экспорт",
         'level' => ACCESS_LEVEL_SERVER,    ),
    ),
    'refStats'      => array (
        array(
         'href'  => "sale_list",
         'title' => "Продажи",
         'level' => ACCESS_LEVEL_CLIENT + ACCESS_LEVEL_SERVER,      ),
        array(
         'href'  => "repair_library",
         'title' => "Ремонт и ТО",
         'level' => ACCESS_LEVEL_CLIENT + ACCESS_LEVEL_SERVER,      ),
        array(
         'href'  => "reports",
         'title' => "Отчеты",
         'level' => ACCESS_LEVEL_SERVER,    ),
    ),
    'refActions'   => array (
        array(
         'href'  => "sale_create",
         'title' => "Продажа",
         'level' => ACCESS_LEVEL_CLIENT,    ),
        array(
         'href'  => "repair_create",
         'title' =>  "Приемка",
         'level' => ACCESS_LEVEL_CLIENT,    ),
        array(
         'href'  => "repair_delivery",
         'title' => "Выдача",
         'level' => ACCESS_LEVEL_CLIENT,    ),
    ),

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
         'href'  => "tecstore_msg",
         'title' => "Выписки",
         'level' => ACCESS_LEVEL_CLIENT + ACCESS_LEVEL_SERVER,    ),
        array (
         'href'  => "tecstore",
         'title' => "Склад",
         'level' => ACCESS_LEVEL_CLIENT + ACCESS_LEVEL_SERVER,    ),
    ),

    'refSystem' => array (
        array (
         'href'  => "sync",
         'title' => "Синхронизация",
         'level' => ACCESS_LEVEL_CLIENT,    ),
        array(
         'href'  => "login",
         'title' => ":Завершить Сеанс:",
         'qs'    => '?logout=yes',
         'level' => ACCESS_LEVEL_CLIENT + ACCESS_LEVEL_SERVER + ACCESS_LEVEL_VISOR,      ),
    ),



);
    /* $guide->unit->dbgMsg('====', $guide->user);
    if     ( $guide->user['login']=='galion' )  $customer_level = ACCESS_LEVEL_VISOR;
    elseif ( $guide->user['login']=='admin'  )  $customer_level = ACCESS_LEVEL_SERVER;
    elseif ( !empty($guide->user['login']))     $customer_level = ACCESS_LEVEL_CLIENT;
    else                                        $customer_level = 0;   // */
    $customer_level = $guide->user['role'];  //     $customer_level = 1;

    /* обход по рубрикам  */
    $tpf = 'html.common.html:MenuRef';

    foreach ( $menu_items as $alias => $ref ) {
      $items = array();
      foreach ( $ref as $k => $item ) {    /* $access  = ($item['level'] & 2) && ($customer_level==2) ||
                ($item['level'] & 1) && ($customer_level==1) || ($item['level'] & 4) && ($customer_level==4)  ; */
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