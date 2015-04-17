///////////////////////////////////////////////////////////////////
///////                  DialogKutesan                      ///////
///////             PrestaShop Tools Module                 ///////
///////                                                     ///////
///////               CREATED BY :  KUTESAN                 ///////
///////                  MADE IN : SPAIN                    ///////
///////  TESTED WITH THE VERSIONS: 1.2                      ///////
///////             CREATED DATE : 02 - JUNE - 2009         ///////
///////                  LICENSE : CopyLeft                 ///////
///////              AUTOR'S WEB :                          ///////
///////                                                     ///////
///////////////////////////////////////////////////////////////////


    $(document).ready(function() { 
 
         
        $.blockUI({ message: $('#question'), css: { 
            width: '800px',
            height: '613px',
            top: '15%',
            left: '15%',
            backgroundColor: 'transparent', 
            color: '#000'
             } 
             }); 
        $('#yes').click(function() { 
            //SE REGISTRA VALOR DE COOKIE
            $.unblockUI();
            setCookie(cookie_name,cookie_value);
            return true; 
        }); 
 
        $('#no').click(function() { 
             
            location.href = page;
             
        });
         
 
    }); 
    
    function setCookie(cookiename,cookievalue,expires,path,domain,secure) {
      document.cookie=
      escape(cookiename)+'='+escape(cookievalue)
      +(expires?'; EXPIRES='+expires.toGMTString():'')
      +(path?'; PATH='+path:'')
      +(domain?'; DOMAIN='+domain:'')
      +(secure?'; SECURE':'');
    }
    
    function getCookie(cookiename) {
      var posName=document.cookie.indexOf(escape(cookiename)+'=');
      if (posName!=-1) {
        var posValue=posName+(escape(cookiename)+'=').length;
        var endPos=document.cookie.indexOf(';',posValue);
        if (endPos!=-1) cookie_get_value=unescape(document.cookie.substring(posValue,endPos));
        else cookie_get_value=unescape(document.cookie.substring(posValue));
      }
      return cookie_get_value;
    } 