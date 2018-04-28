<?php
if(!$_POST['version'] or !$_POST['description'])
{
die("you need to specify the version and the description");
}
$version=$_POST['version'];
$description=$_POST['description'];
$game=$_POST['game'];
file_put_contents("description.txt", $description);
file_put_contents("version.txt", $version);
echo $game." successfully released";
?>