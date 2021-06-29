<?

require($_SERVER["DOCUMENT_ROOT"]."/bitrix/header.php");
$APPLICATION->SetTitle("Фотоотчеты по витринам");

//Список отделов
$arDepartament = hl_getlist(HLBL_PHOTOSSHOWCASEDEPARTAMENTS, [], 'ID', ['UF_NAME' => 'ASC']);
$arShopsList = hl_getlist(HLBL_SHOPS, [
    '=UF_STATUS' => 1,
    '!ID'        => [
        92, 101, 117, 118, 119, 120, 121, 132, 122, 123, 137, 114, 124, 125, 129, 138, 126, 127,
        128, 101, 97, 98, 95, 93, 96, 99, 100, 94, 102, 133, 112, 113, 115, 116, 128, 130, 131,
        134, 82
    ],
], 'UF_XML_ID', ['UF_NAME' => 'ASC']);
$_monthsList = [
    "01" => "январь",
    "02" => "февраль",
    "03" => "март",
    "04" => "апрель",
    "05" => "май",
    "06" => "июнь",
    "07" => "июль",
    "08" => "август",
    "09" => "сентябрь",
    "10" => "октябрь",
    "11" => "ноябрь",
    "12" => "декабрь",
];
foreach (hl_getlist(HLBL_PHOTOSSHOWCASE, []) as $item) {
    $arDate[strtotime(ConvertDateTime($item['UF_DATE'], 'DD.MM.YYYY'))] = ConvertDateTime($item['UF_DATE'], 'MM.YYYY');
}
krsort($arDate);
foreach ($arDate as $date) {
    $arMonth[$_monthsList[current(explode('.', $date))]] = end(explode('.', $date));
}
$arFilter = [
    '=UF_STATUS' => 1,
    '!ID'        => [
        92, 101, 117, 118, 119, 120, 121, 132, 122, 123, 137, 114, 124, 125, 129, 138, 126, 127,
        128, 101, 97, 98, 95, 93, 96, 99, 100, 94, 102, 133, 112, 113, 115, 116, 128, 130, 131,
        134, 82
    ],
];
if ( ! empty($_GET["shop"])) {
    $arFilter['UF_XML_ID'] = $_GET["shop"];
}
if ( ! empty($_GET["departament"])) {
    foreach (hl_getlist(HLBL_PHOTOSSHOWCASEPHOTOS, ["UF_DEPARTAMENT" => $_GET["departament"]], 'ID') as $item) {
        $arIdDepartament[$item['UF_ITEM']] = true;
    }
    $arSortShop = [];
    foreach (hl_getlist(HLBL_PHOTOSSHOWCASE, ['ID' => array_keys($arIdDepartament)]) as $item) {
        if (empty($arFilter['UF_XML_ID'])) {
            $arSortShop[$item['UF_SHOP']] = $item['UF_SHOP'];
        } else {
            if (in_array($item['UF_SHOP'], $arFilter['UF_XML_ID'])) {
                $arSortShop[$item['UF_SHOP']] = $item['UF_SHOP'];
            }
        }
    }
    if ( ! empty($arSortShop)) {
        $arFilter['UF_XML_ID'] = array_keys($arSortShop);
    }
    unset($arSortShop);
}
$dateStart = new DateTime();
$dateStart->modify('Last Sunday');
$start = new DateTime($dateStart->format('d.m.Y'));

$dateEnd = new DateTime();
$dateEnd->modify('Next Sunday');
$end = new DateTime($dateEnd->format('d.m.Y'));
$step = new DateInterval('P1D');
$period = new DatePeriod($start, $step, $end);
foreach ($period as $datetime) {
    $arInterval[$datetime->format("N")] = $datetime->format("d.m.Y");
}

$arWeekNames = [1 => "Пн", "Вт", "Ср", "Чт", "Пт", "Сб", "Вс"];
if (( ! empty($_GET["month"])) && ( ! empty($_GET["week"]))) {
    $monthsNumList = [
        "январь"   => "01",
        "февраль"  => "02",
        "март"     => "03",
        "апрель"   => "04",
        "май"      => "05",
        "июнь"     => "06",
        "июль"     => "07",
        "август"   => "08",
        "сентябрь" => "09",
        "октябрь"  => "10",
        "ноябрь"   => "11",
        "декабрь"  => "12",
    ];
    foreach ($arShopsList as $item) {
        $arSortShop[$item['UF_XML_ID']] = $item['UF_XML_ID'];
    }
    $arSortShop = [];
    define("DayLen", 24 * 60 * 60); // Длина дня в секундах
    define("WeekLen", 7 * DayLen);  // Длина недели в секундах
    $week = $_GET['week'];
    $year = end(explode('.', $_GET["month"]));
    $StJ = gmmktime(0, 0, 0, 1, 1, $year);
    $DayStJ = gmdate("w", $StJ);
    $DayStJ = ($DayStJ == 0 ? 7 : $DayStJ);
    $StWeekJ = $StJ - ($DayStJ - 1) * DayLen;
    if (gmdate("W", $StJ) == "01") {
        $week--;
    }
    $start = $StWeekJ + $week * WeekLen - 86400;
    $end = $start + WeekLen - 1;
    $d = new DateTime();
    $d->setTimestamp($start);
    $first_day = $d->format('d.m.Y');
    $l = new DateTime();
    $l->setTimestamp($end);
    $last_day = $l->format('d.m.Y');
    $res = hl_getlist(HLBL_PHOTOSSHOWCASE, [
        '>=UF_DATE' => $first_day,
        '<=UF_DATE' => $last_day,
    ]);
    if ( ! empty($arSortShop)) {
        $arFilter['UF_XML_ID'] = array_keys($arSortShop);
    }
    unset($arSortShop);
    unset($arInterval);
    $start = new DateTime(date('d.m.Y', strtotime($first_day)));
    $end = new DateTime(date('d.m.Y', strtotime($last_day)));
    $step = new DateInterval('P1D');
    $period = new DatePeriod($start, $step, $end);
    foreach ($period as $datetime) {
        $arInterval[$datetime->format("N")] = $datetime->format("d.m.Y");
    }
}
$currentWeek = strtotime($arInterval[1]);
$arShops = hl_getlist(HLBL_SHOPS, $arFilter, 'UF_XML_ID', ['UF_NAME' => 'ASC']);
$arCity = hl_getlist(HLBL_CITIES, [], 'ID');
foreach ($arShops as $shop) {
    $sortNameShop[$shop['UF_NAME']]['ID'] = $shop['ID'];
    $sortNameShop[$shop['UF_NAME']]['UF_CITY'] = $arCity[$shop['UF_CITY']]['UF_NAME'];
    $sortNameShop[$shop['UF_NAME']]['UF_NAME'] = $shop['UF_NAME'];
}
ksort($sortNameShop);
?>
<div class="row">
    <div class="col-12">
        <div class="pb-3">
            <form method="get">
                <div class="form-row">
                    <div class="form-group col-sm-3">
                        <select name="shop[]" class="form-control" multiple
                                data-live-search="true" data-actions-box="true"
                                title="Магазины">
                            <? foreach ($arShopsList as $shop): ?>
                                <option value="<?=$shop["UF_XML_ID"]?>"<?=in_array($shop["UF_XML_ID"],
                                    $_GET["shop"]) ? " selected"
                                    : ""?>><?=$shop["UF_NAME"]?></option>
                            <? endforeach; ?>
                        </select>
                    </div>
                    <div class="form-group col-sm-3">
                        <select name="departament[]" class="form-control"
                                multiple data-live-search="true"
                                data-actions-box="true" title="Отделы">
                            <? foreach ($arDepartament as $departament): ?>
                                <option value="<?=$departament["ID"]?>"<?=in_array($departament["ID"],
                                    $_GET["departament"]) ? " selected" : ""?>><?=$departament["UF_NAME"]?></option>
                            <? endforeach; ?>
                        </select>
                    </div>
                    <div class="form-group col-sm-2">
                        <select name="month" class="form-control"
                                data-actions-box="true" title="Месяц">
                            <? foreach ($arMonth as $month => $year): ?>
                                <option <?=($month.".".$year == $_GET["month"]) ? " selected" : ""?> value="<?=$month.".".$year?>"><?=$month.".".$year;?></option>
                            <? endforeach; ?>
                        </select>
                    </div>
                    <div class="form-group col-sm-2">
                        <select name="week" class="form-control" disabled
                                data-actions-box="true" title="Неделя">

                        </select>
                    </div>
                    <div class="form-group  col-sm-2">
                        <div class="form-row">
                            <div class="col-6">
                                <button type="submit"
                                        class="btn btn-success btn-block"
                                        data-toggle="tooltip"
                                        data-placement="top" title="Применить">
                                    <i class="fas fa-check"></i>
                                </button>
                            </div>
                            <div class="col-6">
                                <a href="<?=$APPLICATION->GetCurPage(false)?>"
                                   class="btn btn-danger btn-block"
                                   data-toggle="tooltip" data-placement="top"
                                   title="Сбросить">
                                    <i class="fas fa-times"></i>
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            </form>
        </div>
    </div>
    <div class="col-12 text-right mb-3">
        <div class="btn-group">
            <button class="fc-custom2-button btn btn-info" type="button" onclick="getReport()">Отчет</button>
            <button class="fc-custom1-button btn btn-warning" data-toggle="modal" data-target="#addPhotoShowcase" type="button">Добавить фото витрины</button>
        </div>
    </div>
</div>
<div class="modal fade" id="addPhotoShowcase" tabindex="-1" role="dialog"
     aria-labelledby="exampleModalLabel"
     aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <form class="js-ajax-file"
                  action="/services/calendar/v2/ajax/add_showcase.php"
                  method="post" enctype="multipart/form-data">
                <div class="modal-header p-2">
                    <h6>
                        Добавить фото витрины
                    </h6>
                    <button type="button" class="close" data-dismiss="modal"
                            aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <div class="form-group col-sm-12">
                        <select name="UF_SHOP" class="form-control"
                                data-live-search="true" data-actions-box="true"
                                title="Магазины" required>
                            <?
                            foreach ($arShops as $shop): ?>
                                <option value="<?=$shop["UF_XML_ID"]?>"><?=$shop["UF_NAME"]?></option>
                            <?
                            endforeach; ?>
                        </select>
                    </div>
                    <div class="form-group col-sm-12 mb-2">
                        <select name="UF_DEPARTAMENT" class="form-control"
                                data-live-search="true" data-actions-box="true"
                                title="Отдел" required>
                            <?
                            foreach ($arDepartament as $departament): ?>
                                <option value="<?=$departament["ID"]?>"><?=$departament["UF_NAME"]?></option>
                            <?
                            endforeach; ?>
                        </select>
                    </div>
                    <?
                    $APPLICATION->IncludeComponent(
                        "bitrix:main.file.input",
                        "drag_n_drop",
                        [
                            "INPUT_NAME"       => "UF_PHOTO",
                            "MULTIPLE"         => "Y",
                            "MODULE_ID"        => "main",
                            "MAX_FILE_SIZE"    => "",
                            "ALLOW_UPLOAD"     => "I",
                            "ALLOW_UPLOAD_EXT" => "",
                        ],
                        false
                    ); ?>

                </div>
                <div class="modal-footer p-2">
                    <button type="button" class="btn btn-secondary"
                            data-dismiss="modal">Закрыть
                    </button>
                    <input type="submit" class="btn btn-warning"
                           value="Добавить">
                </div>
            </form>
        </div>
    </div>
</div>
<div class="modal fade" id="infoPhotoShowcase" tabindex="-1" role="dialog"
     aria-labelledby="exampleModalLabel"
     aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header p-2 mb-2">
                <h6></h6>
                <button type="button" class="close" data-dismiss="modal"
                        aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body pt-0 pb-2"></div>
            <div class="modal-footer p-2">
                <button type="button" class="btn btn-secondary"
                        data-dismiss="modal">Закрыть
                </button>
            </div>
        </div>
    </div>
</div>
<div class="modal fade" id="commentsPhotoShowcase" tabindex="-1" role="dialog"
     aria-labelledby="exampleModalLabel"
     aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header p-2">
                <h6>Комментарии</h6>
                <button type="button" class="close" data-dismiss="modal"
                        aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <div class="comment__container"></div>
            </div>
            <div class="modal-footer p-2">
                <form action="/services/calendar/v2/ajax/add_comment.php"
                      class="ajax-file-calendar-comment" method="post"
                      enctype="multipart/form-data" style="width: 100%;">
                    <div class="comment__add">
                        <textarea class="form-control mb-3" name="COMMENT" required
                                  rows="5"></textarea>
                        <input type="file" name="UF_FILE"
                               class="form-control mb-2">
                        <input type="text" hidden name="ID">
                        <div class="row">
                            <div class="col-12 col-md-6 mb-2">
                                <button type="submit"
                                        class="btn btn-warning comment__btn-add"
                                        style="width: 100%;">Оставить
                                    комментарий
                                </button>
                            </div>
                            <div class="col-12 col-md-6 mb-2">
                                <button type="button"
                                        class="btn btn-secondary float-right"
                                        style="width: 100%;"
                                        data-dismiss="modal">Закрыть
                                </button>
                            </div>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
<div class="modal fade" id="editPhotoDepartament" tabindex="-1" role="dialog"
     aria-labelledby="exampleModalLabel"
     aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header p-2">
                <h6>Изменить отдел</h6>
                <button type="button" class="close" data-dismiss="modal"
                        aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <form action="/services/calendar/v2/ajax/edit_departament.php" class="ajax" method="post" style="width: 100%;">
                <div class="modal-body">
                    <select name="UF_DEPARTAMENT" class="form-control"
                            data-live-search="true" data-actions-box="true"
                            title="Отдел" required>
                        <?
                        foreach ($arDepartament as $departament): ?>
                            <option value="<?=$departament["ID"]?>"><?=$departament["UF_NAME"]?></option>
                        <?
                        endforeach; ?>
                    </select>
                    <input type="text" name="id" hidden>
                </div>
                <div class="modal-footer p-2">
                    <div class="comment__add">
                        <div class="row">
                            <div class="col-12 col-md-6 mb-2">
                                <button type="submit"
                                        class="btn btn-warning comment__btn-add"
                                        style="width: 100%;">Сохранить
                                </button>
                            </div>
                            <div class="col-12 col-md-6 mb-2">
                                <button type="button"
                                        class="btn btn-secondary float-right"
                                        style="width: 100%;"
                                        data-dismiss="modal">Закрыть
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>
<div class="modal fade" id="reportPhotoShowcase" tabindex="-1" role="dialog"
     aria-labelledby="exampleModalLabel"
     aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header p-2">
                <h6>
                    Отчет
                </h6>
                <button type="button" class="close" data-dismiss="modal"
                        aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">

            </div>
            <div class="modal-footer p-2">
                <button type="button" class="btn btn-secondary"
                        data-dismiss="modal">Закрыть
                </button>
            </div>
        </div>
    </div>
</div>
<div class="modal fade" id="deletePhotoDepartament" tabindex="-1" role="dialog"
     aria-labelledby="exampleModalLabel"
     aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header p-2">
                <h6></h6>
                <button type="button" class="close" data-dismiss="modal"
                        aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <form action="/services/calendar/v2/ajax/delete_photo.php" class="ajax" method="post" style="width: 100%;">
                <div class="modal-body">
                    <div class="col-12 text-center"><h6>Удалить фото?</h6></div>
                    <input type="text" name="id" hidden>
                </div>
                <div class="modal-footer p-2">
                    <div class="comment__add">
                        <div class="row">
                            <div class="col-12 col-md-6 mb-2">
                                <button type="submit"
                                        class="btn btn-danger comment__btn-add"
                                        style="width: 100%;">Удалить
                                </button>
                            </div>
                            <div class="col-12 col-md-6 mb-2">
                                <button type="button"
                                        class="btn btn-secondary float-right"
                                        style="width: 100%;"
                                        data-dismiss="modal">Закрыть
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>
<div class="table-responsive">
    <table class="table" table-month="<?=date('m')?>" table-year="<?=date('Y')?>">
        <thead class="thead-warning">
        <tr>
            <? /*<th scope="col">Город</th>*/ ?>
            <th scope="col" class="shop_name">Город/Филиал</th>
            <? foreach ($arInterval as $nameId => $data): ?>
                <th scope="col" style="min-width: 65px;"
                    data-day="true"
                    data-day-num="<?=$nameId;?>">
                    <span data-reload="week" data-num-header="<?=$nameId;?>"><?=current(explode('.', $data));?></span> <span class="badge badge-secondary"><?=$arWeekNames[$nameId];?></span>
                </th>
            <? endforeach; ?>
            <th scope="col" data-week="num-week" data-day="true">Итог <span class="badge badge-secondary" data-reload="week"><?=date("W", $currentWeek);?> неделя</span></th>
            <th scope="col" style="width: 90px;">
                <div class="btn-group">
                    <button class="fc-prev-button btn btn-warning" type="button" onclick="getWeek('prev','<?=current($arInterval);?>')">
                        <span class="fa fa-chevron-left"></span>
                    </button>
                    <button class="fc-next-button btn btn-warning" type="button" onclick="getWeek('next','<?=end($arInterval);?>')">
                        <span class="fa fa-chevron-right"></span>
                    </button>
                </div>
            </th>
        </tr>
        </thead>
        <tbody>
        <? foreach ($sortNameShop as $shop): ?>
            <tr data-id="<?=$shop['ID'];?>">
                <? /*<th scope="row"><?=$shop['UF_CITY'];?></th>*/ ?>
                <td scope="row" class="shop_name"><?=$shop['UF_NAME'];?></td>
                <? foreach ($arInterval as $data): ?>
                    <td date="<?=$data;?>" data-day="true" data-reload="day">
                        <i class="fas fa-spinner fa-spin"></i>
                    </td>
                <? endforeach; ?>
                <td data-week="summ" data-day="true" data-reload="day">
                    <i class="fas fa-spinner fa-spin"></i>
                </td>
                <td style="border-top:0"></td>
            </tr>
        <? endforeach; ?>
        </tbody>
    </table>
</div>

<script>
  $(document).ready(function () {
    getCurrentWeek()

    let weekSelect = '<?=$_GET['week']?>'
    if (weekSelect != '') {
      let res = $('form [name="month"]').val()
      getWeekMonth(res, weekSelect)
    } else {
      getWeekMonth($('form [name="month"]').val())
    }

    $('form [name="month"]').change(function () {
      getWeekMonth($(this).val())
    })

    // $('.fht-table').fixedHeaderTable({fixedColumn: true });
  })

  //Получение недели
  function getCurrentWeek () {
    let prev = $('.fc-prev-button').attr('onclick'),
      next = $('.fc-next-button').attr('onclick')
    $.ajax({
      type: 'POST',
      url: '/services/calendar/v2/ajax/v2/get_week.php',
      data: {
        prev: prev,
        next: next,
        month: $('.table').attr('table-month'),
        year: $('.table').attr('table-year'),
      },
    }).done(function (ajax) {
      let answer = $.parseJSON(ajax)
      $.each($('[record="true"]'), function (index, value) {
        $(this).removeAttr('onclick')
      })
      $.each(answer.ROW, function (indexShop, arrayValues) {
        $.each(arrayValues, function (data, count) {
          if (count>=5) {
            count = `<a style="color: white"><span class="badge badge-success">${count}</span></a>`
          } else if ((count<5) && (count>0)) {
            count = `<a style="color: white"><span class="badge badge-danger">${count}</span></a>`
          } else {
            count = `<span class="badge badge-dark">0</span>`
          }
          $('[data-id="' + indexShop + '"] [date="' + data + '"]').html(count)
        })
      })
      $.each(answer.ID_RECORDS, function (indexShop, arrayValues) {
        $.each(arrayValues, function (data, id) {
          if (id>0) {
            $('[data-id="' + indexShop + '"] [date="' + data + '"]').attr('onclick', 'getDayShop(' + id + ')')
          }
        })
      })
      $.each(answer.SUMM, function (indexShop, count) {
        if (count>=5) {
          count = `<a onclick="getWeekPhotoShop('${indexShop}','${answer.DATE_START}','${answer.DATE_END}')" style="color: white"><span class="badge badge-success">${count}</span></a>`
        } else if ((count<5) && (count>0)) {
          count = `<a onclick="getWeekPhotoShop('${indexShop}','${answer.DATE_START}','${answer.DATE_END}')" style="color: white"><span class="badge badge-danger">${count}</span></a>`
        } else {
          count = `<span class="badge badge-dark">0</span>`
        }
        $('[data-id="' + indexShop + '"] [data-week="summ"]').html(count)
      })
      $('.fc-prev-button, .fc-next-button').removeAttr('disabled')
    })
  }

  //Получаем неделю вперед или назад
  function getWeek (step, dataValue) {
    //Запускаем значек загрузки
    $.each($('[data-reload="day"]'), function (index, value) {
      $(this).html(`<i class="fas fa-spinner fa-spin"></i>`)
    })
    $.each($('[data-reload="week"]'), function (index, value) {
      $(this).html(`<i class="fas fa-spinner fa-spin"></i>`)
    })
    $('.fc-prev-button, .fc-next-button').attr('disabled', 'disabled')
    $.ajax({
      type: 'POST',
      url: '/services/calendar/v2/ajax/v2/get_days_week.php',
      data: {
        day: dataValue,
        step: step,
      },
    }).done(function (ajax) {
      let answer = $.parseJSON(ajax)
      console.log(answer)
      $('[data-week="num-week"]').html(`Итог <span class="badge badge-secondary" data-reload="week">${answer.num_week} неделя</span>`)
      $('[data-num-header="1"]').text(answer.day_num[1])
      $('[data-num-header="2"]').text(answer.day_num[2])
      $('[data-num-header="3"]').text(answer.day_num[3])
      $('[data-num-header="4"]').text(answer.day_num[4])
      $('[data-num-header="5"]').text(answer.day_num[5])
      $('[data-num-header="6"]').text(answer.day_num[6])
      $('[data-num-header="7"]').text(answer.day_num[0])
      $('.table').removeAttr('table-month').removeAttr('table-year')
      $('.table').attr('table-month', answer.table_month).attr('table-year', answer.table_year)
      $.each(answer.ar_period, function (index, value) {
        $('tr [date="' + index + '"]').attr('date', value)
      })
      $('.fc-prev-button').attr('onclick', 'getWeek(\'prev\',\'' + answer.next + '\')')
      $('.fc-next-button').attr('onclick', 'getWeek(\'next\',\'' + answer.prev + '\')')
      getCurrentWeek()
    })

  }

  //Получить отчет с пн по чт
  function getReport () {
    $.ajax({
      type: 'POST',
      url: '/services/calendar/v2/ajax/report.php',
      data: {},
    }).done(function (result) {
      let htmlJson = $.parseJSON(result)
      $('#reportPhotoShowcase .modal-body').html(htmlJson)
    })
    $('#reportPhotoShowcase').modal('show')
  }

  //Получаем день магазина
  function getDayShop (id) {
    $.ajax({
      type: 'POST',
      url: '/services/calendar/v2/ajax/get_current_day_shop.php',
      data: {ID: id},
    }).done(function (result) {
      let answer = $.parseJSON(result)
      $('#infoPhotoShowcase .modal-header h6').text(answer.nameShop)
      htmlShowcaseStructure(answer, id)
      $('#infoPhotoShowcase').modal('show')

      //Получаем фансибокс и отслеживаем всю инфу после показа слайда
      $('[data-fancybox="all"]').fancybox({
        afterShow: function( instance, slide ) {
          $.ajax({
            type: 'POST',
            url: '/services/calendar/v2/ajax/get_change_and_confirmation.php',
            data: {ID: instance.$caption[0].innerHTML},
          }).done(function (result) {
            let answer = $.parseJSON(result)
            if (answer.CHECK == 'true'){
              $(slide.$content[0]).append(`<button class="btn btn-warning float-right photo_check-user" id-value="${instance.$caption[0].innerHTML}">Подтвердить</button>`)
              $('.photo_check-user').click(function () {
                $.ajax({
                  type: 'POST',
                  url: '/services/calendar/v2/ajax/set_confirmation.php',
                  data: {ID: $(this).attr('id-value')},
                }).done(function (result) {
                  let answer = $.parseJSON(result)
                  if (answer.ID){
                    console.log(answer)
                    $(`[id-value="${instance.$caption[0].innerHTML}"]`).detach()
                    // $(`.btn-departament__check[data-id="${instance.$caption[0].innerHTML}"]`).detach()
                    $(`.container-check[data-id="${instance.$caption[0].innerHTML}"]`).html(`
                        <div class="badge badge-check-success small">Подтверждено</div>
                    `)
                    $(slide.$content[0]).append(`<button class="btn btn-success float-right photo_check-user-success" id-value="${instance.$caption[0].innerHTML}">Подтверждено</button>`)
                  }
                })
              })
            }else{
              $(slide.$content[0]).append(`<button class="btn btn-success float-right photo_check-user-success" id-value="${instance.$caption[0].innerHTML}">Подтверждено</button>`)
            }
          })
        }
      });
    })
  }

  //Получаем неделю с фото в магазине
  function getWeekPhotoShop (id, startDate, endDate) {
    $.ajax({
      type: 'POST',
      url: '/services/calendar/v2/ajax/v2/get_current_week_shop.php',
      data: {
        ID: id,
        START_DATE: startDate,
        END_DATE: endDate
      },
    }).done(function (result) {
      let answer = $.parseJSON(result)
      $('#infoPhotoShowcase .modal-header h6').text(answer.nameShop)
      htmlShowcaseStructure(answer, id)
      $('#infoPhotoShowcase').modal('show')
    })
  }

  //Структура в магазине по отделам
  function htmlShowcaseStructure (answer, idShop) {
    console.log(answer);
    let html = `<div class="row">`
    if (answer.infoHl === null) {
      html += `<div class="col-12 text-center"><h6><span class="badge badge-warning">В данном магазине фото отсутствуют</span></h6></div></div>`
    }
    $.each(answer.infoHl, function (keyDepartament, arPhoto) {
      html += ``
      $.each(arPhoto, function (key, value) {
        html += `
                        <div class="col-6 col-md-4 border p-1">
                            <div class="col-12 text-center p-0" style="font-size: 10px;"><div class="btn-info rounded-0 title__departament">${keyDepartament}</div></div>`
        if (value.EDIT == true) {
          html += `     <div class="btn btn-departament__edit" data-id="${value.PHOTO_ID}" data-departament-id="${value.DEPARTAMENT}"><i class="fas fa-edit"></i></div>`
        }
        if (value.EDIT == true) {
          html += `     <div class="btn btn-departament__delete" data-id="${value.PHOTO_ID}" data-departament-id="${value.DEPARTAMENT}"><i class="far fa-trash-alt"></i></div>`
        }
        // if (value.CHECK == 'true') {
        //   html += `     <div class="container-check" data-id="${value.PHOTO_ID}"><div class="btn badge-dark btn-departament__check" data-id="${value.PHOTO_ID}" data-departament-id="${value.DEPARTAMENT}"><i class="fas fa-check"></i></div></div>`
        // }
        if (value.CHECK == 'false') {
          html += `     <div class="container-check" data-id="${value.PHOTO_ID}"><div class="badge badge-check-success small" data-id="${value.PHOTO_ID}" data-departament-id="${value.DEPARTAMENT}">Подтверждено</div></div>`
        }
        html += `<a href="${value.UF_PHOTO}" class="nav-link text-center p-0" data-fancybox="all" data-caption="${value.PHOTO_ID}">
                               <div class="showcase__photo" style="background: url('${value.UF_PHOTO_PREVIEW}') no-repeat center center;"></div>
                            </a>
                            <div class="col-12 p-0 text-center">
                                <button class="btn btn-warning rounded-0" onclick="getComments('${idShop}','${value.PHOTO_ID}','${value.UF_PHOTO}')" style="font-size: 10px;width: 100%;">
                                  Комментарии <span class="badge badge-secondary">${value.COUNT_COMMENTS}</span>
                                </button>
                            </div>
                        </div>
                        `
      })
      html += ``
    })
    html += `</div>`
    $('#infoPhotoShowcase .modal-body').html(html)

    /**
     * при клике открываем окно редактирования раздела,
     если данный авторизированный пользователь выкладывал фото
     */
    $('.btn-departament__edit').click(function () {
      $('#infoPhotoShowcase').modal('hide')
      $('#editPhotoDepartament').modal('show')
      $('#editPhotoDepartament [name="id"]').val('')
      $('#editPhotoDepartament [name="id"]').val($(this).data('id'))
      $('#editPhotoDepartament [name="UF_DEPARTAMENT"]').selectpicker('val', [$(this).data('departament-id')])

      //Если окно редактирования отдела закрыли, открываем обратно окно с фото по отделам
      $('#editPhotoDepartament').on('hidden.bs.modal', function (e) {
        $('#infoPhotoShowcase').modal('show')
      })
    })

    /**
     * при клике открываем окно удаления фото,
     если данный авторизированный пользователь выкладывал фото
     */
    $('.btn-departament__delete').click(function () {
      $('#infoPhotoShowcase').modal('hide')
      $('#deletePhotoDepartament').modal('show')
      $('#deletePhotoDepartament [name="id"]').val('')
      $('#deletePhotoDepartament [name="id"]').val($(this).data('id'))

      //Если окно редактирования отдела закрыли, открываем обратно окно с фото по отделам
      $('#deletePhotoDepartament').on('hidden.bs.modal', function (e) {
        $('#infoPhotoShowcase').modal('show')
      })
    })

    //Подтвержденеие куратором
    $('.btn-departament__check').click(function () {
      let id = $(this).attr('data-id');
      $.ajax({
        type: 'POST',
        url: '/services/calendar/v2/ajax/set_confirmation.php',
        data: {ID: id},
      }).done(function (result) {
        let answer = $.parseJSON(result)
        if (answer.ID){
          $(`.container-check[data-id="${id}"]`).html(`
                        <div class="badge badge-check-success small">Подтверждено</div>
                    `)
        }
      })
    })
  }

  //Получаем комментарии на фото при открытии idCurentDayShop = ID из ФВО: список
  function getComments (idCurrentDayShop, id, img) {
    $.ajax({
      type: 'POST',
      url: '/services/calendar/v2/ajax/get_comments.php',
      data: {ID: id, IMG: img, ID_CURRENT: idCurrentDayShop},
    }).done(function (result) {
      let answer = $.parseJSON(result)
      $('#infoPhotoShowcase').modal('hide')
      htmlCommentStructure(id, answer)
      $('#commentsPhotoShowcase').modal('show')
      $('#commentsPhotoShowcase').on('hidden.bs.modal', function (e) {
        // $.ajax({
        //   type: 'POST',
        //   url: '/services/calendar/ajax/get_current_day_shop.php',
        //   data: {ID: idCurrentDayShop},
        // }).done(function (result) {
        //   let answer = $.parseJSON(result)
        //   htmlShowcaseStructure(answer, idCurrentDayShop)
        // })
        $('#infoPhotoShowcase').modal('show')
      })
    })
  }

  //Структура комментарий в попапе
  function htmlCommentStructure (id, answer) {
    let html = `<div class="card mb-2">
             <a href="${answer.PHOTO}" class="nav-link text-center" data-fancybox="${answer.PHOTO}">
                        <img src="${answer.PHOTO}" class="card-img-top">
             </a>
          </div>`
    $.each(answer.COMMENTS, function (key, value) {
      html += `
                  <div class="card mb-1 rounded-0">
                    <div class="card-body p-1">
                      <h6 class="card-title mb-1">${value.UF_USER}</h6>
                      <p class="card-text mb-1">${value.UF_TEXT}</p>`
      if (value.UF_FILE != null) {
        html += ` <a target="_blank" href="${value.UF_FILE}" class="card-text mb-1"><i class="fas fa-file-download"></i> Прикрепленный файл</a><br> `
      }
      html += `        <small class="badge badge-warning" style="font-size: 9px;">${value.UF_DATETIME}</small>
                    </div>
                  </div>
                  `
    })
    $('.comment__add [name="ID"]').val(id)
    $('#commentsPhotoShowcase .modal-body .comment__container').html(html)
  }

  //Получаем недели выбраного месяца
  function getWeekMonth (selectDate, choose = false) {
    $('form [name="week"]').prop('disabled', true)
    $.ajax({
      type: 'POST',
      url: '/services/calendar/v2/ajax/v2/get_week_month.php',
      data: {data: selectDate},
    }).done(function (result) {
      if ($.parseJSON(result) != 'false') {
        let answer = $.parseJSON(result),
          selectWeek = ''
        $('form [name="week"]').html(selectWeek)
        $.each(answer, function (index, value) {
          selectWeek += `<option value="${value}">${value} неделя</option>`
        })
        $('form [name="week"]').html(selectWeek)
        $('form [name="week"]').prop('disabled', false)
        $('form [name="week"]').selectpicker('refresh')
        if (choose != false) {
          $('form [name="week"]').selectpicker('val', choose)
          $('form [name="week"]').selectpicker('refresh')
        }
      }
    })
  }

  $('form.ajax-file-calendar-comment').on('submit', function (e) {
    e.preventDefault()
    var _this = $(this),
      formData = new FormData($(this)[0]),
      btn = _this.find('[type="submit"]')

    btn.attr('disabled', 'disabled')
    $('.lockscreen').show()

    $.ajax({
      method: _this.attr('method'),
      url: _this.attr('action'),
      dataType: 'json',
      data: formData,
      async: false,
      cache: false,
      processData: false,
      contentType: false,
      success: function (ans) {
        if (ans.command == 'send') {
          $('#commentsPhotoShowcase [name="COMMENT"]').val('')
          $('#commentsPhotoShowcase [name="UF_FILE"]').val('')
          btn.removeAttr('disabled')
          htmlCommentStructure(ans.id, ans)
          $('.lockscreen').hide()
        } else {
          alert(ans.message)
          btn.removeAttr('disabled')
          $('.lockscreen').hide()
        }
      },
    })
    return false
  })


</script>

<style>
    .photo_check-user {
        position: absolute;
        top: 6px;
        right: 6px;
    }
    .photo_check-user-success {
        position: absolute;
        top: 6px;
        left: 6px;
    }
    .badge-check-success {
        position: absolute;
        top: 80px;
        left: 9px;
        background: #28a745;
        color: #fff;
    }

    /*
    Для фиксации лувого столбца
     */
    thead .shop_name,
    tbody .shop_name {
        position: sticky;
        left: 0px;
        z-index: 2;
        background: white;
    }

    @media (max-width: 470px) {
        thead .shop_name,
        tbody .shop_name {
            font-size: 12px;
        }
    }

    /*
    END
     */

    [data-day = "true"] {
        text-align: center;
        border: solid 1px #7f6003 !important;
        font-size: 18px;
    }

    .table .thead-warning th {
        color: #7f6003;
        background-color: #ffc107;
        border-color: #ffc107;
    }

    .table .thead-warning th:last-child {
        color: #fff;
        background-color: #fff;
        border-color: #fff;
    }

    .table thead th {
        vertical-align: baseline;
        border-bottom: 2px solid #dee2e6;
    }

    .table a {
        transition: .5s;
        display: block;
        width: 100%;
    }

    a:hover .badge-success,
    a:focus .badge-success,
    a:active .badge-success {
        transition: .5s;
        cursor: pointer;
        opacity: .5;
    }

    a:hover .badge-danger,
    a:focus .badge-danger,
    a:active .badge-danger {
        transition: .5s;
        cursor: pointer;
        opacity: .5;
    }


    .btn-departament__edit {
        position: absolute;
        background: #ffc107;
        padding: 5px;
        font-size: 8px;
        right: 32px;
        z-index: 9;
        margin-top: 5px;
        transition: .5s;
    }

    .btn-departament__edit:hover {
        background: #28a745;
        color: #fff;
        transition: .5s;
        cursor: pointer;
    }

    .btn-departament__delete {
        position: absolute;
        background: #dc3545;
        padding: 5px;
        font-size: 8px;
        right: 7px;
        z-index: 9;
        margin-top: 5px;
        transition: .5s;
        color: #fff;
    }

    .title__departament {
        height: 30px;
        overflow: hidden;
        display: flex;
        flex-direction: column;
        justify-content: center;
    }

    .btn-departament__delete:hover {
        background: #ff364c;
        color: #fff;
        transition: .5s;
        cursor: pointer;
    }

    .btn-departament__check {
        position: absolute;
        /*background: #28a745;*/
        padding: 5px;
        font-size: 8px;
        right: 7px;
        z-index: 9;
        margin-top: 5px;
        transition: .5s;
        color: #fff;
    }
    .btn-departament__check:hover {
        background: #218838;
        color: #fff;
        transition: .5s;
        cursor: pointer;
    }

    .comment__add {
        width: 100%;
        display: block;
    }

    #calendar .btn-primary {
        background: #ffc107;
        border-color: #ffc107;
        color: rgba(0, 0, 0, .5);
    }

    #calendar .btn-primary:hover,
    #calendar .btn-primary:focus,
    #calendar .btn-primary:active {
        background: #b88d06;
        border-color: #b88d06;
        color: #ffffff;
        box-shadow: 0 0 0 0.2rem #b88d066e;
    }

    .photo__count {
        color: #fff;
    }

    .fc-daygrid-day-bottom {
        text-align: center;
        width: 100%;
        display: block;
    }

    .fc-list-event-title:hover {
        cursor: pointer;
    }

    .fc-daygrid-day-bottom .fc-daygrid-more-link {
        font-size: 1rem;
        width: 35.88px;
        overflow: hidden;
        display: block;
        margin: 0 auto;
    }

    .showcase__photo {
        transition: .5s;
        height: 70px;
        background-size: cover !important;
    }

    .showcase__photo:hover {
        cursor: pointer;
        opacity: .5;
        transition: .5s;
    }

    .modal {
        overflow: auto;
    }

    .file-selectdialog {
        background: none repeat scroll 0 0 #f8f9fa;
    }

    .fc-daygrid-more-link {
        padding: 0px 5px;
        background: #ffc107;
    }

    .shopNoImages {
        background: red;
        color: #fff;
        padding: 0px 5px;
        font-size: 1rem;
        width: 35.88px;
        display: block;
        margin: 0 auto;
        overflow: hidden;
    }

    .shopYesImages {
        background: #28a745;
        color: #fff;
        padding: 0px 5px;
        font-size: 1rem;
        width: 35.88px;
        display: block;
        margin: 0 auto;
        overflow: hidden;
    }

    .fc-list-day-cushion {
        background: #ffc107 !important;
        color: #8b6003 !important;
    }

    #calendar .fc-custom2-button {
        background: #17a2b8 !important;
        color: #ffffff !important;
        border-color: #17a2b8 !important;
    }

    #calendar .fc-custom2-button:hover,
    #calendar .fc-custom2-button:focus,
    #calendar .fc-custom2-button:active {
        background: #17a2b8;
        border-color: #17a2b8;
        color: #ffffff;
        box-shadow: 0 0 0 0.2rem #17a2b88a;
    }

    @media (max-width: 768px) {
        .fc-toolbar-chunk {
            width: 100% !important;
            display: block;
            min-width: 100%;
            float: left;
            position: inherit;
            text-align: center;
        }

        .fc-header-toolbar.fc-toolbar.fc-toolbar-ltr {
            display: block;
            width: 100%;
        }

        .fc-daygrid-more-link {
            font-size: 11px;
            padding: 0px 5px;
            background: #ffc107;
        }
    }
</style>
<?
require($_SERVER["DOCUMENT_ROOT"]."/bitrix/footer.php"); ?>
