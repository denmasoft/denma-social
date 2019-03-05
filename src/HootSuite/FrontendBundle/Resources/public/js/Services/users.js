(function(){
    'use strict';
    function users($http, $q)
    {
        var usersDefer;
        var recoverPassDefer;
        function create(user, token,plan)
        {
            var createDefer = $q.defer();
            if (!user instanceof User) {
                createDefer.reject('Not a valid user');
            } else {
                $http({
                    method: 'POST',
                    url: Routing.generate('usuario_new',{'plan':plan}),
                    data: $.extend({ _token: token }, user),
                    transformRequest: function(obj) {
                        var str = [];
                        for(var p in obj)
                            str.push(encodeURIComponent(p) + "=" + encodeURIComponent(obj[p]));
                        return str.join("&");
                    },
                    headers: {'Content-Type': 'application/x-www-form-urlencoded'}
                }).success(userCreateSuccess).error(userCreateError);
            }
            function userCreateSuccess(response)
            {
                var  user = new User(response);
                createDefer.resolve(user);
                usersDefer = null;
            }
            function userCreateError(errors)
            {
                createDefer.reject(errors);
            }
            return createDefer.promise;
        }
        function recoverPass(user)
        {
            var recoverDefer = $q.defer();
            if (!user instanceof User) {
                recoverDefer.reject('Not a valid user');
            } else {
                $http({
                    method: 'POST',
                    url: Routing.generate('usuario_recover_pass'),
                    data: $.extend(user),
                    transformRequest: function(obj) {
                        var str = [];
                        for(var p in obj)
                            str.push(encodeURIComponent(p) + "=" + encodeURIComponent(obj[p]));
                        return str.join("&");
                    },
                    headers: {'Content-Type': 'application/x-www-form-urlencoded'}
                }).success(recoverPassSuccess).error(recoverPassError);
            }
            function recoverPassSuccess(response)
            {
                var  user = new User(response);
                recoverDefer.resolve(user);
                recoverPassDefer = null;
            }
            function recoverPassError(errors)
            {
                recoverDefer.reject(errors);
            }
            return recoverDefer.promise;
        }
        function newPass(user,id,token)
        {
            var passDefer = $q.defer();
            if (!user instanceof User) {
                passDefer.reject('Not a valid user');
            } else {
                $http({
                    method: 'POST',
                    url: Routing.generate('usuario_create_new_password'),
                    data: $.extend({'user':id,'token':token},user),
                    transformRequest: function(obj) {
                        var str = [];
                        for(var p in obj)
                            str.push(encodeURIComponent(p) + "=" + encodeURIComponent(obj[p]));
                        return str.join("&");
                    },
                    headers: {'Content-Type': 'application/x-www-form-urlencoded'}
                }).success(newPassSuccess).error(newPassError);
            }
            function newPassSuccess(response)
            {
                var  user = new User(response);
                passDefer.resolve(user);
                usersDefer = null;
            }
            function newPassError(errors)
            {
                passDefer.reject(errors);
            }
            return passDefer.promise;
        }
        return {
            create: create,
            recoverPass: recoverPass,
            newPass: newPass
        };
    };
    angular.module('CqApp').service('$users',users);
    users.$inject=['$http', '$q'];
})();

