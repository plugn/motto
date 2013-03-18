<?php
/*
 *
 * @description generalized html form fields processor
 * @usage
 *
    $list_events = array('sell'   => 'продажа', 'order'  => 'заказ запчастей', );
    $lists =
      array('tpl'=>'options:Select',
            'sql'      => 'select * from ...'
            'data'     =>  $list_events,
            'tplvar'   => 'optEvent',
            'field'    => '_fdata_color_id',
            '_misc'    => ' class="w100"',
            'value'    => $_REQUEST['_fdata_color_id'],
      ),
    echo $this->form->getHTML($lists);

    // 'data' имеет приоритет над 'sql'

 * ======================== ( max@ v 0.2 ) ======================
**/

class txFormField
{
  var $method_map = array(
    'plain'       => 'Plain',
    'options'     => 'Options',
  );

  function txFormField( &$guide ) {
    $this->guide = &$guide;
    $this->db = &$guide->db;
    $this->tpl = &$guide->tpl;
  }

  /* generalized call */
  function getHTML( $field_config ) {
    $p = explode(':', $field_config['tpl']); //  $this->guide->unit->dbgMsg(':::::', $p, 1,1);
    if (count($p)==2) {
      $field_config['tpl']      = $p[0];
      $field_config['tpl_mark'] = $p[1];
    }
    $method = $this->method_map[$field_config['tpl']];
    return $this->$method( $field_config );
  }

 // field Plain type
  function Plain ( $field_config ) {
    $_twd['_field_name']  = $field_config['field'];  // обязательное field, все специальные типа _misc
    $_twd['_field_value'] = $field_config['value'];
    foreach ($field_config as $k=>$v)                // все специальные типа _misc
      if (strpos(' '.$k,'_')==1)  $_twd[$k] = $v;
    $r = $this->tpl->ParseOne($_twd, 'forms/plain.html:'.$field_config['tpl_mark'], $field_config['tplvar'], 0 );
    return $r;
  }

  // field Options type
  function Options( $field_config ) {
    $data = array(); $_data =  array();

    if ($field_config['sql']) {
      $a = $this->db->sql2array($field_config['sql']); // UtilityCore::dbgMsg( BR.$field_config['sql'].' ::: ', $a);
      foreach ($a as $k=>$v)  // adapt to standard forms
        $_data[$v['id']]=$v['name'];
    } elseif ( is_array($field_config['data']) )
      $_data = $field_config['data'];

    $j = 0;
    foreach ( $_data as $k=>$v)  {
      $d = array( '_field_title'=>$v,'_field_value'=>$k, );
      if ( ($field_config['value'] == $k ))   $d[$this->tpl->cfg->loop_curr] = 1;
      $d['_Num'] = ++$j; // только внутреннее использования в шаблоне радиобаттона
      $data[] = $d;
    } // UtilityCore::dbgMsg( BR.'select.data : ', $data, $prewrap=1, $htmlquot=1 );

    $_twd ['_field_name'] = $field_config['field']; // обязательное field, все специальные типа _misc
    foreach ($field_config as $k=>$v)
      if (strpos(' '.$k,'_')==1)  $_twd[$k] = $v;
    $this->tpl->Load($_twd);
    $r = $this->tpl->Loop($data,'forms/options.html:'.$field_config['tpl_mark'],$field_config['tplvar'],0,$implode=0);
    $this->tpl->Free(array_keys($_twd));

    return $r;

  }

  /*  backward compatability : deprecated soon. must be removed at next version
  function Select( $field_config ) { return $this->Options( $field_config ); }
  function Radio ( $field_config ) { return $this->Options( $field_config ); }   */

}
?>