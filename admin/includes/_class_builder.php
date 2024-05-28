<style>
[data-unit] {
	width: 80px;
}

.pseudo_classes {
	margin-bottom: 10px;
}

.tab-inline-header {
	background: #fefefe;
	border: 1px solid #eee;
	padding: 5px;
	border-radius: 5px;
}

.preview_container {
	display: flex;
	align-items: center;
	justify-content: center;
	position: relative;
	padding: 20px;
}

.preview_container.division .preview {
	width: 100%;
	height: 200px;
}

.preview_options {
	margin-top: 10px;
	border: 1px solid #eee;
	background: #fefefe;
	border-radius: 3px;
	padding: 5px;
	display: flex;
	align-items: center;
}

.preview_options .radio_container {
	flex-grow: 1;
}

.fancybox-slide--html .fancybox-content {
	padding: 20px;
	overflow: hidden;
	width: calc(100vw - 40px);
	height: calc(100vh - 40px);
	position: relative;
	border-radius: 3px;
}

.fancybox-slide--html .fancybox-content .preview {
	height: 100%;
}

textarea.css_code {
	height: 300px;
	box-shadow: initial;
	border-radius: 3px;
	background: #fff;
	border: 1px solid #c8c8c8 !important;
	direction: ltr;
	text-align: left;
}

.components_list {
	list-style-type: none;
	list-style-position: inside;
	padding: 0;
	margin-top: 10px;
}

.components_list li {
	display: flex;
	align-items: center;
	background: #fefefe;
	cursor: pointer;
	border-radius: 3px;
	border: 1px solid #eee;
	padding: 5px;
	margin-bottom: 5px;
}

.components_list li.active {
	background: #aaccef;
	border: 1px solid #5a8caf;
}

.components_list li:last-child {
	margin-bottom: 0;
}

.components_list li .sortable {
	font-size: 16px;
	display: flex;
	justify-content: center;
	align-items: center;
}

.components_list li span {
	flex-grow: 1;
}

.components_list li:hover:not(.active) {
	background: #fafafa;
}

.component_empty {
	padding: 20px;
	border: 1px solid #eee;
	border-radius: 3px;
	text-align: center;
	background: #fbfbfb;
}

.background_component,
.text_shadow_component,
.box_shadow_component,
.filter_component {
	display: none;
}

.gradient_bar_container {
	background-image: url(data:image/gif;base64,R0lGODlhFgAWAJEAAGZmZpmZmf///wAAACH5BAEAAAIALAAAAAAWABYAAAItjI8Byw0JnYRJOopsw0czbngLqIik+IDoaapoy62ux9KzVuO3lfP79LsEN6ACADs=);
	height: 20px;
	border-radius: 100px;
	width: calc(100% - 20px);
	margin: 0 10px 15px 10px;
}

.gradient_bar {
	position: relative;
	height: 20px;
	border-radius: 100px;
}

.gradient_bar .gradient_handle {
	margin: 0 -10px 0 -10px;
	width: 20px;
	height: 20px;
	position: absolute !important;
	top: 0 !important;
	left: 0;
	border: 3px solid #fff;
	box-shadow: 1px 1px 4px rgba(0, 0, 0, .2);
	border-radius: 50%;
	transform: scale(1.2);
}

.gradient_colors_list {
	list-style-type: none;
	list-style-position: inside;
	padding: 0;
	margin-top: 10px;
}

.gradient_colors_list li {
	display: flex;
	align-items: center;
	background: #fff;
	border-radius: 3px;
	border: 1px solid #c8c8c8;
	padding: 10px;
	margin-bottom: 5px;
}
</style>

<style id=css></style>

<?
//Initialize selection lists values
$css_lists["units"] = array("px", "%", "em", "cm", "mm", "in", "pc", "vw", "vh", "vmin", "vmax");
$css_lists["gradient_types"] = array("linear", "radial");
$css_lists["radial_shapes"] = array("circle", "ellipse", "circle closest-side", "circle closest-corner", "circle farthest-side", "circle farthest-corner", "ellipse closest-side", "ellipse closest-corner", "ellipse farthest-side", "ellipse farthest-corner");
$css_lists["repeat"] = array("initial", "repeat", "repeat-x", "repeat-y", "no-repeat", "space", "round");
$css_lists["attachment"] = array("scroll", "fixed", "local");
$css_lists["size"] = array("initial", "auto", "cover", "contain", "custom");
$css_lists["position"] = array("initial", "top left", "top center", "top right", "center left", "center center", "center right", "bottom left", "bottom center", "bottom right", "custom");
$css_lists["blend"] = array("normal", "multiply", "screen", "overlay", "darken", "lighten", "color-dodge", "saturation", "color", "luminosity");
$css_lists["font_style"] = array("normal", "italic", "oblique");
$css_lists["font_weight"] = array("normal", "bold", "bolder", "lighter");
$css_lists["white_space"] = array("normal", "nowrap", "pre", "pre-line", "pre-wrap");
$css_lists["decoration_line"] = array("none", "underline", "overline","line-through");
$css_lists["decoration_style"] = array("solid", "double", "dotted", "dashed", "wavy");
$css_lists["text_align"] = array("left", "right","center","justify");
$css_lists["text_align_last"] = array("auto", "left", "right","center", "justify", "start", "end");
$css_lists["text_justify"] = array("auto", "inter-word", "inter-character");
$css_lists["text_transform"] = array("none", "capitalize", "uppercase", "lowercase");
$css_lists["word_wrap"] = array("normal", "break-word");
$css_lists["text_overflow"] = array("none", "clip", "ellipsis");
$css_lists["border_style"] = array("solid", "dotted", "dashed", "hidden", "double", "groove", "ridge", "inset", "outset");
$css_lists["display"] = array("block", "none", "flex", "inline", "inline-block", "inline-flex");
$css_lists["easing"] = array("linear", "ease", "ease-in", "ease-out", "ease-in-out", "ease-in-back", "ease-out-back", "ease-in-out-back", "ease-in-sine", "ease-out-sine", "ease-in-out-sine", "ease-in-quad", "ease-out-quad", "ease-in-out-quad", "ease-in-cubic", "ease-out-cubic", "ease-in-out-cubic", "ease-in-quart", "ease-out-quart", "ease-in-out-quart");
$css_lists["filters"] = array("blur", "brightness", "contrast", "drop-shadow", "grayscale", "hue-rotate", "invert", "opacity", "saturate", "sepia");

//Convert lists to selection options
foreach ($css_lists AS $key=>$property){
	$selection_list = null;
	foreach ($property AS $value){
		$option_title = ($key=="units" ? $value : implode("-", array_map("ucwords", explode("-", $value))));
		$selection_list .= "<option value='$value'>$option_title</option>";
	}
	$css_lists[$key] = $selection_list;
}
?>

<script src="../plugins/jscolor.min.js?v=<?=$system_settings["system_version"]?>"></script>

<!-- =========================== -->
<!-- ==== Selector ==== -->
<!-- =========================== -->

<div class=subtitle>Pseudo Class</div>
<div class=pseudo_classes><ul class=list_grid>
	<li><input type=button class="btn btn-default btn-sm active" value="Standard" pseudo-class=standard></li>
	<li><input type=button class="btn btn-default btn-sm" value="Hover" pseudo-class=hover></li>
	<li><input type=button class="btn btn-default btn-sm" value="Active State" pseudo-class=active-state></li>
	<li><input type=button class="btn btn-default btn-sm" value="Active Class" pseudo-class=active-class></li>
	<li><input type=button class="btn btn-default btn-sm" value="Before" pseudo-class=before></li>
	<li><input type=button class="btn btn-default btn-sm" value="After" pseudo-class=after></li>
	<li><input type=button class="btn btn-default btn-sm" value="Mobile Standard" pseudo-class=mobile-standard></li>
	<li><input type=button class="btn btn-default btn-sm" value="Mobile Hover" pseudo-class=mobile-hover></li>
	<li><input type=button class="btn btn-default btn-sm" value="Mobile Active" pseudo-class=mobile-active></li>
	<li><input type=button class="btn btn-default btn-sm" value="Tablet Standard" pseudo-class=tablet-standard></li>
	<li><input type=button class="btn btn-default btn-sm" value="Tablet Hover" pseudo-class=tablet-hover></li>
	<li><input type=button class="btn btn-default btn-sm" value="Tablet Active" pseudo-class=tablet-active></li>
</ul></div>

<div class=subtitle>CSS Properties</div>
<ul class="nav nav-tabs tab-inline-header">
	<li class=active><a data-toggle=tab href="#background">Background</a></li>
	<li><a data-toggle=tab href="#text">Text</a></li>
	<li><a data-toggle=tab href="#text-shadow">Text Shadow</a></li>
	<li><a data-toggle=tab href="#box-shadow">Box Shadow</a></li>
	<li><a data-toggle=tab href="#border">Border</a></li>
	<li><a data-toggle=tab href="#outline">Outline</a></li>
	<li><a data-toggle=tab href="#filter">Filter</a></li>
	<li><a data-toggle=tab href="#display">Display</a></li>
	<li><a data-toggle=tab href="#custom">Custom CSS</a></li>
</ul>

<script>
$(".tab-inline-header a").on("click", function(){
	$(".settings_subtitle b").text($(this).text());
});
</script>

<div class="row grid-container-15"><!-- Start Row -->

<!-- =========================== -->
<!-- ==== Properties ==== -->
<!-- =========================== -->

<div class="col-md-14 grid-item">
<div class="subtitle settings_subtitle"><b>Background</b><small>Standard</small></div>
<div class=tab-content>
	<!-- ==== Background ==== -->
	<div id=background class="tab-pane active">
		<div class="row grid-container">
			<!-- Insert -->
			<div class="col-md-4 grid-item background_components">
				<a class="btn btn-primary btn-sm btn-block" onclick="css_background.insertGradient()">Insert Gradient</a>
				<a class="btn btn-primary btn-sm btn-block" onclick="css_background.insertImage()">Insert Image</a>
				<ul class="background_components_list components_list"></ul>
			</div>
			<!-- Properties -->
			<div class="col-md-16 grid-item">
				<div class="background_component_empty component_empty">No components are specified</div>
				<div class="background_component component" css-type="background">
					<!-- Gradient Bar -->
					<div component-type="gradient" class=gradient_bar_container>
						<div class=gradient_bar></div>
					</div>
					
					<!-- Colors List -->
					<ul component-type="gradient" class=gradient_colors_list></ul>
					
					<!-- Properties Table -->
					<table class=data_table>
					<tr component-type="image">
						<td class=title>Image:</td>
						<td><input type=file component-image></td>
						<td class=title>Variable:</td>
						<td><select data-input=variable><option value="">No</option><option value="yes">Yes</option></select></td>
					</tr>
					<tr component-type="gradient">
						<td class=title>Type:</td>
						<td colspan=3><select name=gradient-type data-input=gradientType><?=$css_lists["gradient_types"]?></select></td>
					</tr>
					<tr component-type="gradient" visibility-control=gradient-type visibility-value="radial">
						<td class=title>Shape:</td>
						<td colspan=3><div class=d-flex><select data-input=radialShape><?=$css_lists["radial_shapes"]?></select></div></td>
					</tr>	
					<tr component-type="gradient" visibility-control=gradient-type visibility-value="linear">
						<td class=title>Degree:</td>
						<td colspan=3><div class=input-addon><input data-input=linearDegree type=number><span after>deg</span></div></td>
					</tr>						
					<tr>
						<td class=title>Repeat:</td>
						<td><select data-input=repeat><?=$css_lists["repeat"]?></select></td>
						<td class=title>Attachment:</td>
						<td><select data-input=attachment><?=$css_lists["attachment"]?></select></td>
					</tr>
					<tr>
						<td class=title>Size:</td>
						<td colspan=3><select name=background-gradient-size data-input=size><?=$css_lists["size"]?></select></td>
					</tr>
					<tr visibility-control=background-gradient-size visibility-value="custom">
						<td class=title>Horizontal:</td>
						<td><div class=d-flex><input type=number data-extends=size placeholder="auto">&nbsp;<select data-unit=size extends-index=0><?=$css_lists["units"]?></select></div></td>
						<td class=title>Vertical:</td>
						<td><div class=d-flex><input type=number data-extends=size placeholder="auto">&nbsp;<select data-unit=size extends-index=1><?=$css_lists["units"]?></select></div></td>
					</tr>
					<tr>
						<td class=title>Position:</td>
						<td colspan=3><select name=background-gradient-position data-input=position><?=$css_lists["position"]?></select></td>
					</tr>
					<tr visibility-control=background-gradient-position visibility-value="custom">
						<td class=title>Horizontal:</td>
						<td><div class=d-flex><input type=number data-extends=position placeholder="auto">&nbsp;<select data-unit=position extends-index=0><?=$css_lists["units"]?></select></div></td>
						<td class=title>Vertical:</td>
						<td><div class=d-flex><input type=number data-extends=position placeholder="auto">&nbsp;<select data-unit=position extends-index=1><?=$css_lists["units"]?></select></div></td>
					</tr>
					<tr>
						<td class=title>Blend:</td>
						<td colspan=3><select data-input=blend><?=$css_lists["blend"]?></select></td>
					</tr>
					</table>					
				</div>
			</div>
		</div>
	</div>
	
	<!-- ==== Text ==== -->
	<div id=text class=tab-pane>
		<div class="text_component component" css-type="text">
			<div class=data_table_container><table class=data_table>
				<tr>
					<td class=title>Font Family:</td>
					<td><select data-input=font_family></select></td>
					<td class=title>Font Size:</td>
					<td><div class=d-flex><input data-input=font_size type=number placeholder="Default">&nbsp;&nbsp;<select data-unit=font_size><?=$css_lists["units"]?></select></div></td>
				</tr>
				<tr>
					<td class=title>Color:</td>
					<td><input type=text data-input=color data-jscolor="{required:false}" placeholder="Default"></td>
					<td class=title>Font Weight:</td>
					<td><select data-input=font_weight><option value="">Default</option><?=$css_lists["font_weight"]?></select></td>
				</tr>		
				<tr>
					<td class=title>Font Style:</td>
					<td><select data-input=font_style><option value="">Default</option><?=$css_lists["font_style"]?></select></td>
					<td class=title>Text Decoration:</td>
					<td><select data-input=text_decoration><option value="">Default</option><?=$css_lists["decoration_line"]?></select></td>
				</tr>
				<tr>
					<td class=title>Decoration Style:</td>
					<td><select data-input=text_decoration_style><option value="">Default</option><?=$css_lists["decoration_style"]?></select></td>
					<td class=title>Decoration Color:</td>
					<td><input type=text data-input=text_decoration_color data-jscolor="{required:false}" placeholder="Default"></td>
				</tr>				
				<tr>
					<td class=title>Line Height:</td>
					<td><div class=d-flex><input data-input=line_height type=number placeholder="Default">&nbsp;&nbsp;<select data-unit=line_height><?=$css_lists["units"]?></select></div></td>
					<td class=title>Word Wrap:</td>
					<td><select data-input=word_wrap><option value="">Default</option><?=$css_lists["word_wrap"]?></select></td>
				</tr>
				<tr>
					<td class=title>Text Indent:</td>
					<td><div class=d-flex><input data-input=text_indent type=number placeholder="Default">&nbsp;&nbsp;<select data-unit=text_indent><?=$css_lists["units"]?></select></div></td>
					<td class=title>Text Transform:</td>
					<td><select data-input=text_transform><option value="">Default</option><?=$css_lists["text_transform"]?></select></td>
				</tr>	
				<tr>
					<td class=title>Word Spacing:</td>
					<td><div class=d-flex><input data-input=word_spacing type=number placeholder="Default">&nbsp;&nbsp;<select data-unit=word_spacing><?=$css_lists["units"]?></select></div></td>
					<td class=title>Letter Spacing:</td>
					<td><div class=d-flex><input data-input=letter_spacing type=number placeholder="Default">&nbsp;&nbsp;<select data-unit=letter_spacing><?=$css_lists["units"]?></select></div></td>
				</tr>	
				<tr>
					<td class=title>Text Align:</td>
					<td><select data-input=text_align><option value="">Default</option><?=$css_lists["text_align"]?></select></td>
					<td class=title>Text Align Last:</td>
					<td><select data-input=text_align_last><option value="">Default</option><?=$css_lists["text_align_last"]?></select></td>
				</tr>
				<tr>
					<td class=title>Text Justify:</td>
					<td><select data-input=text_justify><option value="">Default</option><?=$css_lists["text_justify"]?></select></td>
					<td class=title>White Space:</td>
					<td><select data-input=white_space><option value="">Default</option><?=$css_lists["white_space"]?></select></td>
				</tr>
				<tr>
					<td class=title>Text Overflow:</td>
					<td colspan=3><select data-input=text_overflow><option value="">Default</option><?=$css_lists["text_overflow"]?></select></td>
				</tr>
			</table></div>
		</div>
	</div>	
	
	<!-- ==== Text Shadow ==== -->
	<div id=text-shadow class=tab-pane>
		<div class="row grid-container">
			<div class="col-md-4 grid-item text_shadow_components">
				<a class="btn btn-primary btn-sm btn-block" onclick="css_text_shadow.insertShadow()">Insert</a>
				<ul class="text_shadow_components_list components_list"></ul>
			</div>
			<div class="col-md-16 grid-item">
				<div class="text_shadow_component_empty component_empty">No components are specified</div>
				<div class="text_shadow_component component" css-type="text-shadow">
					<table class=data_table>
						<tr>
							<td class=title>Horizontal:</td>
							<td><div class=d-flex><input data-input=horizontal type=number>&nbsp;&nbsp;<select data-unit=horizontal><?=$css_lists["units"]?></select></div></td>
							<td class=title>Vertical:</td>
							<td><div class=d-flex><input data-input=vertical type=number>&nbsp;&nbsp;<select data-unit=vertical><?=$css_lists["units"]?></select></div></td>
						</tr>
						<tr>
							<td class=title>Color:</td>
							<td ><input type=text data-input=color data-jscolor></td>
							<td class=title>Blur:</td>
							<td><div class=d-flex><input data-input=blur type=number>&nbsp;&nbsp;<select data-unit=blur><?=$css_lists["units"]?></select></div></td>
						</tr>
					</table>
				</div>
			</div>
		</div>
	</div>	
	
	<!-- ==== Box Shadow ==== -->
	<div id=box-shadow class=tab-pane>
		<div class="row grid-container">
			<div class="col-md-4 grid-item box_shadow_components">
				<a class="btn btn-primary btn-sm btn-block" onclick="css_box_shadow.insertShadow()">Insert</a>
				<ul class="box_shadow_components_list components_list"></ul>
			</div>
			<div class="col-md-16 grid-item">
				<div class="box_shadow_component_empty component_empty">No components are specified</div>
				<div class="box_shadow_component component" css-type="box-shadow">
					<table class=data_table>
						<tr>
							<td class=title>Horizontal:</td>
							<td><div class=d-flex><input data-input=horizontal type=number>&nbsp;&nbsp;<select data-unit=horizontal><?=$css_lists["units"]?></select></div></td>
							<td class=title>Vertical:</td>
							<td><div class=d-flex><input data-input=vertical type=number>&nbsp;&nbsp;<select data-unit=vertical><?=$css_lists["units"]?></select></div></td>
						</tr>
						<tr>
							<td class=title>Blur:</td>
							<td><div class=d-flex><input data-input=blur type=number>&nbsp;&nbsp;<select data-unit=blur><?=$css_lists["units"]?></select></div></td>
							<td class=title>Spread:</td>
							<td><div class=d-flex><input data-input=spread type=number>&nbsp;&nbsp;<select data-unit=spread><?=$css_lists["units"]?></select></div></td>
						</tr>
						<tr>
							<td class=title>Color:</td>
							<td ><input type=text data-input=color data-jscolor></td>
							<td class=title>Position:</td>
							<td>
								<select data-input=inset>
									<option value="">Outside</option>
									<option value="inset">Inside</option>
								</select>
							</td>
						</tr>
					</table>
				</div>
			</div>
		</div>
	</div>	
	
	<!-- ==== Border ==== -->
	<div id=border class=tab-pane>
		<div class="border_component component" css-type="border">
			<div class=data_table_container><table class=data_table>
				<tr>
					<td class=title>Border Type:</td>
					<td colspan=3>
						<select name=type data-input=borderType>
							<option value="">General</option>
							<option value="separate">Separate</option>
						</select>
					</td>
				</tr>
				<tr visibility-control=type visibility-value="">
					<td class=title>Width:</td>
					<td><div class=d-flex><input data-input=width type=number placeholder="Default">&nbsp;&nbsp;<select data-unit=width><?=$css_lists["units"]?></select></div></td>
					<td class=title>Style:</td>
					<td><select data-input=style><option value="">Default</option><?=$css_lists["border_style"]?></select></td>
				</tr>	
				<tr visibility-control=type visibility-value="">
					<td class=title>Color:</td>
					<td colspan=3><input type=text data-input=color data-jscolor="{required:false}" placeholder="Default"></td>
				</tr>
				<tr visibility-control=type visibility-value="separate">
					<td class=title>Left Border:</td>
					<td colspan=3><div class=d-flex><input data-input=left_width type=number placeholder="Default">&nbsp;&nbsp;<select data-unit=left_width><?=$css_lists["units"]?></select>&nbsp;&nbsp;<select data-input=left_style><option value="">Default</option><?=$css_lists["border_style"]?></select>&nbsp;&nbsp;<input type=text data-input=left_color data-jscolor="{required:false}"placeholder="Default"></div></td>
				</tr>	
				<tr visibility-control=type visibility-value="separate">
					<td class=title>Right Border:</td>
					<td colspan=3><div class=d-flex><input data-input=right_width type=number placeholder="Default">&nbsp;&nbsp;<select data-unit=right_width><?=$css_lists["units"]?></select>&nbsp;&nbsp;<select data-input=right_style><option value="">Default</option><?=$css_lists["border_style"]?></select>&nbsp;&nbsp;<input type=text data-input=right_color data-jscolor="{required:false}" placeholder="Default"></div></td>
				</tr>	
				<tr visibility-control=type visibility-value="separate">
					<td class=title>Top Border:</td>
					<td colspan=3><div class=d-flex><input data-input=top_width type=number placeholder="Default">&nbsp;&nbsp;<select data-unit=top_width><?=$css_lists["units"]?></select>&nbsp;&nbsp;<select data-input=top_style><option value="">Default</option><?=$css_lists["border_style"]?></select>&nbsp;&nbsp;<input type=text data-input=top_color data-jscolor="{required:false}" placeholder="Default"></div></td>
				</tr>	
				<tr visibility-control=type visibility-value="separate">
					<td class=title>Bottom Border:</td>
					<td colspan=3><div class=d-flex><input data-input=bottom_width type=number placeholder="Default">&nbsp;&nbsp;<select data-unit=bottom_width><?=$css_lists["units"]?></select>&nbsp;&nbsp;<select data-input=bottom_style><option value="">Default</option><?=$css_lists["border_style"]?></select>&nbsp;&nbsp;<input type=text data-input=bottom_color data-jscolor="{required:false}" placeholder="Default"></div></td>
				</tr>
				</table></div>
				
				<div class=subtitle>Border Radius</div>
				<div class=data_table_container><table class=data_table>
				<tr>
					<td class=title>Radius Type:</td>
					<td colspan=3>
						<select name=radiusType data-input=radiusType>
							<option value="">General</option>
							<option value="separate">Separate</option>
						</select>
					</td>
				</tr>
				<tr visibility-control=radiusType visibility-value="">
					<td class=title>Border Radius:</td>
					<td colspan=3><div class=d-flex><input data-input=radius type=number placeholder="Default">&nbsp;&nbsp;<select data-unit=radius><?=$css_lists["units"]?></select></div></td>
				</tr>
				<tr visibility-control=radiusType visibility-value="separate">
					<td class=title>Top-Left:</td>
					<td><div class=d-flex><input data-input=top_left_radius type=number placeholder="Default">&nbsp;&nbsp;<select data-unit=top_left_radius><?=$css_lists["units"]?></select></div></td>
					<td class=title>Top-Right:</td>
					<td><div class=d-flex><input data-input=top_right_radius type=number placeholder="Default">&nbsp;&nbsp;<select data-unit=top_right_radius><?=$css_lists["units"]?></select></div></td>
				</tr>
				<tr visibility-control=radiusType visibility-value="separate">
					<td class=title>Bottom-Right:</td>
					<td><div class=d-flex><input data-input=bottom_right_radius type=number placeholder="Default">&nbsp;&nbsp;<select data-unit=bottom_right_radius><?=$css_lists["units"]?></select></div></td>
					<td class=title>Bottom-Left:</td>
					<td><div class=d-flex><input data-input=bottom_left_radius type=number placeholder="Default">&nbsp;&nbsp;<select data-unit=bottom_left_radius><?=$css_lists["units"]?></select></div></td>
				</tr>
			</table></div>
		</div>
	</div>	
	
	<!-- ==== Outline ==== -->
	<div id=outline class=tab-pane>
		<div class="outline_component component" css-type="outline">
			<table class=data_table>
				<tr>
					<td class=title>Style:</td>
					<td colspan=3><select data-input=style><option value="">Default</option><?=$css_lists["border_style"]?></select></td>
				</tr>	
				<tr>
					<td class=title>Width:</td>
					<td><div class=d-flex><input data-input=width type=number placeholder="Default">&nbsp;&nbsp;<select data-unit=width><?=$css_lists["units"]?></select></div></td>
					<td class=title>Color:</td>
					<td><input type=text data-input=color data-jscolor="{required:false}" placeholder="Default"></td>
				</tr>		
			</table>
		</div>
	</div>	
	
	<!-- ==== Filter ==== -->
	<div id=filter class=tab-pane>
		<div class="row grid-container">
			<div class="col-md-4 grid-item filter_components">
				<a class="btn btn-primary btn-sm btn-block" onclick="css_filter.insertFilter()">Insert</a>
				<ul class="filter_components_list components_list"></ul>
			</div>
			<div class="col-md-16 grid-item">
				<div class="filter_component_empty component_empty">No components are specified</div>
				<div class="filter_component component" css-type="filter">
					<div class=data_table_container><table class=data_table>
						<tr>
							<td class=title>Filter Type:</td>
							<td colspan=3>
								<select name=filterType data-input=filterType><?=$css_lists["filters"]?></select>
							</td>
						</tr>
						<tr visibility-control=filterType visibility-value="blur">
							<td class=title>Blur Value:</td>
							<td colspan=3><div class=d-flex><input data-input=blur type=number>&nbsp;&nbsp;<select data-unit=blur><?=$css_lists["units"]?></select></div></td>
						</tr>
						<tr visibility-control=filterType visibility-value="brightness,contrast,grayscale,invert,opacity,saturate,sepia">
							<td class=title>Percentage:</td>
							<td colspan=3><div class=input-addon><input data-input=value type=number><span after>%</span></div></td>
						</tr>
						<tr visibility-control=filterType visibility-value="drop-shadow">
							<td class=title>Horizontal:</td>
							<td><div class=d-flex><input data-input=horizontal type=number>&nbsp;&nbsp;<select data-unit=horizontal><?=$css_lists["units"]?></select></div></td>
							<td class=title>Vertical:</td>
							<td><div class=d-flex><input data-input=vertical type=number>&nbsp;&nbsp;<select data-unit=vertical><?=$css_lists["units"]?></select></div></td>
						</tr>
						<tr visibility-control=filterType visibility-value="drop-shadow">
							<td class=title>Blur:</td>
							<td><div class=d-flex><input data-input=shadowBlur type=number>&nbsp;&nbsp;<select data-unit=shadowBlur><?=$css_lists["units"]?></select></div></td>
							<td class=title>Color:</td>
							<td><input type=text data-input=color data-jscolor placeholder="Default"></td>
						</tr>
						<tr visibility-control=filterType visibility-value="hue-rotate">
							<td class=title>Rotation:</td>
							<td colspan=3><div class=input-addon><input data-input=hue type=number><span after>deg</span></div></td>
						</tr>
						<tr visibility-control=filterType visibility-value="url">
							<td class=title>Location:</td>
							<td colspan=3><div class=d-flex><input data-input=location type=number></div></td>
						</tr>
					</table></div>
				</div>
			</div>
		</div>
	</div>	
	
	<!-- ==== Display ==== -->
	<div id=display class=tab-pane>
		<div class="display_component component" css-type="display">
			<div class=data_table_container><table class=data_table>
				<tr>
					<td class=title>Content:</td>
					<td><input data-input=content type=text placeholder="Default"></td>
					<td class=title>Display:</td>
					<td><select data-input=display><option value="">Default</option><?=$css_lists["display"]?></select></td>
				</tr>
				<tr>
					<td class=title>Width:</td>
					<td><div class=d-flex><input data-input=width type=number placeholder="Default">&nbsp;&nbsp;<select data-unit=width><?=$css_lists["units"]?></select></div></td>
					<td class=title>Height:</td>
					<td><div class=d-flex><input data-input=height type=number placeholder="Default">&nbsp;&nbsp;<select data-unit=height><?=$css_lists["units"]?></select></div></td>
				</tr>
				<tr>
					<td class=title>Min-Width:</td>
					<td><div class=d-flex><input data-input=min_width type=number placeholder="Default">&nbsp;&nbsp;<select data-unit=min_width><?=$css_lists["units"]?></select></div></td>
					<td class=title>Min-Height:</td>
					<td><div class=d-flex><input data-input=min_height type=number placeholder="Default">&nbsp;&nbsp;<select data-unit=min_height><?=$css_lists["units"]?></select></div></td>
				</tr>
				<tr>
					<td class=title>Max-Width:</td>
					<td><div class=d-flex><input data-input=max_width type=number placeholder="Default">&nbsp;&nbsp;<select data-unit=max_width><?=$css_lists["units"]?></select></div></td>
					<td class=title>Max-Height:</td>
					<td><div class=d-flex><input data-input=max_height type=number placeholder="Default">&nbsp;&nbsp;<select data-unit=max_height><?=$css_lists["units"]?></select></div></td>
				</tr>
				<tr>
					<td class=title>Background:</td>
					<td><input type=text data-input=background_color data-jscolor="{required:false}" placeholder="Default"></td>
					<td class=title>Opacity:</td>
					<td><div class=d-flex><input data-input=opacity type=number placeholder="Default"></div></td>
				</tr>
				<tr>
					<td class=title>Transition:</td>
					<td><div class=input-addon><input data-input=transition type=number placeholder="Default"><span after>ms</span></div></td>
					<td class=title>Easing:</td>
					<td><select data-input=easing><option value="">Default</option><?=$css_lists["easing"]?></select></td>
				</tr>	
			</table></div>
				
			<div class=subtitle>Margins</div>
			<div class=data_table_container><table class=data_table>
				<tr>
					<td class=title>Margin Type:</td>
					<td colspan=3>
						<select control name=marginType data-input=marginType>
							<option value="">General</option>
							<option value="separate">Separate</option>
						</select>
					</td>
				</tr>	
				<tr visibility-control=marginType visibility-value="">
					<td class=title>Margin Value:</td>
					<td colspan=3><div class=d-flex><input data-input=margin type=number placeholder="Default">&nbsp;&nbsp;<select data-unit=margin><?=$css_lists["units"]?></select></div></td>
				</tr>
				<tr visibility-control=marginType visibility-value="separate">
					<td class=title>Top Margin:</td>
					<td><div class=d-flex><input data-input=margin_top type=number placeholder="Default">&nbsp;&nbsp;<select data-unit=margin_top><?=$css_lists["units"]?></select></div></td>
					<td class=title>Bottom Margin:</td>
					<td><div class=d-flex><input data-input=margin_bottom type=number placeholder="Default">&nbsp;&nbsp;<select data-unit=margin_bottom><?=$css_lists["units"]?></select></div></td>
				</tr>
				<tr visibility-control=marginType visibility-value="separate">
					<td class=title>Left Margin:</td>
					<td><div class=d-flex><input data-input=margin_left type=number placeholder="Default">&nbsp;&nbsp;<select data-unit=margin_left><?=$css_lists["units"]?></select></div></td>
					<td class=title>Right Margin:</td>
					<td><div class=d-flex><input data-input=margin_right type=number placeholder="Default">&nbsp;&nbsp;<select data-unit=margin_right><?=$css_lists["units"]?></select></div></td>
				</tr>
			</table></div>
				
			<div class=subtitle>Paddings</div>
			<div class=data_table_container><table class=data_table>
				<tr>
					<td class=title>Padding Type:</td>
					<td colspan=3>
						<select control name=paddingType data-input=paddingType>
							<option value="">General</option>
							<option value="separate">Separate</option>
						</select>
					</td>
				</tr>	
				<tr visibility-control=paddingType visibility-value="">
					<td class=title>Padding Value:</td>
					<td colspan=3><div class=d-flex><input data-input=padding type=number placeholder="Default">&nbsp;&nbsp;<select data-unit=padding><?=$css_lists["units"]?></select></div></td>
				</tr>
				<tr visibility-control=paddingType visibility-value="separate">
					<td class=title>Top Padding:</td>
					<td><div class=d-flex><input data-input=padding_top type=number placeholder="Default">&nbsp;&nbsp;<select data-unit=padding_top><?=$css_lists["units"]?></select></div></td>
					<td class=title>Bottom Padding:</td>
					<td><div class=d-flex><input data-input=padding_bottom type=number placeholder="Default">&nbsp;&nbsp;<select data-unit=padding_bottom><?=$css_lists["units"]?></select></div></td>
				</tr>
				<tr visibility-control=paddingType visibility-value="separate">
					<td class=title>Left Padding:</td>
					<td><div class=d-flex><input data-input=padding_left type=number placeholder="Default">&nbsp;&nbsp;<select data-unit=padding_left><?=$css_lists["units"]?></select></div></td>
					<td class=title>Right Padding:</td>
					<td><div class=d-flex><input data-input=padding_right type=number placeholder="Default">&nbsp;&nbsp;<select data-unit=padding_right><?=$css_lists["units"]?></select></div></td>
				</tr>
			</table></div>
		</div>
	</div>	
	
	<!-- ==== Custom CSS ==== -->
	<div id=custom class=tab-pane>
		<div class="alert alert-warning class_selector">Class selector <b>.<?=$class_name?></b></div>
		<textarea name=custom_css class=css_code wrap=off><?=$entry["custom_css"]?></textarea>
		<script>
			$("[name=custom_css]").keydown(function(e){
				if (e.keyCode==9){
					var start = this.selectionStart;
					var end = this.selectionEnd;
					var value = $(this).val();
					$(this).val(value.substring(0, start) + "\t" + value.substring(end));
					this.selectionStart = this.selectionEnd = start + 1;
					e.preventDefault();
				}
			});
		</script>
	</div>	
</div>
</div>

<!-- =========================== -->
<!-- ==== Preview Panel ==== -->
<!-- =========================== -->

<div class="col-md-6 grid-item">
	<div class=subtitle>Preview</div>
	<div class=page_container>
		<div class="preview_container division"><div class="preview <?=$class_name?>">Sample Text</div></div>
		<div class=preview_options>
			<div class=radio_container>
				<label><input name=radios type=radio onchange="$('.preview_container').addClass('division')" checked><span>Block</span></label>
				<label><input name=radios type=radio onchange="$('.preview_container').removeClass('division')"><span>Inline</span></label>
			</div>
			<div class=check_container>
				<label><input type=checkbox class=filled-in onchange="$('.preview_container > div').toggleClass('active')"><span>Active</span></label>
			</div>
			<a class="fas fa-expand fa-lg" onclick="previewFull()"></a>
			<script>
			function previewFull(){
				$.fancybox.open({
					src: "<div class=fancybox_preview>" + $(".preview_container").html() + "</div>",
					type: "html",
					smallBtn : false
				});
			}
			</script>
		</div>
	</div>
	
	<div class=subtitle>CSS Code</div>
	<textarea name=css class=css_code wrap=off readonly></textarea>
</div>

</div><!-- End Row -->

<!-- =========================== -->
<!-- ==== Script ==== -->
<!-- =========================== -->

<script>
var pseudo_class = "standard";

var css_class = {
	"standard": {object: null, format: ".[class] {[css]}"},
	"hover": {object: null, format: ".[class]:hover {[css]}"},
	"active-state":  {object: null, format: ".[class]:active {[css]}"},
	"active-class":  {object: null, format: ".[class].active, .[class]-active {[css]}"},
	"before": {object: null, format: ".[class]:before {[css]}"},
	"after": {object: null, format: ".[class]:after {[css]}"},
	"mobile-standard":  {object: null, format: "@media screen and (max-width: 576px){ .[class] {[css]} }"},
	"mobile-hover":  {object: null, format: "@media screen and (max-width: 576px){ .[class]:hover {[css]} }"},
	"mobile-active":  {object: null, format: "@media screen and (max-width: 576px){ .[class]:active {[css]} }"},
	"tablet-standard":  {object: null, format: "@media screen and (min-width: 576px) and (max-width: 768px){ .[class] {[css]} }"},
	"tablet-hover":  {object: null, format: "@media screen and (min-width: 576px) and (max-width: 768px){ .[class]:hover {[css]} }"},
	"tablet-active":  {object: null, format: "@media screen and (min-width: 576px) and (max-width: 768px){ .[class]:active {[css]} }"},	
};

//Pseudo class buttons
$("[pseudo-class]").on("click", function(){
	//Update active state
	$(".pseudo_classes .btn").removeClass("active");
	$(this).addClass("active");
	
	//Change current pseudo class to the new one
	pseudo_class = $(this).attr("pseudo-class");
	
	//Reset class inputs
	resetClassInputs();
	
	//Set class component to the new pseudo class
	setClassComponent(css_class[pseudo_class].object);
	
	//Update subtitle
	$(".settings_subtitle small").text($(this).val());
	
	//Update current class
	updateCSSClass();
});

//JSColor presets
jscolor.presets.default = {
	format: "hexa",
	alphaChannel: true
};

//Initialize sortable on list components
$(".components_list").sortable({
	handle: ".sortable",
	stop: function(){
		updateCSSClass();
	}
});

//====================
//======= Input changes =======
//====================

//Listen for property changes
$("[data-input]").on("input", function(){
	var componentDOM = $(this).closest(".component");
	var targetComponent = componentDOM.data("component");
	var property = $(this).attr("data-input");
	var value = $(this).val();

	//If value is custom, then assign corresponding inputs
	if (value=="custom" || value=="string"){
		var values = [];
		var index = 0;
		componentDOM.find("[data-extends=" + property + "]").each(function(){
			var indexValue = $(this).val();
			if (indexValue){
				componentDOM.find("[data-unit=" + property + "][extends-index=" + index + "]").each(function(){
					indexValue += $(this).val();
				});
			} else {
				indexValue = "auto";
			}
			values.push(indexValue);
			index++;
		});
		targetComponent[property] = values.join(" ");
	
	//If value has an extension unit, assign that unit
	} else if (componentDOM.find("[data-unit=" + property + "]:visible").length){
		targetComponent[property] = (value ? value + componentDOM.find("[data-unit=" + property + "]").val() : "");
		
	//Otherwise assign current value directly
	} else {
		targetComponent[property] = (value ? value : "");
	}
	
	//Update visibility on hidden fields
	toggleVisibility($(this)[0]);
	
	//Update component UI
	if (typeof targetComponent.updateUI !== "undefined"){
		targetComponent.updateUI();
		updateCSSClass();
	} else {
		updateCSSClass();
	}
});

//Listen for extended property changes
$("[data-extends]").on("input", function(){
	var componentDOM = $(this).closest(".component");
	componentDOM.find("[data-input=" + $(this).attr("data-extends") + "]").trigger("input");
});

//Listen for unit property changes
$("[data-unit]").on("input", function(){
	var componentDOM = $(this).closest(".component");
	componentDOM.find("[data-input=" + $(this).attr("data-unit") + "]").trigger("input");
});

//Update property input values
function updatePropertiesInputs(component, selector){
	var componentDOM = $(".component[css-type='" + selector + "']");
	
	//Reset all input DOM objects
	componentDOM.find("[data-input]").val("");
	componentDOM.find("[data-unit]").val("px");
	componentDOM.find("[data-extends]").val("");
	
	//Loop through data input DOM objects
	componentDOM.find("[data-input]").each(function(){
		var property = $(this).attr("data-input");
		var value = component[property];

		//Value can split and not in selection list (eg: "1px auto", "1px 1px", not "top left")
		if (canSplit(value, " ") && !$(this).find("option[value='" + value + "']").length){
			$(this).val("custom");
			var index = 0;
			var split = value.split(" ");
			split.forEach(function(item, index){
				var unit = (item ? item.split(/(-[0-9.]+)|([0-9.]+)/).filter(function(e){ return e }) : null);
				if (unit && unit[1]){
					componentDOM.find("[data-extends=" + property + "]").each(function(){
						$(this).val(unit[0]);
						componentDOM.find("[data-unit=" + property + "][extends-index=" + index + "]").val(unit[1]);
					});
				} else {
					$(this).val(split[index]);
					componentDOM.find("[data-unit=" + property + "][extends-index=" + index + "]").val("px");
				}		
				index++;
			});
		
		//Value cannot be split (or) in selection list (eg: "1px", "auto", "top left")
		} else {
			var unit = (value ? value.split(/(-[0-9.]+)|([0-9.]+)/).filter(function(e){ return e }) : null);
			if (unit && unit[1]){
				$(this).val(unit[0]);
				componentDOM.find("[data-unit=" + property + "]").val(unit[1]);
			} else {
				$(this).val(value);
			}
		}

		//Initialize color if set
		if (typeof $(this).attr("data-jscolor") !== "undefined"){
			if ($(this).val()){
				$(this)[0].jscolor.fromString(value);
			} else {
				jscolor.install();
				$(this)[0].jscolor.fromString("");
			}
		}
		
		//Update visibility on hidden fields
		toggleVisibility($(this)[0]);
	});	
}

//--------------------------------------------------
//--------------------------------------------------

//============================================================
//=========================== Background ===========================
//============================================================

//Listen for input file image changes
$("[component-image]").on("change", function(event){
	var targetComponent = $(this).parents(".component").data("component");
	var files = event.target.files;
	var form = new FormData();
	$.each(files, function(key, value){
		form.append("image", value);
	});
	form.append("token", "<?=$token?>");
	form.append("image", "<?=$token?>");
	
	$.ajax({
		type: "post",
		data: form,
		cache: false,
		contentType: false,
		processData: false,
		success: function(response){
			targetComponent.image = response;
			targetComponent.updateUI();
		},
		error: function(request, status, error){
			quickNotify(request.responseText, readLanguage.plugins.upload_error_title + files[0].name, "danger", "fas fa-times fa-3x")
		}
	});
});

//Insert color on clicking on gradient bar
$(".gradient_bar").on("click", function(event){
	var targetGradient = $(".background_component").data("component");
	var barWidth = $(this).width();
    var clickPosition = event.pageX - $(this).offset().left;
	var percentage = Math.ceil(clickPosition * 100 / barWidth);
	targetGradient.insertColor("#00FF00FF", percentage);
});

//Build gradient CSS text line
function buildGradient(values, horizontal=false){
	var type = (horizontal ? "linear-gradient" : (values.gradientType=="radial" ? "radial-gradient" : "linear-gradient"));
	var orientation = (horizontal ? "90deg" : (values.gradientType=="radial" ? values.radialShape : values.linearDegree + "deg"));
	var colors = [];
	var sorted = Array.prototype.slice.call(values.colors).sort();
	
	sorted.sort((a, b) => parseFloat(a.location) - parseFloat(b.location));
	sorted.forEach(function(item, index, array){
		colors.push(`${item.color} ${item.location}%`);
	});
	
	return `${type}(${orientation}, ${colors.join(", ")})`;
}

//======= Image class object =======

class imageClass {
	constructor(image, variable, repeat="repeat", attachment="scroll", size="auto", position="initial", blend="normal"){
		this.type = "image";
		this.image = image;
		this.variable = variable;
		this.repeat = repeat;
		this.attachment = attachment;
		this.size = size;
		this.position = position;
		this.blend = blend;
	}
	setActive(){
		//Update property input values
		updatePropertiesInputs(this, "background");
	}
	updateUI(){
		//Update preview
		updateCSSClass();
	}
}

//======= Gradient class object =======

class gradientClass {
	constructor(colors=[], gradientType="linear", radialShape="circle", linearDegree=0, repeat="repeat", attachment="scroll", size="auto", position="initial", blend="normal"){
		this.type = "gradient";
		this.colors = colors;
		this.gradientType = gradientType;
		this.radialShape = radialShape;
		this.linearDegree = linearDegree;
		this.repeat = repeat;
		this.attachment = attachment;
		this.size = size;
		this.position = position;
		this.blend = blend;
	}
	insertColor(color, location){
		//Insert color
		this.colors.push({
			color: color,
			location: location
		});
		
		//Set active gradient
		this.setActive();
	}
	removeColor(item){
		this.colors.splice(item.index(), 1);
		this.setActive();
	}
	setActive(){
		let self = this;

		//Empty current handles and lists
		$(".gradient_colors_list").empty();
		$(".gradient_bar").empty();
		
		//Loop through colors and append them to handles & list
		self.colors.forEach(function(value, index, array){
			//Create list item
			var item = $("<li><input type=text data-jscolor>&nbsp;<input type=number data-location>&nbsp;<a class='btn btn-danger btn-sm'>×</a></li>");
			
			//Assign remove
			item.find("a").on("click", function(){
				self.removeColor($(this).parent());
			});
			
			//Assign color selection
			var color = item.find("[data-jscolor]");
			color.val(value.color);
			color.on("input", function(){
				self.colors[index].color = $(this).val();
				self.updateUI();
			});
			
			//Assign location
			var location = item.find("[data-location]");
			location.val(value.location);
			location.on("input", function(){
				self.colors[index].location = $(this).val();
				self.updateUI();
			});
			
			//Append list item
			$(".gradient_colors_list").append(item);
			self.colors[index].list = item;
			
			//Create and append handle
			var handle = $("<div class=gradient_handle></div>");
			handle.on("click", function(event){
				event.stopPropagation();
			});
			
			//Initialize handle draggable
			handle.draggable({
				axis: "x",
				containment: $(".gradient_bar"),
				drag: function(){
					var barWidth = $(".gradient_bar").width();
					var handlePosition = $(this).position().left;
					var percentage = Math.ceil(handlePosition * 100 / barWidth);
					self.colors[index].location = percentage;
					self.updateUI();
				},
				stop: function(){
					handle.css("left", self.colors[index].location + "%");
				}
			});
			
			//Append and assign color handle
			$(".gradient_bar").append(handle);
			self.colors[index].handle = handle;
		});

		//Initialize color selection
		jscolor.install();
		
		//Update property input values
		updatePropertiesInputs(this, "background");
		
		//Update UI colors & preview
		self.updateUI();
	}
	updateUI(){
		let self = this;
		
		//Update gradient bar colors
		$(".gradient_bar").css({
			"background": buildGradient(self, true)
		});
		
		//Update handle color
		self.colors.forEach(function(value, index, array){
			value.handle.css({
				"background": value.color,
				"left": value.location + "%"
			});
			value.list.find("[data-location]").val(value.location);
		});
		
		//Update preview
		updateCSSClass();
	}
};

//======= CSS Background =======

const css_background = {
	//Insert a gradient
	insertGradient: function(values=null){
		let gradient = new gradientClass();
		if (values==null){
			gradient.insertColor("#FF0000FF",0);
			gradient.insertColor("#0000FFFF",100);
		} else {
			gradient.gradientType = values.gradientType;
			gradient.radialShape = values.radialShape;
			gradient.linearDegree = values.linearDegree;
			gradient.repeat = values.repeat;
			gradient.attachment = values.attachment;
			gradient.size = values.size;
			gradient.position = values.position;
			gradient.blend = values.blend;
			values.colors.forEach(function(color, index){
				gradient.insertColor(color.color, color.location);
			});
		}
		css_background.insertComponent(gradient);
	},
	
	//Insert an image
	insertImage: function(values=null){
		let image = new imageClass();
		if (values==null){
			image.image = "data:image/gif;base64,R0lGODlhFgAWAJEAAGZmZpmZmf///wAAACH5BAEAAAIALAAAAAAWABYAAAItjI8Byw0JnYRJOopsw0czbngLqIik+IDoaapoy62ux9KzVuO3lfP79LsEN6ACADs=";
		} else {
			image.image = values.image;
			image.repeat = values.repeat;
			image.attachment = values.attachment;
			image.size = values.size;
			image.position = values.position;
			image.blend = values.blend;					
		}
		css_background.insertComponent(image);
	},
	
	//Insert CSS background component
	insertComponent: function(component){
		//Create list item
		var item = $("<li><div class=sortable><i class='fas fa-bars'></i></div>&nbsp;&nbsp;<span>" + (component.type=="gradient" ? "Gradient" : "Image") + "</span>&nbsp;<a class='btn btn-danger btn-sm'>×</a></li>");
		
		//Append component to list
		item.data("component", component);
		
		//Assign selection on click
		item.on("click", function(){
			css_background.setActiveComponent(component, item);
		});
		
		//Assign delete on remove click
		item.find("a").on("click", function(){
			css_background.removeComponent($(this).parent());
		});
		
		//Append list item
		$(".background_components_list").append(item);
		
		//Set active component
		css_background.setActiveComponent(component, item);
		
		updateCSSClass();
	},
	
	//Remove CSS background component item
	removeComponent: function(item){
		//Select first
		var targetItem = $(item).siblings("li").first();
		if (targetItem.length){
			this.setActiveComponent(targetItem.data("component"), targetItem);
		} else {
			$(".background_component").hide();
			$(".background_component_empty").show();
		}
		
		//Remove component and update preview
		item.remove();
		updateCSSClass();
	},
	
	//Set active CSS background component
	setActiveComponent: function(component, item){
		//Set active list item
		$(".background_components_list li").removeClass("active");
		item.addClass("active");
		
		//Show/Hide relative component type inputs
		$(".background_component [component-type]").hide();
		$(".background_component [component-type=" + component.type + "]").show();
		
		//Reset extended inputs & file since they are not assigned by default
		$(".background_component [data-extends]").val("px");
		$(".background_component [component-image]")[0].value = null;
		
		//Reset components input values
		$(".background_component").data("component", component).show();
		$(".background_component_empty").hide();
		
		//Trigger set active for target component
		component.setActive();
	}
};

//============================================================
//=========================== Text ===========================
//============================================================

class css_text {
	constructor(font_family, font_size, color, font_weight, font_style, text_decoration, text_decoration_style, text_decoration_color, line_height, word_wrap, text_indent, text_transform, word_spacing, letter_spacing, text_align, text_align_last, text_justify, white_space, text_overflow){
		this.type = "text";
		this.font_family = font_family;
		this.font_size = font_size;
		this.color = color;
		this.font_weight = font_weight;
		this.font_style = font_style;
		this.text_decoration = text_decoration;
		this.text_decoration_style = text_decoration_style;
		this.text_decoration_color = text_decoration_color;
		this.line_height = line_height;
		this.word_wrap = word_wrap;
		this.text_indent = text_indent;
		this.text_transform = text_transform;
		this.word_spacing = word_spacing;
		this.letter_spacing = letter_spacing;
		this.text_align = text_align;
		this.text_align_last = text_align_last;
		this.text_justify = text_justify;
		this.white_space = white_space;
		this.text_overflow = text_overflow;
		
		//Update property input values
		updatePropertiesInputs(this, "text");
	}
}

//Initialize standard
$(".text_component").data("component", new css_text());

//============================================================
//=========================== Text Shadow ===========================
//============================================================

class textShadowClass {
	constructor(horizontal="1px", vertical="1px", blur="1px", color="#000000FF"){
		this.horizontal = horizontal;
		this.vertical = vertical;
		this.blur = blur;
		this.color = color;
	}
	setActive(){
		//Update property input values
		updatePropertiesInputs(this, "text-shadow");
	}
};

const css_text_shadow = {
	insertShadow: function(values = null){
		let textShadow = new textShadowClass();
		
		if (values != null){
			textShadow.horizontal = values.horizontal;
			textShadow.vertical = values.vertical;
			textShadow.blur = values.blur;
			textShadow.color = values.color;
		}

		//Create list item
		var item = $("<li><div class=sortable><i class='fas fa-bars'></i></div>&nbsp;&nbsp;<span>Shadow</span>&nbsp;<a class='btn btn-danger btn-sm'>×</a></li>");

		//Append component to list
		item.data("component", textShadow);

		//Assign selection on click
		item.on("click", function(){
			css_text_shadow.setActiveComponent(textShadow, item);
		});

		//Assign delete on remove click
		item.find("a").on("click", function(){
			css_text_shadow.removeComponent($(this).parent());
		});
		
		//Append list item
		$(".text_shadow_components_list").append(item);
		
		//Set active component
		css_text_shadow.setActiveComponent(textShadow, item);
		updateCSSClass();
	},
	removeComponent: function(item){
		//Select first
		var targetItem = $(item).siblings("li").first();
		if (targetItem.length){
			this.setActiveComponent(targetItem.data("component"), targetItem);
		} else {
			$(".text_shadow_component").hide();
			$(".text_shadow_component_empty").show();
		}
		
		//Remove component and update preview
		item.remove();
		updateCSSClass();
	},
	setActiveComponent: function(component, item){
		//Set active list item
		$(".text_shadow_components_list li").removeClass("active");
		item.addClass("active");

		//Reset extended input since they are not assigned by default
		$(".text_shadow_component [data-extends]").val("px");

		//Reset components input values
		$(".text_shadow_component").data("component", component).show();
		$(".text_shadow_component_empty").hide();
		
		//Trigger set active for target component
		component.setActive();
	}
};

//============================================================
//=========================== Box Shadow ===========================
//============================================================

class boxShadowClass {
	constructor(horizontal="1px", vertical="1px", blur="0px", spread="0px", color="#000000FF", inset=""){
		this.horizontal = horizontal;
		this.vertical = vertical;
		this.blur = blur;
		this.spread = spread;
		this.color = color;
		this.inset = inset;
	}
	setActive(){
		//Update property input values
		updatePropertiesInputs(this, "box-shadow");
	}
};

const css_box_shadow = {
	insertShadow: function(values = null){
		let boxShadow = new boxShadowClass();
		
		if (values != null){
			boxShadow.horizontal = values.horizontal;
			boxShadow.vertical = values.vertical;
			boxShadow.blur = values.blur;
			boxShadow.spread = values.spread;
			boxShadow.color = values.color;
			boxShadow.inset = values.inset;
		}

		//Create list item
		var item = $("<li><div class=sortable><i class='fas fa-bars'></i></div>&nbsp;&nbsp;<span>Shadow</span>&nbsp;<a class='btn btn-danger btn-sm'>×</a></li>");

		//Append component to list
		item.data("component", boxShadow);

		//Assign selection on click
		item.on("click", function(){
			css_box_shadow.setActiveComponent(boxShadow, item);
		});

		//Assign delete on remove click
		item.find("a").on("click", function(){
			css_box_shadow.removeComponent($(this).parent());
		});
		
		//Append list item
		$(".box_shadow_components_list").append(item);
		
		//Set active component
		css_box_shadow.setActiveComponent(boxShadow, item);
		updateCSSClass();
	},
	removeComponent: function(item){
		//Select first
		var targetItem = $(item).siblings("li").first();
		if (targetItem.length){
			this.setActiveComponent(targetItem.data("component"), targetItem);
		} else {
			$(".box_shadow_component").hide();
			$(".box_shadow_component_empty").show();
		}
		
		//Remove component and update preview
		item.remove();
		updateCSSClass();
	},
	setActiveComponent: function(component, item){
		//Set active list item
		$(".box_shadow_components_list li").removeClass("active");
		item.addClass("active");

		//Reset extended input since they are not assigned by default
		$(".box_shadow_component [data-extends]").val("px");

		//Reset components input values
		$(".box_shadow_component").data("component", component).show();
		$(".box_shadow_component_empty").hide();
		
		//Trigger set active for target component
		component.setActive();
	}
};

//============================================================
//=========================== Border ===========================
//============================================================

class css_border {
	constructor(borderType, radiusType, width, style, color, left_width, left_style, left_color, right_width, right_style, right_color, top_width, top_style, top_color, bottom_width, bottom_style, bottom_color, radius, top_left_radius, top_right_radius, bottom_right_radius, bottom_left_radius){
		this.type = "border";
		this.borderType = borderType;
		this.radiusType = radiusType;
		
		this.width = width;
		this.style = style;
		this.color = color;
		this.left_width = left_width;
		this.left_style = left_style;
		this.left_color = left_color;
		this.right_width = right_width;
		this.right_style = right_style;
		this.right_color = right_color;
		this.top_width = top_width;
		this.top_style = top_style;
		this.top_color = top_color;
		this.bottom_width = bottom_width;
		this.bottom_style = bottom_style;
		this.bottom_color = bottom_color;
		
		this.radius = radius;
		this.top_left_radius = top_left_radius;
		this.top_right_radius = top_right_radius;
		this.bottom_right_radius = bottom_right_radius;
		this.bottom_left_radius = bottom_left_radius;
		
		//Update property input values
		updatePropertiesInputs(this, "border");
	}
}

$(".border_component").data("component", new css_border());

//============================================================
//=========================== Outline ===========================
//============================================================

class css_outline {
	constructor(style="", width="", color=""){
		this.type = "outline";
		this.width = width;
		this.style = style;
		this.color = color;
		
		//Update property input values
		updatePropertiesInputs(this, "outline");
	}
}

$(".outline_component").data("component", new css_outline());

//============================================================
//=========================== Filter ===========================
//============================================================
		
class filterClass {
	constructor(filterType="blur", blur="", value="", horizontal="", vertical="", shadowBlur="", color="#000000FF", location="", hue=""){
		this.filterType = filterType;
		this.blur = blur;
		this.value = value;
		this.horizontal = horizontal;
		this.vertical = vertical;
		this.shadowBlur = shadowBlur;
		this.color = color;
		this.location = location;
		this.hue = hue;		
	}
	setActive(){
		//Update property input values
		updatePropertiesInputs(this, "filter");
	}
};

const css_filter = {
	insertFilter: function(values = null){
		let filter = new filterClass();
		
		if (values != null){
			filter.filterType = values.filterType;
			filter.blur = values.blur;
			filter.value = values.value;
			filter.horizontal = values.horizontal;
			filter.vertical = values.vertical;
			filter.shadowBlur = values.shadowBlur;
			filter.color = values.color;
			filter.location = values.location;
			filter.hue = values.hue;
		}

		//Create list item
		var item = $("<li><div class=sortable><i class='fas fa-bars'></i></div>&nbsp;&nbsp;<span>Filter</span>&nbsp;<a class='btn btn-danger btn-sm'>×</a></li>");

		//Append component to list
		item.data("component", filter);

		//Assign selection on click
		item.on("click", function(){
			css_filter.setActiveComponent(filter, item);
		});

		//Assign delete on remove click
		item.find("a").on("click", function(){
			css_filter.removeComponent($(this).parent());
		});
		
		//Append list item
		$(".filter_components_list").append(item);
		
		//Set active component
		css_filter.setActiveComponent(filter, item);
		updateCSSClass();
	},
	removeComponent: function(item){
		//Select first
		var targetItem = $(item).siblings("li").first();
		if (targetItem.length){
			this.setActiveComponent(targetItem.data("component"), targetItem);
		} else {
			$(".filter_component").hide();
			$(".filter_component_empty").show();
		}
		
		//Remove component and update preview
		item.remove();
		updateCSSClass();
	},
	setActiveComponent: function(component, item){
		//Set active list item
		$(".filter_components_list li").removeClass("active");
		item.addClass("active");

		//Reset extended input since they are not assigned by default
		$(".filter_component [data-extends]").val("px");

		//Reset components input values
		$(".filter_component").data("component", component).show();
		$(".filter_component_empty").hide();
		
		//Trigger set active for target component
		component.setActive();
	}
};

//============================================================
//=========================== Display ===========================
//============================================================

class css_display {
	constructor(content, display, width, height, min_width, min_height, max_width, max_height, background_color, opacity, transition, easing="", marginType, margin, margin_top, margin_bottom, margin_left, margin_right, paddingType, padding, padding_top, padding_bottom, padding_left, padding_right){
		this.type = "display";
		
		this.content = content;
		this.display = display;
		this.width = width;
		this.height = height;
		this.min_width = min_width;
		this.min_height = min_height;		
		this.max_width = max_width;
		this.max_height = max_height;
		this.background_color = background_color;
		this.opacity = opacity;
		this.transition = transition;
		this.easing = easing;

		this.marginType = marginType;
		this.margin = margin;
		this.margin_top = margin_top;
		this.margin_bottom = margin_bottom;
		this.margin_left = margin_left;
		this.margin_right = margin_right;
		
		this.paddingType = paddingType;
		this.padding = padding;
		this.padding_top = padding_top;
		this.padding_bottom = padding_bottom;
		this.padding_left = padding_left;
		this.padding_right = padding_right;
		
		updatePropertiesInputs(this, "display");
	}
}

$(".display_component").data("component", new css_display());

//--------------------------------------------------
//--------------------------------------------------

//====================
//==== Update Preview ====
//====================

function updateCSSClass(){
	//Save current pseudo class object
	css_class[pseudo_class].object = saveClassComponent();

	let final_css_class = [];
	for (const [pseudo, properties] of Object.entries(css_class)){
		if (properties.object){
			let css_properties = {};

			//Background
			if (properties.object["background"]){
				var backgrounds = [];
				properties.object["background"].forEach(function(item, index){
					var component = item;
					backgrounds.push({
						image: (component.type=="gradient" ? buildGradient(component) : "url(" + component.image + ")"),
						repeat: component.repeat,
						attachment: component.attachment,
						size: component.size,
						position: component.position,
						blend: component.blend				
					});
				});
				if (backgrounds.length){
					css_properties["background-image"] = backgrounds.map(e => e.image).join(", ");
					css_properties["background-repeat"] = backgrounds.map(e => e.repeat).join(", ");
					css_properties["background-attachment"] = backgrounds.map(e => e.attachment).join(", ");
					css_properties["background-size"] = backgrounds.map(e => e.size).join(", ");
					css_properties["background-position"] = backgrounds.map(e => e.position).join(", ");
					css_properties["background-blend-mode"] = backgrounds.map(e => e.blend).join(", ");
				}
			}
			
			//Text
			if (properties.object["text"]){
				for (const property in properties.object["text"]){
					if (properties.object["text"][property]){
						let css_name = property.replace(/_/g, "-");
						css_properties[css_name] = properties.object["text"][property];
					}
				}
			}
			
			//Text-Shadow
			if (properties.object["text-shadow"]){
				var shadows = [];
				properties.object["text-shadow"].forEach(function(item, index){
					var component = item;
					var values = [];
					if (component.horizontal){
						values.push(component.horizontal);
					}
					if (component.vertical){
						values.push(component.vertical);
					}
					if (component.blur){
						values.push(component.blur);
					}
					if (component.color){
						values.push(component.color);
					}
					if (values.length){
						shadows.push(values.join(" "));
					}
				});
				if (shadows.length){
					css_properties["text-shadow"] = shadows.map(e => e).join(", ");	
				}
			}			
			
			//Box-Shadow
			if (properties.object["box-shadow"]){
				var shadows = [];
				properties.object["box-shadow"].forEach(function(item, index){
					var component = item;
					var values = [];
					if (component.inset){
						values.push(component.inset);
					}
					if (component.horizontal){
						values.push(component.horizontal);
					}
					if (component.vertical){
						values.push(component.vertical);
					}
					if (component.blur){
						values.push(component.blur);
					}
					if (component.spread){
						values.push(component.spread);
					}
					if (component.color){
						values.push(component.color);
					}
					if (values.length){
						shadows.push(values.join(" "));
					}
				});
				if (shadows.length){
					css_properties["box-shadow"] = shadows.map(e => e).join(", ");	
				}
			}
			
			//Border
			if (properties.object["border"]){
				var targetProperties = null;
				
				//Border Radius
				if (properties.object["border"].borderType=="separate"){
					targetProperties = ["left_width","left_style","left_color","right_width","right_style","right_color","right_width","top_style","top_color","top_width","bottom_style","bottom_color"];					
				} else {
					targetProperties = ["width","style","color"];
				}
				
				targetProperties.forEach(function(item, index){
					if (properties.object["border"][item]){
						let css_name = item.replace(/_/g, "-");
						css_properties["border-" + css_name] = properties.object["border"][item];							
					}
				});
				
				//Border Radius
				if (properties.object["border"].radiusType=="separate"){
					targetProperties = ["top_left_radius","top_right_radius","bottom_right_radius","bottom_left_radius"];
				} else {
					targetProperties = ["radius"];
				}
				
				targetProperties.forEach(function(item, index){
					if (properties.object["border"][item]){
						let css_name = item.replace(/_/g, "-");
						css_properties["border-" + css_name] = properties.object["border"][item];							
					}
				});
			}

			//Outline
			if (properties.object["outline"]){
				for (const property in properties.object["outline"]){
					css_properties["outline-" + property] = properties.object["outline"][property];
				}
			}			

			//Filter
			if (properties.object["filter"]){
				var filter = [];
				properties.object["filter"].forEach(function(item, index){
					var component = item;
					var values = [];
					if (component.filterType=="blur" && component.blur){
						values.push("blur(" + component.blur + ")");
					} else if (component.filterType=="drop-shadow"){
						
						if (component.horizontal && component.vertical){
							var shadow = [component.horizontal, component.vertical];
							if (component.shadowBlur){
								shadow.push(component.shadowBlur);
							}
							if (component.color){
								shadow.push(component.color);
							}
							values.push("drop-shadow(" + shadow.join(" ") + ")");
						}
					} else if (component.filterType=="hue-rotate" && component.hue){
						values.push("hue-rotate(" + component.hue + "deg)");
					} else if (component.value){
						values.push(component.filterType + "(" + component.value + "%)");
					}
					if (values.length){
						filter.push(values.join(" "));
					}
				});
				if (filter.length){
					css_properties["filter"] = filter.map(e => e).join(" ");	
				}
			}

			//Display
			if (properties.object["display"]){
				var targetProperties = null;
				
				//Content
				if (properties.object["display"].content){
					css_properties["content"] = "'" + properties.object["display"].content + "'";
				}
				
				//Transition
				if (properties.object["display"].transition){
					css_properties["transition"] = "all " + properties.object["display"].transition + "ms" + (properties.object["display"].easing ? " " + properties.object["display"].easing : "");
				}				
				
				//Margins
				targetProperties = (properties.object["display"].marginType=="separate" ? ["margin_top","margin_bottom","margin_left","margin_right"] : ["margin"]);
				targetProperties.forEach(function(item, index){
					if (properties.object["display"][item]){
						let css_name = item.replace(/_/g, "-");
						css_properties[css_name] = properties.object["display"][item];							
					}
				});

				//Paddings
				targetProperties = (properties.object["display"].paddingType=="separate" ? ["padding_top","padding_bottom","padding_left","padding_right"] : ["padding"]);
				targetProperties.forEach(function(item, index){
					if (properties.object["display"][item]){
						let css_name = item.replace(/_/g, "-");
						css_properties[css_name] = properties.object["display"][item];
					}
				});
				
				//Opacity
				if (properties.object["display"].opacity){
					css_properties["opacity"] = properties.object["display"].opacity + "%";
				}
				
				//Other Properties
				targetProperties = ["display","width","height","min_width","min_height","max_width","max_height","background_color"];
				targetProperties.forEach(function(item, index){
					if (properties.object["display"][item]){
						let css_name = item.replace(/_/g, "-");
						css_properties[css_name] = properties.object["display"][item];							
					}
				});				
			}

			//Apply CSS lines
			var css_lines = [];
			for (const [property, value] of Object.entries(css_properties)){
				if (value){
					css_lines.push("\t" + property + ": " + value + ";");
				}
			}
			
			//Push to final CSS class
			if (css_lines.length){
				var selector_result = properties.format.replaceAll("[class]", "<?=$class_name?>").replaceAll("[css]", "\r\n" + css_lines.join("\r\n") + "\r\n");
				final_css_class.push(selector_result);
			}
		}
	}
	
	//Assign final CSS class to text and actual style class
	var css_code = final_css_class.join("\r\n\r\n");
	css_code = css_code.replaceAll(";\r\n", " !important;\r\n");
	$("#css").text(css_code);
	$("[name=css]").text(css_code);
}

//====================
//==== Load, Save and Reset ====
//====================

//Save class component into "css_class" object
//Rebuild current component from relative components assigned to DOM
function saveClassComponent(){
	//Start object
	var object = {};
	
	//Background
	if ($(".background_components_list li").length){
		object["background"] = [];
		$(".background_components_list li").each(function(){
			var component = $(this).data("component");
			var save = {
				type: component.type,
				repeat: component.repeat,
				attachment: component.attachment,
				size: component.size,
				position: component.position,
				blend: component.blend
			};
			if (component.type=="gradient"){
				save.colors = [];
				component.colors.forEach(function(item, index){
					save.colors.push({
						color: item.color,
						location: item.location
					});
				});
				save.gradientType = component.gradientType;
				save.radialShape = component.radialShape;
				save.linearDegree = component.linearDegree;
			} else if (component.type=="image"){
				save.image = component.image;
			}
			object["background"].push(save);
		});
	}
	
	//Text
	var component = $(".text_component").data("component");
	var save = {};
	for (const property in component) {	
		if (component[property] && property != "type"){
			save[property] = component[property];
		}
	}
	if (Object.keys(save).length){
		object["text"] = save;
	}
	
	//Text-Shadow
	if ($(".text_shadow_components_list li").length){
		object["text-shadow"] = [];
		$(".text_shadow_components_list li").each(function(){
			var component = $(this).data("component");
			var save = {
				horizontal: component.horizontal,
				vertical: component.vertical,
				blur: component.blur,
				color: component.color
			};
			object["text-shadow"].push(save);
		});
	}	
	
	//Box-Shadow
	if ($(".box_shadow_components_list li").length){
		object["box-shadow"] = [];
		$(".box_shadow_components_list li").each(function(){
			var component = $(this).data("component");
			var save = {
				horizontal: component.horizontal,
				vertical: component.vertical,
				blur: component.blur,
				spread: component.spread,
				color: component.color,
				inset: component.inset
			};
			object["box-shadow"].push(save);
		});
	}	

	//Border
	component = $(".border_component").data("component");
	var save = {};
	for (const property in component) {	
		if (component[property] && property != "type"){
			save[property] = component[property];
		}
	}
	if (Object.keys(save).length){
		object["border"] = save;
	}

	//Outline
	component = $(".outline_component").data("component");
	save = {};
	if (component.style){
		save.style = component.style;
	}
	if (component.color){
		save.color = component.color;
	}
	if (component.width){
		save.width = component.width;
	}
	if (Object.keys(save).length){
		object["outline"] = save;
	}
	
	//Filter
	if ($(".filter_components_list li").length){
		object["filter"] = [];
		$(".filter_components_list li").each(function(){
			var component = $(this).data("component");
			var save = {
				filterType: component.filterType,
				blur: component.blur,
				value: component.value,
				horizontal: component.horizontal,
				vertical: component.vertical,
				shadowBlur: component.shadowBlur,
				color: component.color,
				hue: component.hue,
				location: component.location
			};
			object["filter"].push(save);
		});
	}	

	//Display
	component = $(".display_component").data("component");
	save = {};
	for (const property in component) {	
		if (component[property] && property != "type"){
			save[property] = component[property];
		}
	}
	if (Object.keys(save).length){
		object["display"] = save;
	}

	return (Object.keys(object).length ? object : null);
}

//Load class component from "css_class" object
function setClassComponent(object){
	if (object){
		//Background
		if (object["background"]){
			object["background"].forEach(function(component, index){
				if (component.type=="gradient"){
					css_background.insertGradient(component);
				} else {
					css_background.insertImage(component);
				}
			});
		}
		
		//Text
		if (object["text"]){
			$(".text_component").data("component", object["text"]);
			updatePropertiesInputs(object["text"], "text");
		}
		
		//Text-Shadow
		if (object["text-shadow"]){
			object["text-shadow"].forEach(function(component, index){
				css_text_shadow.insertShadow(component);
			});	
		}

		//Box-Shadow
		if (object["box-shadow"]){
			object["box-shadow"].forEach(function(component, index){
				css_box_shadow.insertShadow(component);
			});	
		}		
		
		//Border
		if (object["border"]){
			$(".border_component").data("component", object["border"]);
			updatePropertiesInputs(object["border"], "border");
		}
		
		//Outline
		if (object["outline"]){
			$(".outline_component").data("component", object["outline"]);
			updatePropertiesInputs(object["outline"], "outline");
		}
		
		//Filter
		if (object["filter"]){
			object["filter"].forEach(function(component, index){
				css_filter.insertFilter(component);
			});	
		}
		
		//Display
		if (object["display"]){
			$(".display_component").data("component", object["display"]);
			updatePropertiesInputs(object["display"], "display");
		}
	}
}

//Reset all class inputs
function resetClassInputs(){
	//Background
	$(".background_components_list li").remove();
	$(".background_component").hide();
	$(".background_component_empty").show();
	
	//Text
	$(".text_component [data-input]").val("");
	$(".text_component [data-unit]").val("px");
	$(".text_component").data("component", new css_text());
	
	//Text-Shadow
	$(".text_shadow_components_list li").remove();
	$(".text_shadow_component").hide();
	$(".text_shadow_component_empty").show();
	
	//Box-Shadow
	$(".box_shadow_components_list li").remove();
	$(".box_shadow_component").hide();
	$(".box_shadow_component_empty").show();

	//Border
	$(".border_component [data-input]").val("");
	$(".border_component [data-unit]").val("px");
	$(".border_component").data("component", new css_border());

	//Outline
	$(".outline_component [data-input]").val("");
	$(".outline_component [data-unit]").val("px");
	$(".outline_component").data("component", new css_outline());

	//Filter
	$(".filter_components_list li").remove();
	$(".filter_component").hide();
	$(".filter_component_empty").show();

	//Display
	$(".display_component [data-input]").val("");
	$(".display_component [control]").val("general");
	$(".display_component [data-unit]").val("px");
	$(".display_component").data("component", new css_display());
}

//Load class from database stored json
function setClass(json){
	var object = JSON.parse(json);
	for (const [pseudo, values] of Object.entries(css_class)){
		pseudo_class = pseudo;
		if (object[pseudo]){
			css_class[pseudo].object = object[pseudo];
		}
	}
}

//Bind mouse wheel number scroll
$("input[type=number]").bind("mousewheel", function(event, delta){
	if ($(this).is(":focus")){
		if (this.value==""){
			this.value = 0;
		}
		if (delta > 0){
			this.value = parseInt(this.value) + 1;
		} else {
			this.value = parseInt(this.value) - 1;
		}
		$(this).trigger("input");
		return false;
	}
});

//List available custom fonts
function initializeFonts(){
	let { fonts } = document;
	const entries = fonts.entries();
	let array = ["Arial","Arial Black","Calibri","Century Gothic","Comic Sans MS","Georgia","Gadget","Helvetica","Impact","Sans-Serif","Tahoma","Times New Roman","Trebuchet MS","Verdana"];
	let done = false;

	while (!done){
		const font = entries.next();
		if (!font.done && !array.includes(font.value[0].family)){
			array.push(font.value[0].family);
		} else {
			done = font.done;
		}
	}
	
	//Append to list
	$("[data-input=font_family]").append("<option value=''>Default</option>");
	for (font in array){
		$("[data-input=font_family]").append("<option value='" + array[font] + "'>" + array[font] + "</option>");
	}
}

//Initialize fonts list
initializeFonts();

//Trigger standard pseudo class on load
$(document).ready(function(){
	$("[pseudo-class=standard]").trigger("click");
});
</script>