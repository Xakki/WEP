<?
	//$_ADMIN = dirname(__FILE__);
	//require($_ADMIN."/system/config.ini.php");
	//require($_ADMIN."/system/sql.class.php");

//DELETE FROM ip_group_city WHERE country_code!="RU";
//alter table `xakkiorg_01`.`ip_group_city` add index `city` (`city`);
//alter table `xakkiorg_01`.`ip_group_city` drop column `zipcode`,drop column `longitude`, drop column `latitude`, drop column `country_name`;


//SELECT t1.* FROM ip_group_city t1 LEFT JOIN city t2 ON t2.city=t1.city and t2.city!='' WHERE t1.country_code="RU" and isNull(t2.id) and t1.city!='' GROUP BY t1.city ORDER BY t1.region_name;

