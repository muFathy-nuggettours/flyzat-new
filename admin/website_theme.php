<? include "system/_handler.php";

$mysqltable = "website_theme";
$base_name = basename($_SERVER["SCRIPT_FILENAME"],".php");
checkPermissions($base_name);

$original_colors = array(
	"#0c1a26", "#0e2133", "#10263b", "#112b44", "#143350",
	"#112334", "#132d46", "#163451", "#173b5d", "#1b466e",
	"#173047", "#1a3e60", "#1e476f", "#20517f", "#256096",
);

//==== EDIT Options ====
if ($post["token"] && $post["action"]=="options"){
	//Update Colors
	mysqlQuery("UPDATE $mysqltable SET content = CASE
		WHEN title='colors' THEN '" . implode(",",$post["colors"]) . "'
		WHEN title='hue' THEN '" . intval($post["hue"]) . "'
		WHEN title='saturation' THEN '" . intval($post["saturation"]) . "'
		WHEN title='brightness' THEN '" . intval($post["brightness"]) . "'
		WHEN title='contrast' THEN '" . intval($post["contrast"]) . "'
		ELSE content
	END");

	//Update footer
	mysqlQUery("UPDATE " . $suffix . "website_information SET content='" . $post["module_footer"] . "' WHERE title='module_footer'");

	//Build CSS & modules classes
	buildCSSClasses();
	buildCSSModules();
	buildWebsiteTheme();

	$success = readLanguage(records,updated);
}


//==== Import Components ====
if ($post["token"] && $post["action"]=="import"){
	$source = fileUpload($_FILES["source"], "archives/");
	if ($source){
		$can_import = checkCanImport($source, $post["replce_custom"], $post["replce_built"]);
		if ($can_import===true){
			$result = importComponents(basename($source, ".zip"));
			buildWebsiteTheme();
			$success = "Components imported successfully";
		} else {
			$error = "<ul><li>" . implode("</li><li>", $can_import) . "</li></ul>";
		}
	}
}

//Read and Set Operation
if ($success){ $message = "<div class='alert alert-success'>" . $success . "</div>"; }
if ($error){ $message = "<div class='alert alert-danger'>" . $error . "</div>"; }

//Reload website theme
$theme = fetchData($mysqltable);

include "_header.php";?>

<div class=title><?=getPageTitle($base_name)?></div>
<?=$message?>

<!-- Options -->
<form method=post enctype="multipart/form-data">
<input type=hidden name=token value="<?=$token?>">
<input type=hidden name=action value="options">

<div class=subtitle>Colors</div>
<table class=data_table>
<tr>
	<td class=title>Colors Palette:</td>
	<td>
		<div class="row grid-container">
			<div class="col-md-12 grid-item">
				<div>
					<span>Hue</span>
					<input filter-slider class=range_slider id=hue name=hue type=range min=0 max=360 value=<?=$theme["hue"]?>>
				</div>
				<div>
					<span>Saturation</span>
					<input filter-slider class=range_slider id=saturation name=saturation type=range min=0 max=500 value=<?=$theme["saturation"]?>>
				</div>
				<div>
					<span>Brightness</span>
					<input filter-slider class=range_slider id=brightness name=brightness type=range min=0 max=500 value=<?=$theme["brightness"]?>>
				</div>				
				<div>
					<span>Contrast</span>
					<input filter-slider class=range_slider id=contrast name=contrast type=range min=0 max=500 value=<?=$theme["contrast"]?>>
				</div>
				<div class=margin-top>
					<button type=button class="btn btn-primary btn-sm" onclick="setDefaultColors()">Reset Colors</button>
					<script>
					function setDefaultColors(){
						//Reset filters
						$("#hue").val(0);
						$("#saturation").val(100);
						$("#brightness").val(100);
						$("#contrast").val(100);
						updateFilters();
						
						//Reset colors
						var defaultColors = "<?=implode(",",$original_colors)?>".split(",");
						defaultColors.forEach(function(item, index){
							console.log(index,item);
							$("input[color-index=" + index + "]").val(item);					
						});
					}
					</script>
				</div>
			</div>
			<div class="col-md-8 grid-item">
				<canvas id=canvas width=320 height=192 style="margin-bottom:-5px; border-radius:3px"></canvas>
				<img id=palette src="images/palette.png" style="display:none">
				<script>
				var c = document.getElementById("canvas");
				var ctx = c.getContext("2d");
				var img = document.getElementById("palette");
				
				//Initialize image
				window.onload = function() {
					updateFilters();
				};
				
				//Slider change
				$("[filter-slider]").on("input",function(){
					updateFilters();
					updateColors();
				});
				
				//Update image filters
				function updateFilters(){
					var hue = $("#hue").val();
					var saturation = $("#saturation").val();
					var brightness = $("#brightness").val();
					var contrast = $("#contrast").val();
					ctx.filter = "hue-rotate(" + hue + "deg) saturate(" + saturation + "%) brightness(" + brightness + "%) contrast(" + contrast + "%)";
					ctx.drawImage(img, 0, 0);					
				}
				
				//Convert RGB color to HEX
				function rgbToHex(r,g,b) {
					return "#" + ((1 << 24) + (r << 16) + (g << 8) + b).toString(16).slice(1);
				}
				
				//Update colors
				function updateColors(){
					var colors_coordinates = [
						[32,32],[96,32],[160,32],[224,32],[288,32],
						[32,96],[96,96],[160,96],[224,96],[288,96],
						[32,160],[96,160],[160,160],[224,160],[288,160]
					];
					colors_coordinates.forEach(function(item, index){
						var data = ctx.getImageData(item[0], item[1], 1, 1).data;
						var color = rgbToHex(data[0], data[1], data[2]);
						$("input[color-index=" + index + "]").val(color);
					});
				}
				</script>		
			</div>
		</div>
	</td>
</tr>	
<tr>
	<td class=title>Separated Colors:</td>
	<td>
		<div style="margin:-3px">
		<? for ($x = 1; $x <= 15; $x++){
			$colors = explode(",", $theme["colors"]);
			$index = $x - 1;
			echo "<input type=color name=colors[] value='" . $colors[$index] . "' color-index=$index style='margin:3px'>";
			echo ($x % 5 === 0 ? "<br>" : "");
		} ?>
		</div>
	</td>
</tr>
</table>

<div class=subtitle>Settings</div>
<table class=data_table>
<tr>
	<td class=title>Footer Module:</td>
	<td>
		<? $information = fetchData($suffix . "website_information"); ?>
		<select name=module_footer id=module_footer>
			<option value=""></option>
			<?=populateData("SELECT * FROM " . $suffix . "website_modules_custom", "uniqid", "placeholder")?>
		</select>
		<script>setSelectValue("#module_footer", "<?=$information["module_footer"]?>")</script>
		<script>$("#module_footer").select2({
			placeholder: "Please select the default footer module",
			allowClear: true
		})</script>
	</td>
</tr>
</table>

<div class=submit_container><input type=button class=submit value="<?=readLanguage(records,update)?>"></div>
</form>

<!-- Import -->
<div class=subtitle>Import Components</div>
<form method=post enctype="multipart/form-data" action="<?=$action?>">
<input type=hidden name=token value="<?=$token?>">
<input type=hidden name=action value="import">

<div class=data_table_container><table class=data_table>
<tr>
	<td class=title>Source File: <i class=requ></i></td>
	<td colspan=3><input type=file name=source accept=".zip" data-validation=required></td>
</tr>
<tr>
	<td class=title>Replace Existing:</td>
	<td>
		<div class=switch><label><?=$data_no_yes[0]?><input type=checkbox name=replce_custom value=1><span class=lever></span><?=$data_no_yes[1]?></label></div>
	</td>
	<td class=title>Replace Built-In:</td>
	<td>
		<div class=switch><label><?=$data_no_yes[0]?><input type=checkbox name=replce_built value=1><span class=lever></span><?=$data_no_yes[1]?></label></div>
	</td>
</tr>
</table></div>

<div class=submit_container><input type=button class=submit value="Import"></div>
</form>

<? include "_footer.php";