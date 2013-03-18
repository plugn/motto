<?php
/*
  @path         core/classes/dbUtil.php

  @title        DbUtil
  @description  odioso db table manipulator
  @author       Max L. Dolgov

  new features: queries $logging option, sql results caching at Load() and LoadById()
==================== v. 0.4 ( max@ ) ==================================
**/

class DbUtil {
  var $db_table = '';
  var $db_id = 'id';
  var $id;
  var $data;
  var $recordset;
  var $db_protect = false;
  var $logging = true;
  var $cache = array();
  var $caching = false;


  function DbUtil( &$db, $db_table='', $db_id = '' ) {
    $this->db = $db;
    if ($db_table)
      $this->Init ( $db_table, $db_id );
  }

  function Init( $db_table='', $db_id='' ) {
    $this->Cleanup( $id_aff=1 );
    $this->db_table = $db_table;
    if ($db_id)       $this->db_id = $db_id;
  }

  /* грузим  из таблицы как попало. если есть id, перенаправляем запрос в LoadById()   */
  function Load( $id = null, $where = '') {
    $this->Cleanup( $id_aff=1 );
    if (!$this->db_table) { trigger_error('DbUtil::Load() : empty db_table var!'); return false; }
    if (!is_null($id)) return $this->LoadById($id);
    $sql = 'select * from '.$this->db_table.($where?' where '.$where:'');
    if ($this->caching && isset($this->cache[$sql]) ) { return $this->cache[$sql]; }
    if ($this->logging) echo 'sql ## : '.$sql.BR;
    if ($result = $this->db->query($sql)) {
        $this->recordset = $this->db->result2array($result);
        if ( $this->caching ) $this->cache[$sql] = $this->recordset; // *** NEW ***
        return $this->recordset;
    }
    $this->err($sql);
    return false;
  }
  /* грузим из таблицы по id*/
  function LoadById($id = null ) {
    $this->Cleanup( $id_aff=1 );
    $sql = 'select * from '.$this->db_table.' where '.$this->db_id. " = ".$this->db->quote($id);
    if ($this->caching && isset($this->cache[$sql]) ) return $this->cache[$sql];
    if ($this->logging) echo 'sql ## : '.$sql.BR;
    if ($result = $this->db->query($sql)) {
        $this->id  = $id;
        $this->recordset = $this->db->result2array($result);
        $this->data = $this->recordset[0];
        if ( $this->caching ) $this->cache[$sql] = $this->data; // *** NEW ***
        return $this->data;
    }
    else $this->err($sql);
    return false;
  }

  function Cleanup ($id_aff = false) {
    if ($id_aff)       $this->id = false;
    $this->data      = false;
    $this->recordset = false;
  }

  function GetField($field) {
    return $this->data[$field];
  }

  function SetField($field, $value) {
    $this->data[$field] = $value;
    if ( $field == $this->db_id )
      $this->id = $value;
  }

  function SetData($data, $id_aff = false) {
    $this->Cleanup($id_aff);
    foreach ($data as $field => $value)
      $this->SetField($field, $value);
  }

  function Save( $mode=null ) { // attention!
    if ( $mode!=='insert' && $mode!=='update' )  $mode = $this->id? 'update':'insert';

    if ($mode=='insert') {
        $pairs = $this->data;
        unset($pairs[$this->db_id]);
        $qry = 'insert into `'.$this->db_table.'` set '.$this->qryDataPairs($pairs);
    } elseif ( $mode=='update' && $this->id ) {
        $qry = 'update `'.$this->db_table.'` set '.
                          $this->qryDataPairs().($this->id? ' where `'.$this->db_id."`='".$this->id."'":'');
    } else  {
        echo ' [ UPDATE FAILS: unspecified this->id ]'.BR ;
        return false;
    }

    return $this->dbWrite($qry);
  }

  function Drop( $id=null, $where='' ) {
    $cid = is_null($id)?$this->id:$id;
    if ($cid) { // trigger_error( __CLASS__.'::'.__FUNCTION__.'() : cannot manipulate data record without id!');
      $qry = 'delete from `'.$this->db_table.'` where `'.$this->db_id."`='".$cid."'";
    } elseif($where!='') {
      $qry = 'delete from `'.$this->db_table.'` where '.$where;
    } else {
      trigger_error( __CLASS__.'::'.__FUNCTION__.'() : cannot manipulate data without id or where-clause!');
    }
    return $this->dbWrite($qry);
  }

  function dbWrite( $qry ) { //  echo BR .'dbWrite(): . $qry; $this->err( $qry ); return;
    if ( $this->db_protect )          { $this->err('  Qry aborted: [ '.$qry.']'); return false; }
    else                              {  echo ' Qry [' . $qry . ']  '.BR ;                      }

    $this->db->query($qry);
    return;
  }


  function qryDataPairs ($pairs=null) {
    if (is_null($pairs))
      $pairs = $this->data;

    foreach ( $pairs as $field => $value )
      $s1[] = "`".$field."` = ".$this->db->quote( $value );
    $s2 = implode(', ', $s1);

    return $s2;
  }

  function err($sql) {
    trigger_error('<font color="#0ed000">'.$sql.'</font>'.BR.
                  'mysql&gt; err : '.$this->db->errno().'; msg:'.$this->db->error().BR);
  }


} // EOC { DbUtil }

?>