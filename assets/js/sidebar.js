var SideBar = {
    LoadSideBar(){
        $.ajax({           
            type: "POST",
            url: "class/SideBar.php",
            data: {
                action: 'ReadAll',
                url: window.location.href
            }
        })
        .done(function( e ) {
            SideBar.sideBarDraw(e);
        })   

    },
    sideBarDraw(e) {

        var dataMenu= JSON.parse(e); 
        if ($("#sidebar-menu").length) {

            $("#sidebar-menu").empty();

            var menu_section =
                `<div class="menu_section">
                <h3>${dataMenu.title}</h3>
                <ul id="menu" class="nav side-menu">
                    
                </ul>
            </div>`;
            $("#sidebar-menu").append(menu_section);

            $.each(dataMenu, function (i, item) {
                if ($('#' + item.menuL1).length) {
                    var link =
                        ` <li><a href="${item.url}">${item.menuL2}</a></li>`
                    $("#list_" + item.menuL1).append(link);
                } else {
                    var menu =
                        `<li id="${item.menuL1}" ><a><i class="${item.icon}"></i> ${item.menuL1} <span class="fa fa-chevron-down"></span></a>
                            <ul id="list_${item.menuL1}" class="nav child_menu">
                                <li><a href="${item.url}">${item.menuL2}</a></li>
                            </ul>
                        </li>`
                    $("#menu").append(menu);
                }
            });

            if (typeof init_sidebar === "function") 
                init_sidebar();
            else {
                setTimeout(function(){
                    init_sidebar()                     
                 }, 1000);
                
            }
        }
    }
}