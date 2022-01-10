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
});