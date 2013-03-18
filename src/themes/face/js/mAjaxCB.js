/*
 * @description   mAjax ( AJAX Request client )
 * @author        Max L. Dolgov      <eujam at newmail dot ru>
 *
 * experimental version with simple callback support
**/



function mAjax() {}
(
    function()
    {
        mAjax.prototype = {

            URI           : null,
            async         : true,
            method        : 'GET',

            createRequest : function() {
                var C=null;
                try {
                  C=new ActiveXObject("Msxml2.XMLHTTP")
                } catch (e) {
                  try  {
                      C=new ActiveXObject("Microsoft.XMLHTTP")
                  } catch (sc) {
                    C=null}
                  }
                if(!C&&typeof XMLHttpRequest!="undefined")
                  { C=new XMLHttpRequest() }

                try {  // Needed for Mozilla if local file tries to access an http URL
                  netscape.security.PrivilegeManager.enablePrivilege("UniversalBrowserRead");
                } catch (e) {  /* ignore */ }

                return C;
            },

            send          : function( cfg ) {
                /* allow client script to override properties */
                for ( var prop in cfg  )  { if (prop!='prm') this[prop] = cfg[prop]; }
                /* 'prm' is hash contaning a querystring data  */
                var querystr = "";
                if (cfg.prm) {
                    for ( var prop in cfg.prm )
                        querystr += prop+"="+encodeURIComponent(cfg.prm[prop])+"&";
                    querystr = querystr.substr(0, querystr.length-1);  // alert('qs : ' + querystr);
                }

                switch (this.method)
                {   case 'get':   case 'GET':  URL = cfg.URI + '?'+ querystr;
                                               args = '';
                    break;
                    case 'post':  case 'POST': URL = cfg.URI;
                                               args = querystr;
                    break;
                }

                request = this.createRequest();
                request.open( this.method, URL, this.async ); //); false - sync, true - async (default)
                if (args!='')
                    request.setRequestHeader("Content-Type", "application/x-www-form-urlencoded");

                request.onreadystatechange = function() {
                    pgsBar = document.getElementById(cfg.pgsBarId);
                    pgsBar.style.visibility="visible";
                    switch (request.readyState)    {
                        case 1 : pgsBar.innerHTML = '*... загрузка'; break;
                        case 2 : pgsBar.innerHTML = '**.. загрузка'; break;
                        case 3 : pgsBar.innerHTML = '***. загрузка'; break;
                        case 4 : pgsBar.innerHTML = '**** готово';
                            timer = setTimeout('pgsBar.innerHTML = "";pgsBar.style.visibility="hidden";', 1000 );
                            eval ( cfg.rspCallBk+'( request.responseText, cfg );' );
                        break;
                        case 0 :
                        default: pgsBar.innerHTML = '.... установка соединения';
                    }
                    return;
                }

                request.send (args);
            }
        }
    }

) ();