<?php
$runs=0;
if(file_exists("runs.txt"))
{
$runs=file_get_contents("runs.txt");
}
$runs=$runs+1;
file_put_contents("runs.txt", $runs);
echo $runs;
?>