angular.module('ui.bootstrap.ovd', ['ngAnimate', 'ngSanitize', 'ui.bootstrap']);
angular.module('ui.bootstrap.ovd').controller('mainCtrl', function ($scope, $http, $location, $window) {
  $scope._ = window._;
  $scope.$location = $location;
  //console.log($location.absUrl());
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

  if ( ($location.absUrl().indexOf('showClosed') > -1) && 
       ($scope.getUrlParam('showClosed') == "true" || 
         parseInt($scope.getUrlParam('showClosed')) == 1 ) ) { 
    $scope.get_showClosed = true;
  } else { 
    $scope.get_showClosed = false;
  }
  console.log("showClosed: " + $scope.get_showClosed);
 
  if($location.absUrl().indexOf('fmsg') > -1){    
    $scope.get_fmsg = $scope.getUrlParam('fmsg');
    //console.log("fmsg: " + $scope.get_fmsg);
  } else { 
    $scope.get_fmsg = 0;
  }
 
  if($location.absUrl().indexOf('warehouse_id') > -1){    
    $scope.get_warehouse_id = parseInt($scope.getUrlParam('warehouse_id'));
    //console.log("warehouse_id: " + $scope.get_warehouse_id);
  }
   if($location.absUrl().indexOf('container_id') > -1){    
    $scope.get_container_id = parseInt($scope.getUrlParam('container_id'));
    //console.log("container_id: " + $scope.get_container_id);
  }
  if($location.absUrl().indexOf('serial_id') > -1){    
    $scope.get_serial_id = parseInt($scope.getUrlParam('serial_id'));
    //console.log("serial_id: " + $scope.get_serial_id);
  }
  if($location.absUrl().indexOf('type') > -1){    
    $scope.get_damage_type = $scope.getUrlParam('damage_type');
    //console.log("damage_type: " + $scope.get_damage_type);
  }
  $scope.oneAtATime = true;
  $scope.ui = {
    'selectContainer': "מכולה",
    'selectWarehouse': "מחסן",
    'selectSerial': "סידורי",
    'takePhoto': "צלם תמונה",
    'internalDamage': "תמונות נזק בתוך המכולה",
    'externalDamage': "תמונות נזק מחוץ למכולה",
    'labeledDamage': "תמונות נזק עם מדבקת ברקוד",
    'all': "הכל",
    'welcome': "מערכת ניהול נזיקים",
    'notes': "פרטים",
    'submit': "שלח",
    'photo': "תמונה",
    'login': "אנא הזן פרטי משתמש",
    'showClosed': "הצג סידוריים שנסגרו",
    'closeSerial': "סגור סידורי",
    'openSerial': "פתח סידורי",
    'invalid_credentials': "שם משתמש או סיסמא שגויים",
    'logout': "יציאה"
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
    $scope.raw_serials = response.data;
    $scope.serials = [];

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

        angular.forEach($scope.raw_serials,function(serial,key){
          if (typeof serial.id != 'undefined' && serial.id != null) { 
            tmp_serial_id = serial.id;
            if (typeof serial.serial_id != 'undefined' && serial.serial_id != null) { 
              tmp_serial_id = serial.serial_id;
            }
            if (typeof $scope.damages['serial'][tmp_serial_id] == 'undefined' || typeof $scope.damages['serial'][tmp_serial_id]['damages'] == 'undefined') {
              $scope.damages['serial'][tmp_serial_id] = { 'damages': [], 'types': { 1: 'label-default', 2: 'label-default', 3: 'label-default' }} ;
            }
            $scope.serials[serial.id] = serial;
            tmp_serial_id = null;
          }  
        });

        angular.forEach($scope.get_damages,function(damage,key){
          if (typeof damage.serial_id != 'undefined' && damage.serial_id != null) { 
            tmp_damage_serial_id = damage.serial_id;
            if (typeof $scope.serials[damage.serial_id] != 'undefined' && typeof $scope.serials[damage.serial_id].serial_id != 'undefined' && $scope.serials[damage.serial_id].serial_id != null) { 
              tmp_damage_serial_id = $scope.serials[damage.serial_id].serial_id;
              console.log('presenting damages for ' + damage.serial_id + ' under ' + tmp_damage_serial_id);
            }
            if (typeof $scope.damages['serial'][tmp_damage_serial_id] == 'undefined' || typeof $scope.damages['serial'][tmp_damage_serial_id]['damages'] == 'undefined') {
              $scope.damages['serial'][tmp_damage_serial_id] = { 'damages': [], 'types': { 1: 'label-default', 2: 'label-default', 3: 'label-default' }} ;
            }
            damage.serial_id = tmp_damage_serial_id;
            $scope.damages['serial'][tmp_damage_serial_id]['damages'].push(damage);
            $scope.damages['serial'][tmp_damage_serial_id]['types'][damage.type] = 'label-success';
            tmp_damage_serial_id = null;
          } 
          if (typeof damage.container_id != 'undefined' ) { 
            if (typeof $scope.damages['container'][damage.container_id] == 'undefined' || typeof $scope.damages['container'][damage.container_id]['damages'] == 'undefined') {
              $scope.damages['container'][damage.container_id] = { 'damages': [], 'types': { 1: 'label-default', 2: 'label-default' }} ;
            }
            $scope.damages['container'][damage.container_id]['damages'].push(damage);
            $scope.damages['container'][damage.container_id]['types'][damage.type] = 'label-success';
          }
        });
    });
  });

  $scope.labeltest = "label-primary";

  $scope.getContainerById = function(id=$scope.get_container_id) {
    var intId = parseInt(id);
    //console.log("getContainerById " + id);
    //console.log("getContainerById return " + $scope._.find($scope.containers, {id: intId}) );
    return $scope._.find($scope.containers, {id: intId});
  }
  $scope.getSerialById = function(id=$scope.get_serial_id) {
    var intId = parseInt(id);
    //console.log("getSerialById " + id);
    //console.log("getSerialById return " +  $scope._.find($scope.serials, {id: intId}) );
    return $scope._.find($scope.serials, {id: intId});
  }
  $scope.getContainerBySerialId = function(id=$scope.get_serial_id) {
    //console.log("getContainerBySerialId " + id);
    var intId = parseInt(id);
    var containerId = $scope.getSerialById(intId);
    if (typeof containerId != 'undefined') { 
      //console.log(containerId.container_id);
      //console.log($scope.containers);
      //console.log("getContainerBySerialId return " + $scope._.find($scope.containers, {id: parseInt(containerId.container_id)}) );
      return $scope._.find($scope.containers, {id: parseInt(containerId.container_id)});
    } else { 
      //console.log( "false" );
      return false;
    }
  }
  $scope.setSerialStatus = function(stat, id=$scope.get_serial_id) { 
    $http.get("api.php?action=set_serial_status&id=" + id + "&stat=" + stat)
    .then(function(response) {
        //console.log(response.data);
        var url = $window.location.protocol + "//" + $window.location.host + "/main.php" + $window.location.search;
        $window.location.href = url;
    });
  }

  $scope.setSelected = function() {
    var data = JSON.stringify({
      'selectedWarehouse': $scope.selectedWarehouse.id,
      'selectedContainer': $scope.selectedContainer.id
    });
    //console.log(data);
    $http.post("/api.php?action=set_selected", data).then(function(data, status) {
      //console.log(data);
    })
  }                   

});
