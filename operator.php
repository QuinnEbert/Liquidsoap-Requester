<?php
  // Ha!  You thought this might do something interesting?
  // 
  // Sorry to disappoint, but, this really just lets you see what IP address
  // the server sees you as coming in from.  It can be helpful for testing your
  // $myUsers setting, however.
  // 
  // KEEP AN EYE OUT -- More useful diagnostic stuff will probably show up here
  // in the future!
?>
<html>
<head>
<title>Testing Operator</title>
</head>
<body>
<h1>Testing Operator</h1>
<hr />
Your IP address currently reads as:<br />
<pre><?php echO($_SERVER['REMOTE_ADDR']); ?></pre>
<hr />
<address>
  For Liquidsoap Requester by <a href="http://www.quinnebert.net/">Quinn
  Ebert</a>
</address>
</body>
</html>
