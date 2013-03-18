<?php
/*
 *
 * @description cache static library
 * @usage
 *     // see source at afisha/kino/front/ CmxGuide::Guide()
 *     if ( $this->tpl_caching && txCache::CachePreface( $this->proj_cache_dir.$this->uri_hash )) die(); // cache also written to stdout
 *     // not cached. process routine here
 *     // ...  after routine use txCache ::CacheOutput()
 *     if ( $this->tpl_caching )   return txCache::CacheOutput( $this->proj_cache_dir.$this->uri_hash, $html );
 *     else                        echo $html;
 *
 * ======================== ( max@ v 0.1a ) ======================
**/

class txCache
{

  function CacheCheck( $tfile ) { // $tfile = $this->proj_cache_dir.$this->uri_hash;
      if ( file_exists($tfile) && is_file($tfile) )  return true;
      else                                           return false;
  }

  function CacheOut( $tfile ) {
      $html = file_get_contents( $tfile );
      return $html;
  }

  function CachePut( $tfile, $what ) {
      $fp = fopen( $tfile, "w" );
      $f  = fputs( $fp,    $what );
      fclose($fp);
  }

}  // { EOC txClass }

?>