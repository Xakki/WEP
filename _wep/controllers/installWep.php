<?php

error_reporting(-1); // включаем показ всех ошибок
ini_set('display_errors', -1);
header('Content-type: text/html; charset=utf-8');

$w_host = 'http://xakki.ru/';

$file = $w_host . '_js.php?_modul=wepcontrol&_func=getlastfile';

$ajax_data = file_get_contents($file);
$ajax_data = json_decode($ajax_data);
if (count($ajax_data)) {
	$ajax_data = current($ajax_data);
	$file = $w_host . $ajax_data->wepcontrol;
	$newFile = __DIR__ . '/temp.zip';
	copy($file, $newFile);
	extractZip($newFile, __DIR__);
	unlink($newFile);
	header("Location: /_wep/install.php");

	$data = file(__FILE__);
	$data[0] .= 'exit("File blocked");';
	file_put_contents(__FILE__, implode('', $data));

}


function extractZip($zipFile = '', $zipDir = '', $dirFromZip = '')
{
	// $zipDir Папка для распаковки.
	if (!$zipDir) {
		$zipDir = substr($zipFile, 0, -4) . '/';
	}
	$zip = zip_open($zipFile);

	if ($zip) {
		while ($zip_entry = zip_read($zip)) {
			// Перекодируем с CP866 в CP1251
			$completePath = $zipDir . dirname(iconv('CP866', 'CP1251', zip_entry_name($zip_entry)));
			$completeName = $zipDir . iconv('CP866', 'CP1251', zip_entry_name($zip_entry));

			if (!file_exists($completePath) && preg_match('#^' . $dirFromZip . '.*#', dirname(zip_entry_name($zip_entry)))) {
				$tmp = '';
				foreach (explode('/', $completePath) as $k) {
					$tmp .= $k . '/';
					if (!file_exists(trim($tmp, '/'))) {
						@mkdir($tmp, 0777);
					}
				}
			}

			if (zip_entry_open($zip, $zip_entry, "r")) {
				if (preg_match('#^' . $dirFromZip . '.*#', dirname(zip_entry_name($zip_entry)))) {
					if (substr($completeName, -1) != '/' and $fd = @fopen($completeName, 'w+')) {
						fwrite($fd, zip_entry_read($zip_entry, zip_entry_filesize($zip_entry)));
						fclose($fd);
					} else {
						if (!file_exists(trim($completeName, '/')))
							mkdir($completeName, 0777);
					}

					zip_entry_close($zip_entry);
				}
			}
		}

		zip_close($zip);
	}

	return rtrim($zipDir, '/');
}
