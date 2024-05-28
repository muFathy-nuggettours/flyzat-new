<? $result = mysqlQuery("SELECT * FROM users_balance WHERE user_id=" . $logged_user["id"] . " ORDER BY date DESC");
if (!mysqlNum($result)){ ?>
<div class=page_container>
	<div class=message>
		<i class="fas fa-money-check-alt"></i>
		<b><?=readLanguage(operations,no_registered_ops)?></b>
		<small><?=readLanguage(operations,no_reg_ops_small)?></small>
	</div>
</div>

<? } else { ?>
	<table class="fancy square">
		<thead>
			<th>#</th>
			<th><?=readLanguage(common,record)?></th>
			<th><?=readLanguage(common,amount)?></th>
			<th><?=readLanguage(common,date)?></th>
		</thead>
		<? while ($entry = mysqlFetch($result)){ $serial++; ?>
		<tr>
			<td class=center-large><?=$serial?></td>
			<td><?=$entry["title"]?></td>
			<td class=center-large><b><?=number_format($entry["amount"], 2)?></b></td>
			<td class=center-large><?=dateLanguage("l, d M Y", $entry["date"])?></td>
		</tr>
		<? } ?>
	</table>
<? } ?>