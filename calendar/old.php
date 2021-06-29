<?

require($_SERVER["DOCUMENT_ROOT"]."/bitrix/header.php");
$APPLICATION->SetTitle("Фотоотчеты по витринам");

//Календарь скрипты и стили
$APPLICATION->AddHeadScript("/services/calendar/fullcalendar/lib/main.js");
$APPLICATION->SetAdditionalCSS("/services/calendar/fullcalendar/lib/main.css",
    true);

//Список магазинов
$arShops = hl_getlist(HLBL_SHOPS, ['UF_STATUS' => 1], 'UF_XML_ID', ['UF_NAME' => 'ASC']);

//Список отделов
$arDepartament = hl_getlist(HLBL_PHOTOSSHOWCASEDEPARTAMENTS, [], 'ID', ['UF_NAME' => 'ASC']);

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

?>

<div class="row">
    <div class="col-12">
        <div class="pb-3">
            <form method="get">
                <div class="form-row">
                    <div class="form-group col-sm-5">
                        <select name="shop[]" class="form-control" multiple
                                data-live-search="true" data-actions-box="true"
                                title="Магазины">
                            <?
                            foreach ($arShops as $shop): ?>
                                <option value="<?=$shop["UF_XML_ID"]?>"<?=in_array($shop["UF_XML_ID"],
                                    $_GET["shop"]) ? " selected"
                                    : ""?>><?=$shop["UF_NAME"]?></option>
                            <?
                            endforeach; ?>
                        </select>
                    </div>
                    <div class="form-group col-sm-5">
                        <select name="departament[]" class="form-control"
                                multiple data-live-search="true"
                                data-actions-box="true" title="Отделы">
                            <?
                            foreach ($arDepartament as $departament): ?>
                                <option value="<?=$departament["ID"]?>"<?=in_array($departament["ID"],
                                    $_GET["departament"]) ? " selected"
                                    : ""?>><?=$departament["UF_NAME"]?></option>
                            <?
                            endforeach; ?>
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
        <div class="calendar_container">
            <div id='calendar'></div>
        </div>
    </div>
</div>
<div class="modal fade" id="addPhotoShowcase" tabindex="-1" role="dialog"
     aria-labelledby="exampleModalLabel"
     aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <form class="js-ajax-file"
                  action="/services/calendar/ajax/add_showcase.php"
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
                <form action="/services/calendar/ajax/add_comment.php"
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
            <form action="/services/calendar/ajax/edit_departament.php" class="ajax" method="post" style="width: 100%;">
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
            <form action="/services/calendar/ajax/delete_photo.php" class="ajax" method="post" style="width: 100%;">
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

<script>
  $(document).ready(function () {
    renderingCalendar(<?=json_encode($arFilter);?>)
  })

  function renderingCalendar (filter = false) {
    $('.lockscreen').show()
    $.ajax({
      type: 'POST',
      url: '/services/calendar/ajax/get_calendar.php',
      data: {
        filter: filter,
      },
    }).done(function (json) {
      let answer = $.parseJSON(json)
      var calendarEl = document.getElementById('calendar')
      var calendar = new FullCalendar.Calendar(calendarEl, {
        initialView: 'dayGridMonth',
        locale: 'ru',
        showNonCurrentDates: false,
        events: answer.arJsonShops,
        navLinks: true,
        themeSystem: 'bootstrap',
        headerToolbar: {
          left: 'dayGridMonth,listDay prev,next',
          center: 'title',
          right: 'custom2,custom1',
        },
        buttonText: {
          month: 'Месяц',
          day: 'День',
        },
        customButtons: {
          custom1: {
            text: 'Добавить фото витрины',
            click: function () {
              $('#file-selectdialogswitcher-mfiPHOTO_INPUT').click()
              $('#addPhotoShowcase').modal('show')
            },
          },
          custom2: {
            text: 'Отчет',
            click: function () {
              $.ajax({
                type: 'POST',
                url: '/services/calendar/ajax/report.php',
                data: {},
              }).done(function (result) {
                let htmlJson = $.parseJSON(result)
                $('#reportPhotoShowcase .modal-body').html(htmlJson)
                // console.log(answer)
              })
              $('#reportPhotoShowcase').modal('show')
            },
          },
        },
        eventColor: '#ffc107',
        eventTextColor: 'rgba(0,0,0,.5)',
        handleWindowResize: true,
        dayMaxEventRows: 0,
        moreLinkClick: 'day',
        moreLinkText: '',
        refetchResourcesOnNavigate: true,
        allDayText: 'Магазин',
        eventClick: function (info) {
          $.ajax({
            type: 'POST',
            url: '/services/calendar/ajax/get_current_day_shop.php',
            data: {ID: info.event.id},
          }).done(function (result) {
            console.log(info.event.id)
            let answer = $.parseJSON(result)
            $('#infoPhotoShowcase .modal-header h6').text(info.event.title)
            htmlShowcaseStructure(answer, info.event.id)
            $('#infoPhotoShowcase').modal('show')
          })
        },
      })
      calendar.render()
      $.each(answer.arSmallShopPhoto, function (index, value) {
        $(`[data-date="${index}"] .fc-daygrid-day-bottom`).prepend(`<div style="width: 100%;"><span class="shopNoImages">${value}</span><div>`)
      })
      $.each(answer.arManyShopPhoto, function (index, value) {
        $(`[data-date="${index}"] .fc-daygrid-day-bottom`).prepend(`<div style="width: 100%;"><span class="shopYesImages">${value}</span><div>`)
      })
      $('.lockscreen').hide()

      $('.fc-prev-button, .fc-next-button, .fc-listDay-button, .fc-dayGridMonth-button').click(function () {
        $.each(answer.arSmallShopPhoto, function (index, value) {
          $(`[data-date="${index}"] .fc-daygrid-day-bottom`).prepend(`<div style="width: 100%;"><span class="shopNoImages">${value}</span><div>`)
        })
        $.each(answer.arManyShopPhoto, function (index, value) {
          $(`[data-date="${index}"] .fc-daygrid-day-bottom`).prepend(`<div style="width: 100%;"><span class="shopYesImages">${value}</span><div>`)
        })
      })
    })
  }

  //Структура в магазине по отделам
  function htmlShowcaseStructure (answer, idShop) {
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
        html += `<a href="${value.UF_PHOTO}" class="nav-link text-center p-0" data-fancybox="${keyDepartament}">
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
  }

  //Получаем комментарии на фото при открытии idCurentDayShop = ID из ФВО: список
  function getComments (idCurrentDayShop, id, img) {
    $.ajax({
      type: 'POST',
      url: '/services/calendar/ajax/get_comments.php',
      data: {ID: id, IMG: img, ID_CURRENT: idCurrentDayShop},
    }).done(function (result) {
      let answer = $.parseJSON(result)
      $('#infoPhotoShowcase').modal('hide')
      htmlCommentStructure(id, answer)
      $('#commentsPhotoShowcase').modal('show')
      $('#commentsPhotoShowcase').on('hidden.bs.modal', function (e) {
        $.ajax({
          type: 'POST',
          url: '/services/calendar/ajax/get_current_day_shop.php',
          data: {ID: idCurrentDayShop},
        }).done(function (result) {
          let answer = $.parseJSON(result)
          htmlShowcaseStructure(answer, idCurrentDayShop)
        })
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
