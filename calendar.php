<?php
$userpwd = '<user>:<pwd>';
$baseurl = 'https://dav.mailbox.org/';

header('Content-type: text/calendar; charset=utf-8');
header('Content-Disposition: attachment; filename=Kalender.ics');

// new cURL-Handle
$ch = curl_init();

// set options
curl_setopt($ch, CURLOPT_URL, $baseurl.'caldav/'.$_GET["id"]);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_HTTPHEADER, array('Accept: text/xml','Depth: 1'));
curl_setopt($ch, CURLOPT_USERPWD, $userpwd); 
curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'PROPFIND');

// execute and close
$response = curl_exec($ch);
curl_close($ch);
 
// Read all ics-Links like <D:href>/caldav/25/pahiq27ji7s2fq64m662517q80.ics</D:href>
preg_match_all("/<D:href>([^<]+\.ics)\s*<\/D:href>/mi", $response, $links);
// if link found, take subelement 1 which is the link (token)
if (!empty($links)) {
  $links = $links[1];
  // setup new handle
  $ch = curl_init();
  // set options
  curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
  curl_setopt($ch, CURLOPT_HTTPHEADER, array('Accept: text/calendar'));
  curl_setopt($ch, CURLOPT_USERPWD, $userpwd); 
  curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
  
  $firstentry = true;
  foreach ($links as $link) {
    $link = $baseurl.urldecode($link);
    curl_setopt($ch, CURLOPT_URL, $link);
    // execute
    $response = curl_exec($ch);
    // First entry? use part before first VEVENT as header
    if ($firstentry) {
      $firstentry = false;
      preg_match_all("/(\A.+?)BEGIN:VEVENT/s", $response, $intro);
      if (!empty($intro)) {
        echo $intro[1][0] ;
      }
    }
    // Get all VEVENTS
    preg_match_all("/BEGIN:VEVENT.*END:VEVENT/s", $response, $events);
    if (!empty($events)) {
      echo $events[0][0]."\r\n";
    }
  }
}
// Footer
echo "END:VCALENDAR";
curl_close($ch);
?>