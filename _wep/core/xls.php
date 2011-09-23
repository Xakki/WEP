<?php

function xlsBOF() {
	$xls = pack("ssssss", 0x809, 0x8, 0x0, 0x10, 0x0, 0x0);
	return $xls;
}

function xlsEOF() {
	$xls = pack("ss", 0x0A, 0x00);
	return $xls;
}

function xlsWriteNumber($Row, $Col, $Value) {
	$xls =  pack("sssss", 0x203, 14, $Row, $Col, 0x0);
	$xls .= pack("d", $Value);
	return $xls;
}

function xlsWriteLabel($Row, $Col, $Value ) {
	$Value = iconv('utf-8', 'windows-1251', $Value);
	$L = strlen($Value);
	$xls = pack("ssssss", 0x204, 8 + $L, $Row, $Col, 0x0, $L);
	$xls .= $Value;
	return $xls;
}

