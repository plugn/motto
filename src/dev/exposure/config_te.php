<?php

// unbound from global config. must store synchronized
// $this->proj_tpl_dir   = 'themes/client/';  // dupe of config.php
// $this->proj_plugn_dir = 'plugins/';        // dupe of config.php

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
$this->loop_empty = '_Empty';
$this->loop_delim = '_Delim';

// $this->tpl_markup = true;


?>