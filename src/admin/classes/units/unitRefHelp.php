<?php

  require_once (dirname(__FILE__)."/unitAbstract.php" );
    require_once (dirname(__FILE__)."/unitUnit.php" );

class unitRefHelp extends unitUnit
{

  var $tpf         = 'ref_help.html';
  var $html_title  = 'Помощь';

  function Handle() {
    $this->tpl->Set('HTMLTitle', $this->html_title);
    $this->dbgMsg ( ' unitRefHelp::Handle >>  ', $this->tpl->Get('HTML:Body'), $pre=1, $htmlqt=1 );
    $this->tpl->Parse('ref_help.html', 'HTML:Body');

  }



} // EOC { unitRefHelp }

?>