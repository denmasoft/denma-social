(function(){
    'use strict';
    function contacts($http, $q)
    {
        var contactsDefer;
        function send(contact, token)
        {
            var createDefer = $q.defer();
            if (!contact instanceof Contact) {
                createDefer.reject('Not a valid contact');
            } else {
                $http({
                    method: 'POST',
                    url: Routing.generate('send_contact'),
                    data: $.extend({ _token: token }, user),
                    transformRequest: function(obj) {
                        var str = [];
                        for(var p in obj)
                            str.push(encodeURIComponent(p) + "=" + encodeURIComponent(obj[p]));
                        return str.join("&");
                    },
                    headers: {'Content-Type': 'application/x-www-form-urlencoded'}
                }).success(contactSentSuccess).error(contactNotSentError);
            }
            function contactSentSuccess(response)
            {
                var  contact = new Contact(response);
                createDefer.resolve(contact);
                contactsDefer = null;
            }
            function contactNotSentError(errors)
            {
                createDefer.reject(errors);
            }
            return createDefer.promise;
        }

        return {
            send: send
        };
    };
    angular.module('CqApp').service('$contacts',contacts);
    contacts.$inject=['$http', '$q'];
})();

