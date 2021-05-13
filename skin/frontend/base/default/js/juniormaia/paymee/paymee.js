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
                        document.getElementById('container').style.display = 'none';
                        document.getElementById('alert').style.display = 'block';
                    }
                },
            })
        },6000);
    }

    var fiveMinutes = 60 * 10, display = document.querySelector('#tempo');
    startTimer(fiveMinutes, display);

    function startTimer(duration, display) {
        var timer = duration, minutes, seconds;
        setInterval(function () {
            minutes = parseInt(timer / 60, 10);
            seconds = parseInt(timer % 60, 10);

            minutes = minutes < 10 ? "0" + minutes : minutes;
            seconds = seconds < 10 ? "0" + seconds : seconds;

            display.textContent = "Tempo restante "+ minutes + ":" + seconds;

            if (--timer < 0) {
                timer = duration;
            }
        }, 1000);
    }

    var clipboard = new ClipboardJS('.btn');

    clipboard.on('success', function(e) {
        console.info('Action:', e.action);
        console.info('Text:', e.text);
        console.info('Trigger:', e.trigger);
        alert('Código de pagamento copiado com sucesso!');
        e.clearSelection();
    });

    clipboard.on('error', function(e) {
        console.error('Action:', e.action);
        console.error('Trigger:', e.trigger);
    });
});