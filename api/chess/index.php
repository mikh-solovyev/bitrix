<?
header("Access-Control-Allow-Origin: *");
define("NO_KEEP_STATISTIC", true); //Не учитываем статистику
define("NOT_CHECK_PERMISSIONS", true); //Не учитываем права доступа
require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_before.php");
header('Content-type: application/json');

$result = [];

CModule::IncludeModule("iblock");
$filter = ["IBLOCK_ID" => 3, "ACTIVE" => "Y"];

if($_REQUEST["floor"]) 
{
	$filter["PROPERTY_etazh"] = $_REQUEST["floor"];
}
if($_REQUEST["min_floor"]) 
{
	$filter[">=PROPERTY_etazh"] = $_REQUEST["min_floor"];
}
if($_REQUEST["max_floor"]) 
{
	$filter["<=PROPERTY_etazh"] = $_REQUEST["max_floor"];
}
if($_REQUEST["rooms"])
{
	$arRooms = explode(";", $_REQUEST["rooms"]);
	$filter["PROPERTY_kolichestvo_komnat_"] = $arRooms;
}
if($_REQUEST["areaFrom"]) $filter[">=PROPERTY_ploshchad_s_balkonami"] = $_REQUEST["areaFrom"];
if($_REQUEST["areaTo"]) $filter["<=PROPERTY_ploshchad_s_balkonami"] = $_REQUEST["areaTo"];
if($_REQUEST["priceFrom"]) $filter[">=PROPERTY_stoimost_"] = $_REQUEST["priceFrom"];
if($_REQUEST["priceTo"]) $filter["<=PROPERTY_stoimost_"] = $_REQUEST["priceTo"];
if($_REQUEST["price"]) $filter["<PROPERTY_stoimost_"] = $_REQUEST["price"];

if($_REQUEST["block"]) $filter["PROPERTY_korpus"] = explode(";", $_REQUEST["block"]);
if($_REQUEST["project"]) $filter["PROPERTY_project"] = $_REQUEST["project"];
if($_REQUEST["city"]) $filter["PROPERTY_gorod"] = $_REQUEST["city"];

if($_REQUEST["id"])
{
	$filter["XML_ID"] = $_REQUEST["id"];
}
$filter["!=PROPERTY__kvartiry_na_etazhe"] = "паркинг";

// pr($filter);

$page = $_REQUEST["page"] ? $_REQUEST["page"] : 1;
$count = $_REQUEST["count"] ? $_REQUEST["count"] : 300000;

$dbCounts = CIBlockElement::GetList($sort, $filter);
$totalCount = $dbCounts->result->num_rows;

$dbElements = CIBlockElement::GetList($sort, $filter, false, array("iNumPage" => $page, "nPageSize" => $count));

if($page > $dbElements->NavPageCount)
{
	echo \Bitrix\Main\Web\Json::encode(array(
		"items" => [],
		"totalCount" => $totalCount
	));
	die();
}
// pr($dbElements->NavPageCount);

while($obElement = $dbElements->GetNextElement())
{
	$arElement = $obElement->GetFields();
	$arElement["PROPERTIES"] = $obElement->GetProperties();
	
	$arChess = explode("-", $arElement["PROPERTIES"]["kod_dlya_shakhmatki"]["VALUE"]);
	
	$item = array(
		"id" => $arElement["XML_ID"],
		"number_of_rooms" => $arElement["PROPERTIES"]["kolichestvo_komnat_"]["VALUE"],
		"plan_image" => $arElement["PROPERTIES"]["planirovki"]["VALUE"],
		"plan_floor" => $arElement["PROPERTIES"]["_plan_poetazhnyy"]["VALUE"],
		"genplan" => $arElement["PROPERTIES"]["genplan"]["VALUE"],
		"dom_sektsiya_na_plane" => $arElement["PROPERTIES"]["dom_sektsiya_na_plane"]["VALUE"],
		"price" => $arElement["PROPERTIES"]["stoimost_"]["VALUE"],
		"mortgage_price" => $arElement["PROPERTIES"]["_ezhemesyachnyy_platezh_ipoteka"]["VALUE"],
		"decoration" => $arElement["PROPERTIES"]["otdelka_dobavit"]["VALUE"],
		"sale_price" => (float)$arElement["PROPERTIES"]["skidka_natsenka"]["VALUE"] > 0 ? $arElement["PROPERTIES"]["skidka_natsenka"]["VALUE"] : 0,
		"otdelka" => mb_ucfirst(mb_strtolower($arElement["PROPERTIES"]["otdelka_berem_iz_spiska"]["VALUE"]), "utf8"),
		"3d_render" => $arElement["PROPERTIES"]["tride_render_planirovki_s_mebelyu"]["VALUE"],
		"status" => ($arElement["PROPERTIES"]["ustupka_bron"]["VALUE"] != "" && $arElement["PROPERTIES"]["ustupka_bron"]["VALUE"] != "-") ? $arElement["PROPERTIES"]["ustupka_bron"]["VALUE"] : ($arElement["PROPERTIES"]["aktsiya_"]["VALUE"] ? $arElement["PROPERTIES"]["aktsiya_"]["VALUE"] : "Уступка"),
		"project" => $arElement["PROPERTIES"]["project"]["VALUE"],
		"city" => $arElement["PROPERTIES"]["gorod"]["VALUE"],
		
		// "project" => $arChess[0],
		"block" => (int)$arChess[1],
		"section" => (int)$arElement["PROPERTIES"]["sektsiya"]["VALUE"],
		"entrance" => $arChess[2],
		"floor" => (int)$arChess[3],
		"number_on_floor" => (int)$arChess[4],
	);
	$item["status"] = mb_ucfirst(mb_strtolower($item["status"]), "utf8");
	$result["items"][] = $item;
}

$result["totalCount"] = $totalCount;


if($_REQUEST["id"]) $result = $result["items"][0];

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