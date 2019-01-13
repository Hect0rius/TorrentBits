<?php

function mysql_connect($host, $user, $password, $database, $port) {
    $DB = mysqli_connect($host, $user, $password, $database, $port, $socket);
    
    if(!is_object($DB)) { 
        die("Could not connect to the database.");
    }
    return $DB;
}
function mysql_errno() {
    global $TBDEV;
    
    return mysqli_errno($TBDEV['DB']);
}
function mysql_set_charset($Charset) {
    global $TBDEV;
    mysqli_set_charset($TBDEV['DB'], $Charset);
}
function mysql_real_escape_string($str) {
    global $TBDEV;
    return mysqli_real_escape_string($TBDEV['DB'], $str);
}
function mysql_fetch_row($res) {
    return mysqli_fetch_row($res);
}
function mysql_query($query) {
    global $TBDEV;
    return mysqli_query($TBDEV['DB'], $query);
}
function mysql_fetch_assoc($res) {
    return mysqli_fetch_assoc($res);
}
function mysql_fetch_array($res) {
    return mysqli_fetch_array($res);
}
function mysql_affected_rows() {
    global $TBDEV;
    return mysqli_affected_rows($TBDEV['DB']);
}
function mysql_num_rows($Res) {
    return mysqli_num_rows($Res);
}