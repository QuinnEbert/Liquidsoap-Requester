<!-- This script and many more are available free online at -->
<!-- The JavaScript Source!! http://javascript.internet.com -->
<!-- Original:  Vladimir Geshanov                           -->
<!-- Web Site:  http://hotarea.com/                         -->
<script language="JavaScript"> 
<!--
function openDir( jumpmenu ) { 
	var newIndex = jumpmenu.dropmenu.selectedIndex; 
	if ( newIndex == 0 ) { 
		alert( "Please select a location!  :-(" ); 
	} else if ( jumpmenu.dropmenu.options[ newIndex ].value == "SEP_ITEM" ) { 
		alert( "Selecting a menu seperator won't get you anywhere!  ;-)" ); 
	} else { 
		cururl = jumpmenu.dropmenu.options[ newIndex ].value; 
		window.location.assign( cururl ); 
	} 
} 
 -->
</script> 
<form name=jumpmenu> 
	<select name="dropmenu" size="1"> 
		<option>Go To View / Feature (select one)</option>
		<option value="soap_req.php">Main Page (with Media List)</option>
		<option value="SEP_ITEM">--------------------</option>
		<option value="soap_req.php?vue=php">PHP Software Information</option>
	</select> <input type="button" name="Let's Go" value="Let's Go" onClick="openDir( this.form )"> 
</form> 
