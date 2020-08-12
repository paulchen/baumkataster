<?php

chdir(dirname(__FILE__));

require_once('common.php');

log_info('Beginning import');

db_query('UPDATE baumkataster SET outdated = 1');

foreach(scandir('import') as $file) {
	if(substr($file, 0, 7) != 'import_') {
		continue;
	}

	require_once("import/$file");
}

$data = db_query('SELECT COUNT(*) outdated FROM baumkataster WHERE outdated = 1');
$count = $data[0]['outdated'];
log_info("Deleting $count outdated records");

db_query('DELETE FROM baumkataster WHERE outdated = 1');

touch($status_file);

log_info('Done');

