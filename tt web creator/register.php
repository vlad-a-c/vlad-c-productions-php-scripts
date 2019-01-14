<?php
include "tt5_api_lib.php";
$host="your TeamTalk server address";
$port="10333";
//note: you need to set the script to log in to one of the server's admin accounts in order to properly work
$user="your admin username";
$password="your admin password";
$socket = fsockopen($host, $port, $errno, $errstr, 5.0);
if(!$socket) die("there are some issues going on with the teamtalk server. please try again later");
stream_set_timeout($socket, 5);
$cmdid=1;
fwrite($socket, "login username=\"$user\" password=\"$password\" protocol=5.0 nickname=\"tt creator\" id=$cmdid\r\n");
$username=$_POST['username'];
$password=$_POST['passwd'];
if($username=="" or $password=="") die("you need to enter a username or password");
$cmdid++;
$userrights= UserRight::USERRIGHT_MULTI_LOGIN;
$userrights|= UserRight::USERRIGHT_TRANSMIT_VOICE;
//fwrite($socket, "newaccount username=\"$username\" password=\"$password\" usertype=2 note=\"created using the web script\" id=$cmdid\r\n");
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