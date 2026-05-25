<?php
/**
 * Configuracion central del proyecto.
 *
 * Las credenciales se leen desde variables de entorno o desde el archivo .env
 * ubicado en la raiz del proyecto.
 */

function aio_load_env($path)
{
	if (!is_readable($path)) {
		return;
	}

	$lines = file($path, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
	foreach ($lines as $line) {
		$line = trim($line);

		if ($line === '' || $line[0] === '#') {
			continue;
		}

		if (strpos($line, 'export ') === 0) {
			$line = trim(substr($line, 7));
		}

		$separator = strpos($line, '=');
		if ($separator === false) {
			continue;
		}

		$key = trim(substr($line, 0, $separator));
		$value = trim(substr($line, $separator + 1));

		if ($key === '') {
			continue;
		}

		if (
			(strlen($value) >= 2) &&
			(($value[0] === '"' && substr($value, -1) === '"') || ($value[0] === "'" && substr($value, -1) === "'"))
		) {
			$value = substr($value, 1, -1);
		}

		if (getenv($key) === false) {
			putenv($key . '=' . $value);
		}

		$_ENV[$key] = $value;
		$_SERVER[$key] = $value;
	}
}

function aio_env($key, $default = null)
{
	$value = getenv($key);

	if ($value !== false) {
		return $value;
	}

	if (array_key_exists($key, $_ENV)) {
		return $_ENV[$key];
	}

	if (array_key_exists($key, $_SERVER)) {
		return $_SERVER[$key];
	}

	return $default;
}

aio_load_env(__DIR__ . '/.env');

$aioHost = aio_env('AIO_HOST');
$aioDb = aio_env('AIO_DB');
$aioDsn = aio_env('AIO_DSN', 'mysql:host=' . $aioHost . ';dbname=' . $aioDb);
$aioWeb = aio_env('AIO_WEB', '');

defined('AIO_HOST') || define('AIO_HOST', $aioHost);
defined('AIO_DB') || define('AIO_DB', $aioDb);
defined('_AIO_DSN') || define('_AIO_DSN', $aioDsn);
defined('_AIO_USER') || define('_AIO_USER', aio_env('AIO_USER'));
defined('_AIO_PASS') || define('_AIO_PASS', aio_env('AIO_PASS'));

defined('_FAN_DSN') || define('_FAN_DSN', aio_env('FAN_DSN'));
defined('_FAN_USER') || define('_FAN_USER', aio_env('FAN_USER'));
defined('_FAN_PASS') || define('_FAN_PASS', aio_env('FAN_PASS'));

defined('_CLAVE') || define('_CLAVE', aio_env('APP_KEY'));
