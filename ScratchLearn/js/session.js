// Shows "Welcome, <name> | Log out" in the header when the visitor is logged in.
//
// The HTML already contains the default "Log in | Sign up" links inside #user-status, so the
// header is complete (and never stuck on "Loading...") even if this request fails.
// Needs jQuery to be loaded BEFORE this file.
$(document).ready(function () {
    // PHP only runs on a web server (http://...). If the page was opened by double-clicking
    // it (file://...), there is nobody to ask, so the default links simply stay.
    if (location.protocol === "file:") {
        return;
    }

    $.get("session_info.php", function (data) {
        // Only accept the real answer of session_info.php (never an error or a host's check page)
        if (typeof data === "string" && (data.indexOf("logout.php") !== -1 || data.indexOf("LogIn_SL.html") !== -1)) {
            $("#user-status").html(data);
        }
    });
});
