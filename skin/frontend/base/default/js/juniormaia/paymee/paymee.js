jQuery(document).ready(function() {
    var paymeeMethod    = document.getElementById("paymee-method").value;
    console.log(paymeeMethod);

    if (paymeeMethod == 'PIX') {
        console.log('chamou js success paymee pix');
        var uuid            = document.getElementById("uuid").value;
        var url             = document.getElementById("paymee-url").value;
        var customerUrl     = document.getElementById("customer-url").value;

        setInterval(function(){
            jQuery.ajax({
                url: url,
                type: "GET",
                data: "uuid="+uuid,
                success: function (data) {
                    if (data == 'PAID') {
                        console.log('Pagamento aprovado!');
                        window.location.href = customerUrl;
                    }
                },
            })
        },6000);
    }
});