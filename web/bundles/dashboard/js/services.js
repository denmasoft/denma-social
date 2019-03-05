var hootServices = angular.module('hootServices',[]);

hootServices.service('Tabs', function($http, $q, $rootScope){
    var service = {}

    service.load = function(){
        var request = $http({
            method  : "get",
            url     : Routing.generate('load_tabs', "", true)
        });
        return( request.then( handleSuccess, handleError ) );
    }

    service.addTab = function(){
        $rootScope.textLoading = "Salvando Tab";
        var request = $http({
            method  : "post",
            url     : Routing.generate('add_tab', "", true)
        });
        return( request.then( handleSuccess, handleError ) );
    }

    service.delTab = function(id){
        $rootScope.textLoading = "Eliminando Tab";
        var request = $http({
            method  : "post",
            data    : {'id': id},
            url     : Routing.generate('del_tab', '', true)
        });
        return( request.then( handleSuccess, handleError ) );
    }

    service.getColumns = function(tab){
        $rootScope.textLoading = "Obteniendo columnas";
        var request = $http({
            method  : "get",
            url     : Routing.generate('columns_tab', {id: tab.id}, true)
        });
        return( request.then( handleSuccess, handleError ) );
    }

    service.renameTab = function(tab){
        $rootScope.textLoading = "Renombrabdo";
        var request = $http({
            method  : "post",
            data    : {'id' : tab.id, 'name' : tab.rename},
            url     : Routing.generate('rename_tab', '', true)
        });
        return( request.then( handleSuccess, handleError ) );
    }
    /* columnas */

    service.loadCol = function(data){
        var request = $http({
            method  : "POST",
            data    : data,
            url     : Routing.generate('load_column', '', true)
        });
        return( request.then( handleSuccess, handleError ) );
    }

    service.addCol = function(data){
        $rootScope.textLoading = "Salvando Columna";
        var request = $http({
            method  : "post",
            data    : data,
            url     : Routing.generate('add_column', '', true)
        });
        return( request.then( handleSuccess, handleError ) );
    }

    service.delCol = function(id){
        $rootScope.textLoading = "Eliminando Columna";
        var request = $http({
            method  : "post",
            data    : {'id': id},
            url     : Routing.generate('del_column', '', true)
        });
        return( request.then( handleSuccess, handleError ) );
    }

    service.automatic = function(id, value){
        var request = $http({
            method  : "post",
            data    : {'id': id, 'value' : value},
            url     : Routing.generate('automatic_update', '', true)
        });
        return( request.then( handleSuccess, handleError ) );
    }

    service.activate = function(id){
        var request = $http({
            method  : "post",
            data    : {'id': id},
            url     : Routing.generate('activate_tab', '', true)
        });
        return( request.then( handleSuccess, handleError ) );
    }

    service.visible = function(tab){
        var request = $http({
            method  : "post",
            data    : {'id': tab.id, value: tab.visible},
            url     : Routing.generate('visible_tab', '', true)
        });
        return( request.then( handleSuccess, handleError ) );
    }

    service.searchCol = function(prof_id){
        var request = $http({
            method  : "post",
            data    : {p_id : prof_id},
            url     : Routing.generate('search_column', "", true)
        });
        return( request.then( handleSuccess, handleError ) );
    }

    service.listsCol = function(prof_id){
        var request = $http({
            method  : "post",
            data    : {p_id : prof_id},
            url     : Routing.generate('lists_column', "", true)
        });
        return( request.then( handleSuccess, handleError ) );
    }

    service.facebookPagesLikedByUser = function(prof_id){
        var request = $http({
            method  : "post",
            data    : {p_id : prof_id},
            url     : Routing.generate('facebook_pages', "", true)
        });
        return( request.then( handleSuccess, handleError ) );
    }
    service.instagramHashTag = function(prof_id,value){
        var request = $http({
            method  : "post",
            data    : {p_id : prof_id,query: value},
            url     : Routing.generate('instagram_hashtag', "", true)
        });
        return( request.then( handleSuccess, handleError ) );
    }

    service.listsSubscribesCol = function(prof_id){
        var request = $http({
            method  : "post",
            data    : {p_id : prof_id},
            url     : Routing.generate('lists_column', "", true)
        });
        return( request.then( handleSuccess, handleError ) );
    }

    service.delStream = function(msg_id, prof_id, type){
        var request = $http({
            method  : "post",
            data    : {p_id: prof_id, id: msg_id, tp : type},
            url     : Routing.generate('message_del', "", true)
        });
        return( request.then( handleSuccess, handleError ) );
    }

    service.order = function(order){
        var request = $http({
            method  : "post",
            data    : {order : order},
            url     : Routing.generate('order_tab', "", true)
        });
        return( request.then( handleSuccess, handleError ) );
    }

    service.getProfile = function(screen_name, p_id, type){
        var request = $http({
            method  : "post",
            data    : {id : screen_name, p_id : p_id, ty: type},
            url     : Routing.generate('usuarios_redes_profile', "", true)
        });
        return( request.then( handleSuccess, handleError ) );
    }
    service.getUserDataProfile = function(){
        var request = $http({
            method  : "post",
            data    : {},
            url     : Routing.generate('usuarios_data_profile', "", true)
        });
        return( request.then( handleSuccess, handleError ) );
    }
    service.adjust = function(){//ADJUSt
        var request = $http({
            method  : "post",
            data    : {},
            url     : Routing.generate('adjustments', "", true)
        });
        return( request.then( handleSuccess, handleError ) );
    };

    function handleError( response ) {
        if (! angular.isObject( response.data ) || ! response.data.success ) {
            return( $q.reject( "An unknown error occurred." ) );
        }
        return( $q.reject( response.data.message ) );
    }
    function handleSuccess( response ) {
        return( response.data );
    }

    return service;
});

hootServices.service('Redes', function($http, $q){
    var service = {};

    service.load = function(){
        var request = $http({
            method  : "get",
            url     : Routing.generate('load_redes', "", true)
        });
        return( request.then( handleSuccess, handleError ) );
    }

    service.addRed = function(){

    }

    function handleError( response ) {
        if (! angular.isObject( response.data ) || ! response.data.message ) {
            return( $q.reject( "An unknown error occurred." ) );
        }
        return( $q.reject( response.data.message ) );
    }
    function handleSuccess( response ) {
        return( response.data );
    }

    return service;
});

hootServices.service('Profiles', function($http, $q){
    var service = {};

    service.load = function(){
        var request = $http({
            method  : "get",
            url     : Routing.generate('load_profiles', "", true)
        });
        return( request.then( handleSuccess, handleError ) );
    }

    service.addProfile = function(){

    }

    function handleError( response ) {
        if (! angular.isObject( response.data ) || ! response.data.message ) {
            return( $q.reject( "An unknown error occurred." ) );
        }
        return( $q.reject( response.data.message ) );
    }
    function handleSuccess( response ) {
        return( response.data );
    }

    return service;
});

hootServices.service('Miembro', function($http, $q){
    var service = {};

    service.load = function(){
        var request = $http({
            method  : "get",
            url     : Routing.generate('miembro_data', "", true)
        });
        return( request.then( handleSuccess, handleError ) );
    };

    service.addProfile = function(){

    };
    service.removeProfile = function(id){
        var request = $http({
            method  : "post",
            data: {'id':id},
            url     : Routing.generate('remove_profile', "", true)
        });
        return( request.then( handleSuccess, handleError ) );
    };

    service.synchronize = function(profile){
        var request = $http({
         method  : "post",
         data    : {profile: profile},
         url     : Routing.generate('profile_synchronize', "", true)
         });
        return( request.then( handleSuccess, handleError ) );
    };
    service.update = function(id,user){
        var request = $http({
            method  : "post",
            data: $.extend({'id':id},user),
            url     : Routing.generate('member_update', "", true)
        });
        return( request.then( handleSuccess, handleError ) );
    };

    function handleError( response ) {
        if (! angular.isObject( response.data ) || ! response.data.message ) {
            return( $q.reject( "An unknown error occurred." ) );
        }
        return( $q.reject( response.data.message ) );
    }
    function handleSuccess( response ) {
        return( response.data );
    }

    return service;
});

hootServices.service('Message', function($http, $q,Upload){
    var service = {};

    service.loadDrafts = function(){
        var request = $http({
            method  : "get",
            url     : Routing.generate('load_drafts', "", true)
        });
        return( request.then( handleSuccess, handleError ) );
    }
    service.loadScheduled = function(){
        var request = $http({
            method  : "get",
            url     : Routing.generate('load_scheduled', "", true)
        });
        return( request.then( handleSuccess, handleError ) );
    }

    service.send = function(message,profiles,photos){
        var request = Upload.upload({
            url     : Routing.generate('message_send', "", true),
            data    : {file: photos,message: message, profiles: profiles}
        });
        /*var request = $http({
         method  : "post",
         data    : {message: message, profiles: profiles},
         url     : Routing.generate('message_send', "", true)
         });*/
        return( request.then( handleSuccess, handleError ) );
    };
    service.draft = function(message,profiles,photos){
        var request = Upload.upload({
            url     : Routing.generate('message_draft', "", true),
            data    : {file: photos,message: message, profiles: profiles}
        });
        return( request.then( handleSuccess, handleError ) );
    };
    service.schedule = function(message,profiles,photos){
        var request = Upload.upload({
            url     : Routing.generate('message_schedule', "", true),
            data    : {file: photos,message: message, profiles: profiles}
        });
        return( request.then( handleSuccess, handleError ) );
    };
    service.shorten = function(url,service){
        var request = $http({
            method  : "post",
            data    : {'url':url,'service':service},
            url     : Routing.generate('shorten_url', "", true)
        });
        return( request.then( handleSuccess, handleError ) );
    };

    service.Countries = function(){
        var request = $http({
            method  : "get",
            url     : Routing.generate('countries', "", true)
        });
        return( request.then( handleSuccess, handleError ) );
    };

    function handleError( response ) {
        if (! angular.isObject( response.data ) || ! response.data.message ) {
            return( $q.reject( "An unknown error occurred." ) );
        }
        return( $q.reject( response.data.message ) );
    }
    function handleSuccess( response ) {
        return( response.data );
    }

    return service;
});

hootServices.service('Twitter', function($http, $q){
    var service = {};

    service.favorite = function(prof_id, id){
        var request = $http({
            method  : "post",
            data    : {p_id : prof_id, id : id},
            url     : Routing.generate('twitter_favorite', "", true)
        });
        return( request.then( handleSuccess, handleError ) );
    }

    service.follow = function(prof_id, id, follow){
        var request = $http({
            method  : "post",
            data    : {p_id: prof_id, id: id, follow : follow},
            url     : Routing.generate('twitter_follow', "", true)
        });
        return( request.then( handleSuccess, handleError ) );
    }

    service.spammer = function(prof_id, id){
        var request = $http({
            method  : "post",
            data    : {p_id: prof_id, id : id},
            url     : Routing.generate('twitter_spammer', "", true)
        });
        return( request.then( handleSuccess, handleError ) );
    }

    service.getRetuits = function(id, prof_id){
        var request = $http({
            method  : "post",
            data    : {id : id, p_id: prof_id},
            url     : Routing.generate('twitter_get_retweets', "", true)
        });
        return( request.then( handleSuccess, handleError ) );
    }

    service.createList = function(data){
        var request = $http({
            method  : "post",
            data    : data,
            url     : Routing.generate('twitter_create_list', "", true)
        });
        return( request.then( handleSuccess, handleError ) );
    }

    service.createListMember = function(list_id, prof_id, user_id){
        var request = $http({
            method  : "post",
            data    : {id: list_id, p_id: prof_id, u_id: user_id},
            url     : Routing.generate('twitter_create_list_member', "", true)
        });
        return( request.then( handleSuccess, handleError ) );
    }

    service.showConversation = function(id, profile_id){
        var request = $http({
            method  : "post",
            data    : {id: id, p_id : profile_id},
            url     : Routing.generate('twitter_show_conversation', "", true)
        });
        return( request.then( handleSuccess, handleError ) );
    }

    service.addToList = function(user, profile_id){
        var request = $http({
            method  : "post",
            data    : {id: user, p_id : profile_id},
            url     : Routing.generate('twitter_show_conversation', "", true)
        });
        return( request.then( handleSuccess, handleError ) );
    }

    service.getCronology = function(u_id, p_id){
        var request = $http({
            method  : "post",
            data    : {id : u_id, p_id : p_id},
            url     : Routing.generate('twitter_user_cronology', "", true)
        });
        return( request.then( handleSuccess, handleError ) );
    }

    service.hashSearch = function(p_id, hash, min_id){
        var request = $http({
            method  : "post",
            data    : {hash : hash, p_id : p_id, min_id : (min_id ? min_id : '')},
            url     : Routing.generate('twitter_hash-search', "", true)
        });
        return( request.then( handleSuccess, handleError ) );
    }

    service.searchUsers = function(p_id, hash, min_id){
        var request = $http({
            method  : "post",
            data    : {hash : hash, p_id : p_id, min_id : (min_id ? min_id : '')},
            url     : Routing.generate('twitter_user-search', "", true)
        });
        return( request.then( handleSuccess, handleError ) );
    }

    service.getMentions = function(u_id, p_id){
        var request = $http({
            method  : "post",
            data    : {id : u_id, p_id : p_id},
            url     : Routing.generate('twitter_user_mentions', "", true)
        });
        return( request.then( handleSuccess, handleError ) );
    }

    service.getFavorites = function(u_id, p_id){
        var request = $http({
            method  : "post",
            data    : {id : u_id, p_id : p_id},
            url     : Routing.generate('twitter_user_favorites', "", true)
        });
        return( request.then( handleSuccess, handleError ) );
    }

    service.retweet = function(id, p_id){
        var request = $http({
            method  : "post",
            data    : {id : id, p_id : p_id},
            url     : Routing.generate('twitter_retweet', "", true)
        });
        return( request.then( handleSuccess, handleError ) );
    }

    service.trendTopics = function(p_id){
        var request = $http({
            method  : "post",
            data    : {p_id : p_id},
            url     : Routing.generate('twitter_trend-topics', "", true)
        });
        return( request.then( handleSuccess, handleError ) );
    }

    function handleError( response ) {
        if (! angular.isObject( response.data ) || ! response.data.message ) {
            return( $q.reject( "An unknown error occurred." ) );
        }
        return( $q.reject( response.data.message ) );
    }
    function handleSuccess( response ) {
        return( response.data );
    }

    return service;
});

hootServices.service('Facebook', function($http, $q){
    var service = {};

    service.addComment = function(prof_id, id, comment){
        var request = $http({
            method  : "post",
            data    : {p_id : prof_id, id : id, comment : comment},
            url     : Routing.generate('fb_add_comment', "", true)
        });
        return( request.then( handleSuccess, handleError ) );
    }

    service.likes = function(prof_id, id, on){
        var request = $http({
            method  : "post",
            data    : {p_id : prof_id, id : id, on : on},
            url     : Routing.generate('fb_likes', "", true)
        });
        return( request.then( handleSuccess, handleError ) );
    }

    service.addGroup = function(group_id, profile){
        var request = $http({
            method  : "post",
            data    : {g_id : group_id, p_id : profile},
            url     : Routing.generate('fb_add_group', "", true)
        });
        return( request.then( handleSuccess, handleError ) );
    }

    function handleError( response ) {
        if (! angular.isObject( response.data ) || ! response.data.message ) {
            return( $q.reject( "An unknown error occurred." ) );
        }
        return( $q.reject( response.data.message ) );
    }
    function handleSuccess( response ) {
        return( response.data );
    }

    return service;
});

hootServices.service('Linkedin', function($http, $q){
    var service = {};

    service.addComment = function(prof_id, id, comment){
        var request = $http({
            method  : "post",
            data    : {p_id : prof_id, id : id, comment : comment},
            url     : Routing.generate('li_add_comment', "", true)
        });
        return( request.then( handleSuccess, handleError ) );
    }

    service.likes = function(prof_id, id, on){
        var request = $http({
            method  : "post",
            data    : {p_id : prof_id, id : id, on : on},
            url     : Routing.generate('li_likes', "", true)
        });
        return( request.then( handleSuccess, handleError ) );
    }

    service.userUpdates = function(id, prof_id, ty){
        var request = $http({
            method  : "post",
            data    : {p_id : prof_id, id : id, ty : ty},
            url     : Routing.generate('li_user_updates', "", true)
        });
        return( request.then( handleSuccess, handleError ) );
    }

    function handleError( response ) {
        if (! angular.isObject( response.data ) || ! response.data.message ) {
            return( $q.reject( "An unknown error occurred." ) );
        }
        return( $q.reject( response.data.message ) );
    }
    function handleSuccess( response ) {
        return( response.data );
    }

    return service;
});
hootServices.service('Instagram', function($http, $q){
    var service = {};

    service.getUserFeed = function(u_id, p_id){
        var request = $http({
            method  : "post",
            data    : {id : u_id, p_id : p_id},
            url     : Routing.generate('instagram_user_feed', "", true)
        });
        return( request.then( handleSuccess, handleError ) );
    }

    service.addComment = function(prof_id, id, comment){
        var request = $http({
            method  : "post",
            data    : {p_id : prof_id, id : id, comment : comment},
            url     : Routing.generate('instagram_add_comment', "", true)
        });
        return( request.then( handleSuccess, handleError ) );
    }

    service.likes = function(prof_id, id, on){
        var request = $http({
            method  : "post",
            data    : {p_id : prof_id, id : id, on : on},
            url     : Routing.generate('instagram_likes', "", true)
        });
        return( request.then( handleSuccess, handleError ) );
    }

    service.searchTerms = function(prof_id, term, on){
        var request = $http({
            method  : "post",
            data    : {p_id : prof_id, term : term, on : on},
            url     : Routing.generate('instagram_search-terms', "", true)
        });
        return( request.then( handleSuccess, handleError ) );
    }

    service.searchUsers = function(prof_id, term, on){
        var request = $http({
            method  : "post",
            data    : {p_id : prof_id, term : term, on : on},
            url     : Routing.generate('instagram_search-users', "", true)
        });
        return( request.then( handleSuccess, handleError ) );
    }

    service.follows = function(prof_id, id, on){
        var request = $http({
            method  : "post",
            data    : {p_id : prof_id, id : id, on : on},
            url     : Routing.generate('instagram_follows', "", true)
        });
        return( request.then( handleSuccess, handleError ) );
    }


    function handleError( response ) {
        if (! angular.isObject( response.data ) || ! response.data.message ) {
            return( $q.reject( "An unknown error occurred." ) );
        }
        return( $q.reject( response.data.message ) );
    }
    function handleSuccess( response ) {
        return( response.data );
    }

    return service;
});

hootServices.service('Tags', function($http, $q){
    var service = {};

    service.load = function(){
        var request = $http({
            method  : "get",
            url     : Routing.generate('load_tags', "", true)
        });
        return( request.then( handleSuccess, handleError ) );
    }
    service.remove = function(id){
        var request = $http({
            method  : "post",
            data:{id:id},
            url     : Routing.generate('tag_remove', "", true)
        });
        return( request.then( handleSuccess, handleError ) );
    }

    function handleError( response ) {
        if (! angular.isObject( response.data ) || ! response.data.message ) {
            return( $q.reject( "An unknown error occurred." ) );
        }
        return( $q.reject( response.data.message ) );
    }
    function handleSuccess( response ) {
        return( response.data );
    }
    return service;
});