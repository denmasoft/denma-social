(function(){
    'use strict';
    function ngCompare() {
        return {
            require: 'ngModel',
            link: function (scope, currentEl, attrs, ctrl) {
                var comparefield = document.getElementsByName(attrs.ngCompare)[0];
                var compareEl = angular.element(comparefield);
                currentEl.on('keyup', function () {
                    if (compareEl.val() !== "") {
                        var isMatch = currentEl.val() === compareEl.val();
                        ctrl.$setValidity('compare', isMatch);
                        scope.$digest();
                    }
                });
                compareEl.on('keyup', function () {
                    if (currentEl.val() !== "") {
                        var isMatch = currentEl.val() === compareEl.val();
                        ctrl.$setValidity('compare', isMatch);
                        scope.$digest();
                    }
                });
            }
        };
    };
    angular.module('CqApp').directive('ngCompare',ngCompare);
    ngCompare.$inject=[];
})();

