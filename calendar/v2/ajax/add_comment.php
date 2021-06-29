<?

if (isset($_SERVER["HTTP_X_REQUESTED_WITH"]) && $_SERVER["HTTP_X_REQUESTED_WITH"] == "XMLHttpRequest") {
    require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_before.php");
    $currentUser = $USER->getId();//Текущий комментатор
    if ($res = hl_add(
        HLBL_PHOTOSSHOWCASECOMMENTS,
        [
            'UF_TEXT'     => trim(strip_tags($_REQUEST['COMMENT'])),
            'UF_ITEM'     => trim(strip_tags($_REQUEST['ID'])),//ID в фото списке
            'UF_USER'     => $currentUser,
            'UF_DATETIME' => date('d.m.Y H:i:s'),
            'UF_FILE'     => $_FILES['UF_FILE'],
        ]
    )
    ) {
        $arComments = hl_getlist(HLBL_PHOTOSSHOWCASECOMMENTS, ['UF_ITEM' => trim(strip_tags($_REQUEST['ID']))], 'ID', ['UF_DATETIME' => 'ASC']);
        $arUsers = user_getlist([], 'ID');
        $i = 0;
        foreach ($arComments as $key => $value) {
            $newArComments[$i]['UF_TEXT'] = $value['UF_TEXT'];
            $newArComments[$i]['UF_FILE'] = CFile::GetFileArray($value['UF_FILE'])['SRC'];
            $newArComments[$i]['UF_USER'] = $arUsers[$value['UF_USER']]['LAST_NAME']." ".$arUsers[$value['UF_USER']]['NAME']." ".$arUsers[$value['UF_USER']]['SECOND_NAME'];
            $newArComments[$i]['UF_DATETIME'] = ConvertDateTime($value['UF_DATETIME'], 'DD.MM.YYYY HH:MI:SS');
            $i++;
        }
        $currentUserDownload = current(hl_getlist(HLBL_PHOTOSSHOWCASEPHOTOS, ['ID' => trim(strip_tags($_REQUEST['ID']))]));
        $currentShowcase = current(hl_getlist(HLBL_PHOTOSSHOWCASE, ['ID' => $currentUserDownload['UF_ITEM']]));
        $currentShop = current(hl_getlist(HLBL_SHOPS, ['UF_XML_ID' => $currentShowcase['UF_SHOP']]))['UF_NAME'];
        $arUsers = user_getlist([], 'ID');
        $arAllDeals = CRest::call(
            'im.notify',
            [
                'to'      => $arUsers[$currentUserDownload['UF_USER_DOWNLOAD']]['XML_ID'],
                'message' => 'Под Вашим фото, в Фотоотчетах по витринам, пользователь '.$arUsers[$currentUser]['LAST_NAME'].' '.$arUsers[$currentUser]['NAME'].' '.$arUsers[$currentUser]['SECOND_NAME'].' оставил комментарий[br]Дата витрины: '.ConvertDateTime($currentShowcase['UF_DATE'], 'DD.MM.YYYY').'[br]Магазин: '.$currentShop,
            ]
        );

        foreach ($arComments as $item) {
            $arSentUser[] = $item['UF_USER'];
        }
        foreach (array_unique($arSentUser) as $user) {
            if (($currentUserDownload['UF_USER_DOWNLOAD'] != $user)&&($user != $USER->getId())) {
                $arAllDeals = CRest::call(
                    'im.notify',
                    [
                        'to'      => $arUsers[$user]['XML_ID'],
                        'message' => 'Под фото, в Фотоотчетах по витринам, пользователь '.$arUsers[$currentUser]['LAST_NAME'].' '.$arUsers[$currentUser]['NAME'].' '.$arUsers[$currentUser]['SECOND_NAME'].' оставил комментарий[br]Дата витрины: '.ConvertDateTime($currentShowcase['UF_DATE'], 'DD.MM.YYYY').'[br]Магазин: '.$currentShop,
                    ]
                );
            }
        }


        echo json_encode(
            [
                'id'       => trim(strip_tags($_REQUEST['ID'])),
                'command'  => 'send',
                'PHOTO'    => CFile::GetFileArray($currentUserDownload['UF_PHOTO'])['SRC'],
                'COMMENTS' => $newArComments,
            ]
        );
    }
} else {
    http_response_code(404);

    return;
}
