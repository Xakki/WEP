<?php

ini_set("max_execution_time", "100000");
set_time_limit(100000);

_new_class('modulprm', $MODULPRM);
$data = $MODULPRM->qs('id,name', 'WHERE active=1', 'id');
$mod = 10000;
foreach ($data as $key => $row) {
	if (_new_class($key, $MODUL) && count($MODUL->attaches)) {
		foreach ($MODUL->attaches as $key => $row) {
			if (count($MODUL->attaches[$key]['thumb'])) {
				foreach ($MODUL->attaches[$key]['thumb'] as $modkey => $imod) {
					if (!isset($imod['pref'])) $imod['pref'] = '';
					if (!isset($imod['path'])) $imod['path'] = '';
					if ((!$imod['pref'] and !$imod['path']) or (!$imod['pref'] and $imod['path'] == $MODUL->attaches[$key]['path'])) {
						continue;
					}
					/****************************/
					$path = SITE . $MODUL->getPathForAtt($key);
					static_tools::_checkdir($path . 'thumb/');
					$dir = dir($path);
					$i = 0;
					while (false !== ($entry = $dir->read())) {
						$explodeFile = explode('.', $entry);
						if ($entry === '.' || $entry === '..' || count($explodeFile) == 1) continue;
						$i++;

						if (strpos($explodeFile[0], $imod['pref']) === 0) {
							$id = (int)substr($explodeFile[0], strlen($imod['pref']));
							$subPath = ceil($id / $mod);
							$newFile = $path . $imod['pref'] . 'thumb/' . $subPath . '/' . $id . '.' . $explodeFile[1];
							static_tools::_checkdir($path . $imod['pref'] . 'thumb/' . $subPath);
						}
						else {
							$id = (int)$explodeFile[0];
							$subPath = ceil($id / $mod);
							$newFile = $path . $subPath . '/' . $entry;
							static_tools::_checkdir($path . $subPath);
						}
						rename($path . $entry, $newFile);
					}
					$dir->close();
					/*****************************/
				}
			}
		}

	}
}

return 'OK';
