(function(){
    'use strict';
    function contactCtrl($rootScope,$scope, $http,$resource, $contacts,$filter,$compile,$window) {

        $scope.sendContact = function ()
        {
            function contactSent(contact)
            {
                $scope.contactForm.$setPristine();
            }
            function contactNotSent(errors)
            {}
            if ($scope.contactForm.$valid) {
                var token = $('#contact_token').val();
                $contacts.send($scope.contact,token).then(contactSent, contactNotSent);
            }
        };
    };
    angular.module('CqApp').controller('contactCtrl',contactCtrl);
    contactCtrl.$inject=['$rootScope','$scope', '$http','$resource','$contacts','$filter','$compile','$window'];
})();

