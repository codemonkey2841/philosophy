<?php

mysql_connect($argv[0], $argv[2], $argv[3]);
mysql_select_db($argv[1]);

$query = "SELECT * FROM articles WHERE name = 'Philosophy';";
$result = mysql_query($query);
if( mysql_num_rows($result) == 0 ) {
   $query = "INSERT INTO articles (name, links_to, degree) VALUES "
      . "('Philosophy', 0, 0);";
   mysql_query($query);
}

ini_set('user_agent', 'Mozilla/5.0 (Windows; U; Windows NT 5.1; en-US;
rv:1.8.1.9) Gecko/20071025 Firefox/2.0.0.9');

$query = "SELECT * FROM articles WHERE traversed = 0 ORDER BY degree, id LIMIT 1;";
$result = mysql_query($query);

while( $row = mysql_fetch_array($result) ) {

   $id = $row['id'];
   $article = $row['name'];
   $links_to = $row['links_to'];
   $degree = $row['degree'];

   print "$article\n";

   $url = 'http://en.wikipedia.org/w/api.php?action=query&list=backlinks'
      . '&format=xml&blnamespace=0&bllimit=500&bltitle=';

   $blcontinue = '';
   $active = true;
   $list = array();

   while( $active ) {
      while( !($file = fopen($url . urlencode($article) . $blcontinue, 'r')) ) {}
      $text = stream_get_contents($file);
      fclose($file);
      $xml = new SimpleXMLElement($text);
      foreach( $xml->query->backlinks->bl as $bl ) {
         $title = $bl->attributes()->title;
         $title = preg_replace('/\ /', "_", $title);
         array_push($list, $title);
      }

      $qcont = "query-continue";
      if( empty($xml->$qcont) ) {
         $active = false;
      } else {
         $blcontinue = "&blcontinue=" . $xml->$qcont->backlinks->attributes()->blcontinue;
      }
   }

   $insert_query = "INSERT INTO articles (name, links_to, degree) VALUES ";
   foreach( $list as $item ) {
      $item = mysql_real_escape_string($item);
      $query = "SELECT * FROM articles WHERE name = '$item';";
      $res = mysql_query($query);
      if( mysql_num_rows($res) == 0 ) {
         $insert_query .= "('$item', $id, " . ($degree + 1) . "), ";
      }
   }
   $insert_query = substr($insert_query, 0, -2) . ";";
   mysql_query($insert_query);

   $query = "UPDATE articles SET traversed = 1 WHERE id = $id;";
   $result = mysql_query($query);

   $query = "SELECT * FROM articles WHERE traversed = 0 ORDER BY degree, id LIMIT 1;";
   $result = mysql_query($query);

}

