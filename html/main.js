angular.module('ui.bootstrap.ovd', ['ngAnimate', 'ngSanitize', 'ui.bootstrap']);
angular.module('ui.bootstrap.ovd').controller('mainCtrl', function ($scope, $http, $location, $window) {
  $scope._ = window._;
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
 
  if($location.absUrl().indexOf('container_id') > -1){    
    $scope.get_container_id = $scope.getUrlParam('container_id');
    console.log("container_id: " + $scope.get_container_id);
  }
  if($location.absUrl().indexOf('serial_id') > -1){    
    $scope.get_serial_id = $scope.getUrlParam('serial_id');
    console.log("serial_id: " + $scope.get_serial_id);
  }
  if($location.absUrl().indexOf('type') > -1){    
    $scope.get_damage_type = $scope.getUrlParam('damage_type');
    console.log("damage_type: " + $scope.get_damage_type);
  }
  $scope.oneAtATime = true;
  $scope.ui = {
    'selectContainer': "Container",
    'selectWarehouse': "Warehouse",
    'selectSerial': "Serial",
    'takePhoto': "Take a Photo",
    'internalDamage': "Internal Damages",
    'externalDamage': "External Damages",
    'labeledDamage': "Labeled Damages",
    'all': "All",
    'welcome': "Welcome to OVD",
    'notes': "Notes",
    'submit': "Submit",
    'photo': "Photo",
    'login': "Please Login",
    'showClosed': "Show Closed Serials",
    'closeSerial': "Close Serial",
    'openSerial': "Open Serial",
    'logout': "Logout"
  };

  $http.get("api.php?action=get_user")
  .then(function(response) {
      $scope.user = response.data;
  });

  $http.get("api.php?action=get_warehouse")
  .then(function(response) {
      $scope.warehouse = response.data;
  });

  $http.get("api.php?action=get_containers")
  .then(function(response) {
      $scope.containers = response.data;
  });

  $http.get("api.php?action=get_serials")
  .then(function(response) {
    $scope.serials = response.data;

    $http.get("api.php?action=get_damages")
    .then(function(response) {
        $scope.get_damages = response.data;
        $scope.damages = {'serial': {}, 'container': {}};

        angular.forEach($scope.containers,function(container,key){
          if (typeof container.id != 'undefined' ) { 
            if (typeof $scope.damages['container'][container.id] == 'undefined' || typeof $scope.damages['container'][container.id]['damages'] == 'undefined') {
              $scope.damages['container'][container.id] = { 'damages': [], 'types': { 1: 'label-default', 2: 'label-default', 3: 'label-default' }} ;
            }
          } 
        });

        angular.forEach($scope.serials,function(serial,key){
          if (typeof serial.id != 'undefined' ) { 
            if (typeof $scope.damages['serial'][serial.id] == 'undefined' || typeof $scope.damages['serial'][serial.id]['damages'] == 'undefined') {
              $scope.damages['serial'][serial.id] = { 'damages': [], 'types': { 1: 'label-default', 2: 'label-default', 3: 'label-default' }} ;
            }
          } 
        });

        angular.forEach($scope.get_damages,function(damage,key){
          if (typeof damage.serial_id != 'undefined' ) { 
            if (typeof $scope.damages['serial'][damage.serial_id] == 'undefined' || typeof $scope.damages['serial'][damage.serial_id]['damages'] == 'undefined') {
              $scope.damages['serial'][damage.serial_id] = { 'damages': [], 'types': { 1: 'label-default', 2: 'label-default', 3: 'label-default' }} ;
            }
            $scope.damages['serial'][damage.serial_id]['damages'].push(damage);
            $scope.damages['serial'][damage.serial_id]['types'][damage.type] = 'label-success';
          } 
          if (typeof damage.container_id != 'undefined' ) { 
            if (typeof $scope.damages['container'][damage.container_id] == 'undefined' || typeof $scope.damages['container'][damage.container_id]['damages'] == 'undefined') {
              $scope.damages['container'][damage.container_id] = { 'damages': [], 'types': { 1: 'label-default', 2: 'label-default' }} ;
            }
            $scope.damages['container'][damage.container_id]['damages'].push(damage);
            $scope.damages['container'][damage.container_id]['types'][damage.type] = 'label-success';
          }
        });
      console.log($scope.damages);
    });
  });

  $scope.labeltest = "label-primary";

  $scope.getContainerById = function(id=$scope.get_container_id) {
    var intId = parseInt(id);
    return $scope._.find($scope.containers, {id: intId});
  }
  $scope.getSerialById = function(id=$scope.get_serial_id) {
    var intId = parseInt(id);
    return $scope._.find($scope.serials, {id: intId});
  }
  $scope.getContainerBySerialId = function(id=$scope.get_serial_id) {
    var intId = parseInt(id);
    var containerId = $scope.getSerialById(intId);
    if (typeof containerId != 'undefined') { 
      return $scope._.find($scope.containers, {id: parseInt(containerId.container_id)});
    }
  }
  $scope.setSerialStatus = function(stat, id=$scope.get_serial_id) { 
    $http.get("api.php?action=set_serial_status&id=" + id + "&stat=" + stat)
    .then(function(response) {
        console.log(response.data);
        var url = $window.location.protocol + "//" + $window.location.host + "/main.html";
        $window.location.href = url;
    });
  }

});
