module.exports = {
    methods: {
        isLanguageSupported: function (input) {
            var supported = false;

            this.$store.state.kirby.languages.forEach(function (lang) {
                if (!lang.isDefault && lang.locale === input) {
                    supported = true;
                }
            });

            return supported;
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