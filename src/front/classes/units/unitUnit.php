<?php

  require_once (dirname(__FILE__)."/unitAbstract.php" );

class unitUnit extends unitAbstract
{

  function unitUnit( $guide ) {
    $this->guide = &$guide;
    $this->db    = &$guide->db;
    $this->tpl   = &$guide->tpl;
    $this->form  = &$guide->form;
    $this->tbl   = &new DbUtil($guide->db);
    if (!empty($this->guide->user))
        $this->dbgMsg('user@ '.date("H-i-s").' :', $this->guide->user);
    $this->dbgMsg('_SESSION :', $_SESSION, 1,1); // $this->dbgMsg('_GET :', $_GET, 1,1 );
    // $this->dbgMsg('proj_root:',$this->proj_root,0); $this->dbgMsg('http_tpl_dir:',$this->http_tpl_dir,0);
  }

  function Handle() {
    echo 'unitUnit()::Handle(); '.BR;
  }

  /*** кандидаты на вынесение в сервисную либу ***/
  function dbQryRs ($sql) { // db wrappo le comfort
    return $this->db->result2array($this->db->query($sql));
  }

  function dbgMsg($desc, $_var, $prewrap=1, $htmlquot=0 ) {
    return UtilityCore::dbgMsg($desc, $_var, $prewrap, $htmlquot );
  }


  function dbNormalize( $id ) { // нужен  только для 'INSERT ...'
    if (!$id) return false; // пустой id (например после операции update) этот вызов не имеет смысла

    $this->tbl->Init($db_table = $this->guide->db_pfx.'events');
    $r = $this->tbl->Load($id);

    if ( strpos(' '.$r['event'], 'repair_')==1 )
      $sql =  "delete from ".$this->guide->db_pfx."events where event='".$r['event'].
              "' and vin='".$r['vin']."' and user_date='".$r['user_date']."' and id <> '".$id."'";
    else
      $sql = "delete from ".$this->guide->db_pfx."events where event='".$r['event'].
              "' and vin='".$r['vin']."' and id <> '".$id."'";
    $result = $this->db->query($sql);
    if (!$result) $this->tbl->trigger_error($sql);

    return $result;
  }
} // EOC { unitUnit }

?>