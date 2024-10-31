if (typeof $ === 'undefined')
    $ = jQuery;

$(document).ready(function () {

    $(document).on('click', '.payment-completed-button', function () {
        $('#paymendo-bank-transfer-modal').paymendo_bank_transfer_modal({
            fadeDuration: 250,
            escapeClose: true,
            //clickClose: false,
            showClose: true,
            modalClass: "paymendo-bank-transfer-modal"
        });
    })

    $(document).on('submit', '#paymendo-bank-transfer-modal', function (e) {
        e.preventDefault();
        var ajax_url = paymendo_bank_transfer_site.ajaxurl + "?action=paymendo_bank_transfer";

        var form = $(this);

        var submitButton = $(this).find('button')[0];
        var originalSubmitButtonHTML = submitButton.innerHTML;
        submitButton.disabled = true;
        submitButton.setAttribute('style', 'opacity: 1 !important');
        submitButton.innerHTML = '<img src="' + paymendo_bank_transfer_site.loading_gif + '" width="30">';

        $.ajax({
            method: 'POST',
            url: ajax_url,
            data: form.serialize(),
            success: function (msg) {
                submitButton.disabled = false;
                submitButton.innerHTML = originalSubmitButtonHTML;
                if (msg !== 'success') {
                    alert(paymendo_bank_transfer_site.error_msg);
                    return;
                }
                $.paymendo_bank_transfer_modal.close();
                $('.payment-information').load(location.href + " .payment-information");
            },
            error: function () {
                alert(paymendo_bank_transfer_site.error_msg);
                submitButton.disabled = false;
                submitButton.innerHTML = originalSubmitButtonHTML;
            }
        });

    })

});
