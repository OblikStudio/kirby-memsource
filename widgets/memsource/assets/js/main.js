var $widget = $('#memsource-widget');
var memsourceUrl = 'http://localhost/nexobank/panel/memsource';

function updateStore (data) {
	$.ajax({
		method: 'PUT',
		url: memsourceUrl + '/store',
		contentType: 'application/json',
		data: JSON.stringify(data)
	}).fail(function (error) {
		console.warn('Could not update store:', error.responseJSON);
	});
}

$widget.find('form').on('submit', function (event) {
	event.preventDefault();

	var $this = $(this);
	var data = $this.serializeArray();

	console.log(data);

	$.ajax({
		method: 'POST',
		url: 'https://cloud.memsource.com/web/api2/v1/auth/login',
		contentType: 'application/json',
		data: JSON.stringify({
			userName: 'joroyordanov',
			password: 'BCRm6C6uZmjrQ9a'
		})
	}).then(function () {
		console.log(arguments);
	});
});

var $btn = $('.memsource-req');
$btn.on('click', function () {
	updateStore({
		foo: 3295
	});
});