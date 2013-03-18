<?php

$menu_refs = array (
    'refWidgetSale'   => '������� ���������',
    'refStats'        => '����������',
    'refActions'      => '��������',
    'refRefs'         => '�������',
    'refSystem'       => '�������',
    'refDatabase'     => '����',
    'refUploads'      => '��������',
    'refTecSale'      => '������� �������',

    'refDealers'      => '������',         // !!!!!!
    'refPlans'        => '������������',   // !!!!!!

    'refRepairs'      => '������ � ��',

) ;

$menu_items = array (
    'refWidgetSale' => array (
        array(
         'href'  => "widgets",
         'title' => "�������",
         'level' => ACCESS_LEVEL_CLIENT ), // + ACCESS_LEVEL_SERVER,
        array(
         'href'  => "widget_orders",
         'title' => "������",
         'level' => ACCESS_LEVEL_CLIENT ), // ACCESS_LEVEL_SERVER,
        array(
         'href'  => "widget_import",
         'title' => "������",
         'level' => 0 ), // ACCESS_LEVEL_SERVER,
        array(
         'href'  => "widget_export",
         'title' => "�������",
         'level' => 0 ), // ACCESS_LEVEL_SERVER,
    ),
    'refStats'      => array (
        array(
         'href'  => "sale_list",
         'title' => "�������",
         'level' => 0 ), // ACCESS_LEVEL_CLIENT ), // + ACCESS_LEVEL_SERVER,
        array(
         'href'  => "repair_library",
         'title' => "������ � ��",
         'level' => 0 ), // ACCESS_LEVEL_CLIENT ), // + ACCESS_LEVEL_SERVER,
        array(
         'href'  => "reports",
         'title' => "������",
         'level' => 0 ), // ACCESS_LEVEL_SERVER,
    ),
    'refActions'   => array (
        array(
         'href'  => "sale_create",
         'title' => "�������",
         'level' => 0 ), // ACCESS_LEVEL_CLIENT,    ),
        array(
         'href'  => "repair_create",
         'title' =>  "�������",
         'level' => 0 ), // ACCESS_LEVEL_CLIENT,    ),
        array(
         'href'  => "repair_delivery",
         'title' => "������",
         'level' => 0 ), // ACCESS_LEVEL_CLIENT,    ),
    ),
/*
    'refRefs'   => array (
        array(
         'href'  => "refmodels_list",
         'title' => "������",
         'level' => ACCESS_LEVEL_SERVER,    ),
        array(
         'href'  => "refcolors_list",
         'title' => "�����",
         'level' => ACCESS_LEVEL_SERVER,    ),
        array(
         'href'  => "refdealers_list",
         'title' => "������",
         'level' => ACCESS_LEVEL_SERVER,     ),
        ),
*/

    'refDatabase' => array (
        array(
          'href'  => 'dbs_distributor',
          'title' => '������������',
          'level' => ACCESS_LEVEL_VISOR,     ),
        array(
          'href'  => 'dbs_dealers',
          'title' => '������',
          'level' => ACCESS_LEVEL_VISOR     ),
    ),

    'refUploads' => array (
        array (
          'href'  => 'uploads',
          'title' => '�����������',
          'level' => ACCESS_LEVEL_VISOR,    ),
        array (
          'href'  => 'uploads_archive',
          'title' => '�����',
          'level' => ACCESS_LEVEL_VISOR,    ),
        /*
        array (
          'href'  => 'uploads_import',
          'title' => '������',
          'level' => ACCESS_LEVEL_VISOR,    ), */

    ),

    'refTecSale' => array (
        array (
         'href'  => "dealinvoice",
         'title' => "�������",
         'level' => ACCESS_LEVEL_CLIENT ), // + ACCESS_LEVEL_SERVER,
        array (
         'href'  => "dealstore",
         'title' => "�����",
         'level' => ACCESS_LEVEL_CLIENT,    ),
        array (
         'href'  => "tecstore",
         'title' => "�����",
         'level' => ACCESS_LEVEL_SERVER,    ),
        array (
         'href'  => "invoice",
         'title' => "������� ����� � ���������",
         'level' => ACCESS_LEVEL_SERVER,    ),
    ),

    'refRepairs' => array (
        array (
         'href'  => "support",
         'title' => "��",
         'level' => ACCESS_LEVEL_SERVER ), // + ACCESS_LEVEL_CLIENT,
    ),


    'refDealers'   => array (
        array(
         'href'  => "refdealers_list",
         'title' => "������ �� �������",
         'level' => ACCESS_LEVEL_SERVER,     ),
    ),

    'refPlans'     => array (
        array(
         'href'  => "uploads_distr",
         'title' => "�������� � �������",
         'level' => ACCESS_LEVEL_SERVER,     ),
    ),

    'refSystem' => array (
        array (
         'href'  => "sync",
         'title' => "�������������",
         'level' => 0 ), // ACCESS_LEVEL_CLIENT,    ,
        array(
         'href'  => "login",
         'title' => ":��������� �����:",
         'qs'    => '?logout=yes',
         'level' => ACCESS_LEVEL_CLIENT + ACCESS_LEVEL_SERVER + ACCESS_LEVEL_VISOR,      ),
    ),



);

    /* ����� �� ��������  */
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