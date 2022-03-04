<?php

chdir(dirname(__FILE__) . '/../');

require_once('common.php');
require_once('import/Csv.class.php');

function create_name($gattung, $art, $sorte, $name_deutsch) {
	$text = $gattung;
	if(trim($art) != '' && trim($art) != '-') {
		$text .= " $art";
	}
	if(trim($sorte) != '' && trim($sorte) != '-') {
		$text .= " $sorte";
	}
	if(trim($name_deutsch) != '' && trim($name_deutsch) != '-') {
		$text .= " ($name_deutsch)";
	}
	return $text;
}

function get_data_url($id) {
	$url = "https://www.data.gv.at/katalog/api/3/action/package_show?id=$id";

	$data = @file_get_contents($url);
	$json = @json_decode($data, true);

	if ($data === false || $json == null || !isset($json['result']) || !isset($json['result']['resources']) || !is_array($json['result']['resources']) 
			|| count($json['result']['resources']) == 0 || !isset($json['result']['resources'][0]['url'])) {
		return false;
	}
	return $json['result']['resources'][0]['url'];
}

$columns = 'BAUM_ID,GATTUNG_ART,STAMMUMFANG,STAMMUMFANG_TXT,BAUMHOEHE,BAUMHOEHE_TXT,KRONENDURCHMESSER,KRONENDURCHMESSER_TXT,BAUMNUMMER,lon,lat,source,outdated';

log_info("[Linz] Downloading metadata");
$url = get_data_url('f660cf3f-afa9-4816-aafb-0098a36ca57d');
if($url === false) {
	$data = false;
	log_info("[Linz] Error downloading metadata");
}
else {
	log_info("[Linz] Downloading data from URL: $url");
	$data = file_get_contents($url);
}
if($data === false) {
	$error = 1;
	log_info("[Linz] Error downloading data");
}
else {
	$data = iconv('ISO-8859-1', 'UTF-8', $data);

	log_info("[Linz] Parsing data");

	$csv = new Csv();
	$csv->separator = ';';
	$csv->parse_data($data);
	unset($data);

	$columns_array = explode(',', $columns);
	$placeholders = preg_replace('/[^,]+/', '?', $columns);

	$query = "INSERT INTO baumkataster ($columns) VALUES ($placeholders) ON DUPLICATE KEY UPDATE ";
	$query .= implode(', ', array_map(function($column) { return "$column = ?"; }, explode(',', $columns)));

	log_info("[Linz] Importing data");

	foreach($csv->rows as $row) {
		if($row[0] == 'Flaeche') {
			continue;
		}

		$new = array();
		$new[] = $row[10] . $row[11];
		$new[] = create_name($row[2], $row[3], $row[4], $row[5]);
		$new[] = $row[8];
		$new[] = $row[8] . ' cm';
		if (trim($row[6]) != '') {
			$new[] = get_height_index($row[6]);
			$new[] = $row[6] . ' m';
		}
		else {
			$new[] = '0';
			$new[] = '';
		}
		if (trim($row[7]) != '') {
			$new[] = get_treetop_diameter($row[7]);
			$new[] = $row[7] . ' m';
		}
		else {
			$new[] = '0';
			$new[] = '';
		}
		$new[] = $row[1];
		$new[] = $row[12];
		$new[] = $row[13];
		$new[] = 'LINZ';
		$new[] = 0;

		$columns_count = count($new);
		for($a=0; $a<$columns_count; $a++) {
			$new[] = $new[$a];
		}

		db_query($query, $new);
	}

	log_info("[Linz] Done");
}

