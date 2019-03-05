profile_fb_created = 0;
(function(d, s, id) {
    var js, fjs = d.getElementsByTagName(s)[0];
    if (d.getElementById(id)) return;
    js = d.createElement(s); js.id = id;
    js.src = "//connect.facebook.net/en_US/sdk.js";
    fjs.parentNode.insertBefore(js, fjs);
}(document, 'script', 'facebook-jssdk'));

window.fbAsyncInit = function() {
    FB.init({
        appId      : '494431310714400',
        cookie     : true,  // enable cookies to allow the server to access
                            // the session
        xfbml      : true,  // parse social plugins on this page
        version    : 'v2.2' // use version 2.2
    });
};
function statusChangeCallback(response,type) {
    if (response.status === 'connected') {
        processFacebook(response.authResponse.accessToken,type);
    } else if (response.status === 'not_authorized') {
        alert('Please log into this app.');
    } else {
        alert('Please log into facebook');
    }
};
$("#facebookLogin-modal-groups").on("click", ".select-fb-group", function(e){
    var el = $(e.target);
    el.prop('disabled', true).addClass('disabled');
    if( profile_fb_created ){
        $.ajax({
            type: "POST",
            url: Routing.generate('fb_add_group', "", true),
            data: {p_id : profile_id_fb_created, g_id : el.attr('gid')},
            dataType: "json",
            success: function(data){

            }
        });
    }
});
function processFacebook(accessToken,type) {
    localStorage.setItem('facebook',null);
    FB.api('/me?fields=id,name,email,picture,link', function (response) {
        localStorage.setItem('facebook',response.id);
        $.ajax({
            url: '/app_dev.php/es/check-facebook',
            method: "POST",
            dataType: 'json',
            data: {'id':response.id,
                'name': response.name,
                'email':response.email,
                'link': response.link,
                'picture': response.picture.data.url,
                'accessToken': accessToken},
            success: function(data){
                if(data.logged===true)
                {
                    window.location = Routing.generate('dashboard');
                }
                else
                {
                    window.location = "/app_dev.php/es/setup/"+data.user;
                    /*FB.api('/me/groups?fields=id,name', function (response) {
                        $.ajax({
                            url: '/app_dev.php/es/load-facebook-groups',
                            method: "POST",
                            dataType: 'json',
                            data: {'groups':response.data},
                            success: function(result){
                                $("#facebookLogin-modal-groups").modal('show').find(".modal-body").html(result.html);
                                profile_fb_created = data.profile;
                            }
                        });
                    });*/
                }
            }
        });
    });
};
function facebook(type)
{
    FB.login(function(response) {
        statusChangeCallback(response,type);
    }, {scope: 'email, publish_actions, user_about_me, user_birthday, user_education_history, user_events, user_groups, user_hometown, user_likes, user_location, user_photos, user_relationship_details, user_relationships, user_religion_politics, user_status, user_tagged_places,user_videos, user_website, user_work_history'});
}