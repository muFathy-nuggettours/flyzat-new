<div class=module_expressions>
    <ul class=links id=seo_links></ul>
    <div class=navigation>
		<hr>
		<div class=bar>
			<button class="btn btn-primary" id=prev><i class="fa fa-caret-right"></i></button>
			<p><span class=current>1</span>/<span class=total>1</span></p>
			<button class="btn btn-primary" id=next><i class="fa fa-caret-left"></i></button>
		</div>
    </div>
</div>

<script>
let currentPage = 1;
let totalPages = 1;

function getDestinations(page=1, scroll=true){
	if (page === null) page = 1;
	$.post("requests/", {
		token: "<?=$token?>",
		action: "get_expressions",
		origin_type: "<?=$origin_type?>",
		destination_type: "<?=$destination_type?>",
		origin: "<?=$origin?>",
		expression: "<?=$expression?>",
		start: page
	}, function(res, status){
		if (status == "success"){
			$("#seo_links").html("");
			let data = JSON.parse(res);
			if (!data.error){
				data.result.forEach((entry) => $('#seo_links').append(`<li class=link-item><a href='${entry.url}'>${entry.name}</a></li>`));
				totalPages = data.total;
				currentPage = +page;
				$('.module_expressions .total').html(totalPages);
				$('.module_expressions .current').html(currentPage);
				$(".module_expressions .navigation").show();
				$('.module_expressions #prev').removeAttr("disabled");
				$('.module_expressions #next').removeAttr("disabled");
				if (currentPage == totalPages){
					$('.module_expressions #next').attr("disabled", true);
				}
				if (currentPage == 1){
					$('.module_expressions #prev').attr("disabled", true);
				}
				if (scroll){
					const params = new URLSearchParams(window.location.search);
					params.set('page', page);
					window.history.replaceState({}, '', decodeURIComponent(`${window.location.pathname}?${params}`));
					scrollToView($(".module_expressions"), $("#nav-sticky").height() + 80, "start");
				}
			} else {
				$('#seo_links').append(readLanguage.reservation.no_trips_page);
				$(".module_expressions .navigation").hide();
			}
		}
	});
}

$(document).ready(() => getDestinations(new URLSearchParams(window.location.search).get('page'), false));
$('#prev').on('click', () => currentPage !== 1 ? getDestinations(currentPage-1) : void(0));
$('#next').on('click', () => currentPage !== totalPages ? getDestinations(currentPage+1) : void(0));
</script>