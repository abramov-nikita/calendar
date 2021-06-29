<?

if (isset($_SERVER["HTTP_X_REQUESTED_WITH"]) && $_SERVER["HTTP_X_REQUESTED_WITH"] == "XMLHttpRequest") {
    require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_before.php");

    $day = trim(strip_tags($_REQUEST['day']));
    $stepRequest = trim(strip_tags($_REQUEST['step']));
    $step = new DateInterval('P1D');
    if ($stepRequest == 'next') {
        $start = new DateTime(date('d.m.Y', strtotime($day) + 86400));
        $end = new DateTime(date('d.m.Y', strtotime($day) + 691200));
        $period = new DatePeriod($start, $step, $end);
        foreach ($period as $datetime) {
            $arInterval[] = $datetime->format("d.m.Y");
        }
        $start = new DateTime(date('d.m.Y', strtotime($day) - 518400));
        $end = new DateTime(date('d.m.Y', strtotime($day) + 86400));
        $period = new DatePeriod($start, $step, $end);
        foreach ($period as $datetime) {
            $arIntervalOld[] = $datetime->format("d.m.Y");
        }
    }
    if ($stepRequest == 'prev') {
        $start = new DateTime(date('d.m.Y', strtotime($day) - 604800));
        $end = new DateTime($day);
        $period = new DatePeriod($start, $step, $end);
        foreach ($period as $datetime) {
            $arInterval[] = $datetime->format("d.m.Y");
        }

        $start = new DateTime(date('d.m.Y', strtotime($day)));
        $end = new DateTime(date('d.m.Y', strtotime($day) + 691200));
        $period = new DatePeriod($start, $step, $end);
        foreach ($period as $datetime) {
            $arIntervalOld[] = $datetime->format("d.m.Y");
        }
    }

    $arReplacementsPeriod[$arIntervalOld[0]] = $arInterval[0];
    $arReplacementsPeriod[$arIntervalOld[1]] = $arInterval[1];
    $arReplacementsPeriod[$arIntervalOld[2]] = $arInterval[2];
    $arReplacementsPeriod[$arIntervalOld[3]] = $arInterval[3];
    $arReplacementsPeriod[$arIntervalOld[4]] = $arInterval[4];
    $arReplacementsPeriod[$arIntervalOld[5]] = $arInterval[5];
    $arReplacementsPeriod[$arIntervalOld[6]] = $arInterval[6];
    $ar = explode('.', current($arReplacementsPeriod));
    $month = $ar[1];
    $year = $ar[2];
    //$week = date("W", strtotime(current($arReplacementsPeriod)));
    $week = date("W", strtotime($arReplacementsPeriod[$arIntervalOld[1]]));
    foreach ($arReplacementsPeriod as $data) {
        $arDayNum[] = current(explode('.', $data));
    }
    echo json_encode([
        'table_month' => $month,
        'table_year'  => $year,
        'day_num'     => $arDayNum,
        'num_week'    => $week,
        'ar_period'   => $arReplacementsPeriod,
        'next' => current($arReplacementsPeriod),
        'prev' => end($arReplacementsPeriod),
        'ssss' => date("W", strtotime($arReplacementsPeriod[$arIntervalOld[1]]))
    ]);
} else {
    http_response_code(404);

    return;
}
