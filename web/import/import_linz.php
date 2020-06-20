<?php

chdir(dirname(__FILE__));

require_once('../common.php');
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

function get_height_index($height) {
	$height = ceil($height/5);
	return min($height, 8);
}

function get_treetop_diameter($diameter) {
	$diameter = ceil($diameter/3);
	return min($diameter, 8);
}

$columns = 'BAUM_ID,GATTUNG_ART,STAMMUMFANG,STAMMUMFANG_TXT,BAUMHOEHE,BAUMHOEHE_TXT,KRONENDURCHMESSER,KRONENDURCHMESSER_TXT,BAUMNUMMER,lon,lat,source';

echo("Downloading data\n");
$data = file_get_contents('http://data.linz.gv.at/katalog/umwelt/baumkataster/2020/FME_BaumdatenBearbeitet_OGD_20200225.csv');
if(!$data) {
	echo("Error downloading data\n");
	die(1);
}
$data = iconv('ISO-8859-1', 'UTF-8', $data);

echo("Parsing data\n");

$csv = new Csv();
$csv->separator = ';';
$csv->parse_data($data);
unset($data);

$columns_array = explode(',', $columns);
$placeholders = preg_replace('/[^,]+/', '?', $columns);

$query = "INSERT INTO baumkataster ($columns) VALUES ($placeholders) ON DUPLICATE KEY UPDATE ";
$query .= implode(', ', array_map(function($column) { return "$column = ?"; }, explode(',', $columns)));

echo("Importing data\n");

$db->beginTransaction();
foreach($csv->rows as $row) {
	if($row[0] == 'Flaeche') {
		continue;
	}

	$new = array();
	$new[] = $row[10] . $row[11];
	$new[] = create_name($row[2], $row[3], $row[4], $row[5]);
	$new[] = $row[8];
	$new[] = $row[8] . ' cm';
	$new[] = get_height_index($row[6]);
	$new[] = $row[6] . ' m';
	$new[] = get_treetop_diameter($row[7]);
	$new[] = $row[7] . ' m';
	$new[] = $row[1];
	$new[] = $row[12];
	$new[] = $row[13];
	$new[] = 'LINZ';

	$columns_count = count($new);
	for($a=0; $a<$columns_count; $a++) {
		$new[] = $new[$a];
	}

	db_query($query, $new);
}
$db->commit();

touch($status_file);

echo("Done\n");

