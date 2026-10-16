// Builds the header (logo, menu, login status) and the footer that every page shares, so the
// menu is written ONCE here instead of being copied into every HTML file.
//
// Each page contains   <header id="site-header"></header>   and   <footer id="site-footer"></footer>
// and loads this file as the LAST thing in <body>, so the menu exists before js/session.js
// (which fills the login status) runs.
(function () {
    "use strict";

    var header = document.getElementById("site-header");
    var footer = document.getElementById("site-footer");

    // The page we are on, to mark its menu entry as the current one
    var page = location.pathname.split("/").pop() || "SL_HomePage.html";
    if (page === "index.html") {
        page = "SL_HomePage.html";
    }

    // The menu. "also" lists pages that belong to the same section.
    var menu = [
        { href: "About_SL.html", text: "About" },
        { href: "Quizes.html", text: "Quizzes", also: ["MathQuiz.html", "physQuiz.html"] },
        { href: "learnonyourown.html", text: "Discover Scratch" },
        { href: "feed.html", text: "Leave a Feedback" },
        { href: "Leaderboards.html", text: "Leaderboards" }
    ];

    // What the login area shows until js/session.js knows better (also when the server is down)
    var loggedOut = '<a href="LogIn_SL.html">Log in</a>' +
                    '<span class="sep" aria-hidden="true">|</span>' +
                    '<a href="SignUp_SL.html">Sign up</a>';

    function isCurrent(item) {
        return item.href === page || (item.also && item.also.indexOf(page) !== -1);
    }

    function buildHeader() {
        var links = menu.map(function (item) {
            return '<a href="' + item.href + '"' + (isCurrent(item) ? ' aria-current="page"' : '') + '>' + item.text + '</a>';
        }).join("");

        header.className = "site-header";
        header.innerHTML =
            '<a class="brand" href="https://scratch.mit.edu/" target="_blank" rel="noopener noreferrer">' +
                '<img src="imgs/Scratchlogo.png" width="128" height="40" alt="Scratch (opens the official Scratch website)">' +
            '</a>' +
            '<button class="nav-toggle" type="button" aria-expanded="false" aria-controls="site-nav">' +
                '<span aria-hidden="true">&#9776;</span> Menu' +
            '</button>' +
            '<nav id="site-nav" class="site-nav" aria-label="Main">' +
                '<span id="user-status">' + loggedOut + '</span>' +
                links +
                '<a class="nav-home" href="SL_HomePage.html"' + (page === "SL_HomePage.html" ? ' aria-current="page"' : '') + '>' +
                    '<img src="imgs/home-icon-4.png" width="26" height="26" alt=""><span class="nav-home-text">Home</span>' +
                '</a>' +
            '</nav>';

        // The Menu button (small screens) opens and closes the menu
        var toggle = header.querySelector(".nav-toggle");
        var nav = header.querySelector("#site-nav");

        function setOpen(open) {
            nav.classList.toggle("is-open", open);
            toggle.setAttribute("aria-expanded", open ? "true" : "false");
        }
        toggle.addEventListener("click", function () {
            setOpen(!nav.classList.contains("is-open"));
        });
        document.addEventListener("keydown", function (event) {
            if (event.key === "Escape" && nav.classList.contains("is-open")) {
                setOpen(false);
                toggle.focus();
            }
        });
    }

    function buildFooter() {
        footer.className = "site-footer";
        footer.innerHTML =
            '<p>&copy; ' + new Date().getFullYear() + ' Scratch Learn</p>' +
            '<img src="imgs/scratch-cat.png" width="57" height="30" alt="">'; // decorative
    }

    // "Skip to content" link for keyboard and screen-reader users
    function addSkipLink() {
        var skip = document.createElement("a");
        skip.className = "skip-link";
        skip.href = "#main";
        skip.textContent = "Skip to main content";
        document.body.insertBefore(skip, document.body.firstChild);
    }

    addSkipLink();
    if (header) { buildHeader(); }
    if (footer) { buildFooter(); }
})();
