//PHP date equivalent function (format, js date object or PHP unix timestamp)
function dateUTC(n, t){
	var localize = $("html").attr("lang");
	t = (typeof t.now === "function" ? t.now() : t);

	const date = new Date();
	const utcOffsetMinutes = date.getTimezoneOffset();
	t = t + (utcOffsetMinutes * 60);
	
	let e, r;
	if (localize=="ar"){
		var array = ["الأحد", "الإثنين", "الثلاثاء", "الأربعاء", "الخميس", "الجمعة", "السبت", "يناير", "فبراير", "مارس", "إبريل", "مايو", "يونيو", "يوليو", "أغسطس", "سبتمبر", "أكتوبر", "نوفمبر", "ديسمبر"];		
	} else {
		var array = ["Sunday", "Monday", "Tuesday", "Wedday", "Thursday", "Friday", "Saturday", "January", "February", "March", "April", "May", "June", "July", "August", "September", "October", "November", "December"];
	}
	const u = array,
	o = /\\?(.?)/gi,
		c = function(n, t){
			return r[n] ? r[n]() : t
		},
		i = function(n, t){
			for (n = String(n); n.length < t;) n = "0" + n;
			return n
		};
	r = {
		d: function(){
			return i(r.j(), 2)
		},
		D: function(){
			return r.l().slice(0, 3)
		},
		j: function(){
			return e.getDate()
		},
		l: function(){
			return u[r.w()]
		},
		N: function(){
			return r.w() || 7
		},
		S: function(){
			const n = r.j();
			let t = n % 10;
			return t <= 3 && 1 === parseInt(n % 100 / 10, 10) && (t = 0), ["st", "nd", "rd"][t - 1] || "th"
		},
		w: function(){
			return e.getDay()
		},
		z: function(){
			const n = new Date(r.Y(), r.n() - 1, r.j()),
				t = new Date(r.Y(), 0, 1);
			return Math.round((n - t) / 864e5)
		},
		W: function(){
			const n = new Date(r.Y(), r.n() - 1, r.j() - r.N() + 3),
				t = new Date(n.getFullYear(), 0, 4);
			return i(1 + Math.round((n - t) / 864e5 / 7), 2)
		},
		F: function(){
			return u[6 + r.n()]
		},
		m: function() {
			return i(r.n(), 2)
		},
		M: function(){
			return r.F().slice(0, 3)
		},
		n: function(){
			return e.getMonth() + 1
		},
		t: function(){
			return new Date(r.Y(), r.n(), 0).getDate()
		},
		L: function(){
			const n = r.Y();
			return n % 4 == 0 & n % 100 != 0 | n % 400 == 0
		},
		o: function(){
			const n = r.n(),
				t = r.W();
			return r.Y() + (12 === n && t < 9 ? 1 : 1 === n && t > 9 ? -1 : 0)
		},
		Y: function(){
			return e.getFullYear()
		},
		y: function(){
			return r.Y().toString().slice(-2)
		},
		a: function(){
			return e.getHours() > 11 ? (localize=="ar" ? "م" : "pm") : (localize=="ar" ? "ص" : "am")
		},
		A: function(){
			return r.a().toUpperCase()
		},
		B: function(){
			const n = 3600 * e.getUTCHours(),
				t = 60 * e.getUTCMinutes(),
				r = e.getUTCSeconds();
			return i(Math.floor((n + t + r + 3600) / 86.4) % 1e3, 3)
		},
		g: function(){
			return r.G() % 12 || 12
		},
		G: function(){
			return e.getHours()
		},
		h: function(){
			return i(r.g(), 2)
		},
		H: function(){
			return i(r.G(), 2)
		},
		i: function(){
			return i(e.getMinutes(), 2)
		},
		s: function(){
			return i(e.getSeconds(), 2)
		},
		u: function(){
			return i(1e3 * e.getMilliseconds(), 6)
		},
		e: function(){
			throw new Error("Not supported")
		},
		I: function(){
			return new Date(r.Y(), 0) - Date.UTC(r.Y(), 0) != new Date(r.Y(), 6) - Date.UTC(r.Y(), 6) ? 1 : 0
		},
		O: function(){
			const n = e.getTimezoneOffset(),
				t = Math.abs(n);
			return (n > 0 ? "-" : "+") + i(100 * Math.floor(t / 60) + t % 60, 4)
		},
		P: function(){
			const n = r.O();
			return n.substr(0, 3) + ":" + n.substr(3, 2)
		},
		T: function(){
			return "UTC"
		},
		Z: function(){
			return 60 * -e.getTimezoneOffset()
		},
		c: function(){
			return "Y-m-d\\TH:i:sP".replace(o, c)
		},
		r: function(){
			return "D, d M Y H:i:s O".replace(o, c)
		},
		U: function(){
			return e / 1e3 | 0
		}
	};
	return function(n, t) {
		return e = void 0 === t ? new Date : t instanceof Date ? new Date(t) : new Date(1e3 * t), n.replace(o, c)
	}(n, t)
}

//Append extra zero for time
var formatTime = function(integer) {
    if (integer < 10) {
        return "0" + integer; 
    } else {
        return integer;
    }
}

//Convert seconds to duration
function getDuration(seconds, separator=", ", show_days=true, show_hours=true, show_minutes=true, show_seconds=false){
	if ($("html").attr("lang")=="ar"){
		var language = {
			"day": "يوم",
			"hour": "ساعة",
			"minute": "دقيقة",
			"second": "ثانية",
			"separator": " و "
		};
	} else {
		var language = {
			"day": "Day",
			"hour": "Hour",
			"minute": "Minute",
			"second": "Second",
			"separator": " and "
		};	
	}

	var days = Math.floor(seconds / (24 * 60 * 60));
	seconds -= days * (24 * 60 * 60);
	var hours = Math.floor(seconds / (60*60));
	seconds -= hours * (60 * 60);
	var minutes = Math.floor(seconds / (60));
	seconds -= minutes * (60);
	
	var value = [];
	if (days && show_days){ value.push(days + " " + language["day"]); }
	if (hours && show_hours){ value.push(hours + " " + language["hour"]); }
	if (minutes && show_minutes){ value.push(minutes + " " + language["minute"]); }
	if (seconds && show_seconds){ value.push(seconds + " " + language["second"]); }
	
	return value.join(language["separator"]);
}

//Parse passport file
function parsePassport(file, identifier=null){
	var ajaxRequest = null;
	var myFormData = new FormData();
	myFormData.append("token", user_token);
	myFormData.append("action", "upload_passport");
	myFormData.append("passport", file[0].files[0]);
	$.alert({
		closeIcon: true,
		content: function(){
			var self = this;
			self.showLoading(false);
			ajaxRequest = $.ajax({
				type: "post",
				url: "requests/",
				data: myFormData,
				dataType: "text",
				cache: false,
				contentType: false,
				processData: false,
				success: function(response){
					var input_suffix = (identifier!==null ? "-" + identifier : "");
					var data = JSON.parse(response);
					$(`input[name='passport${input_suffix}']`).val(data.passport);
					if (!data.ssn){
						quickNotify("تعثر قراءة البيانات من صورة الباسبور", null, "danger", "fas fa-times fa-3x");
					} else {
						$(`select[name='name_prefix${input_suffix}']`).val(data.name_prefix);
						$(`input[name='first_name${input_suffix}']`).val(data.first_name);
						$(`input[name='last_name${input_suffix}']`).val(data.last_name);
						$(`input[name='birth_date${input_suffix}']`).val(data.birth_date);
						$(`select[name='nationality${input_suffix}']`).val(data.nationality).trigger("change");
						$(`input[name='ssn${input_suffix}']`).val(data.ssn);
						$(`input[name='ssn_end${input_suffix}']`).val(data.ssn_end);						
					}
				},
				error: function(request, status, error){
					quickNotify(request.responseText, null, "danger", "fas fa-times fa-3x");
				},
				complete: function(){
					self.close();
				}
			});						
		},
		onClose: function(){
			ajaxRequest.abort();
		}
	});
}

//Rating stars
function ratingStars(rating){
	if (rating <= 0.5 && rating > 0){
		return '<i class="fas fa-star-half-alt"></i><i class="far fa-star"></i><i class="far fa-star"></i><i class="far fa-star"></i><i class="far fa-star"></i>';
	}
	if (rating <= 1 && rating > 0.5){
		return '<i class="fas fa-star"></i><i class="far fa-star"></i><i class="far fa-star"></i><i class="far fa-star"></i><i class="far fa-star"></i>';
	}
	if (rating <= 1.5 && rating > 1){
		return '<i class="fas fa-star"></i><i class="fas fa-star-half-alt"></i><i class="far fa-star"></i><i class="far fa-star"></i><i class="far fa-star"></i>';
	}
	if (rating <= 2 && rating > 1.5){
		return '<i class="fas fa-star"></i><i class="fas fa-star"></i><i class="far fa-star"></i><i class="far fa-star"></i><i class="far fa-star"></i>';
	}
	if (rating <= 2.5 && rating > 2){
		return '<i class="fas fa-star"></i><i class="fas fa-star"></i><i class="fas fa-star-half-alt"></i><i class="far fa-star"></i><i class="far fa-star"></i>';
	}
	if (rating <= 3 && rating > 2.5){
		return '<i class="fas fa-star"></i><i class="fas fa-star"></i><i class="fas fa-star"></i><i class="far fa-star"></i><i class="far fa-star"></i>';
	}
	if (rating <= 3.5 && rating > 3){
		return '<i class="fas fa-star"></i><i class="fas fa-star"></i><i class="fas fa-star"></i><i class="fas fa-star-half-alt"></i><i class="far fa-star"></i>';
	}
	if (rating <= 4 && rating > 3.5){
		return '<i class="fas fa-star"></i><i class="fas fa-star"></i><i class="fas fa-star"></i><i class="fas fa-star"></i><i class="far fa-star"></i>';
	}
	if (rating <= 4.5 && rating > 4){
		return '<i class="fas fa-star"></i><i class="fas fa-star"></i><i class="fas fa-star"></i><i class="fas fa-star"></i><i class="fas fa-star-half-alt"></i>';
	}
	if (rating <= 5 && rating > 4.5){
		return '<i class="fas fa-star"></i><i class="fas fa-star"></i><i class="fas fa-star"></i><i class="fas fa-star"></i><i class="fas fa-star"></i>';
	}
}