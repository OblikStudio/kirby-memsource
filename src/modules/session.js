exports.load = function () {
	var value = null

	try {
		if (localStorage.memsourceSession) {
			value = JSON.parse(localStorage.memsourceSession)
		}
	} catch (e) {
		console.warn(e)
	}

	var expireDate = (value && value.expires)
	if (expireDate && new Date(expireDate) <= Date.now()) {
		value = null
	}

	return value
}

exports.save = function (data) {
	try {
		localStorage.memsourceSession = JSON.stringify(data)
	} catch (e) {
		console.warn(e)
	}
}
