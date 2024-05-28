<?
//======================================================================
//========== [Custom] Air Search & Booking Functions ==========
//======================================================================

//Build search request
function customSearch($parameters){
	if ($parameters["type"]!=1){ return array(true, "Error"); }
	
	global $data_flight_classes;
	global $user_currencyCode;
	
	//Get custom trips
	$custom_trips = array();
	$date_start = strtotime("today", getTimestamp($parameters["departure"], "j-n-Y"));
	$date_end = strtotime("tomorrow", $date_start) - 1;
	$date_start = $date_start - ($parameters["flexible"] ? (86400 * 3) : 0);
	$date_end = $date_end + ($parameters["flexible"] ? (86400 * 3) : 0);
	if ($parameters["carrier"]){
		$conditions .= " AND airline LIKE '%" . $parameters["carrier"] . "%'";
	}
	
	$custom_result = mysqlQuery("SELECT * FROM flights_custom WHERE currency='" . $user_currencyCode . "' AND origin='" . $parameters["from"] . "' AND destination='" . $parameters["to"] . "' AND date BETWEEN $date_start AND $date_end $conditions");
	while ($custom_entry = mysqlFetch($custom_result)){
		$seating = json_decode($custom_entry["pricing"], true)[$parameters["class"]];
		$adt_seating = $seating["0-seats"];
		$cnn_seating = $seating["1-seats"];
		$inf_seating = $seating["2-seats"];
		if ($parameters["adults"] <= $adt_seating && $parameters["children"] <= $cnn_seating && $parameters["toddlers"] <= $inf_seating){
			array_push($custom_trips, $custom_entry["id"]);
		}
	}

	$return = array();
	foreach ($custom_trips AS $index){
		$trip_data = getID($index, "flights_custom");
		$origin = getData("system_database_airports", "iata", $trip_data["origin"]);
		$destination = getData("system_database_airports", "iata", $trip_data["destination"]);
		
		//Trips object
		$trip = array();
		$trip["key"] = encryptText($index);
		$trip["date"] = $trip_data["date"];
		$trip["from"] = ["airport" => $origin];
		$trip["to"] = ["airport" => $destination];
		
		//Fligt
		$trip["flights"] = [[
			"flights" => [[
				"from" => $origin,
				"to" => $destination,
				"airline" => getData("system_database_airlines", "iata", $trip_data["airline"]),
				"equipment" => getData("system_database_planes", "iata", $trip_data["plane_type"]),
				"trip" => $trip_data["flight_number"],
				"cabin" => $data_flight_classes[$parameters["class"]],
				"takeoff" => [
					"time" => $trip_data["takeoff"],
					"terminal" => $trip_data["takeoff_hall"]
				],
				"landing" => [
					"time" => $trip_data["landing"],
					"terminal" => $trip_data["landing_hall"]
				],
				"luggage" => [
					"pieces" => $trip_data["luggage_number"],
					"weight" => $trip_data["luggage_weight"],
					"unit" => "Kilograms"
				],
				"duration" => $trip_data["duration"],
				"distance" => $trip_data["distance"],
				"rating" => null,
			]],
			"booking" => md5(rand(1000,9999)) . md5(uniqid())
		]];
		
		//Pricing
		$pricing = json_decode($trip_data["pricing"], true)[$parameters["class"]];
		$total_price = 0;
		$trip["pricing"] = array();
		if ($parameters["adults"]){
			$base = $pricing["0-price"];
			$commission = $pricing["0-commission"];
			$total = ($base + $commission) * $parameters["adults"];
			$trip["pricing"]["ADT"] = [
				"base" => $base,
				"taxes" => 0,
				"units" => $base,
				"commission" => $commission,
				"count" => $parameters["adults"],
				"total" => $total
			];
			$total_price += $total;
		}
		if ($parameters["children"]){
			$base = $pricing["1-price"];
			$commission = $pricing["1-commission"];
			$total = ($base + $commission) * $parameters["children"];
			$trip["pricing"]["CNN"] = [
				"base" => $base,
				"taxes" => 0,
				"units" => $base,
				"commission" => $commission,
				"count" => $parameters["children"],
				"total" => $total
			];
			$total_price += $total;
		}
		if ($parameters["toddlers"]){
			$base = $pricing["2-price"];
			$commission = $pricing["2-commission"];
			$total = ($base + $commission) * $parameters["toddlers"];
			$trip["pricing"]["INF"] = [
				"base" => $base,
				"taxes" => 0,
				"units" => $base,
				"commission" => $commission,
				"count" => $parameters["toddlers"],
				"total" => $total
			];
			$total_price += $total;
		}

		//Penalties
		$penalties = json_decode($trip_data["penalties"], true);
		$trip["penalties"] = array();
		if ($parameters["adults"]){
			$trip["penalties"]["ADT"] = [
				"change" => [
					"amount" => $penalties["change"]["adt"] . ($penalties["change"]["adt-policy"]=="0" ? " " . $trip_data["currency"] : "%"),
					"applies" => "Anytime"
				],
				"cancel" => [
					"amount" => $penalties["cancel"]["adt"] . ($penalties["cancel"]["adt-policy"]==0 ? " " . $trip_data["currency"] : "%"),
					"applies" => "Anytime"			
				]
			];
		}
		if ($parameters["children"]){
			$trip["penalties"]["CNN"] = [
				"change" => [
					"amount" => $penalties["change"]["cnn"] . ($penalties["change"]["cnn-policy"]==0 ? " " . $trip_data["currency"] : "%"),
					"applies" => "Anytime"
				],
				"cancel" => [
					"amount" => $penalties["cancel"]["cnn"] . ($penalties["cancel"]["cnn-policy"]==0 ? " " . $trip_data["currency"] : "%"),
					"applies" => "Anytime"			
				]
			];	
		}
		if ($parameters["toddlers"]){
			$trip["penalties"]["INF"] = [
				"change" => [
					"amount" => $penalties["change"]["inf"] . ($penalties["change"]["inf-policy"]==0 ? " " . $trip_data["currency"] : "%"),
					"applies" => "Anytime"
				],
				"cancel" => [
					"amount" => $penalties["cancel"]["inf"] . ($penalties["cancel"]["inf-policy"]==0 ? " " . $trip_data["currency"] : "%"),
					"applies" => "Anytime"			
				]
			];
		}
	
		$result["key"] = md5(rand(1000,9999)) . md5(uniqid());
		$result["platform"] = 0;
		$result["travelers"] = ($parameters["adults"] + $parameters["children"] + $parameters["toddlers"]);
		$result["price"] = $total_price;
		$result["currency"] = $trip_data["currency"];
		$result["trips"] = array($trip);
		array_push($return, $result);
	}

	return array(true, json_encode($return, JSON_UNESCAPED_UNICODE));
}

//Custom booking
function customBook($id){
	$reservation = getID($id, "flights_reservations");
	$pnr = strtoupper(uniqid());
	mysqlQuery("UPDATE flights_reservations SET pnr='$pnr', status=2 WHERE id=" . $reservation['id']);
	return array(true, $pnr);
}

//======================================================================
//========== [Travelport] Air Search & Booking Functions ==========
//======================================================================

//Initialize CURL request
function travelportCURL($request, $save=false){
	global $system_settings;

	//Live
	/*
	Username: uAPI5876662770-8d052977
	Password: E+j3c5*PQz
	Branch: P3763969
	PCC: 36X1
	*/
	$url = "https://emea.universal-api.travelport.com/B2BGateway/connect/uAPI/AirService";
	
	//Sandbox
	/*
	Username: uAPI3824679469-99c896bb
	Password: s}6C3mZ/tG
	Branch: P7146780
	PCC: 36X1, 36X2
	*/
	//$url = "https://americas.universal-api.pp.travelport.com/B2BGateway/connect/uAPI/AirService";
	
	$authorization = "Universal API/" . $system_settings["travelport_user"] . ":" . $system_settings["travelport_password"];
	$authorization = base64_encode($authorization);

	$curl = curl_init($url);
	$header = array(
		"Content-Type: text/xml;charset=UTF-8", 
		"Accept: gzip,deflate", 
		"Cache-Control: no-cache", 
		"Pragma: no-cache", 
		"SOAPAction: \"\"",
		"Authorization: Basic $authorization", 
		"Content-length: " . strlen($request),
	); 
	curl_setopt($curl, CURLOPT_CONNECTTIMEOUT, 120); 
	curl_setopt($curl, CURLOPT_TIMEOUT, 120); 
	curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false); 
	curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, false); 
	curl_setopt($curl, CURLOPT_POST, true); 
	curl_setopt($curl, CURLOPT_POSTFIELDS, $request); 
	curl_setopt($curl, CURLOPT_HTTPHEADER, $header);
	curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
	$response = curl_exec($curl);
	$curl_info = curl_getinfo($curl);
	$curl_error = curl_error($curl);
	curl_close($curl);
	
	//Return response
	if ($response === false){
		$error_string = ($curl_error ? $curl_error : "cURL error " . $curl_info["http_code"]);
		$return = array(false, $error_string);
	
	} else {
		$xml = parseXMLObject($response);
		//Error with soap
		if ($xml->query("//SOAP-ENV:Body//SOAP-ENV:Fault")->length){
			$error_code = $xml->query("//SOAP-ENV:Body//SOAP-ENV:Fault//SOAP-ENV:faultcode")[0]->nodeValue;
			$error_string = $error_code . " - " . $xml->query("//SOAP-ENV:Body//SOAP-ENV:Fault//SOAP-ENV:faultstring")[0]->nodeValue;
			$return = array(false, $error_string);
		
		//Error without soap
		} else if ($xml->query("//SOAP:Body//SOAP:Fault")->length){
			$error_code = $xml->query("//SOAP:Body//SOAP:Fault//faultcode")[0]->nodeValue;
			$error_string = $error_code . " - " . $xml->query("//SOAP:Body//SOAP:Fault//faultstring")[0]->nodeValue;
			$return = array(false, $error_string);			
		
		//Success
		} else {
			$return = array(true, parseXMLObject(cleanXML($response)));
		}
	}
	
	if ($save){
		$xml_request = "Travelport-Request-" . uniqid() . "-" . rand(1000,9999) . ".xml";
		prettySaveXML($request, "uploads/xml/$xml_request");

		$xml_response = "Travelport-Response-" . uniqid() . "-" . rand(1000,9999) . ".xml";
		prettySaveXML($response, "uploads/xml/$xml_response");
	}
	
	//Save results on fail
	if ($error_string && $error_string!="Server.Business - NO AVAILABILITY FOR THIS REQUEST"){
		if (!$save){
			$xml_request = "Travelport-Request-" . uniqid() . "-" . rand(1000,9999) . ".xml";
			prettySaveXML($request, "uploads/xml/$xml_request");
			
			if ($response !== false){
				$xml_response = "Travelport-Response-" . uniqid() . "-" . rand(1000,9999) . ".xml";
				prettySaveXML($response, "uploads/xml/$xml_response");
			}
		}
		
		$query = "INSERT INTO travelport_errors (
			error,
			xml_request,
			xml_response,
			date
		) VALUES (
			'" . $error_string . "',
			'" . $xml_request . "',
			'" . $xml_response . "',
			'" . time() . "'
		)";
		mysqlQuery($query);
	}
	
	return $return;
}

//Build search request
function travelportSearch($parameters){
	global $user_session_id;
	global $system_settings;
	$branch = $system_settings["travelport_branch"];
	
	//Cabin class
	if ($parameters["class"]){
		$travelport_cabin_classes = array(
			1 => "Economy",
			2 => "Business",
			3 => "First",		
		);
		$airLegModifiersXML = "<air:AirLegModifiers>
			<air:PermittedCabins>
				<com:CabinClass Type='" . $travelport_cabin_classes[$parameters["class"]] . "'/>
			</air:PermittedCabins>
		</air:AirLegModifiers>";
	}

	//Passengers
	$passengers = array();
	$adults = intval($parameters["adults"]);
	for ($i = 0; $i < $adults; $i++){
		array_push($passengers, "<com:SearchPassenger Code='ADT' Age='30'/>");
	}
	if ($parameters["children"]){
		$children = intval($parameters["children"]);
		for ($i = 0; $i < $children; $i++){
			array_push($passengers, "<com:SearchPassenger Code='CNN' Age='10'/>");
		}
	}
	if ($parameters["toddlers"]){
		$toddlers = intval($parameters["toddlers"]);
		for ($i = 0; $i < $toddlers; $i++){
			array_push($passengers, "<com:SearchPassenger Code='INF' Age='1'/>");
		}
	}
	$passengersXML = implode("", $passengers);
	
	//Flexible 
	if ($parameters["flexible"]){
		$searchExtraDaysXML = "<com:SearchExtraDays DaysBefore='3' DaysAfter='3'/>";
	}
	
	//Build base trip
	$airLegsXML = "<air:SearchAirLeg>
		<air:SearchOrigin>
			<com:CityOrAirport Code='" . $parameters["from"] . "'/>
		</air:SearchOrigin>
		<air:SearchDestination>
			<com:CityOrAirport Code='" . $parameters["to"] . "'/>
		</air:SearchDestination>
		<air:SearchDepTime PreferredTime='" . date("Y-m-d", getTimestamp($parameters["departure"], "j-n-Y")) . "'>$searchExtraDaysXML</air:SearchDepTime>
		$airLegModifiersXML
	</air:SearchAirLeg>";

	//Build return trip
	if ($parameters["type"]==2){
		$airLegsXML .= "<air:SearchAirLeg>
			<air:SearchOrigin>
				<com:CityOrAirport Code='" . $parameters["to"] . "'/>
			</air:SearchOrigin>
			<air:SearchDestination>
				<com:CityOrAirport Code='" . $parameters["from"] . "'/>
			</air:SearchDestination>
			<air:SearchDepTime PreferredTime='" . date("Y-m-d", getTimestamp($parameters["arrival"], "j-n-Y")) . "'>$searchExtraDaysXML</air:SearchDepTime>
			$airLegModifiersXML
		</air:SearchAirLeg>";
	
	//Build multiple trips
	} else if ($parameters["type"]==3){
		foreach ($parameters["trips"] AS $key=>$trip){
			$from = $trip["from"];
			$to = $trip["to"];
			$departure = date("Y-m-d", getTimestamp($trip["departure"], "j-n-Y"));
			$airLegsXML .= "<air:SearchAirLeg>
				<air:SearchOrigin>
					<com:CityOrAirport Code='" . $from . "'/>
				</air:SearchOrigin>
				<air:SearchDestination>
					<com:CityOrAirport Code='" . $to . "'/>
				</air:SearchDestination>
				<air:SearchDepTime PreferredTime='" . $departure . "'>$searchExtraDaysXML</air:SearchDepTime>
				$airLegModifiersXML
			</air:SearchAirLeg>";			
		}
	}
	
	//Airlines
	if ($parameters["carrier"]){
		$carrierXML = "<air:PermittedCarriers>
			<com:Carrier Code='" . $parameters["carrier"] . "'/>
		</air:PermittedCarriers>";
	}
	
	//Non stops
	if ($parameters["nonstop"]){
		$flightTypeXML = "<air:FlightType NonStopDirects='true'/>";	
	}
	
	//Currency
	global $user_paymentCurrency;
	$pricingXML = "<air:AirPricingModifiers CurrencyType='" . $user_paymentCurrency["code"] . "'/>";
	
	//Final request XML
	$request = "<soapenv:Envelope xmlns:soapenv='http://schemas.xmlsoap.org/soap/envelope/'><soapenv:Header/><soapenv:Body>
		<air:LowFareSearchReq xmlns:air='http://www.travelport.com/schema/air_v51_0' xmlns:com='http://www.travelport.com/schema/common_v51_0' SolutionResult='false' TargetBranch='$branch' TraceId='$user_session_id'>
		<com:BillingPointOfSaleInfo OriginApplication='UAPI'/>
			$airLegsXML
			<air:AirSearchModifiers>
				<air:PreferredProviders>
					<com:Provider Code='1G'/>
				</air:PreferredProviders>
				$carrierXML        
				$flightTypeXML
			</air:AirSearchModifiers>
			$passengersXML
			$pricingXML
		</air:LowFareSearchReq>
	</soapenv:Body></soapenv:Envelope>";
	
	//Execute search
	$search_result = travelportCURL($request);
	if ($search_result[0]){
		$result_object = travelportParse($search_result[1]);
		return array(true, $result_object);
	} else {
		return array(false, $search_result[1]);
	}
}

function getUTCTime($string){
	date_default_timezone_set("UTC");
	$time = explode(".", $string)[0];
	$time = strtotime($time);
	date_default_timezone_set("Africa/Cairo");
	return $time;
}

function dateLanguageUTC($format, $date){
	date_default_timezone_set("UTC");
	$return = dateLanguage($format, $date);
	date_default_timezone_set("Africa/Cairo");
	return $return;
}

//Parse search results to object
function travelportParse($xpath){
	global $user_session_id;
	
	//Flights
	$flight_details = array();
	$query = $xpath->query("//FlightDetails");
	foreach ($query AS $flight_detail){
		$attributes = array();
		foreach ($flight_detail->attributes AS $key=>$value){
			$attributes[$key] = $value->nodeValue;
		}
		$attributes["XML"] = $flight_detail->ownerDocument->saveXML($flight_detail);
		$flight_details[$flight_detail->getAttribute("Key")] = $attributes;
	}

	//Air Segments
	$air_segments = array();
	$query = $xpath->query("//AirSegment");
	foreach ($query AS $air_segment){
		$attributes = array();
		foreach ($air_segment->attributes AS $key=>$value){
			$attributes[$key] = $value->nodeValue;
		}
		$attributes["FlightDetailsRef"] = $air_segment->getElementsByTagName("FlightDetailsRef")[0]->getAttribute("Key");
		$attributes["XML"] = $air_segment->ownerDocument->saveXML($air_segment);
		$air_segments[$air_segment->getAttribute("Key")] = $attributes;
	}

	//Fair Infos
	$fair_infos = array();
	$query = $xpath->query("//FareInfo");
	foreach ($query AS $fair_info){
		$attributes = array();
		foreach ($fair_info->attributes AS $key=>$value){
			$attributes[$key] = $value->nodeValue;
		}
		$attributes["BaggageAllowance"] = array(
			"pieces" => intval($fair_info->getElementsByTagName("BaggageAllowance")[0]->getElementsByTagName("NumberOfPieces")[0]->nodeValue),
			"weight" => floatval($fair_info->getElementsByTagName("BaggageAllowance")[0]->getElementsByTagName("MaxWeight")[0]->getAttribute("Value")),
			"unit" => $fair_info->getElementsByTagName("BaggageAllowance")[0]->getElementsByTagName("MaxWeight")[0]->getAttribute("Unit")
		);
		$fair_info->nodeValue = "";
		$attributes["XML"] = $fair_info->ownerDocument->saveXML($fair_info);
		$fair_infos[$fair_info->getAttribute("Key")] = $attributes;
	}

	//=============================================	
	
	$result = array();
	
	//Start building itineraries object
	$itineraries = $xpath->query("//AirPricePoint");
	
	foreach ($itineraries AS $itinerary){
		//Cancellation & change policy, detailed pricing
		$travelers = 0;
		
		foreach ($itinerary->getElementsByTagName("AirPricingInfo") AS $info){
			$change_penalty = $info->getElementsByTagName("ChangePenalty");
			$cancel_penalty = $info->getElementsByTagName("CancelPenalty");
			if ($change_penalty[0]){
				$change_penalty_price = preg_split("#(?<=[a-z])(?=\d)#i", $change_penalty[0]->getElementsByTagName("Amount")[0]->nodeValue);
				$change_penalty_percentage = floatval($change_penalty[0]->getElementsByTagName("Percentage")[0]->nodeValue);
				$change_penalty = array(
					"amount" => ($change_penalty_price[0] ? floatval($change_penalty_price[1]) . " " . $change_penalty_price[0] : $change_penalty_percentage . "%"),
					"applies" => $change_penalty[0]->getAttribute("PenaltyApplies")
				);
			}
			if ($cancel_penalty[0]){
				$cancel_penalty_price = preg_split("#(?<=[a-z])(?=\d)#i", $cancel_penalty[0]->getElementsByTagName("Amount")[0]->nodeValue);
				$cancel_penalty_percentage = floatval($cancel_penalty[0]->getElementsByTagName("Percentage")[0]->nodeValue);
				$cancel_penalty = array(
					"amount" => ($cancel_penalty_price[0] ? floatval($cancel_penalty_price[1]) . " " . $cancel_penalty_price[0] : $cancel_penalty_percentage . "%"),
					"applies" => $cancel_penalty[0]->getAttribute("PenaltyApplies")
				);
			}
			
			$type = $info->getElementsByTagName("PassengerType")[0]->getAttribute("Code");
			$penalties[$type] = array(
				"change" => $change_penalty,
				"cancel" => $cancel_penalty
			);
			
			//Pricing
			$pricing_base[$type] = array(
				"base" => $info->getAttribute("ApproximateBasePrice"),
				"taxes" => $info->getAttribute("Taxes"),
				"count" => $info->getElementsByTagName("PassengerType")->length,
				"units" => $info->getAttribute("TotalPrice")
			);
			$travelers += $info->getElementsByTagName("PassengerType")->length;
			
			//Get origin and destination for commission calculation
			$calculation_origins = array();
			$calculation_destinations = array();
			foreach ($info->getElementsByTagName("FlightOptionsList")[0]->getElementsByTagName("FlightOption") AS $option){
				array_push($calculation_origins, $option->getAttribute("Origin"));
				array_push($calculation_destinations, $option->getAttribute("Destination"));
			}
			
			//Prase pricing values with commission
			$ticket_currency = parsePriceCurrency($info->getAttribute("TotalPrice"), 1)[1];
			$ticket_price = 0;
			foreach ($pricing_base AS $key=>$value){
				$pricing[$key]["base"] = parsePriceCurrency($pricing_base[$key]["base"], 1)[0];
				$pricing[$key]["taxes"] = parsePriceCurrency($pricing_base[$key]["taxes"], 1)[0];
				$pricing[$key]["units"] = parsePriceCurrency($pricing_base[$key]["units"], 1)[0];
				$pricing[$key]["commission"] = calculateComission($pricing[$key]["units"], $ticket_currency, $calculation_origins, $calculation_destinations);
				$pricing[$key]["count"] = $pricing_base[$key]["count"];
				$pricing[$key]["total"] = ($pricing[$key]["units"] + $pricing[$key]["commission"]) * $pricing[$key]["count"];
				$ticket_price += $pricing[$key]["total"];
			}
		}
		
		//Set first air pricing info as defualt (The rest is for other passengers with same data)
		$point = $itinerary->getElementsByTagName("AirPricingInfo")[0];
		
		//Start building trips object
		$trips = array();
		foreach ($point->getElementsByTagName("FlightOption") AS $trip){
			
			//Start building flights object
			$flights = array();
			foreach ($trip->getElementsByTagName("Option") AS $flight){
				//Start building journeys object
				$journeys = array();
				
				//Variables required for booking
				$flights_air_segments = array();
				$flights_flight_details = array();
				$flights_fair_infos = array();
				
				foreach ($flight->getElementsByTagName("BookingInfo") AS $journey){
					$journey_segment = $air_segments[$journey->getAttribute("SegmentRef")];
					$journey_flight_details = $flight_details[$journey_segment["FlightDetailsRef"]];
					$joruney_fare_info = $fair_infos[$journey->getAttribute("FareInfoRef")];
					
					//Push variables required for booking
					array_push($flights_air_segments, $journey_segment["XML"]);
					array_push($flights_flight_details, $journey_flight_details["XML"]);
					array_push($flights_fair_infos, $joruney_fare_info["XML"]);
					
					//Rating
					$journey_rating = null;
					$rating = ratingCalculate($journey_segment["FlightNumber"], null, $journey_segment["Carrier"]);
					if ($rating["total"]){
						$journey_rating["value"] = $rating["airport"];
						$journey_rating["total"] = $rating["total"];
					}
					
					//Journey object
					$journey_object = array(
						"from" => getDatabaseData("system_database_airports", "iata", $journey_segment["Origin"], "iata,ar_name,en_name,ar_short_name,en_short_name,country,region"),
						"to" => getDatabaseData("system_database_airports", "iata", $journey_segment["Destination"], "iata,ar_name,en_name,ar_short_name,en_short_name,country,region"),
						"airline" => getDatabaseData("system_database_airlines", "iata", $journey_segment["Carrier"], "iata,ar_name,en_name"),
						"equipment" => getDatabaseData("system_database_planes", "iata", $journey_flight_details["Equipment"], "iata,ar_name,en_name"),
						"trip" => $journey_segment["FlightNumber"],
						"cabin" => $journey->getAttribute("CabinClass"),
						"takeoff" => [
							"time" => getUTCTime($journey_flight_details["DepartureTime"]),
							"terminal" => $journey_flight_details["OriginTerminal"]
						],
						"landing" => [
							"time" => getUTCTime($journey_flight_details["ArrivalTime"]),
							"terminal" => $journey_flight_details["DestinationTerminal"]
						],
						"luggage" => $joruney_fare_info["BaggageAllowance"],
						"duration" => floatval($journey_segment["FlightTime"]),
						"distance" => floatval($journey_segment["Distance"]),
						"rating" => $journey_rating
					);
					array_push($journeys, $journey_object);
				}
				
				//Build connections if any
				$connections = array();
				foreach ($flight->getElementsByTagName("Connection") AS $connection){
					array_push($connections, $connection->ownerDocument->saveXML($connection));
				}
				
				//Build data required for booking
				$booking = array(
					"Connection" => implode("", $connections),
					"OptionKey" => $flight->getAttribute("Key"),
					"AirSegmentList" => $flights_air_segments,
					"FlightDetailsList" => $flights_flight_details,
					"FareInfoList" => $flights_fair_infos,
					"AirPricePoint" => $itinerary->ownerDocument->saveXML($itinerary)
				);
				array_push($flights, array(
					"flights" => $journeys,
					"booking" => base64_encode(json_encode($booking))
				));
			}
			
			//Read origin and destination data
			$origin = getDatabaseData("system_database_airports", "iata", $trip->getAttribute("Origin"), "id,iata,ar_name,en_name,ar_short_name,en_short_name,country,region");
			$destination = getDatabaseData("system_database_airports", "iata", $trip->getAttribute("Destination"), "id,iata,ar_name,en_name,ar_short_name,en_short_name,country,region");

			//Trips object
			$trip_object = array(
				"key" => $trip->getAttribute("LegRef"),
				"date" => $flights[0]["flights"][0]["takeoff"]["time"],
				"penalties" => $penalties,
				"pricing" => $pricing,
				"from" => [
					"airport" => $origin
				],
				"to" => [
					"airport" => $destination
				],
				"flights" => $flights
			);
			array_push($trips, $trip_object);
		}

		//Itineraries object
		$object = array(
			"key" => $itinerary->getAttribute("Key"),
			"platform" => 1,
			"travelers" => $travelers,
			"price" => round($ticket_price, 2),
			"currency" => $ticket_currency,
			"trips" => $trips
		);
		array_push($result, $object);
	}

	return json_encode($result, JSON_UNESCAPED_UNICODE);
}

//Booking request
function travelportBook($id){
	global $user_session_id;
	
	//Parse passenger type int as travelport code
	$data_map_travelport_passenger_types = array(
		0 => "ADT",
		1 => "CNN",
		2 => "INF"
	);

	//Selections is an array containing all flight options with the required data
	//OptionKey, AirSegmentList, FlightDetailsList, FareInfoList, AirPricePoint
	//Built via the search request function and passed as base64 encoded to the selection radio button
	$reservation = getID($id, "flights_reservations");
	$selections = json_decode($reservation["selections"], true);
	
	//Build passengers XML
	$passengers_keys["ADT"] = array();
	$passengers_keys["CNN"] = array();
	$passengers_keys["INF"] = array();
	$passengers_parameters = array();
	$passengers = explode(",", $reservation["passengers"]);
	foreach ($passengers AS $id){
		$passenger = getID($id, "users_passengers");
		$passenger_type = $data_map_travelport_passenger_types[$passenger["type"]];
		$passengers_xml .= "<com:BookingTraveler xmlns:com='http://www.travelport.com/schema/common_v51_0' Key='" . $passenger["code"] . "' TravelerType='$passenger_type' DOB='" . date("Y-m-d", $passenger["birth_date"]) . "'>
			<com:BookingTravelerName First='" . $passenger["first_name"] . "' Last='" . $passenger["last_name"] . "'/>
			<com:PhoneNumber Number='" . getID($passenger["user_id"], "users_database", "mobile") . "'/>
		</com:BookingTraveler>";
		$passengers_parameters[$passenger["code"]]["age"] = ceil((time() - $passenger["birth_date"]) / 31536000);
		array_push($passengers_keys[$passenger_type], $passenger["code"]);
	}


	//PCC
	global $system_settings;
	$pccs = json_decode($system_settings["travelport_pcc"], true);
	$target_pcc = $pccs[$reservation["so_currency"]];
	if ($target_pcc){
		$pcc = "PseudoCityCode='$target_pcc'";
	}
	$branch = $system_settings["travelport_branch"];

	$AirPricingSolutions = base64_decode($reservation["selection_parameters"]);
	$AirPricingSolutions = str_replace("<", "<air:", $AirPricingSolutions);
	$AirPricingSolutions = str_replace("<air:/", "</air:", $AirPricingSolutions);
	$AirPricingSolutions = str_replace("air:common", "common", $AirPricingSolutions);
	
	$request = "<soapenv:Envelope xmlns:soapenv='http://schemas.xmlsoap.org/soap/envelope/'><soapenv:Header/><soapenv:Body>
		<univ:AirCreateReservationReq xmlns:air='http://www.travelport.com/schema/air_v51_0' xmlns:common_v51_0='http://www.travelport.com/schema/common_v51_0' xmlns:univ='http://www.travelport.com/schema/universal_v51_0' RetainReservation='Both' TargetBranch='$branch' TraceId='$user_session_id'>
		<com:BillingPointOfSaleInfo xmlns:com='http://www.travelport.com/schema/common_v51_0' OriginApplication='UAPI'/>
		$passengers_xml
		<com:FormOfPayment xmlns:com='http://www.travelport.com/schema/common_v51_0' Type='Cash'/>
		$AirPricingSolutions
		<com:ActionStatus xmlns:com='http://www.travelport.com/schema/common_v51_0' ProviderCode='1G' Type='ACTIVE' $pcc/>
		</univ:AirCreateReservationReq>
	</soapenv:Body></soapenv:Envelope>";
	
	//Execute booking
	$search_result = travelportCURL($request, true);
	if ($search_result[0]){
		$result_object = $search_result[1];
		try {
			//$pnr = $result_object->query("//universal:UniversalRecord")[0]->getElementsByTagName("AirReservation")[0]->getAttribute("LocatorCode");
			$pnr = $result_object->query("//universal:ProviderReservationInfo")[0]->getAttribute("LocatorCode");
		} catch(Exception $e){
			$pnr = '';
		}
		//Update PNR
		if ($pnr){
			mysqlQuery("UPDATE flights_reservations SET pnr='$pnr', status=2 WHERE id=" . $reservation['id']);
		}
		return array(true, $pnr);
	} else {
		return array(false, $search_result[1]);
	}
}

//AirPrice Request
function travelportAirPrice($id){
	//PASSENGERS ARE HOT FIXED - NEEDS REFACTORING ASAP
	
	global $user_session_id;
	
	//Parse passenger type int as travelport code
	$data_map_travelport_passenger_types = array(
		0 => "ADT",
		1 => "CNN",
		2 => "INF"
	);

	//Selections is an array containing all flight options with the required data
	//OptionKey, AirSegmentList, FlightDetailsList, FareInfoList, AirPricePoint
	//Built via the search request function and passed as base64 encoded to the selection radio button
	$reservation = getID($id, "flights_reqeusts");
	$selections = json_decode($reservation["selections"], true);
	
	//Build passengers XML
	$passengers_keys["ADT"] = array();
	$passengers_keys["CNN"] = array();
	$passengers_keys["INF"] = array();
	$passengers_parameters = array();
	$passengers = explode(",", $reservation["passengers"]);
	foreach ($passengers AS $id){
		$passenger = getID($id, "users_passengers");
		$passenger_type = $data_map_travelport_passenger_types[$passenger["type"]];
		$passengers_xml .= "<com:BookingTraveler xmlns:com='http://www.travelport.com/schema/common_v51_0' Key='" . $passenger["code"] . "' TravelerType='$passenger_type' DOB='" . date("Y-m-d", $passenger["birth_date"]) . "'>
			<com:BookingTravelerName First='" . $passenger["first_name"] . "' Last='" . $passenger["last_name"] . "'/>
			<com:PhoneNumber Number='" . getID($passenger["user_id"], "users_database", "mobile") . "'/>
		</com:BookingTraveler>";
		$passengers_parameters[$passenger["code"]]["age"] = ceil((time() - $passenger["birth_date"]) / 31536000);
		array_push($passengers_keys[$passenger_type], $passenger["code"]);
	}

	//Start building booking XML
	$AirSegmentList = array();

	//Set primary variables to be used in air price point
	foreach ($selections AS $selection){
		$selection = json_decode(base64_decode($selection), true);

		//Get connection air segments
		$connection_air_segments = array();
		$AirPricePoint = parseXMLObject($selection["AirPricePoint"]);
		$query = $AirPricePoint->query("//Option");
		foreach ($query AS $Option){
			foreach ($Option->getElementsByTagName("Connection") AS $Connection){
				$SegmentIndex = intval($Connection->getAttribute("SegmentIndex"));
				$target_segment_key = $Option->getElementsByTagName("BookingInfo")[$SegmentIndex]->getAttribute("SegmentRef");
				array_push($connection_air_segments, $target_segment_key);
			}
		}

		//Build air segment array (By replacing the flight details reference as well)
		foreach ($selection["AirSegmentList"] AS $xml){
			$xpath = parseXMLObject($xml);
			$segment = $xpath->query("//AirSegment")[0];
			$segmentKey = $segment->getAttribute("Key");
			$is_connection = in_array($segmentKey, $connection_air_segments);
			$segment->nodeValue = ($is_connection ? "<air:Connection/>" : "");
			$segment = $segment->ownerDocument->saveXML($segment);
			array_push($AirSegmentList, $segment);
		}
	}

	//Set XPath to air price point
	$selection = json_decode(base64_decode($selections[0]), true);
	$xpath = parseXMLObject($selection["AirPricePoint"]);
	$point = $xpath->query("//AirPricePoint")[0];

	//Air Segments
	$AirSegments = html_entity_decode(implode("", $AirSegmentList));

	//Air Pricing Infos
	$PassengerTypes = null;
	$for_replacement_in_airprice_response = array();
	foreach ($point->getElementsByTagName("AirPricingInfo") AS $AirPricingInfo){
		//Passenger Type
		foreach ($AirPricingInfo->getElementsByTagName("PassengerType") AS $PassengerType){
			$passengerTypeCode = $PassengerType->getAttribute("Code");
			$PassengerType->setAttribute("BookingTravelerRef", $passengers_keys[$passengerTypeCode][0]);
			$PassengerType->setAttribute("Age", $passengers_parameters[$passengers_keys[$passengerTypeCode][0]]["age"]);
			array_push($for_replacement_in_airprice_response, $passengerTypeCode . "|" . $passengers_parameters[$passengers_keys[$passengerTypeCode][0]]["age"] . "|" . $passengers_keys[$passengerTypeCode][0]);
			array_splice($passengers_keys[$passengerTypeCode], 0, 1);
			$PassengerTypes .= $PassengerType->ownerDocument->saveXML($PassengerType);
		}
	}
	
	//Currency
	global $user_paymentCurrency;
	$pricingXML = "<air:AirPricingModifiers CurrencyType='" . $user_paymentCurrency["code"] . "'/>";

	//Branch
	global $system_settings;
	$branch = $system_settings["travelport_branch"];

	//Cabin class
	$search_object = json_decode($reservation["search_object"], true);
	$cabin_class = $search_object["trips"][0]["flights"][0]["cabin"];
	
	//Final request XML
	$request = "<soapenv:Envelope xmlns:soapenv='http://schemas.xmlsoap.org/soap/envelope/'><soapenv:Header/><soapenv:Body>
		<air:AirPriceReq xmlns:air='http://www.travelport.com/schema/air_v51_0' xmlns:common_v51_0='http://www.travelport.com/schema/common_v51_0' xmlns:univ='http://www.travelport.com/schema/universal_v51_0' TargetBranch='$branch' TraceId='$user_session_id'>
		<com:BillingPointOfSaleInfo xmlns:com='http://www.travelport.com/schema/common_v51_0' OriginApplication='UAPI'/>
		<air:AirItinerary>
		$AirSegments
		</air:AirItinerary>
		$pricingXML
		$PassengerTypes
		<air:AirPricingCommand/>
		</air:AirPriceReq>
	</soapenv:Body></soapenv:Envelope>";

	//Replace needed contents
	$request = str_replace("PassengerType", "com:SearchPassenger xmlns:com='http://www.travelport.com/schema/common_v51_0'", $request);
	$request = str_replace("AirSegment", "air:AirSegment", $request);
	$request = str_replace("<air:AirSegment", '<air:AirSegment ProviderCode="1G"', $request);

	//Execute request
	$request_result = travelportCURL($request, true);
	if ($request_result[0]){
		$result_object = $request_result[1];
		try {
			//Save AirPricingSolution
			$AirPricingSolution = $result_object->query("//AirPricingSolution")[0];
			//foreach ($query AS $AirPricingSolution){
				$aps .= $AirPricingSolution->ownerDocument->saveXML($AirPricingSolution);
			//}
			
			//Replace AirSegmentRef with actual AirSegment
			$AirSegments = array();
			$query = $result_object->query("//AirSegment");
			foreach ($query AS $AirSegment){
				$key = $AirSegment->getAttribute("Key");
				$AirSegments[(string)$key] = $AirSegment->ownerDocument->saveXML($AirSegment);
			}
			foreach ($AirSegments AS $key=>$value){
				$aps = str_replace("<AirSegmentRef Key=\"$key\"/>", $value, $aps);
			}
			
			//Append BookingTravelerRef to PassengerType
			$PassengerTypes = array();
			$query = $result_object->query("//PassengerType");
			foreach ($query AS $PassengerType){
				$code = $PassengerType->getAttribute("Code");
				$age = $PassengerType->getAttribute("Age");
				$currentRef = $PassengerType->getAttribute("BookingTravelerRef");
				if (!$currentRef){
					foreach ($for_replacement_in_airprice_response AS $key=>$value){
						$explode = explode("|", $value);
						$value_code = $explode[0];
						$value_age = $explode[1];
						$value_ref = $explode[2];
						if ($code==$value_code && $age==$value_age){
							$BookingTravelerRefReplace = $value_ref;
							unset($for_replacement_in_airprice_response[$key]);
							break;
						}
					}
					$aps = str_replace("<PassengerType Code=\"$code\" Age=\"$age\"/>", "<PassengerType Code=\"$code\" Age=\"$age\" BookingTravelerRef=\"$BookingTravelerRefReplace\"/>", $aps);
				}
			}
			
		} catch(Exception $e){
			$aps = '';
		}

		//Update AirPricingSolution
		if ($aps){
			mysqlQuery("UPDATE flights_reqeusts SET selection_parameters='" . base64_encode($aps) . "' WHERE id=" . $reservation['id']);
		}
		return array(true, true);
	} else {
		return array(false, $request_result[1]);
	}
}


//========== XML Functions ==========

//Parse XML into an object
function parseXMLObject($xml){
	$response = new DOMDocument();
	$response->loadXML($xml);
	$xpath = new DOMXPath($response);
	return $xpath;
}

//Clean XML for parsing
function cleanXML($string){
	$xml = str_replace("air:", "", $string);
	$xml = str_replace("<SOAP:Body>", "", $xml);
	$xml = str_replace('<SOAP:Envelope xmlns:SOAP="http://schemas.xmlsoap.org/soap/envelope/">', "", $xml);
	$xml = str_replace("<SOAP:Body>", "", $xml);
	$xml = str_replace("</SOAP:Body>", "", $xml);
	$xml = str_replace("</SOAP:Envelope>", "", $xml);
	$xml = str_replace("'", '"', $xml);
	return $xml;
}

//Pretty print XML for saving
function prettySaveXML($result, $path){
	$dom = new DOMDocument;
	$dom->preserveWhiteSpace = false;
	$dom->loadXML($result);
	$dom->formatOutput = true;	
	file_put_contents($path, $dom->saveXML());
	return $dom->saveXML();
}

//========== Common Flights Functions ==========

//Render Flight Details
function renderFlightDetails($flight, $penalties=null){
	global $suffix;
	global $system_settings;
	
	$return = "<div class=flight_details>";
	
	foreach ($flight AS $key=>$value){
		$from_country = getData("system_database_countries", "code", $value["from"]["country"], $suffix . "name");
		$from_region = getID($value["from"]["region"], "system_database_regions", $suffix . "name");
		$to_country = getData("system_database_countries", "code", $value["to"]["country"], $suffix . "name");
		$to_region = getID($value["to"]["region"], "system_database_regions", $suffix . "name");
		
		$return .= "<div class=flight>
			<div class=details>
				<div class=airline>
					<img src='uploads/airlines/" . $value["airline"]["iata"] . ".png'>
					<div>" . $value["airline"][$suffix . "name"] . "</div>
					<span><b>الدرجة</b> " . getDictionary($value["cabin"]) . "</span>
					<span><b>الرحلة</b> " . $value["airline"]["iata"] . "-" . $value["trip"] . "</span>
					<span><b>الطائرة</b> " . $value["equipment"][$suffix . "name"] . "</span>
				</div>
				<div class=departure_arrival>
					<h4>" . $value["from"][$suffix . "short_name"] . " (" . $value["from"]["iata"] . ")</h4>
					<h5>" . dateLanguageUTC("H:i A", $value["takeoff"]["time"]) . "</h5>
					<div>" . dateLanguageUTC("l, d F Y", $value["takeoff"]["time"]) . "</div>
					<span><b>الصالة</b> " . naRes($value["takeoff"]["terminal"]) . "</span>
					<small>" . $value["from"][$suffix . "name"] . "<br>$from_region<br>$from_country</small>
				</div>
				<div class=duration_distance>
					<i class='fal fa-plane'></i>
					<div><b>مدة الرحلة</b><br>" . naRes($value["duration"], getDuration($value["duration"] * 60)) . "</div>
					<div><b>المسافة</b><br>" . naRes($value["distance"], convertDistance($value["distance"])) . "</div>
				</div>
				<div class=departure_arrival>
					<h4>" . $value["to"][$suffix . "short_name"] . " (" . $value["to"]["iata"] . ")</h4>
					<h5>" . dateLanguageUTC("H:i A", $value["landing"]["time"]) . "</h5>
					<div>" . dateLanguageUTC("l, d F Y", $value["landing"]["time"]) . "</div>
					<span><b>الصالة</b> " . naRes($value["landing"]["terminal"]) . "</span>
					<small>" . $value["to"][$suffix . "name"] . "<br>$to_region<br>$to_country</small>
				</div>
			</div>
			<div class=luggage>
				<i class='fal fa-suitcase'></i>
				<b>معلومات الأمتعة</b>&nbsp;&nbsp;&nbsp;&nbsp;
				<span><b>العدد</b>&nbsp;&nbsp;" . ($value["luggage"]["pieces"] ? $value["luggage"]["pieces"] . "&nbsp;&nbsp;<small>(للشخص الواحد)</small>" : naRes(null)) . "</span>&nbsp;&nbsp;&nbsp;&nbsp;
				<span><b>الوزن</b>&nbsp;&nbsp;" . ($value["luggage"]["weight"] ? $value["luggage"]["weight"] . "&nbsp;" . $value["luggage"]["unit"] . "&nbsp;&nbsp;<small>(للشخص الواحد)</small>" : naRes(null)) . "</span>
			</div>
		</div>";
		
		if ($flight[$key + 1]){
			$waiting_time = $flight[$key + 1]["takeoff"]["time"] - $value["landing"]["time"];
			$waiting_time = naRes($waiting_time, getDuration($waiting_time));
			$waiting_airport = $value["to"][$suffix . "name"] . " - " . $to_region;
			$return .= "<div class=transit><b>الإنتظار</b> $waiting_time في $waiting_airport</div>";
		}
	}
	
	//Cancellation and change policies
	if ($penalties && (!$system_settings["hide_cancel_fees"] || !$system_settings["hide_change_fees"])){
		$return .= "<div class=penalties>";
			if (!$system_settings["hide_cancel_fees"]){
				$return .= "<div><b class=title>سياسة الإلغاء</b>";
					foreach ($penalties AS $key=>$value){
						$target = $value["cancel"];
						switch ($target["amount"]){
							case "100%": $amount = "<b style='color:red'>غير قابلة للإلغاء</b>"; break;
							case "0%": $amount = "<b style='color:green'>قابلة للإلغاء</b>"; break;
							default: $amount = naRes($target["amount"], "<b style='color:red'>" . $target["amount"] . "</b>");
						}
						$return .= "<div><p>" . getDictionary($key) . "</p><span>" . $amount . ($target["applies"] ? "<small>" . getDictionary($target["applies"]) . "</small>" : "") . "</span></div>";
					}
				$return .= "</div>";
			}
			if (!$system_settings["hide_change_fees"]){
				$return .= "<div><b class=title>سياسة التغيير</b>";
					foreach ($penalties AS $key=>$value){
						$target = $value["change"];
						switch ($target["amount"]){
							case "100%": $amount = "<b style='color:red'>غير قابلة للتغيير</b>"; break;
							case "0%": $amount = "<b style='color:green'>قابلة للتغيير</b>"; break;
							default: $amount = naRes($target["amount"], "<b style='color:red'>" . $target["amount"] . "</b>");
						}
						$return .= "<div><p>" . getDictionary($key) . "</p><span>" . $amount . ($target["applies"] ? "<small>" . getDictionary($target["applies"]) . "</small>" : "") . "</span></div>";
					}
				$return .= "</div>";
			}
		$return .= "</div>";
	}
	
	$return .= "</div>";
	
	return $return;
}

//========== Custom Project Functions ==========

//Calculate company commission
function calculateComission($price, $currency, $origins, $destinations){
	global $logged_user;
	$agent = mysqlFetch(mysqlQuery("SELECT * FROM users_agents WHERE user_id=" . $logged_user["id"]));
	
	//Load agent pricing matrix
	if ($agent){
		$pricing_matrix["fixed"] = json_decode($agent["fixed"], true);
		$pricing_matrix["percentage"] = $agent["percentage"];		
		
	//Load pricing matrix
	} else {
		$pricing_matrix = mysqlFetch(mysqlQuery("SELECT * FROM flights_pricing WHERE origin IN ('" . implode("','", $origins) . "') AND destination IN ('" . implode("','", $destinations) . "') LIMIT 0,1"));
		if (!$pricing_matrix){
			global $system_settings;
			$pricing_matrix["fixed"] = $system_settings["pricing_fixed"];
			$pricing_matrix["percentage"] = $system_settings["pricing_percentage"];
		}
		$pricing_matrix["fixed"] = json_decode($pricing_matrix["fixed"], true);
	}
	
	//Apply pricing matrix
	$fixed = floatval($pricing_matrix["fixed"][$currency]);
	$percentage = floatval($pricing_matrix["percentage"]);
	$commission = floatval(($price * $percentage / 100) + $fixed);
	return round($commission, 2);
}

//Parse amount and currency
function parsePriceCurrency($string, $platform=1){
	switch ($platform){
		//Travelport
		case 1:
			$split = preg_split("#(?<=[a-z])(?=\d)#i", $string);
		break;
	}
	return array($split[1], $split[0]);
}

//Get record by data with fallback
function getDatabaseData($mysqltable, $column, $value, $columns){
	$result = mysqlFetch(mysqlQuery("SELECT $columns FROM $mysqltable WHERE $column='$value'"));
	if (!$result){
		$result = array();
		$explode = explode(",", $columns);
		foreach ($explode AS $column){
			$result[$column] = $value;
		}
	}
	return $result;
}

//Get Country Data
function getCountry($code){
	return mysqlFetch(mysqlQuery("SELECT * FROM system_database_countries WHERE code='$code'"));
}

//Get value from dictionary
function getDictionary($value){
	global $data_dictionary;
	$return = $data_dictionary[$value];
	return ($return ? $return : $value);
}

//Create keywords
function createKeywords($data){
	$keywords = array();
	if ($data["iata"]){ array_push($keywords, strtolower($data["iata"])); }
	if ($data["code"]){ array_push($keywords, strtolower($data["code"])); }
	if ($data["ar_name"]){ array_push($keywords, normalizeString($data["ar_name"])); }
	if ($data["en_name"]){ array_push($keywords, strtolower(normalizeString($data["en_name"]))); }
	if ($data["ar_short_name"]){ array_push($keywords, normalizeString($data["ar_short_name"])); }
	if ($data["en_short_name"]){ array_push($keywords, strtolower(normalizeString($data["en_short_name"]))); }
	if ($data["alias"]){ array_push($keywords, strtolower(normalizeString($data["alias"]))); }
	return implode(" ", $keywords);
}

//Create slugs
function createSlugs($data){
	$slugs = array();
	if ($data["iata"]){ array_push($slugs, createCanonical($data["iata"])); }
	if ($data["code"]){ array_push($slugs, createCanonical($data["code"])); }
	if ($data["ar_name"]){ array_push($slugs, createCanonical($data["ar_name"])); }
	if ($data["en_name"]){ array_push($slugs, createCanonical($data["en_name"])); }
	if ($data["ar_short_name"]){ array_push($slugs, createCanonical($data["ar_short_name"])); }
	if ($data["en_short_name"]){ array_push($slugs, createCanonical($data["en_short_name"])); }
	return implode(",", $slugs);	
}

//Convert seconds to duration
function getDuration($seconds, $separator=", ", $show_days=true, $show_hours=true, $show_minutes=true, $show_seconds=false){
	global $website_language;
	if ($website_language=="ar" || !$website_language){
		$language = array(
			"day" => "يوم",
			"hour" => "ساعة",
			"minute" => "دقيقة",
			"second" => "ثانية",
			"separator" => " و "
		);
	} else {
		$language = array(
			"day" => "Day",
			"hour" => "Hour",
			"minute" => "Minute",
			"second" => "Second",
			"separator" => " and "
		);	
	}

	$days = floor($seconds / (24 * 60 * 60));
	$seconds -= $days * (24 * 60 * 60);
	$hours = floor($seconds / (60*60));
	$seconds -= $hours * (60 * 60);
	$minutes = floor($seconds / (60));
	$seconds -= $minutes * (60);
	
	$value = array();
	if ($days && $show_days){ array_push($value, $days . " " . $language["day"]); }
	if ($hours && $show_hours){ array_push($value, $hours . " " . $language["hour"]); }
	if ($minutes && $show_minutes){ array_push($value, $minutes . " " . $language["minute"]); }
	if ($seconds && $show_seconds){ array_push($value, $seconds . " " . $language["second"]); }
	
	return implode($language["separator"], $value);
}

//Convert distance
function convertDistance($value, $from="mi", $to="km", $include_suffix=true, $precision=2){
	global $data_units;
	if ($from=="mi" && $to=="km"){
		$return = round($value * 1.60934, $precision);
	}
	return $return . ($include_suffix ? " " . $data_units[$to] : "");
}

//Check if payment method is available
function paymentMethodAvailable($id){
	global $system_settings;
	
	switch ($id){
		//بنك مصر مصري
		case 1:
			$available = ($system_settings["misr_egp_merchant"] && $system_settings["misr_egp_password"]);
		break;
		
		//بنك مصر دولار
		case 2:
			$available = ($system_settings["misr_usd_merchant"] && $system_settings["misr_usd_password"]);
		break;
		
		//هايبر باي فيزا/ماستركارد
		case 3:
			$available = ($system_settings["hyperpay_access_token"] && $system_settings["hyperpay_entity_visa"]);
		break;
		
		//هايبر باي مدي
		case 4:
			$available = ($system_settings["hyperpay_access_token"] && $system_settings["hyperpay_entity_mada"]);
		break;
		
		//فودافون كاش
		case 5:
			$available = ($system_settings["vodafone"]);
		break;
		
		//نقدي
		case 6:
			$available = $system_settings["payment_cash"];
		break;
		
		default: $available = false;
	}

	return $available;
}

//Calculate ratings
function ratingCalculate($flight=null, $airport=null, $airline=null){
	$conditions = array();
	if ($flight){
		array_push($conditions, "flight='$flight'");
	}
	if ($airport){
		array_push($conditions, "airport='$airport'");
	}
	if ($airline){
		array_push($conditions, "airline='$airline'");
	}
	$conditions = ($conditions ? "WHERE " . implode(" AND ", $conditions) : "");
	
	$result = mysqlFetch(mysqlQuery("SELECT SUM(rating_flight) AS flights, SUM(rating_airport) AS airports, SUM(rating_airline) AS airlines, COUNT(id) AS total FROM flights_ratings $conditions"));
	
	$return["flight"] = round($result["flights"] / $result["total"], 2);
	$return["airport"] = round($result["airports"] / $result["total"], 2);
	$return["airline"] = round($result["airlines"] / $result["total"], 2);
	$return["total"] = $result["total"];
	
	return $return;
}

//Return rating stars
function ratingStars($rating){
	if ($rating <= 0.5 && $rating > 0){
		return '<i class="fas fa-star-half-alt"></i><i class="far fa-star"></i><i class="far fa-star"></i><i class="far fa-star"></i><i class="far fa-star"></i>';
	}
	if ($rating <= 1 && $rating > 0.5){
		return '<i class="fas fa-star"></i><i class="far fa-star"></i><i class="far fa-star"></i><i class="far fa-star"></i><i class="far fa-star"></i>';
	}
	if ($rating <= 1.5 && $rating > 1){
		return '<i class="fas fa-star"></i><i class="fas fa-star-half-alt"></i><i class="far fa-star"></i><i class="far fa-star"></i><i class="far fa-star"></i>';
	}
	if ($rating <= 2 && $rating > 1.5){
		return '<i class="fas fa-star"></i><i class="fas fa-star"></i><i class="far fa-star"></i><i class="far fa-star"></i><i class="far fa-star"></i>';
	}
	if ($rating <= 2.5 && $rating > 2){
		return '<i class="fas fa-star"></i><i class="fas fa-star"></i><i class="fas fa-star-half-alt"></i><i class="far fa-star"></i><i class="far fa-star"></i>';
	}
	if ($rating <= 3 && $rating > 2.5){
		return '<i class="fas fa-star"></i><i class="fas fa-star"></i><i class="fas fa-star"></i><i class="far fa-star"></i><i class="far fa-star"></i>';
	}
	if ($rating <= 3.5 && $rating > 3){
		return '<i class="fas fa-star"></i><i class="fas fa-star"></i><i class="fas fa-star"></i><i class="fas fa-star-half-alt"></i><i class="far fa-star"></i>';
	}
	if ($rating <= 4 && $rating > 3.5){
		return '<i class="fas fa-star"></i><i class="fas fa-star"></i><i class="fas fa-star"></i><i class="fas fa-star"></i><i class="far fa-star"></i>';
	}
	if ($rating <= 4.5 && $rating > 4){
		return '<i class="fas fa-star"></i><i class="fas fa-star"></i><i class="fas fa-star"></i><i class="fas fa-star"></i><i class="fas fa-star-half-alt"></i>';
	}
	if ($rating <= 5 && $rating > 4.5){
		return '<i class="fas fa-star"></i><i class="fas fa-star"></i><i class="fas fa-star"></i><i class="fas fa-star"></i><i class="fas fa-star"></i>';
	}
}

//Validate Hyperpay
function validateHyperPay($id, $entity="mada"){
	global $system_settings;
	$entity_id = ($entity=="mada" ? $system_settings["hyperpay_entity_mada"] : $system_settings["hyperpay_entity_visa"]);

	$url = ($system_settings["hyperpay_live"] ? "https://eu-prod.oppwa.com/v1/checkouts/$id/payment?entityId=$entity_id" : "https://test.oppwa.com/v1/checkouts/$id/payment?entityId=$entity_id");
	$curl = curl_init($url);
	curl_setopt($curl, CURLOPT_CUSTOMREQUEST, 'GET');
	curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, ($system_settings["hyperpay_live"] ? true : false));
	curl_setopt($curl, CURLOPT_HTTPHEADER, ['Authorization:Bearer ' . $system_settings["hyperpay_access_token"]]);
	curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
	$response = curl_exec($curl);

	//Validate
	$json = json_decode($response, true);
	//000.100.112
	if ($json["result"]["code"]=="000.000.000" || $json["result"]["code"]=="000.000.100"){
		$return = array(true, null, $response);
	} else {
		$return = array(false, $json["result"]["description"], $response);
	}
	
	return $return;
}

//Get reservation pricing matrix
function getReservationPricing($search_object){
	$trip = json_decode($search_object, true);
	$trips = $trip["trips"];
	$return = array();
	foreach ($trips[0]["pricing"] AS $type=>$pricing){
		$return["base"] += $pricing["base"] * $pricing["count"];
		$return["taxes"] += $pricing["taxes"] * $pricing["count"];
		$return["commission"] += $pricing["commission"] * $pricing["count"];
	}
	return $return;
}

//Manage reservation
function reservationManage($data){
	$button = "<i class='fas fa-cogs'></i>&nbsp;&nbsp;" . readLanguage(operations,manage);
	
	$list["pay"] = "<li><a class='d-flex align-items-center' onclick='reservationPay(" . $data["id"] . ")'><i class='fas fa-wallet fa-fw'></i>&nbsp;&nbsp;تأكيد الدفع</a></li>";
	$list["pnr"] = "<li><a class='d-flex align-items-center' onclick='reservationPNR(" . $data["id"] . ")'><i class='fas fa-plane fa-fw'></i>&nbsp;&nbsp;إدخال كود الحجز</a></li>";
	$list["confirm"] = "<li><a class='d-flex align-items-center' onclick='reservationConfirm(" . $data["id"] . ")'><i class='fas fa-check-circle fa-fw'></i>&nbsp;&nbsp;تنفيذ الحجز</a></li>";
	$list["pending_update"] = "<li><a class='d-flex align-items-center' onclick='reservationPendingUpdate(" . $data["id"] . ")'><i class='fas fa-edit fa-fw'></i>&nbsp;&nbsp;طلب تعديل الحجز</a></li>";
	$list["pending_cancel"] = "<li><a class='d-flex align-items-center' onclick='reservationPendingCancel(" . $data["id"] . ")'><i class='fas fa-times fa-fw'></i>&nbsp;&nbsp;طلب الغاء الحجز</a></li>";
	$list["cancel"] = "<li><a class='d-flex align-items-center' onclick='reservationCancel(" . $data["id"] . ")'><i class='fas fa-times-circle fa-fw'></i>&nbsp;&nbsp;إلغاء الحجز</a></li>";
	$list["reopen"] = "<li><a class='d-flex align-items-center' onclick='reservationReOpen(" . $data["id"] . ")'><i class='fas fa-undo fa-fw'></i>&nbsp;&nbsp;إعادة فتح الحجز</a></li>";
	
	switch ($data["status"]){
		case 0: //بانتظار الدفع
			$list = $list["pay"] . $list["cancel"];
		break;
		
		case 1: //Pending - معلق
			$list = $list["pnr"] . $list["pending_cancel"];
		break;
		
		case 2: //Paid
			$list = $list["confirm"] . $list["pending_update"] . $list["pending_cancel"];
		break;
		
		case 3: //Confirmed
			$list = $list["pending_update"] . $list["pending_cancel"];
		break;
		
		case 4: //Pending Update
			$list = $list["confirm"];
		break;
		
		case 5: //Pending Cancel
			$list = $list["cancel"] . $list["reopen"];
		break;
		
		case 6: //Cancelled
			$list = $list["reopen"];
		break;
	}

	return "<div class='crud-dropdown-container'><button type=button class='dropdown-toggle btn btn-default btn-sm btn-block' data-toggle=dropdown aria-haspopup=true aria-expanded=false>" . $button . "&nbsp;&nbsp;<i class='fas fa-angle-down'></i></button><ul class='dropdown-menu animate reverse'>" . $list . "</ul></div>";
}

//========== Common Project Functions [Updatable per project] ==========

//Extra page global parameters
function extraPageGlobalParameters($page_data){
	$extra_parameters = array();
	if ($page_data["date"] > time()){
		$extra_parameters["upcoming"] = true;
	}
	return $extra_parameters;
}

//Built-In page block
function builtPageBlock($type, $entry){
	global $website_information;
	switch ($type){
		case "sandbox":
			$block["title"] = $entry["title"];
			$block["description"] = $entry["description"];
			$block["cover"] = ($entry["cover_image"] ? "uploads/__sandbox/" . $entry["cover_image"] : "uploads/_website/" . $website_information["cover_image"]);
			$block["header"] = ($entry["header_image"] ? "uploads/__sandbox/" . $entry["header_image"] : "uploads/_website/" . $website_information["header_image"]);
			$block["url"] = customPageURL($entry, "__sandbox_section", "sandbox/");
			$block["date"] = $entry["date"];
			$block["views"] = $entry["views"];
			$block["subtitle"] = $entry["child_subtitle"];
			$block["content"] = $entry["content"];
			$gallery = json_decode($page_data["gallery"], true)[0];
			$block["image"] = ($gallery["url"] ? $gallery["url"] = "uploads/__sandbox/" . $gallery["url"] : "uploads/_website/" . $website_information["cover_image"]);
			$block["entry"] = $entry;
		break;
		
		case "en_website_pages": case "ar_website_pages":
			$block["title"] = $entry["title"];
			$block["description"] = $entry["description"];
			$block["cover"] = ($entry["cover_image"] ? "uploads/pages/" . $entry["cover_image"] : "uploads/_website/" . $website_information["cover_image"]);
			$block["header"] = ($entry["header_image"] ? "uploads/pages/" . $entry["header_image"] : "uploads/_website/" . $website_information["header_image"]);
			$block["url"] = customPageURL($entry);
			$block["date"] = $entry["date"];
			$block["views"] = $entry["views"];
			$block["subtitle"] = $entry["child_subtitle"];
			$block["content"] = $entry["content"];
			$gallery = json_decode($page_data["gallery"], true)[0];
			$block["image"] = ($gallery["url"] ? $gallery["url"] = "uploads/pages/" . $gallery["url"] : "uploads/_website/" . $website_information["cover_image"]);
			$block["entry"] = $entry;
		break;
		
		case "gallery":
			$block["title"] = $entry["title"];
			$block["cover"] = "uploads/pages/thumbnails/" . $entry["url"];
			$block["url"] = "uploads/pages/" . $entry["url"];
			$block["url_attributes"] = "data-fancybox=gallery";
		break;
		
		case "videos":
			$block["title"] = $entry["title"];
			$block["cover"] = "https://i.ytimg.com/vi/" . getYoutubeID($entry["url"]) . "/mqdefault.jpg";
			$block["url"] = "https://www.youtube.com/watch?v=" . getYoutubeID($entry["url"]) . "?autoplay=1&rel=0&controls=1&showinfo=0";
			$block["url_attributes"] = "data-fancybox=videos";
		break;

		case "attachments":
			$extension = strtolower(pathinfo($entry["url"], PATHINFO_EXTENSION));
			$block["title"] = $entry["title"];
			$block["cover"] = "uploads/_website/" . $website_information["cover_image"];
			$block["url"] = "uploads/pages/" . $entry["url"];
			$block["url_attributes"] = "download='" . $entry["title"] . ".$extension'";
			$block["icon"] = ($data_file_icons[$extension] ? $data_file_icons[$extension] : "fas fa-file");
		break;		
	}
	return $block;
}

//Read Record Data
function readRecordData($data, $page, $title=null){
	switch ($page){
		case "channel_requests": case "channel_requests_custom":
		$message = "<span>
			<div class=data-item><b>" . readLanguage(users,name) . "</b><div class=data>" . $data["name"] . "</div></div>
			<div class=data-item><b>" . readLanguage(users,email) . "</b><div class=data>" . $data["email"] . "</div></div>
			<div class=data-item><b>" . readLanguage(users,mobile) . "</b><div class=data>" . $data["mobile"] . "</div></div>
			<div class=data-item><b>" . readLanguage(channels,subject) . "</b><div class=data>" . $data["subject"] . "</div></div>
			<div class=data-item><b>" . readLanguage(channels,message) . "</b><div class=data>" . nl2br($data["message"]) . "</div></div>
		</span>";
		break;
		
		case "channel_records_email":
		case "channel_records_sms":
		case "channel_records_push":
			$message = $data["message"];
		break;
		
		case "transaction":
		$message = "<span>
			<div class=data-item><b>البنك المصدر</b><div class=data>" . $data["resultDetails"]["sourceOfFunds.provided.card.issuer"] . "</div></div>
			<div class=data-item><b>الاسم</b><div class=data>" . $data["card"]["holder"] . "</div></div>
			<div class=data-item><b>اخر 4 ارقام</b><div class=data>" . $data["card"]["last4Digits"] . "</div></div>
			<div class=data-item><b>تنتهي شهر</b><div class=data>" . $data["card"]["expiryMonth"] . "</div></div>
			<div class=data-item><b>تنتهي عام</b><div class=data>" . $data["card"]["expiryYear"] . "</div></div>
		</span>";
		break;
	}
	
	$title = ($title ? $title : readLanguage(operations,view));
	$message = cleanString($message);
	
	$return = "<button class='btn btn-primary btn-sm btn-block' onclick=\"bootbox.alert({title:'$title', message:'$message'})\"><i class='fas fa-search'></i>&nbsp;&nbsp;" . readLanguage(operations,view) . "</button>";
	return $return;
}

//Status Labels
function returnStatusLabel($variable, $value, $full_width=true){
	global $$variable;
	$variable_array = $$variable;
	
	switch ($variable){
		case "data_reservation_status";
			$colors = array( 0 => "info", 1 => "warning", 2 => "primary", 3 => "success", 4 => "default", 5 => "default", 6 => "danger" );
		break;	
		
		case "data_no_yes";
			$colors = array( 0 => "danger", 1 => "success" );
		break;	
		
		case "data_disabled_enabled";
			$colors = array( 0 => "danger", 1 => "success" );
		break;

		case "data_new_closed";
			$colors = array( 0 => "success", 1 => "danger" );
		break;			

		case "data_active_inactive";
			$colors = array( 0 => "success", 1 => "danger" );
		break;
	}
	
	return "<div data-status-id='$value'>
		<span class='label label-" . $colors[$value] . ($full_width ? " label-block" : "") . "'>" . $variable_array[$value] . "</span>
	</div>";
}

//CRUD Dropdown Lists
function crudDropdown($data, $form){
	global $data_new_closed;
	
	switch ($form){
		case "channel_requests":
			$button = "<i class='fas fa-cogs'></i>&nbsp;&nbsp;" . readLanguage(operations,manage);
			$list = "<li><a onclick=\"comReplyModal(" . $data["id"] . ",'" . $data["name"] . "','" . $data["email"] . "')\"><i class='fas fa-reply'></i> " . readLanguage(channels,reply) . "</a></li>
			<li class=divider></li>
			<li class=title>" . readLanguage(records,update) . "</li>
			<li><a onclick=\"comUpdateStatus(" . $data["id"] . ",0)\"><i class='fas fa-check-circle'></i> " . $data_new_closed[0] . "</a></li>
			<li><a onclick=\"comUpdateStatus(" . $data["id"] . ",1)\"><i class='fas fa-times-circle'></i> " . $data_new_closed[1] . "</a></li>";
		break;
	}
	
	return "<div class='crud-dropdown-container'><button type=button class='dropdown-toggle btn btn-success btn-sm btn-block' data-toggle=dropdown aria-haspopup=true aria-expanded=false>" . $button . "&nbsp;&nbsp;<i class='fas fa-angle-down'></i></button><ul class='dropdown-menu animate'>" . $list . "</ul></div>";
}

//========== Communication Channels Functions ==========

//Format E-Mails
function mailFormat($message, $language=null){
	global $base_url, $website_information, $database_language;
	$language = ($language ? languageOptions($language) : $database_language);
		$mail_template = "<div style='direction:" . $language["dir"] . "; text-align:" . $language["left"] . "; font-size:13px; font-family:tahoma'>";
		$mail_template .= "<div style='background:#f8f8f8; padding:10px'><img src='" . $base_url . "uploads/_website/" . $website_information["website_logo"] . "' style='height:60px'></div>";
		$mail_template .= "<div style='padding:10px'>";
		$mail_template .= $message;
		$mail_template .= "</div>";
		$mail_template .= "<div style='background:#f8f8f8; padding:10px; text-align:center; font-size:12px; color:#808080'>© " . $website_information["website_name"] . " " . date("Y",time()) . "</div>";
		$mail_template .= "</div>";
	return $mail_template;
}

//Send Channel Template
function sendChannelTemplate($template, $user_id, $data){
	$template = getData("channel_templates", "target", $template);
	$user = getID($user_id, "users_database");
	$user["name"] = explode(" ", $user["name"])[0]; //First name only
	
	//E-Mail
	if ($template["email"] && $user["email"]){
		$subject = str_replace("{name}", $user["name"], $template["email_subject"]);
		$message = str_replace("{name}", $user["name"], $template["email_message"]);
		if ($data){
			foreach ($data AS $key=>$value){
				$message = str_replace("{" . ($key + 1) . "}", $value, $message);
				$subject = str_replace("{" . ($key + 1) . "}", $value, $subject);
			}
		}
		sendMail(array($user["email"]), $subject, html_entity_decode($message));		
	}
	
	//SMS
	if ($template["sms"] && $user["mobile_conventional"]){
		$message = str_replace("{name}", $user["name"], $template["sms_message"]);
		if ($data){
			foreach ($data AS $key=>$value){
				$message = str_replace("{" . ($key + 1) . "}", $value, $message);
			}
		}
		sendSMS(array($user["mobile_conventional"]), $message);
	}
	
	//Push Notification
	if ($template["push"]){
		$title = str_replace("{name}", $user["name"], $template["push_title"]);
		$message = str_replace("{name}", $user["name"], $template["push_message"]);
		if ($data){
			foreach ($data AS $key=>$value){
				$title = str_replace("{" . ($key + 1) . "}", $value, $message);
				$message = str_replace("{" . ($key + 1) . "}", $value, $message);
			}
		}
		sendNotification($user["user_id"], $title, $message, $base_url);
	}
}

//Save E-Mail
function saveMail($response){ //$response -> array($emails, $subject, $message, $response_status, $response_message)
	global $logged_user;
	
	$type = 1;
	$emails = implode("\r\n", $response[0]);
	$subject = $response[1];
	$message = $response[2];
	$response_status = $response[3];
	$response_message = $response[4];
	
	$query = "INSERT INTO channel_records (
		type,
		email,
		title,
		message,
		success,
		response,
		personnel_id,
		date
	) VALUES (
		'" . $type . "',
		'" . $emails . "',
		'" . $subject . "',
		'" . $message . "',
		'" . $response_status . "',
		'" . $response_message . "',
		'" . $logged_user["personnel_id"] . "',
		'" . time() . "'
	)";
	mysqlQuery($query);
}

//Save SMS
function saveSMS($response){ //$response -> array($mobile_numbers, $message, $response_status, $response_message)
	global $logged_user;
	
	$type = 2;
	$mobile_numbers = implode("\r\n", $response[0]);
	$message = $response[1];
	$response_status = $response[2];
	$response_message = $response[3];
	
	$query = "INSERT INTO channel_records (
		type,
		mobile,
		message,
		success,
		response,
		personnel_id,
		date
	) VALUES (
		'" . $type . "',
		'" . $mobile_numbers . "',
		'" . $message . "',
		'" . $response_status . "',
		'" . $response_message . "',
		'" . $logged_user["personnel_id"] . "',
		'" . time() . "'
	)";
	mysqlQuery($query);
}

//Save Push Notification
function saveNotification($response){ //$response -> array($users, $title, $message, $response_status, $response_message)
	global $logged_user;
	
	$type = 3;
	$users = $response[0];
	$title = $response[1];
	$message = $response[2];
	$response_status = $response[3];
	$response_message = $response[4];
	
	$query = "INSERT INTO channel_records (
		type,
		users,
		title,
		message,
		success,
		response,
		personnel_id,
		date
	) VALUES (
		'" . $type . "',
		'" . $users . "',
		'" . $title . "',
		'" . $message . "',
		'" . $response_status . "',
		'" . $response_message . "',
		'" . $logged_user["personnel_id"] . "',
		'" . time() . "'
	)";
	mysqlQuery($query);	
}
?>