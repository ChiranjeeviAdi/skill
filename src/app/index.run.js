(function() {
    'use strict';
    function getBaseURL () {
       var url=location.protocol + "//" + location.hostname + (location.port && ":" + location.port) + "/";
       var pathArray = window.location.pathname.split( '/' );
       url=url+pathArray[1];
       var mainUrl;
       if(url.search('http://localhost:3000/')>=0){
        mainUrl=url.replace('http://localhost:3000/','http://localhost/smartskilladmin2');
       }
       else{
        if(url.search('192.168.86.220')>=0){
            mainUrl='192.168.86.220/smartskilladmin2';
        } 
        mainUrl=url;
       }
       return mainUrl;
    }
    angular
        .module('smartskillApp')
        .run(runBlock)
        .controller('DropdownController', DropdownController)
        .factory('authHttpResponseInterceptor',function($q,$location, $rootScope){
            return {
                response: function(response){
                    $rootScope.offline = false;
                    if (response.status === 401) {
                           $location.path('/login');
                       
                    }
                    return response || $q.when(response);
                },
                responseError: function(rejection) {
                    if (rejection.status === 401) {
                           $location.path('/login');
                        
                    }
                    return $q.reject(rejection);
                }
            };
        })
        .filter('myDateTimeFormatUserReadable', function myDateFormat($filter){
          return function(text){
            var  tempdate= new Date(text.replace(/-/g,'/'));
            return $filter('date')(tempdate, "dd-MMM-yyyy hh:mm a");
          };
        })  
        .config(function($httpProvider) {
              //Http Intercpetor to check auth failures for xhr requests
             
              $httpProvider.interceptors.push('authHttpResponseInterceptor');
        });

    /** @ngInject */
    function runBlock($log,$rootScope,$cookies,$http) {

        $rootScope.validateOptions={
            errorElement: 'em',
            errorClass: 'invalid',
            highlight: function(element, errorClass, validClass) {
                $(element).addClass(errorClass).removeClass(validClass);
                $(element).parent().addClass('state-error').removeClass('state-success');

            },
            unhighlight: function(element, errorClass, validClass) {
                $(element).removeClass(errorClass).addClass(validClass);
                $(element).parent().removeClass('state-error').addClass('state-success');
            },
            errorPlacement : function(error, element) {
                error.insertAfter(element.parent());
            }
        };
        $log.debug('runBlock end');
        /*$rootScope.BASE_URL='http://192.168.86.220/SmartskillAdmin2/';
        
        $rootScope.SITE_URL=$rootScope.BASE_URL+'index.php/';*/
        $rootScope.URL=getBaseURL();
        console.log($rootScope.BASE_URL);
        $rootScope.BASE_URL = $rootScope.URL+"/";
        $rootScope.SITE_URL=$rootScope.BASE_URL+'index.php/';


        if(angular.isDefined($cookies.get('idA'))){
          $http.defaults.headers.common['X-Id-Admin'] = $cookies.get('idA');
        }
        if(angular.isDefined($cookies.get('csrf_token'))){
          $http.defaults.headers.common['X-CSRF-TOKEN'] = $cookies.get('csrf_token');
        }

       
    }

    function DropdownController() {
    var vm = this;

    vm.isCollapsed = true;
    vm.status = {
      isopen: false
    };
  }	
})();