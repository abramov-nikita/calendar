<?

if (isset($_SERVER["HTTP_X_REQUESTED_WITH"]) && $_SERVER["HTTP_X_REQUESTED_WITH"] == "XMLHttpRequest") {
    require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_before.php");

    $date = explode('-',date('Y-m-d'));
    $date = trim(strip_tags($date[2].".".$date[1].".".$date[0]));
    $shop = trim(strip_tags($_REQUEST['UF_SHOP']));
    $departament = trim(strip_tags($_REQUEST['UF_DEPARTAMENT']));

    //Проверяем нету ли уже записи на указанный день
    if (empty($resId = hl_getlist(HLBL_PHOTOSSHOWCASE, array('UF_SHOP'=>$shop, 'UF_DATE'=>$date)))){
        //Создаем запись по магазину
        $resId = hl_add(HLBL_PHOTOSSHOWCASE, array(
            "UF_DATE" => $date,
            "UF_SHOP" => $shop
        ));
    }
    if (is_array($resId)){
        $resId = current($resId)['ID'];
    }
    foreach ($_REQUEST['UF_PHOTO'] as $photo) {
        hl_add(HLBL_PHOTOSSHOWCASEPHOTOS, array(
            "UF_ITEM" => $resId,
            "UF_DEPARTAMENT" => $departament,
//            "UF_PHOTO" => CFile::MakeFileArray($photo),
            "UF_PHOTO" => CFile::MakeFileArray($_SERVER["DOCUMENT_ROOT"].CFile::ResizeImageGet($photo, array('width' => 1000, 'height' => 1000), BX_RESIZE_IMAGE_PROPORTIONAL, true)['src']),
            "UF_USER_DOWNLOAD" => $USER->getId()
        ));
        CFile::Delete($photo);
    }

    echo json_encode(
                array(
                    'command' => 'reload',
                )
            );
} else {
    http_response_code(404);

    return;
}
