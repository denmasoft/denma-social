(function(){
    'use strict';
    function tags($http, $q,Upload)
    {
        var tagDefer;
        function create(tag,id)
        {
            var createDefer = $q.defer();
            if (!tag instanceof Tag) {
                createDefer.reject('Not a valid tag');
            } else {
                Upload.upload({
                    url     : Routing.generate('tag_new'),
                    data    : {name: tag.Name,id:id}
                }).then(tagCreateSuccess, tagCreateError);
            }
            function tagCreateSuccess(response)
            {
                var  tag = new Tag(response.data.object);
                createDefer.resolve(tag);
                tagDefer = null;
            }
            function tagCreateError(errors)
            {
                createDefer.reject(errors);
            }
            return createDefer.promise;
        }
        return {
            create: create
        };
    };
    angular.module('hootApp').service('$tags',tags);
    tags.$inject=['$http', '$q','Upload'];
})();


