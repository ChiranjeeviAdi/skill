(function() {
    'use strict';

    angular
        .module('smartskillApp')
        .config(config);

    /** @ngInject */
    function config($logProvider) {
        // Enable log
        $logProvider.debugEnabled(true);

       
    }

})();