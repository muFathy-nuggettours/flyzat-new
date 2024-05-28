<div class=module_airports>
	<ul>
	<? $result = mysqlQuery("SELECT * FROM system_database_airports WHERE publish=1 ORDER BY popularity DESC, priority DESC LIMIT 0,20");
	while ($entry = mysqlFetch($result)){ ?>
		<li><a href="airports/<?=$entry[$suffix . "slug"]?>/"><?=$entry[$suffix . "name"]?></a></li>
	<? } ?>
	</ul>
</div>