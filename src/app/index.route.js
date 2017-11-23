(function() {
    'use strict';

    angular
        .module('smartskillApp')
        .config(routeConfig);

    /** @ngInject */
    function routeConfig($stateProvider, $urlRouterProvider) {
        /*$stateProvider
            .state('home', {
                url: '/',
                templateUrl: 'app/main/main.html',
                controller: 'MainController',
                controllerAs: 'main'
            });

        $urlRouterProvider.otherwise('/');*/
        $stateProvider
            
            .state('login', {
                url: '/login',
                templateUrl: 'app/login/login.html',
                controller: 'LoginController',
                controllerAs: 'login',
                data: { pageTitle: 'Login To Smartskill' }
               
            })  
            .state('logout', {
                url: '/logout',
                controller: 'LogoutController',
                controllerAs: 'logout',
                data: { pageTitle: 'Logout Form Smartskill' }
               
            })                    
            .state('index', {
                abstract:true,
                templateUrl: "app/components/navbar/navbar.html"
            })

            .state('index.main', {
                url: '/main',
                templateUrl: 'app/main/main.html',
                controller: 'MainController',
                controllerAs: 'main'
            })

            .state('index.dashboard', {
                url: '/dashboard',
                templateUrl: 'app/dashboard/dashboard.html',
                controller: 'DashboardController',
                controllerAs: 'dashboard'
            })

            .state('index.quiz', {
                url: '/quiz',
                templateUrl: 'app/quiz/quiz.html',
                controller: 'QuizController',
                controllerAs: 'quiz',
                resolve: {
                loadPlugin: function ($ocLazyLoad,$rootScope) {

                    return $ocLazyLoad.load([

                        {
                            serie: true,
                            files: [$rootScope.BASE_URL+'resources/jsplugins/datatables/media/js/jquery.dataTables.min.js',
                                    $rootScope.BASE_URL+'resources/jsplugins/datatables-tabletools/js/dataTables.tableTools.js',
                                    $rootScope.BASE_URL+'resources/jsplugins/datatables-colvis/js/dataTables.colVis.js',
                                    $rootScope.BASE_URL+'resources/jsplugins/datatables-responsive/files/1.10/js/datatables.responsive.js',
                                    $rootScope.BASE_URL+'resources/cssplugins/dataTables.min.css',
                                    $rootScope.BASE_URL+'bower_components/alertifyjs/dist/js/ngAlertify.js']
                        }
                     ]);
                    }
                }
                        
            })

            .state('index.quizquestion', {
                url: '/quiz/:id',
                templateUrl: 'app/quizquestion/add_quiz_question.html',
                controller: 'QuizQuestionController',
                controllerAs: 'quizquestion',
                resolve: {
                loadPlugin: function ($ocLazyLoad,$rootScope) {

                    return $ocLazyLoad.load([

                        {
                            serie: true,
                            files: [$rootScope.BASE_URL+'bower_components/alertifyjs/dist/js/ngAlertify.js']
                        }
                     ]);
                    }
                }

            });

        $urlRouterProvider.otherwise('/login');
    }

})();