<?php

include_once('db.php');
include_once('template.php');

//if ($LM_DEBUG==TRUE) error_reporting(E_ALL ^ E_NOTICE); else error_reporting(0);
error_reporting(E_ALL ^ E_NOTICE); // since errors are now handled by a custom handler, there is no need to use error_reporting(0);


function lmeveCrashHandler() {
    $error_info = error_get_last();
    $FATAL = E_ERROR | E_PARSE | E_CORE_ERROR | E_CORE_WARNING | E_COMPILE_ERROR | E_COMPILE_WARNING;
    if ($error_info !== null && ($error_info['type'] & $FATAL)) {
        # stack trace set to empty array, as generating one here is useless
        lmeveErrorHandler(E_USER_ERROR, $error_info['message'], $error_info['file'], $error_info['line']);
    }
}

function lmeveErrorHandler($errno, $errstr, $errfile, $errline) {
    //echo("lmeveErrorHandler($errno, $errstr, $errfile, $errline)");
    
    global $LM_APP_NAME, $LM_DEBUG;

    /*if (!(error_reporting() & $errno)) {
        // This error code is not included in error_reporting, so let it fall
        // through to the standard PHP error handler
        return false;
    }*/
    
    $logmsg = '';
    $id = uniqid();
    $stacktrace = get_caller_info();

    switch ($errno) {
    case E_USER_ERROR:
        $logmsg .= date(DATE_W3C) . " $id $LM_APP_NAME ERROR [$errno] $errstr<br />\n";
        $logmsg .=  "Fatal error on line $errline in file $errfile\n\n";
        $logmsg .=  "$stacktrace\n";
        error_log($logmsg);
        ob_clean();
        $content = "<h3>Last known K162 exit is <strong>$id</strong></h3>";
        if ($LM_DEBUG == TRUE) $content .= '<p>Additional details are available because LM_DEBUG flag is set to TRUE:</p><pre>' . $logmsg . '</pre>';
        template_error(generate_meta("$LM_APP_NAME - Error $id", "$LM_APP_NAME - Error"), $content);
        exit(1);
        break;

    case E_USER_WARNING:
        $logmsg .= date(DATE_W3C) . " $id $LM_APP_NAME WARNING [$errno] $errstr<br />\n";
        $logmsg .=  "  Warning on line $errline in file $errfile\n";
        error_log($logmsg);
        break;

    case E_USER_NOTICE:
        if ($LM_DEBUG == TRUE) {
            $logmsg .= date(DATE_W3C) . " $id $LM_APP_NAME NOTICE [$errno] $errstr<br />\n";
            $logmsg .=  "  Notice on line $errline in file $errfile\n";
            error_log($logmsg);
        }
        break;
    
    case E_NOTICE:
        if ($LM_DEBUG == TRUE) {
            $logmsg .= date(DATE_W3C) . " $id $LM_APP_NAME NOTICE [$errno] $errstr<br />\n";
            $logmsg .=  "  Notice on line $errline in file $errfile\n";
            error_log($logmsg);
        }
        break;
    
    default:
        $logmsg .= date(DATE_W3C) . " $id $LM_APP_NAME ERROR [$errno] $errstr<br />\n";
        $logmsg .=  "  Unknown error on line $errline in file $errfile\n";
        error_log($logmsg);
        break;
    }
    

    /* Don't execute PHP internal error handler */
    return true;
}

function get_caller_info() {
    ob_start();
    debug_print_backtrace();
    $trace = ob_get_contents();
    ob_end_clean();

    // Remove first item from backtrace as it's this function which
    // is redundant.
    $trace = preg_replace ('/^#0\s+' . __FUNCTION__ . "[^\n]*\n/", '', $trace, 1);

    // Renumber backtrace items.
    $trace = preg_replace ('/^#(\d+)/m', '\'#\' . ($1 - 1)', $trace);

    return $trace; 
}

set_error_handler("lmeveErrorHandler");
register_shutdown_function('lmeveCrashHandler');