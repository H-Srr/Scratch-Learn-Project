// ScratchLearn.post(url, data) works like $.post(url, data), but first fetches the site's
// CSRF token (csrf_token.php) and sends it along. The server refuses every request that
// changes something unless it carries that token, so other websites cannot forge them.
// Needs jQuery to be loaded BEFORE this file.
window.ScratchLearn = window.ScratchLearn || {};

(function () {
    var tokenRequest = null; // the token is fetched once and reused

    function getToken() {
        if (tokenRequest === null) {
            tokenRequest = $.getJSON("csrf_token.php");
        }
        return tokenRequest;
    }

    window.ScratchLearn.post = function (url, data) {
        var result = $.Deferred();

        getToken()
            .done(function (res) {
                var payload = $.extend({}, data, { csrf_token: res.token });
                $.post(url, payload)
                    .done(function () {
                        result.resolve.apply(result, arguments);
                    })
                    .fail(function (xhr) {
                        if (xhr && xhr.status === 403) {
                            tokenRequest = null; // session expired: get a fresh token on the next try
                        }
                        result.reject.apply(result, arguments);
                    });
            })
            .fail(function () {
                tokenRequest = null;
                result.reject.apply(result, arguments);
            });

        return result.promise();
    };
})();
