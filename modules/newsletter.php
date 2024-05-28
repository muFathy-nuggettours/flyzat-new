<form class=module_newsletter>
	<input type=email data-validation=email placeholder="<?=readLanguage(newsletter,placeholder)?>">
	<input type=button value="<?=readLanguage(newsletter,subscribe)?>" class="btn btn-primary btn-sm" onclick="newsletterSubscribe(this)">			
</form>

<script>
if (typeof newsletterSubscribe === "undefined"){
	function newsletterSubscribe(target){
		var form = $(target).parent();
		var email = form.find("input[type=email]");
		if (!form.isValid(null, null, false)){
			email.attr("style", "border-color:rgb(185, 74, 72)");
		} else {
			email.removeAttr("style");
			$.ajax({
				method: "POST",
				url: "requests/",
				data: {
					token: user_token,
					action: "newsletter",
					email: email.val()
				},
			}).done(function(response){
				email.val("");
				messageBox("<?=readLanguage(footer,newsletter)?>", response, "fas fa-check", "green");
			}).fail(function(response){
				messageBox("<?=readLanguage(footer,newsletter)?>", response.responseText, "fas fa-times", "red");
			});					   
		}
	}
}
</script>