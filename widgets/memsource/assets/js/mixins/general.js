var dateFormat = require('dateformat');

module.exports = {
    methods: {
        dateFormat: dateFormat,
        plural: function (value, text) {
            return value + ' ' + text + (value != 1 ? 's' : '');
        },
        capitalize: function (text) {
            return text.substr(0, 1).toUpperCase() + text.substr(1).toLowerCase();
        },
        isLanguageAvailable: function (input) {
            var value = false;

            this.$store.state.kirby.languages.forEach(function (lang) {
                if (!lang.isActive && lang.locale === input) {
                    value = true;
                }
            });

            return value;
        },
        languageExistsInKirby: function (input) {
            var value = false;

            this.$store.state.kirby.languages.forEach(function (lang) {
                if (lang.locale === input) {
                    value = true;
                }
            });

            return value;
        },
        getErrorMessage: function (error, defaultMessage) {
            var data = (error.response && error.response.data),
                message = null;

            if (data) {
                if (data.errorDescription) {
                    message = data.errorDescription;
                } else if (data.errorCode) {
                    message = data.errorCode;

                    switch (data.errorCode) {
                        case 'AuthInvalidCredentials': message = 'Invalid credentials.'; break;
                    }
                }
            } else if (error.message) {
                message = error.message;
            }

            if (!message) {
                message = defaultMessage || 'Unknown error occurred.';
            }

            return message;
        }
    }
};