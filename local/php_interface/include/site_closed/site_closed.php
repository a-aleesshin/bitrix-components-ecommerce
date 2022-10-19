<?php

header('HTTP/1.1 503 Service Temporarily Unavailable');
header('Status: 503 Service Temporarily Unavailable');
header('Retry-After: 86400');

?><!DOCTYPE html>
<html>
<head>
    <title>Технические работы</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8">
    <link rel="stylesheet" href="//fonts.googleapis.com/css?family=Montserrat:400,700" type="text/css" media="all">
    <link rel="stylesheet" href="/local/php_interface/include/site_closed/style.css?<?=time()?>" type="text/css" media="all">
</head>
<body>

<main>
    <h1>На сайте ведутся<br> технические работы</h1>
    <p>Уже скоро мы снова будем  с вами.<br><br> <span class="time"><?=date('c')?></span></p>
</main>

</body>
</html>