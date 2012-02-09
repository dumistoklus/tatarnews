<?php
/*
 * http://192.168.0.85/pma
 */

$username = "root";
$password = "876abRGA";
$host = '192.168.0.123';
$db_name = "tatarnews.ru";
define("PREFIX", "fms_");

function db_connect($host, $db_name, $user, $pass) {
        
	$connect = @mysql_connect($host, $user, $pass);
	$select = @mysql_select_db($db_name);
	mysql_query("SET NAMES utf8");
	if(!$connect) {
		return "NOT_CONNECTED";
	}
	elseif (!$select) {
		return "NOT_SELECTED_DB";
	}
}

db_connect($host, $db_name, $username, $password);

function query($sql, $where = '') {	
	
	$s_time = mctime();
	$result = mysql_query($sql);
	$e_time = mctime() - $s_time;
	
	Logger::append_sql($sql, $where, $e_time);
	return $result;
}

function get_bd_data($sql) {
	$result = query($sql, __FUNCTION__.':'.__FILE__.':'.__LINE__);

	$array = array();
	if($result) {
		while ($data = mysql_fetch_assoc($result)){
			$array[] = $data;			
		} 	
	}
	else Logger::append_error(mysql_error(),  __FILE__.':'.__FUNCTION__.':'.__LINE__);

	return $array;
}

function affectedRowsQuery($sql) {
	
	$result = query($sql, __FUNCTION__.':'.__FILE__);
	
	if($result) {
		return mysql_affected_rows();
	}
	
	return 0;
}

function filter_string($string)
{
    $string = filter_var($string, FILTER_SANITIZE_STRING);
    $string = strip_tags($string);
    $string = htmlspecialchars($string, ENT_QUOTES);
    return mysql_real_escape_string($string);
}

function filter_mysql($string)
{
    $string = strip_tags($string, '<b><p><a><i><u><strong><div><br><del><s><sub><sup><li><ul><em><style><img><ol><span>');
    return mysql_real_escape_string($string);
}

function filter_html_text($string) {
    // $string = filter_var($string, FILTER_SANITIZE_STRING);
    $string = strip_tags($string, '<a><p><ul><li><table><tr><th><td><span><i><b><img><div><hr><font><br><em><strong><s><u>');
    return mysql_real_escape_string($string);
}
