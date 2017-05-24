angular.module('ui.bootstrap.ovd', ['ngAnimate', 'ngSanitize', 'ui.bootstrap']);
angular.module('ui.bootstrap.ovd').controller('mainCtrl', function ($scope, $http, $location) {
  $scope.$location = $location;
  console.log($location.absUrl());
  $scope.getUrlParam = function getUrlParam(sParam) {
    var sPageURL = decodeURIComponent(window.location.search.substring(1)),
      sURLVariables = sPageURL.split('&'),
      sParameterName,
      i;

    for (i = 0; i < sURLVariables.length; i++) {
      sParameterName = sURLVariables[i].split('=');

      if (sParameterName[0] === sParam) {
          return sParameterName[1] === undefined ? true : sParameterName[1];
      }
    }
  };

  if($location.absUrl().indexOf('fmsg') > -1){    
    $scope.get_fmsg = $scope.getUrlParam('fmsg');
    console.log("fmsg: " + $scope.get_fmsg);
  } else { 
    $scope.get_fmsg = 0;
  }
 
  if($location.absUrl().indexOf('manifest_id') > -1){    
    $scope.get_manifest_id = $scope.getUrlParam('manifest_id');
    console.log("manifest_id: " + $scope.get_manifest_id);
  }
  if($location.absUrl().indexOf('type') > -1){    
    $scope.get_damage_type = $scope.getUrlParam('damage_type');
    console.log("damage_type: " + $scope.get_damage_type);
  }
  $scope.oneAtATime = true;
  $scope.ui = {
    'selectContainer': "Container",
    'selectWarehouse': "Warehouse",
    'selectManifest': "Manifest",
    'uploadPhoto': "Upload Photo",
    'internalDamage': "Internal Damages",
    'externalDamage': "External Damages",
    'labeledDamage': "Labeled Damages",
    'all': "All",
    'welcome': "Welcome to OVD",
    'notes': "Notes",
    'submit': "Submit",
    'photo': "Photo",
    'login': "Please Login"
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
