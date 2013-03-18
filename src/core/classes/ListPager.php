<?php

/**
 *
 * @name ListPager
 * @author Max L. Dolgov
 *
 * Attention! this code work under Rocket Enviro only, thereby it needs a revision
 *
 * примитивная листалка постраничного вывода
 *   без запросов в БД ( с версии 0.1 )
 *   ибо порой мы хотим листать не только таблицы,
 *   да и парсинг результатов происходит
 *   всегда уровнем выше, в хандлере.
 *
 *   $cfg. ключи массива конфигурации:
 *     "query_amount", // полный размер выборки
 *     "page_max_items" = 3*8, // количество элементов на страницу
 *     "template"  =  'contents.html:Pager', // шаблонка
 *     "pageno_key" = '_page', // имя переменной, хранящей номер страницы в ссылках и querystring
 *
 * ============================== max@ v 0.2 ==========================
 */

class ListPager
{
  var $page_max_items_dflt = 24;
  var $pageno_key_dflt = "_page";

  function ListPager( &$rh, &$cfg )  {
    $this->rh = $rh;
    $this->cfg = $cfg;
  }

  function Handle()  {
    extract ($this->cfg); // java-like class-members access

    // settings
    $page_max_items = $page_max_items ? $page_max_items : $this->page_max_items_dflt;
    $pages_qty = ceil( $query_amount / $page_max_items );
    $pageno_key = $pageno_key ? $pageno_key : $this->pageno_key_dflt;
    $page_current = $_GET[$pageno_key]?(integer)$_GET[$pageno_key]:1;

    // querystring tweaks
    $_QS = str_replace('page=index.php&', '', $_SERVER["QUERY_STRING"]);
    $_QS = str_replace('page='.$this->rh->url.'&', '', $_QS);

    if ( $pages_qty >1 )
    { for($i=1; $i<=$pages_qty; $i++)
      {
        $QS = '';
        if ($i==$page_current)
          $r[$i]["IsCurrent"]=1;
        $r[$i]["PageNo"]=$i;

        if(strpos(" ".$_QS, $pageno_key."=" ))
          $QS = preg_replace("/(.*".$pageno_key."=)(\d*)(.*)/i", "\${1}".$i."\${3}",$_QS,-1);
        else
          $QS = $_QS.'&'.$pageno_key."=".$i;

        $r[$i]["Href"] = $this->rh->ri->Href( $this->rh->url, STATE_IGNORE ).'?'.$QS;
      }
      $this->rh->tpl->Loop( $r, $template.'_List', "_List", false, false );
      return $this->rh->tpl->Parse( $template );
    }
  }

} // EOC ListPager

?>