var $ = jQuery;
var app = angular.module('Core', []);
var error = 0;

var stripAccents = (function () {
  var in_chrs   = 'àáâãäçèéêëìíîïñòóôõöùúûüýÿÀÁÂÃÄÇÈÉÊËÌÍÎÏÑÒÓÔÕÖÙÚÛÜÝğşİ',
      out_chrs  = 'aaaaaceeeeiiiinooooouuuuyyAAAAACEEEEIIIINOOOOOUUUUYgsI', 
      chars_rgx = new RegExp('[' + in_chrs + ']', 'g'),
      transl    = {}, i,
      lookup    = function (m) { return transl[m] || m; };

  for (i=0; i<in_chrs.length; i++) {
    transl[ in_chrs[i] ] = out_chrs[i];
  }

  return function (s) { return s.replace(chars_rgx, lookup); }
})();

function imagets_fetchPage(url, callback){
	var data = {
		action: 'imagets_fetch',
		url : url
	};

	$("#scanPage").show();

	$.post(ajaxurl, data, function(response) {
		callback(JSON.parse(response));
		$("#scanPage").hide();
 	}).error(function(){
 		callback('');
 		$("#scanPage").hide();
 	});
}


function imagets_search(word, callback){
	var data = {
		action: 'imagets_search',
		word : word
	};

	$("#scanPage").show();

	$.post(ajaxurl, data, function(response) {
		callback(JSON.parse(response));
		$("#scanPage").hide();
 	}).error(function(){
 		callback('');
 		$("#scanPage").hide();
 	});
}

function createCookie(name,value,days) {
	if (days) {
		var date = new Date();
		date.setTime(date.getTime()+(days*24*60*60*1000));
		var expires = "; expires="+date.toGMTString();
	}
	else var expires = "";
	document.cookie = name+"="+value+expires+"; path=/";
}

function readCookie(name) {
	var nameEQ = name + "=";
	var ca = document.cookie.split(';');
	for(var i=0;i < ca.length;i++) {
		var c = ca[i];
		while (c.charAt(0)==' ') c = c.substring(1,c.length);
		if (c.indexOf(nameEQ) == 0) return c.substring(nameEQ.length,c.length);
	}
	return null;
}

app.controller('Controller', function ($scope, $http, $location) {

	$scope.type   = editor_mode;
	$scope.images = [];
	$scope.selectedImages = [];
	$scope.form   = [];
	$scope.form.tab  = 'search';
	$scope.form.size = option_imgsz;
	$scope.form.status = 'home';

	$scope.element = [];
	$scope.element.push({name : 'Site', selected:0, options: [
		{name : 'Google', value: ''},
		{name : 'Pinterest', value: 'site:pinterest.com '},
		{name : 'Instagram', value: 'site:instagram.com '},
		{name : 'Tumblr', value: 'site:tumblr.com '}
	]});

	$scope.element.push({name : 'Renk', selected:0, options: [
		{name : 'Normal', value: ''},
		{name : 'Kırmızı', value: 'red'},
		{name : 'Mavi', value: 'blue'},
		{name : 'Sarı', value: 'yellow'},
		{name : 'Yeşil', value: 'green'},
		{name : 'Turuncu', value: 'orange'},
		{name : 'Beyaz', value: 'white'},
		{name : 'Siyah', value: 'black'},
		{name : 'Şeffaf', value: 'transparent'}
	]});

	$scope.element.push({name : 'İçerik', selected:0, options: [
		{name : 'Normal', value: 'active'},
		{name : 'Yetişkin', value: 'off'}
	]});

	$scope.element.push({name : 'Dosya tipi', selected:0, options: [
		{name : 'Tümü', value: ''},
		{name : 'JPEG', value: 'jpg'},
		{name : 'PNG', value: 'png'},
		{name : 'Gif', value: 'gif'}
	]});

	$scope.element.push({name : 'Telif hakkı', selected:0, options: [
		{name : 'Yoksay', value: ''},
		{name : 'Herkese açık', value: 'cc_publicdomain'},
		{name : 'Ticari olmayan', value: 'cc_noncommercial'}
	]});

	$scope.getElement = function(which){
		return $scope.element[which].options[$scope.element[which].selected].value;
	}

	$scope.clearIfDisabled = function(val){
		if(imagets_desc == 'true'){
			return val;
		}else{
			return '';
		}
	}

	$scope.imagets_downloadImage = function(append_type, image_url, image_title, image_desc, image_tags, is_featured, callback){
		var data = {
			action: 'imagets_response',
			image_url : image_url,
			image_title: image_title,
			image_desc: image_desc,
			image_tags: image_tags,
			is_featured: is_featured,
			post_id   : post_id,
			append_type : append_type
		};

		$.post(ajaxurl, data, function(response) {
			callback(response);

			if(!response){
				error++;
			}
	 	}).error(function(){
	 		alert('Web sayfanızın yapılandırmasıyla ilgili bir sorun tespit edildi.');
	 		callback('');
	 		error++;
	 	});
	}

	$scope.goLink = function(link){
		$scope.bigImage = link;
	}

	var count = option_count + option_start_index;
	$scope.search = function(){
		$('#suggestion').html('').hide();
	 	stopped = true;

		if($scope.form.keyword){
			var i = option_start_index;

			if($scope.getElement(0) == 'site:instagram.com '){
				$scope.form.size = 'large';
			}

			while(count>i){
				$.get($location.protocol() + "://imagets.com/api/search?q=" + encodeURI($scope.getElement(0) + '' + $scope.form.keyword) + "&count=8&start=" + i + "&auth=" + licence).
				success(function(data, status, headers, config) {

					console.log(data);
					$.merge($scope.images, data.responseData.results);
					$scope.$apply();
				});

				i+=8;
			}
		}
	}

	$scope.fetchPage = function(){
		imagets_fetchPage($scope.form.page, function(response){
			$.merge($scope.images, response);
			$scope.$apply();
		});
	}

	$scope.loadedImage = function(url, thisObject){
		var newImg = new Image;
		newImg.onerror = function() {
		    $scope.images.splice( $scope.images.indexOf(thisObject), 1 );
		    $scope.$apply();
		}
		newImg.onload = function(){
			if(newImg.width<2){
				$scope.images.splice( $scope.images.indexOf(thisObject), 1 );
			}

			thisObject.width = newImg.width;
			thisObject.height = newImg.height;
		    $scope.$apply();
		}
		newImg.src = url;
	}

	$scope.getLinks = function(){
		var links = $scope.form.links.split('\n');

		var firstLine = links[0].toString();
		if(firstLine.search('(X)')>-1){
			console.log(firstLine);
			for(i=0;i<20;i++){
				$scope.images.push({tbUrl : firstLine.replace('(X)', i), url: firstLine.replace('(X)', i)});
			}
		}

		for(i=0;i<links.length;i++){
		    $scope.images.push({tbUrl : links[i], url: links[i]});
		}
	}

	var imageCount = 0;

	$scope.download = function(type){
		var thisName = '';
		var ids = '';

		if($scope.selectedImages.length>0){
			$scope.form.status = 'loading';
			$(".btn").attr("disabled", true);
			$("." + type).show();

			for(i=0;i<$scope.selectedImages.length;i++){
				$scope.imagets_downloadImage(type, $scope.selectedImages[i].url, $scope.selectedImages[i].name, $scope.selectedImages[i].contentNoFormatting, $scope.selectedImages[i].tag, $scope.selectedImages[i].featured, function(response){
					if(type == 'gallery'){
						if(response){
							ids+=response + ',';
						}
		        	}else if(type == 'append' || type == 'not_download'){
		        		if(response){
		        			window.parent.imagets_addImages(response);
		        		}
		        	}else if(type == 'only_download'){

		        	}

		        	imageCount++;
		        	$(".water").eq((imageCount-1)).addClass('full');

		        	setTimeout(function(){
		        		$(".mini-picture").first().fadeOut(function(){
		        			$(this).remove();
		        		});
		        	},500);

		        	if(imageCount == $scope.selectedImages.length){
		        		if(error > 0){
		        			alert(error + ' adet resme ulaşılamadı.');
		        		}

		        		if(type == 'gallery'){
		        			window.parent.imagets_createGallery(post_id, ids);
		        		}

		        		$(".loading").hide();
	        			$(".btn").attr("disabled", false);
		        		window.parent.imagets_closeThickBox();
		        	}
				});
			}

	    }else{
	    	alert("Hiç resim seçmediniz, lütfen yüklemek istediklerinizi seçin.");
	    }
	}

	$scope.deleteObject = function(image){
		$scope.images.splice( $scope.images.indexOf(image), 1 );
	}

	$scope.isSelected = function(image){
		return $scope.selectedImages.indexOf(image);
	}

	$scope.selectObject = function(image, value){
		if($scope.selectedImages.indexOf(image)>-1 && value != true){
			$scope.selectedImages.splice( $scope.selectedImages.indexOf(image), 1 );

			if($scope.featured == image){
				$scope.featured = '';
			}

		}else{
			$scope.selectedImages.push(image);
		}
	}

	$scope.selectAllImages = function(){
		$scope.selectedImages = $scope.images;
	}

	$scope.clear = function(){
		$scope.images = $scope.selectedImages;
		$scope.selectedImages = [];
	}

	$scope.urlFix = function(val){
		return val;
	}

	if(gallery){
		$scope.images = gallery;	
	}
	
	$(".form-control").first().focus();

	$http.get($location.protocol() + "://imagets.com/count/" + licence).
	success(function(data, status, headers, config) {
		$scope.form.digit = data.digit;
	});

	$scope.basename = function(str){
		return str.split('/').reverse()[0]
	}

	$scope.setByTitle = function(){
		for(i=0;i<$scope.images.length;i++){
			$scope.images[i].name = window.parent.jQuery("#title").val() + '_' + i + '.jpg';
		}
	}

	$scope.featured = '';
	var pressed = false;

	$scope.setFeatured = function(obj){
		pressed = true;

		setTimeout(function(){
			if(pressed==true){
				console.log("pressing", obj);
				$scope.featured = obj;
				console.log($scope.selectedImages);
				obj.featured = 'true';
				$scope.$apply();
			}
		},500);
	}

	$scope.isFeatured = function(obj){
		if($scope.featured == obj){
			return true;
		}
	}

	$scope.cancelPress = function(){
		pressed = false;
	}

	if(window.parent.jQuery("#title").length){
		$scope.form.keyword = window.parent.jQuery("#title").val();
	}

	/*
		if(readCookie('state')){
			$scope.element = JSON.parse(readCookie('state'));
		}

		setInterval(function(){
			console.log(JSON.stringify($scope.element));
			createCookie('state', JSON.stringify($scope.element));
		},2000);
	*/

	var stopped = false;
	$("#searchFor").keydown(function(e){
		var data = {
			action: 'imagets_suggestion',
			keyword : $(this).val()
		};

		$.post(ajaxurl, data, function(response) {
			var response = JSON.parse(response);

			if(response && !stopped){
				$('#suggestion').html('').show();
				for(i=0;i<5;i++){
					$('#suggestion').append('<a href="#" class="list-group-item">' + response[i] + '</a>');
				}

				$('#suggestion a').click(function(){
					$('#searchFor').val($(this).text());
					$('#suggestion').html('').hide();
					$scope.search();
				});
			}
			
	 	});
	});

});