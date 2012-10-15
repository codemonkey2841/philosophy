<?php

$link = mysql_connect('localhost', 'root', '');
if (!$link) {
   die('Could not connect: ' . mysql_error());
}

$db_selected = mysql_select_db('philosophy');
if (!$db_selected) {
   die("Can't use foo : " . mysql_error());
}

$data = array();
//$data['Philosophy'] = get_article_tree(1);

require_once('./classes/GDRenderer.php');

//create new GD renderer, optinal parameters: LevelSeparation, SiblingSeparation, SubtreeSeparation, defaultNodeWidth, defaultNodeHeight
$objTree = new GDRenderer(30, 10, 30, 50, 40);

//add nodes to the tree, parameters: id, parentid optional title, text, width, height, image(path)
$query = "SELECT id, links_to, name FROM articles WHERE links_to != 0 OR "
   . "name = 'Philosophy' ORDER BY id;";
$result = mysql_query($query);

while( $row = mysql_fetch_array($result) ) {
   file_put_contents('php://stderr', $row['id'] . " - " . $row['name'] . "\n");
   $objTree->add($row['id'], $row['links_to'], $row['name']);
}

//$objTree->setNodeLinks(GDRenderer::LINK_BEZIER);

$objTree->setBGColor(array(255, 255, 255));
$objTree->setNodeTitleColor(array(0, 128, 255));
$objTree->setNodeMessageColor(array(0, 192, 255));
$objTree->setLinkColor(array(0, 64, 128));
//$objTree->setNodeLinks(GDRenderer::LINK_BEZIER);
$objTree->setNodeBorder(array(0, 0, 0), 2);
$objTree->setFTFont('/usr/share/fonts/truetype/msttcorefonts/arial.ttf', 10, 0, GDRenderer::CENTER);

$objTree->stream();

