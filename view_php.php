<?php
  /*
     "View" to show PHP software information
     
     Part of Liquidsoap requester by Quinn Ebert
     <http://www.quinnebert.net>
  */
      if (isset($_SERVER) && is_array($_SERVER)) {
        ?>
        <tr><td colspan="2" valign="top" align="center">
        <h3>Web Server Information:</h3>
          <table width="75%" border="4" cellspacing="1" cellpadding="4"><?php
            foreach ($_SERVER as $key => $val) {
              // weed out some stuff that looks crappy...
              if (is_array($val) || (stl($key) == $key)) continue;
              // handle some stuff from Apache $_SERVER's that breaks HTML/4.01
              $val = str_replace('</address>','',$val);
              $val = str_replace('</ADDRESS>','',$val);
              $val = str_replace('<address>','',$val);
              $val = str_replace('<ADDRESS>','',$val);
              ?>
            <tr>
              <td width="15%" valign="middle" align="center"><b><?php echo($key); ?></b></td>
              <td width="85%" valign="middle" align="center"><font face="Lucida Console, Courier New, Courier, Monaco"><?php echo($val); ?></font></td>
            </tr><?php } ?>
          </table>
          <h3>&nbsp;</h3>
        </td></tr>
        <?php
      }
