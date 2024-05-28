<?
//Disable direct access
if (!isset($base_url)){ include "../404.php"; exit(); }

//Set include path relative to working directory
$crud_directory = (basename(getcwd())==$panel_folder ? "" : $base_url . $panel_folder . "/");

//Initial parameters
if (!isset($crud_data["table"])){ $crud_data["table"] = $mysqltable; }

//If join statement is used, append table name to columns
if ($crud_data["join"]){
	foreach ($crud_data["columns"] AS $key=>$column){
		//If not joined or custom column, append table name to column
		if (strpos($column[0], ".") === false && !$crud_data["select_extra"][$column[0]]){
			$crud_data["columns"][$key][0] = $crud_data["table"] . "." . $column[0];
		}
	}
}

//Prepare select statement
if (!isset($crud_data["select"])){
	$columns = array();
	foreach ($crud_data["columns"] AS $key=>$column){
		//Custom columns
		if ($crud_data["select_extra"][$column[0]]){
			array_push($columns, $crud_data["select_extra"][$column[0]] . " AS " . $column[0]);
		
		//Table columns
		} else {
			//If join is set column will be selected AS "table_name_column_name" from "table_name.column_name"
			array_push($columns, (!$crud_data["join"] ? $column[0] : $column[0] . " AS " . str_replace(".", "_", $column[0])));
		}
	}
	//Append order field if set & id by default
	if ($crud_data["order_field"]){
		array_push($columns, $crud_data["order_field"]);
	}
	array_push($columns, ($crud_data["join"] ? $crud_data["table"] . ".id" : "id"));
	$columns = array_unique($columns);
	$crud_data["select"] = implode(", ", $columns);
}

if (!isset($crud_data["filename"])){ $crud_data["filename"] = (getPageTitle($base_name,false) ?: dateLanguage("d F Y", time())); }
if (!isset($crud_data["data_rows"])){ $crud_data["data_rows"] = 20; }
if (!isset($crud_data["order_by"])){ $crud_data["order_by"] = ($crud_data["join"] ? $crud_data["table"] . ".id DESC" : "id DESC"); }
if (!isset($crud_data["multiple_operations"])){ $crud_data["multiple_operations"] = false; }
if (!isset($crud_data["export_raw"])){ $crud_data["export_raw"] = true; }
if (!isset($crud_data["export_excel"])){ $crud_data["export_excel"] = true; }
if (!isset($crud_data["export_pdf"])){ $crud_data["export_pdf"] = true; }
if (!isset($crud_data["table_class"])){ $crud_data["table_class"] = 'crud'; }
if (!isset($crud_data["hide_tips"])){ $crud_data["hide_tips"] = false; }

$crud_id = uniqid('_crud_');

//Encrypt CRUD Data
$crud_data_encrypted = encryptText(json_encode($crud_data, JSON_UNESCAPED_UNICODE)); ?>

<div class='<?=$crud_data["table_class"]?>' id='<?=$crud_id?>'>

	<!-- Buttons -->
	<div class=crud_buttons>
		<!-- Reset, Add and Search -->
		<button class="btn btn-default btn-md crud-btn" onclick="<?=$crud_id?>.crudResetTable()"><i class="fas fa-sync"></i>&nbsp;&nbsp;<?=readLanguage(crud,button_reload)?></button>&nbsp;&nbsp;
		<? if ($crud_data["buttons"][0]){ ?><button class="btn btn-default btn-md crud-btn" onclick="<?=$crud_id?>.crudSetOperation()"><i class="fas fa-plus"></i>&nbsp;&nbsp;<?=readLanguage(crud,button_add)?></button>&nbsp;&nbsp;<? } ?>
		<? if ($crud_data["buttons"][1]){ ?><button class="btn btn-default btn-md crud-btn" onclick="<?=$crud_id?>.crudTriggerSearch()"><i class="fas fa-search"></i>&nbsp;&nbsp;<?=readLanguage(crud,button_search)?></button>&nbsp;&nbsp;<? } ?>
		
		<!-- Export -->
		<? if ($crud_data["export_raw"] || $crud_data["export_excel"] || $crud_data["export_pdf"]){ ?>
		<div class=dropdown>
			<button class="btn btn-default btn-md crud-btn" data-toggle=dropdown><i class="fas fa-file-download"></i>&nbsp;&nbsp;<?=readLanguage(crud,button_export)?>&nbsp;&nbsp;<span class="fas fa-angle-down"></span></button>
			<ul class="dropdown-menu animate compact">
				<? if ($crud_data["export_raw"]){ ?><li><a onclick="<?=$crud_id?>.crudExportRaw()"><i class="fas fa-print"></i>&nbsp;&nbsp;<?=readLanguage(crud,button_raw)?></a></li><? } ?>
				<? if ($crud_data["export_excel"]){ ?><li><a onclick="<?=$crud_id?>.crudExportExcel()"><i class="fas fa-file-excel"></i>&nbsp;&nbsp;<?=readLanguage(crud,button_excel)?></a></li><? } ?>
				<? if ($crud_data["export_pdf"]){ ?><li><a onclick="<?=$crud_id?>.crudExportPDF()"><i class="fas fa-file-pdf"></i>&nbsp;&nbsp;<?=readLanguage(crud,button_pdf)?></a></li><? } ?>
			</ul>
		</div>&nbsp;&nbsp;
		<? } ?>
		
		<!-- Multiple Operations -->
		<? if ($crud_data["multiple_operations"]){ ?>
		<div class=dropdown>
			<button class="btn btn-default btn-md crud-btn" data-toggle=dropdown aria-haspopup=true aria-expanded=false><i class="fas fa-cogs"></i>&nbsp;&nbsp;<?=readLanguage(crud,button_operations)?>&nbsp;&nbsp;<span class="fas fa-angle-down"></span></button>
			<ul class="dropdown-menu animate compact">
				<? foreach ($crud_data["multiple_operations"] AS $key=>$operation){ ?>
				<li><a onclick="<?=$crud_id?>.crudMultipleOperations('<?=$operation[1]?>','<?=$operation[0]?>')"><i class="<?=$operation[2]?>"></i>&nbsp;&nbsp;<?=$operation[1]?></a></li>
				<? } ?>
			</ul>
		</div>&nbsp;&nbsp;
		<? } ?>
	</div>

	<!-- Records per page -->
	<div class=crud_records>
		<span><?=readLanguage(crud,page_records)?></span>&nbsp;&nbsp;
		<select data-records-per-page onchange="<?=$crud_id?>.crudRecordsPerPage(this.value)">
			<option value=5>5</option>
			<option value=10>10</option>
			<option value=20>20</option>
			<option value=50>50</option>
			<option value=100>100</option>
			<option value=200>200</option>
		</select>
	</div>

	<!-- Main table -->
	<div class=crud_table_wrapper><div data-crud-table-container>
		<table class=crud_table data-crud-table>
		<!-- Header -->
		<tr class="nodrag nodrop" data-crud-header>
			<?
			$exportable = array();
			if ($crud_data["order_field"]){ print "<th class='drag-head fixed-left'></th>"; }
			if ($crud_data["multiple_operations"]){ print "<th class='check-head fixed-left'><input type=checkbox name=crud_checkbox_handler></th>"; }
			foreach ($crud_data["columns"] AS $key => $value){
				$row_function = explode("(",$value[4])[0];
				$date_functions = array("date", "dateLanguage");
				$allowable_arrange_functions = array("date", "dateLanguage", "getVariable", "hasVal", "number_format");
				
				if (!$value[4] || in_array($row_function, $allowable_arrange_functions) || $value[9]){
					$order_asc_button = "<a onclick=\"$crud_id.crudArrange($key, 'ASC')\" class='sort sort_asc'><i class='glyphicon glyphicon-triangle-top'></i></a>";
					$order_desc_button = "<a onclick=\"$crud_id.crudArrange($key, 'DESC')\" class='sort sort_desc'><i class='glyphicon glyphicon-triangle-bottom'></i></a>";
				} else {
					$order_asc_button = null;
					$order_desc_button = null;
				}
				if (in_array($row_function, $date_functions)){
					$calendar_button = "<a onclick=\"$crud_id.crudTriggerCalendar($key)\" class='crud_button_sm filter_calendar'><i class='fas fa-calendar'></i></a>";
				} else {
					$calendar_button = null;
				}
				$filter_button = "<a onclick=\"$crud_id.crudTriggerFilter($key)\" class='crud_button_sm filter'><i class='fas fa-th-list'></i></a>";
				$copy_button = "<a onclick='$crud_id.crudTriggerCopy(this)' class='crud_button_sm copy'><i class='fas fa-copy'></i></a>";
				
				//Add to search select box
				if ($value[6]){ $search_options[$crud_id] .= "<option data-key='$key' value='$key'>" . $value[1] . "</option>"; }
				
				//Check if column can be exported
				//Evaluate column content if it has a function
				if ($value[4]){
					$eval = str_replace("%s",0,$value[4]);
					$eval = str_replace("%d",0,$eval);
					$column_content = eval("return " . $eval . ";");
				} else {
					$column_content = null;
				}
				
				//Check if it's excluded or has a button or image
				if (!$value[8] && strpos($column_content,"btn")===false && strpos($column_content,"img")===false){
					array_push($exportable, $value[1]);
				}
				
				//Remove right border on the cell before the last fixed-right
				$border_right = (($crud_data["columns"][$key + 1][3]=="fixed-right") || ($value[3]!="fixed-right" && $key==count($crud_data["columns"]) - 1) ? "border-right:0; " : "");
				
				//Print row
				print "<th class='" . $value[3] . (substr($value[2], -1)=="%" ? " percentage" : null) . "' style='" . $border_right . "width:" . $value[2] . "; min-width:" . $value[2] . "'>" . $order_asc_button . $order_desc_button . ($value[7] ? $copy_button : null) . $value[1] . ($value[5] ? $filter_button : null) . ($calendar_button && !$value[5] ? $calendar_button : null) . "</th>";
			}
			print "<th class='fixed-right buttons'>";
			print ($crud_data["buttons"][0] ? "<a onclick='$crud_id.crudSetOperation()' class=crud_button_sm><i class='fas fa-plus'></i></a>" : "");
			print ($crud_data["buttons"][1] ? "<a onclick='$crud_id.crudTriggerSearch()' class=crud_button_sm><i class='fas fa-search'></i></a>" : "");
			print "</th>";
			?>
		</tr>
		</table>
	</div></div>

	<!-- Footer -->
	<div class=crud_footer></div>

	<!-- Tips -->
	<? if (!$crud_data["hide_tips"]){ ?>
	<div class=crud_tips>
		<?=readLanguage(crud,tip_update)?>
		<? if ($crud_data["order_field"]){ print "<br>" . readLanguage(crud,tip_arrange); } ?>
	</div>
	<? } ?>

	<!-- Save order button -->
	<? if ($crud_data["order_field"]){ ?>
		<button class="btn btn-default btn-md crud-btn" data-crud-save-order onclick="<?=$crud_id?>.crudSaveOrderRows()"><i class="fas fa-save"></i>&nbsp;&nbsp;<?=readLanguage(crud,save_arrangment)?></button>
	<? } ?>

	<!-- Pagination -->
	<div data-pagination-container>
		<div style="clear:both"></div>
		<div class=pagination_container>
			<ul data-pagination></ul>
			<div>
				<small><?=readLanguage(crud,page_jump)?></small>&nbsp;
				<select data-page-select onchange="<?=$crud_id?>.crudLoadPage(this.value)"></select>
			</div>
		</div>
	</div>

</div>
<div style="clear:both"></div>

<script>
<? if (!$initated){ $initated = true; ?>
/* Dnd [Drag and Drop] */
!function($,window,document,undefined){var hasTouch='ontouchstart'in document.documentElement,startEvent='touchstart mousedown',moveEvent='touchmove mousemove',endEvent='touchend mouseup';$(document).ready(function(){function parseStyle(css){var objMap={},parts=css.match(/([^;:]+)/g)||[];while(parts.length)objMap[parts.shift()]=parts.shift().trim();return objMap}$('table').each(function(){if($(this).data('table')=='dnd'){$(this).tableDnD({onDragStyle:$(this).data('ondragstyle')&&parseStyle($(this).data('ondragstyle'))||null,onDropStyle:$(this).data('ondropstyle')&&parseStyle($(this).data('ondropstyle'))||null,onDragClass:$(this).data('ondragclass')==undefined&&"tDnD_whileDrag"||$(this).data('ondragclass'),onDrop:$(this).data('ondrop')&&new Function('table','row',$(this).data('ondrop')),onDragStart:$(this).data('ondragstart')&&new Function('table','row',$(this).data('ondragstart')),onDragStop:$(this).data('ondragstop')&&new Function('table','row',$(this).data('ondragstop')),scrollAmount:$(this).data('scrollamount')||5,sensitivity:$(this).data('sensitivity')||10,hierarchyLevel:$(this).data('hierarchylevel')||0,indentArtifact:$(this).data('indentartifact')||'<div class="indent">&nbsp;</div>',autoWidthAdjust:$(this).data('autowidthadjust')||true,autoCleanRelations:$(this).data('autocleanrelations')||true,jsonPretifySeparator:$(this).data('jsonpretifyseparator')||'\t',serializeRegexp:$(this).data('serializeregexp')&&new RegExp($(this).data('serializeregexp'))||/[^\-]*$/,serializeParamName:$(this).data('serializeparamname')||false,dragHandle:$(this).data('draghandle')||null})}})});jQuery.tableDnD={currentTable:null,dragObject:null,mouseOffset:null,oldX:0,oldY:0,build:function(options){this.each(function(){this.tableDnDConfig=$.extend({onDragStyle:null,onDropStyle:null,onDragClass:"tDnD_whileDrag",onDrop:null,onDragStart:null,onDragStop:null,scrollAmount:5,sensitivity:10,hierarchyLevel:0,indentArtifact:'<div class="indent">&nbsp;</div>',autoWidthAdjust:true,autoCleanRelations:true,jsonPretifySeparator:'\t',serializeRegexp:/[^\-]*$/,serializeParamName:false,dragHandle:null},options||{});$.tableDnD.makeDraggable(this);this.tableDnDConfig.hierarchyLevel&&$.tableDnD.makeIndented(this)});return this},makeIndented:function(table){var config=table.tableDnDConfig,rows=table.rows,firstCell=$(rows).first().find('td:first')[0],indentLevel=0,cellWidth=0,longestCell,tableStyle;if($(table).hasClass('indtd'))return null;tableStyle=$(table).addClass('indtd').attr('style');$(table).css({whiteSpace:"nowrap"});for(var w=0;w<rows.length;w++){if(cellWidth<$(rows[w]).find('td:first').text().length){cellWidth=$(rows[w]).find('td:first').text().length;longestCell=w}}$(firstCell).css({width:'auto'});for(w=0;w<config.hierarchyLevel;w++)$(rows[longestCell]).find('td:first').prepend(config.indentArtifact);firstCell&&$(firstCell).css({width:firstCell.offsetWidth});tableStyle&&$(table).css(tableStyle);for(w=0;w<config.hierarchyLevel;w++)$(rows[longestCell]).find('td:first').children(':first').remove();config.hierarchyLevel&&$(rows).each(function(){indentLevel=$(this).data('level')||0;indentLevel<=config.hierarchyLevel&&$(this).data('level',indentLevel)||$(this).data('level',0);for(var i=0;i<$(this).data('level');i++)$(this).find('td:first').prepend(config.indentArtifact)});return this},makeDraggable:function(table){var config=table.tableDnDConfig;config.dragHandle&&$(config.dragHandle,table).each(function(){$(this).bind(startEvent,function(e){$.tableDnD.initialiseDrag($(this).parents('tr')[0],table,this,e,config);return false})})||$(table.rows).each(function(){if(!$(this).hasClass("nodrag")){$(this).bind(startEvent,function(e){if(e.target.tagName=="TD"){$.tableDnD.initialiseDrag(this,table,this,e,config);return false}}).css("cursor","move")}else{$(this).css("cursor","")}})},currentOrder:function(){var rows=this.currentTable.rows;return $.map(rows,function(val){return($(val).data('level')+val.id).replace(/\s/g,'')}).join('')},initialiseDrag:function(dragObject,table,target,e,config){this.dragObject=dragObject;this.currentTable=table;this.mouseOffset=this.getMouseOffset(target,e);this.originalOrder=this.currentOrder();$(document).bind(moveEvent,this.mousemove).bind(endEvent,this.mouseup);config.onDragStart&&config.onDragStart(table,target)},updateTables:function(){this.each(function(){if(this.tableDnDConfig)$.tableDnD.makeDraggable(this)})},mouseCoords:function(e){if(e.originalEvent.changedTouches)return{x:e.originalEvent.changedTouches[0].clientX,y:e.originalEvent.changedTouches[0].clientY};if(e.pageX||e.pageY)return{x:e.pageX,y:e.pageY};return{x:e.clientX+document.body.scrollLeft-document.body.clientLeft,y:e.clientY+document.body.scrollTop-document.body.clientTop}},getMouseOffset:function(target,e){var mousePos,docPos;e=e||window.event;docPos=this.getPosition(target);mousePos=this.mouseCoords(e);return{x:mousePos.x-docPos.x,y:mousePos.y-docPos.y}},getPosition:function(element){var left=0,top=0;if(element.offsetHeight==0)element=element.firstChild;while(element.offsetParent){left+=element.offsetLeft;top+=element.offsetTop;element=element.offsetParent}left+=element.offsetLeft;top+=element.offsetTop;return{x:left,y:top}},autoScroll:function(mousePos){var config=this.currentTable.tableDnDConfig,yOffset=window.pageYOffset,windowHeight=window.innerHeight?window.innerHeight:document.documentElement.clientHeight?document.documentElement.clientHeight:document.body.clientHeight;if(document.all)if(typeof document.compatMode!='undefined'&&document.compatMode!='BackCompat')yOffset=document.documentElement.scrollTop;else if(typeof document.body!='undefined')yOffset=document.body.scrollTop;mousePos.y-yOffset<config.scrollAmount&&window.scrollBy(0,-config.scrollAmount)||windowHeight-(mousePos.y-yOffset)<config.scrollAmount&&window.scrollBy(0,config.scrollAmount)},moveVerticle:function(moving,currentRow){if(0!=moving.vertical&&currentRow&&this.dragObject!=currentRow&&this.dragObject.parentNode==currentRow.parentNode)0>moving.vertical&&this.dragObject.parentNode.insertBefore(this.dragObject,currentRow.nextSibling)||0<moving.vertical&&this.dragObject.parentNode.insertBefore(this.dragObject,currentRow)},moveHorizontal:function(moving,currentRow){var config=this.currentTable.tableDnDConfig,currentLevel;if(!config.hierarchyLevel||0==moving.horizontal||!currentRow||this.dragObject!=currentRow)return null;currentLevel=$(currentRow).data('level');0<moving.horizontal&&currentLevel>0&&$(currentRow).find('td:first').children(':first').remove()&&$(currentRow).data('level',--currentLevel);0>moving.horizontal&&currentLevel<config.hierarchyLevel&&$(currentRow).prev().data('level')>=currentLevel&&$(currentRow).children(':first').prepend(config.indentArtifact)&&$(currentRow).data('level',++currentLevel)},mousemove:function(e){var dragObj=$($.tableDnD.dragObject),config=$.tableDnD.currentTable.tableDnDConfig,currentRow,mousePos,moving,x,y;e&&e.preventDefault();if(!$.tableDnD.dragObject)return false;e.type=='touchmove'&&event.preventDefault();config.onDragClass&&dragObj.addClass(config.onDragClass)||dragObj.css(config.onDragStyle);mousePos=$.tableDnD.mouseCoords(e);x=mousePos.x-$.tableDnD.mouseOffset.x;y=mousePos.y-$.tableDnD.mouseOffset.y;$.tableDnD.autoScroll(mousePos);currentRow=$.tableDnD.findDropTargetRow(dragObj,y);moving=$.tableDnD.findDragDirection(x,y);$.tableDnD.moveVerticle(moving,currentRow);$.tableDnD.moveHorizontal(moving,currentRow);return false},findDragDirection:function(x,y){var sensitivity=this.currentTable.tableDnDConfig.sensitivity,oldX=this.oldX,oldY=this.oldY,xMin=oldX-sensitivity,xMax=oldX+sensitivity,yMin=oldY-sensitivity,yMax=oldY+sensitivity,moving={horizontal:x>=xMin&&x<=xMax?0:x>oldX?-1:1,vertical:y>=yMin&&y<=yMax?0:y>oldY?-1:1};if(moving.horizontal!=0)this.oldX=x;if(moving.vertical!=0)this.oldY=y;return moving},findDropTargetRow:function(draggedRow,y){var rowHeight=0,rows=this.currentTable.rows,config=this.currentTable.tableDnDConfig,rowY=0,row=null;for(var i=0;i<rows.length;i++){row=rows[i];rowY=this.getPosition(row).y;rowHeight=parseInt(row.offsetHeight)/2;if(row.offsetHeight==0){rowY=this.getPosition(row.firstChild).y;rowHeight=parseInt(row.firstChild.offsetHeight)/2}if(y>(rowY-rowHeight)&&y<(rowY+rowHeight))if(draggedRow.is(row)||(config.onAllowDrop&&!config.onAllowDrop(draggedRow,row))||$(row).hasClass("nodrop"))return null;else return row}return null},processMouseup:function(){if(!this.currentTable||!this.dragObject)return null;var config=this.currentTable.tableDnDConfig,droppedRow=this.dragObject,parentLevel=0,myLevel=0;$(document).unbind(moveEvent,this.mousemove).unbind(endEvent,this.mouseup);config.hierarchyLevel&&config.autoCleanRelations&&$(this.currentTable.rows).first().find('td:first').children().each(function(){myLevel=$(this).parents('tr:first').data('level');myLevel&&$(this).parents('tr:first').data('level',--myLevel)&&$(this).remove()})&&config.hierarchyLevel>1&&$(this.currentTable.rows).each(function(){myLevel=$(this).data('level');if(myLevel>1){parentLevel=$(this).prev().data('level');while(myLevel>parentLevel+1){$(this).find('td:first').children(':first').remove();$(this).data('level',--myLevel)}}});config.onDragClass&&$(droppedRow).removeClass(config.onDragClass)||$(droppedRow).css(config.onDropStyle);this.dragObject=null;config.onDrop&&this.originalOrder!=this.currentOrder()&&$(droppedRow).hide().fadeIn('fast')&&config.onDrop(this.currentTable,droppedRow);config.onDragStop&&config.onDragStop(this.currentTable,droppedRow);this.currentTable=null},mouseup:function(e){e&&e.preventDefault();$.tableDnD.processMouseup();return false},jsonize:function(pretify){var table=this.currentTable;if(pretify)return JSON.stringify(this.tableData(table),null,table.tableDnDConfig.jsonPretifySeparator);return JSON.stringify(this.tableData(table))},serialize:function(){return $.param(this.tableData(this.currentTable))},serializeTable:function(table){var result="";var paramName=table.tableDnDConfig.serializeParamName||table.id;var rows=table.rows;for(var i=0;i<rows.length;i++){if(result.length>0)result+="&";var rowId=rows[i].id;if(rowId&&table.tableDnDConfig&&table.tableDnDConfig.serializeRegexp){rowId=rowId.match(table.tableDnDConfig.serializeRegexp)[0];result+=paramName+'[]='+rowId}}return result},serializeTables:function(){var result=[];$('table').each(function(){this.id&&result.push($.param(this.tableData(this)))});return result.join('&')},tableData:function(table){var config=table.tableDnDConfig,previousIDs=[],currentLevel=0,indentLevel=0,rowID=null,data={},getSerializeRegexp,paramName,currentID,rows;if(!table)table=this.currentTable;if(!table||!table.rows||!table.rows.length)return{error:{code:500,message:"Not a valid table."}};if(!table.id&&!config.serializeParamName)return{error:{code:500,message:"No serializable unique id provided."}};rows=config.autoCleanRelations&&table.rows||$.makeArray(table.rows);paramName=config.serializeParamName||table.id;currentID=paramName;getSerializeRegexp=function(rowId){if(rowId&&config&&config.serializeRegexp)return rowId.match(config.serializeRegexp)[0];return rowId};data[currentID]=[];!config.autoCleanRelations&&$(rows[0]).data('level')&&rows.unshift({id:'undefined'});for(var i=0;i<rows.length;i++){if(config.hierarchyLevel){indentLevel=$(rows[i]).data('level')||0;if(indentLevel==0){currentID=paramName;previousIDs=[]}else if(indentLevel>currentLevel){previousIDs.push([currentID,currentLevel]);currentID=getSerializeRegexp(rows[i-1].id)}else if(indentLevel<currentLevel){for(var h=0;h<previousIDs.length;h++){if(previousIDs[h][1]==indentLevel)currentID=previousIDs[h][0];if(previousIDs[h][1]>=currentLevel)previousIDs[h][1]=0}}currentLevel=indentLevel;if(!$.isArray(data[currentID]))data[currentID]=[];rowID=getSerializeRegexp(rows[i].id);rowID&&data[currentID].push(rowID)}else{rowID=getSerializeRegexp(rows[i].id);rowID&&data[currentID].push(rowID)}}return data}};jQuery.fn.extend({tableDnD:$.tableDnD.build,tableDnDUpdate:$.tableDnD.updateTables,tableDnDSerialize:$.proxy($.tableDnD.serialize,$.tableDnD),tableDnDSerializeAll:$.tableDnD.serializeTables,tableDnDData:$.proxy($.tableDnD.tableData,$.tableDnD)})}(jQuery,window,window.document);

/* Double Scroll */
!function(l){jQuery.fn.doubleScroll=function(e){var o={contentElement:void 0,scrollCss:{"overflow-x":"auto","overflow-y":"hidden",height:"15px"},contentCss:{"overflow-x":"auto","overflow-y":"hidden"},onlyIfScroll:!0,resetOnWindowResize:!1,timeToWaitForResize:30};l.extend(!0,o,e),l.extend(o,{topScrollBarMarkup:'<div class="doubleScroll-scroll-wrapper"><div class="doubleScroll-scroll"></div></div>',topScrollBarWrapperSelector:".doubleScroll-scroll-wrapper",topScrollBarInnerSelector:".doubleScroll-scroll"});var r=function(e,o){if(o.onlyIfScroll&&e.get(0).scrollWidth<=e.width()*1.05)e.prev(o.topScrollBarWrapperSelector).remove();else{var r,t=e.prev(o.topScrollBarWrapperSelector);if(0==t.length){t=l(o.topScrollBarMarkup),e.before(t),t.css(o.scrollCss),l(o.topScrollBarInnerSelector).css("height","15px"),e.css(o.contentCss),t.bind("scroll.doubleScroll",function(){e.scrollLeft(t.scrollLeft())});e.bind("scroll.doubleScroll",function(){t.scrollLeft(e.scrollLeft())})}r=void 0!==o.contentElement&&0!==e.find(o.contentElement).length?e.find(o.contentElement):e.find(">:first-child"),l(o.topScrollBarInnerSelector,t).width(r.outerWidth()),t.width(e.width()),t.scrollLeft(e.scrollLeft())}};return this.each(function(){var e=l(this);if(r(e,o),o.resetOnWindowResize){var t,c=function(l){r(e,o)};l(window).bind("resize.doubleScroll",function(){clearTimeout(t),t=setTimeout(c,o.timeToWaitForResize)})}})}}(jQuery);

class Crud {
	//Binding crud options
	constructor (options){
		this.id = options.id;
		this.container = $('#' + this.id);
		this.encryptedData = options.encryptedData;
		this.crud_records_per_page = +options.perPage;
		this.orderField = options.orderField;
		this.exportable = options.exportable;
		this.fileName = options.fileName;
		this.original_priorities = [];
		this.current_page = 0;
		this.num_rows = 0;
		this.search_options = options.searchOptions;
		this.new_window = false;
		this.query_statement = {
			order: {},
			limit: [],
			search: {},
			calendar: {},
			filter: {}
		};
		this.bindEvents();
	}

	//Main function
	crudLoadPage(page){
		page = +page;
		this.crudStartLoading();

		this.current_page = page;
		let limit_min = (page - 1) * this.crud_records_per_page;
		let limit_max = this.crud_records_per_page;

		this.query_statement.limit = [limit_min, limit_max];

		$.ajax({
			method: 'POST',
			url: '<?=$crud_directory?>crud/_operations.php',
			data: {
				token: '<?=$token?>',
				action: 'count-rows',
				crud_data: this.encryptedData,
				query: JSON.stringify(this.query_statement),
			},
			success: (response) => {
				//Set number of rows
				this.num_rows = +response;

				//Rebuild pagination
				let pages = Math.ceil(this.num_rows / this.crud_records_per_page);
				this.container.find('[data-pagination]').empty();
				this.container.find('[data-page-select]').empty();

				if (pages > 1){
					let temp = -1;
					let delta = 2;
					let left = this.current_page - delta;
					let right = this.current_page + delta;
					let range = [];
					let range_truncated = [];

					for (let i = 0; i <= pages; i++){
						if (i == 1 || i == pages || i >= left && i < right){
							range.push(i);
						}
					}

					for (let i = 0; i < range.length; i++){
						if (temp != -1){
							if (range[i] - temp === 2) range_truncated.push(temp + 1);
							else if (range[i] - temp !== 1) range_truncated.push('...');
						}

						range_truncated.push(range[i]);
						temp = range[i];
					}

					range_truncated.forEach((value) => {
						if (value){
							let button_class = this.current_page == value ? 'active' : 'standard';
							if (!isNaN(value)){
								this.container.find('[data-pagination]').append($('<li>').html('<a class="btn btn-primary btn-sm ' + button_class + '" onclick="' + this.id + '.crudLoadPage(' + value + ')">' + value + '</a>'));
							} else {
								this.container.find('[data-pagination]').append($('<li class=dots>').html('...'));
							}
						}
					});

					for (let i = 1; i <= pages; i++){
						let selected = this.current_page == i ? 'selected' : '';
						this.container.find('[data-page-select]').append('<option value="' + i + '" ' + selected + '>' + i + '</option>');
					}

					this.container.find('[data-pagination-container]').show();

				} else this.container.find('[data-pagination-container]').hide();

				//Update last row content
				if (this.num_rows > 0){
					let remaining_records = this.crud_records_per_page + (this.num_rows - (pages * this.crud_records_per_page));
					let rows_to = 0;

					if (page == pages){
						rows_to = page * this.crud_records_per_page - (this.crud_records_per_page - remaining_records);
					} else {
						rows_to = page * this.crud_records_per_page;
					}

					this.container.find('.crud_footer').html('<?=readLanguage(crud,records_showing)?> ' + (limit_min + 1) + ' <?=readLanguage(crud,records_to)?> ' + rows_to + ' <?=readLanguage(crud,records_of)?> ' + this.num_rows);
				} else {
					this.container.find('.crud_footer').html('<?=readLanguage(crud,records_empty)?>');
				}

				$.ajax({
					method: 'POST',
					url: '<?=$crud_directory?>crud/_operations.php',
					data: {
						token: '<?=$token?>',
						action: 'operation',
						id: this.id,
						crud_data: this.encryptedData,
						query: JSON.stringify(this.query_statement),
					},
					success: (response) => {
						//Remove current table rows & insert new ones
						this.container.find('[data-crud-table] tr:gt(0)').remove();
						this.container.find('[data-crud-header]').after(response);

						//Fix Dropdown Issues
						let dropdown_cell = null;
						this.container.find('.crud-dropdown-container').on('show.bs.dropdown', function (){
							dropdown_cell = $(this).parent();
							let position = $(this)[0].getBoundingClientRect();

							$(this).css({
								position: 'absolute',
								top: position.top + window.scrollY + 'px',
								left: position.left + 'px',
								width: position.width + 'px',
								height: position.height + 'px'
							});

							$('body').append($(this).detach());
							dropdown_cell.append('<div class=compensation style="height:' + $(this).height() + 'px"></div>');
						});

						this.container.find('.crud-dropdown-container').on('hide.bs.dropdown', function(){
							let container = $(this).detach();
							container.css({ position: 'initial' });
							dropdown_cell.find('.compensation').remove();
							dropdown_cell.append(container);
						});

						//Fixed left columns
						this.container.find('td.fixed-left').each(function (){
							let parent_position = $(this).parent()[0].getBoundingClientRect();
							let position = $(this)[0].getBoundingClientRect();
							$(this).css({ left: (position.left - parent_position.left) + 'px' });
						});

						//Fixed right columns
						this.container.find('td.fixed-right').each(function (){
							let parent_position = $(this).parent()[0].getBoundingClientRect();
							let position = $(this)[0].getBoundingClientRect();
							$(this).css({right: (parent_position.width + parent_position.left - position.right) + 'px'});
						});

						//Multiple checkbox handler
						this.container.find('[name=crud_row_check]').change((e) => {
							let row = e.target;
							if (!row.checked) this.container.find('[name=crud_checkbox_handler]').prop('checked', false);
						});

						this.crudEndLoading();

						//Bind double scroll
						this.container.find('[data-crud-table-container]').doubleScroll({
							resetOnWindowResize: true,
							onlyIfScroll: true
						});

						if (this.orderField){
							this.container.find('[data-crud-table]').tableDnD({
								onDragClass: 'row_drag',
								dragHandle: '.drag-cell',
							});
							this.crudResetOrderRows();
						}
					},
				});
			}
		});
	}

	//Reset crud table
	crudResetTable(){
		this.query_statement = {
			order: {},
			limit: [],
			search: {},
			calendar: {},
			filter: {}
		};
		this.container.find('[data-crud-save-order]').prop('disabled', false);
		this.crudLoadPage(1);
	}

	//Bind crud specific events
	bindEvents(){
		//Handle new window
		$(document).keydown(e => this.new_window = e.keyCode == "17" ? true : void(0));
		$(document).keyup(e => this.new_window = e.keyCode == "17" ? false : void(0));

		//Multiple check handler
		this.container.find('[name=crud_checkbox_handler]').change(function(){
			$('[name=crud_row_check]').prop('checked', this.checked);
		});

		//Fixed left coulmns
		this.container.find("th.fixed-left").each(function(){
			let parent_position = $(this).parent()[0].getBoundingClientRect();
			let position = $(this)[0].getBoundingClientRect();
			$(this).css({ left: (position.left - parent_position.left) + 'px' });
		});

		//Fixed right coulmns
		this.container.find("th.fixed-right").each(function(){
			let parent_position = $(this).parent()[0].getBoundingClientRect();
			let position = $(this)[0].getBoundingClientRect();
			$(this).css({ right: (parent_position.width + parent_position.left - position.right) + 'px'});
		});

		//Close dropdown on scroll
		this.container.find('[data-crud-table-container]').on('scroll', function (){
			$(this).find('.crud-dropdown-container.open').find('[data-toggle]').dropdown('toggle');
		});
	}

	/* ====== [Arrange By] ====== */

	//Arrange crud
	crudArrange(field, direction, reset = true){
		if (reset) this.query_statement.order = {};
		this.query_statement.order[field] = direction;
		this.container.find('[data-crud-save-order]').prop('disabled', true);
		this.crudLoadPage(this.current_page);
	}

	/* ====== [Filter Record] ====== */

	//Filter crud
	crudTriggerFilter(field){
		let parent = this;
		let return_array = [];
		$.confirm({
			title: '<?=readLanguage(crud,filter)?>',
			content: function(){

				return $.ajax({
					method: 'POST',
					url: '<?=$crud_directory?>crud/_operations.php',
					data: {
						token: '<?=$token?>',
						action: 'filter',
						crud_data: parent.encryptedData,
						query: JSON.stringify(parent.query_statement),
						field: field
					},
					success: (response) => {
						return_array = JSON.parse(response);
						let filter_options = '';
						for (let i = 0; i < return_array.length; i++){
							if (return_array[i][1] && return_array[i][1].replace(/<[^>]*>/g,"")){
								filter_options += '<option value="' + return_array[i][0] + '">' + return_array[i][1] + '</option>';
							} else {
								filter_options += '<option value="' + return_array[i][0] + '"><?=readLanguage(general,na)?></option>';
							}
						}
						this.setContent('<span class=crud_input><select data-filter-select multiple>' + filter_options + '</select><small><?=readLanguage(crud,filter_tip)?></small></span>');
					}
				})
			},
			icon: 'fas fa-filter',
			buttons: {
				formSubmit: {
					text: '<?=readLanguage(crud,filter)?>',
					btnClass: 'btn-blue',
					action: function (){
						let selected = this.$content.find("[data-filter-select] option:selected").length;
						if (selected){
							let selected_values = [];
							this.$content.find("[data-filter-select] option:selected").each(function(){
								selected_values.push($(this).val());
							});
							parent.query_statement.filter[field] = selected_values;
							parent.crudLoadPage(1);
						} else return false;
					}
				},
				cancel: { text: '<?=readLanguage(crud,cancel)?>' }
			}
		});
	}

	/* ====== [Trigger Calendar] ====== */

	//Filter calendar
	crudTriggerCalendar(field){
		let parent = this;
		$.confirm({
			title: '<?=readLanguage(crud,calendar)?>',
			content: "<table class=data_table><tr><td class=title style='width:20%'><?=readLanguage(crud,calendar_start)?></td><td><input type=text id=date_from class=date_field readonly></td></tr><tr><td class=title style='width:20%'><?=readLanguage(crud,calendar_end)?></td><td><input type=text id=date_to class=date_field readonly></td></tr></table>",
			icon: 'fas fa-calendar',
			theme: 'light-noborder',
			onOpenBefore: function (){
				this.showLoading();
			},
			onContentReady: function (){
				createCalendar(this.$content.find('#date_from'), new Date(), null, null, null, this.$content.find('#date_from').parent()[0]);
				createCalendar(this.$content.find('#date_to'), new Date(), null, null, null, this.$content.find('#date_to').parent()[0]);
				this.hideLoading();
			},
			buttons: {
				submit: {
					text: '<?=readLanguage(crud,calendar_filter)?>',
					btnClass: 'btn-blue',
					action: function (){
						let value_from = this.$content.find("#date_from").val().split("/");
						let value_to = this.$content.find("#date_to").val().split("/");
						let crud_date_from = Math.round(new Date(value_from[2], value_from[1] - 1, value_from[0], 0, 0, 0, 0).getTime() / 1000);
						let crud_date_to = Math.round(new Date(value_to[2], value_to[1] - 1, value_to[0], 23, 59, 59, 0).getTime() / 1000);
						if (crud_date_from && crud_date_to){
							parent.query_statement.calendar[field] = [crud_date_from, crud_date_to];
							parent.crudLoadPage(1);
						} else return false;
					}
				},
				cancel: { text: '<?=readLanguage(crud,cancel)?>' }
			}
		});
	}


	/* ====== [Trigger Calendar] ====== */

	//Search crud
	crudTriggerSearch(){
		let parent = this;
		$.confirm({
			title: '<?=readLanguage(crud,search)?>',
			content: "<table class=data_table><tr><td class=title style=\"width:20%\"><?=readLanguage(crud,search_in)?></td><td><select data-search-column>" + parent.search_options + "</select></td></tr><tr><td class=title style=\"width:20%\"><?=readLanguage(crud,search_for)?></td><td><input data-search-value type=text></td></tr></table>",
			icon: 'fas fa-search',
			theme: 'light-noborder',
			buttons: {
				submit: {
					text: '<?=readLanguage(crud,search)?>',
					btnClass: 'btn-blue',
					action: function (){
						let index = this.$content.find('[data-search-column]').val();
						let value = this.$content.find('[data-search-value]').val();
						if (index != '' && value != ''){
							parent.query_statement.search[index] = value;
							parent.crudLoadPage(1);
						} else return false;
					}
				},
				cancel: { text: '<?=readLanguage(crud,cancel)?>' }
			}
		});
	}

	/* ====== [Delete Record] ====== */

	//Delete crud
	crudTriggerDelete(id, title){
		$.confirm({
			title: '<?=readLanguage(crud,delete_title)?>',
			content: '<span class=crud_input><?=readLanguage(crud,delete_message)?>' + (title ? '<b class=subtitle>' + title + '</b>' : ''),
			icon: 'fas fa-trash',
			buttons: {
				confirm: {
					text: '<?=readLanguage(crud,yes)?>',
					btnClass: 'btn-red',
					action: () => this.crudSetOperation(id, 'delete', '<?=$token?>')
				},
				cancel: { text: '<?=readLanguage(crud,cancel)?>' }
			}
		});
	}

	/* ====== [Arrange Priorities] ====== */

	//Save crud order
	crudSaveOrderRows(){
		let new_priorities = [];
		let new_record_ids = [];
		this.container.find("[data-crud-table] tr:gt(0)").each(function(){
			new_priorities.push($(this).attr("priority"));
			new_record_ids.push($(this).attr("row-id"));
		});

		this.crudStartLoading();
		setTimeout(() => {
			$.ajax({
				method: 'POST',
				url: '<?=$crud_directory?>crud/_operations.php',
				data: {
					token: '<?=$token?>',
					action: 'save-order',
					crud_data: this.encryptedData,
					query: JSON.stringify(this.query_statement),
					new_record_ids: new_record_ids,
					original_priorities: this.original_priorities,
					new_priorities: new_priorities,
				},
				success: (response) => {
					this.crudLoadPage(this.current_page);
					quickNotify('<?=readLanguage(crud,arrange_success)?>', '<?=readLanguage(crud,save_arrangment)?>');
				}
			});
		});
	}

	//Reset table order when operation is done
	crudResetOrderRows(){
		let original_priorities = [];
		this.container.find('[data-crud-table] tr:gt(0)').each(function(){
			original_priorities.push($(this).attr('priority'));
		});

		this.original_priorities = original_priorities
	}

	/* ====== [Copy Column] ====== */

	//Copy crud column
	crudTriggerCopy(column){
		let cells = this.container.find('[data-crud-table] tbody > tr > td:nth-child(' + ($(column).parent().index() + 1) + ')');

		let column_text = [];
		cells.each(function(){
			if ($(this).text() && $(this).text() != '<?=readLanguage(general,na)?>'){
				column_text.push($.trim($(this).text()));
			}
		});

		column_text = column_text.join('\r\n');

		let dummy = document.createElement("textarea");
		document.body.appendChild(dummy);
		dummy.value = column_text;
		dummy.select();
		document.execCommand('copy');
		document.body.removeChild(dummy);

		quickNotify('<?=readLanguage(crud,copy_success)?>', $(column).parent().text());
	}


	/* ====== [Set CRUD Operations] ====== */

	//Set crud operations
	crudSetOperation(id, operation, token = null){
		if (!id || !operation){
			var header_parameters = reconstructHeaderParameters(['edit','delete','token','clone']);
		} else {
			if (operation == 'delete'){
				var header_parameters = reconstructHeaderParameters(['edit','delete','token','clone'], [operation + '=' + id, 'token=' + token]);
			} else {
				var header_parameters = reconstructHeaderParameters(['edit','delete','token','clone'], [operation + '=' + id]);
			}
		}

		if (this.new_window){
			window.open(window.location.href.split('?')[0] + header_parameters);
			this.new_window = false;
		} else {
			setWindowLocation(window.location.href.split('?')[0] + header_parameters);
		}
	}

	//Create multiple crud operations
	crudMultipleOperations(title, operation){
		let checked = [];
		this.container.find('[name=crud_row_check]').each(function(){
			if ($(this).prop('checked')){
				checked.push($(this).attr('check-row'));
			}
		});
		
		if (!checked.length){
			quickNotify('<?=readLanguage(crud,operations_selection)?>', title, 'danger', 'fas fa-times fa-3x');
		} else {
			window[operation](checked.join(","));
		}
	}

	/* ====== [Export Functions] ====== */

	//Get crud raw table
	crudGetRawTable(type, callback){
		let exportable = this.exportable;
		let checkboxes = '';
		exportable.forEach((element) => {
			checkboxes += '<label><input type=checkbox value="' + element + '">' + element + '</label>';
		});

		let parent = this;
		$.confirm({
			icon: 'fas fa-file-download',
			title: '<?=readLanguage(crud,export_records)?>',
			theme: 'light-noborder',
			content: "<div class='d-flex align-items-center'><span class='flex-grow-1'><?=readLanguage(crud,export_select)?></span><label class='flex-center'><input type=checkbox class=clear-margin onchange=\"$('.crud_export_checkboxes').find('input').prop('checked', $(this).prop('checked'))\">&nbsp;<?=readLanguage(crud,select_all)?></label></div><div class=crud_export_checkboxes>" + checkboxes + "</div>",
			buttons: {
				submit: {
					text: '<?=readLanguage(crud,export)?>',
					btnClass: 'btn-green',
					action: function(){
						let checked = [];
						this.$content.find('.crud_export_checkboxes input[type=checkbox]').each(function(){
							if ($(this).is(':checked')) checked.push($(this).val());
						});

						if (!checked.length){
							this.$content.find('.crud_export_checkboxes').css('border-color', 'rgb(185,74,72)');
						} else {
							this.showLoading(true);
							$.ajax({
								method: 'POST',
								url: '<?=$crud_directory?>crud/_operations.php',
								data: {
									token: '<?=$token?>',
									action: 'raw-table',
									crud_data: parent.encryptedData,
									query: JSON.stringify(parent.query_statement),
									type: type,
									crud_columns: checked.join(',')
								},
								success: (response) => {
									callback(response);
									this.close();
								}
							});
						}
						return false;
					}
				},
				cancel: { text: '<?=readLanguage(crud,cancel)?>' }
			}
		});
	}

	//Print table
	crudExportRaw(){
		this.crudGetRawTable('RAW', (response) => {
			let newForm = jQuery('<form>', {
				method: 'post',
				action: '<?=$crud_directory?>crud/_export_raw.php',
				target: '_blank'
			});

			newForm.append(jQuery('<input>', {
				name: 'token',
				value: '<?=$token?>',
				type: 'hidden'
			}));

			newForm.append(jQuery('<input>', {
				name: 'table',
				value: response,
				type: 'hidden'
			}));

			newForm.append(jQuery('<input>', {
				name: 'filename',
				value: this.fileName,
				type: 'hidden'
			}));

			$(document.body).append(newForm);
			newForm.submit();
		});
	}

	//Export excel
	crudExportExcel(){
		this.crudGetRawTable('EXCEL', (response) => {
			let newForm = jQuery('<form>', {
				method: 'post',
				action: '<?=$crud_directory?>crud/_export_excel.php',
				target: '_blank'
			});

			newForm.append(jQuery('<input>', {
				name: 'token',
				value: '<?=$token?>',
				type: 'hidden'
			}));

			newForm.append(jQuery('<input>', {
				name: 'table',
				value: response,
				type: 'hidden'
			}));

			newForm.append(jQuery('<input>', {
				name: 'filename',
				value: this.fileName,
				type: 'hidden'
			}));

			$(document.body).append(newForm);
			newForm.submit();
		});
	}

	//Export PDF
	crudExportPDF(){
		this.crudGetRawTable('PDF', (response) => {
			exportPDF('_pdf_html.php', this.fileName, '', `archives/${response}.txt`, true);
		});
	}

	/* ====== [General] ====== */

	//Start crud loading
	crudStartLoading(){
		this.container.css({
			'opacity': '0.5',
			'pointer-events': 'none'
		});
	}

	//End crud loading
	crudEndLoading(){
		this.container.css({
			'opacity': '1',
			'pointer-events': 'initial'
		});
	}

	//Set records per page
	crudRecordsPerPage(val){
		this.crud_records_per_page = +val;
		this.crudLoadPage(1);
	}
}
<? } ?>

//Creating new crud instance
const <?=$crud_id?> = new Crud({
	id: "<?=$crud_id?>",
	perPage: <?=$crud_data['data_rows']?>,
	encryptedData: "<?=$crud_data_encrypted?>",
	orderField: "<?=$crud_data['order_field']?>",
	exportable: JSON.parse('<?=json_encode($exportable, true)?>'),
	fileName: "<?=$crud_data['filename']?>",
	searchOptions: "<?=$search_options[$crud_id]?>"
});

//Load first page in table on start
<?=$crud_id?>.crudLoadPage(1);
$('#<?=$crud_id?> [data-records-per-page]').val(<?=$crud_data['data_rows']?>);
</script>