<style>
.subtitle p {
	font-size: 12px;
}

.modules_list {
	margin: 0;
	padding: 0;
	list-style-type: none;
}

.modules_list li {
	padding: 5px;
	border: 1px solid #c8c8c8;
	background: #f8f8f8;
	border-radius: 3px;
	margin-bottom: 5px;
	display: flex;
	align-items: center;
}

.modules_list li .handle {
	display: none;
	font-size: 20px;
	cursor: move;
}

.modules_list li span {
	flex-grow: 1;
	padding: 4px;
}

.modules_list li .btn {
	width: 30px;
	height: 30px;
	display: flex;
	justify-content: center;
	align-items: center;
}

.modules_list li .btn:not(.btn-edit) {
	display: none;
}

.modules_list li:last-child {
	margin-bottom: 0;
}

.modules_list.empty:after,
.modules_list:empty:after {
	content: "<?=readLanguage(builder,empty_modules)?>";
	display: flex;
	justify-content: center;
	align-items: center;
	height: 30px;
}

.modules_list.used li.fixed {
	font-weight: bold;
	border: 1px solid #ccc;
	background: #eee;
	text-align: center;
}

.modules_list.used li .handle {
	display: block;
}

.modules_list.used li span {
	text-align: center;
}

.modules_list.used li .btn-remove {
	display: block;
}

.modules_list.available {
	max-height: 200px;
	overflow-y: scroll;
	padding: 0 5px 0 0;
}

.modules_list.available::-webkit-scrollbar {
	width: 5px;
}

.modules_list.available::-webkit-scrollbar-track {
	background: #f1f1f1;
	border-radius: 10px;
}

.modules_list.available::-webkit-scrollbar-thumb {
	background: #888;
	border-radius: 10px;
}

.modules_list.available:lang(ar) {
	padding: 0 0 0 5px;
}

.modules_list.available li.used {
	font-weight: bold;
	border: 1px solid #ccc;
	background: #eee;
}

.modules_list.available li .btn-append {
	display: block;
}

.modules_list.available:lang(ar) li .btn-append i {
	transform: scale(-1);
}

.modules_list.available li.no-margin {
	margin-bottom: 0;
}

#modules_search {
	border-radius: 3px;
	margin-bottom: 10px;
}
</style>

<div class="row grid-container">

<div class="col-md-10 grid-item">
<div class=subtitle><p><?=readLanguage(builder,layout_modules_available)?></p></div>
	<input type=text id=modules_search placeholder="<?=readLanguage(general,search)?>">
	<div class=page_container>
		<? $modules_result = mysqlQuery("SELECT * FROM " . $suffix . "website_modules_custom WHERE FIND_IN_SET($modules_type,type)");
		if (mysqlNum($modules_result)){
			while ($module_entry = mysqlFetch($modules_result)){
				$modules_list .= "<li data-module='" . $module_entry["uniqid"] . "' search-normalized='" . $module_entry["uniqid"] . " " . normalizeString($module_entry["placeholder"]) . " " . strtolower($module_entry["placeholder"]) . "'>
					<i class='fas fa-bars handle'></i>
					<span>" . $module_entry["placeholder"] . "</span>
					<a class='btn btn-warning btn-sm btn-edit' href='website_custom_modules.php?edit=" . $module_entry["id"] . "&inline' data-fancybox data-type=iframe><i class='fas fa-pen'></i></a>&nbsp;
					<a class='btn btn-danger btn-sm btn-remove' onclick='moduleRemove(this)'><i class='fas fa-times'></i></a>
					<a class='btn btn-primary btn-sm btn-append' onclick='moduleAppend(this)'><i class='fas fa-chevron-right'></i></a>
				</li>";
			}
		} ?>
		<ul class="modules_list available"><?=$modules_list?></ul>
	</div>
</div>

<div class="col-md-10 grid-item">
<div class=subtitle><p><?=readLanguage(builder,layout_modules_used)?></p></div>
		<div class=page_container>
		<ul class="modules_list used"><? if ($modules_content){ ?><li class=fixed data-module="content"><span><?=readLanguage(builder,page_content)?></span></li><? } ?></ul>
	</div>
</div>

</div>

<script>
//Modules search
$("#modules_search").on("input", function(){
	var search = $(this).val().toLowerCase();
	var mapObj = {آ:"ا", أ:"ا", إ:"ا", ى:"ي", ة:"ه", ؤ:"و"};
	search = search.replace(/آ|أ|إ|ى|ة|ؤ/gi, function(matched){
	  return mapObj[matched];
	});
	$(".modules_list").removeClass("empty");
	if (search.length > 0){
		$(".modules_list.available li").removeClass("no-margin").hide();
		$(".modules_list.available li[search-normalized*='" + search + "']").show();
		$(".modules_list.available li:visible:last").addClass("no-margin");
		if (!$(".modules_list.available li:visible").length){
			$(".modules_list.available").addClass("empty");
		}
	} else {
		$(".modules_list.available li").removeClass("no-margin").show();
	}
});

//Append module (accepts target as button or module name)
function moduleAppend(target, before=false){
	var module = (typeof target === "object" ? $(target).parent() : $(".modules_list.available li[data-module='" + target + "']"));
	if (module){
		var clone = module.clone();
		clone.removeClass("used");
		if (before){
			var content_before = $(".modules_list.used li[data-module='content']").prev();
			if (content_before.length){
				clone.insertAfter(content_before);
			} else {
				$(".modules_list.used").prepend(clone);
			}
		} else {
			$(".modules_list.used").append(clone);
		}
		module.addClass("used");
	}
}

//Remove module
function moduleRemove(button){
	var module = $(button).parent();
	
	//Remove used class
	if ($(".modules_list.used li[data-module='" + module.data("module") + "']").length==1){
		$(".modules_list.available li[data-module='" + module.data("module") + "']").removeClass("used");
	}

	module.remove();
}

//Initialize sortable
$(".modules_list.used").sortable({
	placeholder: "ui-state-highlight",
	handle: ".handle",
	tolerance: "pointer",
    start: function(event, ui){
		ui.item.height("auto");
		$(".ui-state-highlight").height(ui.item.height()).width(ui.item.width());
    }
});

//Build used modules values
function moduleBuild(){
	var modules = [];
	$(".modules_list.used li").each(function(){
		modules.push($(this).data("module"));
	});
	$("<?=$modules_input?>").val(modules.join(","));
}

//Trigger build before submit
function onBeforeValidation(){
	moduleBuild();
}

//Load saved modules
<? if ($modules_entry){ ?>
var modules_entry = "<?=$modules_entry?>";
var modules_array = modules_entry.split(",");
var content_index = modules_array.indexOf("content");
modules_array.forEach(function(item, index){
	if (item=="content"){
		return;
	}
	var position = (index < content_index);
	moduleAppend(item, position);
});
<? } ?>
</script>