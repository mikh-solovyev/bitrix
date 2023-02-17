<?
header("Access-Control-Allow-Origin: *");
define("NO_KEEP_STATISTIC", true); //Не учитываем статистику
define("NOT_CHECK_PERMISSIONS", true); //Не учитываем права доступа
require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_before.php");
header('Content-type: application/json');

global $DB;
$result = [];

if($_REQUEST["type"] == "rooms")
{
	$res = $DB->Query("SELECT DISTINCT VALUE FROM b_iblock_element_property WHERE IBLOCK_PROPERTY_ID=376 ORDER BY VALUE ASC;");
	while($row = $res->Fetch()) $result["rooms"][] = $row["VALUE"];
}
elseif($_REQUEST["type"] == "all")
{
	CModule::IncludeModule("iblock");
	
	$arNeedRooms = array(
		"C", 
		"1", 
		"2E", 
		"2",  
		"3E",
		"3",  
		"4E", 
	);
	
	$filter = ["IBLOCK_ID" => 3, "ACTIVE" => "Y"];
	$filter["!=PROPERTY__kvartiry_na_etazhe"] = "паркинг";
	
	if($_REQUEST["city"]) $filter["PROPERTY_gorod"] = $_REQUEST["city"];
	if($_REQUEST["project"]) $filter["PROPERTY_project"] = $_REQUEST["project"];
	
	
	
	$arMinPriceEl = CIBlockElement::GetList(array("property_stoimost_" => "asc"), $filter, false, array("nPageSize" => 1), array("property_stoimost_"))->Fetch();
	$arMaxPriceEl = CIBlockElement::GetList(array("property_stoimost_" => "desc"), $filter, false, array("nPageSize" => 1), array("property_stoimost_"))->Fetch();
	$result["price"] = array(
		"min" => $arMinPriceEl["PROPERTY_STOIMOST__VALUE"],
		"max" => $arMaxPriceEl["PROPERTY_STOIMOST__VALUE"],
	);
	
	$arMinAreaEl = CIBlockElement::GetList(array("property_ploshchad_s_balkonami_" => "asc"), $filter, false, array("nPageSize" => 1), array("property_ploshchad_s_balkonami_"))->Fetch();
	$arMaxAreaEl = CIBlockElement::GetList(array("property_ploshchad_s_balkonami_" => "desc"), $filter, false, array("nPageSize" => 1), array("property_ploshchad_s_balkonami_"))->Fetch();
	$result["area"] = array(
		"min" => $arMinAreaEl["PROPERTY_PLOSHCHAD_S_BALKONAMI__VALUE"],
		"max" => $arMaxAreaEl["PROPERTY_PLOSHCHAD_S_BALKONAMI__VALUE"],
	);
	
	$totalFilter = array(
		"IBLOCK_ID" => 3, 
		"ACTIVE" => "Y"
	);
	if($_REQUEST["project"]) $totalFilter["PROPERTY_project"] = $_REQUEST["project"];
	if($_REQUEST["city"]) $totalFilter["PROPERTY_gorod"] = $_REQUEST["city"];
	// pr($totalFilter);
	$dbTotal = CIBlockElement::GetList(array("sort" => "asc"), $totalFilter);
	$result["total_count"] = $dbTotal->result->num_rows;

	$result["roomsNotSort"] = [];
	while($obElement = $dbTotal->GetNextElement())
	{
		$arElement = $obElement->GetFields();
		$arElement["PROPERTIES"] = $obElement->GetProperties();
		
		$result["roomsNotSort"][] = $arElement["PROPERTIES"]["kolichestvo_komnat_"]["VALUE"];
		$result["blocks"][] = $arElement["PROPERTIES"]["korpus"]["VALUE"];
		$result["floors"][] = $arElement["PROPERTIES"]["etazh"]["VALUE"];
		$result["data_vydachi_klyuchey"][] = $arElement["PROPERTIES"]["vydacha_klyuchey"]["VALUE"];
		if(trim($arElement["PROPERTIES"]["otdelka_dobavit"]["VALUE"]) != "") $result["decoration"][] = mb_ucfirst($arElement["PROPERTIES"]["otdelka_dobavit"]["VALUE"], "utf8");
	}
	$result["roomsNotSort"] = array_unique($result["roomsNotSort"]);
	$result["blocks"] = array_unique($result["blocks"]);
	$result["floors"] = array_unique($result["floors"]);
	$result["data_vydachi_klyuchey"] = array_unique($result["data_vydachi_klyuchey"]);
	$result["decoration"] = array_unique($result["decoration"]);
	
	foreach($arNeedRooms as $roomType) if(in_array($roomType, $result["roomsNotSort"])) $result["rooms"][] = $roomType;
}
$result["floors"] = array("min" => min($result["floors"]), "max" => max($result["floors"]));
$result["blocks"] = array_values($result["blocks"]);
$result["data_vydachi_klyuchey"] = array_values($result["data_vydachi_klyuchey"]);
$result["decoration"] = array_values($result["decoration"]);

// pr($result);

echo \Bitrix\Main\Web\Json::encode($result);
die();

function mb_ucfirst($string, $encoding)
{
    $firstChar = mb_substr($string, 0, 1, $encoding);
    $then = mb_substr($string, 1, null, $encoding);
    return mb_strtoupper($firstChar, $encoding) . $then;
}
?>