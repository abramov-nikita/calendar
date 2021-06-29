<?

if (isset($_SERVER["HTTP_X_REQUESTED_WITH"]) && $_SERVER["HTTP_X_REQUESTED_WITH"] == "XMLHttpRequest") {
    require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_before.php");
    function preNone($var)
    {
        echo "<pre>";
        print_r($var);
        echo "</pre>";
    }

    $userId = $USER->GetId();
    $groups = CUser::GetUserGroup($userId);
    if ((in_array('18', $groups)) || (in_array('28', $groups))){
        $res = current(hl_getlist(HLBL_PHOTOSSHOWCASEPHOTOS, ['ID' => trim(strip_tags($_REQUEST['ID']))]));
        if (( ! empty($res)) && ($res['UF_CHECK'] != 1)) {
            if ((in_array(current(hl_getlist(HLBL_PHOTOSSHOWCASE, ['ID' => $res['UF_ITEM']]))['UF_SHOP'], array_keys(hl_getlist(HLBL_SHOPS, ['ID' => current($USER->GetByID($userId)->arResult)['UF_CURATOR']], 'UF_XML_ID')))) ||
             (in_array('28', $groups))){
                echo json_encode(
                    [
                        'CHECK' => 'true',
                    ]
                );
            }else{
                echo json_encode(
                    [
                        'CHECK' => 'false',
                    ]
                );
            }
        }else{
            echo json_encode(
                [
                    'CHECK' => 'false',
                ]
            );
        }
    }else{
        echo json_encode(
            [
                'CHECK' => 'false',
            ]
        );
    }
} else {
    http_response_code(404);

    return;
}
