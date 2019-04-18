<?php if (!defined('PHPLIB5_INC')): define('PHPLIB5_INC', 1);

spl_autoload_register(function ($className) {
//	if (class_exists($className)) return;

	if (preg_match('/^[a-z_]\w*$/si', $className)) {
		foreach (explode(PATH_SEPARATOR, get_include_path()) as $path) {
			if (file_exists("$path/class/$className.class.inc"))
				include "class/$className.class.inc";
		}
	}
});

endif/* PHPLIB_INC */?>
