<?php
/**
 *
 */

class DbAdapter extends Config
{
  var $_class = 'CDatabase'; // as defaults
  var $obj;

  function &StaticFactory() {
    static $obj;
    $_class = $this->_class;
    if (get_class($obj) !== strtolower($_class))  // References are not stored STATICally!
      $obj = new $_class ( $this->db_name, $this->db_host, $this->db_user, $this->db_password );
    return $obj;
  }
  // *Born* is experimental but universal method of creating an object
  function &StaticBorn() {
    static $obj;
    $_class = $this->_class;
    if (get_class($obj) !== strtolower($_class))  // References are not stored STATICally!
      $obj = UtilityCore::cBorn ( $_class,
      $params = array( $this->db_name, $this->db_host, $this->db_user, $this->db_password) );
    return $obj;
  }

  function &Factory() {
    $_class = $this->_class;
    if (get_class($this->obj) !== strtolower($_class))
      $this->obj = &new $_class ( $this->db_name, $this->db_host, $this->db_user, $this->db_password );
    return $this->obj;
  }

}

?>