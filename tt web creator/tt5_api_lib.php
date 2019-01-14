<?php
function process_reply_cmd($cmdid)
{
    global $socket, $users, $channels, $quota_options;

    //variable to keep track of the command ID which is currently
    //being processed.
    $curcmdid = 0;
    //variable to keep track of whether the $cmdid parameter
    //succeeded.
    $success = FALSE;
    while(TRUE)
    {
        //BUG: Typically the TCP recv buffer is 8192 bytes so this
        //call will fail if the command is longer.
        $line = fgets($socket);

        //do a syntax check to ensure the reply is valid.
        $cmdline = get_cmdline($line);
        if(!$cmdline)
            return FALSE;

        //extract the command name
        $cmd = get_cmd($cmdline);
        if(!$cmd)
            return FALSE;

        //process the command
        switch($cmd)
        {
        case 'teamtalk' :
            //welcome command (the first message when we connect
            $userid = get_int($cmdline, 'userid');
            /*if($userid)
                echo "Got user ID# $userid\r\n";
            $servername = get_str($cmdline, 'servername');
            echo "Server name is: " . $servername . "\r\n";
            $usertimeout = get_int($cmdline, 'usertimeout');
            if($usertimeout)
                echo "Your session will time out in $usertimeout seconds\r\n";
*/
            break;
        case 'begin' :
            //A reply to a command ID is starting.
            $curcmdid = get_int($cmdline, 'id');
            break;
        case 'end' :
            //A reply to a command ID ended.
            if(get_int($cmdline, 'id') == $cmdid)
                return $success;
            else
                $curcmdid = 0;
            break;
        case 'error' :
            //a command failed. We check if it's the one we're waiting
            //for.
            echo $cmdline;
             if($curcmdid == $cmdid) 
                 $success = FALSE; 
            break;
        case 'ok' :
            //a command succeeded. We check if it's the one we're
            //waiting for.
            if($curcmdid == $cmdid)
                $success = TRUE;
            break;
        case 'accepted' :
            if((get_int($cmdline, 'usertype') & 2) == 0)
            {
                echo "The user account for login must be an administrator account\r\n";
                exit(1);
            }
//            echo "Log in successful!\r\n";
            break;
        case 'loggedin' :
        case 'adduser' :
        case 'updateuser' :
            $id = get_int($cmdline,'userid');
            $users[$id]['userid'] = $id;
            $users[$id]['nickname'] = get_str($cmdline, 'nickname');
            $users[$id]['ipaddr'] = get_str($cmdline, 'ipaddr');
            $users[$id]['chanid'] = get_int($cmdline, 'chanid');
            $users[$id]['channelpath'] = getchannelpath($users[$id]['chanid']);
            $users[$id]['username'] = get_str($cmdline, 'username');
            break;
        case 'removeuser' :
            $id = get_int($cmdline,'userid');
            unset($users[$id]['chanid']);
            unset($users[$id]['channelpath']);
            break;
        case 'loggedout' :
            $id = get_int($cmdline,'userid');
            unset($users[$id]);
            break;
        case 'addchannel' :
        case 'updatechannel' :
            $id = get_int($cmdline, 'chanid');
            $channels[$id]['chanid'] = $id;
            $name = get_str($cmdline, 'name');
            $channels[$id]['name'] = $name;
            $parentid = get_int($cmdline, 'parentid');
            if($parentid)
                $channels[$id]['parentid'] = $parentid;
            $topic = get_str($cmdline, 'topic');
            if($topic)
                $channels[$id]['topic'] = $topic;
            $passwd = get_str($cmdline, 'password');
            if($passwd)
                $channels[$id]['password'] = $passwd;
            $oppasswd = get_str($cmdline, 'oppassword');
            if($oppasswd)
                $channels[$id]['oppassword'] = $oppasswd;
            $audiocodec = get_list($cmdline, 'audiocodec');
            if($audiocodec)
                $channels[$id]['audiocodec'] = $audiocodec;
            $audioconfig = get_list($cmdline, 'audiocfg');
            if($audioconfig)
                $channels[$id]['audioconfig'] = $audioconfig;
            break;
        case 'removechannel' :
            $id = get_str($cmdline, 'chanid');
            unset($channels[$id]);
            echo "Removed channel $id\r\n";
            break;
        case 'joined' :
            $chanid = get_str($cmdline, 'chanid');
            echo "Joined channel ".getchannelpath($chanid)."\r\n";
            break;
        case 'left' :
            $chanid = get_str($cmdline, 'chanid');
            echo "Left channel ".getchannelpath($chanid)."\r\n";
            break;
        case 'addfile' :
        case 'removefile' :
            break;
        case 'useraccount' :
            //echo $cmdline;
            break;
        case 'userbanned' :
            echo $cmdline;
            break;
        case 'messagedeliver' :
            break;
        case 'stats' :
            $totaltx = get_int($cmdline, 'totaltx');
            $totalrx = get_int($cmdline, 'totalrx');
            $voicetx = get_int($cmdline, 'voicetx');
            $voicerx = get_int($cmdline, 'voicerx');
            $vidcaptx = get_int($cmdline, 'videocaptx');
            $vidcaprx = get_int($cmdline, 'videocaprx');
            $mediafiletx = get_int($cmdline, 'mediafiletx');
            $mediafilerx = get_int($cmdline, 'mediafilerx');
            echo "Server statistics.\r\n";
            echo "Total TX: " . $totaltx / 1024 . " KBytes\r\n";
            echo "Total RX: " . $totalrx / 1024 . " KBytes\r\n";
            echo "Voice TX: " . $voicetx / 1024 . " KBytes\r\n";
            echo "Voice RX: " . $voicerx / 1024 . " KBytes\r\n";
            echo "Video TX: " . $videocaptx / 1024 . " KBytes\r\n";
            echo "Video RX: " . $videocaprx / 1024 . " KBytes\r\n";
            echo "Media TX: " . $mediafiletx / 1024 . " KBytes\r\n";
            echo "Media RX: " . $mediafilerx / 1024 . " KBytes\r\n";
            break;
        case 'serverupdate' :
            //echo "Server updated...\r\n";
            $servername = get_str($cmdline, 'servername');
            //echo "Server name is: " . $servername . "\r\n";
            $maxusers = get_int($cmdline, 'maxusers');
            //echo "Max users on server: $maxusers\r\n";
            $usertimeout = get_int($cmdline, 'usertimeout');
            //echo "User timeout: $usertimeout seconds\r\n";
            $motd = get_str($cmdline, "motd");
            //echo "Server's MOTD is: $motd\r\n";
            break;
        case 'pong' :
            $success = TRUE;
            break;
        default:
            echo 'Unhandled cmd: ' . $cmdline;
            break;        
        }
        //stop processing now if we're not waiting for a command ID to
        //finish.
        if($cmdid == 0)
            return TRUE;
    }
}

//extract the command line sent by the server (EOL terminates a command)
function get_cmdline($data)
{
    $cmd_regex = '/^([^\r]*\r\n)/';
    if(preg_match($cmd_regex, $data, $matches))
    {
        return $matches[1];
    }
    return FALSE; 
}

//get the command name from a server command
function get_cmd($cmd)
{
    $cmd_regex = '/^(\S+)/';
    if(preg_match($cmd_regex, $cmd, $matches))
    {
        return $matches[1];
    }
    return FALSE; 
}

//get an integer parameter from a server command
function get_int($cmd, $name)
{
    return get_param($cmd, $name);
}

//get a string parameter from a server command
function get_str($cmd, $name)
{
    //example: addchannel chanid=56 topic="here a \"quote\" in the topic" 
    $str = get_param($cmd, $name);
    if($str)
    {
        $str = str_replace("\\n", "\n", $str);
        $str = str_replace("\\r", "\r", $str);
        $str = str_replace("\\\"", "\"", $str);
        $str = str_replace("\\\\", "\\", $str);
        $str = utf8_decode($str);
    }
    return $str;
}

//get a set parameter from a server command
function get_list($cmd, $name)
{
    $list = get_param($cmd, $name);
    if($list)
        return explode(",", $list); //not pretty but it works...
    return FALSE;
}

function get_param($cmd, $param)
{
    $CMDNAME = '([a-zA-Z0-9._-]+)';
    $PARAMNAME = '([a-zA-Z0-9._-]+)';
    $DIGIT = '(-?\d+)';
    $STR = '"(([^\\\\^"]+|\\\\n|\\\\r|\\\\"|\\\\\\\\)*)"';
    $LIST = '\[([-?\d+]?[,-?\d+]*)\]';

    $PARAM =  '\s+'.$PARAMNAME.'=('.$LIST.'|'.$DIGIT.'|'.$STR.')';

    $PARAM_STR   =  '\s+'.$PARAMNAME.'='.$STR;
    $PARAM_DIGIT =  '\s+'.$PARAMNAME.'='.$DIGIT;
    $PARAM_LIST  =  '\s+'.$PARAMNAME.'='.$LIST;

    $regex = '/^' . $CMDNAME . '/';
    //strip cmd
    if(!preg_match($regex, $cmd, $matches))
        return FALSE;

    $cmd = substr($cmd, strlen($matches[0]));

    while(strlen($cmd)>0)
    {
        $regex_str   = '/^' . $PARAM_STR . '/';
        $regex_digit = '/^' . $PARAM_DIGIT . '/';
        $regex_list  = '/^' . $PARAM_LIST . '/';

        if(preg_match($regex_str, $cmd, $matches))
        {
        }
        else if(preg_match($regex_digit, $cmd, $matches))
        {
        }
        else if(preg_match($regex_list, $cmd, $matches))
        {
        }
        else return FALSE;

        if($matches[1] == $param)
            return $matches[2];
        else
            $cmd = substr($cmd, strlen($matches[0]));
    }

    return FALSE;    
}

//prepare a string to it can be used in a command to the server
function to_str($str)
{
    $str = utf8_encode($str);
    $str = str_replace("\\", "\\\\", $str);
    $str = str_replace("\"", "\\\"", $str);
    $str = str_replace("\r", "\\r", $str);
    $str = str_replace("\n", "\\n", $str);
    return $str;
}

//get user input from STDIN
function get_userinput($text)
{
    echo $text;
    return trim(fgets(STDIN));
}

//get user input from STDIN and escape it so it can be used in a command to the server
function get_userparam($text)
{
    $str = get_userinput($text);
    return to_str($str); //convert to escaped string parameter
}

//get audio codec for channel
function get_audiocodec()
{
    $audiocodec = get_userinput("Audio codec of new channel (0=No audio, 1=Speex, 2=Speex VBR, 3=OPUS): ");
    switch($audiocodec)
    {
	default:
    case 0 :
        $audiocodec = '[0]';
        break;
    case 1 : //Speex
        $audiocodec = '[1,1,4,2,0]'; //[Speex CBR codec id, bandmode, quality, frames per packet, simulate stereo]
        break;
    case 2 : //Speex VBR
        $audiocodec = '[2,1,4,0,0,1,2,0]'; //[Speex VBR codec id, bandmode, VBR quality, bitrate, max bitrate, DTX enabled, frames per packet, simulate stereo]
        break;
    case 3: //OPUS
        $audiocodec = '[3,48000,1,2048,10,1,0,32000,1,0,1920]'; //[OPUS codec id, samplerate, channels, application, complexity, FEC, DTX enabled, bitrate, VBR enabled, VBR constraint, frame size]
        break;
    }
    return $audiocodec;
}

function getchannelpath($id)
{
    global $channels;

    if(!isset($channels[$id]))
       return "";

    $path = "/";
    $path = $channels[$id]['name']  . $path;
    $id = $channels[$id]['parentid'];
    while($id != 0)
    {
        $path = $channels[$id]['name'] . "/" . $path;
        $id = $channels[$id]['parentid'];
    }

    return $path;
}

class UserRight
{
    const USERRIGHT_NONE                      = 0x00000000; 
    const USERRIGHT_MULTI_LOGIN               = 0x00000001;
    const USERRIGHT_VIEW_ALL_USERS            = 0x00000002;
    const USERRIGHT_CREATE_TEMPORARY_CHANNEL  = 0x00000004;
    const USERRIGHT_MODIFY_CHANNELS           = 0x00000008;
    const USERRIGHT_TEXTMESSAGE_BROADCAST     = 0x00000010;
    const USERRIGHT_KICK_USERS                = 0x00000020;
    const USERRIGHT_BAN_USERS                 = 0x00000040;
    const USERRIGHT_MOVE_USERS                = 0x00000080;
    const USERRIGHT_OPERATOR_ENABLE           = 0x00000100;
    const USERRIGHT_UPLOAD_FILES              = 0x00000200;
    const USERRIGHT_DOWNLOAD_FILES            = 0x00000400;
    const USERRIGHT_UPDATE_SERVERPROPERTIES   = 0x00000800;
    const USERRIGHT_TRANSMIT_VOICE            = 0x00001000; 
    const USERRIGHT_TRANSMIT_VIDEOCAPTURE     = 0x00002000;
    const USERRIGHT_TRANSMIT_DESKTOP          = 0x00004000;
    const USERRIGHT_TRANSMIT_DESKTOPINPUT     = 0x00008000;
    const USERRIGHT_TRANSMIT_MEDIAFILE_AUDIO  = 0x00010000;
    const USERRIGHT_TRANSMIT_MEDIAFILE_VIDEO  = 0x00020000;
};
?>