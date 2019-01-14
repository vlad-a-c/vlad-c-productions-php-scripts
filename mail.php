<?php
$from=$_POST['from'];
$mess=$_POST['mess'];
$mess=wordwrap($mess,70,"\r\n");
$sub=$_POST['sub'];
$name=$_POST['name'];
$who=$_POST['who'];
if(!$from or !$name  or !$mess or  !$sub or !$who)
{
	die("damn fuck. stop trying to hack us.");
}
mail($who, $sub, $mess, "from: ".$from);
echo "success";
?>