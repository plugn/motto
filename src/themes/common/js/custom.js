/*
 * @description  custom project page handlers
 * @author       Max L. Dolgov      <bananafishbone at gmail dot com>
 *
 */
function cssClass(id, whichClass)
{  document.getElementById(id).className = whichClass; }

function popup(url, name, width, height, left, top) {
  p = window.open(url, name, opts='dependent=yes,innerWidth='+width+',innerHeight='+height+',left='+left+
                              ',top='+top+',screenx='+left+',screeny='+top+',width='+width+',height='+height+
                              ',outerHeight='+(height+2)+',outerWidth='+(width+2)+',titlebar=yes,scrollbars=yes,resizable=yes'); // alert(opts);
  return p;
}

function wgtEnter(el) {
  if (parseInt(el.value)!=el.value) {
      el.style.color='white';
      el.style.backgroundColor='red';
      return false;
  }

  el.style.color='black';
  el.style.backgroundColor='white';
  return true;
}

function wgtCommit(el,isValid) {  // стандартное обращение к AJAX-классу без callback
    if (isValid)        {
      (new mAjax()).send(  // RPC
            {   prmkey:el.id,
                prmval:el.value,
                URI : 'http://'+location.host+"/ui.server/widget_rpc/",
                pgsBarId: "pgsbar",
                rspBarId: "cartSpySum"
            }           );
    }
}

function chkToggle ( id ) {  document.getElementById(id).click();  }

function elSwitch( node ) {
  var el = getNode( node ); // el=document.getElementById(id); // alert(el.style.toString());
  // try
  { if (!el.style.display || el.style.display=='block') el.style.display='none';
    else if (el.style.display=='none') el.style.display='block';
  } // catch(e) { alert('exception:a2 '+e.toString()); }
}

function nvcMode( mode ) {
    if (mode=='manual') {
        if (!document.opts_manual) {
          tbl.AddRow();
          document.opts_manual=1;
          strLd = document.getElementById('ctrlStoreLoad');
          strLd.onclick = function() { return false; };
        }
        elFtRow = document.getElementById('nvcFootRow');
        elAttache( 'input', elFtRow, { 'name':'_f_event', 'type':'hidden', 'value':'insert' }, 'After' );
    }
}

/*** tec route : rpc-based UI constructor. ***/
function tecCB1 ( respTxt, cfg ) { /*** callback. it will be called from mAjax object when response complete ***/
    RspId = cfg.RspId;    // data_id
    document.getElementById(RspId).innerHTML = respTxt;
    // eval ( 'document.phaseof_'+cfg.params.data_id+'+=1;' );eval('alert(document.phaseof_'+cfg.params.data_id+');');
}

function tecExec( data_id, RspId, alias, prm ) {
    (new mAjax()).send(      // pay attention: this mAjax source differs with one used higher
            {   prm       : prm,
                URI       : 'http://'+location.host+'/ui.server/tecrpc/'+alias+'/'+data_id,
                RspId     : RspId,
                pgsBarId  : 'pgsbar',
                rspCallBk : 'tecCB1'
            } );
}

function node2string( obj ) {
    var r1, r2;    r2  = '{\n';    r2 += ( 'obj2string('+obj+') : '+obj.length+'; name: '+obj.name+'; attrs: ' + obj.attributes );
    for ( var i in obj.attributes ) { r1 = i + ' : '+obj.attributes[i]+'\n'; r2 += r1; } r2 += '}\n';  return r2;
}
function obj2string( obj ) { /* i am dirty obfuscator, yeee */
    var r1, r2; r2  = '{\r\n';
    for ( var i in obj ) { r1 = i + ' : '+obj[i]+',\r\n'; r2 += r1; }    r2 += '}\r\n';    return r2;
}

/* attention */
Object.prototype.toString = function()  {  return  obj2string( this );  }
Array.prototype.toString  = function()  {  return  obj2string( this );  }

/* Navigation Assistance with Cookies */
var cookies = new Object(); // associative array indexed as cookies["name"] = "value"
function extractCookies()
{
   var name, value;
   var beginning, middle, end;
   for (name in cookies)
   { // if there are any entries currently, get rid of them
     cookies = new Object();
     break;
   }
   beginning = 0;  // start at beginning of cookie string
   while (beginning < document.cookie.length)
   {
     middle = document.cookie.indexOf('=', beginning);  // find next =
     end = document.cookie.indexOf(';', beginning);  // find next ;

     if (end == -1)  // if no semicolon exists, it's the last cookie
       end = document.cookie.length;
     if ( (middle > end) || (middle == -1) )
      { // if the cookie has no value...
        name = document.cookie.substring(beginning, end);
        value = "";
      }
      else
      { // extract its value
        name = document.cookie.substring(beginning, middle);
        value = document.cookie.substring(middle + 1, end);
      }
      cookies[name] = unescape(value);  // add it to the associative array
      beginning = end + 2;  // step over space to beginning of next cookie
   }
}
/* pop-up routines  */
function pop_opnrMemDealer( dl ) { // popup opener's UI hard tweaks
    var op = window.opener.document;
    var popbg = op.getElementById('_f_popbg');
    popbg.setAttribute('onclick',null);
    popbg.blur();

    var ctg = op.getElementById('_f_nvc_ctg');
    ctg.setAttribute('disabled','disabled');

    var byr = op.getElementById('_f_nvc_buyer');
    byr.value = dl.options[dl.selectedIndex].text;

    var tCfg = { 'type':'hidden', 'name':'_fnvc_invc_dealer_id', 'value':dl.value };
    var el=op.createElement('input');
    for ( var prop in tCfg  )
    {   el.setAttribute(prop, tCfg[prop]);   }
    byr.parentNode.insertBefore( el, byr );

}

function pop_opnrStoreLoad( form ) {
  var op = window.opener.document;
  var r0 = '';
  var r1=[]; /* checked ids : */
  for (var f = 0; f < form.elements.length; f++)
  {   var el = form.elements[f];
      r0 += ' f:'+f+'='+el+'; type: '+el.getAttribute('type')+'; name: '+el.getAttribute('name')+
            '; value: '+el.value+'; checked:'+el.checked+';\n'; /* */
      if ( el.getAttribute('type')=='checkbox' && el.checked )
          r1[r1.length]=el.value;
  }

  var tbl = window.opener.tbl;
  for ( var rrow = 0; rrow < r1.length; rrow++ )
  {
    var cnt=0, cntR=0, msg ='';  //   alert(rrow+';;;; '+'reserve_'+r1[rrow]+'\n');
    var lstCheck = document.getElementById('reserve_'+r1[rrow]);
    var lstTr = lstCheck.parentNode.parentNode;
    var inputText = tbl.AddRow(lstCheck.value);
    while ( cnt < (tbl.ffields.length-1) ) {
      cntR++; // real index counter
      if ( lstTr.childNodes[cntR-1].nodeType==1 ) {
        cnt++; // real nodes counter, not whitespaces (NS fix)
        msg += tbl.ffields[cnt-1]+'=' + lstTr.childNodes[cntR-1].nodeName + lstTr.childNodes[cntR-1].innerHTML+'\n';
        inputText[cnt-1].value = inputText[cnt-1].text = lstTr.childNodes[cntR-1].innerHTML;
      }
    }
    var tmp = inputText[1].value; inputText[1].value = inputText[2].value;  inputText[2].value = tmp; // alert( msg );
  }


  var byr = op.getElementById('_f_nvc_buyer');
  var cfg = { 'type':'hidden', 'name':'_f_event', 'value':'update' };
  var el  = op.createElement('input');  // elAttache( 'input', byr, { 'name':'_f_event', 'type':'hidden', 'value':'update'},'Append' );
  for ( var prop in cfg  ) {   el.setAttribute(prop, cfg[prop]);   }
  byr.parentNode.appendChild( el );

  el4=window.opener.document.getElementById('nvc_wrap4');
  el4.style.display='block';
  window.close();

}

/* simple table routines */
function tblCtrl( eid ) {
    this.table = document.getElementById( eid );
}

tblCtrl.prototype.AddRow = function( rowNum ) {      // NS counts also empty whitespaces, IE - only nodes

    var rowsLen = this.table.tBodies[0].rows.length;    // cellNum = this.table.rows[rowsLen-1].childNodes.length; */
    if ( rowNum==null  ) rowNum = rowsLen;
    cells = [];
    for (var k in this.table.rows[rowsLen].childNodes ) {
        childNode = this.table.rows[rowsLen].childNodes[k];
        if (childNode.nodeType==1)
          cells[cells.length] = k+'='+childNode.nodeName +' : '+childNode.innerHTML+';\r\n';
    }  // alert( ' tr['+(rowsLen-1).toString()+ '] cells : ' + cells.length  );

    var tr = this.table.insertRow( rowsLen ); // tbl.tBodies[0].rows.length
    tr.setAttribute('class', 'tbl_row');
    var cnt=0, rwc=0, td=[], inputText=[];
    while ( cnt < this.ffields.length ) {  cnt++;
      td[cnt-1]        = elAttache('td',    tr, {'id':'tbe_R'+rowsLen+'_C'+cnt}, 'Within' );
      inputText[cnt-1] = elAttache('input', td[cnt-1],
                  {'id':'tbi_R'+rowsLen+'_C'+cnt, 'name':'_fdata_'+this.ffields[cnt-1]+'['+rowNum+']', 'type':'text'},
                  'Within' );
    }

    return inputText;
}

function getNode( node ) {
    if (node.nodeType!=1) { // keyNode : node or nodeId
      try  {  node = document.getElementById(node); }
      catch (e) { alert( 'keyNode Id :'+keyNode.nodeName + ' keyNode.id :'+keyNode.getAttribute('id') );
          try    {   node = document.getElementsByName(node)[0]; alert('###'+node);      }
          catch(e2) { alert('Exception. NAME-attr of keynode "'+node+'" unknown;'+e); }
      }

    }
    return node;
}

function removeNode( el )
{
  var el = getNode(el);
  while (el.hasChildNodes()) el.removeChild(el.lastChild);
  if (el)  {
    var newText = document.createTextNode('');
    el.parentNode.replaceChild(newText, el);
  }
}


function elAttache( nodeName, keyNode, cfg, insertMode ) {
    var keyNode = getNode( keyNode );
    var el=document.createElement( nodeName );   // out of document scope

    el = elSetup( el, cfg );

    switch ( insertMode ) {
      case 'Before':  keyNode.parentNode.insertBefore( el, keyNode );    break;
      case 'Within':  keyNode.appendChild( el );                         break;
      case 'Append':  keyNode.parentNode.appendChild( el );              break;
      case 'After' :
        if (keyNode.nextSibling) { keyNode.parentNode.insertBefore(el, keyNode.nextSibling); }
        else                     { keyNode.parentNode.appendChild( el ); }
        break;
    }    // alert( 'inserted under parent: '+el.parentNode.nodeName );

    return el;
}

function elSetup( node, cfg ) {
    var el = getNode( node );
    for ( var prop in cfg )    {
      if ( prop.substr(0,2)=='on') {
          var exp = 'el.'+prop+'=function() { ' + cfg[prop] + ' } ';
          eval(exp)
      } else {  el.setAttribute(prop, cfg[prop]);  }
    }
    return el;
}

function showHalls( data_id, RspId, alias, prm ) {
    (new mAjax()).send(      // pay attention: this mAjax source differs with one used higher
            {   prm       : prm,
                URI       : 'http://'+location.host+'/afisha/kino/admin/ht.rpc/'+alias+'/'+data_id,
                RspId     : RspId,
                pgsBarId  : 'pgsbar',
                rspCallBk : 'showCB3'
            } );
}

function showCB3 ( respTxt, cfg ) { /*** callback. it will be called from mAjax object when response complete ***/
    document.getElementById(cfg.RspId).innerHTML = respTxt;    // data_id
}

function fpicPopulate( keyNode ) {// alert(keyNode);
    var idx = document.fpicCnt;
    var kyId = keyNode.getAttribute('id');
    var tpl = '_fdata_newpic';

    var delta = ++idx - parseInt(kyId.substr(tpl.length));  //   alert('document.fpicCnt:'+document.fpicCnt+'\n kyId:'+kyId+'\n delta: '+delta);
    if ( delta > 1) { return false; } // alert('population canceled.'); alert('populating:'+idx);
    br        = elAttache('br',    keyNode, {}, 'After' );
    inputFile = elAttache('input', br,
                {'id':tpl+idx, 'name':tpl+idx, 'type':'file', 'onchange':'fpicPopulate(this)' },
                'After' );
    document.fpicCnt = idx;

}

// TODO unbind and customize document.states for multi-task handling
function cssToggle( node, states ) {  // init :
  var node = getNode ( node );  // anyhow! alert( 'states: '+states );
  if ( states.length ) { // init for first time:
    if ( document.states===null || document.states===undefined ) {
        var currState = states.shift(); states.push( currState );
        document.states = states;      // alert( 'states: '+document.states );
    }
    // routine :
    var newState = document.states.shift();
    document.states.push( newState );  // alert( 'newState: '+newState );alert( 'states: '+states );
    node.className = newState;
  }
}

function timePopulate( keyNode, locks ) { // alert(keyNode);
    // if ( !validateTime(keyNode, ['btnTimeAdd']) ) return false;
    if ( !validateTime(keyNode, locks) ) return false;
    var idx = document.timeCnt;
    var kyId = keyNode.getAttribute('id');
    var tpl = '_add_start_time'; // '_add_newtime';
    var delta = ++idx - parseInt(kyId.substr(tpl.length));
    // alert(kyId+'.substr('+tpl.length+'):'+kyId.substr(tpl.length)+'\n document.timeCnt:'+document.timeCnt+'\n kyId:'+kyId+'\n delta: '+delta);

    if ( delta > 1) { return false; } // alert('population canceled.'); alert('populating:'+idx);
    br        = elAttache('br',    keyNode, {}, 'After' );
    input = elAttache('input', br,
                {'id':tpl+idx, 'name':tpl+'['+idx+']', 'value':'--:--','type':'text', 'onchange':'timePopulate(this,[\'btnTimeAdd\'])',  'maxlength':5, 'size':5 },
                'After' );
    document.timeCnt = idx;
    input.focus();
}


function unlockNodes ( nodes ) {
    for ( var i=0; i<nodes.length; i++ ) {
      var node = getNode( nodes[i] ); //  alert('unlock node : '+nodes[i]+';'+node );
      node.setAttribute('disabled',null);
      node.removeAttribute('disabled');
    }
}

function lockNodes ( nodes ) {
    for ( var i=0; i<nodes.length; i++ ) {
      var node = getNode( nodes[i] );      // alert('lock node : '+nodes[i]+';'+node );
      node.setAttribute('disabled','disabled');
    }
}

function arraySum (a) {
  var r=0;
  for ( var i in a ) r += a[i]; //  alert ('array:'+a+'\n sum: '+r);
  return r;
}

function validateTime ( node, locks ) {
  if (locks==undefined)  { alert ('achtung'); var locks = ['btnTimeEdit']; }  // alert('locks : ' +locks);
  var el = getNode( node );
  var reasons='';
  if ( document.errors==undefined ) { document.errors = new Array; }
  if ( document.errors.vt1==undefined ) { document.errors.vt1 = new Array; }
  if ( document.errors.vt1[node.id]==undefined || document.errors.vt1[node.id]==null ) document.errors.vt1[node.id] = 0;
  // alert ('node.id : ' + node.id+'\n document.errors['+node.id+'] : '+document.errors.vt1[node.id]);
  var errMsg = isTime(el.value);
  if (errMsg.length) {
    for (var i in errMsg ) { reasons += errMsg[i]+'\n'; }
    alert('    ¬рем€ указано некорректно:\n'+ reasons );
    el.style.color='white';
    el.style.backgroundColor='red';
    document.errors.vt1[node.id]=1; if (document.errors.vt1[node.id] == 1 ) lockNodes( locks );
    return false;
  } else   {
    el.style.color='black';
    el.style.backgroundColor='white';
    if (document.errors.vt1[node.id] > 0 ) { document.errors.vt1[node.id]=0;
                            if ( arraySum(document.errors.vt1) < 1 )  unlockNodes( locks );
    }
    return true;
  }
}


function isTime ( what ) {
  var length_default = 5;  if ( what.length!=5 ) alert( 'time format unsupported yet : ['+what+']' );
  var parts, t = new Array(), fail = false;
  var errMsg = new Array();
  for ( var i=0; i<5; i++ ) {
    t[i] = what.slice(i,i+1);
    if (t[i]!=parseInt(t[i]) && i!=2 ) { errMsg[errMsg.length]= t[i]+'- не цифра'; fail=true; }
  }
  if ( t[2]!= ':' ) errMsg[errMsg.length] = ' пропущен разделитель ":" ';
  if (   t[0]>2 || t[3]>5 || (t[0]==2 && t[1]>3) )
  {
    fail=true;
    errMsg[errMsg.length] = ''+t[0]+'>2 || '+t[3]+'>5 || ('+t[0]+'==2 && '+t[1]+'>3)';
  }
  return errMsg;
}

function  cnmTRowAdd( wrapperNode ) {  // document.btnsAddedNum++
  var edx = ++document.btnsAddedNum;
  var wrapper = getNode( wrapperNode ) ;
  var span = elAttache('span', wrapper, {'id':'_container['+edx+']'}, 'Within' );
  var input = elAttache('input', span, {'id':'_add_place_name['+edx+']','type':'text', 'name':'_add_place_name['+edx+']', 'value':'', 'size':'20', 'class':'w100'}, 'Within' );
  var br = elAttache('br', span, {}, 'Within' );
  input.focus();
  return true;
}

function  cnmTRowDrop() {
  if ( document.btnsAddedNum < 1 ) return;
  var edx = document.btnsAddedNum--;
  var spanNode  = getNode('_container['+edx+']');
  spanNode.innerHTML = '';
  removeNode( spanNode );
  if ( edx>1 ) getNode('_add_place_name['+(edx-1)+']').focus();
  return true;
}

function  validateIsEmpty( checkIds, respIds ) {
  var errNum=0;
  try {
    for (var i=0; i<checkIds.length; i++ )  {
      var el = getNode( checkIds[i] );
      var re = getNode( respIds[i] );
        var ptrn = new RegExp("[^a-zA-Z0-9ј-яа-€®Є_]", "g");
        var rpcd = el.value.replace(ptrn, ''); // alert ( '{'+el.value + '} replaced : {' + rpcd+'}' );
        if (el.value=='' || rpcd.length==0 || el.value==undefined )
        {   re.innerHTML='*'; errNum++;  }
        else re.innerHTML='';
    }
  } catch (e) { alert('exception caught : '+e ); }

  if ( errNum ) return false;
  return true;
}

// обойти весь узел, индексиру€ элементы, выполн€€ установленный в cfgBehvr соответствующий индексу callback
function nodeTravrs( nd, cfgBehvr, level ) {  //  cfgBehvr = { 'condition_to_evaluate1':'callback1', ... , 'condition_to_evaluateN':'callbackN' };
  if ( document.tvsx==undefined )  document.tvsx=0;
  var dx=document.tvsx;
  if ( nd.nodeType==1 )  { // если это элемент - обработать  // var el = getNode(nd);
    dx = ++document.tvsx;       // handling :
    if ( cfgBehvr[dx] != null ) // alert ('eval: ' + cfgBehvr[dx] );
      eval ( cfgBehvr[dx] );
  }
  /*   // debug  alert( 'nodeName: <'+nd.nodeName+'/>; node: '+nd+'; dx:'+dx+'; nodeValue : '+nd.nodeValue+'/value: '+nd.value+
                        '; nodeType : '+nd.nodeType+'\n;'+'level'+level+' nd.hasChildNodes():'+nd.hasChildNodes()+'\n');    //*/
  if ( nd.hasChildNodes() ) {
    for ( var j=0; j<nd.childNodes.length; j++ ) {
      nodeTravrs( nd.childNodes[j], cfgBehvr, ++level );
      level--;
    }
  }

  if ( level===0 ) document.tvsx=undefined;
}

function rfShwPerAdd( protoNode, targetNode ) { // берем за основу узел-прототип, клонируем его, и немножечко напильником
  if ( document.periodsNum===undefined ) document.periodsNum=0;
  var idx = ++document.periodsNum;

  var Dt = new Date();  var dateDay = Dt.getDate();  if (++dateDay<10) dateDay = '0' + dateDay;
  var dateMon = Dt.getMonth(); if (++dateMon<10) dateMon = '0' + dateMon;
  var today = Dt.getFullYear()+'-'+ dateMon+'-'+dateDay;

  var nPro = getNode( protoNode );
  var nTrg = getNode( targetNode );
  var nCln = nPro.cloneNode( withChildren=1 ); // keyNode.parentNode.insertBefore( el, keyNode );
  nCln.setAttribute('id', (nPro.id+'_'+idx) );
  nTrg.parentNode.insertBefore( nCln, nTrg );    // is IE defined in popcalendar.js
  var cfgBehvr = { '1': 'nd.style.display=\'\'',// +(isIE?'nd.style.display=\'block\';':'nd.style.display=\'table-row\';'), // [ MOZ, IE6 ]
                   '5':'elSetup( nd, {\'name\':\'_add_date_start['+document.periodsNum+']\', \'id\':\'_add_date_start_'+document.periodsNum+'\', \'value\':\''+today+'\'} );',
                   '8':'elSetup( nd, {\'name\':\'_add_date_stop['+document.periodsNum+']\', \'id\':\'_add_date_stop_'+document.periodsNum+'\', \'value\':\''+today+'\'} );',
                   '6':'elSetup( nd, {\'onclick\':\'popUpCalendar(this, document.getElementById(\\\'_add_date_start_'+document.periodsNum+'\\\'), \\\'yyyy-mm-dd\\\',\\\'right\\\');return false;\' } );',
                   '9':'elSetup( nd, {\'onclick\':\'popUpCalendar(this, document.getElementById(\\\'_add_date_stop_'+document.periodsNum+'\\\'), \\\'yyyy-mm-dd\\\',\\\'right\\\');return false;\' } );',
                   '11':'removeNode( nd );' // удалить кнопку "¬рем€"
                 }; // alert ( cfgBehvr );
  nodeTravrs( nCln, cfgBehvr, 0 );

}

function rfShwPerRemove(protoNode) {
  if ( document.periodsNum===undefined ) document.periodsNum=0;
  if ( document.periodsNum < 1 ) return;

  var idx = document.periodsNum--;
  var nPro = getNode( protoNode );
  var el_id = nPro.id+'_'+idx;   //   alert(' removeNode( '+el_id+' )' );
  removeNode( el_id ) ;
}

function contentReflect( fromNode, toNode ) {
  getNode(toNode).innerHTML = getNode(fromNode).innerHTML;
}