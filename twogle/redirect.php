<?php
//href="redirect.php?category=[category]&url=[url]"
include("Model2.php");
session_start();
$Category = $_SESSION['category'];

$Domain = isolateDomain($_GET['url']);

foreach($Category as $eachCategory) {

    increment($eachCategory, $Domain);
}
echo '<script type="text/javascript">
           window.location = "'.$_GET['url'].'"
      </script>';
?>
