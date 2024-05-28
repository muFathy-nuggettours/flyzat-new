//========== [Panel Only] ==========

//Initialize sticky header
$("#nav-sticky").sticky({
	className: "nav-stuck",
	topSpacing: 0.01,
	bottomSpacing: 0,
	zIndex: 1000
});

//TinyMCE full components
tinymce.init({
	selector: ".mceEditor",
	language: ($("html").attr("lang")=="ar" ? "ar" : "en"),
	directionality: ($("body").attr("database-language")=="ar" ? "rtl" : "ltr"),
	menubar: false,
	branding: false,
	paste_as_text: false,
	forced_root_block: false,
	table_responsive_width: true,
	table_default_attributes: {},
	table_sizing_mode: "relative",
	table_resize_bars: false,
	invalid_styles: {
		"table": "height",
		"tr": "height",
		"td": "height"
	},
	formats: {
		bold: {inline: "b"},
		underline: {inline: "u"},
		italic: {inline: "i"},
	},
	extended_valid_elements: "u,b,i",
	invalid_elements: "em strong",
	font_formats: "الخط الكوفي=Droid Arabic Kufi;Open Sans=Open Sans;Tahoma=Tahoma;Arial=Arial;Arial Black=Arial Black;Comic Sans MS=Comic Sans MS;Courier New=Courier New;Georgia=Georgia;Helvetica=Helvetica;Impact=Impact;Times New Roman=Times New Roman;Verdana=Verdana",
	fontsize_formats: "8px 10px 12px 13px 14px 15px 16px 18px 20px 22px 24px 26px 28px 30px 32px 34px 36px 38px 40px 42px 44px 46px 48px 50px",
	lineheight_formats: "1 1.2 1.4 1.6 1.8 2 2.2 2.4 2.6 2.8 3 10px 12px 14px 16px 18px 20px 22px 24px 26px 28px 30px 32px 34px 36px 38px 40px 42px 44px 46px 48px 50px",
	content_css: "../core/_core.css",
	content_style: "*{max-width:100%}body{padding:10px!important;font-size:13px}",
	plugins: "advlist autolink code directionality hr image lineheight link lists media pageembed paste preview print table visualblocks fullscreen nonbreaking pagebreak",
	toolbar: ["newdocument undo redo copy paste pastetext styleselect | fontselect fontsizeselect lineheightselect | bold italic underline strikethrough superscript subscript forecolor backcolor | alignleft aligncenter alignright alignjustify outdent indent ltr rtl | bullist numlist | link media image pageembed | table hr pagebreak nonbreaking | code preview visualblocks fullscreen"],
	visualblocks_default_state: true,
	images_upload_url: "../plugins/tinymce/upload.php",
	images_upload_base_path: location.pathname.split("/")[2] + "/",
	setup: function(editor){
		//Initialize default fonts
        editor.on("init", function(){
			if ($("html").attr("lang")=="ar"){
				this.getDoc().body.style.fontFamily = "Droid Arabic Kufi";
			} else {
				this.getDoc().body.style.fontFamily = "Open Sans";				
			}
        });
		
		//Break content inside division on paragraph
		editor.on("keydown", function (event){
			if (event.keyCode == 13 && event.shiftKey){
				event.preventDefault();
				event.stopPropagation();
				var editor =  tinymce.activeEditor;
				var element = editor.selection.getNode();
				var parent = editor.dom.getParent(element);
				var tempID = tinymce.DOM.uniqueId();
				$(parent).after("<span id=" + tempID + ">&nbsp;</span>");
				var my_span = editor.getBody().querySelector("#" + tempID);
				editor.selection.select(my_span);
				editor.selection.getRng(1).collapse(0);
				my_span.removeAttribute("id");	
				return false;
			}
		});
    },
	mobile: {
		theme: "silver"
	}
});

//Tiny MCE limited components
tinymce.init({
	selector: ".mceEditorLimited",
	menubar: false,
	language: ($("html").attr("lang")=="ar" ? "ar" : "en"),
	directionality: ($("body").attr("database-language")=="ar" ? "rtl" : "ltr"),
	branding: false,
	paste_as_text: true,
	forced_root_block: false,
	font_formats: "الخط الكوفي=Droid Arabic Kufi;Open Sans=Open Sans;Tahoma=Tahoma;Arial=Arial;Arial Black=Arial Black;Comic Sans MS=Comic Sans MS;Courier New=Courier New;Georgia=Georgia;Helvetica=Helvetica;Impact=Impact;Times New Roman=Times New Roman;Verdana=Verdana",
	fontsize_formats: "8px 9px 10px 11px 12px 13px 14px 16px 18px 24px 28px 36px",
	content_style: "@font-face{font-family:'Droid Arabic Kufi';src:url(../fonts/DroidKufi-Regular.ttf) format('truetype');font-weight:400;font-style:normal;font-display:swap}@font-face{font-family:'Open Sans';src:url(../fonts/OpenSans-Regular.ttf) format('truetype');font-weight:400;font-style:normal;font-display:swap}@font-face{font-family:'Open Sans';src:url(../fonts/OpenSans-Bold.ttf) format('truetype');font-weight:700;font-style:normal;font-display:swap}*{box-sizing:border-box;outline:0!important}body{font-family:'Droid Arabic Kufi','Open Sans',Tahoma,Sans-Serif;font-size:13px;-webkit-font-smoothing:antialiased}body:lang(ar){font-size:12px;line-height:165%}hr{border:0;height:0;border-top:1px solid rgba(0,0,0,.1);border-bottom:1px solid rgba(255,255,255,.3)}a,a:active,a:hover,a:link,a:visited{color:#606060;text-decoration:none;transition:color .25s ease,background-color .25s ease}a:hover{color:#202020;cursor:pointer}img{max-width:100%;height:auto}form,h1,h2,h3,h4,h5,h6,label,p{margin:0;padding:0;line-height:inherit}h1:lang(ar),h2:lang(ar),h3:lang(ar),h4:lang(ar),h5:lang(ar),h6:lang(ar){line-height:1.65}table{max-width:100%}",
	plugins: "autolink directionality paste",
	lineheight_formats: "10pt 12pt 14pt 16pt 18pt 20pt 22pt 24pt 26pt 36pt",
	toolbar: ["fontselect fontsizeselect | bold italic underline strikethrough superscript subscript forecolor backcolor | alignleft aligncenter alignright alignjustify ltr rtl"],
	setup: function(editor){
        editor.on("init", function(){
			this.getDoc().body.style.fontSize = "13px";
			if ($("html").attr("lang")=="ar"){
				this.getDoc().body.style.fontFamily = "Droid Arabic Kufi";
			} else {
				this.getDoc().body.style.fontFamily = "Open Sans";				
			}
        });
    },
	mobile: {
		theme: "silver"
	}
});

//TinyMCE content editor components
tinymce.init({
	selector: ".contentEditor",
	language: ($("html").attr("lang")=="ar" ? "ar" : "en"),
	directionality: ($("body").attr("database-language")=="ar" ? "rtl" : "ltr"),
	menubar: false,
	branding: false,
	paste_as_text: false,
	forced_root_block: false,
	table_responsive_width: true,
	table_default_attributes: {},
	table_sizing_mode: "relative",
	table_resize_bars: false,
	height: 400,
	invalid_styles: {
		"table": "height",
		"tr": "height",
		"td": "height"
	},
	formats: {
		bold: {inline: "b"},
		underline: {inline: "u"},
		italic: {inline: "i"},
	},
	extended_valid_elements: "u,b,i,@[data*],@[parallax*],@[rellax*],svg[*],defs[*],pattern[*],desc[*],metadata[*],g[*],mask[*],path[*],line[*],marker[*],rect[*],circle[*],ellipse[*],polygon[*],polyline[*],linearGradient[*],radialGradient[*],stop[*],image[*],view[*],text[*],textPath[*],title[*],tspan[*],glyph[*],symbol[*],switch[*],use[*]",
	invalid_elements: "em strong",
	font_formats: "الخط الكوفي=Droid Arabic Kufi;Open Sans=Open Sans;Tahoma=Tahoma;Arial=Arial;Arial Black=Arial Black;Comic Sans MS=Comic Sans MS;Courier New=Courier New;Georgia=Georgia;Helvetica=Helvetica;Impact=Impact;Times New Roman=Times New Roman;Verdana=Verdana",
	fontsize_formats: "8px 10px 12px 13px 14px 15px 16px 18px 20px 22px 24px 26px 28px 30px 32px 34px 36px 38px 40px 42px 44px 46px 48px 50px",
	lineheight_formats: "1 1.2 1.4 1.6 1.8 2 2.2 2.4 2.6 2.8 3 10px 12px 14px 16px 18px 20px 22px 24px 26px 28px 30px 32px 34px 36px 38px 40px 42px 44px 46px 48px 50px",
	content_css: "../core/_core.css?v=" + theme_version + ", ../website/_classes.css?v=" + theme_version,
	content_style: "*{max-width:100%}body{padding:10px!important;font-size:13px}",
	plugins: "advlist autolink advcode directionality hr image lineheight link lists media pageembed paste preview print table visualblocks fullscreen nonbreaking pagebreak prismatecs",
	toolbar: ["newdocument undo redo copy paste pastetext styleselect | fontselect fontsizeselect lineheightselect | bold italic underline strikethrough superscript subscript forecolor backcolor | alignleft aligncenter alignright alignjustify outdent indent ltr rtl | bullist numlist | link media image pageembed | table hr pagebreak nonbreaking | code preview visualblocks fullscreen | classes animate division removewrapper removeformat"],
	visualblocks_default_state: true,
	images_upload_url: "../plugins/tinymce/upload.php",
	images_upload_base_path: location.pathname.split("/")[2] + "/",
	setup: function(editor){
		//Initialize default fonts
        editor.on("init", function(){
			if ($("html").attr("lang")=="ar"){
				this.getDoc().body.style.fontFamily = "Droid Arabic Kufi";
			} else {
				this.getDoc().body.style.fontFamily = "Open Sans";				
			}
        });
		
		//Break content inside division on paragraph
		editor.on("keydown", function (event){
			if (event.keyCode == 13 && event.shiftKey){
				event.preventDefault();
				event.stopPropagation();
				var editor =  tinymce.activeEditor;
				var element = editor.selection.getNode();
				var parent = editor.dom.getParent(element);
				var tempID = tinymce.DOM.uniqueId();
				$(parent).after("<span id=" + tempID + ">&nbsp;</span>");
				var my_span = editor.getBody().querySelector("#" + tempID);
				editor.selection.select(my_span);
				editor.selection.getRng(1).collapse(0);
				my_span.removeAttribute("id");	
				return false;
			}
		});
    },
	mobile: {
		theme: "silver"
	}
});

//Main panel (index) icons
$(".menu-panel .panel-collapse").on("shown.bs.collapse", function(e){
	scrollToView($(this).closest(".panel").find("a"), $("#nav-sticky").outerHeight() + 10, "start");
});

//Portal side icons [Fixed side icons]
$(".side-panel .panel-collapse").on("shown.bs.collapse", function(e){
	scrollToView($(this).closest(".panel").find("a"), 0, "start");
});

//Scroll to active side menu
if ($(".side-panel ul.icons_container li.active").length){
	scrollToView($(".side-panel ul.icons_container li.active"), -$("#nav-sticky").outerHeight(), "center");
}

//Small screen side menu
$(".side-menu-overlay").click(function(){
	$(".side-menu-overlay").fadeOut(200);
	$(".menu-container").removeClass("opened");
});

$("[side-menu-toggle]").click(function(){
	$(".side-menu-overlay").fadeIn(200);
	$(".menu-container").addClass("opened");
});

//Trigger window resize on tab shown
//Fix for tabs containing CRUD table to trigger "doubleScrollbar"
$(".nav-tabs").on("shown.bs.tab", function(event){
	$(window).trigger('resize');
});

//Hide Loading Cover
hideLoadingCover();