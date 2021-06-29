<?

if (isset($_SERVER["HTTP_X_REQUESTED_WITH"]) && $_SERVER["HTTP_X_REQUESTED_WITH"] == "XMLHttpRequest") {
    require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_before.php");
    $arDepartaments = hl_getlist(HLBL_PHOTOSSHOWCASEDEPARTAMENTS, [], 'ID');
    $arShop = current(hl_getlist(HLBL_SHOPS, ['UF_STATUS' => 1, 'ID' => trim(strip_tags($_REQUEST['ID']))], 'UF_XML_ID', ['UF_NAME' => 'ASC']));
    $currentNameShop = $arShop['UF_NAME'];
    $currentXmlShop = $arShop['UF_XML_ID'];
    $arRecords = hl_getlist(HLBL_PHOTOSSHOWCASE,
        [
            '=UF_SHOP'  => $currentXmlShop,
            '>=UF_DATE' => trim(strip_tags($_REQUEST['START_DATE'])),
            '<=UF_DATE' => trim(strip_tags($_REQUEST['END_DATE'])),
        ],
        'ID'
    );
    foreach (hl_getlist(HLBL_PHOTOSSHOWCASEPHOTOS, ['UF_ITEM' => array_keys($arRecords)]) as $item) {
        if ( ! empty($res = hl_getlist(HLBL_PHOTOSSHOWCASECOMMENTS, ['UF_ITEM' => $item['ID']], 'ID'))) {
            $resCountComments = count($res);
        } else {
            $resCountComments = 0;
        }
        $resPhotoDayShop[$arDepartaments[$item['UF_DEPARTAMENT']]['UF_NAME']][] = [
            'PHOTO_ID'         => $item['ID'],
            'UF_PHOTO'         => CFile::GetFileArray($item['UF_PHOTO'])['SRC'],
            'UF_PHOTO_PREVIEW' => CFile::ResizeImageGet($item['UF_PHOTO'], ['width' => 100, 'height' => 100], BX_RESIZE_IMAGE_PROPORTIONAL, true)['src'],
            'COUNT_COMMENTS'   => $resCountComments,
            'UF_USER_DOWNLOAD' => $item['UF_USER_DOWNLOAD'],
            'EDIT'             => false,
            'DEPARTAMENT'      => $item['UF_DEPARTAMENT'],
        ];
    }
    echo json_encode(
        [
            'nameShop' => trim($currentNameShop),
            'infoHl'   => $resPhotoDayShop,
        ]
    );
} else {
    http_response_code(404);

    return;
}
