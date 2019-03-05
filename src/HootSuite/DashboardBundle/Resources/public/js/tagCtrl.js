(function(){
    'use strict';
    function tagCtrl($rootScope,$scope, $http, $tags,$filter,$compile,$window) {
        $scope.tags=[];
        $scope.createTag = function ()
        {
            function tagCreated(tag)
            {
                $rootScope.tags.push({'id':tag.id,'name':tag.name});
                $scope.tagForm.$setPristine();
            }
            function tagNotCreated(errors)
            {}
            if ($scope.tagForm.$valid) {

                $tags.create($scope.tag,null).then(tagCreated, tagNotCreated);
            }
        };
        $scope.editTag = function (id)
        {
            function tagCreated(tag)
            {
                $rootScope.tags = $filter('filter')($rootScope.tags,function(sp) {
                    if(sp.id == tag.id){sp.name=tag.name};
                });
                $scope.tagForm.$setPristine();
            }
            function tagNotCreated(errors)
            {}
            if ($scope.tagForm.$valid) {

                $tags.create($scope.tag,id).then(tagCreated, tagNotCreated);
            }
        };
    };
    angular.module('hootApp').controller('tagCtrl',tagCtrl);
    tagCtrl.$inject=['$rootScope','$scope', '$http','$tags','$filter','$compile','$window'];
})();