(function(){
    'use strict';
    function organizations($http, $q,Upload)
    {
        var organizationDefer;
        function create(organization)
        {
            var createDefer = $q.defer();
            if (!organization instanceof Organization) {
                createDefer.reject('Not a valid organization');
            } else {
                Upload.upload({
                    url     : Routing.generate('organization_new'),
                    data    : {file: organization.Photo,name: organization.Name}
                }).then(organizationCreateSuccess, organizationCreateError);
            }
            function organizationCreateSuccess(response)
            {
                var  organization = new Organization(response);
                createDefer.resolve(organization);
                organizationDefer = null;
            }
            function organizationCreateError(errors)
            {
                createDefer.reject(errors);
            }
            return createDefer.promise;
        }
        return {
            create: create
        };
    };
    angular.module('hootApp').service('$organizations',organizations);
    organizations.$inject=['$http', '$q','Upload'];
})();


