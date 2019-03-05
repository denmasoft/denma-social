var hootControllers = angular.module('hootControllers', ['geolocation','cgNotify']);
hootControllers.controller('routeController', function ($scope, $location) {
    $scope.active_route = $location.path();
});

hootControllers.controller('MessagesController', function($scope, $rootScope, geolocation, Profiles, Redes, Message, dialogs, notify){
    $rootScope.message = {};
    $rootScope.profiles = {};
    Profiles.load().then(function(data){
        $rootScope.profiles = data.object;
    }, function(){
        Profiles.load().then(function( data ) {
            $rootScope.profiles = $rootScope.profiles_copia = data.object;
        })
    });
    
    Redes.load().then(function(data){
        $scope.redes = $rootScope.redes_copia = data.object;
    }, function(){
        Redes.load().then(function( data ) {
            $scope.redes = $rootScope.redes_copia = data.object;
        })
    });
    
    $rootScope.addProfile = function(id_red){
        var url = '../es/modal/redes';
        if( id_red ){url = url + '/'+ id_red;}
        $scope.modalAddProf = dialogs.create(url,'modalsDialogCtrl',{'id_red' : id_red},{backdrop: 'static', windowClass:"height-normal"});
    }
    
    $rootScope.getProfile = function(profile_id){
        var object = '';
        angular.forEach($rootScope.profiles, function(prof){
            if(prof.id == profile_id){
                object = prof;
            }
        });
        return object;
    }
    
    $rootScope.model = {};
    $rootScope.model.message_writting = false;
    $rootScope.model.profiles_selections = new Array();
    $rootScope.model.profiles_default = new Array();
    $rootScope.model.lat = null;
    $rootScope.model.lng = null;
    
    $rootScope.startWriteMessage = function(autofocus){
        if( autofocus === true){
            $scope.is_focused_wind = true;
            angular.element("#message-box-input").focus().hover();
        }   
        $rootScope.model.message_writting = true;
    }
    
    $rootScope.getColId = function(profile, type){
        var col_id = 0;
        angular.forEach($rootScope.profiles, function(prof){
            if( prof.id == profile ){
                angular.forEach($rootScope.redes_copia, function(red){
                    if( red.uniquename == 'TWITTER' && red.id == prof.redid ){
                        angular.forEach(red.columns, function(col){
                            if( col.type == type ){
                                col_id = col.id;
                                return;
                            }
                        });
                    }
                });
            }
        });
                
        return col_id;
    }
    
    $rootScope.networkHasProfile = function(red_id){
        var prof_id = 0;
        angular.forEach($rootScope.profiles, function(prof){
            if( prof.redid == red_id ){
                prof_id = prof.id;
                return;
            }
        });
        return prof_id;
    }
    
    $scope.stopWriteMessage = function(blur){
        if( blur === true){
            $scope.is_focused_wind = false;
        }
        if(!$scope.is_focused_wind)
            $rootScope.model.message_writting = false;
    }
    
    $rootScope.selectProfile = function(id, select){
        if( select === true && !$rootScope.model.profiles_selections.indexOf(id) ){
            $rootScope.model.profiles_selections.push(id);
        }
        if($rootScope.model.profiles_selections.indexOf(id) >= 0){
            $rootScope.model.profiles_selections.splice($rootScope.model.profiles_selections.indexOf(id),1);
        }
        else{
            $rootScope.model.profiles_selections.push(id);
        }
    }
    
    $scope.isProfileSelected = function(id){
        return $rootScope.model.profiles_selections.indexOf(id) >= 0 ;
    }
    
    $scope.removeAllProfiles = function(){
        $rootScope.model.profiles_selections = new Array();
    }
    
    $scope.selectAllProfiles = function(){
        angular.forEach($rootScope.profiles, function(item){
            $rootScope.model.profiles_selections.push(item.id);
        });
    }
    
    $scope.selectProfileDefault = function(id){
        if( $rootScope.model.profiles_default.indexOf(id) == -1 ){
            $rootScope.model.profiles_default.push(id);
        }
        if( $rootScope.model.profiles_selections.indexOf(id) == -1 ){
            $rootScope.model.profiles_selections.push(id);
        }
        console.debug('Select allways profile '+id);
    }
    
    $scope.unselectProfileDefault = function(id){
        $rootScope.model.profiles_default.splice($rootScope.model.profiles_default.indexOf(id),1);
        console.debug('Unselect profile '+id);
    }
    
    $scope.isProfileDefault = function(id){
        return $rootScope.model.profiles_default.indexOf(id) >= 0 ;
    }
    
    $scope.showAcortarVinculo = function(){
        $rootScope.model.extra_url=1; 
        $scope.input_width = 190;
        $rootScope.model.link_editing=true; 
        $rootScope.model.extra_editing='url';
        console.debug('peticion ajax Acortar Vinculo');
    }
    
    $scope.agregarVinculo = function(){
        $rootScope.model.link_editing=false; 
        $scope.input_width = 160;
        $rootScope.model.extra_editing='';
        var text = angular.element('.message-box-message');
        text.val(text.val()+$scope.message_link);
        $scope.message_link = '';
        console.debug('Acortar Vinculo Finish');
    }
    
    $scope.cancelarVinculo = function(){
        $scope.input_width = 160;
        $rootScope.model.link_editing=false; 
    }
    
    $scope.hideExtraMessage = function(id){
        console.debug(id);
    }
    
    $scope.agregarUbicacion = function(){
        $rootScope.loading = true;
        $rootScope.textLoading = 'Resolviendo ubicación';
        geolocation.getLocation().then(function(data){
            $rootScope.model.lat = data.coords.latitude;
            $rootScope.model.lng = data.coords.longitude;
            
        }, function(reason) {
            $rootScope.loading = false;
        });
    }
    /* sending message */    
    $scope.sendMessage = function(){
        $scope.sending_msg = true;
        $rootScope.textLoading = 'Enviando mensaje...';
        $rootScope.message.data.text = $scope.enter_text;
        Message.send($rootScope.message.data).then(function(data){
            $scope.sending_msg = false;
            $scope.stopWriteMessage(true);
            $scope.enter_text = "";
            notify(data.msg);
            //actualizar columnas.....pensarlo
        }, function(){
            //data.msg
            notify({message:'El mensaje no ha podido ser enviado.', type : 'error'});
        });
    }
    
    $rootScope.prepareMessage = function(profile, user, type, text, mentions){
        $rootScope.startWriteMessage(true);
        $rootScope.selectProfile(profile, true);
        $rootScope.message.user = user;
        $rootScope.message.data = {};
        $rootScope.message.data.type = type;
        $rootScope.message.data.profiles = new Array();        
        if(type == 'DIRECT'){
            $scope.enter_text = 'd @' + $rootScope.message.user.screen_name + ' ';
        }
        else if(type == 'REPLY'){
            $scope.enter_text = '@' + $rootScope.message.user.screen_name + ' ';
        }
        else if(type == 'RT'){
            $scope.enter_text = 'RT @' + $rootScope.message.user.screen_name + ': '+ text;
        }
        else if(type == 'ALL'){
            $scope.enter_text = "@"+$rootScope.message.user.screen_name;
            if( mentions ){
                angular.forEach(mentions, function(item){
                    if( item.screen_name != $rootScope.message.user.screen_name ){
                        $scope.enter_text += '@'+ item.screen_name + ' ';
                    }
                });
            }
            else{
                $scope.enter_text = '@' + $rootScope.message.user.screen_name + ' ';
            }
        }
        else{
            $scope.enter_text = $rootScope.message.user.name + ' ';
        }
        angular.forEach($rootScope.model.profiles_selections, function(item){
            $rootScope.message.data.profiles.push(item);
        });
        try{$rootScope.modalUser.close();}catch(e){}
        try{$rootScope.modalHash.close();}catch(e){}
    }
});

hootControllers.controller('WorkSpaceController', function($scope, $rootScope, Tabs, Profiles, Twitter, Facebook, Linkedin,Instagram, dialogs, $timeout, notify){
    Tabs.load().then(function( data ) {
        $scope.tabs = data.object;
        $scope.updateColumns();
    }, function(){
        Tabs.load().then(function( data ) {
            $scope.tabs = data.object;
            $scope.updateColumns();
        })
    });
    $rootScope.lists_own = new Array();
    $rootScope.lists_subs = new Array();
    
    $scope.num_columns = [{value: 1,name:1},{value: 2,name:2},{value: 3,name: 3},{value: 4, name: 4}];

    $scope.navWidth = (document.body.offsetWidth - 59)/4;
    $scope.winWidth = document.body.offsetWidth - 59;
    $scope.panelActive = true;
    
    $rootScope.openAuth = function (red_name, id){
        var win = window.open(Routing.generate('connect_'+red_name.toLowerCase(), {id:id}, true), "", "width=800, height=600");
        $scope.count_auth = 0;
        var interval = window.setInterval(function() {
            try {
                if (win == null || win.closed) {
                    window.clearInterval(interval);
                    $rootScope.isAuthorized(id, red_name, $scope.count_auth);
                }
            }
            catch (e) {}
        }, 1500);
        angular.element('.btn-auth-'+red_name).addClass('disabled');
    }

    $rootScope.isAuthorized = function(red_id, red_name){
        if( $scope.count_auth < 5 ){
            $.getJSON( Routing.generate('social_is_authorized', {'red': red_id}, true), function( data ){
                if( data.success == true ){
                    angular.element('.btn-auth-'+red_name).removeClass('disabled');
                    $rootScope.profiles.push( data.profile );
                    $scope.count_auth = 0;
                    notify("El perfil ha sido agregado correctamente.");
                    if( red_id == 2 ){ //facebook
                        $rootScope.fb_groups = data.groups;
                        $rootScope.modalUser = dialogs.create('fbGroups.html', 'modalsDialogCtrl',{},{backdrop: 'static', windowClass:"height-normal", size: "sm"});
                        $rootScope.profile_id_fb_created = data.profile.id;
                    }
                }
                else{
                    $scope.count_auth++;
                    setTimeout(function(){$rootScope.isAuthorized(red_id,red_name,$scope.count_auth)}, 1000);
                }
            });
        }
        else{
            angular.element('.btn-auth-'+red_name).removeClass('disabled');
        }
    }
    
    $scope.sortableOptions = {
        tolerance: "pointer",
        distance: 5,
        axis: 'x',
        placeholder: "sortable-placeholder",
        forcePlaceholderSize: true,
        handle: ".column-header"
    };

    $scope.clickedTab = function(tab){
        if( tab.active ){
            if( ( tab.columns === 0 ) ){
                Tabs.getColumns(tab).then(function(data){
                    tab.columns = data.object;
                    $scope.updateColumns();
                });
            }
            if( !angular.isDefined(tab.timer_refresh) && tab.active){
                tab.time_refresh = setInterval(function(){
                    $scope.updateColumns(true);
                }, (tab.interval*60*1000));
            }
        }
    }
    
    $scope.selectTab = function(tab){
        if( !tab.activating ){
            tab.activating = true;
            $timeout(function(){
                Tabs.activate(tab.id).then(function(){
                    tab.activating = false;
                }, function(){tab.activating = false});
            }, 2000);
        }
    }
    
    $scope.changeWorkspaceCols = function(tab){
        $rootScope.textLoading = 'Guardando espacio de trabajo';
        $timeout(function(){
            Tabs.visible(tab);
        }, 1000);
    }
    
    $scope.addTab = function(){
        Tabs.addTab().then(function(data){
            $scope.tabs.push(data.object);
        });
    }
    
    $scope.delTab = function(id){
        var pos = $scope.getTabPos(id);
        var con = window.confirm("¿Está seguro que desea eliminar este tab?");
        if( pos >= 0 && con ){
            Tabs.delTab($scope.tabs[pos].id).then(function(data){
                $scope.tabs.splice(pos,1);
            });
        }
    }
    
    $scope.getTabPos = function(id){
        for( var i = 0; i < $scope.tabs.length; i++ ){
            if( (id && $scope.tabs[i].id == id ) || (!id && $scope.tabs[i].active) ){
                return i;
            }
        }
        return -1;
    }
    
    $scope.getColumnPos = function(id){
        var pos = $scope.getTabPos();
        if( pos != -1 && id ){
            for( var i = 0; i < $scope.tabs[pos].columns.length; i++ ){
                if( $scope.tabs[pos].columns[i].id == id ){
                    return pos+'-'+i;
                }
            }
        }
        return -1;
    }
    
    $scope.renameTab = function(tab){
        if( tab.active ){
            tab.editing = true;
        }
    }
    
    $scope.saveTabName = function(tab){
        tab.editing = false;      
        if( tab.name != tab.rename ){
            //save old value
            Tabs.renameTab(tab).then(function(){
                tab.name = tab.rename;
            });
        }
    }
    
    $scope.renamekeyPress = function(event, tab){
        if( event.keyCode == 13 ){
            $scope.saveTabName(tab);
        }
    }
    
    $scope.delRed = function(){
        alert('deleting Red');
    }
    
    $scope.delColumn = function(id){
        var pos = $scope.getColumnPos(id);
        var positions = pos.split('-');
        if( positions.length ){
            var col = $scope.tabs[positions[0]].columns[positions[1]];
            Tabs.delCol(col.id).then(function( data ) {
                $scope.tabs[positions[0]].columns.splice(positions[1],1);
                var params =  '\''+col.column+'\',\''+col.profile+'\',\''+col.type+'\',\''+col.terns+'\',\''+col.tab+'\'';
                notify({message:(data.message+' <a ng-click="addColumn('+params+'); $close()">\n\
                                <span class="glyphicon glyphicon1-undo2"></span> Deshacer</a>')});
            });
        }
    }
    
    $scope.updateColumns = function(timer){
        var pos = $scope.getTabPos();
        if($scope.tabs.length && $scope.tabs[pos].columns.length){
            $scope.update_all_cols = true;
            $rootScope.textLoading = 'Actualizando Columnas';
            angular.forEach($scope.tabs[pos].columns, function(item){
                if( timer != undefined ){
                    $scope.loadColData(item, 'max');
                }
                else{
                    $scope.updateColumn(item);    
                }
            });
        }
    }
    
    $scope.watchColumsActive = function(){
        var pos = $scope.getTabPos();
        var updating = false;
        angular.forEach($scope.tabs[pos].columns, function(item){
            if(item.updating == true){
                updating = true;
                return;
            }
        });
        if( updating == false ){
            $scope.update_all_cols = false;
        }
    }
    
    $scope.updateColumn = function(col){
        col.updating = true;
        if(!$scope.update_all_cols){
            $rootScope.textLoading = 'Actualizando Columna';
        }
        Tabs.loadCol({id: col.id}).then(function( data ) {
            col.data = data.object;
            col.html = data.html;
            col.max_id = data.max_id;
            col.min_id = data.min_id;
            col.updating=false;
            $scope.watchColumsActive();
        }, function(){
            col.updating=false;
            $scope.watchColumsActive();
        });
    }
    
    $scope.loadColData = function(col,opp){
        if( col.max_id != undefined || col.min_id != undefined ){
            var init = {
                id: col.id,
                max_id  : (opp == 'max' ? col.max_id : ''),
                min_id  : (opp == 'min' ? col.min_id : '')
            };
            col.updating = true;
            col.scrolling = true;
            Tabs.loadCol(init).then(function( data ) {
                angular.forEach(data.object, function(item){
                    col.data.push(item);
                });
                col.max_id = data.max_id;
                col.min_id = data.min_id;
                $timeout(function(){
                    col.scrolling = false;
                }, 1000);
                col.updating=false;
                $scope.watchColumsActive();
            }, function(){
                col.scrolling = false;
                col.updating=false;
                $scope.watchColumsActive();
            });
        }
    }
    
    $scope.automaticUpdateCall = function(tab,segs){
        Tabs.automatic(tab.id, segs).then(function( data ) {
            tab.interval = segs;
        }, function(){
            console.debug('No se pudo actualizar');
        });
    }
    
    $scope.selectColumnProfile = function(){
        $scope.modalAddCol = dialogs.create('typeColumns.html','modalsDialogCtrl',{},{backdrop: 'static', windowClass:"height-normal"});
    }
    
    $scope.testing = function(){
        $scope.modalTesting= dialogs.create('testing.html','modalsDialogCtrl',{},{backdrop: 'static', windowClass:"height-normal"});
    }
       
    $rootScope.userProfile = function(id, profile, name, sn, event, type){
        if( event ){
            event.preventDefault();
        }
        $rootScope.user = {};
        $rootScope.user.profile_screen  = name ? name : id;
        $rootScope.user.profile_id      = profile;
        if( !angular.isDefined($rootScope.user.profile)){
            Tabs.getProfile(id, profile, type).then(function( data ) {
                if( data.success ){
                    $rootScope.user.profile = data.object;
                }
                else{
                    notify({ message : data.msg, type : 'error'});
                }
            }, function(){
                notify({ message : "No se pudo obtener el perfil del usuario.", type : 'error'});
            });
        }
        try{$rootScope.modalUser.close();}catch(e){}
        $rootScope.modalUser = dialogs.create('userProfileModal.html', 'modalsDialogCtrl',{},{backdrop: 'static', windowClass:"height-normal"});
    }
    
    $rootScope.hashSearch = function(profile, hash){
        $rootScope.twitter_search = {}
        $rootScope.twitter_search.profile_id = profile;
        $rootScope.twitter_search.hash_name = hash;
        $rootScope.twitter_search.searching = true;
        Twitter.hashSearch(profile, hash, $rootScope.twitter_search.min_id).then(function( data ) {
            if( data.success ){
                $rootScope.twitter_search.hash = data.object;
                $rootScope.twitter_search.hash_html = data.html;
            }
            else{
                notify({ message : data.msg, type : 'error'});
            }
            $rootScope.twitter_search.searching = false;
        }, function(){
            notify({ message : "No se pudo obtener el resultado para la búsqueda.", type : 'error'});
            $rootScope.twitter_search.searching = false;
        });
        try{$rootScope.modalHash.close();}catch(e){}
        $rootScope.modalHash = dialogs.create('hashSearch.html', 'modalsDialogCtrl',{},{backdrop: 'static', windowClass:"height-normal", size: "sm"});
    }
    
    /* id de columna en base, id del perfil seleccionado, tipo de col (BOX, SEARCH, PROGRAM, APP, terminos de columna)*/
    $rootScope.addColumn = function(column_id, profile_id, type, terms, tab_id){
        var pos = $scope.getTabPos();
        var tab = tab_id ? tab_id : $scope.tabs[pos].id;
        var columns = $scope.tabs[pos].columns;
        try{$rootScope.modalHash.close();}catch(e){}
        if( columns.length < 10 ){
            if(tab && profile_id && type){
                Tabs.addCol({
                    'tab' : $scope.tabs[pos].id, 
                    'col' : column_id, 
                    'profile' : profile_id,
                    'type'  : type,
                    'terms' : terms
                }).then(function(data){
                    columns.push(data.object);
                    $scope.scrollRight(); 
                    $scope.updateColumn(data.object);
                });
            }
        }
        else{
            notify("Solo puedes agregar un máximo de 10 columnas por pestaña. Intenta crear nuevas pestañas.");
        }
    }
    
    $rootScope.searchSuggest = function(profile){
        if(!angular.isDefined(profile) || !angular.isDefined(profile.search)){
            profile.searching = true;
            Tabs.searchCol(profile.id).then(function(data){
                profile.search = data.object;
                profile.searching = false;
            }, function(){profile.searching = false;});
        }
    }
    
    $rootScope.searchLists = function(profile){
        if(!angular.isDefined(profile) || !angular.isDefined(profile.search)){
            profile.searching = true;
            Tabs.listsCol(profile.id).then(function(data){
                profile.lists = data.object;
                profile.searching = false;
            }, function(){profile.searching = false;});
        }
    }
    
    $rootScope.searchLists = function(profile){
        if(!angular.isDefined(profile) || !angular.isDefined(profile.search)){
            profile.searching = true;
            Tabs.listsCol(profile.id).then(function(data){
                profile.lists = data.object;
                profile.searching = false;
            }, function(){profile.searching = false;});
        }
    }
    
    $rootScope.facebookPagesLikedByUser = function(profile){
        if(!angular.isDefined(profile) || !angular.isDefined(profile.search)){      
            profile.searching = true;
            Tabs.facebookPagesLikedByUser(profile.id).then(function(data){
                profile.pages = data.object;
                profile.searching = false;
            }, function(){});        
         }
    }
    $rootScope.instagramHashTag = function(profile,value){
        if(!angular.isDefined(profile) || !angular.isDefined(profile.search)){      
            profile.searching = true;
            Tabs.instagramHashTag(profile.id,value).then(function(data){
                profile.hashtags = data.object;
                profile.searching = false;
            }, function(){});        
         }
    }
    
    $rootScope.searchListsFolColSubscrib = function(profile_id){
        var profile = $rootScope.getProfile(profile_id);
        if(profile && !angular.isDefined($rootScope.lists_subs[profile_id])){
            Tabs.listsSubscribesCol(profile.id).then(function(data){
                $rootScope.lists_subs[profile_id] = data.object;
            }, function(){});
        }
    }
    
    $scope.scrollRight = function(){
        $timeout(function(){
            var width = angular.element('.tab-pane.active .columns-streams-control').width()-(angular.element('body').width()-51);
            angular.element('.tab-pane.active .tab-colums-stream').animate({
                scrollLeft : width-angular.element('.tab-pane.active .tab-colums-stream-last').width()
            }, 'slow');
        });
    }
    
    $scope.hideShowPanel = function(hide){
        if( hide ){
            angular.element('.tab-pane.active .tab-colums-stream-last .column-item').hide();
            angular.element('.tab-pane.active .tab-column-panel-show').show();
        }
        else{
            angular.element('.tab-pane.active .tab-column-panel-show').hide();
            angular.element('.tab-pane.active .tab-colums-stream-last .column-item').show();
            $scope.scrollRight(); 
        }
    }
    
    $scope.updateOrder = function(){
        var pos = $scope.getTabPos();
        var cols = $scope.tabs[pos].columns;
        var orders = new Array();
        $rootScope.textLoading = 'Guardando espacio de trabajo';
        angular.forEach(cols, function(item, i){
            orders.push({
                'id'    : item.id
            })
        });
        $timeout(function(){
            Tabs.order(orders);
        }, 1000);
    }
    
    $scope.delStream = function(col, msg_id, profile_id, type){
        $timeout(function() {
            if (confirm("¿Está seguro que desea eliminar este mensaje definitivamente de Twitter?")) {
                $rootScope.textLoading = 'Eliminando';
                Tabs.delStream(msg_id, profile_id, type).then(function(data){
                    if( data.success ){
                        angular.forEach(col.data, function(item, i){
                            if(item.id_str == msg_id){
                                col.data.splice(i,1);
                            }
                        });
                    }
                    else{
                        notify({ message : "El mensaje no ha podido eliminar. Por favor inténtelo nuevamente.", type : 'error'});
                    }
                }, function(){
                    notify({ message : "El mensaje no ha podido eliminar. Por favor inténtelo nuevamente.", type : 'error'});
                });
            }
        });
    }
    
/* ------------------------------- METODOS PARA TWITTER  --------------------------------------*/
    
    $scope.favorite = function(prof_id, id){
        $rootScope.textLoading = 'Procesando';
        Twitter.favorite(prof_id, id).then(function(data){
            if( !data.success ){
                notify({ message : data.msg, type : 'error'});
            }
            else{
                notify( data.msg);
            }
        }, function(){
            notify({ message : "Ha ocurrido un error inesperado. Por favor inténtelo nuevamente.", type : 'error'});
        });
    }
    
    $rootScope.follow = function(prof_id, id, follow){
        $rootScope.textLoading = 'Procesando';
        Twitter.follow(prof_id, id, follow).then(function(data){
            if( !data.success ){
                notify({ message : data.msg, type : 'error'});
            }
        }, function(){
            notify({ message : "Ha ocurrido un error inesperado. Por favor inténtelo nuevamente.", type : 'error'});
        });
    }
    
    $rootScope.sendToEmail = function(text){
        window.location.assign("mailto:?Subject=Mail subject&body=" + text);
    }
    
    $rootScope.spammer = function(prof_id, id){
        $rootScope.textLoading = 'Procesando';
        Twitter.spammer(prof_id, id).then(function(data){
            if( !data.success ){
                notify({ message : data.msg, type : 'error'});
            }
        }, function(){
            notify({ message : "Ha ocurrido un error inesperado. Por favor inténtelo nuevamente.", type : 'error'});
        });
    }
    
    $rootScope.getRetuits = function(row, profile_id){
        if( !angular.isDefined( row.retuits_data ) ){
            $rootScope.textLoading = 'Buscando';
            Twitter.getRetuits(row.id_str, profile_id).then(function(data){
                row.retuits_data = data.object;
            }, function(){
                notify({ message : "Ha ocurrido un error inesperado. Por favor inténtelo nuevamente.", type : 'error'});
            });
        }
    }
    
    $rootScope.createList = function(prof){
        $rootScope.textLoading = 'Creando lista';
        prof.new_list.p_id = prof.id;
        prof.new_list.creating = true;
        Twitter.createList(prof.new_list).then(function(data){
            if( !data.success ){
                notify({ message : data.msg, type : 'error'});
            }
            else{
                prof.lists.push(data.object);
            }
            prof.new_list = false;
        }, function(){
            notify({ message : "Ha ocurrido un error inesperado. Por favor inténtelo nuevamente.", type : 'error'});
            prof.new_list = false;
        });
    }
    
    $rootScope.createListMember = function(list_id){
        $rootScope.textLoading = 'Agregando miembro';
        Twitter.createListMember(list_id, $rootScope.profile, $rootScope.list_user.id_str).then(function(data){
            if( !data.success ){
                notify({ message : data.msg, type : 'error'});
            }
        }, function(){
            notify({ message : "Ha ocurrido un error inesperado. Por favor inténtelo nuevamente.", type : 'error'});
        });
    }
    
    $scope.showConversation = function(row, profile){
        if( !row.conversation_shown ){
            row.conversation_loading = true;
            Twitter.showConversation(row.in_reply_to_status_id_str, profile).then(function(data){
                row.conversation_loading = false;
                if( data.success ){
                    row.conversation_shown = true;
                    row.conversation = data.object;
                }
                else{
                    notify({ message : data.msg, type : 'error'});
                }
            }, function(){
                row.conversation_loading = false;
                notify({ message : "Ha ocurrido un error inesperado. Por favor inténtelo nuevamente.", type : 'error'});
            });
        }else{
            row.conversation_shown = false;
        }
    }
    
    $rootScope.addToList = function(user, profile){
        $rootScope.list_loading = true;
        Tabs.listsCol(profile).then(function(data){
            $rootScope.list_loading = false;
            $rootScope.profile = profile;
            $rootScope.list_user = user;
            $rootScope.lists = data.object;
            }, function(){
                $rootScope.list_loading = false;
                notify({ message : "Ha ocurrido un error inesperado. Por favor inténtelo nuevamente.", type : 'error'});
            });
        $scope.dlg = dialogs.create('addToList.html', 'modalsDialogCtrl',{},{backdrop: 'static', windowClass:"height-normal", size: "sm"});
    }
    
    $rootScope.retweet = function(id, profile){
        try{$rootScope.modalUser.close();}catch(e){}
        try{$rootScope.modalHash.close();}catch(e){}
        Twitter.retweet(id,profile).then(function(data){
            if( data.success ){
                notify("Retuit enviado.");
            }else{
                notify({
                    message : data.msg, 
                    type : 'error'
                });
            }
        }, function(){
            $rootScope.list_loading = false;
            notify({
                message : "El estado no ha podido ser retuiteado. Por favor inténtelo nuevamente.", 
                type : 'error'
            });
        });
    }
    
    $rootScope.userFavorites = function(user, profile){
        if( !angular.isDefined($rootScope.user.favorites)){
            Twitter.getFavorites(user.id_str, profile).then(function( data ) {
                if( data.success ){
                    $rootScope.user.favorites = data.object;
                    $rootScope.user.favorites_html = data.html;
                }
                else{
                    notify({ message : data.msg, type : 'error'});
                }
            }, function(){
                notify({ message : "No se pudo obtener la conología del usuario.", type : 'error'});
            });
        }
    }
    
    $rootScope.userCronology = function(user, profile){
        if( !angular.isDefined($rootScope.user.crono)){
            Twitter.getCronology(user.profile.id_str, profile).then(function( data ) {
                if( data.success ){
                    $rootScope.user.crono = data.object;
                    $rootScope.user.crono_html = data.html;
                }
                else{
                    notify({ message : data.msg, type : 'error'});
                }
            }, function(){
                notify({ message : "No se pudo obtener la conología del usuario.", type : 'error'});
            });
        }
    }
    
    $rootScope.userMentions = function(user, profile){
        if( !angular.isDefined($rootScope.user.mentions)){
            Twitter.getMentions(user.profile_screen, profile).then(function( data ) {
                if( data.success ){
                    $rootScope.user.mentions = data.object;
                    $rootScope.user.mentions_html = data.html;
                }
                else{
                    notify({ message : data.msg, type : 'error'});
                }
            }, function(){
                notify({ message : "No se pudo obtener la conología del usuario.", type : 'error'});
            });
        }
    }
/* --------------------------------------- END TWITTER METHODS ------------------------------------- */
    
/* ---------------------------------------- FACEBOOK METHODS  ---------------------------------------*/
    $rootScope.fbAddComment = function(profile, row, modal){
        if( !angular.isDefined(row.comments) ){
            row.comments = {};
            row.comments = new Array();
        }
        var text =  modal ? row.comments.fb_comment_text_md : row.comments.fb_comment_text;
        if( text ){
            if( modal ){
                row.comments.adding_comment_md = true;
            }
            else{
                row.comments.adding_comment = true;
            }
            Facebook.addComment(profile, row.id, text).then(function( data ) {
                row.comments.adding_comment = row.comments.adding_comment_md = false;
                row.comments.fb_writing_comment = row.comments.fb_writing_comment_md = false;
                row.comments.fb_comment_text = row.comments.fb_comment_text_md = "";
                if( data.success ){
                    row.comments.push(data.object);
                }
                else{
                    notify({ message : data.msg, type : 'error'});
                }
            }, function(){
                row.comments.adding_comment = row.comments.adding_comment_md = false;
                row.comments.fb_writing_comment = row.comments.fb_writing_comment_md = false;
                row.comments.fb_comment_text = row.comments.fb_comment_text_md = "";
                notify({ message : "No se pudo agregar el comentario.", type : 'error'});
            });
        }
    }
    
    $rootScope.fbLikes = function(profile, row, on){
        Facebook.likes(profile, row.id, on).then(function( data ) {
            if( data.success ){
                if( !angular.isDefined(row.likes) ){
                    row.likes = {};
                    row.likes = new Array();
                }
                notify(data.msg);
                if( on ){
                    row.likes.push({'id' : row.from.id, 'name' : row.from.name});
                    row.user_likes = true;
                }
                else{
                    angular.forEach(row.likes, function(like, i){
                        if( row.from.id == like.id ){
                            row.likes.splice(i,1);
                            row.user_likes = false;
                            return;
                        }
                    });
                }
                row.like_count = row.likes.length;
            }
            else{
                notify({ message : data.msg, type : 'error'});
            }
        }, function(){
            notify({ message : "Ha ocurrido un error y no se pudo realizar la operación.", type : 'error'});
        });
    }
    
    $rootScope.fbShowMediaPreview = function(row){
        row.source_object = '<object width="100%" height="240" type="application/x-shockwave-flash" \n\
                                    id="item_source_'+row.id+'" name="item_source_'+row.id+'" data="'+row.source+'">\n\
                                    <param name="wmode" value="transparent"><param name="allowFullScreen" value="true">\n\
                            </object>';
    }
    
    $scope.fbLikedIt = function(row){
        if( !angular.isDefined(row.likes) )return false;
        var ret = false;
        angular.forEach(row.likes, function(like){
            if( row.from.id == like.id ){
                ret = true;
                return;
            }
        });
        return ret;
   }
    
   $rootScope.fbShowPhotoPreview = function(row){
        if( row.type == 'photo' ){
            $rootScope.photo_preview = row;
            $rootScope.modalPhotoPrev = dialogs.create('imagePreview.html','modalsDialogCtrl',{},{backdrop: 'static', windowClass:"height-normal"});
        }
    }
    
   $scope.fbShowComments = function(row, profile){
        $rootScope.post_comments = row;
        $rootScope.post_comments.profile = profile;
        $rootScope.modalPostComments = dialogs.create('postComments.html','modalsDialogCtrl',{},{backdrop: 'static', windowClass:"height-normal", size: "sm"});
    }
    
   $rootScope.fbAddGroup = function(group_id){
       angular.element("#fb-group-"+group_id).prop('disabled', true).addClass('disabled');
       Facebook.addGroup(group_id, $rootScope.profile_id_fb_created).then(function( data ) {
           if( !data.success ){
               notify({ message : "No se pudo agregar el grupo.", type : 'error'});
               angular.element("fb-group-"+group_id).removeAttr('disabled').removeClass('disabled');
           }
           else if( data.success && data.profile ){
               $rootScope.profiles.push( data.profile );
           }
        }, function(){});
    }
/* ---------------------------------------- END TWITTER METHODS -------------------------------------- */

/* ---------------------------------------- LINKEDIN METHODS  ---------------------------------------*/
    $rootScope.liLikes = function(profile, row, on){
        Linkedin.likes(profile, row.id, on).then(function( data ) {
            if( data.success ){
                if( !angular.isDefined(row.likes) ){
                    row.likes = {};
                    row.likes.values = new Array();
                }
                notify(data.msg);
                if( on ){
                    var person = new Array();
                    person.push({'firstName' : data.person.firstName, 'lastName' : data.person.lastName, 'id' : data.person.id});
                    row.likes.values.push({'person' : person});
                    row.isLiked = true;
                }
                else{
                    angular.forEach(row.likes.values, function(like, i){
                        if( row.from.id == like.id ){
                            row.likes.data.splice(i,1);
                            row.isLiked = false;
                            return;
                        }
                    });
                }
                row.like_count = row.likes.data.length;
            }
            else{
                notify({ message : data.msg, type : 'error'});
            }
        }, function(){
            notify({ message : "Ha ocurrido un error y no se pudo realizar la operación.", type : 'error'});
        });
    }
    
    $rootScope.liAddComment = function(profile, row, updateComents){
        if( !angular.isDefined(row.comments) ){
            row.comments = {};
            row.comments.data = new Array();
        }
        var text = row.updateComents.li_comment_text;
        if( text ){
            row.comments.adding_comment = true;
            Linkedin.addComment(profile, row.id, text).then(function( data ) {
                row.comments.adding_comment = false;
                row.comments.li_writing_comment = false;
                row.comments.li_comment_text = "";
                if( data.success ){
                    updateComents.values.push(data.object);
                }
                else{
                    notify({ message : data.msg, type : 'error'});
                }
            }, function(){
                row.comments.adding_comment = false;
                row.comments.li_writing_comment = false;
                row.comments.li_comment_text = "";
                notify({ message : "No se pudo agregar el comentario.", type : 'error'});
            });
        }
    }
    
    $rootScope.liUserUpdates = function(id,profile_id, type){
        Linkedin.userUpdates(id, profile_id, type).then(function( data ) {
            if( data.success ){
                $rootScope.user.updates = data.object;
                $rootScope.user.updates_html = data.html;
            }
            else{
                notify({ message : data.msg, type : 'error'});
            }
        }, function(){
            notify({ message : "No se pudo obtener la actualización del usuario.", type : 'error'});
        });
    }
/* ---------------------------------------- END LINKEDIN METHODS -------------------------------------- */

/* ---------------------------------------- INSTAGRAM METHODS  ---------------------------------------*/
    $rootScope.instagramAddComment = function(profile, row, modal){
        if( !angular.isDefined(row.comments) ){
            row.comments = {};
            row.comments = new Array();
        }
        var text =  modal ? row.comments.inst_comment_text_md : row.comments.inst_comment_text;
        if( text ){
            if( modal ){
                row.comments.adding_comment_md = true;
            }
            else{
                row.comments.adding_comment = true;
            }
            Facebook.addComment(profile, row.id, text).then(function( data ) {
                row.comments.adding_comment = row.comments.adding_comment_md = false;
                row.comments.inst_writing_comment = row.comments.inst_writing_comment_md = false;
                row.comments.inst_comment_text = row.comments.inst_comment_text_md = "";
                if( data.success ){
                    row.comments.push(data.object);
                }
                else{
                    notify({ message : data.msg, type : 'error'});
                }
            }, function(){
                row.comments.adding_comment = row.comments.adding_comment_md = false;
                row.comments.inst_writing_comment = row.comments.inst_writing_comment_md = false;
                row.comments.inst_comment_text = row.comments.inst_comment_text_md = "";
                notify({ message : "No se pudo agregar el comentario.", type : 'error'});
            });
        }
    }
    
    $rootScope.instagramLikes = function(profile, row, on){
        Instagram.likes(profile, row.id, on).then(function( data ) {
            if( data.success ){
                if( !angular.isDefined(row.likes) ){
                    row.likes = {};
                    row.likes = new Array();
                }
                notify(data.msg);
                if( on ){
                    row.likes.push({'id' : row.from.id, 'name' : row.from.name});
                    row.user_likes = true;
                }
                else{
                    angular.forEach(row.likes, function(like, i){
                        if( row.from.id == like.id ){
                            row.likes.splice(i,1);
                            row.user_likes = false;
                            return;
                        }
                    });
                }
                row.like_count = row.likes.length;
            }
            else{
                notify({ message : data.msg, type : 'error'});
            }
        }, function(){
            notify({ message : "Ha ocurrido un error y no se pudo realizar la operación.", type : 'error'});
        });
    } 
    
    $rootScope.instagramFollow = function(profile, row, on){
        Instagram.follows(profile, row.id, on).then(function( data ) {
            if( data.success ){
                if( !angular.isDefined(row.follows) ){
                    row.follows = {};
                    row.follows = new Array();
                }
                notify(data.msg);
                if( on ){
                    row.follows.push({'id' : row.from.id, 'name' : row.from.name});
                    row.user_follows = true;
                }
                else{
                    angular.forEach(row.follows, function(follow, i){
                        if( row.from.id == follow.id ){
                            row.follows.splice(i,1);
                            row.user_follows = false;
                            return;
                        }
                    });
                }
                row.follows_count = row.length;
            }
            else{
                notify({ message : data.msg, type : 'error'});
            }
        }, function(){
            notify({ message : "Ha ocurrido un error y no se pudo realizar la operación.", type : 'error'});
        });
    } 
    
    
    $scope.instagramLikedIt = function(row){
        if( !angular.isDefined(row.likes) )return false;
        var ret = false;
        angular.forEach(row.likes.data, function(like){
            if( row.user.id == like.id ){
                ret = true;
                return;
            }
        });
        return ret;
   }  
   
    
   $scope.instagramShowComments = function(row, profile){
        $rootScope.post_comments = row;
        $rootScope.post_comments.profile = profile;
        $rootScope.modalPostComments = dialogs.create('instagramComments.html','modalsDialogCtrl',{},{backdrop: 'static', windowClass:"height-normal", size: "sm"});
    }
    
    $rootScope.userFeed = function(user, profile){
        if( !angular.isDefined($rootScope.user.feed)){
            Instagram.getUserFeed(user.profile.id, profile).then(function( data ) {
                if( data.success ){
                    $rootScope.user.feed = data.object;
                    $rootScope.user.feed_html = data.html;
                }
                else{
                    notify({ message : data.msg, type : 'error'});
                }
            }, function(){
                notify({ message : "No se pudo obtener las actualizaciones del usuario.", type : 'error'});
            });
        }
    }
});

hootControllers.controller('MiembroController', function($scope, Miembro, $rootScope){
    $scope.type='cv';
    Miembro.load().then(function(data){
        $rootScope.miembro = data.object;
    }, function(){});
    $scope.changeView = function(view){
        $scope.type = view;
    }
});

hootControllers.controller('EditorController', function($scope){
    $scope.templates = [ 
        { url: '../../bundles/dashboard/subviews/editor/programados.html'},
        { url: '../../bundles/dashboard/subviews/editor/programados.html' },
        { url: '../../bundles/dashboard/subviews/editor/programados.html' } 
    ];
    $scope.template = $scope.templates[0];
});

hootControllers.controller('ReportesController', function($scope){
    
});

hootControllers.controller('AsignacionesController', function($scope){
    
});

hootControllers.controller('CuentaController', function($scope){
    
});
hootControllers.controller('AjustesController', function($scope, $routeParams){
    $scope.template = $routeParams.template ? $routeParams.template : 'cuenta' ;
});
hootControllers.controller('HerramientasController', function($scope){
    
});
hootControllers.controller('AyudaController', function($scope){
    
});

hootControllers.controller('modalsDialogCtrl',function($scope,$modalInstance,data){
        $scope.cancel = function(){
                $modalInstance.dismiss('Canceled');
        }; // end cancel

        $scope.save = function(){
                $modalInstance.close($scope.user.name);
        }; // end save

        $scope.hitScape = function(evt){
                if(angular.equals(evt.keyCode,27))
                        $modalInstance.dismiss('Canceled');
        };
})

hootControllers.controller('DatetimeController', function($scope){
    $scope.datet = new Date();
    $scope.minDate = new Date();
    $scope.mytime = new Date();
});