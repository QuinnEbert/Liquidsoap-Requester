<?php
//FIXME: It would be a FANTASTIC idea to split up this codefile into small pieces!
//...
// Workaround for GetID3 Windows HelperApps bug:
define('GETID3_HELPERAPPSDIR', dirname(__FILE__).'/php-getid3/helperapps/');
// maximum error verbosity
error_reporting(E_ALL);
// Uncomment the below if bad stuff seems to happen:
//ini_set('display_errors',True);
// Make sure we are on UNIX, and have UNIX "find" available...
if (! isset($_SERVER['SERVER_SOFTWARE']))
  die("Fatal Error:\nThis script cannot be run as a command interface scriptlet!\n");
$sys = strtolower($_SERVER['SERVER_SOFTWARE']);
//if (strstr($sys,"windows") || strstr($sys,"winnt") || strstr($sys,"win32"))
//  die("<strong>Fatal Error:</strong><br><br>This script cannot be run on a Microsoft&reg; Windows&reg; hosting platform!");
// Pull in the configuration file:
$cfgFile = dirname(__FILE__).'/soap_req.cfg';
if ( !file_exists($cfgFile) || !is_readable($cfgFile) )
  die('<strong>Fatal Error:</strong><br><br>Couldn\'t find (or read) the config file "'.$cfgFile.'"!  Check it is there, and that the permissions to read it are sufficient, and try loading this page again!');
require_once($cfgFile);
if (isset($myUsers) && !strstr($myUsers,$_SERVER['REMOTE_ADDR']))
  die("Fatal Error: We don't like you, so, go away!");
// MOSTLY-WINDOWS TESTING: Having a FLATPATH that doesn't work should illicit an error:
if ( $mSource == 'flatpath' && !file_exists($mReadIn) )
  die('<strong>Fatal Error:</strong><br><br>The media path of "'.$mReadIn.'" could not be read or was unusable!  Check it is there, and that the permissions to read it are sufficient, and try loading this page again!');
// include getID3() library (can be in a different directory if full path is specified)
$id3IsAt = dirname(__FILE__).'/php-getid3/getid3/getid3.php';
if (file_exists($id3IsAt) && is_readable($id3IsAt)) {
  require_once($id3IsAt);
  require_once(dirname($id3IsAt).'/getid3.lib.php');
} else {
  die('<strong>Fatal Error:</strong><br><br>Unable to load the required  code library, please check your installation!');
}
// Code to let us be sure DBM is supported by the ID3 library:
$dbmFnFl = 'codelibs/getid3SupportsDbmCache.php';
if (file_exists($dbmFnFl) && is_readable($dbmFnFl)) {
  require_once($dbmFnFl);
} else {
  die('<strong>Fatal Error:</strong><br><br>Unable to load the required "codelibs" file to verify your ID3 install supports DBM caching, please check your installation!');
}
// Initialize getID3 engine, using DBM cache if it's available:
if (! getid3SupportsDbmCache()) {
  $getID3 = new getID3;
  //DEBUG: die('<pre>CACHE=NO</pre>');
} else {
  //FIXME: I know, this setup is redundant.  I'll fix it later (see the codelib for why it's redundant):
  $dbmIsAt = dirname(__FILE__).'/php-getid3/getid3/extension.cache.dbm.php';
  // Include the codefile to support ID3 DBM cache:
  getid3_lib::IncludeDependency($dbmIsAt, __FILE__, true);
  $i3dbAt = dirname(__FILE__).'/lsqueuer.id3.dbm';
  $getID3 = new getID3_cached_dbm('db4', $i3dbAt, $i3dbAt.'.LCK');
  //DEBUG: die('<pre>CACHE=OK</pre>');
}
global $getID3;
// for requesting metadata QUICKLY...
function req_meta($fname) {
	global $useMeta;
	if (!$useMeta)
		return('');
	global $getID3;
	$ThisFileInfo = $getID3->analyze($fname);
	getid3_lib::CopyTagsToComments($ThisFileInfo);
	//print_r($ThisFileInfo);
	//die();
	return('"'.$ThisFileInfo['comments_html']['title'][0].'" by '.$ThisFileInfo['comments_html']['artist'][0]);
	/*foreach ($ThisFileInfo['comments_html']['title'] as $title) {
	}*/
}
// Function to get # of processors, and load, if both are available
// RETURNS:
//   string with status/info if we can get it
//   FALSE if we can't get info/status
function cpu_stat() {
  if (!file_exists('/bin/grep'))
    return(false);
  if (!is_readable('/bin/grep'))
    return(false);
  if (!file_exists('/proc/cpuinfo'))
    return(false);
  if (!is_readable('/proc/cpuinfo'))
    return(false);
  if (!file_exists('/proc/loadavg'))
    return(false);
  if (!is_readable('/proc/loadavg'))
    return(false);
  $dum = explode('.',`hostname`);
  $a = trim($dum[0]);
  $dun = explode("\n",rtrim(`/bin/grep 'processor' /proc/cpuinfo`));
  $b = strval(count($dun));
  $tmp = explode(" ",file_get_contents('/proc/loadavg'));
  $c = strval($tmp[0]);
  $d = strval($tmp[1]);
  $e = strval($tmp[2]);
  if ($b != 1)
    return("On Linux-compatible host &quot;$a&quot;<br>with $b processors, <b style=\"font-style: normal;\">loads: $c $d $e</b>");
  return("On Linux-compatible host &quot;$a&quot;<br>with $b processor, <b style=\"font-style: normal;\">loads: $c $d $e</b>");
}
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN" "http://www.w3.org/TR/html4/loose.dtd">
<html>
<head>
<meta http-equiv="Content-type" content="text/html;charset=UTF-8">
<title>Liquidsoap Requester</title>
</head>
<body bgcolor="#000000" text="#ffffff" link="lime" alink="lime" vlink="yellow">
<?php
  function soap_req($reqFile) {
    global $ctlPort;
    $fp = stream_socket_client("tcp://localhost:$ctlPort", $errno, $errstr, 20);
    if (!$fp) {
      return("<b><u>TELNET FAILURE:</u> $errstr ($errno)</b><br>");
    } else {
      fwrite($fp, "requests.push ".str_replace("\\'","'",str_replace('&amp;','&',urldecode($reqFile)))."\nquit\n");
      $eat = '';
      while (!feof($fp)) {
        $eat .= fgets($fp, 1024);
      }
      fclose($fp);
      return("<b><u>GREAT:</u> Queued \"".$reqFile."\" for playing!</b><br>");
    }
  }
  function soap_nxt($skipVia) {
    global $ctlPort;
    $fp = stream_socket_client("tcp://localhost:$ctlPort", $errno, $errstr, 20);
    if (!$fp) {
      return("<b><u>TELNET FAILURE:</u> $errstr ($errno)</b><br>");
    } else {
      fwrite($fp, "$skipVia.skip\nquit\n");
      $eat = '';
      while (!feof($fp)) {
        $eat .= fgets($fp, 1024);
      }
      fclose($fp);
      return("<b><u>GREAT:</u> Sent the Telnet command!  If things are setup properly, the next track should begin in just a moment...</b><br>");
    }
  }
  // Hack to allow users to "eek" their way out of bad situations:
  function soap_eek() {
    global $ctlPort;
    $fp = stream_socket_client("tcp://localhost:$ctlPort", $errno, $errstr, 20);
    if (!$fp) {
      return("<b><u>TELNET FAILURE:</u> $errstr ($errno)</b><br>");
    } else {
      fwrite($fp, "list_one.reload\nlist_two.reload\nquit\n");
      $eat = '';
      while (!feof($fp)) {
        $eat .= fgets($fp, 1024);
      }
      fclose($fp);
      return("<b><u>GREAT:</u> Sent the Telnet command!  If things are setup properly, this will hopefully help you out of your situation...</b><br>");
    }
  }
  function stl($aString) {
    return(strtolower($aString));
  }
  
  $msg = '';
  if (isset($_REQUEST['act'])) {
    $act = $_REQUEST['act'];
  } else {
    $act = '';
  }
  if (isset($_REQUEST['que'])) {
    $que = $_REQUEST['que'];
  } else {
    $que = '';
  }
  if (isset($_REQUEST['gus'])) {
    $gus = $_REQUEST['gus'];
  } else {
    $gus = '';
  }
  if (isset($_REQUEST['vue'])) {
    $vue = $_REQUEST['vue'];
  } else {
    $vue = '';
  }
  if ($act != '' && $que != '') {
    if ($act == 'req') {
      $msg .= soap_req($que);
    } else {
      $msg .= '<b><u>ERROR:</u> The Command Given Is Unknown!</b><br>';
    }
  } else {
    if ($act == 'req') {
      $msg .= '<b><u>ERROR:</u> No File Given To Queue!</b><br>';
    } elseif ($act == 'nxt') {
      $msg .= soap_nxt($skipVia);
    } elseif ($act == 'eek') {
      $msg .= soap_eek();
    } else {
      if ($act != '') {
        $msg .= '<b><u>ERROR:</u> The Command Given Is Unknown!</b><br>';
      }
    }
  }
?>
<table width="100%" border="16" cellspacing="4" cellpadding="1">
<tr>
<td valign="middle" align="center">
<h1>Liquidsoap Requester</h1>
<h4 style="font-weight: normal; font-style: italic;"><?php
if (cpu_stat())
  echo(cpu_stat());
if ($useMeta) {
  if (getid3SupportsDbmCache()) {
    echo("<br>\n");
    echo("<br>\n");
    echo("Metadata caching is supported and enabled!");
  } else {
    echo("<br>\n");
    echo("<br>\n");
    echo("Un-cached metadata is supported and enabled!");
  }
}
?></h4>
</td>
<td valign="middle" align="center">
<?php require_once(dirname(__FILE__).'/jumpmenu.php'); echo("<br>\n"); echo("<br>\n"); ?>
<form action="<?php echo(basename(__FILE__)); ?>" method=GET>
  <input type="text" size="32" maxlength="255" name="gus" value="enter search query"><br>
  <input type="submit" name="Search Songs, Artists, and Albums" value="Search Songs, Artists, and Albums">
</form>
</td>
</tr>
<tr>
<?php if ( $microMe || $vue == '' ) { ?>
<td colspan="2" valign="top" align="left">
<?php if ( $microMe ) { ?>
<h2 align="center">SKIP-ONLY MODE</h2>
<?php } else { if ( $vue == '' ) { ?>
<h2 align="center">Select A Track To Queue It:</h2>
<?php } } ?>
</td>
</tr>
<?php
  } // << I know this is confusing, but, it's from the "if ( $microMe || $vue == '' ) {" a few lines up.
  if ($msg != '') {
?>
<tr>
<td colspan="2" valign="top" align="left">
<?php
    echo($msg);
?>
<form action="<?php echo(basename(__FILE__)); ?>" method=POST>
<input type="submit" name="Clear This" value="Clear This">
</form>
</td>
</tr>
<?php
  }

if ( $skipVia != '' && !strstr($skipVia,'.') && $vue == '' ) {
  echo('<tr><td colspan="2" valign="top" align="center">'
    .'<a href="'.basename(__FILE__).'?act=nxt">'
    .'Click Here to Skip the Currently-Playing Track...'
    .'</a></td></tr>'."\n");
}

// Allow Bryan V. to reset things when he buggers up...
if ( $bryanMode ) {
  echo('<tr><td colspan="2" valign="top" align="center">'
    .'<a href="'.basename(__FILE__).'?act=eek">'
    .'Automation Stuck?  Click Here to Attempt a Reset...'
    .'</a></td></tr>'."\n");
}

if ( $microMe ) {
  if ( $skipVia == '' || strstr($skipVia,'.') ) {
    echo('<tr><td colspan="2" valign="top" align="center">'
      .'<b>PROBLEM:</b><br>'
      .'<br>'
      .'SKIP-ONLY MODE is enabled in your configuration file, but either you have not configured the $skipVia variable, or you have specified a Source ID with a period (".") in it, which is something you should not do!<br>'
      .'<br>'
      .'Repair this problem with your configuration, and then reload this page!'
      .'</td></tr>'."\n");
  }
} else {
  if ($vue == 'php') {
    require_once(dirname(__FILE__).'/view_php.php');
  } else {
    $desired_extension = 'mp3'; //extension we're looking for 
    $dirname = "$mReadIn";
    if ($mSource == 'flatpath') { 
      $dir = opendir($dirname);
      while(false !== ($file = readdir($dir))) { 
        if(($file != ".") and ($file != "..")) { 
          $fileChunks = explode('.',$file);
          if(strtolower($fileChunks[1]) == strtolower($desired_extension)) { //interested in second chunk only
            $song = req_meta($dirname.'/'.$file);
            // A few hacks to escape special characters properly
            // for maintaining HTML4 compliance:
            $file = str_replace('&','&amp;',$file);
            $had = FALSE;
            if ($gus == '')
              echo '<tr><td colspan="2" valign="top" align="left"><a href="'.basename(__FILE__).'?act=req&amp;que='.urlencode($dirname.$file).'">'.str_replace(' ','&nbsp;',$file).'</a><br><br>'.$song.'</td></tr>'."\n"; 
            if ($gus != '' && strstr(stl($file),stl($gus))) {
              echo '<tr><td colspan="2" valign="top" align="left"><a href="'.basename(__FILE__).'?act=req&amp;que='.urlencode($dirname.$file).'">'.str_replace(' ','&nbsp;',$file).'</a><br><br>'.$song.'</td></tr>'."\n"; 
              $had = TRUE;
            }
            if ($had != TRUE && $gus != '' && strstr(stl($song),stl($gus)))
              echo '<tr><td colspan="2" valign="top" align="left"><a href="'.basename(__FILE__).'?act=req&amp;que='.urlencode($dirname.$file).'">'.str_replace(' ','&nbsp;',$file).'</a><br><br>"'.$song.'"</td></tr>'."\n";
          }
        }
      }
      closedir($dir);
    } else {
      $list = file($mReadIn,FILE_IGNORE_NEW_LINES|FILE_SKIP_EMPTY_LINES);
      if ( $list ) {
        echo '<tr><td colspan="2" valign="top" align="left">'.count( $list )." files found in &quot;$mReadIn&quot;".'</td></tr>'."\n"; 
        foreach ($list as $file) {
          $song = req_meta($file);
          // A few hacks to escape special characters properly
          // for maintaining HTML4 compliance:
          $file = str_replace('&','&amp;',$file);
          $had = FALSE;
          if ($gus == '')
            echo '<tr><td colspan="2" valign="top" align="left"><a href="'.basename(__FILE__).'?act=req&amp;que='.urlencode($file).'">'.str_replace(' ','&nbsp;',basename($file)).'</a><br><br>'.$song.'</td></tr>'."\n"; 
          if ($gus != '' && strstr(stl($file),stl($gus))) {
            echo '<tr><td colspan="2" valign="top" align="left"><a href="'.basename(__FILE__).'?act=req&amp;que='.urlencode($file).'">'.str_replace(' ','&nbsp;',basename($file)).'</a><br><br>'.$song.'</td></tr>'."\n"; 
            $had = TRUE;
          }
          if ($had != TRUE && $gus != '' && strstr(stl($song),stl($gus)))
            echo '<tr><td colspan="2" valign="top" align="left"><a href="'.basename(__FILE__).'?act=req&amp;que='.urlencode($file).'">'.str_replace(' ','&nbsp;',basename($file)).'</a><br><br>"'.$song.'"</td></tr>'."\n";
        }
      } else {
        echo("<strong>PROBLEM:</strong><br><br>Unable to read the playlist file at &quot;$mReadIn&quot;!");
      }
    }
  }
}
?>
<tr>
<td colspan="2" valign="middle" align="center">
<p>This script is a product of<br>
<a href="http://www.quinnebert.net/">Quinn Ebert</a> Software Creations</p>
<p><a rel="license" href="http://creativecommons.org/licenses/by-nc-sa/3.0/us/"><img alt="Creative Commons License" style="border-width:0" src="http://creativecommons.org/images/public/somerights20.png"></a><br><span>Liquidsoap Requester</span> by <a href="http://www.quinnebert.net/" rel="cc:attributionURL">Quinn Ebert</a><br>is licensed under a <a rel="license" href="http://creativecommons.org/licenses/by-nc-sa/3.0/us/">Creative Commons<br>Attribution-Noncommercial-Share Alike 3.0<br>United States License</a></p>
<p>
  This script aims to produce W3C-Compliant<br>
  <a href="http://validator.w3.org/check?uri=referer"><img border="0" src="http://www.w3.org/Icons/valid-html401-blue" alt="Valid HTML 4.01 Transitional" height="31" width="88"></a><br>
  <a href="http://validator.w3.org/check?uri=referer">Valid HTML 4.01 Transitional</a> Code<br>
  If you <i><u>ever</u></i> notice this script falling out-of-standard<br>
  <i><u>please report your incident</u></i> at <a href="http://bug.quinnebert.net/">http://bug.quinnebert.net/</a>
</p>
</td>
</tr>
</table>
</body>
</html>
<?php
  // My work here is done!
  die();
