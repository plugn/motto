<?php

// 
$_SERVER['HTTP_HOST'] = 'www.e1.ru';

// trail-slash: fixed presence
$_SERVER['DOCUMENT_ROOT'] = preg_replace('{/$}', '', $_SERVER['DOCUMENT_ROOT']).'/';

$this->doc_root       = $_SERVER["DOCUMENT_ROOT"];
$this->proj_root_os   = dirname(__FILE__);
$this->proj_tpl_dir   = 'themes/default/';
// $this->proj_tpl_dir   = 'themes/server/';

$this->proj_root      = UtilityCore::getLocalPath( $this->proj_root_os.'/.' ).'/';
// http template path (try one of lines below )
// $this->http_tpl_dir   = UtilityCore::getLocalPath ( realpath(dirname(__FILE__)) ).'/'.$this->proj_tpl_dir;
$this->http_tpl_dir   = '/'.UtilityCore::getLocalPath ( realpath(dirname(__FILE__)) ).'/'.$this->proj_tpl_dir;

$this->proj_class_dir = 'core/classes/';
$this->proj_plugn_dir = 'plugins/';
$this->proj_unit_dir  = 'classes/units/';

// тип раздела по умолчанию [ text | feed | ... | etc. ]
$this->unit_default = 'text';
$this->unit_404     = '404';
$this->token404     = '*404*';

$this->units_map = array (  '/'          => 'cmxMain',
                            'main'       => 'cmxMain',
                            '404'        => '404',
                            'about'      => 'text',
                            'login'      => 'login',
                            'halls'      => 'refHalls',
                            'films'      => 'refFilms',
                            'shows'      => 'refShows',
                            'ht.rpc'     => 'htmlRPC',
                            'prefs'      => 'prefsMain',

                            'autxpo'     => 'autXpo',
                            'comprefs'   => 'comPrefs',

                            'help'       => 'refHelp',
                         );
$this->debug = false;
$this->debug_to_file = dirname(__FILE__).'./../_tmp/debug.html';

$this->form_exists_var = '__form_present';
$this->tpl_main_wrapper = 'common.html';

$this->auth_required = true;          // нужна ли авторизация
$this->auth_var      = 'cmxauth_'; // имя переменной в сессии
$this->auth_uri      = 'login';            // реальный адрес логина
$this->cookie_ttl    = 3600*24*365;   // (секунд в часе)*(часов в дне)*(дней в году)
$this->session_ttl   = 3600*3;        // (секунд в часе)*(часов)
$this->session_name  = 'cmxsess_';

$this->cart_pfx = '_cart_'; // префикс данных корзины

// возможные в системе привилегированные логины и их роли
$this->roles = array(   /* логин   =>  роль
                        'admin'  =>  ACCESS_LEVEL_SERVER,   // тестовые
                        'galion' =>  ACCESS_LEVEL_VISOR,
                        'distr'  =>  ACCESS_LEVEL_SERVER,   // действующие
                        'pred'   =>  ACCESS_LEVEL_VISOR,
                        */
                    );

$this->resizer = '/usr/local/bin/imageresize';
$this->cache_dirs = array( '/disk2/kino/face/', '/disk2/kino/wap/' );

?>