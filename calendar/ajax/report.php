<?

// set document_root if not exist
if ( ! $_SERVER["DOCUMENT_ROOT"]) {
	$_SERVER["DOCUMENT_ROOT"] = "/home/avrora/web/portal.avrora24.ru/public_html";
}
require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_before.php");
$CUser = new CUser;

//<editor-fold desc="Список магазинов">
$arShops = hl_getlist(HLBL_SHOPS, ['UF_STATUS' => 1, '=UF_TYPE_SHOP' => ''], 'UF_XML_ID', ['UF_NAME' => 'ASC']);
//</editor-fold>
//<editor-fold desc="Получаем витрины с ПН по ПТ текущей недели">
//$date = new \DateTime();
//$date->modify('last Monday');
$lastDay = date("d.m.Y",strtotime('Sunday last week'));
$endDay = date("d.m.Y", strtotime($lastDay.'+ 7 days'));
$arFilter = [
	'>=UF_DATE' => $lastDay,
	'<=UF_DATE' => $endDay,
];

$arCurrentMonthPhotoShowcases = hl_getlist(HLBL_PHOTOSSHOWCASE, $arFilter);
foreach ($arCurrentMonthPhotoShowcases as $showcase) {
	$arId[] = $showcase["ID"];
}
$arCurrentPhotoShowcases = hl_getlist(HLBL_PHOTOSSHOWCASEPHOTOS, ['UF_ITEM' => $arId]);
foreach ($arCurrentPhotoShowcases as $item) {
	$arAllShopsPhoto[$item['UF_ITEM']]++;
}
unset($arId, $arCurrentPhotoShowcases);
foreach ($arCurrentMonthPhotoShowcases as $showcase) {
	$arResShops[ConvertDateTime($showcase["UF_DATE"], 'YYYY-MM-DD')][$showcase["UF_SHOP"]] = $arAllShopsPhoto[$showcase["ID"]];
}
unset($arAllShopsPhoto);
foreach ($arResShops as $date => $shops) {
	$arShopsCheck = $arShops;
	foreach ($shops as $xmlShop => $value) {
		unset($arShopsCheck[$xmlShop]);
	}
	foreach (array_keys($arShopsCheck) as $xmlShop) {
		$arResShops[$date][$xmlShop] = 0;
	}
	unset($arShopsCheck);
}
//</editor-fold>

//<editor-fold desc="Для отчета за период">
foreach ($arResShops as $date => $shops) {
	foreach ($shops as $xmlShop => $value) {
		$arReports[$xmlShop] = 0;
	}
	foreach ($shops as $xmlShop => $value) {
		$arReports[$xmlShop] += $value;
	}
}
foreach ($arResShops as $date => $shops) {
	foreach ($shops as $xmlShop => $value) {
		$arReports[$xmlShop] += $value;
	}
}
foreach ($arReports as $keyXml => $value){
	if ($value >= 6) {
		unset($arReports[$keyXml]);
	}
}
//</editor-fold>

$str = "<table class=\"table table-hover table-dark\">
                    <thead>
                    <tr class=\"text-center\">
                    <th scope=\"col\" style='font-size: 11px;padding: 5px'>Период с ".$lastDay." по ".date("d.m.Y", strtotime($endDay.'- 1 days'))." </th>
                    </tr>
                    </thead>
                    <tbody>";
foreach ($arReports as $shop => $value) {
		$str .= "<tr class=\"bg-danger\">
                        <th scope=\"row\" style='font-size: 11px;padding: 5px'>".$arShops[$shop]['UF_NAME']."</th>
                    </tr>";
}
$str .= "</tbody>
                </table>";
echo json_encode($str);
?>
