<?php
function ruslat($st) # Задаём функцию перекодировки кириллицы в транслит.
{
    // Затем - "многосимвольные".
    $st = strtr($st,
        array(
            ' ' => '_',
            'а' => 'a', 'б' => 'b', 'в' => 'v', 'г' => 'g', 'д' => 'd', 'е' => 'e', 'ё' => 'e', 'з' => 'z', 'и' => 'i', 'й' => 'y', 'к' => 'k', 'л' => 'l', 'м' => 'm', 'н' => 'n', 'о' => 'o', 'п' => 'p', 'р' => 'r',
            'с' => 's', 'т' => 't', 'у' => 'u', 'ф' => 'f', 'х' => 'h', 'ы' => 'i', 'э' => 'e',
            'А' => 'A', 'Б' => 'B', 'В' => 'V', 'Г' => 'G', 'Д' => 'D', 'Е' => 'E', 'Ё' => 'E', 'З' => 'Z', 'И' => 'I', 'Й' => 'Y', 'К' => 'K', 'Л' => 'L', 'М' => 'M', 'Н' => 'N', 'О' => 'O', 'П' => 'P', 'Р' => 'R',
            'С' => 'S', 'Т' => 'T', 'У' => 'U', 'Ф' => 'F', 'Х' => 'H', 'Ы' => 'I', 'Э' => 'E',
            "ж" => "zh", "ц" => "ts", "ч" => "ch", "ш" => "sh",
            "щ" => "shch", "ю" => "yu", "я" => "ya",
            "Ж" => "ZH", "Ц" => "TS", "Ч" => "CH", "Ш" => "SH",
            "Щ" => "SHCH", "Ю" => "YU", "Я" => "YA",
            "ї" => "i", "Ї" => "Yi", "є" => "ie", "Є" => "Ye"
            //,"Ь"=>"'","Ъ"=>"'","ь"=>"'","ъ"=>"'"
        , "Ь" => "", "Ъ" => "", "ь" => "", "ъ" => ""
        )
    );
    // Возвращаем результат.
    return $st;
}

function latrus($string) # Теперь задаём функцию перекодировки транслита в кириллицу.
{
    $string = str_replace("Republic", "Республика", $string);
    $string = str_replace("republic", "республика", $string);
    $string = str_replace("Mariy-El", "Маpий-Эл", $string);
    $string = str_replace("Saint Petersburg", "Санкт-Питербург", $string);
    $string = str_replace("Taymyr", "Таймыр", $string);
    $string = str_replace("City", "Город", $string);
    $string = str_replace("zh", "ж", $string);
    $string = str_replace("Kh", "Х", $string);
    $string = str_replace("kh", "х", $string);
    $string = str_replace("Zh", "Ж", $string);
    $string = str_replace("yo", "ё", $string);
    $string = str_replace("Yu", "Ю", $string);
    $string = str_replace("Ju", "Ю", $string);
    $string = str_replace("ju", "ю", $string);
    $string = str_replace("yu", "ю", $string);
    $string = str_replace("sh", "ш", $string);
    $string = str_replace("ye", "э", $string);
    $string = str_replace("yа", "я", $string);
    $string = str_replace("Sh", "Ш", $string);
    $string = str_replace("Ch", "Ч", $string);
    $string = str_replace("ch", "ч", $string);
    $string = str_replace("Yo", "Ё", $string);
    $string = str_replace("Ya", "Я", $string);
    $string = str_replace("ya", "я", $string);
    $string = str_replace("Ja", "Я", $string);
    $string = str_replace("Ye", "Э", $string);
    $string = str_replace("i", "и", $string);
    $string = str_replace("'", "ь", $string);
    $string = str_replace("ts", "ц", $string);
    $string = str_replace("u", "у", $string);
    $string = str_replace("k", "к", $string);
    $string = str_replace("e", "е", $string);
    $string = str_replace("n", "н", $string);
    $string = str_replace("g", "г", $string);
    $string = str_replace("z", "з", $string);
    $string = str_replace("''", "ъ", $string);
    $string = str_replace("f", "ф", $string);
    $string = str_replace("y", "й", $string);
    $string = str_replace("v", "в", $string);
    $string = str_replace("a", "а", $string);
    $string = str_replace("p", "п", $string);
    $string = str_replace("r", "р", $string);
    $string = str_replace("o", "о", $string);
    $string = str_replace("l", "л", $string);
    $string = str_replace("d", "д", $string);
    $string = str_replace("s", "с", $string);
    $string = str_replace("m", "м", $string);
    $string = str_replace("t", "т", $string);
    $string = str_replace("b", "б", $string);
    $string = str_replace("I", "Й", $string);
    $string = str_replace("'", "Ь", $string);
    $string = str_replace("C", "Ц", $string);
    $string = str_replace("U", "У", $string);
    $string = str_replace("K", "К", $string);
    $string = str_replace("E", "Е", $string);
    $string = str_replace("N", "Н", $string);
    $string = str_replace("G", "Г", $string);
    $string = str_replace("Z", "З", $string);
    $string = str_replace("H", "Х", $string);
    $string = str_replace("''", "Ъ", $string);
    $string = str_replace("F", "Ф", $string);
    $string = str_replace("Y", "Ы", $string);
    $string = str_replace("V", "В", $string);
    $string = str_replace("A", "А", $string);
    $string = str_replace("P", "П", $string);
    $string = str_replace("R", "Р", $string);
    $string = str_replace("O", "О", $string);
    $string = str_replace("L", "Л", $string);
    $string = str_replace("D", "Д", $string);
    $string = str_replace("S", "С", $string);
    $string = str_replace("M", "М", $string);
    $string = str_replace("I", "И", $string);
    $string = str_replace("T", "Т", $string);
    $string = str_replace("B", "Б", $string);

    return $string;
}

