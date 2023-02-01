<?php
spl_autoload_register(function ($class) {
    @include __DIR__
           . "/lib/"
           . strtolower(preg_replace(["/([a-z])([A-Z])/", "/\\\\/"], ['$1_$2', "/"], $class))
           . ".php";
});

function base64url_encode(string $str): string {
    return str_replace(["+", "/", "="], ["-", "_", ""], base64_encode($str));
}

function base64url_decode(string $str): string {
    return base64_decode(str_replace(["-", "_"], ["+", "/"], $str));
}

function unique_id(int $length = 6): string {
    return random_bytes($length);
}

function dbg($x) {
    $backtrace = debug_backtrace(DEBUG_BACKTRACE_PROVIDE_OBJECT, 2);
    $bt = $backtrace[0];
    $file = file($bt["file"]);
    preg_match("/dbg(\((?:[^)(]+|(?1))*+\))/",
               $file[$bt["line"] - 1], $expr);
    error_log("Debug: $bt[file]:$bt[line]: $expr[1] = " . print_r($x, true));
    return $x;
}
?>
