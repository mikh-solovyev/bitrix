<?
header("Access-Control-Allow-Origin: *");
define("NO_KEEP_STATISTIC", true); //Не учитываем статистику
define("NOT_CHECK_PERMISSIONS", true); //Не учитываем права доступа
require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_before.php");
header('Content-type: application/json');

global $DB;
$result = [];

$arForms = [ 
	"sending" => 1,
	"consultation" => 2,
	"Application" => 3,
	"request_call" => 4,
	"feedback" => 5,
	"sing_up_for_viewing" => 6,
	"new_quarter" => 7,
];

$formId = false;
$arValues = false;
$formId = $arForms[$_REQUEST["type"]];

if($formId == 1)
{
	$arValues = array (
		"form_text_1" => $_REQUEST["name"],
		"form_text_2" => $_REQUEST["email"],
		"form_text_3" => $_REQUEST["phone"],
		"form_text_4" => $_REQUEST["project"],
	);
}
elseif($formId == 2)
{
	$arValues = array (
		"form_text_5" => $_REQUEST["name"],
		"form_text_6" => $_REQUEST["phone"],
		"form_text_7" => $_REQUEST["project"],
	);
}
elseif($formId == 3)
{
	$arValues = array (
		"form_text_8" => $_REQUEST["name"],
		"form_text_9" => $_REQUEST["phone"],
		"form_text_10" => $_REQUEST["project"],
	);
}
elseif($formId == 4)
{
	$arValues = array (
		"form_text_11" => $_REQUEST["name"],
		"form_text_12" => $_REQUEST["phone"],
		"form_text_13" => $_REQUEST["project"],
	);
}
elseif($formId == 5)
{
	$arValues = array (
		"form_text_14" => $_REQUEST["grade"],
		"form_text_15" => $_REQUEST["email"],
		"form_text_16" => $_REQUEST["comment"],
	);
}
elseif($formId == 6)
{
	$arValues = array (
		"form_text_17" => $_REQUEST["name"],
		"form_text_18" => $_REQUEST["email"],
		"form_text_19" => $_REQUEST["phone"],
		"form_text_20" => $_REQUEST["project"],
	);
}
elseif($formId == 7)
{
	$arValues = array (
		"form_text_21" => $_REQUEST["name"],
		"form_text_22" => $_REQUEST["email"],
		"form_text_23" => $_REQUEST["phone"],
	);
}

if($formId > 0)
{
	CModule::IncludeModule("form");
	$RESULT_ID = CFormResult::Add($formId, $arValues);
	CFormCRM::onResultAdded($formId, $RESULT_ID);
	CFormResult::SetEvent($RESULT_ID);
	CFormResult::Mail($RESULT_ID);
}

?>