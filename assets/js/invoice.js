class Invoice {
    // Constructor
    constructor(tb_Invoice, tb_InvoiceDetail, id, masterInvoice, detailInvoice) {
        this.tb_Invoice = tb_Invoice || null;
        this.tb_InvoiceDetail = tb_InvoiceDetail || null;
        this.id = id || null;
        this.masterInvoice = masterInvoice || new Array();
        this.detailInvoice = detailInvoice || new Array();
    };

    loadInvoice() {
        $.ajax({
            type: "POST",
            url: "class/Invoice.php",
            data: {
                action: 'ReadAll'
            }
        })
            .done(function (e) {
                invoice.invoicesDraw(e);
            });
    };

    invoicesDraw(e){
        var invoice = JSON.parse(e);

        this.tb_Invoice = $('#tb_DOM').DataTable({
            data: invoice, 
            destroy: true,
            "paging": false,
            columns: [
                {
                    title: "ID Cliente",
                    data: "idCliente",
                    visible: false
                },
                {
                    title: "Nombre",
                    data: "name"
                },
                {
                    title: "ID Contrato",
                    data: "idContrato",
                    visible: false
                },
                {
                    title: "Contrato",
                    data: "number"
                },
                {
                    title: "Día de Fact",
                    data: "bill_day"
                },
                {
                    title: "Frecuencia",
                    data: "frequencyPay"
                },
                {
                    title: "Precio",
                    data: "priceMonitoring"
                },
                {
                    title: "Pago",
                    data: "methodPayment"
                }
            ]
        });  
        // document.getElementById("btnBill").click();      
    };

    GenerateInvoice() {
        if(this.tb_Invoice.columns().length){
            // $( this.tb_Invoice.rows().data()).each(function (ic, c) {
            //     var objetoInvoice = new Object();
            //     objetoInvoice.idClient = c.idCliente;
            //     objetoInvoice.name = c.name;
            //     objetoInvoice.number = c.number;
            //     objetoInvoice.idContrato = c.idContrato;
            //     objetoInvoice.bill_day = c.bill_day
            //     objetoInvoice.totalVenta = c.priceMonitoring;
            //     invoice.masterInvoice.push(objetoInvoice);
            // });

            $.ajax({
                type: "POST",
                url: "class/Invoice.php",
                data: {
                    action: 'Create'
                    // obj: JSON.stringify(this)
                }
            })
            .done(function (e) {
                // if(e==true)
                swal({
                    type: 'success',
                    text: 'Correo Enviado!',
                    timer: 1000
                    // showConfirmButton: true
                });
            });
        }
    };
}

let invoice = new Invoice();




$("#btnBill").click(function(){
    invoice.GenerateInvoice();
});

$(document).ajaxStart(function() {
    // show loader on start
    $("#btnBill").addClass("disabled");
    NProgress.start();
}).ajaxSuccess(function() {
    // hide loader on success
    $("#btnBill").removeClass("disabled");
    NProgress.done();
});