<?

if (isset($_SERVER["HTTP_X_REQUESTED_WITH"]) && $_SERVER["HTTP_X_REQUESTED_WITH"] == "XMLHttpRequest") {
    require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_before.php");
    function preNone($var)
    {
        echo "<pre>";
        print_r($var);
        echo "</pre>";
    }
    $currentUser = $USER->getId();
   if (!empty($res = current(hl_getlist(HLBL_PHOTOSSHOWCASEPHOTOS, ['ID' => trim(strip_tags($_REQUEST['ID']))])))){
       hl_update(HLBL_PHOTOSSHOWCASEPHOTOS, $res['ID'], ['UF_CHECK' => 1]);
       hl_add(HLBL_PHOTOSSHOWCASECOMMENTS, [
           'UF_ITEM' => trim(strip_tags($_REQUEST['ID'])),
           'UF_USER' => $currentUser,
           'UF_TEXT' => 'Проверено, подтверждено',
           'UF_DATETIME' => date('d.m.Y H:i:s'),
       ]);
       $currentShop = current(hl_getlist(HLBL_SHOPS, ['UF_XML_ID' => current(hl_getlist(HLBL_PHOTOSSHOWCASE, ['ID' => $res['UF_ITEM']]))['UF_SHOP']]))['UF_NAME'];
       $currentShowcase = current(hl_getlist(HLBL_PHOTOSSHOWCASE, ['ID' => $res['UF_ITEM']]));
       //Идем в Б24 и выводим уведомление
       $arUsers = user_getlist([], 'ID');
       $arAllDeals = CRest::call(
           'im.notify',
           [
               'to'      => $arUsers[$res['UF_USER_DOWNLOAD']]['XML_ID'],
               'message' => 'Под Вашим фото, в Фотоотчетах по витринам, пользователь '.$arUsers[$currentUser]['LAST_NAME'].' '.$arUsers[$currentUser]['NAME'].' '.$arUsers[$currentUser]['SECOND_NAME'].' оставил комментарий[br]Дата витрины: '.ConvertDateTime($currentShowcase['UF_DATE'], 'DD.MM.YYYY HH:MI:SS').'[br]Магазин: '.$currentShop,
           ]
       );


       echo json_encode(
           [
               'ID' => trim(strip_tags($_REQUEST['ID']))
           ]
       );
   }

} else {
    http_response_code(404);

    return;
}
