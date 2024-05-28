<? if ($post["token"] && $post["rating_reservation"]){
	$reservation = getID($post["rating_reservation"], "flights_reservations");
	$reservation_user = $reservation["user_id"];
	if ($reservation_user==$logged_user["id"]){
		$exists = mysqlFetch(mysqlQuery("SELECT id FROM flights_ratings WHERE reservation_id=" . $post["rating_reservation"]))["id"];
		if (!$exists){
			$search_object = json_decode($reservation["search_object"], true);
			$flight = $search_object["trips"][0]["flights"][0]["trip"];
			$airport = $search_object["trips"][0]["flights"][0]["from"]["iata"];
			$airline = $search_object["trips"][0]["flights"][0]["airline"]["iata"];
			$query = "INSERT INTO flights_ratings (
				reservation_id,
				flight,
				rating_flight,
				comment_flight,
				airport,
				rating_airport,
				comment_airport,
				airline,
				rating_airline,
				comment_airline,
				date
			) VALUES (
				" . $post["rating_reservation"] . ",
				'$flight',
				" . intval($post['rating_flight']) . ",
				'{$post['comment_flight']}',
				'$airport',
				" . intval($post['rating_airport']) . ",
				'{$post['comment_airport']}',
				'$airline',
				" . intval($post['rating_airline']) . ",
				'{$post['comment_airline']}',
				" . time() . "
			)";
			mysqlQuery($query);
		} else {
			$query = "UPDATE flights_ratings SET
				rating_flight=" . intval($post['rating_flight']) . ",
				comment_flight='{$post['comment_flight']}',
				rating_airport=" . intval($post['rating_airport']) . ",
				comment_airport='{$post['comment_airport']}',
				rating_airline=" . intval($post['rating_airline']) . ",
				comment_airline='{$post['comment_airline']}'
			WHERE id=$exists";
			mysqlQuery($query);
		}
		$message = "<div class='alert alert-success'>".readLanguage(reservation,save_rate_success)."</div>";
	}
} ?>

<?=$message?>

<? $result = mysqlQuery("SELECT * FROM flights_reservations WHERE user_id=" . $logged_user["id"] . " ORDER BY date DESC");
if (!mysqlNum($result)){ ?>
<div class=page_container>
	<div class=message>
		<i class="fas fa-plane"></i>
		<b><?=readLanguage(reservation,no_reserves_added)?></b>
		<small><?=readLanguage(reservation,no_reserves_small)?>!</small>
	</div>
</div>

<? } else { ?>
	<table class="fancy square">
		<thead>
			<th>#</th>
			<th><?=readLanguage(pages,code)?></th>
			<th><?=readLanguage(common,passengers)?></th>
			<th><?=readLanguage(reservation,reserve_date)?></th>
			<th><?=readLanguage(reservation,trip_date)?></th>
			<th width=100><?=readLanguage(common,status)?></th>
			<th width=100><?=readLanguage(common,details)?></th>
			<th width=100><?=readLanguage(common,manage)?></th>

		</thead>
		<? while ($entry = mysqlFetch($result)){ $serial++;
			$search_object = json_decode($entry["search_object"], true);
			$rating = getData("flights_ratings", "reservation_id", $entry["id"]); ?>
		<tr reservation-id="<?=$entry["id"]?>" data-rating="<?=($rating ? base64_encode(json_encode($rating, JSON_UNESCAPED_UNICODE)) : "")?>" data-flight="<?=$search_object["trips"][0]["flights"][0]["trip"]?>" data-airport="<?=$search_object["trips"][0]["flights"][0]["from"]["iata"]?>" data-airline="<?=$search_object["trips"][0]["flights"][0]["airline"]["iata"]?>">
			<td class=center-large><?=$serial?></td>
			<td class=center-large><?=$entry["code"]?></td>
			<td class=center-large><?=count(explode(",", $entry["passengers"]))?></td>
			<td class=center-large><?=dateLanguage("l, d M Y", $entry["date"])?></td>
			<td class=center-large><?=dateLanguage("l, d M Y", $entry["so_start"])?></td>
			<td class=center-large><?=returnStatusLabel("data_reservation_status", $entry["status"])?></td>
			<td class=center-large><a class="btn btn-primary btn-sm btn-block" href="reservation/<?=$entry["code"]?>/" data-fancybox data-type=iframe data-frame-width=1000><?=readLanguage(common,details)?></a></td>
			<td class=center-large>
				<? if ($entry["so_end"] < time()){ ?>
				<a class="btn btn-default btn-sm btn-block" onclick="showRatingModal(<?=$entry["id"]?>)"><?=readLanguage(reservation,flight_rating)?></a>
				<? } else if ($entry["status"]!=0){ ?>
				<a class="btn btn-danger btn-sm btn-block" href="contact/"><?=readLanguage(reservation,cancel_request)?></a>
				<? } else { ?>
				<a class="btn btn-success btn-sm btn-block" href="contact/"><?=readLanguage(reservation,inquiry)?></a>
				<? } ?>
			</td>
		</tr>
		<? } ?>
	</table>
	
	<!-- Rating Modal -->
	<div class="modal fade" id=ratingModal><div class=modal-dialog style="max-width:400px"><div class=modal-content>
		<div class=modal-body>
			<form method=post>
				<input type=hidden name=token value="<?=$token?>">
				<input type=hidden name=rating_reservation>
				<div class=subtitle><?=readLanguage(reservation,rating_airline)?> &nbsp; <b rating_airline></b></div>
				<div class="d-flex rating_container" id=rating_airline>
					<label><input type=radio name=rating_airline class=d-none value=1><i class="fa fa-angry fa-2x"></i></label>
					<label><input type=radio name=rating_airline class=d-none value=2><i class="fa fa-frown fa-2x"></i></label>
					<label><input type=radio name=rating_airline class=d-none value=3><i class="fa fa-meh fa-2x"></i></label>
					<label><input type=radio name=rating_airline class=d-none value=4><i class="fa fa-grin fa-2x"></i></label>
					<label><input type=radio name=rating_airline class=d-none value=5 checked><i class="fa fa-grin-stars fa-2x"></i></label>
				</div>
				<script>$("#rating_airline").find("[value='<?=$entry["rating_airline"][0]?>']").prop("checked",true);</script>
				<input type=text name=comment_airline placeholder="<?=readLanguage(common,add_comment)?>">

				<div class=subtitle><?=readLanguage(reservation,flight_rating)?> &nbsp; <b rating-flight></b></div>
				<div class="d-flex rating_container" id=rating_flight>
					<label><input type=radio name=rating_flight class=d-none value=1><i class="fa fa-angry fa-2x"></i></label>
					<label><input type=radio name=rating_flight class=d-none value=2><i class="fa fa-frown fa-2x"></i></label>
					<label><input type=radio name=rating_flight class=d-none value=3><i class="fa fa-meh fa-2x"></i></label>
					<label><input type=radio name=rating_flight class=d-none value=4><i class="fa fa-grin fa-2x"></i></label>
					<label><input type=radio name=rating_flight class=d-none value=5 checked><i class="fa fa-grin-stars fa-2x"></i></label>
				</div>
				<script>$("#rating_flight").find("[value='<?=$entry["rating_flight"][0]?>']").prop("checked",true);</script>
				<input type=text name=comment_flight placeholder="<?=readLanguage(common,add_comment)?>">

				<div class=subtitle><?=readLanguage(reservation,rating_take_off_airport)?>&nbsp; <b rating-airport></b></div>
				<div class="d-flex rating_container" id=rating_airport>
					<label><input type=radio name=rating_airport class=d-none value=1><i class="fa fa-angry fa-2x"></i></label>
					<label><input type=radio name=rating_airport class=d-none value=2><i class="fa fa-frown fa-2x"></i></label>
					<label><input type=radio name=rating_airport class=d-none value=3><i class="fa fa-meh fa-2x"></i></label>
					<label><input type=radio name=rating_airport class=d-none value=4><i class="fa fa-grin fa-2x"></i></label>
					<label><input type=radio name=rating_airport class=d-none value=5 checked><i class="fa fa-grin-stars fa-2x"></i></label>
				</div>
				<script>$("#rating_airport").find("[value='<?=$entry["rating_airport"][0]?>']").prop("checked",true);</script>
				<input type=text name=comment_airport placeholder="<?=readLanguage(common,add_comment)?>">
				
				<input type=button class="submit margin-top" value="<?=readLanguage(reservation,save_rate)?>">
			</form>
		</div>
	</div></div></div>
	
	<script>
	function showRatingModal(id){
		var modal = $("#ratingModal");
		var row = $("tr[reservation-id=" + id + "]");
		
		modal.find("[rating_airline]").text(row.attr("data-airline"));
		modal.find("[rating-flight]").text(row.attr("data-airline") + "-" + row.attr("data-flight"));
		modal.find("[rating-airport]").text(row.attr("data-airport"));
		modal.find("[name=rating_reservation]").val(id);

		if (row.attr("data-rating")){
			var data = JSON.parse(atob(row.attr("data-rating")));
			$("#rating_airline").find("[value='" + data.rating_airline + "']").prop("checked",true);
			$("#rating_flight").find("[value='" + data.rating_flight + "']").prop("checked",true);
			$("#rating_airport").find("[value='" + data.rating_airport + "']").prop("checked",true);
			$("[name=comment_airline]").val(data.comment_airline);
			$("[name=comment_flight]").val(data.comment_flight);
			$("[name=comment_airport]").val(data.comment_airport);
		}
		
		modal.modal("show");
	}
	</script>
<? } ?>