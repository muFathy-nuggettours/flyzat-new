<div class="row grid-container">
<?
foreach ($user_pages AS $key=>$value){
	if ($key != "dashboard"){
		print "<div class='col-md-4 col-sm-5 col-xs-10 grid-item'><a class=dashboard href='user/$key/'>
			<div class=dashboard_icon><span class='" . $value[2] . "'></span><b>" . $value[0] . "</b></div>
		</a></div>";
	}
}
?>
</div>