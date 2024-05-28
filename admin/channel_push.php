<? include "system/_handler.php";

$multiple_languages = false;
$base_name = basename($_SERVER["SCRIPT_FILENAME"], ".php");
checkPermissions($base_name);

//==== SEND ====
if ($post["token"]){
	//Subscribers
	if ($post["subscribers"]){
		$subscribers = mysqlFetchAll(mysqlQuery("SELECT token FROM users_push_notifications WHERE user_id IN (" . $post["subscribers"] . ")"), "token");
	} else {
		$subscribers = mysqlFetchAll(mysqlQuery("SELECT token FROM users_push_notifications"), "token");
	}
	
	//Image
	$image = imgUpload($_FILES["image"], "../uploads/_website/", null, "push_");
	$image_url = ($image ? $base_url . "uploads/_website/" . $image : null);

	$result =  sendNotification($subscribers, $post["title"], $post["message"], null, $post["url"], $image_url);
	$error = $result[0];
	$success = $result[1];
}

//Read and Set Operation
if ($success){ $message = "<div class='alert alert-success'>$success</div>"; }
if ($error){ $message .= "<div class='alert alert-danger'>$error</div>"; }

include "_header.php"; ?>

<div class=title><?=getPageTitle($base_name)?></div>
<?=$message?>

<form method=post enctype="multipart/form-data" action="<?=$action?>">
<input type=hidden name=token value="<?=$token?>">

<table class=data_table>
<tr>
	<td class=title><?=readLanguage(channels,subscribers)?>:</td>
	<td>
		<select name=subscribers id=subscribers multiple></select>
		<script>
		$("#subscribers").select2({
			width: "100%",
			placeholder: "<?=readLanguage(users,search)?>",
			minimumInputLength: 3,
			escapeMarkup: function(markup){ return markup },
			templateResult: function(data) { return data.html },
			templateSelection: function(data) { return data.text },
			ajax: {
				url: "__requests.php",
				type: "POST",
				dataType: "json",
				delay: 50,
				processResults: function (data){
					return { results: data.results }
				},
				data: function (params){
					return { token:"<?=$token?>", action:"search_users", search:params.term, conditions:"<?=$conditions?>" };
				}
			}
		});
		</script>
		<div class=input_description><?=readLanguage(channels,push_empty)?></div>
	</td>
</tr>
<tr>
	<td class=title><?=readLanguage(inputs,title)?>: <i class=requ></i></td>
	<td><input type=text name=title data-validation=required></td>
</tr>
<tr>
	<td class=title><?=readLanguage(channels,message)?>: <i class=requ></i></td>
	<td><textarea name=message data-validation=required></textarea></td>
</tr>
<tr>
	<td class=title><?=readLanguage(inputs,url)?>:</td>
	<td class=ltr-input>
		<div class="input-addon input-addon-ltr">
			<span before><?=$base_url?></span>
			<select name=url id=url><?=$data_menu_items?></select>
			<script>$("#url").select2({ tags: true })</script>
		</div>
	</td>
</tr>
<tr>
	<td class=title><?=readLanguage(inputs,image)?>:</td>
	<td colspan=3>
		<table class=attachment><tr>
		<td>
			<input type=file name=image id=image accept="image/*" allowed-mimes="image/bmp,image/jpeg,image/png,image/gif">
		</td>
		<td width=150>
			<? $path = ($entry["image"] ? "../uploads/_website/" . $entry["image"] : "images/placeholder.png") ?>
			<a data-fancybox=images href="<?=$path?>"><img class=sample_img image-placeholder=image src="<?=$path?>"></a>
		</td>
		</tr></table>
		<!-- Used Only For Upload -->
		<script>$(document).ready(function(){ bindImage("image") })</script>
	</td>
</tr>
</table>

<div class=submit_container><input type=button class=submit value="<?=readLanguage(channels,send)?>"></div>
</form>

<? include "_footer.php"; ?>