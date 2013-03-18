<?php

// unbound from global config. must store synchronized
$this->proj_tpl_dir   = 'themes/default/'; // dupe of config.php
$this->proj_plugn_dir = 'plugins/';        // dupe of config.php

// настройки шаблонного движка - RocketTE compatible
$this->tpl_tag_open  = '{{';
$this->tpl_tag_close = '}}';
$this->tpl_mark_open   = 'TPL:';
$this->tpl_mark_close  = '/TPL:';
$this->tpl_plugin  = '!';
$this->tpl_include = '@';

// списки
$this->loop_item  = '_Item' ;
$this->loop_curr  = '_Curr' ;
$this->loop_empty = '_Empty';  // not implemented yet
$this->loop_delim = '_Delim';

// if..else
$this->tpl_construct_if       = "?";    // {{?var}} or {{?!var}}
$this->tpl_construct_ifelse   = "?:";   // {{?:}}
$this->tpl_construct_ifend    = "?/";   // {{?/}} is similar to {{/?}}

$this->tpl_markup = true;

?>