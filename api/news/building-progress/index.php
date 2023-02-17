<?
header("Access-Control-Allow-Origin: *");
define("NO_KEEP_STATISTIC", true); //Не учитываем статистику
define("NOT_CHECK_PERMISSIONS", true); //Не учитываем права доступа
require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_before.php");
header('Content-type: application/json');

$result = [
	"items" => []
];

CModule::IncludeModule("iblock");
$sort = ["active_from" => "desc"];
$arFilter = ["IBLOCK_ID" => 5, "ACTIVE" => "Y"];

if($_REQUEST["dateForm"]) $arFilter['>=DATE_ACTIVE_FROM'] = date('d.m.Y H:i:s', $_REQUEST["dateForm"]);
if($_REQUEST["dateTo"]) $arFilter['<=DATE_ACTIVE_FROM'] = date('d.m.Y H:i:s', $_REQUEST["dateTo"]);
if($_REQUEST["ciry"]) $arFilter['PROPERTY_CITY'] = $_REQUEST["ciry"];
if($_REQUEST["block"]) $arFilter['PROPERTY_BLOCK'] = $_REQUEST["block"];
if($_REQUEST["project"]) $arFilter['PROPERTY_PROJECT_VALUE'] = $_REQUEST["project"];

$page = $_REQUEST["page"] ? $_REQUEST["page"] : 1;
$count = $_REQUEST["count"] ? $_REQUEST["count"] : 50;

$dbElements = CIBlockElement::GetList($sort, $arFilter, false, array("iNumPage" => $page, "nPageSize" => $count));
$NavPageCount = $dbElements->NavPageCount;
$NavPageNomer = $dbElements->NavPageNomer;

$arProjects = [
	"souzny" => "Союзный",
	"ok" => "Одинцовские кварталы",
	"vremena-goda" => "Времена года",
	"europa" => "Европа",
];

while($obElement = $dbElements->GetNextElement())
{
	$arElement = $obElement->GetFields();
	$arElement["PROPERTIES"] = $obElement->GetProperties();
	
	$arItem = array(
		"date" => $arElement["NAME"],
		"id" => $arElement["ID"],
		"project" => $arElement["PROPERTIES"]["PROJECT"]["VALUE"],
		"project_name" => $arProjects[$arElement["PROPERTIES"]["PROJECT"]["VALUE"]],
		"ciry" => $arElement["PROPERTIES"]["CITY"]["VALUE"],
		"preview" => "http://".$_SERVER["SERVER_NAME"].CFile::GetPath($arElement["PREVIEW_PICTURE"]),
		"block" => $arElement["PROPERTIES"]["BLOCK"]["VALUE"],
	);
	
	if($arElement["PREVIEW_PICTURE"]) $arItem["preview"] = "http://".$_SERVER["SERVER_NAME"].CFile::GetPath($arElement["PREVIEW_PICTURE"]);
	if($arElement["PROPERTIES"]["MORE_PHOTO"]["VALUE"][0])
	{
		foreach($arElement["PROPERTIES"]["MORE_PHOTO"]["VALUE"] as $photoId) $arItem["images"][] = "http://".$_SERVER["SERVER_NAME"].CFile::GetPath($photoId);
	}
	$result["items"][] = $arItem;
}

// pr($result);

echo \Bitrix\Main\Web\Json::encode($result);
die();
?>