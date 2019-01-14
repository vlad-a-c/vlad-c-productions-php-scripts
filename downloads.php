<?php
if(!$_GET['target'] or !$_GET['dir'])
{
die("sorry.  but you need to specify the target and directory for making this script to work.");
}
$dir=$_GET['dir'];
$target=$_GET['target'];
if(!is_dir($dir))
{
die("
<html>
<head>
<title> error </title>
</head>
<body>
<h1> error </h1>
<br/> an error occurred while trying  to download a file. this means that even the requested directory  doesn't exists or there is a miss spell of the file name. please contact us  and  we'll fix it as soon as we figure out  the probloem.
</body>
</html>
");
}
else if(!file_exists($target))
{
die("
<html>
<head>
<title> error </title>
<br/>
</head>
<body>
<br/>
<h1> an error occured </h1>
<br/>
an error occured while trying to download the file. this means that the the file you're trying to download doesn't exist, or it's a miss spell of it's location. please contact us and we'll solve this problem once we see your message.
</body>
</html>
");
}
$file=$dir."/clicks.txt";
$clicks=0;
if(file_exists($file))
{
$clicks=file_get_contents($file);
}
$clicks=$clicks+1;
file_put_contents($file, $clicks);
header("Location: ".$target);
?>