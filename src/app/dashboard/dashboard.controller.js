(function() {
    'use strict';

    angular
        .module('smartskillApp')
        .controller('DashboardController', DashboardController);

    /** @ngInject */
    function DashboardController($log) {
    	$log.info('hhdash');
    }
})();