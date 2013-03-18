<?php

  // $guide->unit->dbgMsg( '%%%film_editor : ',  $guide->user['film_editor'] );
  $cfg = array (
      'basic' => array (          // 'shows'=>'Сеансы',        // 'halls'=>'Залы',
      ),
      'film_editor' => array (    // 'shows'=>'Сеансы',         // 'halls'=>'Залы',         // 'films'=>'Фильмы',
      ),
      'portal' => array (
          'company'  => 'Компании',
          'refedit'  => 'Разделы',
          'refmix'   => 'Настройки',
          ),
  );

  if ( $guide->user['role']=='portal' )       $alias = 'portal';
  // elseif( $guide->user['film_editor']=='1')   $alias = 'film_editor';
  else                                        $alias = 'basic';

  // $lp_curr = $tpl->cfg->loop_curr;
  foreach ( $cfg[$alias] as $href => $title ) {
    $is_curr = 0;
    if ( $guide->uri_parts[1]==$href ) {
      $title = '<b>'.$title.'</b>';
      if (!$guide->uri_parts[2]) $is_curr = 1;
    }

    $data[] = array( 'href'=>$guide->uri_parts[0].'/'.$href, 'title'=>$title, $tpl->cfg->loop_curr=>$is_curr ); //'_Curr'
  }

  // $guide->unit->dbgMsg ( ' plugin comref_menu . data : ', $data , $pre=1, $htmlqt=0 );
  echo $guide->tpl->Loop( $data, 'plugins/cmx.afisha.kino.menu.html:Menu', $store_to='', $append=0, $implode=true, $wrap_empty=true );




?>