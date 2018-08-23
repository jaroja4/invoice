class Clients {
    // Constructor
    constructor(tb_Clients, tb_Device_x_Clients, id, name, company, tel, email) {
        this.tb_Clients = tb_Clients || null;
        this.tb_Device_x_Clients = tb_Device_x_Clients || null;
        this.id = id || null;
        this.name = name || null;
        this.company = company || null;
        this.tel = tel || null;
        this.email = email || null;
    };

    loadClients() {
        $.ajax({
            type: "POST",
            url: "class/Client.php",
            data: {
                action: 'ReadAll'
            }
        })
            .done(function (e) {
                // clients.clientsDraw(e);
            });
    };

}

let clients = new Clients();

$(document).ready(function () {
    clients.loadClients();
});