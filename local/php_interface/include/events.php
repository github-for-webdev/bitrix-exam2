<?
IncludeModuleLangFile(__FILE__);
AddEventHandler("iblock", "OnBeforeIBlockElementUpdate", array("Ex2", "Ex2_50"));
AddEventHandler("main", "OnBeforeEventAdd", array("Ex2", "Ex2_51"));
AddEventHandler("main", "OnEpilog", array("Ex2", "Ex2_93"));

class Ex2
{
    function Ex2_50(&$arFields)
	{
        if ($arFields["IBLOCK_ID"] == IBLOCK_CATALOG) {
            if ($arFields["ACTIVE"] == "N") {
                $arSelect = array(
					"ID",
					"IBLOCK_ID",
					"NAME",
					"SHOW_COUNTER"
				);
				$arFilter = array(
					"IBLOCK" => IBLOCK_CATALOG,
					"ID" => $arFields["ID"]
				);
				$res = CIBlockElement::GetList(array(), $arFilter, false, false, $arSelect);
				$arItems = $res->Fetch();
				if ($arItems["SHOW_COUNTER"] > MAX_COUNT) {
					global $APPLICATION;
					$APPLICATION->throwException(GetMessage("EX2_50_ERROR", array("#COUNT#" => $arItems["SHOW_COUNTER"])));
					return false;
				}
            }
        }
	}
    function Ex2_51(&$event, &$lid, &$arFields)
    {
        if ($event == "FEEDBACK_FORM") {
            global $USER;
            if ($USER->isAuthorized()) {
                $arFields["AUTHOR"] = GetMessage(
                    "EX2_51_AUTH_USER",
                    array(
                        "ID" => $USER->GetID(),
                        "LOGIN" => $USER->GetLogin(),
                        "NAME" => $USER->GetFullName(),
                        "NAME_FORM" => $arFields["AUTHOR"]
                    )
                );
            } else {
                $arFields["AUTHOR"] = GetMessage(
                    "EX2_51_NO_AUTH_USER",
                    array(
                        "#NAME_FORM#" => $arFields["AUTHOR"]
                    )
                );
            }
            CEventLog::Add(
                array(
                    "SEVERITY" => "SECURITY",
                    "AUDIT_TYPE_ID"  => GetMessage("EX2_51_REPLACEMENT"),
                    "MODULE_ID" => "main",
                    "ITEM_ID" => $event,
                    "DESCRIPTION" => GetMessage("EX2_51_REPLACEMENT") . '-' . $arFields["AUTHOR"]
                )
            );
        }
    }
    function Ex2_93()
	{
		if (defined("ERROR_404") && ERROR_404 == "Y")
		{
			global $APPLICATION;
			$APPLICATION->RestartBuffer();
			include $_SERVER["DOCUMENT_ROOT"] . "/" . SITE_TEMPLATE_PATH . "/header.php";
			include $_SERVER["DOCUMENT_ROOT"] . "/404.php";
			include $_SERVER["DOCUMENT_ROOT"] . "/" . SITE_TEMPLATE_PATH . "/footer.php";
			CEventLog::Add(
				array(
					"SEVERITY"	=>	"INFO",
					"AUDIT_TYPE_ID"	=>	"ERROR_404",
					"MODULE_ID"	=>	"main",
					"DESCRIPTION"	=>	$APPLICATION->GetCurPage()
				)
            );
		}
	}
}
