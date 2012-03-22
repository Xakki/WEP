[WEP CMS](http://wep.xakki.com/) - Модульная система управления контентом на PHP+MySQL
==================================================
Введение
----------
WEP - означает <b>Web Engine on PHP</b>

https://github.com/Xakki/WEP -- Основная ветка разработки

https://github.com/Xakki/WEP_PLUGIN -- дополнительные модули

http://wep.xakki.ru -- сайт проекта

Особенности
-----------
* мультиязыковая платформа
* мультишаблонность
* гибкость (модульная структура)
* быстродействие
* бесплатный

Требования
----------
* MYSQL 5.0 и новее
* PHP 5.3.0 и новее
* Apache 2 или(и) NGINX (с модуем php)
* Apache модули [mod_rewrite, mod_mime_magic]
* PHP модули [php_gd2, php_mbstring, php_exif, php_mysqli, php_openssl или php_mcrypt, php_xsl (не обязательно), php_curl (не обязательно), php_memcache (не обязательно)]

Установка
---------
1. [Скачать фаилы](https://github.com/Xakki/WEP/zipball/master) или выгрузить репозиторий c GITHUB  <br/>
2. Распаковать на сервере (например /var/www/localhost) <br/>
3. Настройть Apache <br/>
4. Открыть сайт (например http://localhost) и вы попадете на страницу дефолтной авторизации (логин root и пароль rootpass) <br/>
5. После успешной авторизации откроется пошаговая установка.(http://localhost/_wep/install.php)<br/>

### Первый шаг - Настройки сайта###
Поля <b>выделенныен цветом</b> (Login БД с правами суперпользователя и Пароль БД с правами суперпользователя) необходимы для первоначального создания базы данных. Если вы уже создали БД вручную, то <b>не</b> заполняйте их

create database `wepbd` character set utf8;
create user 'wepmysqluser'@'localhost' identified by 'defaultpass';
grant all privileges on wepbd.* to 'wepmysqluser'@'localhost';

Определения и значения полей:

* Мастер-логин и Мастер-пароль - для авторизации/регистрации админа (только с этим логином и паролем в последствии можно запускать установщик и менять настройки , если сломалась база)
* Система авторизации на сайте - определяет, создавать БД для пользователей и прав доступа либо будет только один Мастер-логин
* Тип хранени сессий пользователей - средствами PHP либо в БД
* Ловец жуков - типы отлавливаемых ошибок
* php error_reporting - PHP деректива
* DEBUG MODE - Режим отладки
* _showerror - название параметра передаваемый в адреседля ручного отображения ошибок (0 - скрыть, 1- краткая инфа, 2 + SQL запросы, 3 + все логи)
* _showallinfo - название параметра передаваемый в адреседля ручного отображения инфы (0- скрыть, 1- сообщение об ошибке, 2- показать текст самих ошибок, 3 - редеректы вручную)
* HTTP_HOST - домен сайта, необходим для CRON
* Включить режим "Технический перерыв" - ставит заглушку на FRONTEND, только админ всёравно видит сайт
* Memcache - служба кеширования, для увелечение быстродействия , кеширутся контент в модуле "Страницы"
...

### Второй шаг  - Проверка структуры сайта###
На данном этапе устанавливаются необходимые модули и создаются таблицы.

### Третий шаг - Установка модулей и удаление.###
Сдесь можно выбрать установку дополнительных плагинов(модулей).

### Завершение ###
Если всё успешно пройдено, то можно перейти в админку , откроется форма авторизации, потребуется ввести мастер-логин и пароль. На этом установка закончена.





### Поздравляю тебя мой юный подаван. Велики ждёт путь тебя. Джедаем стать можешь ты, овладев этой СМС :) ###






Создание и управление контентом
-------------------------------

### Модуль "Страницы"


Основные своиства ядра
----------------------

### Константы ядра ###
Поля помеченные %parent_id - могут принимать как булевое значение (наименование поля по умол), так и string - с указанием своего наименования поля
* `$this->mf_istree = false;` %parent_id - создает дополнительное поле и структурирует записи в виде дерева
* `$this->mf_ordctrl = false;` %ordind - для сортировки
* `$this->mf_actctrl = false;` %active - состояние вкл/выкл

* `this->mf_use_charid = false;` //if true - id varchar
* `$this->mf_namefields = true;` //добавлять поле name
* `$this->mf_createrid = true;` //польз владелец
* `$this->mf_istree = false;` // древовидная структура?
* `$this->mf_treelevel = 0;` // разрешенное число уровней в дереве , 0 - безлимита, 1 - разрешить 1 подуровень
* `$this->mf_ordctrl = false;` // поле ordind для сортировки
* `$this->mf_actctrl = false;` // поле active
* `$this->mf_timestamp = false;` // создать поле  типа timestamp
* `$this->mf_timecr = false;` // создать поле хранящще время создания поля
* `$this->mf_timeup = false;` // создать поле хранящще время обновления поля
* `$this->mf_timeoff = false;` // создать поле хранящще время отключения поля (active=0)
* `$this->mf_ipcreate = false;` //IP адрес пользователя с котрого была добавлена запись
* `$this->prm_add = true;` // добавить в модуле
* `$this->prm_del = true;` // удалять в модуле
* `$this->prm_edit = true;` // редактировать в модуле
* `$this->showinowner = true;` // показывать под родителем
* `$this->owner_unique = false;` // поле owner_id не уникально
* `$this->mf_mop = true;` // выключить постраничное отображение
* `$this->reversePageN = false;` // обратный отчет для постраничного отображения
* `$this->messages_on_page = 20;` //число эл-ов на странице
* `$this->numlist = 10;` //максим число страниц при котором отображ все номера страниц
* `$this->mf_indexing = false;` // TOOLS индексация
* `$this->mf_statistic = false;` // TOOLS показывать  статистику по дате добавления
* `$this->cf_childs = false;` // TOOLS true - включить управление подключение подмодулей в настройках модуля
* `$this->cf_reinstall = false;` // TOOLS
* `$this->includeJStoWEP = false;` // подключать ли скрипты для формы через настройки
* `$this->includeCSStoWEP = false;` // подключать ли стили для формы через настройки
* `$this->singleton = true;`  класс-одиночка
* `$this->ver = '0.1.1';`  версия модуля
* `$this->RCVerCore = self::versionCore;` - требуемая миним версия ядра
* `$this->icon = 0;`  числа  означают отсуп для бэкграунда, а если будет задан текст то это сам рисунок
* `$this->default_access = '|0|';`



###Как получить комулятивный патч###
`git diff --name-status commit1 commit2 | awk '{ if ($1 != "D") print $2 }' | xargs git archive -o output.zip HEAD`