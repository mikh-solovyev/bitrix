<?
header("Access-Control-Allow-Origin: *");
define("NO_KEEP_STATISTIC", true); //Не учитываем статистику
define("NOT_CHECK_PERMISSIONS", true); //Не учитываем права доступа
require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_before.php");
header('Content-type: application/json');

$result = [];

CModule::IncludeModule("iblock");
$sort = ["active_from" => "desc"];
$filter = ["IBLOCK_ID" => 1, "ACTIVE" => "Y"];

if($_REQUEST["tag"]) $filter["=PROPERTY_TAGS_VALUE"] = $_REQUEST["tag"];
if($_REQUEST["year"]) $filter["=PROPERTY_YEAR_VALUE"] = $_REQUEST["year"];

$page = $_REQUEST["page"] ? $_REQUEST["page"] : 1;
$count = $_REQUEST["count"] ? $_REQUEST["count"] : 10;

$dbElements = CIBlockElement::GetList($sort, $filter, false, array("iNumPage" => $page, "nPageSize" => $count));
$NavPageCount = $dbElements->NavPageCount;
$NavPageNomer = $dbElements->NavPageNomer;

$result["last_page"] = $NavPageNomer >= $NavPageCount ? true : false;
$result["next_page"] = $NavPageNomer < $NavPageCount ? ($NavPageNomer + 1) : false;

while($obElement = $dbElements->GetNextElement())
{
	$arElement = $obElement->GetFields();
	$arElement["PROPERTIES"] = $obElement->GetProperties();
	
	$arItem = array(
		"id" 			=> $arElement["ID"],
		"preview" 		=> "http://".$_SERVER["SERVER_NAME"].CFile::GetPath($arElement["PREVIEW_PICTURE"]),
		"params" 		=> implode(", ", $arElement["PROPERTIES"]["PARAMS"]["VALUE"]),
		"title" 		=> $arElement["NAME"],
		"building"		=> $arElement["PROPERTIES"]["BUILDING"]["VALUE"],
		"date" 			=> FormatDate("d F Y", MakeTimeStamp($arElement['ACTIVE_FROM'])),
		"description" 	=> $arElement["PREVIEW_TEXT"],
		"text" 			=> $arElement["DETAIL_TEXT"],
	);
	
	foreach($arElement["PROPERTIES"]["MORE_PHOTO"]["VALUE"] as $photoId) $arItem["images"][] = "http://".$_SERVER["SERVER_NAME"].CFile::GetPath($photoId);
	
	$result["articles"][] = $arItem;
}


// pr($result);
echo \Bitrix\Main\Web\Json::encode($result);
die();
?>