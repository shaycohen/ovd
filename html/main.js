angular.module('ui.bootstrap.ovd', ['ngAnimate', 'ngSanitize', 'ui.bootstrap']);
angular.module('ui.bootstrap.ovd').controller('mainCtrl', function ($scope, $http) {
  $scope.oneAtATime = true;
  
  $scope.ui = {
    'selectContainer': "Container",
    'selectWarehouse': "Warehouse",
    'selectManifest': "Manifest",
    'uploadPhoto': "Upload Photo",
    'all': "All",
    'welcome': "Welcome to OVD"
  };
  $http.get("api.php?action=get_warehouse")
  .then(function(response) {
      $scope.warehouse = response.data;
  });

  $http.get("api.php?action=get_containers")
  .then(function(response) {
      $scope.containers = response.data;
  });

  $http.get("api.php?action=get_manifests")
  .then(function(response) {
      $scope.manifests = response.data;
  });

  $http.get("api.php?action=get_damages")
  .then(function(response) {
      $scope.damages = response.data;
      $scope.damageByManifest = {};
      angular.forEach($scope.damages,function(value,key){
        if (typeof $scope.damageByManifest[value.manifest_id] == 'undefined' || typeof $scope.damageByManifest[value.manifest_id]['damages'] == 'undefined') {
          $scope.damageByManifest[value.manifest_id] = { 'damages': [], 'types': { 1: 'label-default', 2: 'label-default', 3: 'label-default' }} ;
        }
        $scope.damageByManifest[value.manifest_id]['damages'].push(value);
        $scope.damageByManifest[value.manifest_id]['types'][value.type] = 'label-success';
        
      });
  });

  $scope.labeltest = "label-primary";

  $scope.status = {
    isCustomHeaderOpen: false,
    isFirstOpen: false,
    isFirstDisabled: false
  };

  $scope.add = function() {
    var f = document.getElementById('file').files[0],
        r = new FileReader();

    r.onloadend = function(e) {
      var data = e.target.result;
      console.log(data);
      //send your binary data via $http or $resource or do anything else with it
    }

    r.readAsBinaryString(f);
  }

});
