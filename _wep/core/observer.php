<?php

class observer
{

    static private $events = array(); // массив с уже совешенными событиями
    static private $observers = array(); // массив с функциями-наблюдателями

    /**
     * Сообщает наблюдателям о том, что произошло событие $event
     * при этом выполняются те фукции, которые зарегестрированы на получение этого события
     *
     * В массив self::$events заносится информация о том, что произошло это событие, и если в дальнейшем
     * какая-нибудь функция подпишется на это событие, то она сразу же выполнится
     * @param string $event
     */

    static function notify_observers($event)
    {
        if (!isset(self::$events[$event])) {
            self::$events[$event] = true;
        }

        if (isset(self::$observers[$event])) {
            foreach (self::$observers[$event] as $r) {
                if (isset($r['args']) && !empty($r['args'])) {
                    $str_args = '$r[\'args\'][' . implode('], $r[\'args\'][', array_keys($r['args'])) . ']';
                } else {
                    $str_args = '';
                }

                if (isset($r['obj'])) {
                    eval('$r["obj"]->' . $r['func'] . '(' . $str_args . ');');
                } else {
                    eval($r['func'] . '(' . $str_args . ');');
                }
            }
        }
    }

    /**
     * Регистрирует функцию-наблюдателя на получение события $event
     * При этом функция выполнится, когда произойдет событие $event,
     * или если на данный момент событие уже произошло, то функция выполнится сразу же
     *
     * В массиве params находятся данные о вызываемой функции
     *    - $params['obj'] - объект, в котором находится функция (если функция глобальная, то данного элемента быть не должно)
     *    - $params['func'] - строка - название функции
     *    - $params['args'] - одномерный массив, содержащий передаваемые в функию аргументы, ключи могут быть любые, значения - аргументы ф-ии
     *        если функция не принимает аргументов, то этот элемент должен отсутствовать или в нём должен быть пустой массив
     *
     * @param array $params
     * @param string $event
     * @return bool
     */
    static function register_observer($params, $event)
    {
        if (!isset($params['func']) || !isset($event)) {
            trigger_error('В функцию register_observer не переданы все необходимые параметры', E_USER_WARNING);
            return false;
        }
        if (isset($params['args']) && !is_array($params['args'])) {
            trigger_error('В функции register_observer переданный пар-р params[args] должен быть массивом', E_USER_WARNING);
            return false;
        }

        self::$observers[$event][] = $params;

        if (isset(self::$events[$event])) {
            if (isset($params['args']) && !empty($params['args'])) {
                $str_args = '$params[\'args\'][' . implode('], $params[\'args\'][', array_keys($params['args'])) . ']';
            } else {
                $str_args = '';
            }

            if (isset($params['obj'])) {
                eval('$params[\'obj\']->' . $params['func'] . '(' . $str_args . ');');
            } else {
                eval($params['func'] . '(' . $str_args . ');');
            }
        }
    }

}