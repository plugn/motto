<?php

  require_once (dirname(__FILE__)."/unitAbstract.php" );
    require_once (dirname(__FILE__)."/unitUnit.php" );

class unitText extends unitUnit
{


  function Handle() {
    /*
    $this->tpl->Set('cell_heading', ' [cell_heading] ');
    $this->tpl->Set('cell_value', ' [cell value] ');
    $this->tpl->Parse('te_auto.html', 'HTML:Html');
    $this->tpl->Parse('_tpl_inner.html', 'HTML:Html');
    */

    // $this->tpl->Parse('reference.html', 'HTML:Html');
    $this->tpl->Parse('reference.html', 'HTML:Body');
  }



} // EOC { unitText }

?>