<?php

  // $guide->unit->dbgMsg( '%%%film_editor : ',  $guide->user['film_editor'] );

  $cfg = array (
      'basic' => array (
          'shows'=>'������',
          'halls'=>'����', ),
      'film_editor' => array (
          'shows'=>'������',
          'halls'=>'����',
          'films'=>'������', ),
      'portal' => array (
          'shows'=>'������',
          'halls'=>'����',
          'films'=>'������',
          'prefs'=>'���������', ),
  );


  if ($guide->user['role']=='portal')         $alias = 'portal';
  elseif( $guide->user['film_editor']=='1')   $alias = 'film_editor';
  else                                        $alias = 'basic';

  foreach ( $cfg[$alias] as $href => $title ) $data[] = array( 'href'=>$href, 'title'=>$title, );

  echo $guide->tpl->Loop( $data, 'plugins/cmx.afisha.kino.menu.html:Menu', $store_to='', $append=0, $implode=true, $wrap_empty=true );




?>