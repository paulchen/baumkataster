<?php

chdir(dirname(__FILE__) . '/../');

require_once('common.php');

function create_urls() {
	$pattern = 'https://geodaten.graz.at/arcgis/rest/services/OGD/OGD_WFS/MapServer/15/query?' .
		'geometry=[lon1]%2C[lat1]%2C[lon2]%2C[lat2]&' .
		'outFields=OBJEKT_ID%2CBAUMTYP%2CPFLANZJAHR%2CBAUMPATENSCHAFT%2CSHAPE%2CBAUMART%2CDEUTSCHE_BEZEICHNUNG%2CALTERSKLASSIFIZIERUNG%2CGESCH%C3%84TZTE_BAUMH%C3%96HE%2CGESCH%C3%84TZTER_KRONENDURCHMESSER' .
		'&f=pjson';

	$lon_min = 15.34;
	// $lon_min = 15.46;
	$lon_max = 15.545;
	$lon_step = .005;

	$lat_min = 47.00;
	// $lat_min = 47.01;
	$lat_max = 47.145;
	$lat_step = .005;

	$urls = array();
	for($lat1=$lat_min; $lat1<$lat_max; $lat1+=$lat_step) {
		$lat2 = $lat1+$lat_step;
		for($lon1=$lon_min; $lon1<$lon_max; $lon1+=$lon_step) {
			$lon2 = $lon1+$lon_step;
			$url = str_replace(array('[lon1]', '[lon2]', '[lat1]', '[lat2]'), array($lon1, $lon2, $lat1, $lat2), $pattern);
			$urls[] = $url;
		}
	}

	return $urls;
}

function process_tree($feature) {
	$attrs = $feature->attributes;

	$columns = 'BAUM_ID,GATTUNG_ART,BAUMHOEHE,BAUMHOEHE_TXT,KRONENDURCHMESSER,KRONENDURCHMESSER_TXT,PFLANZJAHR,lon,lat,source,outdated';
	$columns_array = explode(',', $columns);
	$placeholders = preg_replace('/[^,]+/', '?', $columns);

	$query = "INSERT INTO baumkataster ($columns) VALUES ($placeholders) ON DUPLICATE KEY UPDATE ";
	$query .= implode(', ', array_map(function($column) { return "$column = ?"; }, explode(',', $columns)));

	$art = $attrs->BAUMART;
	$art_de = $attrs->DEUTSCHE_BEZEICHNUNG;

	$height = $attrs->GESCHÄTZTE_BAUMHÖHE;
	$diameter = $attrs->GESCHÄTZTER_KRONENDURCHMESSER;

	$row = array();
	$row[] = $attrs->OBJEKT_ID;
	$row[] = "$art ($art_de)";
	$row[] = $height ? get_height_index($attrs->GESCHÄTZTE_BAUMHÖHE) : 0;
	$row[] = $height ? $attrs->GESCHÄTZTE_BAUMHÖHE . ' m' : '';
	$row[] = $diameter ? get_treetop_diameter($attrs->GESCHÄTZTER_KRONENDURCHMESSER) : 0;
	$row[] = $diameter ? $attrs->GESCHÄTZTER_KRONENDURCHMESSER . ' m' : '';
	$row[] = $attrs->PFLANZJAHR;
	$row[] = $feature->geometry->x;
	$row[] = $feature->geometry->y;
	$row[] = 'GRAZ';
	$row[] = 0;

	$columns_count = count($row);
	for($a=0; $a<$columns_count; $a++) {
		$row[] = $row[$a];
	}

	db_query($query, $row);
}

function parse_data($url, $data) {
	$json = json_decode($data);
	if(isset($json->exceededTransferLimit) && $json->exceededTransferLimit == 'true') {
		// TODO
		die($url);
	}

	$features = $json->features;
	echo('[Graz] Number of trees: ' . count($features) . "\n");
	foreach($features as $feature) {
		process_tree($feature);
	}
}

echo("[Graz] Downloading and importing data\n");
$urls = create_urls();

$counter = 0;
$total = count($urls);

$db->beginTransaction();

foreach($urls as $url) {
	$counter++;
	echo("[Graz] Processing row $counter/$total\n");

	$data = file_get_contents($url);
	parse_data($url, $data);

	sleep(.1);
}
$db->commit();

echo("[Graz] Done\n");

