jQuery(document).ready(function() {

    jQuery(document).on('keydown', '#paymee_document', function (e) {
        var digit = e.key.replace(/\D/g, '');
        var value = jQuery(this).val().replace(/\D/g, '');
        var size = value.concat(digit).length;
        jQuery(this).mask((size <= 11) ? '000.000.000-00' : '00.000.000/0000-00');
    });

    jQuery('#paymee_branch').mask('0000', {
        onComplete: function(branch) {
            jQuery('#paymee_account').focus();
        }
    });
    jQuery('#paymee_account').mask("ZZZZZZZZZZZ0-A", {
        reverse:true,
        translation: {
            'Z': {
                pattern: /[0-9]/, optional: true
            }
        }
    });

    //blur cpf on boleto installments
    jQuery(document).on('keydown', '#paymee_document_boleto', function (e) {
        var digit = e.key.replace(/\D/g, '');
        var value = jQuery(this).val().replace(/\D/g, '');
        var size = value.concat(digit).length;
        jQuery(this).mask((size <= 11) ? '000.000.000-00' : '00.000.000/0000-00');
    });

    jQuery(document).on('blur', '#paymee_document_boleto', function (e) {
        var cpf = jQuery(this).val();
        console.log(cpf);
        new Ajax.Request('/paymee/checkout/loans', {
            parameters: {cpf: cpf},
            onSuccess: function(transport) {
                try {
                    var response;
                    if (transport.responseText.isJSON()) {
                        response = transport.responseText.evalJSON()
                    } else {
                        response = transport.responseText
                    }

                    if (response.success == true) {
                        var proposals = response.proposals;
                        jQuery.each(proposals, function( key, value ) {
                            jQuery('#paymee_boleto_installments').append('<option value="'+value['label']+'">'+value['label']+' de R$'+value['final_amount']+'</option>');
                            jQuery('#proposal_id').val(response['proposal_id']);
                            console.log(value['label']);
                        });
                    } else {
                        alert(response.message);
                    }
                    console.log(response);
                } catch(e) {
                    alert(e.message);
                }
            }
        });
    });
});