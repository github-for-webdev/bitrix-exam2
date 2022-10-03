<?
IncludeModuleLangFile(__FILE__);
AddEventHandler("iblock", "OnBeforeIBlockElementUpdate", array("Ex2", "Ex2_50"));
AddEventHandler("main", "OnBeforeEventAdd", array("Ex2", "Ex2_51"));
AddEventHandler("main", "OnEpilog", array("Ex2", "Ex2_93"));
AddEventHandler("main", "OnBeforeProlog", array("Ex2", "Ex2_94"));
AddEventHandler("main", "OnBuildGlobalMenu", array("Ex2", "Ex2_95"));

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
    function Ex2_94()
    {
        global $APPLICATION;

        if (\Bitrix\Main\Loader::includeModule("iblock")) {
            $arFilter = array(
                "IBLOCK_ID" => IBLOCK_META,
                "NAME" => $APPLICATION->GetCurDir()
            );
            $arSelect = array(
                "IBLOCK_ID",
                "ID",
                "PROPERTY_title",
                "PROPERTY_description",
            );

            $ob = CIBlockElement::GetList(
                array(),
                $arFilter,
                false,
                false,
                $arSelect
            );
            if ($arRes = $ob->Fetch()) {
                $APPLICATION->SetPageProperty("title", $arRes["PROPERTY_TITLE_VALUE"]);
                $APPLICATION->SetPageProperty("description", $arRes["PROPERTY_DESCRIPTION_VALUE"]);
            }
        }
    }
    function Ex2_95(&$aGlobalMenu, &$aModuleMenu)
	{
        $isAdmin = false;
        $isManager = false;

        global $USER;
        $userGroup = CUSER::GetUserGroupList($USER->GetID());
        $contentGroupID = CGroup::GetList(
            "c_sort",
            "asc",
            array(
                "STRING_ID" => "content_editor"
            )
        )->Fetch()["ID"];

        while ($group = $userGroup->Fetch()) {

            if ($group["GROUP_ID"] == 1) {
                $isAdmin = true;
            }

            if ($group["GROUP_ID"] == $contentGroupID) {
                $isManager = true;
            }
        }

        if (!$isAdmin && $isManager) {
            foreach ($aModuleMenu as $item) {
                if ($item["items_id"] == "menu_iblock_/news") {
                    $aModuleMenu = array($item);
                    foreach ($item["items"] as $childItem) {
                        if ($childItem["items_id"] == "menu_iblock_/news/1") {
                            $aModuleMenu[0]["items"] = array($childItem);
                            break;
                        }
                    }
                    break;
                }
            }
            $aGlobalMenu  = array(
                "global_menu_content" => $aGlobalMenu["global_menu_content"]
            );
        }
    }
}
