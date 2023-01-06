<?php

chdir(dirname(__FILE__));

require_once('config.php');

$db = new PDO("mysql:dbname=$db_name;host=$db_host", $db_user, $db_password);
$db->setAttribute(PDO::ATTR_AUTOCOMMIT, false);
db_query('SET NAMES utf8');

unset($db_name);
unset($db_host);

function log_info($message) {
	global $log;

	if(!isset($log)) {
		$log = fopen('log/baumkataster.log', 'a');
	}

	$date = date('[Y-m-d H:i:s]');
	fprintf($log, "$date - $message\n");
}

function db_query_single($query, $parameters = array()) {
	$data = db_query($query, $parameters);
	if(count($data) == 0) {
		return null;
	}
	if(count($data) > 1) {
		// TODO
	}
	return $data[0];
}

function db_query($query, $parameters = array()) {
	$stmt = db_query_resultset($query, $parameters);
	$data = $stmt->fetchAll(PDO::FETCH_ASSOC);
	db_stmt_close($stmt);
	return $data;
}

function db_stmt_close($stmt) {
	if(!$stmt->closeCursor()) {
		$error = $stmt->errorInfo();
		db_error($error[2], debug_backtrace(), $query, $parameters);
	}
}

function db_query_resultset($query, $parameters = array()) {
	global $db;

	$query_start = microtime(true);
	if(!($stmt = $db->prepare($query))) {
		$error = $db->errorInfo();
		db_error($error[2], debug_backtrace(), $query, $parameters);
	}
	foreach($parameters as $key => $value) {
		$stmt->bindValue($key+1, $value);
	}
	if(!$stmt->execute()) {
		$error = $stmt->errorInfo();
		db_error($error[2], debug_backtrace(), $query, $parameters);
	}

	return $stmt;
}

function db_error($error, $stacktrace, $query, $parameters) {
	global $config;

	$stacktrace_string = print_r($stacktrace, true);
	$parameters_string = print_r($parameters, true);

	$message = "Database error:\n\n$error\n\n$stacktrace_string\n\n$query\n\n$parameters_string";
	log_info($message);

	header('HTTP/1.1 500 Internal Server Error');
	echo "A database error has occurred.\n";
	die(1);
}

function get_height_index($height) {
	$height = ceil($height/5);
	return min($height, 8);
}

function get_treetop_diameter($diameter) {
	$diameter = ceil($diameter/3);
	return min($diameter, 8);
}

function download_url($url) {
	$delay = 5;
	$retries = 5;
	for($i=0; $i<$retries; $i++) {
		$ch = curl_init();
		curl_setopt($ch, CURLOPT_HEADER, false);
		curl_setopt($ch, CURLOPT_URL, $url);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
		$data = curl_exec($ch);

		if($data) {
			curl_close($ch);
			return $data;
		}

		$error = curl_error($ch);
		curl_close($ch);
		log_info("Unable to download $url ($error), retrying in $delay seconds...");
		sleep($delay);
	}
	log_info("Unable to download $url, giving up now");
	return false;
}

