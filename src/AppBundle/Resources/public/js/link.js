angular.module('linkApp', ["xeditable"])
    .controller('BaseController', function($controller, $scope, $http) {
        var todoList = this;

        $scope.addLink = function () {
            $scope.inserted = {
                id: null,
                original_link: '',
                short_link: '',
                life_time: '',
                is_active: false
            };
            $scope.links.unshift($scope.inserted);
        };

        $scope.saveLink = function (data,link) {

            $http({
                method : "POST",
                url : "/create_link",
                data: link
            }).then(function success(response) {
                var obj = JSON.parse(response.data);
                link.id = obj.id;
                link.short_link = obj.short_link;
            }, function error(response) {
            });
        };
        
        $scope.removeLink = function (index, link) {

            $http({
                method : "POST",
                url : "/remove_link",
                data: link.id
            }).then(function success(response) {
                $scope.links.splice(index, 1);
            }, function error(response) {
            });
        };
        
        $scope.showStatistic = function (link) {

            $http({
                method : "POST",
                url : "/statistic_link",
                data: link.id
            }).then(function success(response) {
                $scope.viewLink = JSON.parse(response.data);
                $('#exampleModal').modal('show')
            }, function error(response) {
            });
        };

    })
    .config(function($interpolateProvider){
        $interpolateProvider.startSymbol('{[{').endSymbol('}]}');
    });