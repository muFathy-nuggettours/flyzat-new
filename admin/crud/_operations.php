<? include "../system/_handler.php";

//Security Measure: Allow for post requests and logged users only
if (!$post["token"]){ brokenLink(); }

//Retrieve CRUD function parameters
function retrieveCurdFunction($function, $string){
    preg_match("/$function\((.*)\)(?:\[(.*)])?/", $string, $matches);
    $params = preg_replace(['/( ?)+\,( ?)+/', '/("?)(\'?)/'], [',', ''], $matches);
    return [
        'parameters' => explode(',', $params[1]),
        'value' => $params[2]
    ];
}

//Decrypt CRUD Data
$crud_data = json_decode(decryptText($post["crud_data"]), true);

//Target Table
$select = $crud_data["select"];
$table = $crud_data["table"];
$join = $crud_data["join"];

//Parse query object
$query_object = json_decode($post["query"], true);

//===== WHERE statement =====

$where = array();

//Original where statement
if ($crud_data["where_statement"]){
	array_push($where, $crud_data["where_statement"]);
}

//Search statement
if ($query_object["search"]){
	$search = array();
	foreach ($query_object["search"] AS $column_index=>$value){
		$column_parameters = $crud_data["columns"][$column_index];
		$column = $column_parameters[0];
		$function = $column_parameters[4];
		
		if ($function){
			//getID
			if (strpos($function, "getID") !== false){
				$function_parameters = retrieveCurdFunction("getID", $function);
				$target_table = $function_parameters["parameters"][1];
				$target_column = ($function_parameters["parameters"][2] ? $function_parameters["parameters"][2] : $function_parameters["value"]);
				array_push($search, "$column IN (SELECT id FROM $target_table WHERE $target_column LIKE '%$value%')");
			
			//getData
			} else if (strpos($function, "getData") !== false){
				$function_parameters = retrieveCurdFunction("getData", $function);
				$target_table = $function_parameters["parameters"][0];
				$target_column = ($function_parameters["parameters"][3] ? $function_parameters["parameters"][3] : $function_parameters["value"]);
				$target_field = $function_parameters["parameters"][1];
				array_push($search, "$column IN (SELECT $target_field FROM $target_table WHERE $target_column LIKE '%$value%')");
			
			//getCustomData
			} else if (strpos($function, "getCustomData") !== false){
				$function_parameters = retrieveCurdFunction("getCustomData", $function);
				$target_table = $function_parameters["parameters"][1];
				$target_column = $function_parameters["parameters"][0];
				$target_field = $function_parameters["parameters"][2];
				array_push($search, "$column IN (SELECT $target_field FROM $target_table WHERE $target_column LIKE '%$value%')");				
				
			} else {
				array_push($search, "$column LIKE '%$value%'");
			}
		} else {
			array_push($search, "$column LIKE '%$value%'");
		}

	}
	array_push($where, "(" . implode(" AND ", $search) . ")");
}

//Calendar statement
if ($query_object["calendar"]){
	$calendar = array();
	foreach ($query_object["calendar"] AS $column_index=>$values){
		$column_parameters = $crud_data["columns"][$column_index];
		if ($column_parameters && is_numeric($values[0]) && is_numeric($values[1])){
			$column = $column_parameters[0];
			array_push($calendar, "$column BETWEEN {$values[0]} AND {$values[1]}");
		}
	}
	array_push($where, "(" . implode(" AND ", $calendar) . ")");
}

//Filter statement
if ($query_object["filter"]){
	$filter = array();
	foreach ($query_object["filter"] AS $column_index=>$value){
		$column_parameters = $crud_data["columns"][$column_index];
		if ($column_parameters){
			$column = $column_parameters[0];
			array_push($filter, "CONCAT(',', $column, ',') REGEXP ',(" . implode("|", $value) . "),'");
		}
	}
	array_push($where, "(" . implode(" AND ", $filter) . ")");
}

$where = ($where ? "WHERE " . implode(" AND ", $where) : "");

//===== ORDER statement =====

if ($query_object["order"]){
	$order = array();
	foreach ($query_object["order"] AS $column_index=>$direction){
		$column_parameters = $crud_data["columns"][$column_index];
		//Make sure column index is valid
		if ($column_parameters){
			$column = $column_parameters[0];
			array_push($order, "$column $direction");
		}
	}
	$order = "ORDER BY " . implode(", ", $order);
} else if ($crud_data["order_by"]){
	$order = "ORDER BY " . $crud_data["order_by"];
}

//===== LIMIT statement =====

if ($query_object["limit"]){
	$limit = "LIMIT " . $query_object["limit"][0] . "," . $query_object["limit"][1];
}

//===== Operations =====

//Count Number of Rows
if ($post["action"]=="count-rows"){
	echo mysqlNum(mysqlQuery("SELECT id FROM $table $where"));	
}

//Filteration
if ($post["action"]=="filter"){
	$post["field"] = ($post["field"] ? $post["field"] : 0);
	$target_column = ($crud_data["join"] ? str_replace(".", "_", $post["field"]) : $post["field"]);
	$column_parameters = $crud_data["columns"][$target_column];
	$column = $column_parameters[0];
	$column_replaced = ($crud_data["join"] ? str_replace(".", "_", $column) : $column);
	$function = $column_parameters[4];
	$implode_check = array();
	$filter_values = array();
	$crud_result = mysqlQuery("SELECT $select FROM $table $join $where GROUP BY $column");
	while ($crud_entry = mysqlFetch($crud_result)){
		//Has Function
		if ($function){
			//implodeDatabase
			if (strpos($function, "implodeDatabase") !== false){
				$explode = explode(",", $crud_entry[$column_replaced]);
				foreach ($explode AS $key=>$value){
					if (!in_array($value, $implode_check)){
						$eval = str_replace("%s", $value, $function);
						$string = eval("return " . $eval . ";");
						$string = trim(str_replace("&nbsp;", "", $string));
						array_push($filter_values, array($value, $string));
						array_push($implode_check, $value);
					}
				}
			
			//implodeVariable
			} else if (strpos($function, "implodeVariable") !== false){
				$explode = explode(",", $crud_entry[$column_replaced]);
				foreach ($explode AS $key=>$value){
					if (!in_array($value, $implode_check)){
						$function_parameters = retrieveCurdFunction("implodeVariable", $function);
						$variable = $function_parameters["parameters"][1];
						$string = ($$variable[$value] ? $$variable[$value] : $value);
						$string = trim(str_replace("&nbsp;", "", $string));
						array_push($filter_values, array($value, $string));
						array_push($implode_check, $value);
					}
				}
				
			} else {
				$eval = str_replace("%s", $crud_entry[$column_replaced], $function);
				$eval = str_replace("%d", '$crud_entry', $eval);
				$string = eval("return " . $eval . ";");
				$string = trim(str_replace("&nbsp;", "", $string));
				array_push($filter_values, array($crud_entry[$column_replaced], $string));
			}
			
		//Generic Text	
		} else {
			$string = $crud_entry[$column_replaced];
			$string = trim(str_replace("&nbsp;", "", $string));
			array_push($filter_values, array($crud_entry[$column_replaced], $string));
		}
	}
	echo json_encode($filter_values);
}

//Save Order
if ($post["action"]=="save-order"){
	$new_record_ids = $post["new_record_ids"];
	$original_priorities = $post["original_priorities"];
	$new_priorities = $post["new_priorities"];
	foreach ($new_record_ids AS $key => $value){
		if ($new_priorities[array_search($value,$new_record_ids)]!=$original_priorities[$key]){
			mysqlQuery("UPDATE $table SET {$crud_data["order_field"]}='{$original_priorities[$key]}' WHERE id='$value'");
		}
	}
}

//Get Raw Table
if ($post["action"]=="raw-table"){
	//Execute Query
	$crud_result = mysqlQuery("SELECT $select FROM $table $join $where $order");

	//Build Table
	$print_data = "<table class=manage style='overflow:wrap'>";
	$print_data .= "<thead><tr>";
	$included = explode(",",$post["crud_columns"]);
	foreach ($crud_data["columns"] AS $key => $value){
		$class = ($post["type"]=="RAW" ? "width:" . $value[2] . "; min-width:" . $value[2] : "max-width:" . $value[2]);
		if (in_array($value[1],$included)){ $print_data .= "<th style='$class'>" . $value[1] . "</th>"; }
	}
	$print_data .= "</tr></thead>";

	if (!mysqlNum($crud_result)){
		$print_data .= "<tr><td colspan='" . count($crud_data["columns"]) . "'>" . readLanguage(general,na) . "</td></tr>";
	} else {
		while ($crud_entry = mysqlFetch($crud_result)){
			$print_data .= "<tr>";
			foreach ($crud_data["columns"] AS $key => $value){
				$target_column = ($crud_data["join"] ? str_replace(".", "_", $value[0]) : $value[0]);
				if (in_array($value[1],$included)){ //Not Excluded
					$print_data .= "<td>";
					//Has Function
					if ($value[4]){
						$eval = str_replace("%s", $crud_entry[$target_column], $value[4]);
						$eval = str_replace("%d", '$crud_entry', $eval);
						$print_data .= eval("return " . $eval . ";");
					
					//Generic Text
					} else {
						$print_data .= ($crud_entry[$target_column] ? $crud_entry[$target_column] : "");
					}
					$print_data .= "</td>";
				}
			}
			$print_data .= "</tr>";
		}
	}
	$print_data .= "</table>";

	//Clean Table Data
	$print_data = strip_tags($print_data,"<table><thead><tr><td><th><br><img><b><i><u><small>");
	$print_data = str_replace("../", ($post["type"]=="RAW" ? "../../" : "../"), $print_data);
	
	//Save Export File
	$filename = uniqid();
	file_put_contents("../archives/" . $filename . ".txt", $print_data);
	echo $filename;
}

//Main Operation
if ($post["action"]=="operation"){
	$crud_result = mysqlQuery("SELECT $select FROM $table $join $where $order $limit");
	while ($crud_entry = mysqlFetch($crud_result)){
		$count++;
		$record_id = $crud_entry[($crud_data["join"] ? $table . "_id" : "id")];
		$record_priority = $crud_entry[$crud_data["order_field"]];
		echo "<tr row-id='$record_id' priority='$record_priority'>";
		if ($crud_data["order_field"]){ echo "<td class='drag-cell fixed-left'><i class='fas fa-bars'></i></td>"; }
		if ($crud_data["multiple_operations"]){ echo "<td class='check-cell fixed-left'><input type=checkbox name=crud_row_check check-row='$record_id'></td>"; }
		foreach ($crud_data["columns"] AS $key => $value){
			$target_column = ($crud_data["join"] ? str_replace(".", "_", $value[0]) : $value[0]);
			$border_right = (($crud_data["columns"][$key + 1][3]=="fixed-right") || ($value[3]!="fixed-right" && $key==count($crud_data["columns"]) - 1) ? "border-right:0" : "");
			echo "<td class='" . $value[3] . "' style='" . $border_right . "'>";
			//Has Function
			if ($value[4]){
				$eval = str_replace("%s", $crud_entry[$target_column], $value[4]);
				$eval = str_replace("%d", '$crud_entry', $eval);
				$eval = str_replace("%c", $count, $eval);
				eval("\$function_value = " . $eval . ";");
				echo ($function_value ? $function_value : "<i class=na>" . readLanguage(general,na) . "</i>");
			
			//Generic Text
			} else {
				echo ($crud_entry[$target_column] ? $crud_entry[$target_column] : "<i class=na>" . readLanguage(general,na) . "</i>");
			}
			echo "</td>";
			if ($crud_data["delete_record_message"] == $target_column){
				$delete_record_title = $crud_entry[$target_column];
			}
		}
		$edit_button = ($crud_data["buttons"][3] ? "<a onclick=\"$post[id].crudSetOperation($record_id,'edit')\" class=crud_button_sm><i class='fas fa-edit' style='color:#f27f2a'></i></a>" : "");
		$delete_button = ($crud_data["buttons"][4] ? "<a onclick=\"$post[id].crudTriggerDelete($record_id,'" . htmlentities($delete_record_title) . "')\" class=crud_button_sm><i class='fas fa-trash' style='color:#b62400'></i></a>" : "");
		echo "<td class='fixed-right buttons'>" . $edit_button . $delete_button . "</td>";
		echo "</tr>";
	}
}
?>