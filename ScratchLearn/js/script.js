// Function to check the Math Quiz
function checkMathQuiz() {
    const correctAnswers = ["12", "4", "10", "3"];
    checkQuiz(correctAnswers, "mathResult");
}

// Function to check the Physics Quiz
function checkPhysicsQuiz() {
    const correctAnswers = ["M", "bounce", "5", "9"];
    checkQuiz(correctAnswers, "physicsResult");
}

// General function to check quiz answers
function checkQuiz(correctAnswers, resultId) {
    let score = 0;
    let userAnswers = correctAnswers.map((_, index) => 
        document.querySelector(`input[name="q${index + 1}"]:checked`)?.value
    );

    userAnswers.forEach((answer, index) => {
        if (answer === correctAnswers[index]) {
            score++;
        }
    });

    document.getElementById(resultId).innerText = `You got ${score} out of ${correctAnswers.length} correct!`;
}

// Function to toggle answer visibility
function toggleAnswer(id) {
    var answer = document.getElementById(id);
    answer.style.display = answer.style.display === 'none' ? 'block' : 'none';
}

// Function to handle feedback submission
function submitFeedback(event) {
    event.preventDefault();
    let name = document.getElementById("name").value;//name
    let feedback = document.getElementById("feedback").value;//comment feedback

    if (name && feedback) {//adding the feed back
        let commentSection = document.getElementById("comments-section");
        let comment = document.createElement("div");
        comment.classList.add("comment");
        comment.innerHTML = `<strong>${name}:</strong> ${feedback}`;
        commentSection.appendChild(comment);

        // Clear input fields after submission
        document.getElementById("name").value = "";
        document.getElementById("feedback").value = "";
    }
}
