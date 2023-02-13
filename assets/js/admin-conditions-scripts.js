jQuery(document).on('change', '.kbs_option_disable_tickets input', function () {

	const input   = jQuery(this),
		  inputTR = jQuery(this).parents('tr.kbs_option_disable_tickets');
	let targets   = jQuery(this).parents('table').find('tr').not(inputTR);

	jQuery('.kbs-settings-sub-nav li').each((el, value) => {
		targets.push(value);
	});
	kbsAdminConditions({input, inputTR, targets});
});

jQuery(document).ready(function ($) {

	const input   = jQuery('.kbs_option_disable_tickets input'),
		  inputTR = input.parents('tr.kbs_option_disable_tickets');
	let targets   = input.parents('table').find('tr').not(inputTR);

	input.parents('#wpbody-content').find('.kbs-settings-sub-nav li').each((el, value) => {
		targets.push(value);
	})
	kbsAdminConditions({input, inputTR, targets});
});


jQuery(document).on('change', '.kbs_option_disable_kb_articles input', function () {
	const input   = jQuery(this),
		  inputTR = jQuery(this).parents('tr.kbs_option_disable_kb_articles');
	let targets   = jQuery(this).parents('table').find('tr').not(inputTR);

	jQuery('.kbs-settings-sub-nav li').each((el, value) => {
		targets.push(value);
	});
	kbsAdminConditions({input, inputTR, targets});
});

jQuery(document).ready(function ($) {

	const input   = jQuery('.kbs_option_disable_kb_articles input'),
		  inputTR = input.parents('tr.kbs_option_disable_kb_articles');
	let targets   = input.parents('table').find('tr').not(inputTR);

	input.parents('#wpbody-content').find('.kbs-settings-sub-nav li').each((el, value) => {
		targets.push(value);
	});
	kbsAdminConditions({input, inputTR, targets});

});
// Show/hide the settings based on the checkbox.
function kbsAdminConditions(object) {
	let {input, inputTr, targets} = {...object};
	if (input.length && input.is(':checked')) {
		targets.hide();
	} else {
		targets.show();
	}
}
