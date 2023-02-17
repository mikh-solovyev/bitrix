<?
header("Access-Control-Allow-Origin: *");
define("NO_KEEP_STATISTIC", true); //Не учитываем статистику
define("NOT_CHECK_PERMISSIONS", true); //Не учитываем права доступа
require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_before.php");
header('Content-type: application/json');

$result = [];

CModule::IncludeModule("iblock");
$property_enums = CIBlockPropertyEnum::GetList(Array("DEF" => "DESC", "SORT" => "ASC"), Array("IBLOCK_ID" => 1, "CODE" => "TAGS"));

$arCodes = array(
	1 => "ok",
	2 => "europa",
	3 => "vremena-goda",
	4 => "souzny",
);

while($enum_fields = $property_enums->GetNext())
{
	$result[] = array(
		"id" => $enum_fields["ID"],
		"value" => $arCodes[$enum_fields["ID"]],
		"label" => $enum_fields["VALUE"]
	);
}
// pr($result);
echo \Bitrix\Main\Web\Json::encode($result);
die();
?>