<?php

  require_once (dirname(__FILE__)."/unitAbstract.php" );
    require_once (dirname(__FILE__)."/unitUnit.php" );

class unit404 extends unitUnit
{

  function Handle() {
    $this->dbgMsg ( '404 : ', $this->guide->uri , $pre=1, $htmlqt=0 );
    $this->tpl->Parse( '404.html', 'HTML:Body' );
  }


} // EOC { unit404 }

?>