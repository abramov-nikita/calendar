<?

if (isset($_SERVER["HTTP_X_REQUESTED_WITH"]) && $_SERVER["HTTP_X_REQUESTED_WITH"] == "XMLHttpRequest") {
    require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_before.php");

    $arShops = hl_getlist(HLBL_SHOPS, [
        '=UF_STATUS' => 1,
        '!ID' => [
            92, 101, 117, 118, 119, 120, 121, 132, 122, 123, 137, 114, 124, 125, 129, 138, 126, 127,
            128, 101, 97, 98, 95, 93, 96, 99, 100, 94, 102, 133, 112, 113, 115, 116, 128, 130, 131,
            134, 82
        ]
    ], 'UF_XML_ID', ['UF_NAME' => 'ASC']);
    $lastDayReceivedWeek = trim(explode('\'',trim(strip_tags($_REQUEST['prev'])))[3]);
    $endDayReceivedWeek = trim(explode('\'',trim(strip_tags($_REQUEST['next'])))[3]);
    $start = new DateTime($lastDayReceivedWeek);
    $end = new DateTime(date('d.m.Y', strtotime($endDayReceivedWeek) + 86400));
    $step = new DateInterval('P1D');
    $period = new DatePeriod($start, $step, $end);
    foreach($period as $datetime) {
        $arInterval[$datetime->format("d.m.Y")] = 0;
    }
    $arFilter = [
        ">=UF_DATE" => $lastDayReceivedWeek,
        "<=UF_DATE" => $endDayReceivedWeek,
    ];
    foreach ($arShops as $item){
        $arRecords['ROW'][$item['ID']] = $arInterval;
        $arRecords['SUMM'][$item['ID']] = array(0);
        $arRecords['ID_RECORDS'][$item['ID']] = $arInterval;
    }
    $showCase = hl_getlist(HLBL_PHOTOSSHOWCASE, $arFilter);
    foreach ($showCase as $item) {
        $arRecords['ROW'][$arShops[$item['UF_SHOP']]['ID']][ConvertDateTime($item['UF_DATE'], 'DD.MM.YYYY')] = $item['ID'];
        $arPhoto[$item['ID']] = $item['ID'];
        $arRecords['SUMM'][$arShops[$item['UF_SHOP']]['ID']][] = $item['ID'];
        $arRecords['ID_RECORDS'][$arShops[$item['UF_SHOP']]['ID']][ConvertDateTime($item['UF_DATE'], 'DD.MM.YYYY')] = $item['ID'];
    }
    foreach (hl_getlist(HLBL_PHOTOSSHOWCASEPHOTOS, ['UF_ITEM' => $arPhoto]) as $item) {
        $arHlPhotos[$item['UF_ITEM']]++;
    }
    foreach ($arRecords['SUMM'] as $idShop => $arrayValue){
        foreach ($arrayValue as $value){
            $resAllCount[$idShop] += $arHlPhotos[$value];
        }
    }
    $arRecords['SUMM'] = $resAllCount;
    foreach ($arRecords['ROW'] as $idShop => $array) {
        foreach ($array as $data => $idRow) {
            $arRecords['ROW'][$idShop][$data] = $arHlPhotos[$idRow];
        }
    }
    $arRecords['DATE_START'] = $lastDayReceivedWeek;
    $arRecords['DATE_END'] = $endDayReceivedWeek;

    echo json_encode($arRecords);
} else {
    http_response_code(404);

    return;
}
