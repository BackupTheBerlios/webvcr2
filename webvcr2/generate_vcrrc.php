<?php
 // $Id: generate_vcrrc.php,v 1.1 2002/02/09 17:45:18 waldb Exp $
 // $Author: waldb $

include("lib/functions.php");

print_header_open();
print_title ("Generate .vcrrc");
print_header_close();

$dotfile = new vcrrc ("/var/www");
if ($dotfile->generate()) {
	print "~/.vcrrc successfully generated.<P>\n";
} else {
	print "~/.vcrrc generation failed.<P>\n";
}

print_page_close();

?>
