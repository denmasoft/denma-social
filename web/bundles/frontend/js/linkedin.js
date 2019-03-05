function linkedin() {
    IN.User.authorize(getProfileData);
}
function getCookie(cname) {
    var name = cname + "=";
    var ca = document.cookie.split(';');
    for(var i=0; i<ca.length; i++) {
        var c = ca[i];
        while (c.charAt(0)==' ') c = c.substring(1);
        if (c.indexOf(name) == 0) return c.substring(name.length,c.length);
    }
    return "";
}

// Handle the successful return from the API call
function onSuccess(response) {
    console.log(response);
    $.ajax({
        url: '/app_dev.php/es/check-linkedin',
        method: "POST",
        dataType: 'json',
        data: {'id':response.id,
            'name': response.formattedName,
            'email':response.emailAddress,
            'link': response.publicProfileUrl,
            'picture': (response.pictureUrl!=null) ? response.pictureUrl : 'https://static.licdn.com/scds/common/u/img/icon/icon_no_photo_40x40.png',
            'accessToken': response.id},
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
}

// Handle an error response from the API call
function onError(error) {
    console.log(error);
}

// Use the API call wrapper to request the member's basic profile data
function getProfileData() {
    IN.API.Raw("/people/~:(id,formattedName,email-address,picture-url,public-profile-url)").result(onSuccess).error(onError);
}