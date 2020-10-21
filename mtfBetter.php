<?php
    ini_set("display_errors", "On");
    error_reporting(E_ALL);

    if (!empty($_SERVER['QUERY_STRING'])) {
        parse_str($_SERVER['QUERY_STRING'], $_conf);
        include 'mtfBetter.class.php';
        $mtfBetter = new mtfBetter($_conf);
        $mtfBetter->handler();
    }
?>