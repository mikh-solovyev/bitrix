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
if($_REQUEST["areaFrom"]) $filter[">=PROPERTY_ploshchad_s_balkonami_"] = $_REQUEST["areaFrom"];
if($_REQUEST["areaTo"]) $filter["<=PROPERTY_ploshchad_s_balkonami_"] = $_REQUEST["areaTo"];
if($_REQUEST["priceFrom"]) $filter[">=PROPERTY_stoimost_"] = $_REQUEST["priceFrom"];
if($_REQUEST["priceTo"]) $filter["<=PROPERTY_stoimost_"] = $_REQUEST["priceTo"];
if($_REQUEST["price"]) $filter["<PROPERTY_stoimost_"] = $_REQUEST["price"];

if($_REQUEST["block"]) $filter["PROPERTY_korpus"] = explode(";", $_REQUEST["block"]);
if($_REQUEST["project"]) $filter["PROPERTY_project"] = $_REQUEST["project"];
if($_REQUEST["city"]) $filter["PROPERTY_gorod"] = $_REQUEST["city"];
if($_REQUEST["decoration"]) $filter["%PROPERTY_otdelka_dobavit"] = $_REQUEST["decoration"];

if($_REQUEST["id"])
{
	$filter["XML_ID"] = $_REQUEST["id"];
}
$filter["!=PROPERTY__kvartiry_na_etazhe"] = "паркинг";

$page = $_REQUEST["page"] ? $_REQUEST["page"] : 1;
$count = $_REQUEST["count"] ? $_REQUEST["count"] : 30;

$dbCounts = CIBlockElement::GetList($sort, $filter);
$totalCount = $dbCounts->result->num_rows;

// pr($filter);

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
		"decoration" => mb_ucfirst(mb_strtolower($arElement["PROPERTIES"]["otdelka_dobavit"]["VALUE"]), "utf8"),
		"city" => $arElement["PROPERTIES"]["gorod"]["VALUE"],
		"sale_price" => (float)$arElement["PROPERTIES"]["skidka_natsenka"]["VALUE"] > 0 ? $arElement["PROPERTIES"]["skidka_natsenka"]["VALUE"] : 0,
		// "otdelka" => mb_ucfirst(mb_strtolower($arElement["PROPERTIES"]["otdelka_dobavit"]["VALUE"]), "utf8"),
		"3d_render" => $arElement["PROPERTIES"]["tride_render_planirovki_s_mebelyu"]["VALUE"],
		"kod_dlya_shakhmatki" => $arElement["PROPERTIES"]["kod_dlya_shakhmatki"]["VALUE"],
		// "project" => "",
		// "favourited" => false,
		// "on_sale" => true,
		"status" => ($arElement["PROPERTIES"]["ustupka_bron"]["VALUE"] != "" && $arElement["PROPERTIES"]["ustupka_bron"]["VALUE"] != "-") ? $arElement["PROPERTIES"]["ustupka_bron"]["VALUE"] : ($arElement["PROPERTIES"]["aktsiya_"]["VALUE"] ? $arElement["PROPERTIES"]["aktsiya_"]["VALUE"] : "Уступка"),
		"project" => $arElement["PROPERTIES"]["project"]["VALUE"],
		"area" => array(
			"all" => $arElement["PROPERTIES"]["ploshchad_s_balkonami_"]["VALUE"],
			"living" => $arElement["PROPERTIES"]["ploshchad_bez_balkonov"]["VALUE"],
			"kitchen" => $arElement["PROPERTIES"]["kukhnya"]["VALUE"],
			"bathroom" => "",
			"second_bathroom" => "",
			"corridor" => $arElement["PROPERTIES"]["koridor"]["VALUE"],
			"wardrobe" => "",
			"balcony" => $arElement["PROPERTIES"]["obshchaya_ploshchad_balkonov"]["VALUE"],
			"finishing" => "",
		),
		"building" => array(
			"MCD" => "",
			"pedestrian" => "",
			"block" => $arElement["PROPERTIES"]["korpus"]["VALUE"], // номер корпуса
			"section" => $arElement["PROPERTIES"]["sektsiya"]["VALUE"], // номер секции
			"floor" => $arElement["PROPERTIES"]["etazh"]["VALUE"], // этаж
			"flat_number" => $arElement["PROPERTIES"]["_kvartiry"]["VALUE"], // номер квартиры
			"max_floor" => (int)$arElement["PROPERTIES"]["etazhey_v_dome"]["VALUE"], // максимальный этаж
			"keys_date" => $arElement["PROPERTIES"]["vydacha_klyuchey"]["VALUE"], // дата выдачи ключей 
		),
		"chess" => array(
			"project" => $arChess[0],
			"block" => (int)$arChess[1],
			"entrance" => $arChess[2],
			"floor" => (int)$arChess[3],
			"number_on_floor" => (int)$arChess[4],
		),
	);
	if(empty($item['decoration'])){
        $item['decoration'] = mb_ucfirst(mb_strtolower($arElement["PROPERTIES"]["otdelka"]["VALUE"]), "utf8");
    }

	if($arElement["PROPERTIES"]["_izobrazhenie_dopolnitelnoe_1"]["VALUE"]) $item["images"][] = $arElement["PROPERTIES"]["_izobrazhenie_dopolnitelnoe_1"]["VALUE"];
	if($arElement["PROPERTIES"]["_izobrazhenie_dopolnitelnoe_2"]["VALUE"]) $item["images"][] = $arElement["PROPERTIES"]["_izobrazhenie_dopolnitelnoe_2"]["VALUE"];
	if($arElement["PROPERTIES"]["_izobrazhenie_dopolnitelnoe_3"]["VALUE"]) $item["images"][] = $arElement["PROPERTIES"]["_izobrazhenie_dopolnitelnoe_3"]["VALUE"];
	if($arElement["PROPERTIES"]["_izobrazhenie_dopolnitelnoe_4"]["VALUE"]) $item["images"][] = $arElement["PROPERTIES"]["_izobrazhenie_dopolnitelnoe_4"]["VALUE"];
	if($arElement["PROPERTIES"]["_izobrazhenie_dopolnitelnoe_5"]["VALUE"]) $item["images"][] = $arElement["PROPERTIES"]["_izobrazhenie_dopolnitelnoe_5"]["VALUE"];
	
	$result["items"][] = $item;

	/*
	id: 1,
	number_of_rooms: 4,
	plan_image: "/images/photos/apartment_plan.png",
	images: [
		"/images/photos/finishing/finishing-1.jpg",
		"/images/photos/finishing/finishing-2.jpg",
	],
	decoration: "full", // см. п. 1
	price: 25_500_000,
	mortgage_price: 25_679, // цена ипотеки в месяц
	city: "Одинцово",
	sale_price: 12_720_390, // цена по скидке, `null` если нет скидки
	project: "Союзный",
	favourited: false,
	on_sale: true,
	status: "completed", // см. п. 2
	area: {
		all: 40.3, // общая площадь квартиры
		living: 30.3,
		kitchen: 9.6,
		bathroom: 2.2,
		second_bathroom: 1.7,
		corridor: 6,
		wardrobe: 2.6,
		balcony: 3.2,
		finishing: true,
	},
	building: {
		MCD: "Поезд МЦД",
		pedestrian: "5 мин",
		block: 2, // номер корпуса
		section: 1, // номер секции
		floor: 20, // этаж
		flat_number: 3, // номер квартиры
		max_floor: 24, // максимальный этаж
		keys_date: "дата выдачи ключей в формате ISO",
	}
	*/
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