'use strict';

var _entries = {};

function createEntry (id, date, delta) {
    var entry = {
        date: date
    };

    entry.promise = new Promise(function (resolve, reject) {
        entry.timer = setTimeout(function () {
            destroyEntry(id);
            resolve();
        }, delta);
    });

    _entries[id] = entry;
    return entry;
}

function destroyEntry (id) {
    var entry = _entries[id];

    if (entry) {
        clearTimeout(entry.timer);
    }

    delete _entries[id];
}

module.exports = function (id, date) {
    var entry = _entries[id],
        expireDate = new Date(date),
        isDateValid = !isNaN(expireDate.getTime());

    if (entry) {
        if (isDateValid && entry.date - expireDate === 0) {
            return entry.promise;
        } else {
            destroyEntry(id);
        }      
    }

    if (isDateValid) {
        var delta = expireDate - Date.now();

        if (delta > 0) {
            return createEntry(id, expireDate, delta).promise;
        } else {
            return Promise.resolve();
        }
    } else {
        return Promise.reject({
            message: 'Invalid date: ' + date
        });
    }
};
