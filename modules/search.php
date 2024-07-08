<script src="plugins/moment.min.js?v=<?= $system_settings["system_version"] ?>"></script>
<script src="plugins/caleran.min.js?v=<?= $system_settings["system_version"] ?>"></script>
<link href="plugins/caleran.min.css?v=<?= $system_settings["system_version"] ?>" rel="stylesheet">

<style>
	.multiple_trips .switch_destinations {
		display: none !important;
	}

	.caleran-rtl .caleran-calendar-container {
		direction: rtl;
	}

	.caleran-rtl .caleran-inner {
		text-align: right;
	}

	.caleran-rtl .caleran-days .caleran-day {
		float: right;
	}

	.caleran-rtl .caleran-prev-next {
		display: flex;
		flex-direction: row-reverse;
	}

	.caleran-rtl .caleran-prev-month {
		order: 2;
	}

	.caleran-rtl .caleran-next-month {
		order: 1;
	}
</style>

<!-- Set default search values from history -->
<? if (!$search && $search_history) {
	$search = $search_history[0];
} ?>
<? if (!$search["from"]) {
	$search["from"] = mysqlFetch(mysqlQuery("SELECT iata FROM system_database_airports WHERE country='$user_countryCode' ORDER BY popularity DESC, priority DESC LIMIT 0,1"))["iata"];
} ?>

<div class=flight_search_module><!-- Start Search Module -->

	<!-- Buttons -->
	<div class=search_types>
		<ul class="nav nav-tabs tab-inline-header">
			<li><a data-toggle=tab data-type=1><i class="fal fa-reply"></i>&nbsp;&nbsp;<?= readLanguage('reservation', 'going') ?></a></li>
			<li><a data-toggle=tab data-type=2><i class="fal fa-repeat"></i>&nbsp;&nbsp;<?= readLanguage('reservation', 'going_comingback') ?></a></li>
			<li><a data-toggle=tab data-type=3><i class="fal fa-sync"></i>&nbsp;&nbsp;<?= readLanguage('reservation', 'several_distinations') ?></a></li>
		</ul>
	</div>

	<div class=search_box><!-- Start Search Box -->

		<div class=trips><!-- Start Trips -->

			<!-- Base Trip -->
			<div class=trip>
				<div class=destination_container>
					<div class="component destination">
						<span><?= readLanguage('reservation', 'departure_arrival_station') ?></span>
						<div>
							<button type=button class=switch_destinations onclick="switchDestinations(this)"><i class="fal fa-sort-alt"></i></button>
							<div class=input><i class="fal fa-plane-departure icon"></i><select data-input=from></select></div>
							<div class=input><i class="fal fa-plane-arrival icon"></i><select data-input=to></select></div>
						</div>
					</div>
				</div>

				<div class="date_container departure_only" style="display:none">
					<div class="component date">
						<span><?= readLanguage('reservation', 'departure_date') ?></span>
						<div>
							<input type=hidden data-input=departure-only value="<?= ($search["departure"] ? $search["departure"] : date("j-n-Y", time())) ?>">
							<div class=input_date date-picker-departure-only>
								<i class="fal fa-calendar"></i>
								<small></small><b></b><span></span>
							</div>
						</div>
					</div>
				</div>

				<div class="date_container departure">
					<div class="component date">
						<span><?= readLanguage('reservation', 'departure_date') ?></span>
						<div>
							<input type=hidden data-input=departure value="<?= ($search["departure"] ? $search["departure"] : date("j-n-Y", time() + 86400)) ?>">
							<div class=input_date date-picker-departure>
								<i class="fal fa-calendar"></i>
								<small></small><b></b><span></span>
							</div>
						</div>
					</div>
				</div>

				<div class="date_container arrival">
					<div class="component date">
						<span><?= readLanguage('reservation', 'arrival_date') ?></span>
						<div>
							<input type=hidden data-input=arrival value="<?= ($search["arrival"] ? $search["arrival"] : date("j-n-Y", time() + (86400 * 2))) ?>">
							<div class=input_date date-picker-arrival>
								<i class="fal fa-calendar"></i>
								<small></small><b></b><span></span>
							</div>
						</div>
					</div>
				</div>

				<div class=options_container>
					<div class="component options">
						<span><?= readLanguage('reservation', 'passengers_class') ?></span>
						<div>
							<div class=input><i class="fal fa-chair-office icon"></i><select data-input=class><?= populateOptions($data_flight_classes) ?></select></div>
							<div style="position:relative">
								<a class="travelers_dropdown input" data-toggle=dropdown><i class="fal fa-users icon"></i><span></span></a>
								<ul class="dropdown-menu travelers">
									<li>
										<div><b><?= readLanguage('common', 'adult') ?></b><select data-input=adults onchange="updateTravelers()"><? for ($i = 1; $i <= 9; $i++) {
																																						print "<option value=$i>$i</option>";
																																					} ?></select><span>12 <?= readLanguage('common', 'years_more') ?></span></div>
										<div><b><?= readLanguage('common', 'child') ?></b><select data-input=children onchange="updateTravelers()"><? for ($i = 0; $i <= 8; $i++) {
																																						print "<option value=$i>$i</option>";
																																					} ?></select><span><?= readLanguage('common', 'from') ?> 2 <?= readLanguage('common', 'to') ?> 12 <?= readLanguage('common', 'years_old') ?></span></div>
										<div><b><?= readLanguage('common', 'infant') ?></b><select data-input=toddlers onchange="updateTravelers()"><? for ($i = 0; $i <= 8; $i++) {
																																						print "<option value=$i>$i</option>";
																																					} ?></select><span><?= readLanguage('common', 'less_than') ?><?= readLanguage('common', 'two_years') ?></span></div>
									</li>
								</ul>
							</div>
							<script>
								$("[data-input=adults]").val(<?= ($search["adults"] ? $search["adults"] : 1) ?>);
								$("[data-input=children]").val(<?= ($search["children"] ? $search["children"] : 0) ?>);
								$("[data-input=toddlers]").val(<?= ($search["toddlers"] ? $search["toddlers"] : 0) ?>);
								$("[data-input=class]").val(<?= ($search["class"] ? $search["class"] : 1) ?>);
							</script>
						</div>
					</div>
				</div>
			</div>

			<!-- Trip Template -->
			<div class="trip_template template">
				<div class=trip_separator>
					<i class="fal fa-plane"></i>
					<span></span>
				</div>

				<div class=trip>
					<div class=destination_container>
						<div class="component destination">
							<span><?= readLanguage('reservation', 'departure_arrival_station') ?></span>
							<div>
								<button type=button class=switch_destinations onclick="switchDestinations(this)"><i class="fal fa-sort-alt"></i></button>
								<div class=input><i class="fal fa-plane-departure icon"></i><select data-input=from-multiple></select></div>
								<div class=input><i class="fal fa-plane-arrival icon"></i><select data-input=to-multiple></select></div>
							</div>
						</div>
					</div>

					<div class="date_container departure_multiple">
						<div class="component date">
							<span><?= readLanguage('reservation', 'departure_date') ?></span>
							<div>
								<input id="dr-1" type=hidden data-input=departure-multiple value="<?= date("j/n/Y", time()) ?>">
								<div class=input_date date-picker-departure-multiple>
									<i class="fal fa-calendar"></i>
									<div class="date_values">
										<small></small><b></b><span></span>
									</div>
								</div>
							</div>
						</div>
					</div>
					<div style="margin: auto;">
						<a class="btn btn-primary btn-sm trip_insert" onclick="insertTrip($(this), null, null, $(this).closest('.trip').find('input[type=hidden]').val())"><i class="fal fa-plus-circle"></i> <?= readLanguage('booking', 'add_trip') ?></a>
						<a class="btn btn-danger btn-sm trip_remove" onclick="removeTrip(this)"><i class="fal fa-times-circle"></i> <?= readLanguage('plugins', 'message_delete') ?></a>
					</div>
				</div>
			</div>

			<!-- Multiple Trips -->
			<div class=multiple_trips></div>

		</div><!-- End Trips -->

		<button class="search_button btn btn-primary btn-sm" onclick="submitSearch()"><i class="fal fa-search fa-2x"></i><span><?= readLanguage('search', 'search_for_trips') ?></span></button>

	</div><!-- End Search Box -->

	<!-- Options -->
	<div class=check_container>
		<label><input type=checkbox class=filled-in data-input=nonstop><span><?= readLanguage('common', 'non_stop_trips') ?></span></label>
		<label><input type=checkbox class=filled-in data-input=flexible><span><?= readLanguage('common', 'flex_dates') ?></span></label>
		<script>
			$("[data-input=nonstop]").prop("checked", <?= ($search["nonstop"] ? "true" : "false") ?>);
			$("[data-input=flexible]").prop("checked", <?= ($search["flexible"] ? "true" : "false") ?>);
		</script>
	</div>

</div><!-- End Search Module -->

<script>
	//Calendar
	var startDate, endDate, startInstance, endInstance;
	var fillInputs = function() {
		startInstance.config.target.val(startDate ? startDate.locale(startInstance.config.format).format(startInstance.config.format) : "");
		endInstance.config.target.val(endDate ? endDate.locale(endInstance.config.format).format(endInstance.config.format) : "");
	};

	//Departure
	$("[date-picker-departure]").caleran({
		//Primary parameters
		target: $("[data-input=departure]"),
		format: "D-M-YYYY",
		calendarCount: 1,
		locale: "ar",
		showHeader: false,
		showFooter: false,
		minDate: moment(),
		maxDate: moment().add(1, "year"),
		hideOutOfRange: true,

		//Linked parameters
		startEmpty: $("[data-input=departure]").val() === "",
		startDate: $("[data-input=departure]").val(),
		endDate: $("[data-input=arrival]").val(),
		enableKeyboard: false,
		oninit: function(instance) {
			startInstance = instance;
			if (!instance.config.startEmpty && instance.config.startDate) {
				instance.$elem.val(instance.config.startDate.locale(instance.config.format).format(instance.config.format));
				startDate = instance.config.startDate.clone();
			}
		},
		onbeforeshow: function(instance) {
			if (startDate) {
				startInstance.config.startDate = startDate;
				endInstance.config.startDate = startDate;
			}
			if (endDate) {
				startInstance.config.endDate = endDate.clone();
				endInstance.config.endDate = endDate.clone();
			}
			fillInputs();
			instance.updateHeader();
			instance.reDrawCells();
		},
		onfirstselect: function(instance, start) {
			startDate = start.clone();
			startInstance.globals.startSelected = false;
			startInstance.hideDropdown();
			endInstance.showDropdown();
			endInstance.config.minDate = startDate.clone();
			endInstance.config.startDate = startDate.clone();
			endInstance.config.endDate = null;
			endInstance.globals.startSelected = true;
			endInstance.globals.endSelected = false;
			endInstance.globals.firstValueSelected = true;
			endInstance.setDisplayDate(start);
			if (endDate && startDate.isAfter(endDate)) {
				endInstance.globals.endDate = endDate.clone();
			}
			endInstance.updateHeader();
			endInstance.reDrawCells();
			fillInputs();
			updateCalendarDivision($("[data-input=departure]"), $("[date-picker-departure]"));
		}
	});

	//Arrival
	$("[date-picker-arrival]").caleran({
		//Primary parameters
		target: $("[data-input=arrival]"),
		format: "D-M-YYYY",
		calendarCount: 1,
		locale: "<?= $website_language ?>",
		showHeader: false,
		showFooter: false,
		minDate: moment(),
		maxDate: moment().add(1, "year"),
		hideOutOfRange: true,

		//Linked parameters
		startEmpty: $("[data-input=arrival]").val() === "",
		startDate: $("[data-input=departure]").val(),
		endDate: $("[data-input=arrival]").val(),
		enableKeyboard: false,
		autoCloseOnSelect: true,
		oninit: function(instance) {
			endInstance = instance;
			if (!instance.config.startEmpty && instance.config.endDate) {
				instance.$elem.val(instance.config.endDate.locale(instance.config.format).format(instance.config.format));
				endDate = instance.config.endDate.clone();
			}
		},
		onbeforeshow: function(instance) {
			if (startDate) {
				startInstance.config.startDate = startDate;
				endInstance.config.startDate = startDate;
			}
			if (endDate) {
				startInstance.config.endDate = endDate.clone();
				endInstance.config.endDate = endDate.clone();
			}
			fillInputs();
			instance.updateHeader();
			instance.reDrawCells();
		},
		onafterselect: function(instance, start, end) {
			startDate = start.clone();
			endDate = end.clone();
			endInstance.hideDropdown();
			startInstance.config.endDate = endDate.clone();
			startInstance.globals.firstValueSelected = true;
			fillInputs();
			endInstance.globals.startSelected = true;
			endInstance.globals.endSelected = false;
			updateCalendarDivision($("[data-input=arrival]"), $("[date-picker-arrival]"));
		}
	});

	//Fill inputs on start
	fillInputs();

	//Bind calendar function
	function bindCalendar(source, target, minimum = moment()) {
		var websiteLanguage = '<?= $website_language ?>';
		var isRTL = websiteLanguage === 'ar';

		source.caleran({
			// Primary parameters
			target: target,
			format: "D-M-YYYY",
			calendarCount: 1,
			locale: websiteLanguage,
			showHeader: false,
			showFooter: false,
			minDate: minimum,
			maxDate: moment().add(1, "year"),
			hideOutOfRange: false,
			singleDate: true,
			isRTL: isRTL,

			// Linked parameters
			startEmpty: target.val() === "",
			startDate: target.val(),
			enableKeyboard: false,
			autoCloseOnSelect: true,
			onafterselect: function(instance, start, end) {
				updateCalendarDivision(target, source);

				var targetValue = target.val();

				moment.updateLocale("ar", {
					months: ["يناير", "فبراير", "مارس", "إبريل", "مايو", "يونيو", "يوليو", "أغسطس", "سبتمبر", "أكتوبر", "نوفمبر", "ديسمبر"],
					weekdays: ["الأحد", "الأثنين", "الثلاثاء", "الأربعاء", "الخميس", "الجمعة", "السبت"],
				});

				var targetDate = moment(targetValue, "D-M-YYYY").locale("ar");
				var allInputs = $('input[data-input=departure-multiple]');
				var startUpdating = false;
				var changeMinDate = false;
				var date1 = moment(targetValue, "D-M-YYYY");

				if ($(target)[0] === $('input[data-input=departure-only]')[0]) {
					startUpdating = true;
				}

				allInputs.each(function() {
					var date2 = moment(this.value, "D-M-YYYY");
					if (changeMinDate) {
						$(this).parent().find(".input_date").data('caleran').setMinDate(targetValue);
					}

					if (this === target[0]) {
						startUpdating = true;
					}

					if (startUpdating && date1 >= date2) {
						this.value = targetValue;
						var parent = $(this).parent();
						parent.find(".input_date .date_values small").text(targetDate.format("dddd"));
						parent.find(".input_date .date_values b").text(targetDate.format("DD"));
						parent.find(".input_date .date_values span").text(targetDate.format("MMMM"));
						changeMinDate = true;
					} else if (startUpdating && date1 < date2) {
						changeMinDate = true;
					}
				});
			},
		});

		updateCalendarDivision(target, source);
	}


	//Bind departure calendars
	bindCalendar($("[date-picker-departure-only]"), $("[data-input=departure-only]"));
	$(document).ready(function() {
		var target = $('#datepicker');
		var source = $('#datepicker');
		bindCalendar(source, target);
	});

	//Update calendar division test
	function updateCalendarDivision(source, target) {
		moment.updateLocale("ar", {
			months: ["يناير", "فبراير", "مارس", "إبريل", "مايو", "يونيو", "يوليو", "أغسطس", "سبتمبر", "أكتوبر", "نوفمبر", "ديسمبر"],
			weekdays: ["الأحد", "الأثنين", "الثلاثاء", "الأربعاء", "الخميس", "الجمعة", "السبت"],
			weekdaysShort: ["أحد", "اثنين", "ثلاثاء", "أربعاء", "خميس", "جمعة", "سبت"],
			weekdaysMin: ["ح", "ن", "ث", "ر", "خ", "ج", "س"]
		});
		var date = moment(source.val(), "D-M-YYYY").locale("<?= $website_language ?>");
		target.find("small").text(moment(date).format("dddd"));
		target.find("b").text(moment(date).format("DD"));
		target.find("span").text(moment(date).format("MMMM"));
	}
	updateCalendarDivision($("[data-input=departure-only]"), $("[date-picker-departure-only]"));
	updateCalendarDivision($("[data-input=departure]"), $("[date-picker-departure]"));
	updateCalendarDivision($("[data-input=arrival]"), $("[date-picker-arrival]"));

	//==============================

	//Update travelers count
	function updateTravelers() {
		var adults = parseInt($("[data-input=adults]").val());
		var children = parseInt($("[data-input=children]").val());
		var toddlers = parseInt($("[data-input=toddlers]").val());
		var total = adults + children + toddlers;
		$(".travelers_dropdown span").text(`${total} <?= readLanguage('common', 'passengers2') ?> (${adults} <?= readLanguage('common', 'adult') ?>، ${children} <?= readLanguage('common', 'child') ?>، ${toddlers} <?= readLanguage('common', 'infant') ?>)`);
	}
	$(document).ready(function() {
		updateTravelers();
	});

	//Prevent travelers dropdown closing
	$("body").on("click", ".dropdown-menu.travelers", function(e) {
		$(this).parent().is(".open") && e.stopPropagation();
	});

	//Bind destination Select2
	function bindDestinationSelect2(object, value = null) {
		object.select2({
			containerCssClass: object.attr("data-input"),
			width: "100%",
			placeholder: (object.attr("data-input") == "from" ? "<?= readLanguage('reservation', 'departure_city_airport') ?>" : "<?= readLanguage('reservation', 'arrival_city_airport') ?>"),
			dropdownParent: $(".flight_search_module"),
			minimumInputLength: 0,
			escapeMarkup: function(markup) {
				return markup;
			},
			templateResult: function(data) {
				return data.html;
			},
			templateSelection: function(data) {
				return data.text;
			},
			ajax: {
				url: "requests/",
				method: "POST",
				dataType: "json",
				processResults: function(data) {
					return {
						results: data.results
					};
				},
				data: function(params) {
					var query = {
						action: "search_destinations",
						token: "<?= $token ?>",
						search: params.term
					};
					return query;
				}
			}
		});
		object.on("select2:select", function(e) {
			let value = $(this).val();
			object.parent().find(".error").removeClass("error");
			if (object.attr("data-input") == "to-multiple" || (object.attr("data-input") == "to" && trip_type == 3)) {
				if (object.attr("data-input") == "to" && trip_type == 3) {
					var container = $(".multiple_trips .trip_extra").first();
				} else {
					var container = object.parents(".trip_extra").next();
				}
				let next_from = container.find("[data-input=from-multiple]");
				if (next_from) {
					$.ajax({
						url: "requests/",
						method: "POST",
						data: {
							action: "get_destination",
							token: "<?= $token ?>",
							iata: value
						},
						success: function(result) {
							if (result) {
								next_from.append("<option value='" + value + "' selected>" + result + "</option>").trigger("change");
							}
						}
					});
				}
			}
		});
		if (value) {
			$.ajax({
				url: "requests/",
				method: "POST",
				data: {
					action: "get_destination",
					token: "<?= $token ?>",
					iata: value
				},
				success: function(result) {
					if (result) {
						object.append("<option value='" + value + "' selected>" + result + "</option>");
					}
				}
			});
		}
		object.trigger("change");
	}

	$(document).ready(function() {
		bindDestinationSelect2($("[data-input=from]"), "<?= $search["from"] ?>");
		bindDestinationSelect2($("[data-input=to]"), "<?= $search["to"] ?>");
	});

	//Switch Destinations
	function switchDestinations(target) {
		var from = $(target).parent().find("[data-input=from]");
		var to = $(target).parent().find("[data-input=to]");

		var from_selected = from.find("option:selected");
		var to_selected = to.find("option:selected");

		var from_data = from.select2("data")[0];
		var to_data = to.select2("data")[0];

		from.empty().append(to_selected).select2("data", to_data);
		from.val(to_data.id).trigger("change");

		to.empty().append(from_selected).select2("data", from_data);
		to.val(from_data.id).trigger("change");
	}

	//Start class Select2
	$("[data-input=class]").select2({
		width: "100%",
		dropdownParent: $(".flight_search_module"),
		minimumResultsForSearch: Infinity
	});

	//===== Multiple Trips =====

	//Insert trip
	function insertTrip(from = null, to = null, departure = null, minimum = moment()) {
		var fromVal = null;
		var total_trips = parseInt($(".trip_extra").length) + 1;
		if (total_trips > 6) {
			quickNotify("الحد الاقصي هو 6 رحلات فقط", "<?= readLanguage('search', 'entrydata_err') ?>", "danger", "fas fa-times fa-2x");
			return false;
		}
		var clone = $(".trip_template.template").clone();
		clone.removeClass("template").addClass("trip_extra");
		clone.find(".trip_separator span").text("<?= readLanguage('reservation', 'trip') ?> " + (total_trips + 1));
		if (from) {
			fromVal = ($(from).parent().parent().find("[data-input=to-multiple]").val());
			clone.find("[data-input=from-multiple]").val(from);
		}
		clone.appendTo(".multiple_trips");

		//Bind plugins
		bindDestinationSelect2(clone.find("[data-input=from-multiple]"), fromVal);
		bindDestinationSelect2(clone.find("[data-input=to-multiple]"), to);
		bindCalendar(clone.find("[date-picker-departure-multiple]"), clone.find("[data-input=departure-multiple]"), minimum);

		//Height compensation in slider
		if (typeof heightCompensation === "function") {
			heightCompensation();
		}
	}

	//Remove trip
	function removeTrip(target) {
		$(target).parent().parent().parent().remove();

		//Renumber trips
		var count = 1;
		$(".trip_extra").each(function() {
			count++;
			$(this).find(".trip_separator span").text("رحلة " + count);
		});

		//Height compensation in slider
		if (typeof heightCompensation === "function") {
			heightCompensation();
		}
	}

	//Remove all extra trips
	function removeAllTrips() {
		$(".trip_extra").remove();

		//Height compensation in slider
		if (typeof heightCompensation === "function") {
			heightCompensation();
		}
	}

	//Set active trip type
	var trip_type = null;

	function setActiveType(type) {
		if (type != trip_type) {
			$(".search_types [data-type=" + type + "]").click();
			switch (type) {
				case 1:
					removeAllTrips();
					$(".date_container.departure_only").show();
					$(".date_container.departure").hide();
					$(".date_container.arrival").hide();
					break;

				case 2:
					removeAllTrips();
					$(".date_container.departure_only").hide();
					$(".date_container.departure").show();
					$(".date_container.arrival").show();
					break;

				case 3:
					removeAllTrips();
					<? if (!$search["trips"]) {
						echo "insertTrip();";
					} else {
						foreach ($search["trips"] as $trip) {
							echo "insertTrip('" . $trip["from"] . "', '" . $trip["to"] . "', '" . $trip["departure"] . "');";
						}
					} ?>
					$(".date_container.departure_only").show();
					$(".date_container.departure").hide();
					$(".date_container.arrival").hide();
					break;
			}
			trip_type = type;
		}
	}

	$(".search_types [data-toggle=tab]").on("shown.bs.tab", function(e) {
		setActiveType(parseInt($(e.target).attr("data-type")));
	});

	setActiveType(<?= ($search["type"] ? $search["type"] : 1) ?>);

	//===== Submit =====

	function submitSearch() {
		var trip_object = {};
		switch (trip_type) {
			case 1:
				trip_object.departure = $("[data-input=departure-only]").val();
				break;

			case 2:
				trip_object.departure = $("[data-input=departure]").val();
				trip_object.arrival = $("[data-input=arrival]").val();
				break;

			case 3:
				trip_object.departure = $("[data-input=departure-only]").val();
				trip_object.trips = $(".trip_extra").length;
				var count = 0;
				$(".trip_extra").each(function() {
					count++;
					var trip_parameter = "trip" + count;
					trip_object[trip_parameter + "from"] = $(this).find("[data-input=from-multiple]").val();
					trip_object[trip_parameter + "to"] = $(this).find("[data-input=to-multiple]").val();
					trip_object[trip_parameter + "departure"] = $(this).find("[data-input=departure-multiple]").val();
				});
				break;
		}

		//Build common parameters
		trip_object.type = trip_type;
		trip_object.from = $("[data-input=from]").val();
		trip_object.to = $("[data-input=to]").val();
		trip_object.class = parseInt($("[data-input=class]").val());
		if ($("[data-input=nonstop]").prop("checked")) {
			trip_object.nonstop = true;
		}
		if ($("[data-input=flexible]").prop("checked")) {
			trip_object.flexible = true;
		}
		trip_object.adults = parseInt($("[data-input=adults]").val());
		if (parseInt($("[data-input=children]").val())) {
			trip_object.children = parseInt($("[data-input=children]").val());
		}
		if (parseInt($("[data-input=toddlers]").val())) {
			trip_object.toddlers = parseInt($("[data-input=toddlers]").val());
		}
		<? if ($search["airline"]) { ?>
			trip_object.airline = "<?= $search["airline"] ?>";
		<? } ?>

		//Form validation
		var errors = [];
		if (!$("[data-input=from]").val()) {
			$(".select2-selection--single.from").addClass("error");
			errors.push("<?= readLanguage('search', 'departure_choose_err') ?>");
		}
		if (!$("[data-input=to]").val()) {
			$(".select2-selection--single.to").addClass("error");
			errors.push("<?= readLanguage('search', 'arrival_choose_err') ?>");
		}

		if (trip_type == 3) {
			var last_to = $("[data-input=to]").val();
			var last_date = moment($("[data-input=departure-only]").val(), "D-M-YYYY");
			var valid = true;
			var toValid = true;
			var dateValid = true;

			$(".trip_extra").each(function() {
				var from = $(this).find("[data-input=from-multiple]");
				var to = $(this).find("[data-input=to-multiple]");
				var date = moment($(this).find("[data-input=departure-multiple]").val(), "D-M-YYYY");

				if (last_to != from.val()) {
					toValid = false;
				}
				if (last_date > date) {
					dateValid = false;
				}
				last_date = date;
				last_to = to.val();

				if (!from.val()) {
					valid = false;
					from.parent().find(".select2-selection--single.from-multiple").addClass("error");
				}
				if (!to.val()) {
					valid = false;
					to.parent().find(".select2-selection--single.to-multiple").addClass("error");
				}
			});
			if (!valid) {
				errors.push("<?= readLanguage('search', 'multi_destination_err') ?>");
			}
			if (!toValid) {
				errors.push("<?= readLanguage('search', 'multi_destination_err') ?>");
			}
			if (!dateValid) {
				errors.push("<?= readLanguage('search', 'multi_destination_err') ?>");
			}
		}

		if (errors.length) {
			quickNotify(errors.join("<br>"), "<?= readLanguage('search', 'entrydata_err') ?>", "danger", "fas fa-times fa-2x");
		} else {
			let u = new URLSearchParams(trip_object).toString();
			setWindowLocation("flights/?" + u);
		}
	}
</script>