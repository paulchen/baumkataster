<?php

chdir(dirname(__FILE__));

require_once('common.php');
require_once('Csv.class.php');

$columns = 'FID,OBJECTID,SHAPE,BAUM_ID,DATENFUEHRUNG,BEZIRK,OBJEKT_STRASSE,GEBIETSGRUPPE,GATTUNG_ART,PFLANZJAHR,PFLANZJAHR_TXT,STAMMUMFANG,STAMMUMFANG_TXT,BAUMHOEHE,BAUMHOEHE_TXT,KRONENDURCHMESSER,KRONENDURCHMESSER_TXT,BAUMNUMMER,SE_ANNO_CAD_DATA,lon,lat';

$csv = new Csv();
$csv->parse('BAUMKATOGD.csv');
#$csv->parse('test.csv');

$columns_array = explode(',', $columns);
$placeholders = preg_replace('/[^,]+/', '?', $columns);

$query = "INSERT INTO baumkataster ($columns) VALUES ($placeholders) ON DUPLICATE KEY UPDATE ";
$query .= implode(', ', array_map(function($column) { return "$column = ?"; }, explode(',', $columns)));

$db->beginTransaction();
$index = 0;
foreach($csv->rows as $row) {
	$index++;
	print("$index\n");
	if($row[0] == 'FID') {
		continue;
	}

	$shape = $row[2];
	$shape = substr($shape, strpos($shape, '(')+1);
	$shape = substr($shape, 0, strlen($shape)-1);

	$parts = explode(' ', $shape);
	$row[] = trim($parts[0]);
	$row[] = trim($parts[1]);

	$columns_count = count($row);
	for($a=0; $a<$columns_count; $a++) {
		$row[] = $row[$a];
	}

	db_query($query, $row);
}
$db->commit();

