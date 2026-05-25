<?php

require_once __DIR__ . '/../../vendor/autoload.php';

if (!class_exists('HTML2PDF_exception')) {
	class_alias('Spipu\Html2Pdf\Exception\Html2PdfException', 'HTML2PDF_exception');
}

if (!class_exists('HTML2PDF')) {
	class HTML2PDF extends Spipu\Html2Pdf\Html2Pdf
	{
		public function __construct(
			$orientation = 'P',
			$format = 'A4',
			$langue = 'fr',
			$unicode = true,
			$encoding = 'UTF-8',
			$marges = array(5, 5, 5, 8)
		) {
			if (is_array($unicode)) {
				$unicode = true;
			}

			if (!is_array($marges)) {
				$marges = array($marges, $marges, $marges, $marges);
			}

			parent::__construct($orientation, $format, $langue, (bool) $unicode, $encoding, $marges);
		}

		public function output($name = 'document.pdf', $dest = 'I')
		{
			if ($dest === false || $dest === '') {
				$dest = 'I';
			} elseif ($dest === true) {
				$dest = 'S';
			}

			$dest = strtoupper($dest);

			if (in_array($dest, array('F', 'FI', 'FD'), true) && !$this->isAbsolutePath($name) && !$this->hasStreamWrapper($name)) {
				$name = $this->getCallerDirectory() . DIRECTORY_SEPARATOR . $name;
			}

			return parent::output($name, $dest);
		}

		private function isAbsolutePath($path)
		{
			return isset($path[0]) && ($path[0] === '/' || preg_match('/^[A-Za-z]:[\\\\\\/]/', $path));
		}

		private function hasStreamWrapper($path)
		{
			return (bool) preg_match('/^[a-z][a-z0-9+.-]*:\/\//i', $path);
		}

		private function getCallerDirectory()
		{
			$trace = debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS);

			foreach ($trace as $call) {
				if (empty($call['file'])) {
					continue;
				}

				if ($call['file'] !== __FILE__ && strpos($call['file'], DIRECTORY_SEPARATOR . 'vendor' . DIRECTORY_SEPARATOR) === false) {
					$directory = realpath(dirname($call['file']));
					if ($directory !== false) {
						return $directory;
					}
				}
			}

			return realpath(getcwd());
		}
	}
}
