(function(){
    'use strict';
    function setupCtrl($rootScope,$scope, $http,$resource, $filter,$compile,$window) {

        $scope.openAuth = function(type, id, btn,created) {

            var left = screen.width / 2 - 200
                , top = screen.height / 2 - 250
                , popup = $window.open(Routing.generate(Routing.generate('connect_' + type, {id: id}, true)), '', "top=" + top + ",left=" + left + ",width=400,height=500")
                , interval = 1000;
            var i = $interval(function () {
                interval += 500;
                try {
                    if (popup.closed) {
                        $interval.cancel(i);
                        popup.close();
                        isAuthorized(id, btn, count,created);
                    }
                } catch (e) {
                }
            }, interval);
            $(btn).addClass('disabled');
        }
    };
    angular.module('CqApp').controller('setupCtrl',setupCtrl);
    setupCtrl.$inject=['$rootScope','$scope', '$http','$resource','$filter','$compile','$window'];
})();

