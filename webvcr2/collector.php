<?php
 // $Id: collector.php,v 1.1 2002/02/09 17:45:17 waldb Exp $
 // $Author: waldb $

include("lib/functions.php");
print_header_open();
print_title ("Collector Configuration");
print_header_close();

?>

<FORM ACTION="config.php" METHOD=POST>
<center>
<table border=1><tr bgcolor="#006666"><td><center><font color="#FFFFFF">Configuration</font></center></td></tr><tr bgcolor="#E0E0E0"><td>
<table border=0>
<?php
 // $Id: collector.php,v 1.1 2002/02/09 17:45:17 waldb Exp $
 // $Author: waldb $

include "lib/module.php";
include "lib/module_collector.php";

$template = "
	<TR>
		<TD>
			<A HREF=\"module_loader.php?module=#class#\"
			>#name#</A>
		</TD>
	</TR>

";
$module_list = new module_list ("WebVCR", ".collector.module.php");
echo $module_list->generate_list("Collector", 0, $template);

/*
$result = $sql->query("SELECT name, collectorid FROM collector");
if (!$sql->results($result)) {
	print "
		<tr><td align=center>
			<I>No collectors listed.</I>
		</td></tr>
	";
} else {
	while($blah = $sql->fetch_array($result)) {
		print "<tr><td>Run <a href=$blah[0].php>$blah[0]</a> Collector</td></tr>\n";
	}
}
*/

?>
</TABLE>
</table>
</center>
</FORM>
<?php print_page_close(); ?>

