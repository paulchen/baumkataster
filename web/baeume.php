<?php

chdir(dirname(__FILE__));

require_once('common.php');

if(!isset($_REQUEST['bbox'])) {
	die();
}

$bbox = explode(',', $_REQUEST['bbox']);
#$bbox = explode(',', '48.19011986526799,16.367147527635098,48.19378203622784,16.37101024389267');
if(count($bbox) != 4) {
	die();
}

$columns = 'FID,OBJECTID,SHAPE,BAUM_ID,DATENFUEHRUNG,BEZIRK,OBJEKT_STRASSE,GEBIETSGRUPPE,GATTUNG_ART,PFLANZJAHR,PFLANZJAHR_TXT,STAMMUMFANG,STAMMUMFANG_TXT,BAUMHOEHE,BAUMHOEHE_TXT,KRONENDURCHMESSER,KRONENDURCHMESSER_TXT,BAUMNUMMER,SE_ANNO_CAD_DATA,lat,lon';
$sql = "SELECT $columns FROM baumkataster WHERE lat >= ? AND lon >= ? AND lat <= ? AND lon <= ?";
$result = db_query($sql, $bbox);

$xml = new SimpleXMLElement('<trees/>');
$xml->addAttribute('xmlns', 'https://android.rueckgr.at/baumkataster/');

foreach($result as $row) {
	$item = $xml->addChild('tree');

	foreach($row as $key => $value) {
		$item->addChild($key, $value);
	}
}

header("Content-Type: text/xml");
print($xml->asXML());

