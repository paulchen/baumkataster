<?php

chdir(dirname(__FILE__));

foreach(scandir('import') as $file) {
	if(substr($file, 0, 7) != 'import_') {
		continue;
	}

	require_once("import/$file");
}

touch($status_file);

