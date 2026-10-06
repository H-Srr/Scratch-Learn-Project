// Fills the #user-status area of the header with either
//   "Welcome, <name> | Log out"   (logged in)   or   "Log in | Sign up"   (logged out).
// The answer comes from session_info.php. Needs jQuery to be loaded BEFORE this file.
$(document).ready(function () {
    var loggedOutLinks = "<a href='LogIn_SL.html'>Log in</a> | <a href='SignUp_SL.html'>Sign up</a>";

    // PHP only runs on a web server (http://...). If the page was opened by double-clicking
    // it (file://...), don't wait for an answer that can never come: show the default links.
    if (location.protocol === "file:") {
        $("#user-status").html(loggedOutLinks);
        return;
    }

    $.get("session_info.php", function (data) {
        $("#user-status").html(data);
    }).fail(function () {
        // Server unreachable or PHP error: never leave the header stuck on "Loading..."
        $("#user-status").html(loggedOutLinks);
    });
});
