<? if ($post["token"] && $post["code"] && $post["address"]){
	if ($post["code"]==$logged_user["mobile_verification"]){
		mysqlQuery("UPDATE users_database SET mobile_verification='', address='" . $post["address"] . "' WHERE id=" . $logged_user["id"]);
		$success = true;
	} else {
		print "<div class='alert alert-danger'>كود التحقق الذي ادخلته غير صحيح، برجاء إعادة المحاولة</div>";
	}
} ?>

<!-- Success form -->
<? if ($success){ ?>
<div class=page_container>
	<div class=message>
		<b>جاري تنفيذ طلبك</b>
		<small>جاري تنفيذ طلبك، برجاء الإنتظار..</small>
	</div>
</div>
<script>
setTimeout(function(){
	setWindowLocation("<?=$payment_redirect?>");
}, 1000);
</script>

<!-- Verification form -->
<? } else { ?>
<div class=page_container><?=htmlContent($system_settings["payment_cash_message"])?></div>

<textarea class=margin-top placeholder="قم بإدخال عنوانك بالكامل" maxlength=1000 id=verification_address style="height: 100px"><?=$logged_user["address"]?></textarea>

<div class="align-center margin-top-20">
	سوف يتم إرسال كود للتحقق علي رقم جوالك المسجل لدينا
	<div class="force-ltr align-center" style="font-weight: bold; font-size: 22px; line-height: 2;"><?=$logged_user["mobile"]?></div>
	<button class="btn btn-primary margin-top-5" onclick="requestVerification()">إرسال كود التحقق</button>
</div>

<script>
function requestVerification(){
	var address = $("#verification_address").val();
	if (!address){
		$("#verification_address").css("border","1px solid rgb(185, 74, 72)");
		quickNotify("برجاء إدخال عنوانك بالكامل", "خطأ", "danger", "fal fa-times fa-2x");
	} else {
		$.confirm({
			title: 'كود التحقق',
			content: function(){
				var self = this;
				return $.ajax({
					type: "POST",
					url: "requests/",
					data: {
						token: "<?=$token?>",
						action: "mobile_request_verification"
					}
				}).done(function(response){
					self.setContent("قم بإدخال كود التحقق المكون من 6 ارقام الذي تم إرساله" + "<input id=verification_code type=number class='margin-top align-center' style='font-size: 20px;'>");
				}).fail(function(response){
					self.close();
					messageBox("خطأ", "تعثر إرسال كود التحقق، برجاء إعادة المحاولة بعد قليل", "fal fa-times", "red");
				})
			},
			icon: 'fal fa-sms',
			theme: 'light-noborder',
			buttons: {
				submit: {
					text: 'إرسال الطلب',
					btnClass: 'btn-blue',
					action: function (){
						var address = $("#verification_address").val();
						var code = this.$content.find("#verification_code").val();
						if (!code){
							this.$content.find('#verification_code').css("border","1px solid rgb(185, 74, 72)");
							return false;
						} else {
							postForm({
								code: code,
								address: address
							});
						}
					}
				},
				cancel: { text: 'إلغاء' }
			}
		});
	}
}
</script>
<? } ?>