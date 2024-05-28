//Icons selection
function bindIconSearch(target=null){
	$.confirm({
		title: "Font Awesome",
		theme: "light-noborder",
		onOpenBefore: function(){
			this.showLoading(true);
		},
		content:" <input type=search name=icon class='icp icp-auto' data-input-search=true data-placement=inline>",
		onContentReady: function (){
			this.$content.find(".icp-auto").iconpicker();
			this.hideLoading(true);
		},
		buttons: {
			submit: {
				text: readLanguage.plugins.message_confirm,
				btnClass: "btn-primary",
				action: function (){
					if (typeof target == "string"){
						$("[name=" + target + "]").val(this.$content.find("[name=icon]").val());
						$("[data-icon=" + target + "]").attr("class",this.$content.find("[name=icon]").val());
					} else {
						$(target).val(this.$content.find("[name=icon]").val()).trigger("input");
					}
				}
			},
			cancel: {
				text: readLanguage.plugins.message_cancel
			},
		}
	});	
}

//Global export PDF function
function exportPDF(path, title='', subtitle='', content='', isFile=false){
	//Variable to hold custom export options DOM inputs
	var exportOptions = "";
	var xhr;
	
	//Perform DOM manipulations if the content is not a file
	if (!isFile){
		//Create a jQuery object of the passed DOM content, content can either be a jQuery selector or an actual jQuery object
		content = $(content).clone();
		
		//Remove DOM objects with .hide_pdf class
		content.find('.hide_pdf').remove();
		
		//Check if there's a view header and sections in content
		var headerPresent = content.find('.info_header').length;
		var sectionsPresent = content.find('.pdf_section').length;

		//Custom export options
		if (headerPresent){
			exportOptions += `<tr><td class=title style="width:30%">${readLanguage.pdf.include_headers}: </td><td><div class=switch><label>${readLanguage.plugins.message_no}<input type=checkbox name=headers checked><span class=lever></span>${readLanguage.plugins.message_yes}</label></div></td></tr>`;
		}
		if (sectionsPresent){
			//Read DOM objects with .pdf_section class if available and assign a uniqID foreach
			var sections = Array.from(content.find('.pdf_section'));
			sections.forEach(section => $(section).attr('id', `_section_${Date.now() + Math.floor((Math.random() * 999) + 100)}`));
			exportOptions += `<tr><td class=title style="width:30%">${readLanguage.pdf.include_sections}: </td><td><div class=check_container>${sections.map(section => `<label><input type=checkbox name=sections[] class=filled-in value='${$(section).attr('id')}' checked><span>${$(section).text()}</span></label>`).join('')}</div></td></tr>`
		}
	}

	//Initialize export dialog
	$.confirm({
		icon: 'fas fa-file-pdf',
		title: title + (subtitle ? ' - ' + subtitle : ''),
		theme: 'light-noborder',
		content: `<table class=data_table>
			<tr><td class=title style="width:30%">${readLanguage.pdf.title}:</td><td><input type=text name=title value="${title}"></td></tr>
			<tr><td class=title style="width:30%">${readLanguage.pdf.subtitle}:</td><td><input type=text name=subtitle value="${subtitle}"></td></tr>
			<tr><td class=title style="width:30%">${readLanguage.pdf.orientation}:</td><td><select name=orientation><option value=P>${readLanguage.pdf.portrait}</option><option value=L>${readLanguage.pdf.landscape}</option></select></td></tr>
			${exportOptions}
		</table>`,
		buttons: {
			submit: {
				text: readLanguage.pdf.export,
				btnClass: 'btn-green',
				action: function(){
					var self = this;
					var title = self.$content.find("[name=title]").val();
					var subtitle = self.$content.find("[name=subtitle]").val();
					var orientation = self.$content.find("[name=orientation]").val();

					//Export option - Remove headers
					if (headerPresent && !this.$content.find('input[name=headers]').is(':checked')){
						content.find('.info_header').remove();
					}
					
					//Export option - Remove unchecked sections
					if (sectionsPresent){
						var selectedSections = Array.from(this.$content.find(`input[name='sections[]']:checked`)).map(el => $(el).val());
						sections.forEach(section => {
							if (!selectedSections.includes($(section).attr('id'))) {
								$(section).siblings('.tab-pane').eq(0).remove();
								$(section).remove();
							}
						});		
					}

					//Run AJAX Request
					self.setContent("<div class=bootbox_loading><b>" + readLanguage.pdf.exporting + "</b><i class='fa fa-spin fa-spinner'></i>" + readLanguage.pdf.exporting_tip + "</div>");
					self.buttons.submit.hide();
					self.buttons.close.hide();
					self.buttons.cancel.show();
					xhr = $.ajax({
						type: 'POST',
						url: path,
						data: {
							token: user_token,
							title: title,
							subtitle: subtitle,
							orientation: orientation,
							content: (!isFile ? content.html() : content),
							file: isFile
						},
						success: function (result){
							if (result){
								self.setContent("<div id=pdf_export_result><div class='alert alert-success'>" + readLanguage.pdf.success + "</div><div class='align-center margin-bottom-10'>" + readLanguage.pdf.succsee_tip + "</div>" + result + "</div>");
							} else {
								self.setContent("<div class='alert alert-danger'>" + readLanguage.pdf.error + "</div><center>" + readLanguage.pdf.error_tip + "</center>");
							}
							self.buttons.cancel.hide();
							self.buttons.close.show();
						}
					});

					return false;
				}
			},
			close: {
				text: readLanguage.plugins.message_close,		
			},
			cancel: {
				isHidden: true,
				text: readLanguage.plugins.message_cancel,
				btnClass: 'btn-red',
				action: function () {
					this.xhr.abort();
				}
			}	
		}
	});
}

//Export PDF from HTML
function exportHTML(title='', subtitle='', content=''){
	exportPDF('_pdf_html.php', title, subtitle, content);
}

//Export Builder
function exportBuilder(target, uniqid, title){
	$.confirm({
		content: function(){
			var self = this;
			return $.ajax({
				method: "POST",
				url: "__requests.php",
				data: {
					token: user_token,
					action: "export_builder",
					target: target,
					uniqid: uniqid
				},
			}).done(function(response){
				forceDownload(response, title);
			}).fail(function(response){
				quickNotify(readLanguage.general.error, response.responseText, 'danger', 'fas fa-times-circle fa-3x')
			}).always(function(){
				self.close();
			});
		}
	});
}