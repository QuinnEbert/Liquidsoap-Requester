<?php
/* 
** Code lib part of Liquidsoap Requester
** by Quinn Ebert
** on January 13, 2011 at 12:54:40PM
*/

/* Function that returns TRUE if it will be possible to use **
** the GetID3 DBM module:                                   */
function getid3SupportsDbmCache() {
  $dbmIsAt = dirname(dirname(__FILE__)).'/php-getid3/getid3/extension.cache.dbm.php';
  if (strlen(strstr($dbmIsAt,':')) == (strlen($dbmIsAt)-1))
    $dbmIsAt = str_replace('/',"\\",$dbmIsAt);
  // A messy "first chance" catch -- No presence of "dba_handlers()" functions immediately means
  // we won't have any DBM support available:
  if (!function_exists('dba_handlers'))
    //die("<pre>ISSUE: no function 'dba_handlers'</pre>");
    return( False );
  // The below reference to "in_array('db3', dba_handlers())" is messy, but
  // it is needed to keep getID3 from being referenced, and then throwing its
  // own error...
  if (file_exists($dbmIsAt) && is_readable($dbmIsAt) && in_array('db4', dba_handlers())) {
    return( True  );
  } else {
    // ... DEBUG STUFF ...
    /*if (!file_exists($dbmIsAt))
      die("<pre>ISSUE: file $dbmIsAt not existing!</pre>");
    if (!is_readable($dbmIsAt))
      die("<pre>ISSUE: file $dbmIsAt not readable!</pre>");
    if (!in_array('db3', dba_handlers()))
      die("<pre>ISSUE: db3 support is not useable!</pre>");*/
    // Nothin' Doin' Lucy!  :(
    return( False );
  }
}


?>