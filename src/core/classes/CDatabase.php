<?php
//****************************************************************************
// phpDatabase 2.1
//****************************************************************************
//      Author: Maxim Poltarak  <maxx at e dash taller dot net>
//    Category: Databases
//****************************************************************************
// The lib is FREEWARE. This means you may use it anywhere you want, you may
// do anything with it. The Author mentioned above is NOT responsible for any
// consequences of using this library.
// If you don't agree with this, you MAY NOT use the lib!
//****************************************************************************
// All improvings, feature requests, bug reports, etc. are gladly accepted.
//****************************************************************************
// Note: For best viewing of the code Tab size 4 is recommended
//****************************************************************************


// E1 sql wrapper
require_once ( "/usr/local/lib/php/UR/sql.hp" );





class CDatabase {
        var $link;
        var $db;
        var $host, $user, $pass;

        function CDatabase($db, $host="localhost", $user="", $pass="") {
                $this->db = $db; $this->host = $host; $this->user = $user; $this->pass = $pass;
                if($this->link = mysql_connect($host,$user,$pass))
                        mysql_select_db($db, $this->link);
                        // return mysql_select_db($db, $this->link); // max@ ??
                        // else return 0; // max@ ??
        }

        function query($sql) { // SQL_query ($query, $dbLink, $line=0)
                if(!$this->link) return 0;

                $result =  SQL_query ( $sql, $this->link );
                // $result =  mysql_query( $sql, $this->link );
                if (!$result)
                  trigger_error('<font color="red">'.$sql.'</font><br />'.$this->errno().' : '.$this->error());

                return $result;
        }

        function affected_rows() {
                return mysql_affected_rows($this->link);
        }

        function num_rows($q) {
                return mysql_num_rows($q);
        }

        function fetch_array($q, $result_type=MYSQL_ASSOC) {
                return mysql_fetch_array($q, $result_type);
        }

        function fetch_assoc($q) {
                return mysql_fetch_assoc($q);
        }

        function fetch_object($q) {
                return mysql_fetch_object($q);
        }

        function data_seek($q, $n) {
                return mysql_data_seek($q, $n);
        }

        function free_result($q) {
                return mysql_free_result($q);
        }

        function insert_id() {
                return mysql_insert_id($this->link);
        }

        function error() {
                return mysql_error($this->link);
        }

        function error_die($msg='') {
                die(((empty($msg))?'':$msg.': ').$this->error());
        }

        function sql2var($sql) {
                if((empty($sql)) || (!($query = $this->query($sql)))) return false;
                if($this->num_rows($query) < 1) return false;
                return $this->result2var($query);
        }

        function result2var($q) {
                if(!($Data = $this->fetch_array($q))) return false;
                $this->free_result($q);
                foreach($Data as $k=>$v) $GLOBALS[$k] = $v;
                return true;
        }

        function sql2array($sql, $keyField='') {
                if((empty($sql)) || (!($query = $this->query($sql)))) return false;
                if($this->num_rows($query) < 1) return false;
                return $this->result2array($query, $keyField);
        }

        function result2array($q, $keyField='') {
                $Result = array();
                while($Data = $this->fetch_array($q))
                        if(empty($keyField)) $Result[] = $Data;
                        else $Result[$Data[$keyField]] = $Data;
                $this->free_result($q);
                return $Result;
        }

        function list_tables() {
                return mysql_list_tables($this->db, $this->link);
        }

        // mysql_list_fields is deprecated
        function list_fields($table_name) {
                return mysql_list_fields($this->db, $table_name, $this->link);
        }

        //  max@
        function field_name($mysql_list_fields_rsc, $i) {
                return mysql_field_name($mysql_list_fields_rsc, $i);
        }

        function fetch_row($result) {
                return mysql_fetch_row($result);
        }

        function errno() {
                return mysql_errno($this->link);
        }

        function quote($value) {             // quote variable to make safe
               if (get_magic_quotes_gpc())   $value = stripslashes($value);
               if (!is_numeric($value))      $value = "'" . mysql_real_escape_string($value) . "'";
               return $value;
        }

};
?>