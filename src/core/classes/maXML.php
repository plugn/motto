<?php

class maXML {

/*
 * @description XML/XHTML Parse&Render Library (static class)
 * @copyright Max L. Dolgov
 *
**/


  function arrayClear($a) {
    foreach ( $a as $v) {     // keys existence should be ignored
      if ( is_array($v))                  return arrayClear( $v);
      elseif ( !empty($v) || $v==='0' )   return false;
    }
    return true;
  }

  function Xmlize($html) {
    // '<tag ></tag>' - is invalid markup for unpaired elements - cannot be processed as well
    $html = preg_replace('/<(br|nobr|hr|img|link|meta|col|input)([^>\/]*?)>/msiSA', "<\${1}\${2}/>", $html);
    $rgxpZ = "{<(\w+)([^>]*)/>}ism";
    $rgxpX = "<\${1}\${2}></\${1}>";
    $xhtml = preg_replace($rgxpZ, $rgxpX, $html);

    return $xhtml;
  }

  function CleanUpHtml($txt){
    // $txt = preg_replace ('/(\s)+/', '${1}', $txt);  // не трогаем отступы, удобно для чтения восстановленного кода
    $txt = preg_replace ('/<\?xml[^>]*\?>/i', '', $txt);
    $txt = preg_replace ('/<!doctype[^>]*?>/i', '', $txt);
    return $txt;
  }

  function codeParse( $html ) {
    // $rgxp =      "/^(.*?)(<([\w]+)([^>]*)>)(.*?)(<\/\\3>)(.*)$/msiSA";
    $rgxps['tag_opn'] = "<([\w]+)([^>]*)>";
    $rgxps['tag_cls'] = "<\/[\w]+>";
    $rgxps['tag_any'] = "<\/?([\w]+)>";
    // паттерн для вложенных структур
    $rgxps['nest'] = "/^(.*?)(<([\w]+)([^>]*)>)(.*)(<\/\\3>)(.*?)$/msiSA";
    // паттерн для последовательных структур
    $rgxps['sibl'] = "/^(.*?)(<([\w]+)([^>]*)>)(.*?)(<\/\\3>)(.*)$/msiSA";

    $pmode = 'sibl';
    preg_match_all( $rgxps[$pmode], $html, $matches, PREG_SET_ORDER );
    $tag = $matches[0][3];
    $sibl_fail = strpos(' '.$matches[0][5],'<'.$tag) && !strpos(' '.$matches[0][5],'</'.$matches[0][3].'>');

  // $fp = fopen( './log3.html' ,"a+");
    if ($sibl_fail) {
      $pmode = 'xnest';
      $rgxps['xnest'] = "/^(.*?)(<(\/?)".$tag."[^>]*>)(.*)$/msiSA";
      $fcontent = $html;
      $flevel = 0;
      do {

  // fputs( $fp,"\r\n".' preg_match_all( '.$rgxps['xnest'].', fcontent, matches2, PREG_SET_ORDER );'."\r\n" );
  // fputs( $fp,       'fcontent : '.var_export($fcontent,1)."\r\n" );

        preg_match_all( $rgxps['xnest'], $fcontent, $matches2, PREG_SET_ORDER );
        if (empty($matches2[0][3]) ) {
          $ftag = 'open';
          $flevel++;
        } else {
          $ftag = 'closed';
          $flevel--;
        }
        $fdata .=   $matches2[0][1].$matches2[0][2];
        $fcontent = $matches2[0][4]?$matches2[0][4]:'';
        $fi++;
      } while ( !($flevel==0 && $ftag=='closed')  && $fcontent!=''   );

  // fclose($fp);

      $pmode = 'znest';  //  echo ' cycle done. iterations:'.$fi.'<br /> $flevel='.$flevel.' ftag='.$ftag;
      $rgxps['znest'] =
      "/^(.*?)(<".$tag."([^>]*?)>)(.*?)((<\/?".$tag."[^>]*>.*?){".(1*$fi-2)."})(<\/".$tag.">)(.*)$/msiSA";
      preg_match_all( $rgxps['znest'], $html, $m5, PREG_SET_ORDER );
      $m = $m5;
      $m[0][5] = $m5[0][4].$m5[0][5];
      $m[0][4] = $m5[0][3];

      $m[0][3] = $tag;
      $m[0][6] = $m5[0][7];
      $m[0][7] = $m5[0][8];
      unset($m[0][8]);
      $matches = $m;
    }
  /* echo '<br/>'.htmlspecialchars('{'.$pmode.'}'.$rgxps[$pmode].($m5?' m5':' matches').' ::  [['. var_export($matches[0] ,1) ).']]<hr />'; */

    return $matches[0];
  }

  function parseAttrs($attrs) {
    if (empty($attrs)) return false;

    $a = trim($attrs, " \r\n\t\x0B");
    $a = str_replace("\r\n", " ", $a);
    $a = str_replace("\n", " ", $a);
    $a = str_replace("\t", " ", $a);
    $a = preg_replace('/(\s)+/', '${1}', $a); // minimize inner spaces to one char
    preg_match_all("|\s?([-:\w]+)(\s?)=(\s?)[\"']{1}([-+=:;\./\w\d\s/]*?)[\"']{1}|ims", $a, $c,  PREG_SET_ORDER);
    foreach($c as $v) $r[$v[1]]=$v[4];
    return $r;
  }

  function &createTextNode( &$context, $value ) {
    $txtnode->type     = 'textnode';
    $txtnode->parent   = &$context; // !! UNCOMMENT THIS LINE BEFORE DOM-OBJECT REAL USAGE !! **ugly for debug**
    $txtnode->value    = $value;
    return $txtnode;
  }

  function &spawnNode( &$context, $cddta ) {
    static $xc; if (++$xc > 9999 ) return; // antiloop
    //  if ($xc==2) { echo 'context node first: ';var_dump($context); die(); }
    if (!empty($cddta[1])) {
      $context->children[] = &maXML::createTextNode($context, $cddta[1] );
    }

    // $node->parent      = &$context;
    $node->type        = 'element';
    $node->name        = $cddta[3];
    $node->attr_string = $cddta[4];
    $node->attributes  = maXML::parseAttrs($node->attr_string); // +TODO+
    $_node_content     = $cddta[5];
    // $node->content     = $cddta[5];

    $_ccdta            = maXML::codeParse($_node_content);
    if (!is_null($_ccdta))            {  $_node_children  =      &maXML::spawnNode( $node, $_ccdta);       }
    elseif (!empty($_node_content))   {  $_node_children  = &maXML::createTextNode( $node, $_node_content);
                                         $node->children[]    = &$_node_children;                   }

    $context->children[] = &$node;

    $_ccdta            = maXML::codeParse($cddta[7]);
    if (!is_null($_ccdta))       {  $_node_children  =      &maXML::spawnNode( $context, $_ccdta);   }
    elseif (!empty($cddta[7]))   {  $_node_children  = &maXML::createTextNode( $context, $cddta[7]);
                                    $context->children[]      =  &$_node_children;            }
    return $node;
  }

  function DOMNodeView  ( $dom_obj, $indent) { // customize your own style here
    $t = str_repeat('&nbsp; &nbsp; &nbsp; ', $indent).'['.var_export($dom_obj->type,1).'].';
    if ($dom_obj->type=='textnode')  $t.= 'value:  <em>'.var_export(htmlspecialchars($dom_obj->value),1).'</em><br />';
    else                             $t.= 'name:  <b>'.var_export($dom_obj->name,1).'</b><br />';
    return $t;
  }

  function DOMTraverse(&$dom_obj, $indent=0, $callback='DOMNodeView')  {
    $t = maXML::DOMNodeView( $dom_obj, $indent);
    // $t = call_user_func_array (array( 'maXML', $callback ), array($dom_obj, $indent));
    if (isset($dom_obj->children))   {
      foreach ($dom_obj->children as $k=> $v) {
        $t.= maXML::DOMTraverse($v, ++$indent);
        --$indent;
      }
    }
    return $t;
  }

  function DOMNodeView2 ( $dom_obj, $indent=0, &$tpl) { // customize your own style here

    $t = str_repeat('&nbsp; &nbsp; &nbsp; ', $indent);
    $tpl->Set('nodetype', $dom_obj->type,1);
    if ($dom_obj->type=='textnode') {
        $t.= $tpl->ParseOne(array('value'=>var_export($dom_obj->value,1)), 'hovertpl.html:DOM_TextNode');

    } else {
       if (empty($dom_obj->name)) return;

       if (is_array($dom_obj->attributes)) {
          foreach($dom_obj->attributes as $k=>$v)
            $attrs .= $tpl->ParseOne(array('attr_name'=>$k, 'attr_value'=>$v), 'hovertpl.html:DOM_AttrSet_Item');

        //if ($attrs)
                $dmn = array('attr_div_id'=> "__id_".UtilityCore::randstr(4), 'element_name'=>$dom_obj->name,
                             'attr_div_content'=>$attrs);
                $t.= $tpl->ParseOne($dmn, 'hovertpl.html:DOM_ElementAttrs');
       } else {
                $dmn = array('element_name'=>$dom_obj->name);
                $t.= $tpl->ParseOne($dmn, 'hovertpl.html:DOM_Element' );
       }
    }
    return $t;
  }

  function DOMTraverse2(&$dom_obj, $indent=0, &$tpl)  {
    $t = maXML::DOMNodeView2( $dom_obj, $indent, $tpl);
    if (isset($dom_obj->children))   {
      foreach ($dom_obj->children as $k=> $v) {
        $t.= maXML::DOMTraverse2($v, ++$indent, $tpl);
        --$indent;
      }
    }
    return $t;
  }

  function DOMRenderSource( &$dom_obj ) {
    $tags_unpaired = array('br','nobr','hr','img','link','meta','col','input');
    // build attributes
    if ($dom_obj->type=="element") {
    // build attributes
      if (is_array($dom_obj->attributes)){
        foreach ($dom_obj->attributes as $k=>$v)
          $attrs[]=$k.'="'.$v.'"';
        $attr_string = implode(" ", $attrs);
        // $dom_obj->attr_string = $attr_string;
      }
      // build tag
      if( in_array($dom_obj->name, $tags_unpaired)) {
        $r .= "<".$dom_obj->name.($attr_string?" ".$attr_string:"")."/>";
      } else {
        $r .= $dom_obj->name? "<".$dom_obj->name.($attr_string?" ".$attr_string:"").">" : '';
        if ($dom_obj->children)
          foreach ($dom_obj->children as $child)
            $r .= maXML::DOMRenderSource( $child );
        $r .= $dom_obj->name? "</".$dom_obj->name.">" : '';
      }
    } else {
      $r = $dom_obj->value;
    }

    return $r;
  }

  function &DOMGetElementByTagname( &$dom_obj, $tag, &$sum ) {
    if ($dom_obj->name==$tag) {
      echo ' ++'.$dom_obj->name.':'.$dom_obj->children.'++ ' ;
      $sum[] = &$dom_obj;
      // return $dom_obj;
    }

    if (is_array($dom_obj->children) && !empty($dom_obj->children)) {
      while (list($k, $child) = each($dom_obj->children))    {
      /* for ($i==0; $i<count($dom_obj->children); $i++)         */
        $sum = &maXML::DOMGetElementByTagname( $child, $tag, $sum );
        /* $sum = maXML::DOMGetElementByTagname( $dom_obj->children[$i], $tag, $sum ); */

      }
    }
    return $sum;
  }

  function &DOMGetElementById( &$dom_obj, $id, &$sum ) {
    if ( isset($dom_obj->attributes['id']) && $dom_obj->attributes['id']==$id )
      $sum[] = &$dom_obj;
    if ($dom_obj->children) {
    foreach ($dom_obj->children as $child)
        $r = maXML::DOMGetElementById( $child, $tag, $sum );
      if (is_array($r)) $sum = $r;
    }
    return $sum;
  }

} // EOC { maXML }

  /*
  $html  = file_get_contents('./../../_tmp/webno.html');//rssfeed.xml');
  $xHtml = maXML::Xmlize( maXML::CleanUpHtml($html)); // echo htmlspecialchars( var_export($xHtml ,1) ).'<br />';die();
  $cddta = maXML::codeParse($xHtml);
  $doc->type="element";
  $obj   = maXML::spawnNode($doc, $cddta);

  // echo '<pre>'.htmlspecialchars( var_export($doc ,1) ).'</pre>';
  $doc->children[1]->children[0]->children[1]->attributes["content"]="text/xml; charset=windows-1251" ;

  // echo DOMTraverse($doc);
  echo maXML::DOMRenderSource($doc); // echo htmlspecialchars(maXML::DOMRenderSource($doc));
  */




?>