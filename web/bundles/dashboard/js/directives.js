hootApp.directive('loading', ['$http', '$rootScope' ,function ($http, $rootScope){
    return {
        restrict: 'E',
        replace:true,
        link: function (scope, elm, attrs)
        {
            $rootScope.loading = false;
            $rootScope.textLoading = 'Cargando';
            $rootScope.isLoading = function () {
                return ($http.pendingRequests.length > 0 || $rootScope.loading);
            };
            scope.$watch($rootScope.isLoading, function (v)
            {
                if(v){
                    elm.show();
                }else{
                    elm.hide();
                    $rootScope.textLoading = 'Cargando';
                }
            });
        },
        template: '<div class="cg-notify-message cg-notify-ok" style="top:6px;"><span class="glyphicon glyphicon-refresh-gif-black"></span> [[textLoading]]</div>'
    };
}]);
hootApp.directive('ngEnter', function() {
    return function(scope, element, attrs) {
        element.bind("keydown keypress", function(event) {
            if(event.which === 13) {
                scope.$apply(function(){
                    scope.$eval(attrs.ngEnter, {
                        'event': event
                    });
                });

                event.preventDefault();
            }
        });
    };
});

hootApp.directive('clickOutside', ['$document', function ($document) {
    directiveDefinitionObject = {
        link: {
            pre: function (scope, element, attrs, controller) { },
            post: function (scope, element, attrs, controller) {
                onClick = function (event) {
                    var el = angular.element(event.target);
                    var isChild = element.has(event.target).length > 0;
                    var isSelf  = element[0] == event.target;
                    var isInside = isChild || isSelf || el.attr('no-propage');
                    if (!isInside) {  
                        scope.is_focused_wind = false;
                        scope.model.message_writting = false;
                        scope.$apply(attrs.clickOutside)
                    }
                    else{                       
                        scope.is_focused_wind = true;
                    }                    
                }
                $document.click(onClick)
            }
        }
    }
    return directiveDefinitionObject
}]);

hootApp.directive('reverseGeocode',['$rootScope', function ($rootScope) {
    return {
        restrict: 'E',
        template: '<div class="btn_active"></div>',
        link: function (scope, element, attrs) {
            attrs.$observe("lat",function( lat ) {
                if( lat )
                    getDirection();
                }
            );
            
            function getDirection(){
                var geocoder = new google.maps.Geocoder();
                var latlng = new google.maps.LatLng(attrs.lat, attrs.lng);
                geocoder.geocode({ 'latLng': latlng }, function (results, status) {
                    if (status == google.maps.GeocoderStatus.OK) {
                        if (results[1]) {
                            element.html('<span class="glyphicon glyphicon-calendar"></span> '+results[1].formatted_address);
                            scope.model.extra_ubicacion = true;
                        } else {
                            element.text('Location not found');
                        }
                    } else {
                        element.text('Geocoder failed due to: ' + status);
                    }
                    $rootScope.loading = false;
                });
            }
        },
        replace: true
    }
}]);

hootApp.directive('autoFocus', function ($timeout) {
    return {
        restrict: 'A',
        link    : function(scope, element, attrs){
            scope.$watch(attrs.ngShow, function(value){
                if( value ){
                    $timeout(function(){
                        element.focus();
                    }, 100);
                }
            })
        }
    }
});

hootApp.directive('noPropage', function () {
    return {
        restrict: 'A',
        link: function (scope, element) {
            element.bind('click', function (event) {
                event.stopPropagation();
            });
        }
    };
});

hootApp.directive('selectProfile', function ($timeout, $rootScope) {
    return {
        restrict: 'E',
        replace : true,
        scope   : {
            profiles    : "=",
            redes       : "="
        },
        link    : function(scope, element){
            $timeout(function(){
                element.on("click", ".btn-create-list", function () {
                    var profile = scope.prof_selected;
                    if( profile.new_list.name && profile.new_list.mode ){
                        $rootScope.createList(profile);
                    }
                });
                element.on("keyup", ".input-hashtag", function () {
                    var profile = scope.prof_selected;
                    var value = angular.element('.input-hashtag').val();                    
                    if( value ){                       
                        $rootScope.instagramHashTag(profile,value);
                    }
                });
                element.on("click", ".gly-add-col-search", function () {
                    var profile = scope.prof_selected;
                    var value = angular.element(this).attr('value');
                    var type  = angular.element('#li-search-types').val();
                    if( value ){
                        scope.show_search_panel = false;
                        var terms = {'q' : value, 'type' : type};
                        $rootScope.addColumn(scope.search_panel.col, profile.id, 'SEARCH', terms);
                    }
                });
                element.on("click", ".gly-add-col-search1", function () {
                    var profile = scope.prof_selected;
                    var terms = {'q' : angular.element(this).attr('terms')};
                    $rootScope.addColumn(scope.search_panel.col, profile.id, 'SEARCH', terms);
                    scope.show_search_panel = false;
                });
                element.on("click", ".gly-add-col-hashtag", function () {
                    var profile = scope.prof_selected;
                    var terms = {'q' : angular.element(this).attr('terms')};
                    $rootScope.addColumn(scope.search_panel.col, profile.id, 'HASHTAG', terms);
                    scope.show_hashtag_panel = false;
                });
                element.on("click", ".gly-add-col-pages1", function () {
                    var profile = scope.prof_selected;
                    var page = angular.element(this).attr('page');
                    var terms = {'q' : angular.element(this).attr('terms'),'page':page};
                    $rootScope.addColumn(scope.search_panel.col, profile.id, 'PAGE', terms);
                    scope.show_pages_panel = false;
                });
                element.on("click", ".gly-add-col-list", function () {
                    var profile = scope.prof_selected;
                    var terms = {'list_id' : angular.element(this).attr('terms')};
                    $rootScope.addColumn(scope.search_panel.col, profile.id, 'LIST', terms);
                    scope.show_list_panel = false;
                });
                element.on("click", ".select-profile-column-btn", function () {
                    scope.prof_selected = $rootScope.getProfile(angular.element(this).attr('prof'));
              
                });
                element.on("click", ".red-option-search", function () {
                    var profile = scope.prof_selected;
                    scope.show_search_panel = true;
                    scope.search_panel = {};
                    scope.search_panel.col = angular.element(this).attr('col');
                    if(angular.element(this).attr('red') == 'TWITTER'){
                        $rootScope.searchSuggest(profile);
                    }
                });
                 element.on("click", ".red-option-pages", function () {
                    var profile = scope.prof_selected;                    
                    scope.show_pages_panel = true;
                    scope.search_panel = {};
                    scope.search_panel.col = angular.element(this).attr('col'); 
                    $rootScope.facebookPagesLikedByUser(profile);
                });
                element.on("click", ".red-option-hashtag", function () {                                      
                    scope.show_hashtag_panel = true;
                    scope.search_panel = {};
                    scope.search_panel.col = angular.element(this).attr('col');                    
                });
                element.on("click", ".red-option-list", function () {
                    var profile = scope.prof_selected;
                    scope.show_list_panel = true;
                    scope.search_panel = {};
                    scope.search_panel.col = angular.element(this).attr('col');
                    $rootScope.searchLists(profile);
                });
                element.on("click", ".box-search-title .close-search", function () {
                    scope.show_search_panel = false;
                });
                element.on("click", ".box-list-title .close-search", function () {
                    var profile = scope.prof_selected;
                    profile.new_list = false;
                    scope.show_list_panel = false;
                });
                element.on("click", ".box-pages-title .close-pages", function () {                    
                    scope.show_pages_panel = false;
                });
                element.on("click", ".box-hashtag-title .close-hashtag", function () {
                    scope.show_hashtag_panel = false;
                });
                element.on("click", ".red-option-clickable", function() {
                    var profile = scope.prof_selected;
                    $rootScope.addColumn(angular.element(this).attr('col'), profile.id, angular.element(this).attr('typ'));
                });
            });
        },
        templateUrl     : '../../bundles/dashboard/templates/profileSelectionTemplate.html'
    };
});

hootApp.directive('draggable', function ($document) {
    "use strict";
    return function (scope, element) {
        var dragging = false;
        var x, y, Ox, Oy, current;
        var dialog = element.find('.modal-dialog');
        dialog.css({
            left    : ((document.body.offsetWidth-dialog.width())/2)+'px',
            top     : '100px'
        });
        
        element.find('.drag-handle').css({
            cursor: 'move'
        });
        element.on('mousedown', function (ev) {
            var target = ev.target;
            if( $(target).hasClass('drag-handle')){
                ev.preventDefault();
                current = target.parentNode;
                dragging = true;
                x = ev.clientX;
                y = ev.clientY;
                Ox = angular.element(current).offset().left;
                Oy = angular.element(current).offset().top;
                $document.on('mousemove', mousemove);
                $document.on('mouseup', mouseup);
            }
        });

        function mousemove(ev) {
            if (dragging === true) {
                var Sx = parseFloat(ev.clientX) - x + Ox;
                var Sy = parseFloat(ev.clientY) - y + Oy;
                var xFinal = Math.min(Math.max(Sx, Math.min(document.body.offsetWidth - Sx, 0)), document.body.offsetWidth - current.offsetWidth) + "px";
                var yFinal = Math.min(Math.max(Sy, Math.min(document.body.offsetHeight - Sy, 0)), document.body.offsetHeight - current.offsetHeight) + "px";
                dialog.css({top: yFinal,left: xFinal});
            }
        }

        function mouseup() {
            $document.unbind('mousemove', mousemove);
            $document.unbind('mouseup', mouseup);
            dragging = false;
        }
    };
});

hootApp.directive( 'compileData', function ( $compile ) {
  return {
    link: function ( scope, element, attrs ) {
      var elmnt;
      attrs.$observe( 'template', function ( myTemplate ) {
        if ( angular.isDefined( myTemplate ) ) {
          // compile the provided template against the current scope
          elmnt = $compile( myTemplate )( scope );
          element.html(""); // dummy "clear"
          element.append( elmnt );
        }
      });
    }
  };
});

hootApp.directive("imagen", function () {
    return {
        restrict: 'E',
        scope: {
            path: '=src'
        },
        template: '<img src="[[path]]" style="width:24px;" />'
    };
});

hootApp.directive("imagenFb", function () {
    return {
        restrict: 'E',
        scope: {
            path: '=src'
        },
        template: '<img src="https://graph.facebook.com/[[path]]/picture?type=square" style="width:24px;" />'
    };
});

hootApp.directive("compile", function ($compile) {
    return {
        restrict: 'A',
        link    : function(scope, el, attrs){
            $compile(el)(scope);
        }
    };
});

hootApp.directive('customPopover', function ($compile, $timeout) {
    return {
        restrict: 'A',
        link: function (scope, el, attrs) {
            angular.element('html').on('click', '.popover-close', function (e) {
                $(el).popover('hide');
            });
            var content = $compile(attrs.popoverHtml)(scope);
            $(el).popover({
                trigger: 'focus',
                html: true,
                container  : 'body',
                content: content,
                title: attrs.popoverTitle,
                placement: attrs.popoverPlacement
            });
        }
    };
});

hootApp.directive('profileSelector', function ($compile, $timeout,Profiles) {
    return {
        controller: 'profileSelectorCtrl',
        restrict: 'E',
        scope: {
            newProfile:'&'
        },
        transclude: true,
        replace: true,
        template:'<div class="profileSelectorWidget newProfileSelector nopin">'+
                    '<div class="profileSelectorWrapper view-list">'+
                        '<div class="selectedList">'+
                            '<div class="">'+
                                '<span class="filterItem">'+
                                    '<em class="ept defaultText pht" style="display: inline;">Filter by profile…</em>'+
                                    '<span class="icon-19 search"></span>'+
                                    '<input class="fpi clean pht" value="" placeholder="Find profile…" style="display: none;" type="text">'+
                                '</span>'+
                            '</div>'+
                            '<div class="tools _tools">'+
                                '<span class="inputBtn selectedCount">0</span>'+
                                    '<span class="inputBtn" style="margin-right: -27px;">'+
                                    '<span class="icon-19 _close"></span>'+
                                '</span>'+
                                '<span class="inputBtn">'+
                                    '<span class="icon-btn icon-19 moreMenu"></span>'+
                                '</span>'+
                            '</div>'+
                        '</div>'+
                    '<div class="picker noShadow" style="display: none;">'+
                        '<div class="snList _itemList">'+
                            '<div class="list-scroll stretched">'+
                                '<div ng-repeat="profile in profiles | filter:name" class="listItem" itemid="[[profile.id]]" type="[[profile.red]]">'+
                                    '<div class="controls">'+
                                        '<a class="icon-19 fave " data-tooltip-selected="Unfavorite" data-tooltip-default="Move to top"></a>'+
                                        '<a class="icon-19 pin " data-tooltip-selected="Unpin" data-tooltip-default="Always select"></a>'+
                                    '</div>'+
                                    '<div class="details" title="[[profile.name]]">'+
                                        '<img class="avatar" src="[[profile.avatar]]">'+
                                        '<strong>[[profile.name]]</strong>'+
                                    '</div>'+
                                    '<span class="icon-static-19 checkmark"></span>'+
                                '</div>'+
                                '<div class="listItem addSocialNetwork" title="Add social network">'+
                                    '<div class="details">'+
                                        '<span class="addIcon"><span class="icon-13 plus"></span></span><strong class="link" ng-click="newProfile()">Add social network</strong>'+
                                    '</div>'+
                                '</div>'+
                            '</div>'+
                        '</div>'+
                    '</div>'+
            '</div>'+
                '</div>',
        link: function (scope, el, attrs) {
            Profiles.load().then(function(data){
                scope.profiles = data.object;
            });
            $(el).on('mouseover',function(e){
                $('.ept').hide();
                $('.fpi').show();
                $('.picker').show();
            });
            $(el).on('mouseleave',function(){
                $('.ept').show();
                $('.fpi').hide();
                $('.picker').hide();
            });
        }
    };
});
hootApp.directive('export', function ($compile, $timeout) {
    return {
        restrict: 'A',
        link: function (scope, el, attrs) {
            $(el).on('change',function(e){
                $('._export').toggleClass('disabled mute');
            });
        }
    };
});

hootApp.directive('twitterOverviewChart', ['$http', '$rootScope' ,function ($http, $rootScope){
    var datum = function(){
        return {
            sinAndCos:function(){
                var lineData = [];
                var y=0;
                var days=1*60*60*1000*24;
                var start_date = new Date("2017-04-01")-days;
                for (var i = 0; i < 31; i++) {
                    days=i*60*60*1000*24;
                    lineData.push({x: new Date(start_date + days), y: y});
                    y=y+Math.floor((Math.random()*10)-3);
                }
                return [
                    {
                        values: lineData,
                        key: 'Dennis_margelys',
                        color: '#ff7f0e'
                    }
                ];
            }
        }
    }();
    return {
        restrict: 'E',
        template: "<div id='chart1'></div>",
        replace:true,
        link: function (scope, elm, attrs)
        {
            var chart;
            var data;
            nv.addGraph(function() {
                chart = nv.models.lineChart()
                    .options({
                        duration: 300,
                        useInteractiveGuideline: true
                    });
                chart.xAxis
                    .axisLabel("")
                    .tickFormat(function(d) { return d3.time.format('%b %d')(new Date(d)); })
                    .staggerLabels(true);

                chart.yAxis
                    .axisLabel('')
                    .tickFormat(d3.format('d'));
                data = datum.sinAndCos();
                d3.select('#chart1').append('svg')
                    .datum(data)
                    .call(chart);
                nv.utils.windowResize(chart.update);
                return chart;
            });
        },
    };
}]);
