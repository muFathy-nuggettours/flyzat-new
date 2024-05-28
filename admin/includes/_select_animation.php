<script src="../plugins/animate-os.min.js?v=<?=$system_settings["system_version"]?>"></script>
<link href="../plugins/animate-os.min.css?v=<?=$system_settings["system_version"]?>" rel="stylesheet">

<style>
.animation_input {
	display: flex;
}

.animation_input span {
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

.animation_input .btn {
	margin: 0 0 0 5px;
}

.animation_input:lang(ar) .btn {
	margin: 0 5px 0 0;
}

#animationSelectionModal .inline_input li {
	flex-basis: 25%;
}

.animation_preview {
	height: 200px;
	border: 1px solid #ddd;
	background: #f8f8f8;
	margin-top: 15px;
	display: flex;
	justify-content: center;
	align-items: center;
	position: relative;
}

.animation_preview .btn {
	position: absolute;
	top: 5px;
	left: 5px;
}
</style>

<?
$cms_aos_animations = array(
	"fade",
	"fade-up",
	"fade-down",
	"fade-left",
	"fade-right",
	"fade-up-right",
	"fade-up-left",
	"fade-down-right",
	"fade-down-left",
	"flip-up",
	"flip-down",
	"flip-left",
	"flip-right",
	"slide-up",
	"slide-down",
	"slide-left",
	"slide-right",
	"zoom-in",
	"zoom-in-up",
	"zoom-in-down",
	"zoom-in-left",
	"zoom-in-right",
	"zoom-out",
	"zoom-out-up",
	"zoom-out-down",
	"zoom-out-left",
	"zoom-out-right",
);

$cms_animation_easings = array(
	"linear",
	"ease",
	"ease-in",
	"ease-out",
	"ease-in-out",
	"ease-in-back",
	"ease-out-back",
	"ease-in-out-back",
	"ease-in-sine",
	"ease-out-sine",
	"ease-in-out-sine",
	"ease-in-quad",
	"ease-out-quad",
	"ease-in-out-quad",
	"ease-in-cubic",
	"ease-out-cubic",
	"ease-in-out-cubic",
	"ease-in-quart",
	"ease-out-quart",
	"ease-in-out-quart",
);
?>

<!-- Animation Selection Modal -->
<div class="modal fade" id=animationSelectionModal><div class=modal-dialog><div class=modal-content>
	<div class=modal-header>
		<button type=button class=close data-dismiss=modal><span>&times;</span></button>
		<h4 class=modal-title><?=readLanguage(builder,animation_selector)?></h4>
	</div>
	<div class=modal-body>
		<ul class=inline_input json-fixed-data=fixed_data>
			<li>
				<p><?=readLanguage(builder,animation)?></p>
				<select data-animation-option=aos>
					<option value=""><?=readLanguage(builder,none)?></option>
					<? foreach ($cms_aos_animations AS $animation){
						print "<option value='$animation'>$animation</option>";
					} ?>
				</select>
			</li>
			<li>
				<p><?=readLanguage(builder,animation_delay)?></p>
				<div class=input-addon><input type=number data-animation-option=aos-delay data-animation-default=0><span after>ms</span></div>
			</li>
			<li>
				<p><?=readLanguage(builder,animation_duration)?></p>
				<div class=input-addon><input type=number data-animation-option=aos-duration data-animation-default=500><span after>ms</span></div>
			</li>
			<li>
				<p><?=readLanguage(builder,animation_easing)?></p>
				<select data-animation-option=aos-easing data-animation-default=linear>
					<? foreach ($cms_animation_easings AS $easing){
						print "<option value='$easing'>$easing</option>";
					} ?>
				</select>
			</li>
			<li>
				<p><?=readLanguage(builder,animation_mirror)?></p>
				<select data-animation-option=aos-mirror data-animation-default=true>
					<option value="true"><?=$data_no_yes[1]?></option>
					<option value="false"><?=$data_no_yes[0]?></option>
				</select>
			</li>
			<li>
				<p><?=readLanguage(builder,animation_once)?></p>
				<select data-animation-option=aos-once data-animation-default=false>
					<option value="true"><?=$data_no_yes[1]?></option>
					<option value="false"><?=$data_no_yes[0]?></option>
				</select>
			</li>
		</ul>
		<div class=animation_preview><a class="btn btn-primary btn-sm" onclick="animationUpdatePreview(animationBuildProperties())"><i class="fas fa-redo"></i></a><h1><?=readLanguage(builder,preview)?></h1></div>
	</div>
	<div class=modal-footer>
		<button type=button class="btn btn-default btn-sm" data-dismiss=modal><?=readLanguage(plugins,message_cancel)?></button>
		<button type=button class="btn btn-primary btn-sm" onclick="animationModalSave()"><?=readLanguage(plugins,message_save)?></button>
	</div>
</div></div></div>

<script>
//Bind animation selection to an input (properties are sent in base64)
function animationBind(target){
	var target = $(target);
	var input = $("<input type=hidden>");
	
	//Bind input name & data-name for multiple data bind
	var input_name = target.attr("animation-bind");
	input.attr("name", input_name).attr("data-name", input_name);

	//Load data if set
	var entry = null;
	var properties = target.attr("animation-properties");

	//Parse properties from base64
	properties = (properties ? atob(properties) : "");
	
	//Assign properties to current input
	input.val(properties);
	
	//Get animation text
	entry = animationTextFromProperties((properties ? JSON.parse(properties) : null));
	target.html("<span>" + entry + "</span>");
	
	//Assign input to target
	target.data("input", input);
	
	//Animation selection button
	var button = $("<a class='btn btn-primary btn-sm'><i class='fas fa-running'></i></a>");
	button.on("click", function(){
		animationShowModal(target);
	});
	
	//Append button and input
	input.insertAfter(target);
	target.append(button);
}

//Show animation selection modal
function animationShowModal(target=null, parameters=null){
	var modal = $("#animationSelectionModal");
	
	//Set input values if input has properties
	var properties = (target ? target.data("input").val() : parameters);
	if (properties){
		properties = JSON.parse(properties);
		for (const [key, value] of Object.entries(properties)) {
			$("[data-animation-option=" + key + "]").val(value);
		}
	
	//Otherwise assign default values
	} else {
		$("[data-animation-option]").each(function(){
			var value = $(this).attr("data-animation-default");
			$(this).val(value);
		});
	}
	
	//Attach target division and input (To be used when saving or closing)
	modal.data("target", target).modal("show");
	
	//Run animation on modal show
	modal.on("shown.bs.modal", function (){
		animationUpdatePreview(properties);
	});
	
	//Return modal object (for use in tinyMCE)
	return modal;
}

//Save & hide animation modal
function animationModalSave(){
	var modal = $("#animationSelectionModal");
	var properties = animationBuildProperties();
	var inputValue = (properties ? JSON.stringify(properties) : "");

	//Save classes to input
	if (modal.data("target")){
		modal.data("target").find("span").text(animationTextFromProperties(properties));
		modal.data("target").data("input").val(inputValue).trigger("change");
	
	//Save classes to tinyMCE dom object
	} else {
		modal.trigger("saved", inputValue);
	}
	
	modal.modal("hide");
}

//Update animation preview in modal
function animationUpdatePreview(properties=null){
	//Create preview element
	var target = $("<h1><?=readLanguage(builder,preview)?></h1>");
	var container = $(".animation_preview");
	
	//Remove previous elements and reappend
	container.find("h1").remove();
	container.append(target);
	
	//Set animation properties
	if (properties){
		//Assign properties attributes
		for (const [key, value] of Object.entries(properties)) {
			target.attr("data-" + key, value);
		}

		//Initialize animate on scroll
		AOS.init();
	}
}

//Get animation properties from modal
function animationBuildProperties(){
	var properties = {};
	$("[data-animation-option]").each(function(){
		var property = $(this).attr("data-animation-option");
		var value = $(this).val();
		properties[property] = value;
	});
	return (properties.aos ? properties : null);
}

//Set animation properties for an already binded element [For modules]
function animationSetProperties(target, properties){
	properties = (properties ? atob(properties) : "");
	
	//Assign properties to current input
	target.data("input").val(properties);
	
	//Get animation text
	entry = animationTextFromProperties((properties ? JSON.parse(properties) : null));
	
	target.find("span").text(entry);
}

//Return text representation from given data
function animationTextFromProperties(data){
	return (data ? data.aos + " " + data["aos-easing"] + " " + data["aos-duration"] + "ms" : "<?=readLanguage(builder,none)?>");
}

//Update preview when changin any of the animation properties
$("[data-animation-option]").on("input", function(){
	animationUpdatePreview(animationBuildProperties());
});

//Initialize on load
$("[animation-bind]").each(function(){
	animationBind($(this));
});
</script>