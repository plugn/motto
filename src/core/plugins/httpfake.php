<?php

/* Specify plain text content in our response */
if(!headers_sent()) {
  header('Content-type: text/html');
  header('Status: 503 Service Unavailable');
}

/* What headers are going to be sent? */
echo '<!DOCTYPE HTML PUBLIC "-//IETF//DTD HTML 2.0//EN">'.
     '<HTML><HEAD><TITLE>503 Service Unavailable</TITLE></HEAD><BODY>'.
     '<H1>Service Unavailable</H1>'.
     '<p>Our service is having a little trouble right now.</p>'.
     '<p>This is not because of anything you did; it\'s just a little hiccup in our system that will hopefully go away soon. We apologize for the inconvenience, and recommend you try reloading this page.</p>'.
     '<hr />'.
     ($_SERVER["SERVER_SIGNATURE"]?$_SERVER["SERVER_SIGNATURE"]:'<ADDRESS>'.$_SERVER["SERVER_SOFTWARE"].' Server at '.
     $_SERVER["HTTP_HOST"].' Port '.$_SERVER["SERVER_PORT"].'</ADDRESS>').'</BODY></HTML>';

?>