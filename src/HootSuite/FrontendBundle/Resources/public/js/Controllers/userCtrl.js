(function(){
    'use strict';
    function userCtrl($rootScope,$scope, $http,$resource, $users,$filter,$compile,$window) {

        $scope.createUser = function (plan)
        {
            function userCreated(user)
            {
                $window.location.href=Routing.generate('usuario_setup',{'id':user.user});
                $scope.userForm.$setPristine();
            }
            function userNotCreated(errors)
            {}
            if ($scope.userForm.$valid) {
                var token = $('#user_create_token').val();
                $users.create($scope.user,token,plan).then(userCreated, userNotCreated);
            }
        };
    };
    angular.module('CqApp').controller('userCtrl',userCtrl);
    userCtrl.$inject=['$rootScope','$scope', '$http','$resource','$users','$filter','$compile','$window'];
})();

