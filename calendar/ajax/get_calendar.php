<?

if (isset($_SERVER["HTTP_X_REQUESTED_WITH"]) && $_SERVER["HTTP_X_REQUESTED_WITH"] == "XMLHttpRequest") {
    require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_before.php");

    //Список магазинов
    $arShops = hl_getlist(HLBL_SHOPS, ['UF_STATUS' => 1, '=UF_TYPE_SHOP' => ''], 'UF_XML_ID', ['UF_NAME' => 'ASC']);

    //Список отделов
    $arDepartament = hl_getlist(HLBL_PHOTOSSHOWCASEDEPARTAMENTS, [], 'ID', ['UF_NAME' => 'ASC']);

    $reqFilter = $_REQUEST['filter'];
    if ( ! empty($reqFilter)) {
        $arFilter = $reqFilter;
    } else {
        $arFilter = [
            '<=UF_DATE' => date('t.m.Y'),
        ];
        if ( ! empty($_GET["shop"])) {
            $arFilter['UF_SHOP'] = $_GET["shop"];
        }
        if ( ! empty($_GET["departament"])) {
            foreach (hl_getlist(HLBL_PHOTOSSHOWCASEPHOTOS, ["UF_DEPARTAMENT" => $_GET["departament"]], 'ID') as $item) {
                $arIdDepartament[$item['UF_ITEM']] = true;
            }
            $arFilter['ID'] = array_keys($arIdDepartament);
        }
    }


    //фото витрины текущего месяца
    $arCurrentMonthPhotoShowcases = hl_getlist(HLBL_PHOTOSSHOWCASE, $arFilter);
    foreach (hl_getlist(HLBL_PHOTOSSHOWCASEPHOTOS, [], 'ID') as $item) {
        $arAllShopsPhoto[$item['UF_ITEM']]++;
    }
    foreach ($arCurrentMonthPhotoShowcases as $item) {
        if ($arAllShopsPhoto[$item['ID']] > 5) {
            $colorBackground = '#28a745';
        } elseif ($arAllShopsPhoto[$item['ID']] == 0) {
            $colorBackground = '#585755';
        } elseif (($arAllShopsPhoto[$item['ID']] > 0) && ($arAllShopsPhoto[$item['ID']] <= 5)) {
            $colorBackground = 'red';
        } else {
            $colorBackground = '#585755';
        }
        if (empty($arAllShopsPhoto[$item['ID']])) {
            $count = 0;
        } else {
            $count = $arAllShopsPhoto[$item['ID']];
        }
        $arJsonShops[] = [
            'title'           => $arShops[$item['UF_SHOP']]['UF_NAME'],
            'count'           => $count,
            'colorBackground' => $colorBackground,
            'start'           => ConvertDateTime($item['UF_DATE'], 'YYYY-MM-DD'),
            'id'              => $item['ID'],
        ];
        unset($colorBackground);
        $arID[] = $item['ID'];
        $arFoundShop[ConvertDateTime($item['UF_DATE'], 'YYYY-MM-DD')][] = $item['UF_SHOP'];//Какие есть магазины по датам
    }
    foreach (hl_getlist(HLBL_PHOTOSSHOWCASEPHOTOS, ['UF_ITEM' => $arID], 'ID') as $item) {
        $arShopsPhoto[$item['UF_ITEM']]++;
    }
    unset($arID);

    foreach ($arFoundShop as $data => $arShop) {
        $checkShops = $arShops;
        foreach ($arShop as $item) {
            if (in_array($item, array_keys($checkShops))) {
                unset($checkShops[$item]);
            }
        }
        $arJsonShopsNoFound[$data] = $checkShops;
        unset($checkShops);
    }

    foreach ($arJsonShopsNoFound as $data => $arShop) {
        foreach ($arShop as $xml => $value) {
            $arJsonShops[] = [
                'title'           => $arShops[$xml]['UF_NAME'],
                'count'           => 0,
                'colorBackground' => '#585755',
                'start'           => $data,
                'id'              => $xml,
            ];
        }
    }
    unset($arJsonShopsNoFound);

    foreach ($arJsonShops as $shop) {
        if ($arShopsPhoto[$shop['id']] < 5) {
            $arSmallShopPhoto[$shop['start']]++;
        }
        if ($arShopsPhoto[$shop['id']] > 5) {
            $arManyShopPhoto[$shop['start']]++;
        }
        if ($arShopsPhoto[$shop['id']] == 5) {
            $arSmallShopPhoto[$shop['start']]++;
        }
    }

    echo json_encode(
        [
            'arJsonShops'      => $arJsonShops,
            'arSmallShopPhoto' => $arSmallShopPhoto,
            'arManyShopPhoto'  => $arManyShopPhoto,
        ]
    );
} else {
    http_response_code(404);

    return;
}
