<link href="modules/statistics.css?v=<?=$system_settings["system_version"]?>" rel="stylesheet">

<?
$block_data[1] = mysqlNum(mysqlQuery("SELECT id FROM system_database_countries"));
$block_data[2] = mysqlNum(mysqlQuery("SELECT id FROM system_database_regions"));
$block_data[3] = mysqlNum(mysqlQuery("SELECT id FROM system_database_airports WHERE active=1"));
$block_data[4] = mysqlNum(mysqlQuery("SELECT id FROM system_database_airlines WHERE active=1"));
$block_data[5] = mysqlNum(mysqlQuery("SELECT id FROM system_database_planes"));
$block_data[6] = mysqlNum(mysqlQuery("SELECT id FROM users_database"));
?>

<div class=statistics_module><div class="row grid-container">
	<div class="col-md-six col-sm-5 col-xs-10 grid-item">
		<div class=circle-tile>
		<div class="circle-tile-heading purple"><i class="fas fa-globe-americas fa-3x"></i></div>
		<div class=circle-tile-content>
			<div class=circle-tile-description>الدول</div>
			<div class=circle-tile-number><?=number_format($block_data[1])?></div>
			<a href="database_countries.php" class=circle-tile-footer>عرض التفاصيل</a>
		</div>
		</div>
	</div>
	
	<div class="col-md-six col-sm-5 col-xs-10 grid-item">
		<div class=circle-tile>
		<div class="circle-tile-heading dark-blue"><i class="fas fa-globe fa-3x"></i></div>
		<div class=circle-tile-content>
			<div class=circle-tile-description>المدن</div>
			<div class=circle-tile-number><?=number_format($block_data[2])?></div>
			<a href="database_regions.php" class=circle-tile-footer>عرض التفاصيل</a>
		</div>
		</div>
	</div>
	
	<div class="col-md-six col-sm-5 col-xs-10 grid-item">
		<div class=circle-tile>
		<div class="circle-tile-heading blue"><i class="fas fa-suitcase-rolling fa-3x"></i></div>
		<div class=circle-tile-content>
			<div class=circle-tile-description>المطارات</div>
			<div class=circle-tile-number><?=number_format($block_data[3])?></div>
			<a href="database_airports.php" class=circle-tile-footer>عرض التفاصيل</a>
		</div>
		</div>
	</div>
	
	<div class="col-md-six col-sm-5 col-xs-10 grid-item">
		<div class=circle-tile>
		<a><div class="circle-tile-heading green"><i class="fas fa-passport fa-3x"></i></div></a>
		<div class=circle-tile-content>
			<div class=circle-tile-description>خطوط الطيران</div>
			<div class=circle-tile-number><?=number_format($block_data[4])?></div>
			<a href="database_airlines.php" class=circle-tile-footer>عرض التفاصيل</a>
		</div>
		</div>
	</div>
	
	<div class="col-md-six col-sm-5 col-xs-10 grid-item">
		<div class=circle-tile>
		<div class="circle-tile-heading orange"><i class="fas fa-plane fa-3x"></i></div>
		<div class=circle-tile-content>
			<div class=circle-tile-description>الطائرات</div>
			<div class=circle-tile-number><?=number_format($block_data[5])?></div>
			<a href="database_planes.php" class=circle-tile-footer>عرض التفاصيل</a>
		</div>
		</div>
	</div>
	
	<div class="col-md-six col-sm-5 col-xs-10 grid-item">
		<div class=circle-tile>
		<div class="circle-tile-heading red"><i class="fas fa-users fa-3x"></i></div>
		<div class=circle-tile-content>
			<div class=circle-tile-description>المستخدمين</div>
			<div class=circle-tile-number><?=number_format($block_data[6])?></div>
			<a href="users_database.php" class=circle-tile-footer>عرض التفاصيل</a>
		</div>
		</div>
	</div>
</div></div>