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
<div ng-controller="mainCtrl" ng-init="uploadClicked=false">
    <!--div class="col col-sm-offset-1" dir="ltr">
      {{getContainerBySerialId().description }}{{getSerialById().number}}
    </div-->
  <form class="form-group" action="upload_be.php?debug_level=0" method="post" enctype="multipart/form-data" id="upload_form" name="upload_form">
    <input type=hidden name="serial_id" id="serial_id" value="{{ get_serial_id }}" ng-if="get_serial_id > 0">
    <input type=hidden name="container_id" id="container_id" value="{{ get_container_id }}" ng-if="get_container_id > 0">
    <input type=hidden name="warehouse_id" id="warehouse_id" value="{{ get_warehouse_id }}" ng-if="get_warehouse_id > 0">
    <input type=hidden name="description" id="description" value="">
    <input type=hidden name="type" id="type" value="{{ get_damage_type }}">
    <input type=hidden name="showClosed" id="showClosed" value="{{ get_showClosed }}">
    <input type=hidden name="file_name" id="file_name" value="{{getContainerBySerialId().description}}{{getSerialById().number}}" ng-if="get_serial_id">
    <input type=hidden name="file_name" id="file_name" value="{{getContainerById().description}}" ng-if="!get_serial_id">

    <div class="input-group" ng-if="get_serial_id">
      <span class="input-group-addon" id="basic-addon1">
        <input type="file" name="fileToUpload" id="fileToUpload" class="inputfile" onchange="document.getElementById('upload_form').submit()" ng-click="uploadClicked=true" accept="image/*" capture="camera">
          <img src="images/loading.gif" height=60px ng-if="!getContainerBySerialId() || uploadClicked">
        <label for="fileToUpload">
          <button class="btn btn-lg btn-info btn-block" ng-if="getContainerBySerialId() && !uploadClicked" type="submit" name="image_submit">
            {{ ui.takePhoto }} &nbsp;&nbsp;&nbsp;
          </button>
        </label>
      </span>
    </div>

    <div class="input-group" ng-if="!get_serial_id">
      <span class="input-group-addon" id="basic-addon1">
        <input type="file" name="fileToUpload" id="fileToUpload" class="inputfile" onchange="document.getElementById('upload_form').submit()" ng-click="uploadClicked=true">
          <img src="images/loading.gif" height=60px ng-if="!getContainerById() || uploadClicked">
        <label for="fileToUpload">
          <button class="btn btn-lg btn-info btn-block" ng-if="getContainerById() && !uploadClicked" type="submit" name="image_submit">
            {{ ui.takePhoto }} &nbsp;&nbsp;&nbsp;
            <span class="glyphicon glyphicon-picture" ng-if="fileToUpload"></span>
          </button>
        </label>
      </span>
    </div>

<!--    <div class="input-group">
      <span class="input-group-addon" id="basic-addon1"><span class="glyphicon glyphicon-list"></span></span>
      <input type="text" class="form-control" placeholder="{{ui.notes}}" aria-describedby="basic-addon1" name="description" id="description" ng-style="{'width': '100%'}">
    </div>
    <button class="btn btn-lg btn-success btn-block" type="submit" name="form_submit">{{ ui.submit }}</button>
-->
  </form>
</div>
</body> </html>
