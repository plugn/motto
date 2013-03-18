<?php

  require_once (dirname(__FILE__)."/unitAbstract.php" );
    require_once (dirname(__FILE__)."/unitUnit.php" );

class unitCmxMain extends unitUnit
{


  function Handle() {
    $this->dbgMsg('session_name():', session_name() );
    $this->dbgMsg('guide->session_name:', $this->guide->session_name);
                                  
    $this->tpl->Parse('cmx_main.html', 'HTML:Body');

  }


  function Init() {
  }


  function createForm () {

  }



} // EOC { unitCmxMain }

?>