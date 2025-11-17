<?php

    function autoload_one($class_name)
    {
        include_once str_replace("\\", "/", $class_name). ".php";
    }

    spl_autoload_register('autoload_one');

?>