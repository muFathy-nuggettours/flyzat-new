<script src="plugins/slider.min.js?v=<?=$system_settings["system_version"]?>"></script>
<link href="plugins/slider.min.css?v=<?=$system_settings["system_version"]?>" rel="stylesheet">

<div class="page_container">

<b class=page_subtitle><?=readlanguage(filter,categorize_search)?></b>

<div class=filters>
	<div class="filter sort">
		<b><?=readlanguage(filter,sort_by)?></b>
		<div>
			<span><?=readlanguage(common,price)?></span>
			<select sort><option value="price:asc"><?=readlanguage(common,minimum)?></option><option value="price:desc"><?=readlanguage(common,maximum)?></option></select>
		</div>
		<div>
			<span><?=readlanguage(filter,flight_time)?></span>
			<select sort><option value="date:asc"><?=readlanguage(common,nearest)?></option><option value="date:desc"><?=readlanguage(common,furthest)?></option></select>
		</div>
		<div>
			<span><?=readlanguage(filter,flight_duration)?></span>
			<select sort><option value="duration:asc"><?=readlanguage(common,shortest)?></option><option value="duration:desc"><?=readlanguage(common,longest)?></option></select>
		</div>
	</div>
	<div class="filter price">
		<b><?=readlanguage(common,price)?></b>
		<div>
			<input id=price_slider type=text>
			<div><span><b></b><?=$user_paymentCurrency[$suffix . "name"]?></span><span><b></b><?=$user_paymentCurrency[$suffix . "name"]?></span></div>
		</div>
	</div>
	<div class="filter stops checkboxes">
		<b><?=readlanguage(filter,stops)?></b>
		<div>
			<label filter-stops=0><input type=checkbox><span><img src="images/icons/0stops.png"><?=readlanguage(filter,no_stop)?></span></label>
			<label filter-stops=1><input type=checkbox><span><img src="images/icons/1stops.png"><?=readlanguage(filter,one_stop)?></span></label>
			<label filter-stops=2><input type=checkbox><span><img src="images/icons/2stops.png"><?=readlanguage(filter,two_stops)?></span></label>
		</div>
	</div>
	<div class="filter airlines checkboxes">
		<b><?=readlanguage(filter,airlines)?></b>
		<div></div>
	</div>
</div>

</div>

<script>
var mixer = null;

//Sort
$("[sort]").selecty();
$(".selecty-options li").click(function(event) {
	var target = $(this).data("value");
	mixer.sort(target);
});

//Initialize filters
function initializeFilters(){
	//Initialize mixer
	mixer = mixitup(".search_results_container", {
		controls: { scope: "local" },
	});
	
	//Reset visibilities
	$(".filter.stops label").show();
	$(".filter.airlines > div label").remove();
	
	//Read and filter filtering data
	var stops = [];
	var airlines = [];
	var prices = [];
	$(".search_results .flight_search_result").each(function(){
		stops = stops.concat($(this).attr("data-stops").split(","));
		airlines = airlines.concat($(this).attr("data-airlines").split(","));
		prices.push(parseFloat($(this).attr("data-price")));
	});
	prices.sort(function(a, b){return a-b});
	stops = stops.filter(function(item, position){
		return stops.indexOf(item) == position;
	});
	airlines = airlines.filter(function(item, position){
		return airlines.indexOf(item) == position;
	});	
	prices = prices.filter(function(item, position){
		return prices.indexOf(item) == position;
	});
	
	//Stops
	$(".filter.stops label").each(function(){
		if (!stops.includes($(this).attr("filter-stops"))){
			$(this).hide();
		}
	});
	/*
	if ($(".filter.stops label:visible").length <= 1){
		$(".filter.stops").hide();
	}
	*/
	var selected_stops = [];
	$("[filter-stops]").on("change", function(){
		selected_stops = [];
		$("[filter-stops] input:checked").each(function(){
			selected_stops.push($(this).parent().attr("filter-stops"));
		});		
		mixer.filter();
	});
	
	//Pricing
	var min = prices[0];
	var max = prices[prices.length - 1];
	var step = (max - min > 1000 ? 1000 : 100);
	var value = "[" + min + "," + max + "]";
	$("#price_slider").attr("data-slider-min", min).attr("data-slider-max", max).attr("data-slider-step", step).attr("data-slider-value", value).slider().on("change", function(){
		mixer.filter();
	});
	$(".filter.price > div > div > span:first-child b").text(numberFormat(min));
	$(".filter.price > div > div > span:last-child b").text(numberFormat(max));
	
	//Airlines
	var selected_airlines = [];
	airlines.forEach(function(item, index){
		var image = item;
		$(".filter.airlines > div").append("<label filter-airlines='" + item + "'><input type=checkbox><span><img src='uploads/airlines/" + image + ".png' onerror=\"$(this).attr('src', 'uploads/airlines/00.png')\"></span></label>");
	});	
	$("[filter-airlines]").on("change", function(){
		selected_airlines = [];
		$("[filter-airlines] input:checked").each(function(){
			selected_airlines.push($(this).parent().attr("filter-airlines"));
		});
		mixer.filter();
	});
	
	//Final filter
	mixitup.Mixer.registerFilter("testResultEvaluateHideShow", "flights", function(result, target){
		var target = $(target.dom.el);
		
		//Price
		var selected_prices = $("#price_slider").val().split(",");
		var filter_price = parseFloat(target.attr("data-price")) >= parseFloat(selected_prices[0]) && parseFloat(target.attr("data-price")) <= parseFloat(selected_prices[1]);
		
		//Airlines
		var target_airlines = target.attr("data-airlines").split(",");
		var common_airlines = $.grep(selected_airlines, function(element) {
			return $.inArray(element, target_airlines) !== -1;
		});
		var filter_airlines = (selected_airlines.length >= 1 ? common_airlines.length >= 1 : true);
		
		//Stops
		var target_stops = target.attr("data-stops").split(",");
		var common_stops = $.grep(selected_stops, function(element) {
			return $.inArray(element, target_stops) !== -1;
		});
		var filter_stops = (selected_stops.length >= 1 ? common_stops.length >= 1 : true);

		return filter_price && filter_airlines && filter_stops;
	});
}

//Reset filters
function resetFilters(){
	mixer.filter();
}
</script>