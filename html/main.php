<?php
if ((include 'common.php') != TRUE ) {
	echo "\necho 'Error with loading common functions file'";
	return false;
}
check_login();
?>
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
    <script src="/main.js"></script>
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/css/bootstrap.min.css">
    <link rel="stylesheet" href="/style.css">

  </head>
  <body>

<div ng-controller="mainCtrl" dir=rtl>
  <div class="container">
    <div class="row">
      <div class="col-sm-1">
       <i>Welcome</i>
        <a href="#">{{user.name}} <span class="glyphicon glyphicon-user" ng-style="{'glyphicon.font-size' : '50px'}"</span></a>
      </div>
    </div>
  </div>
  <div class="container">
    <div class="row"> <div class="col col-sm-offset-2"><h2>{{ui.selectWarehouse}}</h></div> </div>
    <div class="row"> <div class="col">
      &nbsp;&nbsp;
      <select ng-model="selectedWarehouse" ng-style="{'width': '50%'}" ng-options="w.id as w.description for w in warehouse" ng-init="selectedWarehouse=get_warehouse_id">
      </select>
    </div></div>
    <div class="row"> <div class="col col-sm-offset-2"><h2>{{ ui.selectContainer }}</h></div> </div>
    <div class="row"> <div class="col">
      &nbsp;&nbsp;
      <select ng-model="tmp_selectedContainer" ng-style="{'width': '50%'}" ng-options="c.id as c.description for c in containers | filter:{ warehouse_id : selectedWarehouse }" ng-init="tmp_selectedContainer=get_container_id">
      <!--select ng-model="selectedContainer" ng-disabled="!selectedWarehouse" ng-style="{'width': '50%'}" ng-change="">
        <option ng-repeat="selectedContainer in containers | filter : { warehouse_id:selectedWarehouse } : true" ng-value="selectedContainer" ng-selected="selectedContainer.id == 3391">{{selectedContainer.description | limitTo: 100:7}}</option -->
      </select>
      <span ng-show="false">{{selectedContainer = getContainerById(tmp_selectedContainer)}}a</span>
    <br><br>
    </div></div>
  </div>
    <div class="container" ng-if="selectedContainer">
    <div class="row"> <div class="col col-sm-offset-2"><h2>{{ui.selectSerial}}<h></div> </div>
    <div class="row"> <div class="col">
    <input type=checkbox name="showClosed" ng-init="showClosed=get_showClosed" id="showClosed" ng-model="showClosed"/> {{ui.showClosed}}<br>
      <div dir=ltr>
        <table border=0 width=100%><tr><td width=85px>
          <span class="label {{ damages['container'][selectedContainer.id]['types'][1] }}"><span class="glyphicon glyphicon-log-in"></span></span>
          <span class="label {{ damages['container'][selectedContainer.id]['types'][2] }}"><span class="glyphicon glyphicon-log-out"></span></span>
        </td><td>
          <small><a href="/damage.php?container_id={{ selectedContainer.id }}&warehouse_id={{selectedWarehouse}}&showClosed={{showClosed}}">{{ selectedContainer.description }}</a></small>
        </td></tr></table>
      </div>
    </div> </div>
    <div class="row" ng-repeat="m in serials | filter : { container_id:selectedContainer.id } : true" ng-if="selectedContainer">
      <div class="col" ng-if='( ! showClosed && m.status == 0 )  || showClosed '>
	<div dir=ltr>
          <table border=0 width=100%><tr><td width=85px>
            <span class="label {{ damages['serial'][m.id]['types'][1] }}"><span class="glyphicon glyphicon-log-in"></span></span>
            <span class="label {{ damages['serial'][m.id]['types'][2] }}"><span class="glyphicon glyphicon-log-out"></span></span>
            <span class="label {{ damages['serial'][m.id]['types'][3] }}"><span class="glyphicon glyphicon-barcode"></span></span>
          </td><td>
            <span ng-if="!m.serial_id"><small><a href="/damage.php?serial_id={{ m.id }}&container_id={{selectedContainer.id}}&warehouse_id={{selectedWarehouse}}&showClosed={{showClosed}}">{{ m.number}}</a></small></span>
            <span ng-if="m.serial_id"><small><a href="/damage.php?serial_id={{ m.serial_id }}&container_id={{selectedContainer.id}}&warehouse_id={{selectedWarehouse}}&showClosed={{showClosed}}">{{ m.number }} -> {{ getSerialById(m.serial_id).number }}</a></small></span>
          </td></tr><tr><td colspan=2>
            <h4><span ng-click="setSelected()"> {{ m.description }}</span></h4>
          </td></tr></table>
          <BR>
        </div>
      </div>
    </div>
  </div>
  <BR><BR>
  <a href="/logout.php">{{ui.logout}} <span class="glyphicon glyphicon-log-out" ng-style="{'glyphicon.font-size' : '50px'}"</span></a>
</div>
</body> </html>

