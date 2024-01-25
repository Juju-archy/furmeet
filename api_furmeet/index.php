<?php

if(isset($_GET['api']))
{
    $api = $_GET['api'];

    switch($ex)
    {
        case 1:
            include("user_api.php");
            break;
        case 2:
            include("poire.php");
            break;
        case 3:
            include("peche.php");
            break;
        case 4:
            include("fraise.php");
            break;
        case 5:
            include("bonus.php");
            break;
    }
}
else
{
    echo 'Hello World!';
}