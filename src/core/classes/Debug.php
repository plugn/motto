<?php
/*
    Debug( $halt_level=0, $to_file=NULL )  -- Свой простой класс для отладки
      - $halt_level -- уровень severity, при возникновении ошибки такого или большего уровня
                       завершает выполнение скриптов
      - $to_file    -- можно указать имя файла, тогда вывод будет не в поток вывода, а в файл

  ---------
  * Flush( $prefix="<div>Trace log:</div><ul><li>", $separator="</li><li>", $postfix="<b>done.</b></li></ul>" ) --
                             вывод лога с помощью echo(), при этом лог соответствующим образом оформляется
      - $prefix / $postfix -- вставляются в начале/конце лога
      - $separator         -- вставляется между каждыми двумя записями лога

  * Milestone( $what="" ) -- записать в лог время, прошедшее с момента предыдущего запуска Milestone() с комментарием
      - $what -- опциональный комментарий

  * Trace( $what, $flush=0 ) -- сделать запись в лог, если нужно
      - $what  -- текст записи
      - $flush -- если нужно, то после записи лог выводится в echo()
  * TraceX( $what, $flush=0, $prewrap ) --  shortcut для вывода хтмл-кода с отступами

  * Trace_R( $what, $flush=0, $prewrap ) -- сделать запись в лог в виде развёрнутого массива
      - $what  -- массив, который будет развёрнут
      - $flush -- если нужно, то после записи лог выводится в echo()
      - $prewrap -- учитывать отступы
  * Error_R -- much like that

  * IsError( $error_level = 1 ) -- не возникало ли за время работы ошибки, true если возникало
      - $error_level -- уровень чувствительности. если были ошибки только меньшего уровня, игнорирует их

  * Error( $msg, $error_level=1 ) -- записать в лог сообщение об ошибке
      - $msg         -- сопроводительный текст ошибки
      - $error_level -- уровень severity ошибки. чем больше, тем страшней ошибка. рекомендуется (0..5)

  * Halt( $flush = 1 ) -- закончить выполнение скрипта
      - $flush -- если установлен в единицу, то ещё и выведет лог

  // Внутренние методы
  * _getmicrotime() -- для внутреннего использования, замер времени

  // Переменные
  * no_microtime -- не производить замеров времени

=============================================================== v.5 (Kuso, max@)
*/

class Debug
{
   var $halt_level;
   var $log;
   var $_milestone;
   var $milestone;
   var $is_error;
   var $to_file;
   var $no_microtime;
   var $trace_compact;  // max@

   function Debug( $halt_level=0, $to_file=NULL )
   {
     $this->trace_compact = 0; // max@
     $this->halt_level = $halt_level;
     $this->log = array();
     $this->is_error = array();
     $this->milestone = $this->_getmicrotime();
     $this->_milestone = $this->milestone;
     $this->to_file = $to_file;
     // $this->Trace("<b>log started.</b>",0);

     // max@ skip regex patterns in Trace()
     $this->skip_patterns = array(); // default
     $this->skip_patterns = array( '/TPL->/i', '/ачотакое/' );
     $this->Trace("<b>log started.</b>",0);
     // brutal skip logging
     // $e = htmlspecialchars(implode(",", $this->skip_patterns)); // no operations allowed w props at constructor
     // $this->Trace("<b>skip_patterns: </b>".($e?$e:' [empty] '), 0 );
   }

   // вывод лога
   function Flush( $prefix="<div>Trace log:</div><ul><li>", $separator="</li><li>", $postfix="</li></ul>" )
   {
     ob_start();
     echo " [".date("Y-M-d, H:i:s")."] ".$_SERVER["HTTP_X_REWRITE_URL"]. "<br />";
     // echo '_REQUEST[page]:'.$_SERVER["HTTP_X_REWRITE_URL"].$_REQUEST["page"] . "<br />";
     echo $prefix;
     $f=0;
     foreach ($this->log as $item)
     {
      if (!$f) $f=1; else echo $separator;
      echo $item;
     }
     echo $postfix;
     if ($this->to_file)
     {
        $data = ob_get_contents();
        ob_end_clean();
        $fp = fopen( $this->to_file ,"w");
        $f = fputs($fp,$data);
        fclose($fp);
        // echo ' written bytes to '.$this->to_file.': '.$f.'. ';
        // echo htmlspecialchars($data);
     }
     else ob_end_flush();
     $this->log = array();
   }

   // работа с временными отметками
   function _getmicrotime()
   {
     if ($this->no_microtime) return 0;
     list($usec, $sec) = explode(" ",microtime());  return ((float)$usec + (float)$sec);
   }
   function Milestone( $what="" )
   {
     if ($this->no_microtime) return 0;

     $m = $this->_getmicrotime();
     $diff = $m-$this->milestone;
     $this->Trace( "milestone (".sprintf("%0.4f",$diff)." sec): ".$what, 0 );
     $this->milestone = $m;
     return $diff;
   }

   function TraceX ( $what )  { return $this->Trace(htmlspecialchars(stripslashes($what)), 0, $prewrap=1); }
   function Trace  ( $what, $flush=0, $prewrap=0 )
   {
     // << max@ >>
     if ($prewrap) $what = '<pre>'.var_export($what,1).'</pre>';

     if (!empty($this->skip_patterns))  //    : regex matches skipping
     foreach ($this->skip_patterns as $pattern) if (preg_match( $pattern, $what )) return;

     //    : log collapsing
     static $itr = 0;
     static $last_what = null;
     if ($last_what === null) $last_what = $what;

     $last_el = count($this->log)-1;

     if ($this->trace_compact)
     {
       if($itr && $what != $last_what)
       {
         $this->log[$last_el] .= " x{ ".($itr+1)." }";
         $itr = 0;
       }
       if($what === $last_what)
       {
         $itr++;
         return;
       }
       $last_what = $what;
     }
     // << max@ / >>
     if ($this->no_microtime)
     {
       $this->log[] = "[tick] ".$what;
     }
     else
     {
       $m = $this->_getmicrotime();
       $diff = $m-$this->_milestone;

       $this->bps[$last_el+1] = $diff;
       $used = $this->bps[$last_el+1] - $this->bps[$last_el];

       $this->log[$last_el+1] =
          sprintf("[%0.4f] ", $diff ).

         ' <b><font color="'.$this->Colorize($used).'">'.
          sprintf("%0.4f", $used ).
         '</font></b> | '.
          $what."\r\n";

     }

     if ($flush) $this->Flush();
   }

   // вывод в лог
   function TraceZ( $what, $flush=0)
   {
     // << max@ >>
     static $itr = 0;
     static $last_what = null;
     if ($last_what === null) $last_what = $what;

     if ($this->trace_compact)
     {
       $last_el = count($this->log)-1;
       if($itr && $what != $last_what)
       {
         $this->log[$last_el] .= " x{ ".($itr+1)." }";
         $itr = 0;
       }
       if($what === $last_what)
       {
         $itr++;
         return;
       }
       $last_what = $what;
     }
     // << max@ / >>

     if ($this->no_microtime)
     {
       $this->log[] = "[tick] ".$what;
     }
     else
     {
       $m = $this->_getmicrotime();
       $diff = $m-$this->_milestone;
       $this->log[] = sprintf("[%0.4f] ",$diff).$what;
     }
     if ($flush) $this->Flush();
   }

   // вывод в лог массива
   function Trace_R( $what, $flush=0 )
   {
     ob_start();
     print_r($what);
     $result = ob_get_contents();
     ob_end_clean();
     $this->Trace("<b><a href=# onclick='var a=document.getElementById(\"__tracediv".substr(md5($result),0,6)."\");a.style.display=(a.style.display==\"none\"?\"block\":\"none\"); return false;'>Trace recursive</a></b><div style='padding-left:60px; display:none' id='__tracediv".substr(md5($result),0,6)."'><pre style='color:#444444; font:11px Tahoma;margin:0'>".$result."</pre><b><a href=# onclick='var a=document.getElementById(\"__tracediv".substr(md5($result),0,6)."\");a.style.display=(a.style.display==\"none\"?\"block\":\"none\"); return false;'>Hide trace recursive</a></b></div>", $flush);
     return "<pre>".$result."</pre>";
   }

   // не пуст ли лог ошибок
   function IsError( $error_level = 1 )
   {
     if (isset($this->is_error[ $error_level ]))  return true;
     else return false;
   }

   // добавить в лог запись об ошибке и возможно умереть
   function Error( $msg, $error_level=1 )
   {
     $this->Trace( "<span style='font-weight:bold; color:#ff4000;'>ERROR [".str_pad(str_repeat("!", $error_level ),5,".")."]: ".$msg."</span>", 0 );
     for ($e=$error_level; $e>=0; $e--)
       $this->is_error[ $e ]=1;
     if ($this->IsError($this->halt_level)) $this->Halt();
   }

   function Error_R( $msg, $error_level=1 )
   {
     ob_start();
     print_r($msg);
     $result = ob_get_contents();
     ob_end_clean();
     return $this->Error( "<pre>".htmlspecialchars($result)."</pre>", $error_level );
   }

   // умереть, тихо или громко
   function Halt( $flush = 1 )
   {
     header("Content-Type: text/html; charset=windows-1251");
     if ($flush) $this->Flush();
     die("prematurely dying.");
   }

  function Colorize ($range)
  {
    $color_cfg = array(
          0 => "#000000",
          5 => "#333333",
         10 => "#666666",
         20 => "#663366",
         30 => "#660099",
         50 => "#3366CC",
        100 => "#9933FF",
        200 => "#006633",
        400 => "#33FF99",
       1000 => "#CC6600",
      10000 => "#FF0000",
     100000 => "#00FF00",
    );

    $range = 10000 * $range;

    foreach($color_cfg as $k=>$v) {
      $idx[] = $k;
      if (!isset($idx[count($idx)-2])) continue;

      if ( $range >= $idx[count($idx)-2] && $range <= $idx[count($idx)-1] )
      return $color_cfg[$idx[count($idx)-1]];
    }
    // если ничего не смогли, сообщаем:
    return "#aFaFaF";
  }

// EOC{ Debug }
}



?>