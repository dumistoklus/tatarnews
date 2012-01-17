<?php
if (isset($_GET['phpinfo'])) {phpinfo(); die();}
if (!isset($_GET['connection_type'])) die('Not enough params');
include(dirname(__FILE__)."/functions.inc.php");
kl_getdata($domain, $_GET['connection_type']);
?>