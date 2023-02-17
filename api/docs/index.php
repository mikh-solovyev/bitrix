<?
header("Access-Control-Allow-Origin: *");
define("NO_KEEP_STATISTIC", true); //Не учитываем статистику
define("NOT_CHECK_PERMISSIONS", true); //Не учитываем права доступа
require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_before.php");
//header('Content-type: application/json');

$result = [];
CModule::IncludeModule("iblock");

function formatFileSize($size) {
    $a = array("B", "KB", "MB", "GB", "TB", "PB");
    $pos = 0;
    while ($size >= 1024) {
        $size /= 1024;
        $pos++;
    }
    return round($size,2)." ".$a[$pos];
}

$filter = ["IBLOCK_ID" => 2, "ACTIVE" => "Y", "SECTION_ID" => false];
if($_REQUEST["jk"]) 
{
	$filter["PROPERTY_JK_VALUE_XML_ID"] = $_REQUEST["jk"];
}

$dbSections = CIBlockSection::GetList(array("sort" => "asc"), $filter);

while($arSection = $dbSections->Fetch())
{
	$result[$arSection["ID"]]["name"] = $arSection["NAME"];
	
	$filesFilter = ["IBLOCK_ID" => 2, "ACTIVE" => "Y", "SECTION_ID" => $arSection["ID"]];
	if($_REQUEST["project"]) 
	{
		$filesFilter["PROPERTY_JK_VALUE"] = $_REQUEST["project"];
	}
	
	if($_REQUEST["city"]) $filesFilter["PROPERTY_CITY_VALUE"] = $_REQUEST["city"];
	if($_REQUEST["jk"]) $filesFilter["PROPERTY_JK_VALUE"] = $_REQUEST["jk"];
	if($_REQUEST["year"]) $filesFilter["PROPERTY_YEAR_VALUE"] = $_REQUEST["year"];
	
	// pr($filesFilter);
	
	$dbFiles = CIBlockElement::GetList(array("sort" => "asc"), $filesFilter);
	while($obFileElement = $dbFiles->GetNextElement())
	{
		$arFileElement = $obFileElement->GetFields();
		$arFileElement["PROPERTIES"] = $obFileElement->GetProperties();
		$fileUrl = "http:/".$_SERVER["SERVER_NAME"].CFile::GetPath($arFileElement["PROPERTIES"]["FILE"]["VALUE"]);
		$date = explode(" ", $arFileElement["ACTIVE_FROM"])[0];
		
		$pathinfo = pathinfo($fileUrl);
		$size = formatFileSize(filesize($_SERVER["DOCUMENT_ROOT"].CFile::GetPath($arFileElement["PROPERTIES"]["FILE"]["VALUE"])));
		
		$result[$arSection["ID"]]["files"][] = array(
			"name" => $arFileElement["NAME"],
			"url" => $fileUrl,
			"extension" => $pathinfo["extension"],
			"size" => $size,
			"date" => $date,
		);
	}
	
	$filterSub = ["IBLOCK_ID" => 2, "ACTIVE" => "Y", "SECTION_ID" => $arSection["ID"]];
	$dbSubSections = CIBlockSection::GetList(array("sort" => "asc"), $filterSub);
	while($arSubSection = $dbSubSections->Fetch())
	{
		$result[$arSection["ID"]]["subcategories"][$arSubSection["ID"]]["name"] = $arSubSection["NAME"];
		$filesFilter = ["IBLOCK_ID" => 2, "ACTIVE" => "Y", "SECTION_ID" => $arSubSection["ID"]];
		if($_REQUEST["project"]) 
		{
			$filesFilter["PROPERTY_JK_VALUE"] = $_REQUEST["project"];
		}
		if($_REQUEST["city"]) $filesFilter["PROPERTY_CITY_VALUE"] = $_REQUEST["city"];
		if($_REQUEST["jk"]) $filesFilter["PROPERTY_JK_VALUE"] = $_REQUEST["jk"];
		if($_REQUEST["year"]) $filesFilter["PROPERTY_YEAR_VALUE"] = $_REQUEST["year"];
		
		$dbFiles = CIBlockElement::GetList(array("sort" => "asc"), $filesFilter);
		while($obFileElement = $dbFiles->GetNextElement())
		{
			$arFileElement = $obFileElement->GetFields();
			$arFileElement["PROPERTIES"] = $obFileElement->GetProperties();
			$fileUrl = "http:/".$_SERVER["SERVER_NAME"].CFile::GetPath($arFileElement["PROPERTIES"]["FILE"]["VALUE"]);
			$date = explode(" ", $arFileElement["ACTIVE_FROM"])[0];
			
			$pathinfo = pathinfo($fileUrl);
			$size = formatFileSize(filesize($_SERVER["DOCUMENT_ROOT"].CFile::GetPath($arFileElement["PROPERTIES"]["FILE"]["VALUE"])));
			
			$result[$arSection["ID"]]["subcategories"][$arSubSection["ID"]]["files"][] = array(
				"name" => $arFileElement["NAME"],
				"url" => $fileUrl,
				"extension" => $pathinfo["extension"],
				"size" => $size,
				"date" => $date,
			);
		}
	}
}

// pr($result);

echo \Bitrix\Main\Web\Json::encode($result);
die();
?>