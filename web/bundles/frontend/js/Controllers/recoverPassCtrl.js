(function(){
    'use strict';
    function recoverPassCtrl($rootScope,$scope, $http,$resource, $users,$filter,$compile,$window,vcRecaptchaService) {
        $scope.publicKey= "6LdWnCgTAAAAALA9D1klVoCBQpAiII864plT0lTV";
        $scope.recover = false;
        $scope.showRecover=function(){
            $scope.recover = true;
        };
        $scope.recoverPass = function ()
        {
            function userCreated(user){
                var url = Routing.generate('usuario_new_password',{'id':user.user,'token':user.token});
                $window.location.href=url;
            };
            function userNotCreated(){
                ;
            };
            if(vcRecaptchaService.getResponse() === ""){
                alert("Por favor, resuelva el captcha");
            }
            else
            {
                if ($scope.recoverForm.$valid) {
                    $users.recoverPass($scope.user).then(userCreated, userNotCreated);
                }
            }
        };
        $scope.newPass = function ()
        {
            function userCreated(user){
                $window.location.href=Routing.generate('usuario_login');
            };
            function userNotCreated(){};
            if ($scope.passForm.$valid) {
                var id = $('#user_id').val();
                var token = $('#user_token').val();
                $users.newPass($scope.user,id,token).then(userCreated, userNotCreated);
            }
        };
    };
    angular.module('CqApp').controller('recoverPassCtrl',recoverPassCtrl);
    recoverPassCtrl.$inject=['$rootScope','$scope', '$http','$resource','$users','$filter','$compile','$window','vcRecaptchaService'];
})();

