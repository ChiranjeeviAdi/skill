(function() {
    'use strict';
    
    function getRandomString(strlength) {
            var text = "";
            var possible = "ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789";

            for( var i=0; i < strlength; i++ ){
                
                text += possible.charAt(Math.floor(Math.random() * possible.length));
            }

            return text;
    }
    angular
        .module('smartskillApp')
        .controller('LoginController', LoginController);

    /** @ngInject */
    function LoginController($log,$rootScope,$http,$cookies,$location) {
        $log.info('login loaded');
        var login= this;
        login.admin={};
        var randomString =getRandomString(10);
        var dateStr = new Date();
        dateStr = dateStr.getTime();
        login.admin._token = dateStr+randomString;

        login.loginAsUser = function (adminObject) {
            // body...
            $log.info(adminObject);
            $('#loginForm').validate(angular.extend({
                // Rules for form validation
                rules: {
                    email: {
                      required: true,
                      email:true
                    },
                    password: {
                      required: true,
                    }
                },
                // Messages for form validation
                messages: {
                    email: {
                        required: "<span style='color:red;font-style:initial'>Email can't be blank</span>"
                    },
                    password: {
                      required: "<span style='color:red;font-style:initial'>Password can't be blank</span>"
                    }
                }

            }, $rootScope.validateOptions));
            if($('#loginForm').valid()&&login.admin&&login.admin!==''){
                    $http({
                        method: "POST",
                        url: $rootScope.SITE_URL+'Api/v1/login',
                        timeout:20000,
                        data: $.param(login.admin),//posting data from login form
                        headers: {'Content-Type': 'application/x-www-form-urlencoded; charset=UTF-8'}
                    }).success(function (data,status) {
                      if(status===200){
                        $log.info(data,status);
                        $cookies.put('idA',data.user_data.id);
                        $cookies.put('email',data.user_data.username);
                        $cookies.put('name',data.user_data.name);
                        $cookies.put('csrf_token',data.csrf_token);
                        $http.defaults.headers.common['X-CSRF-TOKEN']   = $cookies.get('csrf_token');
                        $http.defaults.headers.common['X-Id-Admin']   = $cookies.get('idA');  //For all $http request it ll apply this header.
                        $location.path('dashboard');
                      }
                    }).error(function(data,status) {
                      if(status===403){
                      }
                    }); 
            }else{
                $log.info('Invalid Login');

            }
        };
    }
})();