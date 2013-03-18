<?php

/**  class: ThemePlayTE

   ! feature list:
     - переменные вида {{var}}
     - поддержка секций в файлах темплейтов  {{TPL:section}} фрагмент {{var}} {{/TPL:section}}
     - плагины (магики). вставка в шаблон результата исполнения php-кода
     - инклюды. абсолютные: {{@tplfile.html}} {{@tplfile.html:mark}}; секция текущего шаблона: {{@.:mark}} {{@:mark}}
     - runtime-кэширование прочитанных шаблонов
     - использование include-path для поиска templates, e.g. никаких механизмов для смены скинов/тем не нужно
     - локализованы настройки TE (..)
       может работать с движком и автономно ( без ссылки на параметр guide, см. метод ThemePlayTE::_Init() )
     - долгожданный регексп для сохранения в шаблоне произвольной xml-декларации
     - простой парсинг списков с поддержкой разделителя(cfg->loop_delim) и выделения элементов ( cfg->loop_curr) :
         Loop($data, $tpf, $store_to="", $append=0)

   + new features:
     - режим отображения имен/следов шаблонов в хтмл комментариях ( tpl_markup )
     - вложенные шаблоны корректно считываются, кэшируются и не создают помех своим врапперам( for future features )
     - устранен опасный конфликт с новой версией PCRE. Modifier 'u'. Pattern strings are treated as UTF-8.
       This modifier is available from PHP 4.1.0 or greater on Unix and from PHP 4.2.3 on win32.
       UTF-8 validity of the pattern is checked since PHP 4.3.5.
     - звездочка-префикс для ключей, {{*key}} дублирует ключи массивов при парсинге методами Loop(), ParseOne()
       рекомендуется использовать именно вариант с префиксом *, вариант без префикса переходит в deprecated

*/


/** RocketTE Interface embedded
 *
 *  Set( $key, $value=1 )  -- установить значение ключу
 *  SetRef( $key, &$ref )  -- установить значение ссылкой
 *  Append( $key, $value ) -- дописать в конец
 *  Is( $key )             -- true, если этот ключ хоть что-то значил (isset is a keyword)
 *  Free( $key="" )        -- очистка домена или unset переменной
 *   - $key   -- имя переменной (поля), если пустое, то очищается весь домен
 *  Load( $domain )        -- загрузка массива в ключи
**/

class ThemePlayTE {
  var $domain;
  var $recent_cache;

  function ThemePlayTE( &$cfg, &$guide ) {
    $this->_Init($cfg, $guide);
  }

  function _Init( & $cfg, &$guide ) { // pass by reference
    $this->cfg   = &$cfg;
    $this->guide = &$guide;
  }

/* ======================== RocketTE: ===================================*/
  function Get( $key ) // -- получить значение (намеренно без ссылки)
  { return isset($this->domain[$key]) ? $this->domain[$key] : "" ; }

  function Set( $key, $value=1 )  // -- установить значение ключу
  { $this->domain[$key] = $value; }

  function SetRef( $key, &$ref )  // -- установить значение ссылкой
  { $this->domain[$key] = &$ref; }

  function Append( $key, $value ) // -- дописать в конец
  { $this->domain[$key] .= $value; }

  function Is( $key ) // -- true, если этот ключ хоть что-то значил
  { return isset( $this->domain[$key] ); }

  function Free( $key="", $_starred=false ) // -- очистка домена или unset переменной
  { if ($key === "") $this->domain = array();
    else if( is_array($key) )
    {
      foreach($key as $k)
      unset( $this->domain[$k] );
      if ($_starred) unset( $this->domain['*'.$k] );
    } else unset( $this->domain[$key] );
  }

  function Load( $domain, $_starred=false ) // -- загрузка массива в ключи
  {
    foreach($domain as $k=>$v) {
       $this->Set( $k, $v );
       if ($_starred) $this->Set( '*'.$k, $v );
    }
  }

/*========================== /RocketTE ====================================*/

  function TplPlugin($plugin) {
    // environment aliases :
    $guide = &$this->guide;
    $db    = &$guide->db;
    $unit =  &$guide->unit;
    $tpl   = &$this;

    $_file = $this->cfg->proj_plugn_dir.$plugin.".php";// echo 'TplPlugin():'.$_file;
    ob_start();
      $result = include ($_file);
      if ($result===false) trigger_error("Problems (file: ".__FILE__.", line: ".__LINE__."): ".ob_get_contents());
      if (($result===NULL) || $result) $result = ob_get_contents();
    ob_end_clean();
    return $result;
  }

  function TplRead( $tpl ) {
    /* last_file, the last processed filename, used when requested something like that:   [ ':mark' | '.:mark' ]  */
    static $last_file;
    // return if cached
    if ( isset($this->recent_cache[$tpl]) )
      return $this->recent_cache[$tpl];

    if ( strpos(' '.$tpl, ':')) {
      $t = explode(':', trim($tpl));
      $file = $t[0]; $mark = $t[1];
      if ($file==='' || $file == '.') $file = $last_file;
      // return if cached
            if ( isset($this->recent_cache[$file.':'.$mark]) )
              return $this->recent_cache[$file.':'.$mark];
            $f_tpl = $file;

            if (!$fp = fopen($f_tpl, 'r', $incpath=1))
              { trigger_error(BR.'Cannot  open file ('.$f_tpl.') at '.__FILE__.':'.__LINE__);  return false; }
            $last_file = $file; // remember last succesfully read template
            $ptrn =   // preg_quote
                ( $this->cfg->tpl_tag_open.$this->cfg->tpl_mark_open  .$mark. $this->cfg->tpl_tag_close ).
                          '(.*)'. // preg_quote
                ( $this->cfg->tpl_tag_open.$this->cfg->tpl_mark_close .$mark. $this->cfg->tpl_tag_close );

            while ($fdata = fread ($fp, 4096))  $fbody .= $fdata;     fclose($fp);

            $a = preg_match('{'.$ptrn.'}ims', $fbody, $matches ); // 'ims' without 'u' for PCRE v4.5 and later
            $content = $matches[1];   // echo 'html:: ['.htmlspecialchars($content).']'. BR.BR;

      // to cache
      if (!isset($this->recent_cache[$file.':'.$mark]))
        $this->recent_cache[$file.':'.$mark] = $content;

    } else {
      $f_tpl = $tpl;
      if (!$fp = fopen($f_tpl, 'r', $incpath=1))
        { trigger_error(BR.'Cannot  open file ('.$f_tpl.') at '.__FILE__.':'.__LINE__); return false; }
      $last_file = $tpl;
      while ($fdata = fread ($fp, 4096))  $content .= $fdata;     fclose($fp);

      // to cache
      if ( !isset($this->recent_cache[$tpl]) )
        $this->recent_cache[$tpl] = $content;
    }

    return $content;
  }

  function Parse( $tpl, $store_to="", $append=0 ) {    // checkout!
    if (!is_array($this->domain)) { trigger_error(BR.'Tpl->domain is not valid: '.var_export($this->domain,1)); }
    if ($this->cfg->tpl_markup) { $_aa="<!-- ".$tpl." : -->";$_zz="<!-- / ".$tpl." -->\r\n"; }
    else                        { $_aa=''; $_zz=''; } // tpl markup
    $content = $_aa . $this->_Preparse( $this->TplRead( $tpl ) ) . $_zz; // echo HR.$content.HR;
    ob_start(); //  'Go, go, go ,go!'
      $evalcode = "?>" . $content. "<?php ";
      // echo $evalcode;
      eval( $evalcode );
      $html = ob_get_contents();
    ob_end_clean();
    if ($store_to) {
      if ($append) $this->Append ($store_to, $html);
      else         $this->Set($store_to, $html);
    }
    return $html;
  }

  function _Preparse($content) {
    $content = str_replace( "<?", "<<?php ; ?>?", $content );

    $content = preg_replace('|<\?xml[^>]*\?>|ims', '<?php echo "\\0"; ?>', $content);
    // плагины
    $ptrn_plug =   // preg_quote
              ( $this->cfg->tpl_tag_open.$this->cfg->tpl_plugin ).
                  '(.*)'. // preg_quote
              ( $this->cfg->tpl_tag_close ); //     echo "ptrn : [".$ptrn.']'.BR ;
    $content = preg_replace('{'.$ptrn_plug.'}e', "\$this->TplPlugin('\\1');", $content);

    /* inner templates : */
    $ptrn_inner =   // preg_quote
        ( $this->cfg->tpl_tag_open.$this->cfg->tpl_mark_open  .'(.*?)'. $this->cfg->tpl_tag_close ).
                  '(.*)'. // preg_quote
        ( $this->cfg->tpl_tag_open.$this->cfg->tpl_mark_close . "\\1" . $this->cfg->tpl_tag_close );
    $content = preg_replace('{'.$ptrn_inner.'}ims',
                  $this->cfg->tpl_tag_open.$this->cfg->tpl_include.":\\1".$this->cfg->tpl_tag_close, $content);
    /* : inner templates */

    // инклюды. полный: {{@tplname.html:Mark}}; фрагмент темплэйта из текущего файла: {{@.:Mark}}, {{@:Mark}}
    $ptrn_inc =   // preg_quote
              ( $this->cfg->tpl_tag_open.$this->cfg->tpl_include ).
                  '(.*?)'. // preg_quote
              ( $this->cfg->tpl_tag_close ); //     echo "ptrn : [".$ptrn.']'.BR ;
    $content = preg_replace('{'.$ptrn_inc.'}e', "\$this->_Preparse(\$this->TplRead('\\1'));", $content);

    // typo correcting {{?/}} => {{/?}}
    $content =  str_replace( $this->cfg->tpl_tag_open.$this->cfg->tpl_construct_ifend.
                              $this->cfg->tpl_tag_close,
                              $this->cfg->tpl_tag_open."/".$this->cfg->tpl_construct_if.
                              $this->cfg->tpl_tag_close,
                              $content );
    // die(' %%% '.$this->cfg->tpl_construct_if.' * '.$this->cfg->tpl_construct_ifelse. ' * '. $this->cfg->tpl_construct_ifend );
    /*
    $content = str_replace( $this->cfg->tpl_tag_open."/".$this->cfg->tpl_construct_if.$this->cfg->tpl_tag_close, '<?php } ?>', $content );
    $content = str_replace( $this->cfg->tpl_tag_open.$this->cfg->tpl_construct_ifelse.$this->cfg->tpl_tag_close, '<?php } else { ?>', $content );

    $ptrn_if = $this->cfg->tpl_tag_open.preg_quote($this->cfg->tpl_construct_if).'([^'.$this->cfg->tpl_tag_close{0}.']*?)'.$this->cfg->tpl_tag_close;
    */
    /*
        $content = preg_replace( '/'.$ptrn_if.'/ims', '<?php [[ << '.$ptrn_if.' >> : \\0 ]] if ( '.$this->domain["\\1"].' ) { ?>', $content );
    */
    $pregm0=preg_match('/'.$ptrn_if.'/ims', $c0=$content );
    $content = preg_replace( '/'.$ptrn_if.'/ims', '<?php if ( $this->domain["\\1"] ) { ?>', $content );
    // if ( $pregm0 )  UtilityCore::dbgMsg( 'if-- : ', $c0.BR.'>>'.BR.$content, $prewrap=0, $htmlquot=1 );
    // $if_start = $this->cfg->tpl_tag_open.$this->cfg->tpl_construct_if;  echo ' %%% $ptrn_if : [[ '.$ptrn_if.' ]] ';

    // переменные
    $content = str_replace( $this->cfg->tpl_tag_open,  '<?php echo $this->domain["', $content );
    $content = str_replace( $this->cfg->tpl_tag_close, '"];?>', $content );
    return $content;
  }

  // ultimately recommended for parsing recordset items
  function ParseOne($data, $tpl, $store_to="", $append=0 ) {
    if (!array($data) || empty($data))  return false;
    $this->Load ($data, $_starred=true);
    $r = $this->Parse($tpl, $store_to, $append);
    $this->Free ( array_keys($data), $_starred=true );

    return $r;
  }

  function Loop ( $data, $tpf, $store_to="", $append=0, $implode=false, $wrap_empty=true ) {
    // empty case
    if (!$data || !count($data)) {
      if ($wrap_empty)  {
          $this->Set('_', $this->Parse($tpf.$this->cfg->loop_empty));
          $r = $this->Parse($tpf, $store_to, $append);
          $this->Free('_');
      }   else   {  $r = $this->Parse($tpf.$this->cfg->loop_empty, $store_to, $append);    }
      return $r;
    }

    // loop
    foreach ($data as $k=>$v) {
      $_mark = $this->cfg->loop_item; // '_Item'
      if ($v[$this->cfg->loop_curr])  // выделить текущий - добавляем в data[элемент]['_Curr'] = 1;
        $_mark = $this->cfg->loop_curr; // '_Curr'
      $_list[] = $this->ParseOne( $v, $tpf.$_mark );
    }

    // implode
    if ($implode)  $delim = $this->Parse($tpf.$this->cfg->loop_delim);
    else           $delim = ''; // !!разделитель парсится (как xml.#PCDATA), притом один раз, и сразу кэшируется!!

    $this->Set('_', implode( $delim, $_list ));
    $r = $this->Parse($tpf, $store_to, $append);
    $this->Free('_');
    return $r;

  }



} // EOC { ThemePlayTE }

?>