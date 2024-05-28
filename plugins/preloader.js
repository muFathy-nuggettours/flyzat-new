//waitForImages Plugin
!function(a){"function"==typeof define&&define.amd?define(["jquery"],a):"object"==typeof exports?module.exports=a(require("jquery")):a(jQuery)}(function(a){var b="waitForImages",c=function(a){return a.srcset&&a.sizes}(new Image);a.waitForImages={hasImageProperties:["backgroundImage","listStyleImage","borderImage","borderCornerImage","cursor"],hasImageAttributes:["srcset"]},a.expr[":"]["has-src"]=function(b){return a(b).is('img[src][src!=""]')},a.expr[":"].uncached=function(b){return!!a(b).is(":has-src")&&!b.complete},a.fn.waitForImages=function(){var d,e,f,g=0,h=0,i=a.Deferred(),j=this,k=[],l=a.waitForImages.hasImageProperties||[],m=a.waitForImages.hasImageAttributes||[],n=/url\(\s*(['"]?)(.*?)\1\s*\)/g;if(a.isPlainObject(arguments[0])?(f=arguments[0].waitForAll,e=arguments[0].each,d=arguments[0].finished):1===arguments.length&&"boolean"===a.type(arguments[0])?f=arguments[0]:(d=arguments[0],e=arguments[1],f=arguments[2]),d=d||a.noop,e=e||a.noop,f=!!f,!a.isFunction(d)||!a.isFunction(e))throw new TypeError("An invalid callback was supplied.");return this.each(function(){var b=a(this);f?b.find("*").addBack().each(function(){var b=a(this);b.is("img:has-src")&&!b.is("[srcset]")&&k.push({src:b.attr("src"),element:b[0]}),a.each(l,function(a,c){var d,e=b.css(c);if(!e)return!0;for(;d=n.exec(e);)k.push({src:d[2],element:b[0]})}),a.each(m,function(a,c){var d=b.attr(c);return!d||void k.push({src:b.attr("src"),srcset:b.attr("srcset"),element:b[0]})})}):b.find("img:has-src").each(function(){k.push({src:this.src,element:this})})}),g=k.length,h=0,0===g&&(d.call(j),i.resolveWith(j)),a.each(k,function(f,k){var l=new Image,m="load."+b+" error."+b;a(l).one(m,function b(c){var f=[h,g,"load"==c.type];if(h++,e.apply(k.element,f),i.notifyWith(k.element,f),a(this).off(m,b),h==g)return d.call(j[0]),i.resolveWith(j[0]),!1}),c&&k.srcset&&(l.srcset=k.srcset,l.sizes=k.sizes),l.src=k.src}),i.promise()}});

//===== Image =====

//Image Preloader [Append Loader]
$(document).ready(function(){
	$("img[preload=true]").each(function(index){
		imageObject = $(this);
		//If Image path is set with src attribute
		if (imageObject.attr("src")){
			var style = (imageObject.attr("wrapper-style") ? imageObject.attr("wrapper-style") : "");
			imageObject.wrap("<div class=preloader-wrapper style='" + style + "'></div>");
			imageObject.parent().append("<div class=preloader-spinner-container><div class=preloader-spinner></div></div>");
			//Wait for Image
			imageObject.waitForImages(function(){
				//All Images Loaded
			}, function(loaded, count, success){
				target = $(this).parent().children(".preloader-spinner-container");
				target.fadeOut("slow", function(){
					$(this).unwrap();
					$(this).parent().find(".preloader-spinner-container").remove();
				});
				$(this).css("opacity","1");
			}, true);			
		}
		//If Image path is set with data-src attribute
		if (imageObject.attr("data-src")){
			/* To be implenented */
		}
	});
});

//===== Division =====

$(document).ready(function(){
	//DIV Preloader [Append Loader]
	$("div[preload=true]").each(function(index){
		divisionObject = $(this);
		var style = (divisionObject.attr("wrapper-style") ? divisionObject.attr("wrapper-style") : "");
		divisionObject.append("<div class=div-preloader-container style='" + style + "'><div class=preloader-spinner></div></div>");
		//Wait for Image
		divisionObject.waitForImages(function(){
			//All Images Loaded
		}, function(loaded, count, success){
			$target = $(this).children(".div-preloader-container");
			$target.fadeOut("slow", function(){ $(this).remove(); });
		}, true);		
	});
});