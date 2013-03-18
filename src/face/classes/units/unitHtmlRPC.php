<?php

  require_once (dirname(__FILE__)."/unitAbstract.php" );
    require_once (dirname(__FILE__)."/unitUnit.php" );

  /* AJAX server
    пишите нам сюда примерно так, а мы сгенерим поле формы
    'http://'+location.host+'/ui.server/tecrpc/'+alias+'/'+data_id   */


class unitHtmlRPC extends unitUnit
{

  function Handle() {
    $alias = $this->guide->uri_parts[1];
    $html = $this->Response( $alias );
    $this->tpl->Set("HTML:Html", $html );
  }

  function Response( $alias ) {
    switch ( $alias ) {
      case 'fpic_up' :
      $cfg = array(
         'tpl' => 'plain:Upload',
         'field' => '_fdata_newpic'. $this->guide->uri_parts[2],
         '_misc' => ' id="_fdata_newpic'. $this->guide->uri_parts[2].'" onchange="alert(\'changed yaya\')" ',
      );
      $html = $this->form->getHTML( $cfg ).CRLF.BR.CRLF;
      break;

      case 'show_halls':
        $halls_data[0] = ' -------------- зал ----------------  ';

        $place_id = $this->guide->uri_parts[2];
        if ( $this->guide->user['role']=='kino') $place_id = $this->guide->user['place_id'];

        if ( $place_id ) { // если площадка известна, то можно выбрать зал
          $this->tbl->Init($this->guide->db_pfx.'halls');
          $halls = $this->tbl->Load( null, "place_id=".$this->db->quote($place_id) ); $this->dbgMsg('@halls : ', $halls);

          if (!empty($halls))
            foreach ( $halls as $k=>$v )
              $halls_data[$v['id']] = $v['name'];  // $this->dbgMsg('@halls_data : ', $halls_data);

        }
        $cfg = array(
          'data'   => $halls_data,
          'tpl'    => 'options:Select', // 'tplvar' => 'formHall','value'  => ($id?$a['hall_id']:null),
          'field'  => '_fdata_hall_id',
          '_misc'  => ' class="w100" ', );
      $html = $this->form->getHTML( $cfg ).CRLF;
      break;

      default:
      $cfg = array (
        'data'   => array('0'=>'тестовое', '1'=>'поле формы', 'all'=>'укажите alias'),
        'field'  => '_filtr_is_complete',
        'tpl'    => 'options:Select',
      );
      $html = $this->form->getHTML( $cfg ).CRLF;
    }

    return $html;

  }


} // EOC { unitHtmlRPC }

?>