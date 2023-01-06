<?php

chdir(dirname(__FILE__) . '/../');

require_once('common.php');
require_once('import/Csv.class.php');

$columns = 'FID,OBJECTID,SHAPE,BAUM_ID,DATENFUEHRUNG,BEZIRK,OBJEKT_STRASSE,GEBIETSGRUPPE,GATTUNG_ART,PFLANZJAHR,PFLANZJAHR_TXT,STAMMUMFANG,STAMMUMFANG_TXT,BAUMHOEHE,BAUMHOEHE_TXT,KRONENDURCHMESSER,KRONENDURCHMESSER_TXT,BAUMNUMMER,SE_ANNO_CAD_DATA,lon,lat,source,outdated';

log_info("[Wien] Downloading data");

$data = download_url('https://data.wien.gv.at/daten/geo?service=WFS&request=GetFeature&version=1.1.0&typeName=ogdwien:BAUMKATOGD&srsName=EPSG:4326&outputFormat=csv');
if($data === false) {
	log_info('[Wien] Could not download data, aborting');
	$error = 1;
}
else {
	$csv = new Csv();
	log_info("[Wien] Parsing data");
	$csv->parse_data($data);

	$columns_array = explode(',', $columns);
	$placeholders = preg_replace('/[^,]+/', '?', $columns);

	$query = "INSERT INTO baumkataster ($columns) VALUES ($placeholders) ON DUPLICATE KEY UPDATE ";
	$query .= implode(', ', array_map(function($column) { return "$column = ?"; }, explode(',', $columns)));

	log_info("[Wien] Importing data");

	foreach($csv->rows as $row) {
		if($row[0] == 'FID') {
			continue;
		}

		$shape = $row[2];
		$shape = substr($shape, strpos($shape, '(')+1);
		$shape = substr($shape, 0, strlen($shape)-1);

		$parts = explode(' ', $shape);
		$row[] = trim($parts[0]);
		$row[] = trim($parts[1]);

		$row[] = 'WIEN';
		$row[] = '0';

		$columns_count = count($row);
		for($a=0; $a<$columns_count; $a++) {
			$row[] = $row[$a];
		}

		db_query($query, $row);
	}

	log_info("[Wien] Done");
}

