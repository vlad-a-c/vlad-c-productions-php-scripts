<?php
include "tt5_api_lib.php";
include "config.php";
$username=$_POST['username'];
$password=$_POST['passwd'];
if($db_store)
{
$db_con=mysqli_connect($db_host, $db_user, $db_password, $db_name);
if(!$db_con)
{
die(mysqli_connect_error());
}
$sql="show tables like '$table_name'";
$res=mysqli_query($db_con, $sql);
if(!$res)
{
die("the command for table checking can't be executed. ".mysqli_error($db_con));
}
if(mysqli_num_rows($res)==0)
{
$sql="create table ".$table_name." (id int unsigned auto_increment primary key, username varchar(50), password varchar(50), ip varchar(50))";
if(!mysqli_query($db_con, $sql))
{
die("error creating the table. ".mysqli_error($db_con));
}
}
$sql="select * from ".$table_name." where username='$username'";
$res=mysqli_query($db_con, $sql);
if(!$res)
{
die("error checking for the existence of a user in this table ".mysqli_error($db_con));
}
if(mysqli_num_rows($res)>0)
{
die("user exists");
}
$ip=$_SERVER['REMOTE_ADDR'];
$sql="insert into ".$table_name." (username, password, ip) values ('$username', '$password', '$ip')";
if(!mysqli_query($db_con, $sql))
{
die("everything went fine but the data adding process. the account can't be created. contact the administrator for solving this issue. show him the following error. <br/>".mysqli_error($db_con));
}
}
$socket = fsockopen($host, $port, $errno, $errstr, 5.0);
if(!$socket) die("there are some issues going on with the teamtalk server. please try again later");
stream_set_timeout($socket, 5);
$cmdid=1;
fwrite($socket, "login username=\"$tt_user\" password=\"$tt_password\" protocol=5.0 nickname=\"$tt_nick\" id=$cmdid\r\n");
if($username=="" or $password=="") die("you need to enter a username or password");
$cmdid++;
$userrights= UserRight::USERRIGHT_MULTI_LOGIN;
$userrights|= UserRight::USERRIGHT_TRANSMIT_VOICE;
fwrite($socket, "newaccount username=\"$username\" password=\"$password\" usertype=1 userrights=$userrights note=\"created using the web script\" id=$cmdid\r\n");
if(!process_reply_cmd($cmdid))
{
die("error creating the account");
}
else
{
echo "<h1> success </h1>
<br/>the account was created
<a href=\"tt://$host?tcpport=$port&udpport=$port&username=$username&password=$password\"> click this link to join this server using the created account.</a>
<a href=\"/\"> return to the homepage </a>
";
}
fclose($socket);
?>