angular
    .module('admin-app.database')
    .config(['$stateProvider', 'AppPaths', function($stateProvider, AppPaths) {

        $stateProvider

        //=====================================================
        // DATABASE
        //=====================================================

            .state('app.db', {
                url: '/db',
                template: '<ui-view></ui-view>',
                abstract: true
            });

        // states WITHOUT custom controller and template
        var generalStates = [
            'pages', 'mail_templates', 'sent_mails', 'settings', 'logs', 'tags', 'templates', 'users'
        ];

        generalStates.forEach(function(state_name){
            var StateName = _.upperFirst(_.camelCase(state_name));

            $stateProvider.state('app.db.' + state_name, {
                url: '/' + state_name,
                controller: 'DBManageGeneralController',
                templateUrl: AppPaths.database + 'general/templates/index.html',
                resolve: {
                    EntityConfig: 'DBManage' + StateName + 'Config'
                }
            })
        });

        // states WITH custom controller and template
        var customStates = [
            'subscribers', 'sub_fields', 'translations'
        ];

        customStates.forEach(function(state_name){
            var StateName = _.upperFirst(_.camelCase(state_name));

            $stateProvider.state('app.db.' + state_name, {
                url: '/' + state_name,
                controller: 'DBManage' + StateName + 'Controller',
                templateUrl: AppPaths.database + state_name + '/templates/index.html',
                resolve: {
                    EntityConfig: 'DBManage' + StateName + 'Config'
                }
            })
        });
    }]);