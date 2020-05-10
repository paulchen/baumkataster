<?php

chdir(dirname(__FILE__));

require_once('common.php');
require_once('Csv.class.php');

$columns = 'FID,OBJECTID,SHAPE,BAUM_ID,DATENFUEHRUNG,BEZIRK,OBJEKT_STRASSE,GEBIETSGRUPPE,GATTUNG_ART,PFLANZJAHR,PFLANZJAHR_TXT,STAMMUMFANG,STAMMUMFANG_TXT,BAUMHOEHE,BAUMHOEHE_TXT,KRONENDURCHMESSER,KRONENDURCHMESSER_TXT,BAUMNUMMER,SE_ANNO_CAD_DATA,lon,lat';

$csv = new Csv();
$csv->parse('BAUMKATOGD.csv');
#$csv->parse('test.csv');

$query = "INSERT INTO baumkataster ($columns) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";

db_query('TRUNCATE TABLE baumkataster');

$db->beginTransaction();
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

	db_query($query, $row);
}
$db->commit();

