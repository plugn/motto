/*

*/

    mAjax = function() {
      this.URI    =  null;
      this.async  =  true;
      this.method = 'GET';
    }


    mAjax.prototype.createRequest = function() {
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
          return C;
    }

    mAjax.prototype.send = function( cfg ) {
      var querystr = "";
      var lg="";
      for (var prop in cfg)  { this[prop] = cfg[prop];}
      for (var prop in this) { lg += prop+"="+this[prop]+"\r\n"; }
          querystr += this.prmkey+"="+encodeURIComponent(this.prmval);

      switch (this.method)
      { case 'get':   case 'GET':  URL = this.URI + '?'+ querystr;
                                   args = '';
        break;
        case 'post':  case 'POST': URL = this.URI;
                                   args = querystr;
        break;
      }
        var request = this.createRequest();
        request.open( this.method, URL, this.async ); // false - sync, true - async (default)
        if (args!='')
          request.setRequestHeader("Content-Type", "application/x-www-form-urlencoded");

        request.onreadystatechange = function() {
          pgsBar = document.getElementById("pgsbar");
          pgsBar.style.display="block";
          switch (request.readyState)    {
            case 1 : pgsBar.innerHTML = '*... loading'; break;
            case 2 : pgsBar.innerHTML = '**.. response headers received'; break;
            case 3 : pgsBar.innerHTML = '***. some response body received'; break;
            case 4 : pgsBar.innerHTML = '**** готово';
              rspMsg = request.status + " " + request.statusText + request.responseText;
              eRsp = document.getElementById("response");
              eRsp.innerHTML += '';// ('<br />'+ rspMsg); //alert(request.getAllResponseHeaders())
              timer = setTimeout('pgsBar.innerHTML = "";pgsBar.style.display="none";', 1000 );

              break;
            case 0 :
            default: pgsBar.innerHTML = '.... установка соединения';
          }
          return;
        }

        request.send (args);

    }

