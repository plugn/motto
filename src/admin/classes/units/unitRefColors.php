<?php

  require_once (dirname(__FILE__)."/unitAbstract.php" );
    require_once (dirname(__FILE__)."/unitUnit.php" );
      require_once (dirname(__FILE__)."/unitRef.php" );


class unitRefColors extends unitRef
{

  // customize here :
  var $db_table    = 'colors';
  var $uripart     = 'refcolors';
  var $tpf         = 'refcolors.html';
  var $html_title  = 'Справочник цветов';

  function refEdit_construct( $id=null ) {

    if ($id) {
      $this->tbl->Init($this->guide->db_pfx.$this->db_table);
      $a = $this->tbl->Load( $id );
      $this->form->getHTML( array('tpl' => 'plain:Hidden',
                                  'tplvar'=>'hdnId',
                                  'field'=>'_fdata_id',
                                  'value'=>$id, ) );

    }
    $this->dbgMsg('a : ',$a, 1,1);

    $this->form->getHTML( array(  'tpl'       => 'plain:Hidden',
                                  'tplvar'    => '__form_present',
                                  'field'     => '__form_present',
                                  'value'     => 1,                   ) );

    $this->form->getHTML( array(  'tpl'       => 'plain:Text',
                                  'tplvar'    => 'txtName',
                                  'field'     => '_fdata_name',
                                  'value'     => ($id?$a['name']:null),
                                  '_misc'     => ' class="w100"',     ) );

  }


} // EOC { unitRefColors }

?>