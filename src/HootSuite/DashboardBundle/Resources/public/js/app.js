var hootApp = angular.module('hootApp', 
    [
        'ngRoute',
        'hootControllers',
        'hootServices',
        'ui.bootstrap',
        'dialogs.main',
        'pascalprecht.translate',
        'ui.sortable',
        'labs.infiniteScroll'
    ]
);
hootApp.filter('toDate', function() {
    return function (input) {
        return new Date(input);
    }
});
hootApp.filter('instagramDate', function() {
    return function (input) {
        return new Date(input*1000);
    }
});
hootApp.filter('facebookDate',function(){
    return function(input){
         var arr = input.split(/-|\s|:/);
        var date = new Date(arr[0], arr[1] -1, arr[2], arr[3], arr[4], arr[5]);
        return date;
    }
});
hootApp.filter('userId',function(){
    return function(input){
        var arr = input.split('_');
        return arr[0];
    }
});
hootApp.months = new Array('Enero','Febrero','Marzo','Abril','Mayo','Junio','Julio','Agosto','Septiembre','Octubre','Noviembre','Diciembre');

hootApp.filter('unsafe', function($sce) {
    return function(val) {
        return $sce.trustAsHtml(val);
    };
});
hootApp.filter('replace', function() {
    return function(input) {
        return input.replace("_normal","");
    }
});
hootApp.filter('userNameLi', function() {
    return function(object) {
        return object.firstName+' '+object.lastName;
    }
});
hootApp.filter('monthName', function() {
    return function(i) {
        return hootApp.months[i-1];
    }
});

hootApp.filter('linkify', function() {
    return function(input) {
        if( angular.isDefined(input) ){
            // http://, https://, ftp://
            var urlPattern = /\b(?:https?|ftp):\/\/[a-z0-9-+&@#\/%?=~_|!:,.;]*[a-z0-9-+&@#\/%=~_|]/gim;

            // www. sans http:// or https://
            var pseudoUrlPattern = /(^|[^\/])(www\.[\S]+(\b|$))/gim;

            // Email addresses
            var emailAddressPattern = /\w+@[a-zA-Z_]+?(?:\.[a-zA-Z]{2,6})+/gim;

            return input
                .replace(urlPattern, '<a class="linkify" target="_blank" href="$&">$&</a>')
                .replace(pseudoUrlPattern, '$1<a class="linkify" target="_blank" href="http://$2">$2</a>')
                .replace(emailAddressPattern, '<a class="linkify" target="_blank" href="mailto:$&">$&</a>');
        }
    }
});

hootApp.filter('truncate', function() {
    return function(input, ln) {
        if( input.length > ln ){
            return input.substr(0,ln)+'...';
        }
        else{
            return input;
        }
    }
});

hootApp.filter('linkusers', function() {
    return function(input, profile) {
        if( angular.isDefined(input) ){
            // http://, https://, ftp://
            var usersPattern = /[@]+[A-Za-z0-9-_]+/;
            var hashPattern = /[#]+[A-Za-z0-9-_]+/;
            return input
                .replace(usersPattern, function(u){
                    var username = u.replace("@","");
                    return '<a class="linkify" href="javascript:;" ng-click="userProfile(\''+username+'\','+profile+')">'+u+'</a>';
                })
                .replace(hashPattern, function(t) {
                    return '<a class="linkify" href="javascript:;" ng-click="hashSearch('+profile+', \''+t+'\')">'+t+'</a>';
                })
        }
    }
});

hootApp.config(function($interpolateProvider, $routeProvider, $httpProvider){
    $httpProvider.defaults.headers.post['Content-Type'] = 'application/x-www-form-urlencoded;charset=utf-8';
    /**
   * The workhorse; converts an object to x-www-form-urlencoded serialization.
   * @param {Object} obj
   * @return {String}
   */ 
    var param = function(obj) {
        var query = '', name, value, fullSubName, subName, subValue, innerObj, i;

        for(name in obj) {
            value = obj[name];

            if(value instanceof Array) {
                for(i=0; i<value.length; ++i) {
                    subValue = value[i];
                    fullSubName = name + '[' + i + ']';
                    innerObj = {};
                    innerObj[fullSubName] = subValue;
                    query += param(innerObj) + '&';
                }
            }
            else if(value instanceof Object) {
                for(subName in value) {
                    subValue = value[subName];
                    fullSubName = name + '[' + subName + ']';
                    innerObj = {};
                    innerObj[fullSubName] = subValue;
                    query += param(innerObj) + '&';
                }
            }
            else if(value !== undefined && value !== null)
                query += encodeURIComponent(name) + '=' + encodeURIComponent(value) + '&';
        }

        return query.length ? query.substr(0, query.length - 1) : query;
    };

    // Override $http service's default transformRequest
    $httpProvider.defaults.transformRequest = [function(data) {
        return angular.isObject(data) && String(data) !== '[object File]' ? param(data) : data;
    }];

    $interpolateProvider.startSymbol('[[');
    $interpolateProvider.endSymbol(']]');
    $routeProvider
        .when('/miembro',
        {
            controller  : 'MiembroController',
            templateUrl : '../../bundles/dashboard/subviews/miembro.html'
        })
        .when('/',
        {
            controller  : 'WorkSpaceController',
            templateUrl : '../../bundles/dashboard/subviews/workspace.html'
        })
        .when('/editor',
        {
            controller  : 'EditorController',
            templateUrl : '../../bundles/dashboard/subviews/editor/index.html'
        })
        .when('/reportes',
        {
            controller  : 'ReportesController',
            templateUrl : '../../bundles/dashboard/subviews/analitica/index.html'
        })
        .when('/asignacion',
        {
            controller  : 'AsignacionesController',
            templateUrl : '../../bundles/dashboard/subviews/asignaciones.html'
        })
        .when('/mi-cuenta',
        {
            controller  : 'CuentaController',
            templateUrl : '../../bundles/dashboard/subviews/cuenta.html'
        })
        .when('/ajustes/:template',
        {
            controller  : 'AjustesController',
            templateUrl : '../../bundles/dashboard/subviews/ajustes/index.html'
        })
        .when('/herramientas',
        {
            controller  : 'HerramientasController',
            templateUrl : '../../bundles/dashboard/subviews/herramientas.html'
        })
        .when('/ayuda',
        {
            controller  : 'AyudaController',
            templateUrl : '../../bundles/dashboard/subviews/ayuda.html'
        })
});