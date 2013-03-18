<?php


  require_once "UR/mysql_get_login_data.hp";
  $mysql_host = 'db';
  $mysql_database = 'kino';
  list( $mysql_login, $mysql_passwd ) = mysql_get_login_data( $mysql_host );


  $this->db_host     = $mysql_host;

  $this->db_user     = $mysql_login ;         // "dbuser";
  $this->db_password = $mysql_passwd ;        // "dbpass";
  $this->db_name     = $mysql_database ;      // "test";
  $this->db_prefix   = "";

  $this->db_al       = "mysql";



?>