<?php

if( isset($argv[4]) ) {
   mysql_connect($argv[1], $argv[3], $argv[4]);
} else {
   mysql_connect($argv[1], $argv[3]);
}
mysql_select_db($argv[2]);

$query = "SELECT * FROM articles WHERE name = 'Philosophy';";
$result = mysql_query($query);
if( mysql_num_rows($result) == 0 ) {
   exit("Database not populated");
}

ini_set('user_agent', 'Mozilla/5.0 (Windows; U; Windows NT 5.1; en-US;
rv:1.8.1.9) Gecko/20071025 Firefox/2.0.0.9');
ini_set('mysql.connect_timeout', 300);
ini_set('default_socket_timeout', 300);

mysql_query("SET max_allowed_packet = 500000000;");

$query = "SELECT * FROM articles WHERE traversed = 0 AND "
   . "links_to != 0 ORDER BY degree, id LIMIT 1000;";
$result = mysql_query($query);
if( mysql_errno() == 2006 ) {
   if( isset($argv[4]) ) {
      mysql_connect($argv[1], $argv[3], $argv[4]);
   } else {
      mysql_connect($argv[1], $argv[3]);
   }
   mysql_query($query);
}
$max = mysql_num_rows($result);

$x = 0;
while( $row = mysql_fetch_array($result) ) {

   $id = $row['id'];
   $article = $row['name'];
   $links_to = $row['links_to'];
   $degree = $row['degree'];

   date_default_timezone_set('UTC');
   print date("m/d/Y H:i:s") . " - $article($x)\n";

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
      $update_query = "UPDATE articles SET links_to = " . $id . ", degree "
         . "= " . ($degree+1) . " WHERE links_to = 0 AND degree = 0 AND "
         . "name in (";
      foreach( $list as $val ) {
         $update_query .= "'" . $val . "', ";
      }
      $update_query = substr($update_query, 0, -2) . ");";
      mysql_query($update_query);
      if( mysql_errno() == 2006 ) {
         if( isset($argv[4]) ) {
            mysql_connect($argv[1], $argv[3], $argv[4]);
         } else {
            mysql_connect($argv[1], $argv[3]);
         }
         mysql_query($query);
      }

      $qcont = "query-continue";
      if( empty($xml->$qcont) ) {
         $active = false;
      } else {
         $blcontinue = "&blcontinue=" . $xml->$qcont->backlinks->attributes()->blcontinue;
      }
   }

   $query = "UPDATE articles SET traversed = 1 WHERE id = $id;";
   mysql_query($query);
   if( mysql_errno() == 2006 ) {
      if( isset($argv[4]) ) {
         mysql_connect($argv[1], $argv[3], $argv[4]);
      } else {
         mysql_connect($argv[1], $argv[3]);
      }
      mysql_query($query);
   }
   $x++;

   if( $x >= $max ) {
      $query = "SELECT * FROM articles WHERE traversed = 0 AND links_to != 0 "
         . "ORDER BY degree, id LIMIT 1000;";
      $result = mysql_query($query);
      if( mysql_errno() == 2006 ) {
         if( isset($argv[4]) ) {
            mysql_connect($argv[1], $argv[3], $argv[4]);
         } else {
            mysql_connect($argv[1], $argv[3]);
         }
         mysql_query($query);
      }
      $x = 0;
      $max = mysql_num_rows($result);
      if( $max == 0 ) {
         exit;
      }
   }

}


