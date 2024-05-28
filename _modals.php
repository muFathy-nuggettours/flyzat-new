<!-- Login Modal -->
<? if (!$logged_user){ ?>
<div class="modal fade" id=loginModal><div class=modal-dialog style="max-width:400px"><div class=modal-content>
	<div class=modal-header>
		<button type=button class=close data-dismiss=modal><span>&times;</span></button>
		<h4 class=modal-title><?=readLanguage(accounts,login)?></h4>
	</div>
	<div class=modal-body>
		<form method=post action="login/">
			<input type=hidden name=token value="<?=$token?>">
			<input type=hidden name=action value="login">
			<div class=fancy_form>
				<b><?=readLanguage(accounts,email)?> *</b>
				<div>
					<label><i class="fas fa-envelope"></i></label>
					<input type=email name=credentials placeholder="<?=readLanguage(accounts,email_placeholder)?>" data-validation=required autocomplete=email>
				</div>

				<b><?=readLanguage(accounts,password)?> *</b>
				<div>
					<label><i class="fas fa-lock"></i></label>
					<input type=password autocomplete=new-password name=password placeholder="<?=readLanguage(accounts,password_placeholder)?>" data-validation=required>
				</div>			
			</div>
			
			<!-- Remember Credential & Reset Password -->
			<div class=login_check>
				<div class=check_container><label><input type=checkbox class=filled-in name=remember value=remember checked><span><?=readLanguage(accounts,remember)?></span></label></div>
				<small><?=readLanguage(accounts,forgot_password)?> <a href="reset-password/"><?=readLanguage(accounts,reset_password)?></a></small>
			</div>

			<!-- Submit -->
			<div class=submit_container_blank>
				<button type=button class="submit margin-bottom"><?=readLanguage(accounts,login)?></button>
				<?=readLanguage(accounts,no_account)?> <a href="signup/"><?=readLanguage(accounts,signup)?></a>
			</div>
		</form>
		
		<!-- Social Media -->
		<? include "_inl_social_login.php"; ?>
	</div>
</div></div></div>
<? } ?>

<!-- Pop-Up Modal -->
<? $popup = mysqlFetch(mysqlQuery("SELECT * FROM " . $suffix . "website_popup WHERE status=1"));
if ($popup && (!$_COOKIE[$popup_cookie] || ($_COOKIE[$popup_cookie] && $_COOKIE[$popup_cookie] != $popup["hash"]))){ ?>
<div class="modal <?=($popup["center"] ? "modal-center" : "modal-initial")?> fade" id=popupModal>
	<div class=modal-dialog>
		<div class=modal-content>
			<div class=modal-body style="padding:<?=$popup["padding"]?>px">
				<button type=button class=close data-dismiss=modal><span>&times;</span></button>
				<?=htmlContent($popup["content"])?>
			</div>
		</div>
	</div>
</div>
<script>
$(document).ready(function(){
	$("#popupModal").modal("show");
});

$("#popupModal").on("hidden.bs.modal",function(){
	triggerHidePopup();
});

function triggerHidePopup(){
	writeCookie("<?=$popup_cookie?>", "<?=$popup["hash"]?>", <?=$popup["appearance"]?>); //Re-Appear After X Hours
}
</script>
<? } ?>