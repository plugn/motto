/*
 * @description   mAjax ( AJAX Request client )
 * @author        Max L. Dolgov      <eujam at newmail dot ru>
 *
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
                var querystr = "";
                var lg="";
                for ( var prop in cfg  )  { this[prop] = cfg[prop]; }
                for ( var prop in this )  { lg += prop+"="+this[prop]+"\r\n"; }
                    querystr += this.prmkey+"="+encodeURIComponent(this.prmval);

                switch (this.method)
                {   case 'get':   case 'GET':  URL = this.URI + '?'+ querystr;
                                               args = '';
                    break;
                    case 'post':  case 'POST': URL = this.URI;
                                               args = querystr;
                    break;
                }

                request = this.createRequest();
                request.open( this.method, URL, this.async ); //); false - sync, true - async (default)
                if (args!='')
                    request.setRequestHeader("Content-Type", "application/x-www-form-urlencoded");

                request.onreadystatechange = function() {
                    pgsBar = document.getElementById(cfg.pgsBarId);
                    // alert (this.pgsBar+'] ::: '+pgsBar);
                    // pgsBar.style.display="block";
                    pgsBar.style.visibility="visible";
                    switch (request.readyState)    {
                        case 1 : pgsBar.innerHTML = '*... загрузка'; break;
                        case 2 : pgsBar.innerHTML = '**.. загрузка'; break;
                        case 3 : pgsBar.innerHTML = '***. загрузка'; break;
                        case 4 : pgsBar.innerHTML = '**** готово';
                            rspMsg = request.status + " " + request.statusText + request.responseText;
                            eRsp = document.getElementById(cfg.rspBarId);
                            eRsp.value = request.responseText;
                            //innerHTML += '';
                            // ('<br />'+ rspMsg); //alert(request.getAllResponseHeaders())
                            // timer = setTimeout('pgsBar.innerHTML = "";pgsBar.style.display="none";', 1000 );
                            timer = setTimeout('pgsBar.innerHTML = "";pgsBar.style.visibility="hidden";', 1000 );
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