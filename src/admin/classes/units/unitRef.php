<?php

  require_once ( dirname(__FILE__)."/unitAbstract.php" );
    require_once ( dirname(__FILE__)."/unitUnit.php" );

class unitRef extends unitUnit
{

  // supported. refactored.
  var $db_table    = '';
  var $tpf         = '';
  var $html_title  = '';

  function Handle() {
    $this->_Init();

    $this->dbgMsg('uri : ', $this->guide->uri, 1); $this->dbgMsg('uri parts : ', $this->guide->uri_parts, 1);
    $this->dbgMsg('_FILES : ', $_FILES );  $this->dbgMsg('_REQUEST : ', $_REQUEST );
    // $this->dbgMsg('$_SERVER[DOCUMENT_ROOT] ', $_SERVER['DOCUMENT_ROOT'] );

    if ($this->guide->uri_parts[1]=='edit')    {
      $this->tpl->Set('CurrOperation', 'Редактирование');
      if ($_POST[$this->guide->form_exists_var]) {
        $this->refEdit_Save();  // $this->guide->Redirect( 'Location: http://'.$_SERVER['HTTP_HOST']."/".$this->guide->proj_root.$this->uripart.'/' );
        header ('Location: http://'.$_SERVER['HTTP_HOST']."/".$this->guide->proj_root.implode('/', $this->guide->uri_parts).'/'); die();

      }
      elseif (is_numeric($this->guide->uri_parts[2])) {
        $this->refEdit_construct($this->guide->uri_parts[2]);
        $this->tpl->Parse($this->tpf.':Edit', 'HTML:Body');
      }

      else { header ('Location: http://'.$_SERVER['HTTP_HOST']."/".$this->guide->proj_root.'404');      }
    }

    if ($this->guide->uri_parts[1]=='create')    {
      $this->tpl->Set('CurrOperation', 'Создать новый');
      if (!$_POST['__form_present']) {
        $this->refEdit_construct(); //   $this->refCreate_construct();
        $this->tpl->Parse($this->tpf.':Edit', 'HTML:Body'); // add
      } else  {
        $this->refEdit_Save();
        $this->dbgMsg('hop2:',
                'Location: http://'.$_SERVER['HTTP_HOST']."/".$this->guide->proj_root.$this->guide->uri_parts[0].'/');
        header ('Location: http://'.$_SERVER['HTTP_HOST']."/".$this->guide->proj_root.$this->guide->uri_parts[0].'/'); die();
      }
    }

    if ($this->guide->uri_parts[1]=='drop' && is_numeric($id=$this->guide->uri_parts[2]))    {
      if ($this->refItem_drop($id)); {      // в список с с прежним фильтром :
          header ('Location: '. $_SERVER['HTTP_REFERER']);   die();
      }
    }

    if ($this->guide->uri_parts[1]=='redrop' && is_numeric($id=$this->guide->uri_parts[2]))    {
      if ($this->refList_drop($id)); {      // в список с с прежним фильтром :
          header ('Location: '. $_SERVER['HTTP_REFERER']);   die();
      }
    }

    if (!$this->guide->uri_parts[1] || $this->guide->uri_parts[1]=='list')    {
      $this->tpl->Set('CurrOperation', 'Перечень');
      $this->refList_construct();    // $this->tpl->Parse('reference-form.html:List', 'HTML:Body'); // add
    }

  }


  function _Init() {
    $this->tpl->Set('HTMLTitle', $this->html_title);
  }

  /* keep this quiet in order of interface declaration  */
  function refEdit_construct( $id=null ) {
  }

  function AdjustData($fdata) {
    return $fdata;
  }

  function getPlaces( $places_data ) {     // $places_data[0] = ' --------- ';
    $db_id='place_id';
    $this->tbl->Init($this->guide->db_pfx.'place', $db_id);
    $pla = $this->tbl->Load( null, "place_login='".$this->guide->user['login']."'" );
    if (!empty($pla)) $places_data[$pla[0][$db_id]] = $pla[0]['place_name'];
    else {
        $this->tbl->Init($this->guide->db_pfx.'place');
        $pla = $this->tbl->Load( null );        // $this->dbgMsg('pla :: ',$pla);
        foreach ( $pla as $k=>$v ) $places_data[$v['place_id']]=$v['place_name'];
    }
    return $places_data;
  }

  function refEdit_Save() {
    $this->tbl->Init($db_table = $this->guide->db_pfx.$this->db_table);
    $fdata = UtilityCore::arrKeysPfxStrip('_fdata_', $_REQUEST);
    $fdata = $this->AdjustData($fdata);
    $this->dbgMsg('refEdit_Save() _fdata_ : ', $fdata );
    $this->tbl->SetData($fdata);
    $r =  $this->tbl->Save();  // INSERT     $this->dbgMsg('', 'hop1');
  }

  function refList_drop( $id ) {
    if ($id) {
      $this->tbl->Init($this->guide->db_pfx.$this->db_table);
      return $this->tbl->Drop( $id );
    }
  }

  function refList_construct() {
    $this->tbl->Init($this->guide->db_pfx.$this->db_table);
    $rs = $this->tbl->Load(null, '1=1 order by '.$this->tbl->db_id );
    $this->tpl->Set('href:create', $this->guide->uri_parts[0].'/create/'.$v[$this->tbl->db_id]);

    foreach ($rs as $k=>$v) {  //  a little precise
      $rs[$k]['href:edit']   = $this->guide->uri_parts[0].'/edit/'  .$v[$this->tbl->db_id];
      $rs[$k]['href:drop']   = $this->guide->uri_parts[0].'/drop/'  .$v[$this->tbl->db_id];
      $rs[$k]['href:drop.confirm'] = ' onclick="return confirm(\'Вы уверены в уничтожении выбранной позиции ?\'); " ';
    }

    $this->dbgMsg('rs : ', $rs, 1, 1);
    $this->tpl->Loop($rs, $this->tpf.':List', "HTML:Body");
  }




} // EOC { unitRef }

?>