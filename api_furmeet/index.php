<?php

if(isset($_GET['ex']))
{
    $ex = $_GET['ex'];

    switch($ex)
    {
        case 1:
            include("cerise.php");
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