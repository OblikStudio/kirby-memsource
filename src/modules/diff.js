function sanitize(value) {
	if (value === null || value === undefined) {
		value = ''
	} else if (typeof value === 'object') {
		value = JSON.stringify(value, null, 4)
	} else if (typeof value !== 'string') {
		value += ''
	}

	return value
}

export function getEntries(data, prefix = null) {
	let entries = []

	for (let k in data) {
		let entry = data[k]
		let name = prefix ? `${prefix}/${k}` : k

		if (entry) {
			let oldValue = entry.$old
			let newValue = entry.$new

			if (typeof oldValue !== 'undefined' && typeof newValue !== 'undefined') {
				entries.push({
					name,
					oldValue: sanitize(oldValue),
					newValue: sanitize(newValue)
				})
			} else if (typeof entry === 'object') {
				entries = entries.concat(getEntries(entry, name))
			}
		}
	}

	return entries
}
