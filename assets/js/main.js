if (typeof $ === "undefined")
    $ = jQuery

var paymendo_bank_transfer_sure_text = paymendo_bank_transfer_lang.sure_text;

/* Datatable */
var paymendo_bank_transfer_filters = {
    banks_filter: null,
    status_filter: null,
    initial_amount: null,
    last_amount: null,
    currency_filter: null,
    initial_date: null,
    last_date: null
};
var pbt_listContractsTableColumnsOrderKeys = [
    'order_id',
    'customer_name',
    'bank_name',
    'amount',
    'created',
    'updated'
];
var pbt_listContractsTableColumns = [
    {
        data: 'order_number', render: function (data, type, row) {
            return "<a class='text-dark' target='_blank' href='post.php?post=" + data + "&action=edit'>#" + data + "</a>"
        }
    },
    {
        sortable: false,
        data: null,
        render: function (data, type, row) {
            return '<a class="text-dark" href="edit.php?post_type=shop_order&_customer_user=' + data.customer_id + '" target="_blank">' + data.customer_name + '</a>'
        }
    },
    {
        data: null, render: function (data, type, row) {
            let out = ""
            out += '<div class="text-center">' +
                (data.bank_slug ? '<span class="bank-logo d-block"><img src="' + paymendo_bank_transfer_bank_list[data.bank_slug].logo + '" /></span>' : "") +
                (data.bank_slug ? '<span class="d-block">' + paymendo_bank_transfer_bank_list[data.bank_slug].bank_name + ' <span class="badge bg-warning p-1">(' + data.currency + ')</span></span>' : "") +
                '</div>'
            return out;
        }
    },
    {data: 'order_formatted_total'},
    {data: 'created'},
    {data: 'updated'},
    {
        sortable: false,
        data: null,
        render: function (data, type, row) {
            let content = '<div class="d-inline-flex"><div class="paymendo-bank-transfer-confirm-payment-area to-update-after-confirm-payment-' + data.id + '">';
            if (data.payment_status == 0) {
                content += '<a class="paymendo-bank-transfer-complete-payment"' +
                    '                   data-value="' + data.id + '">' +
                    '                    <button' +
                    '                        class="btn btn-sm btn-dark mr-2">' + paymendo_bank_transfer_lang.confirm_payment +
                    '                    </button>' +
                    '                </a>';
            } else {
                content += '<a class="paymendo-bank-transfer-completed-button"' +
                    '                   data-value="' + data.id + '">\n' +
                    '                    <button class="btn btn-sm btn-primary mr-2"' +
                    '                            data-value="' + data.id + '"' +
                    '                            title="' + paymendo_bank_transfer_lang.cancel_payment + '">' +
                    '                        <div class="icon-okay"><i class="fa fa-check icon-okay"></i> <span>' + paymendo_bank_transfer_lang.confirmed + '</span></div>' +
                    '                        <div class="icon-cancel"><i class="fa fa-times"></i> <span>' + paymendo_bank_transfer_lang.set_unconfirmed + '</span></div>' +
                    '                    </button>\n' +
                    '                </a>';
            }

            content += '</div>&nbsp;';
            content += '<a href="#" class="btn btn-sm btn-outline-danger paymendo-bank-transfer-delete-payment" data-value="""' + data.order_id + '">' +
                paymendo_bank_transfer_lang.delete_payment + '</a>';
            content += '</div>';
            return content;
        }
    }
];

function pbt_loadTable() {
    if (typeof jQuery().DataTable !== "function")
        return;

    $('#data-table').DataTable({
        "dom": '<"toolbar">frt<"table-footer"ipl<"total-amount">>',
        "processing": true,
        "language": paymendo_bank_transfer_lang.datatable,
        "lengthMenu": [[10, 25, 50, 100/*, -1*/], [10, 25, 50, 100 /*"All"*/]],
        "serverSide": true,
        "order": [4, "desc"],
        "columns": pbt_listContractsTableColumns,
        "ajax": {
            "url": ajaxurl + "?action=paymendo_bank_transfer_payments_data",
            "dataType": "json",
            "contentType": "application/json; charset=utf-8",
            "type": "GET",
            "data": function (d) {
                var customFilters = Object.fromEntries(Object.entries(paymendo_bank_transfer_filters).filter(([, v]) => v !== null));
                d = Object.assign(d, customFilters)
                d.q = d.search.value;
                d.orderBy = pbt_listContractsTableColumnsOrderKeys[d.order[0].column];
                d.orderDir = d.order[0].dir;
                delete d.columns;
                delete d.search;
                delete d.order;
                return d;
            },
            "complete": function (data) {
                data = typeof data.responseJSON === "object" ? data.responseJSON : {};

                var sum_output = "<strong>"
                sum_output += data.sum + " TL"
                sum_output += "</strong>"

                if (data.sum != null)
                    jQuery(".total-amount").html(paymendo_bank_transfer_lang.total_amount.replaceAll("%s", sum_output))

            }
        }
    });
}

function pbt_redraw_table() {
    $('#data-table').DataTable().draw('page');
}

var pbt_filter_input_apply = function (item) {
    if (item.name === "date_range")
        return;
    paymendo_bank_transfer_filters[item.name] = typeof jQuery(item).val() === "object" && jQuery(item).val() !== null ? jQuery(item).val().join(",") : jQuery(item).val()
}
/**/

$(document).ready(function () {

    if (typeof moment === "function") {
        moment.locale('tr');
    }

    /* BANKS PAGE */
    function pbt_append_new_row() {
        var cloneRow = $('#default-bank-card');
        var newRow = cloneRow.clone();
        newRow.hide();
        newRow.attr('id', '');

        if (newRow !== null) {
            newRow.find('input,textarea,select').each(function (index, item) {
                item.removeAttribute('disabled')
            })
        }

        //var totalRows = $('#bank-accounts-body tr').length;
        //newRow[0].innerHTML = newRow[0].innerHTML.replace('{$num}', totalRows);

        if ($('#banks-list .card-collapser').length)
            $('#banks-list .card-collapser').last().after(newRow);
        else
            $('#banks-list>div').last().before(newRow);

        newRow.fadeIn('slow');
    }

    $('#new-row').on('mouseenter', function (e) {
        let btn = jQuery(this).find('.btn-outline-primary')
        btn.addClass('btn-primary')
        btn.removeClass('btn-outline-primary')
    })
    $('#new-row').on('mouseleave', function (e) {
        let btn = jQuery(this).find('.btn-primary')
        btn.addClass('btn-outline-primary')
        btn.removeClass('btn-primary')
    })

    function pbt_re_sort_rows() {
        $('.card-collapser').each(function (value) {
            $(this).find('.bank-name-select').attr('name', 'paymendo_bank_transfer[' + value + '][bank_slug]');
            $(this).find('.account-name-text').attr('name', 'paymendo_bank_transfer[' + value + '][account_owner]');
            $(this).find('.branch-code-text').attr('name', 'paymendo_bank_transfer[' + value + '][branch_code]');
            $(this).find('.account-number-text').attr('name', 'paymendo_bank_transfer[' + value + '][account_number]');
            $(this).find('.iban-text').attr('name', 'paymendo_bank_transfer[' + value + '][iban]');
            $(this).find('.swift-text').attr('name', 'paymendo_bank_transfer[' + value + '][swift]');
            $(this).find('.currency-select').attr('name', 'paymendo_bank_transfer[' + value + '][currency]');
            $(this).find('.note-textarea').attr('name', 'paymendo_bank_transfer[' + value + '][note]');
            $(this).find('.id-text').attr('name', 'paymendo_bank_transfer[' + value + '][id]');
        });
    }

    $('#date-filter').daterangepicker({
        timePicker: true,
        locale: {
            format: 'LLL'
        },
        timePicker24Hour: true,
        ranges: {
            [paymendo_bank_transfer_lang.date_range.today] : [moment().startOf('day'), moment()],
            [paymendo_bank_transfer_lang.date_range.yesterday]: [moment().subtract(1, 'days').startOf('day'), moment().subtract(1, 'days').endOf('day')],
            [paymendo_bank_transfer_lang.date_range.last_a_week]: [moment().subtract(1, 'week').startOf('day'), moment()],
            [paymendo_bank_transfer_lang.date_range.last_a_month]: [moment().subtract(1, 'month').startOf('day'), moment()],
            [paymendo_bank_transfer_lang.date_range.this_month]: [moment().startOf('month'), moment()],
            [paymendo_bank_transfer_lang.date_range.last_month]: [moment().subtract(1, 'month').startOf('month'), moment().subtract(1, 'month').endOf('month')],
            [paymendo_bank_transfer_lang.date_range.this_year]: [moment().startOf('year'), moment()],
            [paymendo_bank_transfer_lang.date_range.last_year]: [moment().subtract(1, 'year').startOf('year'), moment().subtract(1, 'year').endOf('year')]
        }
    }).on('apply.daterangepicker', function (ev, picker) {
        paymendo_bank_transfer_filters["initial_date"] = picker.startDate.format('YYYY-MM-DD HH:mm') + ':00';
        paymendo_bank_transfer_filters["last_date"] = picker.endDate.format('YYYY-MM-DD HH:mm') + ':59';
    })

    jQuery(".filter-toggle").on('click', function () {
        this.classList.remove("d-flex")
        this.classList.add("d-none")
        jQuery("#notification-filter-form").slideDown(300)
    })

    jQuery(".filter-close").on('click', function () {
        document.querySelector(".filter-toggle").classList.remove("d-none")
        document.querySelector(".filter-toggle").classList.add("d-flex")
        jQuery("#notification-filter-form").slideUp(300)
    })


    // input slider
    let min_range = $("#price-filter").data('min')
    let max_range = $("#price-filter").data('max')

    var $priceRange = $("#price-filter").ionRangeSlider({
        skin: "sharp",
        type: "double",
        min: parseFloat(min_range),
        max: parseFloat(max_range),
        from: parseFloat(min_range),
        to: parseFloat(max_range),
        grid: true,
        grid_snap: true,
        from_fixed: false,  // fix position of FROM handle
        to_fixed: false     // fix position of TO handle
    });

    $priceRange.on('change', function () {
        paymendo_bank_transfer_filters["initial_amount"] = jQuery(this).data('from')
        paymendo_bank_transfer_filters["last_amount"] = jQuery(this).data('to')
    })

    pbt_re_sort_rows()

    $('#new-row').on('click', function (ev) {
        ev.preventDefault();
        pbt_append_new_row();
        pbt_re_sort_rows();
    })


    $(document).on('click', '.delete-account', function (ev) {
        ev.preventDefault();

        let dataId = $(this).data('value');

        if (typeof dataId !== 'undefined') {
            if (confirm(paymendo_bank_transfer_sure_text)) {
                $(this).closest('.card-collapser').remove();
                pbt_re_sort_rows();
                $.ajax({
                    method: 'POST',
                    data: 'id=' + dataId,
                    url: ajaxurl + '?action=paymendo_bank_transfer_bank_delete',
                    success: function (msg) {
                        if (msg !== 'success') {
                            alert(paymendo_bank_transfer_lang.error_msg);
                            return;
                        }
                    }
                });
            }
        } else {
            $(this).closest('.card-collapser').remove();
            pbt_re_sort_rows();
        }
    });

    jQuery(document).on('click', '.single-bank-account-card .more-button', function () {
        let card = this.closest('.card');
        let opened = (typeof card.dataset.opened === "boolean" && card.dataset.opened) || (typeof card.dataset.opened === "string" && card.dataset.opened === "true");
        let more_settings = jQuery(card).find(".more-settings");
        if (!opened) {
            more_settings.slideDown(300)
            this.innerText = this.dataset.lesstext;
            this.classList.remove('btn-outline-primary')
            this.classList.add('btn-primary')
        } else {
            more_settings.slideUp(300)
            this.innerText = this.dataset.moretext;
            this.classList.remove('btn-primary')
            this.classList.add('btn-outline-primary')
        }
        card.dataset.opened = !opened;
    })

    /* */

    /* PAYMENTS PAGE */

    $(document).on('click', '.paymendo-bank-transfer-complete-payment', function (ev) {
        ev.preventDefault();
        if (confirm(paymendo_bank_transfer_sure_text)) {
            let id = $(this).data('value');
            let confirmButton = $(this)[0];
            confirmButton.innerHTML = '<img src="' + paymendo_bank_transfer_lang.loading_gif + '">';
            if (typeof id !== 'undefined') {
                $.ajax({
                    method: 'POST',
                    data: 'id=' + id,
                    url: 'admin-ajax.php?action=paymendo_bank_transfer_payments',
                    success: function (res) {
                        if (res !== 'success') {
                            alert(paymendo_bank_transfer_lang.error_msg);
                            return;
                        }
                        pbt_redraw_table();
                    }
                });
            }
        }
    });

    $(document).on('click', '.paymendo-bank-transfer-completed-button', function (ev) {
        ev.preventDefault();
        if (confirm(paymendo_bank_transfer_sure_text)) {
            let id = $(this).data('value');
            let confirmButton = $(this)[0];
            confirmButton.innerHTML = '<img src="' + paymendo_bank_transfer_lang.loading_gif + '">';
            if (typeof id !== 'undefined') {
                $.ajax({
                    method: 'POST',
                    data: 'id=' + id,
                    url: 'admin-ajax.php?action=paymendo_bank_transfer_cancel_payment',
                    success: function (res) {
                        if (res !== 'success') {
                            alert(paymendo_bank_transfer_lang.error_msg);
                            return;
                        }
                        pbt_redraw_table();
                    }
                });
            }
        }
    });

    $(document).on('click', '.paymendo-bank-transfer-delete-payment', function (ev) {
        ev.preventDefault();

        let dataId = $(this).data('value');

        if (typeof dataId !== 'undefined') {
            if (confirm(paymendo_bank_transfer_sure_text)) {
                document.getElementById('order_id_for_payments_deleted').value = dataId;
                $.ajax({
                    method: 'POST',
                    data: 'order_id=' + dataId,
                    url: ajaxurl + '?action=paymendo_bank_transfer_delete_payment',
                    success: function (msg) {
                        if (msg !== 'success') {
                            alert(paymendo_bank_transfer_lang.error_msg);
                            return;
                        }
                        pbt_redraw_table();
                        if (paymendo_bank_transfer_extra.sms_enabled) {
                            $('.pbt-delete-payment-modal').paymendo_bank_transfer_modal({
                                fadeDuration: 250,
                                escapeClose: true,
                                //clickClose: false,
                                showClose: true,
                                modalClass: "paymendo-bank-transfer-modal"
                            });
                        }
                    }
                });
            }
        }
    });

    $(document).on('submit', '.pbt-delete-payment-modal', function (e) {
        e.preventDefault();

        var form = $(this);

        var submitButton = $(this).find('button')[0];
        var originalSubmitButtonHTML = submitButton.innerHTML;
        submitButton.disabled = true;
        submitButton.setAttribute('style', 'opacity: 1 !important');
        submitButton.innerHTML = '<img src="' + paymendo_bank_transfer_lang.loading_gif + '" width="30">';

        $.ajax({
            method: 'POST',
            url: ajaxurl + "?action=paymendo_bank_transfer_sms_for_deleted_payment",
            data: form.serialize(),
            success: function (msg) {
                submitButton.disabled = false;
                submitButton.innerHTML = originalSubmitButtonHTML;

                console.log(msg);
                if (msg !== 'success') {
                    alert(paymendo_bank_transfer_lang.error_msg);
                    return;
                }
                $.paymendo_bank_transfer_modal.close();
            },
            error: function () {
                alert(paymendo_bank_transfer_lang.error_msg);
                submitButton.disabled = false;
                submitButton.innerHTML = originalSubmitButtonHTML;
            }
        });
    });

    /* Datatable */
    pbt_loadTable();
    var filter_inputs_selector = '#notification-filter-form input, #notification-filter-form select'

    document.querySelectorAll(filter_inputs_selector).forEach(function (item) {
        pbt_filter_input_apply(item)
        if (item.nodeName.toLowerCase() === "select") {

            jQuery(item).on('change', function () {
                pbt_filter_input_apply(this)
            });
            let options = {
                allowClear: typeof item.attributes.multiple !== "undefined",
                width: "100%",
            }

            if (typeof item.dataset.placeholder === "string")
                options.placeholder = item.dataset.placeholder

            jQuery(item).select2(options)

        }
        item.addEventListener("change", function () {
            pbt_filter_input_apply(this);
        })
    })
    /**/
    /**/

    /* SETTINGS PAGE */
    if (document.getElementById('enable_sms_for_admin')) {
        document.getElementById('enable_sms_for_admin').addEventListener('change', function () {
            document.getElementById('sms-message-template-setting-for-admin').style.display = this.checked ? "" : "none"
        })

        document.getElementById('enable_sms_for_customer').addEventListener('change', function () {
            document.getElementById('sms-message-template-setting-for-customer').style.display = this.checked ? "" : "none"
        })
    }
    /**/
});

function paymendo_bank_transfer_insert2input(txt) {
    var input = document.getElementById('sms_message_for_admin');
    input.value += txt;
}

function paymendo_bank_transfer_insert2customer_input(txt) {
    var input = document.getElementById('sms_message_for_customer');
    input.value += txt;
}

