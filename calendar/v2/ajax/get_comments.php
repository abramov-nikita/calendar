<?

if (isset($_SERVER["HTTP_X_REQUESTED_WITH"]) && $_SERVER["HTTP_X_REQUESTED_WITH"] == "XMLHttpRequest") {
    require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_before.php");
    $arComments = hl_getlist(HLBL_PHOTOSSHOWCASECOMMENTS, array('UF_ITEM' => trim(strip_tags($_REQUEST['ID']))), 'ID', array('UF_DATETIME'=>'ASC'));
    $arUsers = user_getlist(array(),'ID');
    $i = 0;
    foreach ($arComments as $key => $value){
        $newArComments[$i]['UF_TEXT'] = $value['UF_TEXT'];
        $newArComments[$i]['UF_USER'] = $arUsers[$value['UF_USER']]['LAST_NAME']." ".$arUsers[$value['UF_USER']]['NAME']." ".$arUsers[$value['UF_USER']]['SECOND_NAME'];
        $newArComments[$i]['UF_DATETIME'] = ConvertDateTime($value['UF_DATETIME'], 'DD.MM.YYYY HH:MI:SS');
        $newArComments[$i]['UF_FILE'] = CFile::GetFileArray($value['UF_FILE'])['SRC'];
        $i++;
    }
    echo json_encode(
        array(
            'PHOTO' => trim(strip_tags($_REQUEST['IMG'])),
            'COMMENTS' => $newArComments,
        )
    );
} else {
    http_response_code(404);

    return;
}
