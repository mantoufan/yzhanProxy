<?php
    ini_set("display_errors", "On");
    error_reporting(E_ALL);
    include 'mtfBetter.class.php';
    $mtfBetter = new mtfBetter();

    parse_str($_SERVER['QUERY_STRING'], $arv);
    $mtfBetter->handler($_arv);
?>