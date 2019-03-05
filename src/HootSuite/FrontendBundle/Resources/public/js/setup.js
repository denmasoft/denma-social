profile_id_fb_created = 0;
profile_id_go_created = 0;
profile_id_li_created = 0;
created_ = false;
user_id = null;
$(document).ready(function (e) {
    $('#csp').on('click',function(){
        $('#cspf').submit();
    });
    $(".redes-selected").on("click", ".btn-remove", function () {
        delProfile($(this).parents(".red_added"));
    });

    $("#fb-modal-groups").on("click", "#facebook-imported", function (e) {
        if(created_==true)
        {
            window.location="setup/"+user_id;
        }
    });

    $("#li-modal-groups").on("click", "#linkedin-imported", function (e) {
        if(created_==true)
        {
            window.location="setup/"+user_id;
        }
    })
    $("#go-modal-pages").on("click", "#google-imported", function (e) {
        if(created_==true)
        {
            window.location="setup/"+user_id;
        }
    })

    $("#fb-modal-groups").on("click", ".select-fb-group", function (e) {
        var el = $(e.target);
        el.prop('disabled', true).addClass('disabled');
        if (profile_id_fb_created) {
            $.ajax({
                type: "POST",
                url: Routing.generate('fb_add_group', "", true),
                data: {p_id: profile_id_fb_created, g_id: el.attr('gid')},
                dataType: "json",
                success: function (data) {
                    if(created_==false)
                    {
                        $(".redes-selected").append(data.html);
                    }
                }
            });
        }
    });
    $("#li-modal-groups").on("click", ".select-li-group", function (e) {
        var el = $(e.target);
        el.prop('disabled', true).addClass('disabled');
        if (profile_id_li_created) {
            $.ajax({
                type: "POST",
                url: Routing.generate('li_add_group', "", true),
                data: {p_id: profile_id_li_created, g_id: el.attr('gid')},
                dataType: "json",
                success: function (data) {
                    if(created_==false)
                    {
                        $(".redes-selected").append(data.html);
                    }
                }
            });
        }
    });
    $("#gp-modal-pages").on("click", ".select-go-pages", function (e) {
        var el = $(e.target);
        el.prop('disabled', true).addClass('disabled');
        if (profile_id_go_created) {
            $.ajax({
                type: "POST",
                url: Routing.generate('go_add_page', "", true),
                data: {p_id: profile_id_go_created, g_id: el.attr('gid')},
                dataType: "json",
                success: function (data) {
                    if(created_==false)
                    {
                        $(".redes-selected").append(data.html);
                    }
                }
            });
        }
    });
})


function openAuth(type, id, btn,created) {

    var win = window.open(Routing.generate('connect_' + type, {id: id}, true), "", "width=800, height=600");
    count = 0;
    var interval = window.setInterval(function () {
        try {
            if (win == null || win.closed) {
                window.clearInterval(interval);
                isAuthorized(id, btn, count,created);
            }
        }
        catch (e) {
        }
    }, 1500);

    $(btn).addClass('disabled');
}

function isAuthorized(red, btn, count,created) {
    if (count < 5) {
        $.getJSON(Routing.generate('social_is_authorized', {'red': red}, true), function (data) {
            if (data.success == true) {
                if(!created || created==false)
                {
                    $(btn).removeClass('disabled');
                    $(".redes-selected").append(data.html);
                    count = 0;
                    controlNext();
                }
                if (red == 2 && data.groups) { //facebook
                    $("#fb-modal-groups").modal('show').find(".modal-body").html(data.groups);
                    profile_id_fb_created = data.profile.id;
                    created_=created;
                    user_id = data.profile.user;
                }
                if (red == 3 && data.pages) { //google
                    $("#go-modal-groups").modal('show').find(".modal-body").html(data.groups);
                    profile_id_go_created = data.profile.id;
                    created_=created;
                    user_id = data.profile.user;
                }
                if (red == 4 && data.groups) { //linkedin
                    $("#li-modal-groups").modal('show').find(".modal-body").html(data.groups);
                    profile_id_li_created = data.profile.id;
                    created_=created;
                    user_id = data.profile.user;
                }
                if(created==true)
                {
                    window.location=Routing.generate('usuario_setup',{id:data.profile.user});
                }
            }
            else {
                count++;
                setTimeout(function () {
                    isAuthorized(red, btn, count,created);
                }, 1000);
            }
        });
    }
    else {
        $(btn).removeClass('disabled');
    }
}

function delProfile(el) {
    var id = el.prop('id');
    $.getJSON(Routing.generate('connect_del_profile', {'id': id}, true), function (data) {
        if (data.success == true) {
            el.remove();
            controlNext();
        }
    });
}

function controlNext() {
    if ($(".red_added").length) {
        $(".btn-next").removeClass('disabled');
    }
    else {
        $(".btn-next").addClass('disabled');
    }
}