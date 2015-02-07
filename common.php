<?php
spl_autoload_register(function ($class) {
    $prefix = 'StatTest';

    $baseDir = __DIR__ . '/';

	$ClassParts = explode('\\', $class);

    if ($ClassParts[0] != $prefix) {
        return;
    }

	if (count($ClassParts) == 2){
		$classFile = $baseDir . 'class/' . $ClassParts[1] . '.php';
	} else {
		unset($ClassParts[0]);
		$classFile = $baseDir . implode('/', $ClassParts) . '.php';
	}
	
	if (!file_exists($classFile)) {
		exit ("Can't load class " . $class . " from " . $classFile);
	}

	require_once $classFile;
});
?>