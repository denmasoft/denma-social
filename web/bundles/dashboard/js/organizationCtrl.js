(function(){
    'use strict';
    function organizationCtrl($rootScope,$scope, $http, $organizations,$filter,$compile,$window) {

        $scope.createOrganization = function ()
        {
            function organizationCreated(user)
            {
                $rootScope.miembro.organizations.push(user.data.data);
                //$window.location.href=Routing.generate('usuario_setup',{'id':user.user});
                $scope.organizationForm.$setPristine();
                
            }
            function organizationNotCreated(errors)
            {}
            if ($scope.organizationForm.$valid) {

                $organizations.create($scope.organization).then(organizationCreated, organizationNotCreated);
            }
        };
    };
    angular.module('hootApp').controller('organizationCtrl',organizationCtrl);
    organizationCtrl.$inject=['$rootScope','$scope', '$http','$organizations','$filter','$compile','$window'];
})();


