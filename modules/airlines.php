<div class=module_airlines>
	<ul>
	<? $result = mysqlQuery("SELECT * FROM system_database_airlines WHERE publish=1 ORDER BY popularity DESC, priority DESC LIMIT 0,20");
	while ($entry = mysqlFetch($result)){ ?>
		<li><a href="airlines/<?=$entry[$suffix . "slug"]?>/"><?=$entry[$suffix . "name"]?></a></li>
	<? } ?>
	</ul>
</div>