<?php

  /* typical tpl-plugin. environment : $guide, $db, $tpl */
  /* echo '@@ [plugin mainmenu executed '; return;       */

	$data = array (
	          array('menu_href' => 'about', 'menu_title'   => 'О компании',
	                'menu_img'  => 'items-about',   'menu_img_w' => '77',   'menu_img_h' => '13', ),
	          array('menu_href' => 'news', 'menu_title'=>'Новости',
	                'menu_img'  => 'items-news',    'menu_img_w' => '61',   'menu_img_h' => '13', ),
	          array('menu_href'=>'catalog', 'menu_title'=>'Каталог&nbsp;продукции',
	               'menu_img'  => 'items-catalog',  'menu_img_w' => '132',  'menu_img_h' => '16', ),
	          array('menu_href'=>'partners', 'menu_title'=>'Партнерам',
	                'menu_img'  => 'items-pairs',   'menu_img_w' => '77',   'menu_img_h' => '15', ),
	          array('menu_href'=>'qa', 'menu_title'=>'Вопросы и ответы',
	                'menu_img'  => 'items-faq',     'menu_img_w' => '135',  'menu_img_h' => '16', ),
	          array('menu_href'=>'contacts', 'menu_title'=>'Контакты',
	                'menu_img'  => 'items-contacts','menu_img_w' => '73',   'menu_img_h' => '13', ),
								);

  $img_sel_postfx = '-sel';
  $img_ext        = '.gif';

  $tpf = "plugins/common.menu.html:mainmenu";
	foreach ( $data as $k=>$v ) {
	  if ($guide->uri_parts[0] == $v['menu_href']) {
      $_mark = '_curr';
      $v['menu_img'] = $v['menu_img'].$img_sel_postfx;
      // однако если уровнем ниже, то ссылка
      if ($v['menu_href']!= $unit->nav_data[count($unit->nav_data)-1]['href'])
        $_mark = '_item';
    } else { $_mark = '_item'; }
    $v['menu_img'] = $v['menu_img'].$img_ext;

	  $tpl->Load( $v );
	  $items  .= $tpl->Parse( $tpf.$_mark );
	  $tpl->Free( array_keys($v) ); // чтобы переменные домена не застревали где попало
	}
	  echo $items;


?>