<?php
//$keyPG - id страницы
if (!_new_class('rubric', $RUBRIC)) return false;

$datamap = array();
$RUBRIC->fCache();

function rubGetMap(&$data, $id, $kPG)
{
	global $RUBRIC;
	$s = array();
	if (isset($data[$id]) and is_array($data[$id]) and count($data[$id]))
		foreach ($data[$id] as $key => $value) {
			$s[$key] = $value;
			$s[$key]['href'] = 'http://' . $_SERVER['HTTP_HOST'] . '/' . $RUBRIC->data2[$key]['lname'] . '/' . $kPG . '.html';
			if ($key != $id and count($data[$key]) and is_array($data[$key])) {
				if (!$CITY->id)
					$s[$key]['hidechild'] = 1;
				$s[$key]['#item#'] = rubGetMap($data, $key, $kPG);
			}
		}

	return $s;
}

$datamap = rubGetMap($RUBRIC->data3, 0, $keyPG);
return $datamap;

