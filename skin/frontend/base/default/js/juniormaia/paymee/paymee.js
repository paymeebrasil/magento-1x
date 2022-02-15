jQuery(document).ready(function() {
    var paymeeMethod    = document.getElementById("paymee-method").value;
    console.log(paymeeMethod);

    if (paymeeMethod == 'juniormaia_paymee_pix') {
        console.log('chamou js success paymee pix');
        var uuid            = document.getElementById("uuid").value;
        var url             = document.getElementById("paymee-url").value;
        var customerUrl     = document.getElementById("customer-url").value;

        var pixCheckTimer = setInterval(function(){
            jQuery.ajax({
                url: url,
                type: "GET",
                data: "uuid="+uuid,
                success: function (data) {
                    if (data == 'PAID') {
                        console.log('Pagamento aprovado!');
                        document.getElementById('container').style.display = 'none';
                        document.getElementById('alert').style.display = 'block';
                        clearInterval(pixCheckTimer);
                    }
                },
            })
        },6000);

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
    }

    if (paymeeMethod == 'juniormaia_paymee_boleto') {
        jQuery('#boleto_send_documents').on("click",function(){

            if (document.getElementById("file_upload_document").files.length == 0){
                alert("Por favor, anexe uma imagem do seu documento!");
                return;
            }

            if (document.getElementById("file_upload_selfie").files.length == 0){
                alert("Por favor, anexe uma imagem com sua selfie!");
                return;
            }

            var form_data = new FormData();
            form_data.append('file_upload_selfie', jQuery.noConflict()('#file_upload_selfie').prop('files')[0]);
            form_data.append('file_upload_document', jQuery.noConflict()('#file_upload_document').prop('files')[0]);

            var paymeeSendDocUrl = document.getElementById("send-documents-boleto-url").value;

            jQuery.noConflict().ajax({
                url: paymeeSendDocUrl,
                cache: false,
                contentType: false,
                processData: false,
                data: form_data,
                type: 'post',
                success: function(data){
                    if (data == null || data == "null") {
                        alert('Ops, tivemos problema em salvar seus arquivos... Por favor selecione um arquivo válido (jpg, png, pdf)');
                    } else {
                        console.log(data);
                        if (isJson(data)) {
                            var response = JSON.parse(data);
                            var status = response['success'];
                            console.log(status);
                            if (status == '1') {
                                var elems = document.getElementsByClassName('boleto');
                                for (var i=0;i<elems.length;i+=1){
                                    elems[i].style.display = 'none';
                                }
                                document.getElementById('alert-boleto').style.display = 'block';
                                alert('Sua documentação foi enviada com sucesso!');
                            } else {
                                var error = response['message'];
                                var error_json = JSON.stringify(error, null, 4);
                                alert(error_json);
                            }
                        }
                    }
                }
            });
        })
    }
});

function isJson(str) {
    try {
        JSON.parse(str);
    } catch (e) {
        return false;
    }
    return true;
}
