var app = angular.module('app', ['ngDropdowns']);


app.controller('PromoterController', function($scope, $http){

	$scope.radioChange = function(value) {
		$scope.label_initials = '';
	};

	$scope.labelInputChange = function () {
		$scope.label = false;
	};

	$scope.monoInputChange = function (val) {
		if(val.length < 1) {
			$scope.mono_font = false;	
			$scope.mono_color_id = '';
			$scope.mono_local = '';
		}


	};

	$scope.ctInputChange = function () {
		$scope.coser = false;
		$scope.pregar = false;
		$scope.costura = false;
	};

	$scope.appInputChange = function () {
		$scope.app_option = '';
	};

	$scope.cfInputChange = function () {
		$scope.gola_int = false;
		$scope.gola_ext = false;
		$scope.gola_inf = false;
		$scope.gola = false;
		$scope.punho_int = false;
		$scope.punho_ext = false;
		$scope.bolso_int = false;
		$scope.bolso_beira = false;
		$scope.carcela_int = false;
		$scope.carcela_ext = false;
		$scope.malhete_ext = false;
		$scope.malhete_int = false;
		$scope.gussets = false;
	};

	/*$scope.fontChange = function () {

		var font = $scope.mono_font;

		$scope.mono_font = font.capitalize();
	}*/
	/*angular.forEach(data, function(value, index) {
	    
	    $scope[index] = value
	});*/
});