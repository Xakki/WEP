<?php
/**
 * Импорт Товаров из XLS
 * @ShowFlexForm false
 * @type Магазин
 * @ico default.png
 * @author Xakki
 * @version 0.1
 * @return string html
 */


if (!_new_class('shop', $SHOP)) return false;

$fields_form = $mess = array();
if (!static_main::_prmModul($SHOP->_cl, array(5, 7)))
    $mess[] = static_main::am('error', 'denied', $SHOP);

elseif (isset($_POST['sbmt']) or isset($_SESSION['temp_shop_dir'])) {
    $T1 = time();
    ini_set("max_execution_time", "1000");
    set_time_limit(1000);
    $file = $_CFG['_PATH']['temp'] . 'shop' . time();
    $imgDir = array();
    $xls = $zipDir = '';

    $const_img_dir = $_CFG['_PATH']['content'] . 'Tovar';

    // Если загружен фаил
    if (isset($_FILES['xls']) and $_FILES['xls']['tmp_name'] and move_uploaded_file($_FILES['xls']['tmp_name'], $file)) {
        if (stripos($_FILES['xls']['name'], '.zip') !== false) {
            $zipDir = static_tools::extractZip($file);
            $DIR = scandir($zipDir);
            foreach ($DIR as $dfile) {
                if ($dfile != '.' and $dfile != '..') {
                    if (stripos($dfile, '.xls'))
                        $xls = $zipDir . '/' . $dfile;
                    else
                        $imgDir[$dfile] = $zipDir . '/' . $dfile;
                }
            }
        } else
            $xls = $file;

        // ИМпорт XLS
        if ($xls) {
            $dataCatChange = array();
            //
            $DT = dumpXlsData($xls);
            //$SHOP->_tableClear();
            $i1 = 0;
            $i2 = 0;

            foreach ($DT['dataCat'] as $r) {
                if (isset($dataCatChange[$r['parent_id']])) // если поменялся Id, то меняем родителя
                    $r['parent_id'] = $dataCatChange[$r['parent_id']];

                $dt = $SHOP->qs('id,parent_id,name', 'WHERE id=' . $r['id']); //name="'.$SHOP->SqlEsc($r['name']).'" or

                if (!count($dt)) { // or !isset($dt[$r['name']])
                    /*if(count($dt)) { // если найден хотябы ID, то удалем его и получаем новый
                        $tmpId = $r['id'];
                        unset($r['id']);
                    }*/
                    if ($SHOP->_add($r)) {
                        $i1++;
                        if (count($dt))
                            $dataCatChange[$tmpId] = $SHOP->id;
                    }
                } elseif ($dt[0]['id'] != $r['name']) {
                    $i2++;
                    $SHOP->id = $r['id'];
                    $SHOP->_update(array('name' => $r['name']));
                }
                /*elseif(isset($dt[$r['name']]) and $dt[$r['name']]['id']!=$r['id']) {
                    $dataCatChange[$r['id']] = $dt[$r['name']]['id'];
                }*/
            }

            $mess[] = static_main::am('ok', 'XLS - добавлено ' . $i1 . ', обновлено ' . $i2 . ' записей в каталог');

            $prodName = array(
                1 => 'id',
                2 => 'code',
                3 => 'name',
                4 => 'model',
                5 => 'articul',
                6 => 'madein',
                7 => 'cost',
                'shop' => 'shop'
            );
            $optName = array();
            $i1 = 0;

            //$SHOP->childs['product']->_tableClear();
            //$SHOP->childs['product']->childs['product_value']->_tableClear();
            $SHOP->childs['product']->_update(array('available' => 1), array('active' => 1)); // обновить только включенные
            foreach ($DT['dataProd'] as $r) {
                $tmpProd = array();
                $r[7] = str_replace(',', '.', $r[7]);
                foreach ($prodName as $kk => $rr) {
                    if (isset($r[$kk]))
                        $tmpProd[$rr] = $r[$kk];
                }
                $tmpProd['available'] = 0; // на складе
                $tmpProd['active'] = 1; // включить

                if (isset($dataCatChange[$tmpProd['shop']]))
                    $tmpProd['shop'] = $dataCatChange[$tmpProd['shop']];

                $tmpCode1 = substr($tmpProd['code'], 0, 4);
                $tmpCode2 = substr($tmpProd['code'], 4, 3);
                if (file_exists($const_img_dir . '/' . $tmpCode1 . '/' . $tmpCode2)) {
                    $tmpProd['img_product'] = array('tmp_name' => $const_img_dir . '/' . $tmpCode1 . '/' . $tmpCode2, 'ext' => 'jpg');
                }

                if (!$SHOP->childs['product']->_update($tmpProd, array('code' => $tmpProd['code']))) {
                    if ($SHOP->childs['product']->_add($tmpProd))
                        $i1++;
                }


                $tmpOpt = array(); // опции, если есть
                if (count($optName)) {
                    foreach ($optName as $kk => $rr) {
                        if (isset($r[$kk]))
                            $tmpOpt[$rr] = $r[$kk];
                    }
                }
                if (count($tmpOpt)) { // опции, если есть
                    $tmpOpt['owner_id'] = $SHOP->childs['product']->id;
                    $SHOP->childs['product']->childs['product_value']->_add($tmpOpt);
                }
            }

            $mess[] = static_main::am('ok', 'XLS - добавлено ' . $i1 . ', обновлено ' . (count($DT['dataProd']) - $i1) . ' товаров');
            //$mess[] = static_main::am('ok', 'XLS импортировно успешно!');
        }

        static_tools::_rmdir($zipDir);
    }

    // Если указан путь к картинкам
    if (isset($_SESSION['temp_shop_dir']) or $_POST['img']) {

        if (isset($_SESSION['temp_shop_dir'])) {
            $zipDir = $_SESSION['temp_shop_dir'];
            unset($_SESSION['temp_shop_dir']);
        } else
            $zipDir = $_CFG['_PATH']['temp'] . $_POST['img'];

        $DIR = scandir($zipDir);
        foreach ($DIR as $dfile) {
            if ($dfile != '.' and $dfile != '..') {
                if (!is_dir($zipDir . '/' . $dfile) or preg_match('/[^0-9]/', $dfile)) {
                    $mess[] = static_main::am('ok', 'Каталог не верного формата! Должен содержать только папки с цифровым названием. [' . $dfile . ']');
                    break;
                }
                $imgDir[$dfile] = $zipDir . '/' . $dfile;
            }
        }
    }

    // Импорт картинок
    $flag_repeat = false;
    if (count($imgDir)) {
        $i = $i2 = 0;
        foreach ($imgDir as $kD => $rD) {
            if (isset($_SESSION['temp_shop']) and $_SESSION['temp_shop'] != $kD)
                continue;
            else
                unset($_SESSION['temp_shop']);

            if ((time() - $T1) > 900) {
                $flag_repeat = $kD;
                break;
            }

            $DIR = scandir($rD);
            foreach ($DIR as $rImg) {
                if ($rImg != '.' and $rImg != '..' and is_file($rD . '/' . $rImg)) {
                    $code = $kD . $rImg;
                    $upd = array('img_product' => array('tmp_name' => $rD . '/' . $rImg, 'ext' => 'jpg'));
                    $where = array('code' => $code);
                    if ($SHOP->childs['product']->_update($upd, $where, false)) {
                        $i++;
                    } else {
                        $i2++;
                        //$mess[] = static_main::am('error', 'Код товара `'.$code.'` не найден [папка '.$kD.' : фаил '.$rImg.']');
                    }
                }
            }
        }

        if ($i)
            $mess[] = static_main::am('ok', 'Загружено ' . $i . ' картинок!');
        if ($i2)
            $mess[] = static_main::am('error', 'Нет соответствий для  ' . $i2 . ' картинок!');

        if ($flag_repeat) {
            $mess[] = static_main::am('notice', 'Обработка не закончена, и будет продолжена через пару секунд.');
            $_SESSION['temp_shop'] = $flag_repeat;
            $_SESSION['temp_shop_dir'] = $zipDir;
            $_tpl['onload'] .= 'setTimeout(function(){window.location.reload();},10000);';
        } else
            unset($_SESSION['temp_shop']);
    } elseif (!$xls)
        $mess[] = static_main::am('notice', 'Загрузите архив, xls фаил либо укажите путь к директории с картинками');


} else {
    $fields_form['_info'] = array(
        'type' => 'info',
        'caption' => '<h2 style="text-align:center;">Импорт товаров из XLS</h2>');
    $fields_form['xls'] = array(
        'type' => 'file',
        'caption' => 'Прайс',
        'comment' => 'В формате xls',
        'mask' => array(),
    );
    /*$fields_form['img'] = array(
        'type' => 'list',
        'caption' => 'Укажите место где находится картинки',
        'valuelist'=>array(),
        'mask'=>array(),
    );
    $fields_form['img']['valuelist'][] = array('#name#'=>' - ', '#id#'=>0);
    $DIR = scandir($_CFG['_PATH']['temp']);
    foreach($DIR as $dfile) {
        if($dfile!='.' and $dfile!='..' and is_dir($_CFG['_PATH']['temp'].$dfile)) {
            $fields_form['img']['valuelist'][] = array('#name#'=>$_CFG['PATH']['temp'].$dfile, '#id#'=>$dfile);
        }
    }*/
    $fields_form['sbmt'] = array(
        'type' => 'submit',
        'value' => 'Импортировать',
    );
    $SHOP->kFields2FormFields($fields_form, 'POST');
}
$DATA = Array(
    'form' => $fields_form,
    'messages' => $mess,
    'options' => $SHOP->getFormOptions()
);
$html = transformPHP($DATA, '#pg#formcreat');


function dumpXlsData($file, $sheet = 0)
{

    error_reporting(E_ALL ^ E_NOTICE);
    require_once getLib('excel_reader2');
    $dataXLS = new Spreadsheet_Excel_Reader($file);

    $out = array(
        'dataCat' => array(),
        'dataProd' => array(),
        'info' => array(),
    );

    $idCat = 0;
    for ($row = 1; $row <= $dataXLS->rowcount($sheet); $row++) {
        $tmp = array();
        for ($col = 1; $col <= $dataXLS->colcount($sheet); $col++) {
            // Account for Rowspans/Colspans
            /*$rowspan = $this->rowspan($row,$col,$sheet);
            $colspan = $this->colspan($row,$col,$sheet);
            for($i=0;$i<$rowspan;$i++) {
                for($j=0;$j<$colspan;$j++) {
                    if ($i>0 || $j>0) {
                        $this->sheets[$sheet]['cellsInfo'][$row+$i][$col+$j]['dontprint']=1;
                    }
                }
            }
            if(!$this->sheets[$sheet]['cellsInfo'][$row][$col]['dontprint']) {*/

            $val = trim($dataXLS->val($row, $col, $sheet));
            if ($val != '') {
                //$val = htmlentities($val,ENT_QUOTES,"WINDOWS-1251");
                $val = mb_convert_encoding($val, 'UTF-8', 'WINDOWS-1251');
                $tmp[$col] = $val;
            }
            //}
        }

        $randi = 2;
        if ($cnt = count($tmp)) {
            if ($cnt == 1) {
                if (isset($tmp[1])) {
                    $idCat++;
                    $name0 = '';
                    $name1 = $tmp[1];
                    $tmpName = explode('/', $name1);
                    if (count($tmpName) > 1) {
                        $name0 = $tmpName[0];
                        $name1 = $tmpName[1];
                        //$name1 = str_replace($name0,'',$tmpName[1]);
                        if (!isset($out['dataCat'][$name0])) {
                            $out['dataCat'][$name0] = array(
                                'name' => $name0,
                                'id' => $idCat,
                                'parent_id' => 0
                            );
                            $idCat++;
                        }
                        $pid = 0;
                        if (isset($out['dataCat'][$name0])) {
                            $pid = $out['dataCat'][$name0]['id'];
                            $name1 = trim(preg_replace('/^' . $name0 . '/', '', trim($name1)));
                        }

                        if (isset($out['dataCat'][$name1])) {
                            $name1 .= ' (' . $randi . ')';
                            $randi++;
                        }

                        $out['dataCat'][$name1] = array(
                            'name' => $name1,
                            'id' => $idCat,
                            'parent_id' => $pid
                        );
                    } else {
                        if (isset($out['dataCat'][$name1])) {
                            $name1 .= ' (' . $randi . ')';
                            $randi++;
                        }
                        $out['dataCat'][$name1] = array(
                            'name' => $name1,
                            'id' => $idCat,
                            'parent_id' => 0
                        );
                    }
                } else {
                    $out['info'][] = current($tmp);
                }
            } elseif (isset($tmp[1]) and isset($out['field'])) {
                $tmp['shop'] = $idCat;
                $out['dataProd'][(int)$tmp[1]] = $tmp;
            } elseif (isset($tmp[1])) {
                $out['field'] = $tmp;
            } else
                $out['info'][] = $tmp;
        }
        //if($row>4) return $out;
    }
    return $out;
}

return $html;