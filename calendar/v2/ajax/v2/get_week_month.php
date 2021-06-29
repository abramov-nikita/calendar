<?

if (isset($_SERVER["HTTP_X_REQUESTED_WITH"]) && $_SERVER["HTTP_X_REQUESTED_WITH"] == "XMLHttpRequest") {
    require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_before.php");

    if (!empty($_REQUEST['data'])){
    $date = trim(strip_tags($_REQUEST['data']));
    $monthsNumList = [
        "январь" => "01",
        "февраль" => "02",
        "март" => "03",
        "апрель" => "04",
        "май" => "05",
        "июнь" => "06",
        "июль" => "07",
        "август" => "08",
        "сентябрь" => "09",
        "октябрь" => "10",
        "ноябрь" => "11",
        "декабрь" => "12"
    ];
    $month = $monthsNumList[trim(strip_tags(current(explode('.',$date))))];
    $year = trim(strip_tags(end(explode('.',$date))));
    $beg = (int) date('W', strtotime("first thursday of $year-$month"));
    $end = (int) date('W', strtotime("last  thursday of $year-$month"));
        echo json_encode(range($beg, $end));
    }else{
        echo json_encode('false');
    }
} else {
    http_response_code(404);

    return;
}
