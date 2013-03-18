<?php

  require_once (dirname(__FILE__)."/unitAbstract.php" );
    require_once (dirname(__FILE__)."/unitUnit.php" );

class unitCmxHalls extends unitUnit
{


  function Handle() {
    $this->tpl->Parse('cmx_main.html', 'HTML:Body');

  }


  function Init() {
  }


  function createForm () {

  }



} // EOC { unitCmxHalls }

?>