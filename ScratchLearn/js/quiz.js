// Loads a quiz from the database (get_quiz.php), shows it, and sends the answers to the
// server (check_quiz.php) to be marked. The correct answers never reach the browser.
//
// Usage: <form id="quizForm" data-quiz="Math Quiz"> ... </form>   (jQuery and js/csrf.js must be loaded first)
$(document).ready(function () {
    var $form = $("#quizForm");
    var quizName = $form.data("quiz");
    var $status = $("#quiz-status");
    var $questions = $("#quiz-questions");
    var $submit = $("#quiz-submit");
    var $result = $("#quiz-result");
    var questions = [];

    // Text to show when a request fails: the server's own message if it sent one
    function failText(xhr, fallback) {
        if (xhr && xhr.responseJSON && xhr.responseJSON.error) {
            return xhr.responseJSON.error;
        }
        return fallback;
    }

    function showQuestions(list) {
        questions = list;
        $questions.empty();

        list.forEach(function (q, index) {
            // A fieldset with a legend = a labelled group, so screen readers announce the question
            // before each answer option. .text() (never .html()) so nothing from the database can inject markup.
            var $block = $("<fieldset>").addClass("question");
            $("<legend>").text((index + 1) + ". " + q.text).appendTo($block);

            var $options = $("<div>").addClass("options").appendTo($block);
            q.answers.forEach(function (answer) {
                var $label = $("<label>");
                $("<input>").attr({ type: "radio", name: "q" + q.id, value: answer.id }).appendTo($label);
                $label.append(document.createTextNode(answer.text));
                $label.appendTo($options);
            });

            $block.appendTo($questions);
        });

        $status.hide();
        $submit.prop("hidden", false);
    }

    $.getJSON("get_quiz.php", { quiz: quizName })
        .done(function (res) {
            if (res.success && res.questions && res.questions.length) {
                showQuestions(res.questions);
            } else {
                $status.text(res.error || "This quiz has no questions yet.");
            }
        })
        .fail(function (xhr) {
            $status.text(failText(xhr, "The quiz could not be loaded. Please check your connection and reload the page."));
        });

    $form.on("submit", function (e) {
        e.preventDefault();
        $result.empty();

        var answers = {};
        var unanswered = 0;
        questions.forEach(function (q) {
            var chosen = $form.find('input[name="q' + q.id + '"]:checked').val();
            if (chosen === undefined) {
                unanswered++;
            } else {
                answers[q.id] = chosen;
            }
        });

        if (unanswered > 0) {
            $result.text("Please answer all " + questions.length + " questions before submitting.");
            return;
        }

        $submit.prop("disabled", true);

        ScratchLearn.post("check_quiz.php", { quiz: quizName, answers: answers })
            .done(function (res) {
                if (!res.success) {
                    $result.text(res.error || "Your answers could not be checked.");
                    return;
                }

                $result.append(document.createTextNode("You got " + res.score + " out of " + res.total + " correct!"));

                if (!res.logged_in) {
                    $result.append("<br>");
                    $result.append($("<a>").attr("href", "LogIn_SL.html").text("Log in"));
                    $result.append(document.createTextNode(" to save your score on the Leaderboards."));
                } else if (res.best !== null) {
                    $result.append("<br>");
                    $result.append(document.createTextNode("Your best score on this quiz: " + res.best + " out of " + res.total + ". "));
                    $result.append($("<a>").attr("href", "Leaderboards.html").text("See the Leaderboards"));
                }
            })
            .fail(function (xhr) {
                $result.text(failText(xhr, "Your answers could not be sent. Please try again."));
            })
            .always(function () {
                $submit.prop("disabled", false);
            });
    });
});
