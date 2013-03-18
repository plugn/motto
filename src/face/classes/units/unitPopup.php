<?php

  require_once (dirname(__FILE__)."/unitAbstract.php" );
    require_once (dirname(__FILE__)."/unitUnit.php" );

class unitPopup extends unitUnit
{
  var $dmn = array();

  function Handle() {
    $this->Init();
  }

  // попапы бывают разные, можно дописывать методы сюда, расширяя их набор.
  function Init() { // $this->dbgMsg('this->guide->uri_parts' , $this->guide->uri_parts); die();
    switch ($this->guide->uri_parts[1]) {
        case 'what_dealer'   : return $this->WhatDealer();        break;
        case 'store_load'    : return $this->StoreLoad();         break;
        case 'invoice_list'  : return $this->InvoiceList();       break;
        case 'strld_dealer'  : return $this->StoreLoadDealer();   break;
        case 'nvclst_dealer' : return $this->InvoiceListDealer(); break;
        case 'popup'         :
        default              : return $this->GetInfo();           break;
    }
  }

  function GetInfo() {
    $id=$_REQUEST['vin'];
    $this->tbl->Init($db_table = $this->guide->db_pfx.'events');
    $dmn = $this->tbl->Load(null,  " vin='".$id."'");
    $this->dbgMsg('dmn: ', $dmn, 1);
    $this->tpl->Set('_vin_', $_REQUEST['vin']);
    foreach($dmn as $k=>$v) $dmn[$k]['ru_event']=$this->guide->cl_events[$v['event']];
    $this->tpl->Set('HTMLTitle', 'VIN#'.$_REQUEST['vin'].' - История операций');
    $this->tpl->Loop($dmn, 'popup_history.html:Feed', "HTML:Body" );
    $this->tpl->Parse('xhtml.html', 'HTML:Html');
  }

  function WhatDealer() {
    $this->tbl->Init($db_table = $this->guide->db_pfx.'dealers');
    $dda = $this->tbl->Load( null );
    $dealers_data[0] = ' --------- ';
    foreach ($dda as $k=>$v)
      if ( !in_array($this->guide->GetRole($v['id']), array(ACCESS_LEVEL_SERVER,ACCESS_LEVEL_VISOR)) )
        $dealers_data[$v['id']]=$v['name'];
    $body.= $this->form->getHTML(
        array(      'data'   => $dealers_data,
                    'tplvar' => '',
                    'value'  => 1,
                    'field'  => '_fpop_dealer',
                    '_misc'  => ' onchange="r=window.opener.document.getElementById(\'_f_nvc_ctg\');'.
                                'if(id=this.options[this.selectedIndex].value!=0){'.
                                'pop_opnrMemDealer(this);'.
                                'r.selectedIndex=0;window.close();}"',
                    'tpl'    => 'options:Select',
             )
    ) ;
    $body.= $this->form->getHTML(
        array(
                    'tplvar' => '',
                    'value'  => 'выбрать',
                    'field'  => '',
                    'tpl'    => 'plain:InputButton',
                    '_misc'  => ' onclick="sel=document.getElementById(\'_fpop_dealer\');'.
                                'r=window.opener.document.getElementById(\'_f_nvc_ctg\');'.
                                'if(id=sel.options[sel.selectedIndex].value!=0){'.
                                'pop_opnrMemDealer(sel);'.
                                'r.selectedIndex=0;window.close();}"',
             )
    ) ;

    $body = '<nobr>'.$body.'</nobr>';
    $this->tpl->Set("HTML:Body", $body );
    $this->tpl->Parse('xhtml.html', 'HTML:Html');

  }

  function StoreLoad() {
    $this->tpl->Set('HTMLTitle', 'Выбор со склада');
    require ( 'presets/form/core.form_exists.php' ); // "form_exists" preset
    $where_clause = '';
    $this->tbl->Init( $this->guide->db_pfx."factory_uploads" ) ;
    $a = $this->tbl->Load( null, $where_clause.'status=1 and reserve=0 order by id asc' );
    foreach ( $a as $k=>$v ) {
      /***  FIELD  : RESERVED ***/
      $a[$k]['f_reserve'] = $this->form->getHTML(
            array(  'tpl'       => 'plain:Check',
                    'field'     => 'reserve['.$v['id'].']',
                    'value'     => $v['id'],
                    '_field_id' => 'reserve_'.$v['id'],
                    '_misc'     => ($v['reserve']?' checked="checked"':''),
                 )
      );
    }
    $this->tpl->Loop($a, 'invoice.html:StoreList', 'HTML:Body');
    $this->tpl->Parse('xhtml.html', 'HTML:Html');

  }


  function StoreLoadDealer() {
    $this->tpl->Set('HTMLTitle', 'Выбор со склада');
    require ( 'presets/form/core.form_exists.php' ); // "form_exists" preset
    $where_clause = ($this->guide->user['role']==1)?(" dealer_id=".$this->guide->user['id']." and "):"";
    $this->tbl->Init( $this->guide->db_pfx."factory_uploads" ) ;
    $a = $this->tbl->Load( null, $where_clause."status=1 and reserve=1 and deal_reserve=0 order by id asc" );
    foreach ( $a as $k=>$v ) {
      /***  FIELD  : RESERVED ***/
      if ( $v['deal_upload'])         $a[$k]['f_reserve']='продан';
      elseif ( $v['deal_reserve'] )   $a[$k]['f_reserve']='резерв';
      else  $a[$k]['f_reserve'] = $this->form->getHTML(
            array(  'tpl'       => 'plain:Check',
                    'field'     => 'deal_reserve['.$v['id'].']',
                    'value'     => $v['id'],
                    '_field_id' => 'reserve_'.$v['id'],
                    '_misc'     => ($v['deal_reserve']?' checked="checked"':''),
                 )
      );
    }
    $this->tpl->Loop($a, 'invoice_dealer.html:StoreList', 'HTML:Body');
    $this->tpl->Parse('xhtml.html', 'HTML:Html');

  }

  function InvoiceList() {
    $this->tbl->Init( $this->guide->db_pfx."invoices" ) ;
    $a = $this->tbl->Load( null, "`mode`='distr' order by `user_datetime` desc" );
    foreach ( $a as $k=>$v ) {
        if ( empty($v['buyer_name']) ) $a[$k]['buyer_name'] = ' &mdash; ';
        $a[$k]['invc_date'] = date("d.m.Y", strtotime($v['invc_datetime']));
    }

    $this->tpl->Loop($a, 'invoice.html:InvoiceList', 'HTML:Body');
    $this->tpl->Parse('xhtml.html', 'HTML:Html');
  }

  function InvoiceListDealer() {
    $this->tbl->Init( $this->guide->db_pfx."invoices" ) ;
    $a = $this->tbl->Load( null, "`mode`='dealer' order by `user_datetime` desc" );
    foreach ( $a as $k=>$v ) {
        if ( empty($v['buyer_name']) ) $a[$k]['buyer_name'] = ' &mdash; ';
        $a[$k]['invc_date'] = date("d.m.Y", strtotime($v['invc_datetime']));
    }

    $this->tpl->Loop($a, 'invoice_dealer.html:InvoiceList', 'HTML:Body');
    $this->tpl->Parse('xhtml.html', 'HTML:Html');
  }

} // EOC { unitPopup }




?>