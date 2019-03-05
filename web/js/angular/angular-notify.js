angular.module('cgNotify', []).factory('notify',['$timeout','$http','$compile','$templateCache','$rootScope',
	function($timeout,$http,$compile,$templateCache,$rootScope){

		var startTop = 10;
		var verticalSpacing = 5;
		var duration = 5000;
		var defaultTemplate = 'angular-notify.html';
		var position = 'right';
		var type     = 'ok';
		var container = document.body;

		var messageElements = [];

		var notify = function(args){

			if (typeof args !== 'object'){
				args = {message:args};
			}

			args.template = args.template ? args.template : defaultTemplate;
			args.position = args.position ? args.position : position;
			args.type     = args.type ? args.type : type;
			args.container = args.container ? args.container : container;
                        args.message = '<div>'+args.message+'</div>';

                        var scope = args.scope ? args.scope.$new() : $rootScope.$new();
                        scope.$cssClass= args.type;

			$http.get(args.template,{cache: $templateCache}).success(function(template){

				var templateElement = $compile(template)(scope);
                                var content = $compile(args.message)(scope);
                                templateElement.append(content);
				templateElement.bind('webkitTransitionEnd oTransitionEnd otransitionend transitionend msTransitionEnd', function(e){
					if (e.propertyName === 'opacity' ||
						(e.originalEvent && e.originalEvent.propertyName === 'opacity')){

						templateElement.remove();
						messageElements.splice(messageElements.indexOf(templateElement),1);
						layoutMessages();
					}
				});

				angular.element(args.container).append(templateElement);
				messageElements.push(templateElement);

				if (args.position === 'center'){
					$timeout(function(){
						templateElement.css('margin-left','-' + (templateElement[0].offsetWidth /2) + 'px');
					});
				}

				scope.$close = function(){
					templateElement.css('opacity',0);
				};

				var layoutMessages = function(){
					var j = 0;
					var currentY = startTop;
					for(var i = messageElements.length - 1; i >= 0; i --){
						var element = messageElements[i];
						var top = currentY;
						currentY += messageElements[i][0].offsetHeight + verticalSpacing;
						element.css('top',top + 'px');
						j ++;
					}
				};

				$timeout(function(){
					layoutMessages();
				});

				if (duration > 0){
					$timeout(function(){
						scope.$close();
					},duration);
				}

			}).error(function(data){
					throw new Error('Template specified for cgNotify ('+args.template+') could not be loaded. ' + data);
			});

            var retVal = {};
            
            retVal.close = function(){
                if (scope.$close){
                    scope.$close();
                }
            };

            Object.defineProperty(retVal,'message',{
                get: function(){
                    return scope.$message;
                },
                set: function(val){
                    scope.$message = val;
                }
            });

            return retVal;

		};

		notify.config = function(args){
			startTop = !angular.isUndefined(args.top) ? args.top : startTop;
			verticalSpacing = !angular.isUndefined(args.verticalSpacing) ? args.verticalSpacing : verticalSpacing;
			duration = !angular.isUndefined(args.duration) ? args.duration : duration;
			defaultTemplate = args.template ? args.template : defaultTemplate;
			position = !angular.isUndefined(args.position) ? args.position : position;
			container = args.container ? args.container : container;
			type = !angular.isUndefined(args.type) ? args.type : type;
		};

		notify.closeAll = function(){
			for(var i = messageElements.length - 1; i >= 0; i --){
				var element = messageElements[i];
				element.css('opacity',0);
			}
		};

		return notify;
	}
]);

angular.module('cgNotify').run(['$templateCache', function($templateCache) {
//$templateCache.put('angular-notify.html',
//    "<div  ng-click=\"$close()\" class=\"cg-notify-message\">\n" +
//    "\t[[$message]]\n" +
//    "</div>"
//  );
  $templateCache.put('angular-notify.html',
    "<div class=\"cg-notify-message cg-notify-[[$cssClass]]\"></div>"
  );

}]);
