<?
header("Access-Control-Allow-Origin: *");
define("NO_KEEP_STATISTIC", true); //Не учитываем статистику
define("NOT_CHECK_PERMISSIONS", true); //Не учитываем права доступа
require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_before.php");
header('Content-type: application/json');
$result["items"] = [];
CModule::IncludeModule("iblock");
$arFilter = [
    "IBLOCK_ID" => 4, "ACTIVE" => "Y"
];
$arSelect = [
    "ID", "IBLOCK_ID", "NAME", "CODE"
];

if($_REQUEST["project"])
    $arFilter['CODE'] = $_REQUEST["project"];
$bankListProps = [];
$bankList = CIBlockElement::GetList(["SORT" => "asc"], $arFilter, false, array());
if($obBankList = $bankList->GetNextElement()) {
    $bankListProps = $obBankList->GetProperties();
}

if(!empty($bankListProps["MORTGAGE"]["VALUE"])) {
    $arFilter = ["IBLOCK_ID" => 6, "ACTIVE" => "Y", "ID" => $bankListProps["MORTGAGE"]["VALUE"]];
    $dbElement = CIBlockElement::GetList(["SORT" => "asc"], $arFilter, false, []);
    while ($obElement = $dbElement->GetNextElement()) {
        $arElementFields = $obElement->GetFields();
        $arElementProps = $obElement->GetProperties();
        $result["items"][] = [
            "id" => $arElementFields["ID"],
            "preview" => "http://" . $_SERVER["SERVER_NAME"] . CFile::GetPath($arElementFields["PREVIEW_PICTURE"]),
            "title" => $arElementFields["NAME"],
            'rate' => floatval(str_replace(',', '.', $arElementProps["RATE"]["VALUE"])),
            'first_payment' => $arElementProps["FIRST_PAYMENT"]["VALUE"],
            'period' => $arElementProps["PERIOD"]["VALUE"],
            'sum' => $arElementProps["SUM"]["VALUE"],
        ];
    }
}
echo \Bitrix\Main\Web\Json::encode($result);
die();