<?php
    // echo $request;
    // $output = shell_exec('top -bn 1 | grep "Cpu(s)"');
    // echo "CPU Usage: " . $output;
use model\Logs\Log;

    $route_end = microtime(true);
    $duration =  $route_end - $route_start;
    if($duration >= getenv('TIME-COMPLEXITY-LOG')):
        $log = "Route: {$request} - Duration: {$duration} seconds";
        (new Log)->__log_custom_file($log, "request_time_complexity.log");
        
        //Log expensive requests in a file. Logging to the database may be unreliable
    endif;
?>