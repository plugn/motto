<?php

  // $guide->unit->dbgMsg( '%%%film_editor : ',  $guide->user['film_editor'] );

  $cfg = array (
      'basic' => array (
          'shows'=>'Сеансы',
          'halls'=>'Залы',
          'help' =>'Помощь',
      ),
      'film_editor' => array (
          'shows'=>'Сеансы',
          'halls'=>'Залы',
          'films'=>'Фильмы',
          'help' =>'Помощь',
      ),
      'portal' => array (
          'shows'=>'Сеансы',
          'halls'=>'Залы',
          'films'=>'Фильмы',
          'prefs'=>'Настройки',
          'help' =>'Помощь',
      ),
  );


  if ($guide->user['role']=='portal')         $alias = 'portal';
  elseif( $guide->user['film_editor']=='1')   $alias = 'film_editor';
  else                                        $alias = 'basic';

  // foreach ( $cfg[$alias] as $href => $title )    $data[] = array( 'href'=>$href, 'title'=>$title, ); $lp_curr = $tpl->cfg->loop_curr;
  foreach ( $cfg[$alias] as $href => $title ) {
    $is_curr = 0;
    if ( $guide->uri_parts[0]==$href ) {
      $title = '<b>'.$title.'</b>';
      if (!$guide->uri_parts[1]) $is_curr = 1;
    }
    $data[] = array( 'href'=>$href, 'title'=>$title, $tpl->cfg->loop_curr=>$is_curr ); //'_Curr'
  }

  echo $guide->tpl->Loop( $data, 'plugins/cmx.afisha.kino.menu.html:Menu', $store_to='', $append=0, $implode=true, $wrap_empty=true );




?>