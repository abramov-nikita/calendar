<?

if (isset($_SERVER["HTTP_X_REQUESTED_WITH"]) && $_SERVER["HTTP_X_REQUESTED_WITH"] == "XMLHttpRequest") {
    require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_before.php");


    $data['UF_DEPARTAMENT'] = trim(strip_tags($_REQUEST['UF_DEPARTAMENT']));
    $id = trim(strip_tags($_REQUEST['id']));

    $item = current(hl_getlist(HLBL_PHOTOSSHOWCASEPHOTOS, ['ID' => $id]));

    if ( ! empty($item)) {
        if (($USER->getId() == $item['UF_USER_DOWNLOAD']) || ($USER->isAdmin())) {
            hl_update(HLBL_PHOTOSSHOWCASEPHOTOS, $id, $data);
            echo json_encode(
                [
                    'command' => 'reload',
                ]
            );
        } else {
            echo json_encode(
                [
                    'command' => 'error',
                    'message' => 'Вы не выкладывали данное фото!'
                ]
            );
        }
    } else {
        echo json_encode(
            [
                'command' => 'error',
                'message' => 'Такого элемента не существует!'
            ]
        );
    }
} else {
    http_response_code(404);

    return;
}
