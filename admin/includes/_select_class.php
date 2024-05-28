<link href="../website/_classes.css?v=<?=$website_theme["version"]?>" rel="stylesheet">

<style>
.class_input {
	display: flex;
}

.class_input span {
	flex-grow: 1;
	display: flex;
	align-items: center;
	border: 1px solid #eee;
	padding: 5px;
	height: 30px;
	line-height: 30px;
	vertical-align: middle;
	overflow: hidden;
}

.class_input .btn {
	margin: 0 0 0 5px;
}

.class_input:lang(ar) .btn {
	margin: 0 5px 0 0;
}

.classes_assigned {
	list-style-type: none;
	list-style-position: inside;
	padding: 0 0 5px 5px;
	border: 1px solid #eee;
	margin-top: 10px;
}

.classes_assigned:empty {
	display: none;
}

.classes_assigned li {
	display: inline-block;
	padding: 5px;
	border: 1px solid #ccc;
	background: #eee;
	margin: 5px 5px 0 0;
}

.class_preview_container {
	display: flex;
	align-items: center;
	justify-content: center;
	position: relative;
	border: 1px solid #ddd;
	padding: 20px;
	margin-top: 10px;
	overflow: hidden;
}

.class_preview_container.division .class_preview {
	width: 100%;
	height: 200px;
}

.preview_options {
	display: flex;
	align-items: center;
	margin-top: 10px;
	border: 1px solid #eee;
	background: #fefefe;
	padding: 10px;
}
</style>

<!-- Class Selection Modal -->
<div class="modal fade" id=classSelectionModal><div class=modal-dialog><div class=modal-content>
	<div class=modal-header>
		<button type=button class=close data-dismiss=modal><span>&times;</span></button>
		<h4 class=modal-title><?=readLanguage(builder,css_class_selection)?></h4>
	</div>
	<div class=modal-body>
		<? $classes_result = mysqlQuery("SELECT * FROM website_classes ORDER BY priority DESC"); ?>
		<? if (!mysqlNum($classes_result)){
			print noContent(true, readLanguage(builder,empty_classes));
		} else { ?>
		<!-- Classes list -->
		<div class=d-flex>
			<select classes-list>
				<? while ($classes_entry = mysqlFetch($classes_result)){
					print "<option existing=true value='" . $classes_entry["class"] . "'>" . $classes_entry["placeholder"] . "</option>";
				} ?>
			</select>
			&nbsp;&nbsp;<input type=button class="btn btn-primary btn-sm" value="<?=readLanguage(operations,insert)?>" onclick="classInsert()">
		</div>
		
		<!-- Assigned classes -->
		<ul class=classes_assigned></ul>
		
		<!-- Preview division -->
		<div class="class_preview_container division"><div class=class_preview><?=readLanguage(builder,preview)?></div></div>
		
		<!-- Preview options -->
		<div class=preview_options>
			<div class=radio_container>
				<label><input name=radios type=radio onchange="$('.class_preview_container').addClass('division')" checked><span><?=readLanguage(builder,preview_block)?></span></label>
				<label><input name=radios type=radio onchange="$('.class_preview_container').removeClass('division')"><span><?=readLanguage(builder,preview_inline)?></span></label>
			</div>
			<div class=check_container>
				<label><input type=checkbox class=filled-in onchange="$('.class_preview_container > div').toggleClass('active')"><span><?=readLanguage(builder,preview_active)?></span></label>
			</div>
		</div>
		<? } ?>
	</div>
	<div class=modal-footer>
		<button type=button class="btn btn-default btn-sm" data-dismiss=modal><?=readLanguage(plugins,message_cancel)?></button>
		<? if (mysqlNum($classes_result)){ ?>
		<button type=button class="btn btn-primary btn-sm" onclick="classModalSave()"><?=readLanguage(plugins,message_save)?></button>
		<? } ?>
	</div>
</div></div></div>

<script>
var classes_assigned = [];
var classes_assigned_placeholders = [];

//Bind class selection to an input
function classBind(target){
	var target = $(target);
	var input = $("<input type=hidden>");
	
	//Bind input name & data-name for multiple data bind
	var input_name = target.attr("class-bind");
	input.attr("name", input_name).attr("data-name", input_name);

	//Load data if set
	var properties = target.attr("class-properties");
	input.val(properties);
	target.html("<span>" + classBuildPlaceholders(properties) + "</span>");
	
	//Assign input to target
	target.data("input", input);
	
	//class selection button
	var button = $("<a class='btn btn-primary btn-sm'><i class='fas fa-paint-brush'></i></a>");
	button.on("click", function(){
		classShowModal(target);
	});
	
	//Append button and input
	input.insertAfter(target);
	target.append(button);
}

//Show class selection modal
function classShowModal(target=null, classes=null){
	var modal = $("#classSelectionModal");

	//Remove existing assigned classes
	$(".classes_assigned li").remove();
	classesBuild();
	
	//Set classes if input has values
	if (target){
		var classes = target.data("input").val();
		if (classes){
			classes.split(" ").forEach(function(item, index){
				classInsert(item);
			});	
		}
	
	//Insert classes if sent with show event
	} else if (classes){
		classes.split(" ").forEach(function(item, index){
			classInsert(item);
		});
	}
	
	//Attach target division and input (To be used when saving or closing)
	modal.data("target", target).modal("show");
	
	//Return modal object (for use in tinyMCE)
	return modal;
}

//Insert class
function classInsert(className=null){
	var classOption = (className ? $("[classes-list] option[value='" + className + "']") : $("[classes-list] option:selected"));
	var className = (classOption.length ? classOption.val() : className);
	var classPlaceholder = (classOption.length ? classOption.text() : className);
	
	//Create & append assigned class list item
	var classItem = $("<li data-class='" + className + "' data-class-placeholder='" + classPlaceholder + "'><div class=flex-center><span class=flex-grow-1>" + classPlaceholder + "</span>&nbsp;&nbsp;<a class='fas fa-times'></a></div></li>");
	classItem.find("a").on("click", function(){
		$(this).parents("li").remove();
		classesBuild();
	});
	$(".classes_assigned").append(classItem);
	
	//Build classes values
	classesBuild();
}

//Build classes values
function classesBuild(){
	classes_assigned = [];
	classes_assigned_placeholders = [];
	$(".classes_assigned li").each(function(){
		classes_assigned.push($(this).data("class"));
		classes_assigned_placeholders.push($(this).data("class-placeholder"));
	});
	
	var classes_text = classes_assigned.join(" ");
	$(".class_preview").attr("class", "class_preview").addClass(classes_text);
	
	return classes_text;
}

//Save & hide class modal
function classModalSave(){
	var modal = $("#classSelectionModal");
	var classes = classes_assigned.join(" ");
	
	//Save classes to input
	if (modal.data("target")){
		modal.data("target").find("span").html(classBuildPlaceholders(classes));
		modal.data("target").data("input").val(classes).trigger("change");
	
	//Save classes to tinyMCE dom object
	} else {
		modal.trigger("saved", classes);
	}
	
	modal.modal("hide");	
}

//Set class properties for an already binded element [For modules]
function classSetProperties(target, properties){
	target.data("input").val(properties);
	target.find("span").html(classBuildPlaceholders(properties));
}

//Return render representation from given classes
function classBuildPlaceholders(classes){
	var placeholders = [];
	if (classes){
		classes.split(" ").forEach(function(item, index){
			var classItem = $("[classes-list] option[value='" + item + "']");
			var isExisting = classItem.attr("existing");
			var classEntry = (classItem.text() && isExisting ? "<a href='website_classes.php?inline&class=" + item + "' data-fancybox data-type=iframe>&nbsp;" + classItem.text() + "</a>" : item);
			placeholders.push(classEntry);
		});
	}
	return (classes ? placeholders.join(", ") : "<?=readLanguage(builder,basic)?>");
}

//Initialize sortable on assigned classes
$(".classes_assigned").sortable();

//Initialize on load
$("[class-bind]").each(function(){
	classBind($(this));
});

//Bind select2
$("[classes-list]").select2({
	dropdownParent: $("#classSelectionModal"),
	tags: true
});
</script>