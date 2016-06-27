angular.module('app')
    .controller('AppController', ['$scope', '$http', 'AppPaths', 'AppData', 'Pages', function($scope, $http, AppPaths, AppData, Pages) {
        var self = this;

        self.menuList = [
            {
                heading: 'Dashboard',
                route:   'app.dashboard'
            },
            {
                heading: 'Pages',
                route:   'app.pages'
            },
            {
                heading: 'Dictionary',
                route:   'app.dictionary'
            },
            {
                heading: 'Settings',
                route:   'app.settings'
            },
            {
                heading: 'Logs',
                route:   'app.logs'
            },
            {
                heading: 'Tags',
                route:   'app.tags'
            },
            {
                heading: 'Templates',
                route:   'app.templates'
            },
            {
                heading: 'SubFields',
                route:   'app.sub_fields'
            },
            {
                heading: 'Users',
                route:   'app.users'
            },
            {
                heading: 'Accounts',
                route:   'app.accounts',
                disable: true
            }
        ];

        self.pages = Pages.query();
    }]);