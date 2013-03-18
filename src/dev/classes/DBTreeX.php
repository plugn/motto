<?php

class DBTreeX extends DBTree
{


  function displayOne( $item_id, $indent)
  {
    static $sTop;

    $item_LR = $this->getNodeLR( $item_id );

    $this->tpl->Set ( 'title', $this->getInfo($item_id, 'name' ));
    $this->tpl->Set ( 'id', $item_id );
    $this->tpl->Set ( 'href', '?id='.$item_id );
    $this->tpl->Set ( 'sTop',  $this->cfg['treeY']+((++$sTop)*25) );
    $this->tpl->Set ( 'sLeft', $this->cfg['treeX']+(25*$indent) );
    $this->tpl->Load( $item_LR );
    $r .= str_repeat($this->tpl->Parse($this->tpf.':_WtSpc'), $indent);
    $r .= $this->tpl->Parse($this->tpf.':_Item');
    return $r;
  }


}

?>
