<?php

  require_once (dirname(__FILE__)."/unitAbstract.php" );
    require_once (dirname(__FILE__)."/unitUnit.php" );

class unit404 extends unitUnit
{

  function Handle() {
    $this->tpl->Parse( '404.html', 'HTML:Html' );
  }


} // EOC { unit404 }

?>