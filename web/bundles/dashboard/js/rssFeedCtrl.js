(function(){
    'use strict';
    function rssFeedCtrl($rootScope,$scope, $http, $filter,$compile,$window,dialogs) {

        $scope.addNewRssFeed = function ()
        {
            dialogs.create('RssFeedForm.html','modalsDialogCtrl',{},{backdrop: 'static', windowClass:"height-normal"});
        };
    };
    angular.module('hootApp').controller('rssFeedCtrl',rssFeedCtrl);
    rssFeedCtrl.$inject=['$rootScope','$scope', '$http','$filter','$compile','$window','dialogs'];
})();


