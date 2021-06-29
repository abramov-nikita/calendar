<?

if (isset($_SERVER["HTTP_X_REQUESTED_WITH"]) && $_SERVER["HTTP_X_REQUESTED_WITH"] == "XMLHttpRequest") {
    require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_before.php");
    //Список магазинов
    $arShops = hl_getlist(HLBL_SHOPS, array('UF_STATUS' => 1), 'UF_XML_ID', array('UF_NAME' => 'ASC'));
    $currentNameShop = $arShops[current(hl_getlist(HLBL_PHOTOSSHOWCASE, array('ID' => trim(strip_tags($_REQUEST['ID'])))))['UF_SHOP']]['UF_NAME'];
    $arDepartaments = hl_getlist(HLBL_PHOTOSSHOWCASEDEPARTAMENTS, array(), 'ID');
    foreach (hl_getlist(HLBL_PHOTOSSHOWCASEPHOTOS, array('UF_ITEM' => trim(strip_tags($_REQUEST['ID'])))) as $item) {
        if (!empty($res = hl_getlist(HLBL_PHOTOSSHOWCASECOMMENTS, array('UF_ITEM' => $item['ID']), 'ID'))){
            $resCountComments = count($res);
        }else{
            $resCountComments = 0;
        }
        if (($USER->getId() == $item['UF_USER_DOWNLOAD']) || ($USER->getId() == 1)){
            $edit = true;
        }else{
            $edit = false;
        }
        $resPhotoDayShop[$arDepartaments[$item['UF_DEPARTAMENT']]['UF_NAME']][] = array(
            'PHOTO_ID' => $item['ID'],
            'UF_PHOTO' => CFile::GetFileArray($item['UF_PHOTO'])['SRC'],
            'UF_PHOTO_PREVIEW' => CFile::ResizeImageGet($item['UF_PHOTO'], array('width' => 100, 'height' => 100), BX_RESIZE_IMAGE_PROPORTIONAL, true)['src'],
            'COUNT_COMMENTS' => $resCountComments,
            'UF_USER_DOWNLOAD' => $item['UF_USER_DOWNLOAD'],
            'EDIT' => $edit,
            'DEPARTAMENT' => $item['UF_DEPARTAMENT']
        );
    }
    //    pre($resPhotoDayShop);
    echo json_encode(
        array(
            'nameShop' => trim($currentNameShop),
            'infoHl' => $resPhotoDayShop,
        )
    );
} else {
    http_response_code(404);

    return;
}
