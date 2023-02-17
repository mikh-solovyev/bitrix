<?
header("Access-Control-Allow-Origin: *");
define("NO_KEEP_STATISTIC", true); //Не учитываем статистику
define("NOT_CHECK_PERMISSIONS", true); //Не учитываем права доступа
require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_before.php");
header('Content-type: application/json');

$result = [];
$ID = $_REQUEST["id"];

if($ID > 0) 
{


	CModule::IncludeModule("iblock");
	$sort = ["active_from" => "desc"];
	$filter = ["IBLOCK_ID" => 1, "ACTIVE" => "Y", "ID" => $ID];

	$dbElements = CIBlockElement::GetList($sort, $filter);
	// pr($dbElements);

	if($obElement = $dbElements->GetNextElement())
	{
		$arElement = $obElement->GetFields();
		$arElement["PROPERTIES"] = $obElement->GetProperties();
		$arItem = array(
			"id" 			=> $arElement["ID"],
			"preview" 		=> "http://".$_SERVER["SERVER_NAME"].CFile::GetPath($arElement["PREVIEW_PICTURE"]),
			"detail" 		=> "http://".$_SERVER["SERVER_NAME"].CFile::GetPath($arElement["DETAIL_PICTURE"]),
			"params" 		=> implode(", ", $arElement["PROPERTIES"]["PARAMS"]["VALUE"]),
			"title" 		=> $arElement["NAME"],
			"building"		=> $arElement["PROPERTIES"]["BUILDING"]["VALUE"],
			"date" 			=> FormatDate("d F Y", MakeTimeStamp($arElement['ACTIVE_FROM'])),
			"description" 	=> $arElement["PREVIEW_TEXT"],
			"text" 			=> str_ireplace('"/upload', '"http://'.$_SERVER["SERVER_NAME"].'/upload', $arElement["DETAIL_TEXT"]),
		);
		
		foreach($arElement["PROPERTIES"]["MORE_PHOTO"]["VALUE"] as $photoId) $arItem["images"][] = "http://".$_SERVER["SERVER_NAME"].CFile::GetPath($photoId);
		
		$result["article"] = $arItem;
	}
	
	$prev_articlesFilter = array("IBLOCK_ID" => 1, "ACTIVE" => "Y", "<DATE_ACTIVE_FROM" => $arElement['ACTIVE_FROM'], "!ID" => $arElement["ID"]);
	$dbPrevElements = CIBlockElement::GetList(
		["active_from" => "desc"],
		$prev_articlesFilter,
		false, 
		array("nPageSize" => 2)
	);
	while($obPrevElement = $dbPrevElements->GetNextElement())
	{
		$arPrevElement = $obPrevElement->GetFields();
		$result["article"]["prev_articles"][] = array(
			"id" => $arPrevElement["ID"],
			"preview" => "http://".$_SERVER["SERVER_NAME"].CFile::GetPath($arPrevElement["PREVIEW_PICTURE"]),
			"title" => $arPrevElement["NAME"],
			"date" => FormatDate("d F Y", MakeTimeStamp($arPrevElement['ACTIVE_FROM'])),
		);
	}
    $result["article"]["prev_articles"] = array_reverse($result["article"]["prev_articles"]);
	
	$next_articlesFilter = array("IBLOCK_ID" => 1, "ACTIVE" => "Y", ">DATE_ACTIVE_FROM" => $arElement['ACTIVE_FROM'], "!ID" => $arElement["ID"]);
	$dbNextElements = CIBlockElement::GetList(["active_from" => "asc"], $next_articlesFilter, false, array("nPageSize" => 2));
	while($obNextElement = $dbNextElements->GetNextElement())
	{
		$arNextElement = $obNextElement->GetFields();
		$result["article"]["next_articles"][] = array(
			"id" => $arNextElement["ID"],
			"preview" => "http://".$_SERVER["SERVER_NAME"].CFile::GetPath($arNextElement["PREVIEW_PICTURE"]),
			"title" => $arNextElement["NAME"],
			"date" => FormatDate("d F Y", MakeTimeStamp($arNextElement['ACTIVE_FROM'])),
		);
	}
	
}
// pr($result);

echo \Bitrix\Main\Web\Json::encode($result);
die();
?>