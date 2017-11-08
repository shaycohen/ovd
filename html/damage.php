<?php
if ((include 'common.php') != TRUE ) {
	echo "\necho 'Error with loading common functions file'";
	return false;
}
check_login();
?>
<!doctype html>
<html ng-app="ui.bootstrap.ovd">
<head>
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <meta meta charset=utf-8>
  <script src="/ext/angular.js"></script>
  <script src="/ext/angular-animate.js"></script>
  <script src="/ext/angular-sanitize.js"></script>
  <script src="/ext/ui-bootstrap-tpls-2.5.0.js"></script>
  <script src="/ext/jquery.min.js"></script>
  <script src="/ext/bootstrap.min.js"></script>
  <script src="/ext/lodash.js"></script>
  <script src="main.js"></script>
  <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/css/bootstrap.min.css">
  <link rel="stylesheet" href="/style.css">
</head>
<body>
<div ng-controller="mainCtrl" dir=rtl>
  <div class="container" ng-if="get_serial_id > 0">
    <a href="/main.php?warehouse_id={{get_warehouse_id}}&container_id={{get_container_id}}&showClosed={{get_showClosed}}" class="btn btn-info btn-lg"><span class="glyphicon glyphicon-arrow-left"></span></a>
    <div class="row"> <div class="col col-sm-offset-1"><h1><i> {{getSerialById().number}}<BR>{{getContainerBySerialId().description | limitTo: 100:7 }}<BR>{{getSerialById().description}} </i></h></div></div>
    <div class="row"> <div class="col"><h2>{{ui.internalDamage}}</div></div>
    <table><tr>
    <td><BR><a href="/upload.php?serial_id={{ get_serial_id }}&container_id={{ get_container_id }}&warehouse_id={{ get_warehouse_id}}&damage_type=1&showClosed={{get_showClosed}}"><img src=images/add_photo.png></a></td>
    <td ng-repeat="d in get_damages" ng-if="d.serial_id==get_serial_id && d.type==1">
      <img src="/damages/{{ d.id }}_thumb.jpg" width=50%> 
      <img src="/damages/{{ d.file_name}}-{{ d.id }}_thumb.jpg" width=50%> <br>
      <BR>
      {{ d.description }}
    </td></tr></table>
    <div class="row"> <div class="col"><h2>{{ui.externalDamage}}</div></div>
    <table><tr>
    <td><BR><a href="/upload.php?serial_id={{ get_serial_id }}&container_id={{ get_container_id }}&warehouse_id={{ get_warehouse_id}}&damage_type=2&showClosed={{get_showClosed}}"><img src=images/add_photo.png></a></td>
    <td ng-repeat="d in get_damages" ng-if="d.serial_id==get_serial_id && d.type==2">
      <BR>
      <img src="/damages/{{ d.id }}_thumb.jpg" width=50%> <BR>
      <img src="/damages/{{ d.file_name}}-{{ d.id }}_thumb.jpg" width=50%> <br>
      {{ d.description }}
    </td></tr></table>
    <div class="row"> <div class="col"><h2>{{ui.labeledDamage}}</div></div>
    <table><tr>
    <td><BR><a href="/upload.php?serial_id={{ get_serial_id }}&container_id={{ get_container_id }}&warehouse_id={{ get_warehouse_id}}&damage_type=3&showClosed={{get_showClosed}}"><img src=images/add_photo.png></a></td>
    <td ng-repeat="d in get_damages" ng-if="d.serial_id==get_serial_id && d.type==3">
      <BR>
      <img src="/damages/{{ d.id }}_thumb.jpg" width=50%> <br>
      <img src="/damages/{{ d.file_name}}-{{ d.id }}_thumb.jpg" width=50%> <br>
      {{ d.description }}
    </td></tr></table>
      <BR>
  </div>
  <div class="container" ng-if="! get_serial_id > 0">
    <a href="/main.php?warehouse_id={{get_warehouse_id}}&container_id={{get_container_id}}&showClosed={{get_showClosed}}" class="btn btn-info btn-lg"><span class="glyphicon glyphicon-arrow-left"></span></a>
    <div class="row"> <div class="col col-sm-offset-1"><h1><i>{{getContainerById().description}}</i></h></div></div>
    <div class="row"> <div class="col"><h2>{{ui.internalDamage}}</div></div>
      <table><tr>
      <td><BR><a href="/upload.php?container_id={{ get_container_id }}&warehouse_id={{ get_warehouse_id}}&damage_type=1&showClosed={{get_showClosed}}"><img src=images/add_photo.png></a></td>
      <td ng-repeat="d in get_damages" ng-if="d.container_id==get_container_id && d.type==1">
        <BR>
        <img src="/damages/{{ d.id }}_thumb.jpg" width=50%> 
        <img src="/damages/{{ d.file_name}}-{{ d.id }}_thumb.jpg" width=50%> <br>
        <BR>
        {{ d.description }}
      </td></tr></table>
      <div class="row"> <div class="col"><h2>{{ui.externalDamage}}</div></div>
      <table><tr>
      <td><BR><a href="/upload.php?container_id={{ get_container_id }}&warehouse_id={{ get_warehouse_id}}&damage_type=2&showClosed={{get_showClosed}}"><img src=images/add_photo.png></a></td>
      <td ng-repeat="d in get_damages" ng-if="d.container_id==get_container_id && d.type==2">
        <BR>
        <img src="/damages/{{ d.id }}_thumb.jpg" width=50%><BR>
        <img src="/damages/{{ d.file_name}}-{{ d.id }}_thumb.jpg" width=50%> <br>
        {{ d.description }}
      </td></tr></table>
    </div>
      <button class="btn btn-sm btn-warning btn-block" ng-click="setSerialStatus(1)" ng-if="getSerialById().status==0">{{ ui.closeSerial }}</button>
      <button class="btn btn-sm btn-primary btn-block" ng-click="setSerialStatus(0)" ng-if="getSerialById().status==1">{{ ui.openSerial }}</button>
  </div>
</div>
</body> </html>

