<?php

if( isset($argv[4]) ) {
   mysql_connect($argv[1], $argv[3], $argv[4]);
} else {
   mysql_connect($argv[1], $argv[3]);
}
mysql_select_db($argv[2]);

$query = "SELECT HIGH_PRIORITY * FROM articles WHERE name = 'Philosophy';";
$result = mysql_query($query);
if( mysql_num_rows($result) == 0 ) {
   $query = "INSERT INTO articles (name, links_to, degree) VALUES "
      . "('Philosophy', 0, 0);";
   mysql_query($query);
   print "Philosophy\n";
} else {
   //exit("Database is already populated");
}

ini_set('user_agent', 'Mozilla/5.0 (Windows; U; Windows NT 5.1; en-US;
rv:1.8.1.9) Gecko/20071025 Firefox/2.0.0.9');


date_default_timezone_set('UTC');

$url = 'http://en.wikipedia.org/w/api.php?action=query&list=allpages&format=xml&apfilterredir=nonredirects&aplimit=500';

$apfrom = '&apfrom=Lac-Metei,_Quebec';
$active = true;

while( $active ) {
   while( !($file = fopen($url . $apfrom, 'r')) ) {}
   $text = stream_get_contents($file);
   fclose($file);
   $xml = new SimpleXMLElement($text);
   foreach( $xml->query->allpages->p as $bl ) {
      $title = $bl->attributes()->title;
      $title = preg_replace('/\ /', "_", $title);
      $insert_query = "INSERT INTO articles (name, links_to, degree) "
         . "VALUES ('" . $title . "', 0, 0);";
      mysql_query($insert_query);
      print $insert_query . "\n";
   }

   $qcont = "query-continue";
   if( empty($xml->$qcont) ) {
      $active = false;
   } else {
      $temp = $xml->$qcont->allpages->attributes()->apfrom;
      $temp = preg_replace('/\ /', "_", $temp);
      $apfrom = "&apfrom=" . $temp;
   }
}
