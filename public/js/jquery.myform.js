(function ($) {
    jQuery.fn.myform = function (options) {
        //Настройки по умолчанию
        var defaults = {
            noValidateByBrowser : true
        };
        var methods = {
            init: function () {

                return this.each(function () {

                    this.params = $.extend({}, this.params, $.extend({}, defaults, options));

                    if (this.has_init !== true) {

                        var oForm = this;

                        $(oForm).off("submit.myform");
                        $(oForm).on("submit.myform", {form: oForm}, checkForm);

                        if(this.params.noValidateByBrowser){
                            $(oForm).attr("novalidate", true);
                        }

                        function checkForm(event) {

                            event.preventDefault();
                            var modal = $("<div class='myform_modal-backdrop'><div class='message'>Отправка данных...</div></div>");

                            var _this = oForm;
                            _this.modal = modal;
                            resetInputErrors(oForm);
                            var sData = $(oForm).serialize();

                            var oFormDOM = $(oForm)[0];
                            var oFormData = new FormData(oFormDOM);

                            var bBeforeSend = true;

                            if ($.isFunction(_this.params.onBeforeSend)) {
                                bBeforeSend = _this.params.onBeforeSend(sData, oForm);
                            }

                            $("[required]", oForm).each(function (iKey, $oElement) {
                                if($($oElement).is("input, textarea")){
                                    if("" === $.trim($($oElement).val())){
                                       addInputError($oElement, "Заполните обязательное поле!");
                                       bBeforeSend = false;
                                    }
                                }
                            });


                            if (bBeforeSend) {
                                oFormData.append("ajax_type", "form");
                                oFormData.append("rand", new Date().getTime());

                                oForm.submitVal = $(":submit", oForm).data("submit_val", $(":submit", oForm).val());
                                $(":submit", oForm).val("Отправка...").attr("disabled", "disabled");

                                $.ajax({
                                    url: $(oForm).attr("action"),
                                    type: "POST",
                                    contentType: false, // важно - убираем форматирование данных по умолчанию
                                    processData: false, // важно - убираем преобразование строк по умолчанию
                                    data: oFormData,
                                    success: function (data) {
                                        if ($.isFunction(_this.params.postSuccess)) {
                                            _this.params.postSuccess(data, _this);
                                        } else if (data) {

                                            if (data["error"] || (data["error_fields"] && Object.keys(data["error_fields"]).length)) {
                                                $(modal).detach();
                                                $(oForm).myform("processForm", data);
                                                if ($.isFunction(_this.params.onErrorForm)){
                                                    _this.params.onErrorForm(data, _this);
                                                }
                                            } else if ($.isFunction(_this.params.onCheckForm)) {

                                                $(":submit",oForm).val($(":submit",oForm).data("submit_val")).removeAttr("disabled");
                                                $(modal).detach();
                                                _this.params.onCheckForm(data, _this);
                                            } else if (data["errors_backtrace"] == undefined) {

                                                if (data["redirect_url"] && data["redirect_url"].length) {
                                                    $(modal).appendTo("body");

                                                    if (data["redirect_url"] == location.href) {
                                                        window.location.reload(true);
                                                    } else {
                                                        window.location = data["redirect_url"];
                                                    }
                                                } else {
                                                    $(modal).appendTo("body");
                                                    window.location.reload(true);
                                                }
                                            }
                                        } else {
                                            $(modal).detach();
                                            $("<div/>").addClass("common_form_mess").html("Запрос выполнен, но сервер не вернул никаких данных :(...").prependTo(oForm);
                                        }
                                    },
                                    error: function (jqXHR, textStatus, errorThrown) {
                                        if (jqXHR.status in this.statusCode)
                                            return;

                                        $(modal).detach();
                                        $("div.common_form_mess", oForm).remove();
                                        if ($.isFunction(_this.params.onError)) {
                                            _this.params.onError(jqXHR, textStatus, errorThrown, _this);
                                        } else {
                                            var iRandId = new Date().getTime();
                                            var CollapseBlock = $("<div class=\"accordion\" id=\"accordion_" + iRandId + "\">\n\
																	<div class=\"accordion-group\">\n\
																		<div class=\"accordion-heading\">\n\
																			<a class=\"accordion-toggle\" data-toggle=\"collapse\" data-parent=\"#accordion_" + iRandId + "\" href=\"#collapse_" + iRandId + "\">\n\
																				Ответ сервера:</a>\n\
																		</div>\n\
																		<div id=\"collapse_" + iRandId + "\" class=\"accordion-body collapse\">\n\
																			<div class=\"accordion-inner\">\n\
																			 " + jqXHR.responseText + "\n\
																			</div>\n\
																		</div>\n\
																	</div>\n\
																</div>\n\
																	");
                                            $("<div/>").addClass("common_form_mess").html("Ошибка выполнения запроса на строне сервера. Обратитесь к системному администратору." + $(CollapseBlock).html()).prependTo(oForm);
                                        }

                                    },
                                    dataType: "json",
                                });
                            }
                            return false;
                        }

                        this.has_init = true;

                    } else {
                        console.log("myform already init");
                    }
                });
            },
            processForm: function (data) {
                var oForm = this;
                if (data["error"] || Object.keys(data["error_fields"]).length) {

                    if (typeof data["common_message"] == "object") {
                        var aCommonMessage = [];
                        $.each(data["common_message"], function (iKey, $sMessage) {
                            aCommonMessage[iKey] = $sMessage;
                        });
                        data["common_message"] = aCommonMessage.join("<br>");
                    }

                    if (Object.keys(data["error_fields"]).length) {

                        $.each(data["error_fields"], function (name, mess) {
                            var $Input = $("[name='" + name + "']", oForm);

                            if ($Input.length) {
                                addInputError($Input, mess);
                            } else {
                                var regexp = /^(.+)\[(\d)\]$/;
                                var aMath = [];
                                if (aMath = name.match(regexp)) {
                                    $Input = $($("[name='" + aMath[1] + "[]']",oForm)[aMath[2]]);
                                }

                                if (!aMath || !$Input.length) {
                                    var sMess = "Обнаружена ошибка для поля, которое не удалось найти: '" + name + "' : '" + mess + "'";

                                    if (data["common_message"].length) {
                                        sMess = "<br/>" + sMess;
                                    }

                                    data["common_message"] = data["common_message"] + sMess;

                                } else {
                                    addInputError($Input, mess);
                                }
                            }
                        });
                    }

                    $(":submit", oForm).val($(":submit", oForm).data("submit_val")).removeAttr("disabled");

                }
            },
            resetInputErrors: function () {
                this.each(function () {
                    var oForm = this;
                    $(".input_error", oForm).unwrap().removeClass("input_error");
                    $("div.input_error_mess, div.common_form_mess", oForm).remove();
                    var sSubmitVal = $(":submit", oForm).data("submit_val");
                    sSubmitVal = (sSubmitVal !== undefined && sSubmitVal.length) ? $(":submit", oForm).data("submit_val") : $(":submit", oForm).val();
                    $(":submit", oForm).val(sSubmitVal).removeAttr("disabled");
                });
            },
        };


        var method = options;
        // логика вызова метода
        if (methods[method]) {
            return methods[method].apply(this, Array.prototype.slice.call(arguments, 1));
        } else if (typeof method === "object" || !method) {
            return methods.init.apply(this, arguments);
        } else {
            $.error("Метод с именем " + method + " не существует для jQuery.myform");
        }

    };
})(jQuery);


function addInputError(oInput, sMessage) {

    if ($(oInput).hasClass("selectpicker")) {
        oInput = $(oInput).next(".bootstrap-select");
    }

    if ($(oInput).hasClass("select2-hidden-accessible")) {
        oInput = $(oInput).next("span.select2-container");
    }


    if (oInput === undefined || !$(oInput).is(":visible"))
        return false;



    var inpDiv = $(oInput).parent("div.inpErrDiv");

    if (!$(inpDiv).length) {
        var iOuterWidth = $(oInput).outerWidth();
        var iWidth = $(oInput).width();
        inpDiv = $("<div/>").css("width", iOuterWidth + "px").addClass("inpErrDiv");
        $(oInput).wrap(inpDiv);
    }

    $(oInput).addClass("input_error");

    var oMessageDiv = $(oInput).next("div.input_error_mess").detach();
    if (!$(oMessageDiv).length) {
        oMessageDiv = $("<div/>");
    }

    $("input, select, textarea", inpDiv.parent()).css("vertical-align", "top");

    $(oMessageDiv).insertAfter(oInput);
    $(oMessageDiv).html(sMessage).addClass("input_error_mess");
}

function resetInputErrors(oForm) {
    $(".input_error", oForm).unwrap().removeClass("input_error");
    $("div.input_error_mess, div.common_form_mess", oForm).remove();
}