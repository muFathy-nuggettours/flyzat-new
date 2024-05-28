const cachedMultipleData = {};

function multipleDataOptions(target, options = {}){
	if (!cachedMultipleData[target]){
		cachedMultipleData[target] = Object.assign({
			itemsContainer: "[multiple-sortable]",
			itemRemove: ".remove",
			itemDisplay: "list-item",
			scrollAfterInsertion: true,
			sortable: '[multiple-sortable]',
			creationCallback: `onMultipleDataCreate_${target}`,
			removeCallback: `onMultipleDataRemove_${target}`,
			creationHandlers: {
				date: (id) => createCalendar(id, new Date()),
			},
			readHandlers: {
				checkbox: (id) => $(`#${id}`).prop('checked', true),
			},
		}, options);
	}
	return cachedMultipleData[target];
}

function multipleDataCreate(target, entry=null){
	const options = multipleDataOptions(target);
	const id = Date.now() + Math.floor(Math.random() * 1000);
	const wrapper = $(`[data-multiple="${target}"]`);
	const template = wrapper.find('[data-template]').clone();
	const container = wrapper.find(options.itemsContainer);

	template
		.appendTo(container)
		.removeAttr('data-template')
		.attr({id, 'multiple-item': id})
		.css({display: options.itemDisplay});

	template.find(options.itemRemove).click(() => multipleDataRemove(id));

	template.find('[data-name]').each((i, el) => {
		const inputField = $(el);
		const inputId = target + id + inputField.attr('data-name');
		inputField.attr('id', inputId).prop('disabled', false);

		const handler = options.creationHandlers[inputField.attr('data-type')];
		if (handler) { handler(inputId); }

		if (inputField.attr('data-validation')) {
			inputField.attr('data-validation-optional', false);
		}
	});

	if (options.scrollAfterInsertion && !entry){
		scrollToView($(`#${id}`));
	}

	if (entry){
		Object.keys(entry).forEach((key) => {
			const value = entry[key];
			const inputField = template.find(`[data-name="${key}"]`);
			const handler = options.readHandlers[inputField.attr('data-type')];
			if (handler) { handler(inputField.attr('id'), value); }
			else { inputField.val(decodeHTML(value)); }

			if (!inputField.val() && inputField.is('select')) {
				inputField.append(`<option value="${value}">${value}</option>`).val(value);
			}
		});
	}

	if (typeof window[options.creationCallback] === 'function') {
		window[options.creationCallback](template, entry);
	}

	return template;
}

function multipleDataRemove(id){
	const field = $(`#${id}`);
	const target = field.closest('[data-multiple]').attr('data-multiple');
	const callback = multipleDataOptions(target).removeCallback;
	
	$.confirm({
		title: readLanguage.plugins.message_delete,
		content: readLanguage.plugins.data_delete,
		icon: 'fas fa-trash',
		animateFromElement: false,
		buttons: {
			yes: {
				text: readLanguage.plugins.message_yes,
				btnClass: 'btn-red',
				action: () => {
					field.remove();
					if (typeof window[callback] == 'function') { window[callback](field); }
				}
			},
			cancel: { text: readLanguage.plugins.message_cancel }
		}
	});
}

function multipleDataBuild(){
	$("[data-multiple]").each(function(){
		var dataArray = [];
		$(this).find("[multiple-item]").each(function(){
			var dataObject = {};
			$(this).find("[data-name]").each(function(){
				if ($(this).attr("data-type")=="checkbox"){
					var parentList = $(this).closest("li");
					if (!parentList.attr("data-template") && $(this).prop("checked")){
						dataObject[$(this).attr("data-name")] = 1;
					}
				} else {
					dataObject[$(this).attr("data-name")] = $(this).val();
				}
			});
			dataArray.push(dataObject);
		});
		$(this).find("[name=" + $(this).attr("data-multiple") + "]").val((dataArray.length ? JSON.stringify(dataArray) : ""));
	});
}

$(document).ready(() => {
	$('[data-multiple]').each((i, el) => {
		const target = $(el).attr('data-multiple');
		const sortable = multipleDataOptions(target).sortable;
		if (sortable){
			$(el).find(sortable).sortable({
				containment: $(el),
				placeholder: "ui-state-highlight",
				handle: ($(el).find(`${sortable} .handle`).length ? '.handle' : false),
				tolerance: "pointer",
				forcePlaceholderSize: true
			});
		}
	});
});