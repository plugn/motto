<?php

// trail-slash: fixed presence
$_SERVER['DOCUMENT_ROOT'] = preg_replace('{/$}', '', $_SERVER['DOCUMENT_ROOT']).'/';

$this->doc_root       = $_SERVER["DOCUMENT_ROOT"];
$this->proj_root_os   = dirname(__FILE__);
$this->proj_tpl_dir   = 'themes/face/'; // 'themes/front/';

// $this->proj_root      = UtilityCore::getLocalPath ( $this->proj_root_os.'/.' ).'/';
// manually
$this->proj_root      = 'afisha/kino/'; // 'afisha/kino/face/'; // 'afisha/kino/'

// http template path (try one of lines below )
// $this->http_tpl_dir   = UtilityCore::getLocalPath ( realpath(dirname(__FILE__)) ).'/'.$this->proj_tpl_dir;
$this->http_tpl_dir   = '/'.UtilityCore::getLocalPath ( realpath(dirname(__FILE__)) ).'/'.$this->proj_tpl_dir;

$this->proj_class_dir = 'core/classes/';
$this->proj_plugn_dir = 'plugins/';
$this->proj_unit_dir  = 'classes/units/';

$this->proj_cache_dir =  '/disk2/kino/face/'; // dirname(__FILE__).'/../.html/front/';
$this->tpl_caching = true;
$this->tpl_ssi = true;

// ��� ������� �� ��������� [ text | feed | ... | etc. ]
$this->unit_default = 'text';
$this->unit_404     = '404';
$this->token404     = '*404*';

$this->units_map = array (  '/'          => 'main',
                            'film'       => 'main',
                            'show'       => 'main',

                            'place'      => 'main',
                            'overview'   => 'main',

                            '404'        => '404',
                            'login'      => 'login',
                            'ht.rpc'     => 'htmlRPC',

                            'about'      => 'text',
                         );

$this->debug = true;
$this->debug_to_file = dirname(__FILE__).'./../_tmp/debug.html';

$this->form_exists_var = '__form_present';
$this->tpl_main_wrapper = 'common.af.html'; // 'common_feed.html'; // 'common.tt.html';

$this->auth_required = false; // true;          // ����� �� �����������
$this->auth_var      = 'cmxauth_'; // ��� ���������� � ������
$this->auth_uri      = 'login';            // �������� ����� ������
$this->cookie_ttl    = 3600*24*365;   // (������ � ����)*(����� � ���)*(���� � ����)
$this->session_ttl   = 3600*3;        // (������ � ����)*(�����)
$this->session_name  = 'cmxsess_';

$this->cart_pfx = '_cart_'; // ������� ������ �������

// ��������� � ������� ����������������� ������ � �� ����
$this->roles = array(   /* �����   =>  ����
                        'admin'  =>  ACCESS_LEVEL_SERVER,   // ��������
                        'galion' =>  ACCESS_LEVEL_VISOR,
                        'distr'  =>  ACCESS_LEVEL_SERVER,   // �����������
                        'pred'   =>  ACCESS_LEVEL_VISOR,
                        */
                    );

$this->resizer = '/usr/local/bin/imageresize';

?>