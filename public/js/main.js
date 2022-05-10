$(function () {
    $("body form").myform({});

    $("#users_list").DataTable({
        dom: "liptip",
        paging: true,
        ordering: true,
        stateSave: true,
        "processing": false,
        "serverSide": true,
        pageLength: 20,
        lengthMenu: [10, 20, 50, 75, 100],
        "info": true,
        "order": [[ 1, 'asc' ]],
        "ajax": {
            "url": "/user/getUsersList",
            "type": "POST",
        },
        "columns": [
            {
                "data": "id",
                "sortable": false,
                "class": "id",
            },

            {
                "data": "name",
                "sortable": true,
                "class": "name",
            },
            {
                "data": "email",
                "sortable": true,
                "class": "email",
            },
        ],  "rowCallback": function (row, data) {
            $(".id", row).html($("<a>").attr("href", "/user/"+data["id"]).html(data["id"]));

        },
    });





});


function onCaptchaLoad() {
    var widgetID = hcaptcha.render('hCaptcha', { sitekey: "979f2067-afbb-489f-9b26-22ab45cf0f3e" });
    $("#register_form").myform({
        "onErrorForm" : function (aData, oForm){
            //Сбрасываем каптчу, если валидация формы прошла с ошибками
            if (aData["error"] || (aData["error_fields"] && Object.keys(aData["error_fields"]).length)) {
                $(oForm.modal).detach();
                $(oForm).myform('processForm', aData);
                hcaptcha.reset(widgetID)
            }
        }
    })
}