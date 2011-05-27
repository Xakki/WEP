<?
exit();
$www = $_SERVER["DOCUMENT_ROOT"]."/";

$file = array();
$file[] = $www.'admin/kernel/client.class.php';
print_r("<pre>");

/*
class modul_class{}
foreach($file as $fname)
{	
	require($fname);
	$class_methods = get_class_methods('firms_class');
	print_r($class_methods);
}
*/
foreach($file as $fname)
{
	$new_content='';
	$file_content =file_get_contents($fname);
	$file_array = preg_split('/function/', $file_content);
	$new_content = array_shift($file_array);
	$keys = array();
	foreach($file_array as $fnctn)
	{
		$keys[] = trim(substr($fnctn[0],0,strpos($fnctn[0],'(')));
	}
	//$file_array = array_combine($keys,$file_array); 
}

print_r($file_array);

/*function getPHPStruct()
{
	return true;
}*/

	echo "ok!";
