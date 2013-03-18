<?php

class http {
   var $proxy_host = "";
   var $proxy_port = 0;

  function http_fopen($url, $useragent='*')
  {
      // Parse the URL, and make sure we can handle the schema
      $uri = parse_url($url);

      // has the user set $proxy_host?
         $conn_host = $this->proxy_host? $this->proxy_host : $uri['host'];
         $conn_port = $this->proxy_port? $this->proxy_port : $uri['port'];

      switch ($uri['scheme']) {
        case 'http':
          $fp = @fsockopen($conn_host, ($conn_port ? $conn_port : 80), $errno, $errstr, 15);
          break;
        case 'https':
          // Note: only works for PHP 4.3 compiled with openssl
          $fp = @fsockopen("ssl://$conn_host", ($conn_port ? $conn_port : 443), $errno, $errstr, 20);
          break;
        default:
          $this->error = "invalid schema: ".$uri['scheme'];
          return false;
      }
      if(!$fp)
         return false;

      $query = 'GET '.$url." HTTP/1.0\r\n".
               'Host: '.$uri['host'].':'.$uri['port']."\r\n".
               'User-agent: '.$useragent."\r\n".
               "\r\n";

      fwrite($fp, $query);

      // discard the HTTP header
      while(trim(fgets($fp, 1024)) != "");

      // return the active file pointer
      return $fp;
   }
}
?>
