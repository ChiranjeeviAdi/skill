(function() {
    'use strict';

    angular
        .module('smartskillApp')
        .controller('LogoutController', LogoutController);

    /** @ngInject */
    function LogoutController($log,$rootScope,$http,$cookies,$location) {
    	$log.info('logout called');

    	 $cookies.remove('idA');
         $cookies.remove('name');
         $cookies.remove('email');
         $cookies.remove('csrf_token');
        $http.defaults.headers.common['X-CSRF-TOKEN'] ="";
    	$http.defaults.headers.common['X-Id-Admin'] ="";
    	$location.path('login');
    }

})();