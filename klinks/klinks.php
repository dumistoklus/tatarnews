<div class="klinks">
<?php 
include(dirname(__FILE__)."/functions.inc.php");
echo kl_getlinks($_SERVER['REQUEST_URI'], $domain);
if (isset($_GET['kdebug'])) {
    echo "<!--";
    print_r($_SERVER);
    echo "-->";
}
?>
</div>