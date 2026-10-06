[README.md](https://github.com/user-attachments/files/33090030/README.md)
# Scratch Learn

Copy link to site:
https://scratchlearn.rf.gd/SL_HomePage.html

Scratch Learn is a small educational website that teaches basic 
STEM topics (multiplication & division, geometry and physics) through short videos
made with [Scratch](https://scratch.mit.edu/). Visitors can create an account,
take quizzes, read the FAQ, leave feedback and see the list of registered learners.

It was built as a university project at the Lebanese American University (LAU) with **HTML, CSS, JavaScript/jQuery (AJAX), PHP and MySQL**.


## Live demo

> **https://scratchlearn.rf.gd/SL_HomePage.html**
> *(hosted on the free InfinityFree plan, so the first load can take a few seconds)*

You can create your own account on the Sign Up page, or use the demo account:

| Email              | Password    |
|--------------------|-------------|
| `demo@example.com` | `Demo1234!` |

> Please open the site through the link above (or through `http://localhost/...` when running it locally). 
Double-clicking an `.html` file only shows the static content: 
PHP and MySQL need a web server, so login, sign up, feedback and the leaderboard cannot work that way.
⌄
⌄
⌄
⌄
⌄
⌄
⌄
⌄
⌄
⌄
⌄
⌄
⌄
-----------------------------------------------------------------
## Features

- **Lessons:** multiplication & division, geometry and physics pages, each with an explanation and a Scratch-made video.
- **Quizzes:** a Math quiz and a Physics quiz with instant scoring.
- **User accounts:** sign up, log in and log out through AJAX (no page reload on errors). Passwords 
are hashed with `password_hash()`; the header greets the logged-in user and shows a red *Log out* link.
- **Feedback page:** comments are saved in the database, displayed instantly, and shown permanently. Includes a FAQ accordion.
- **Leaderboards:** a page listing the registered users, loaded from the database through a JSON endpoint.
- **Discover Scratch:** embedded YouTube tutorials for learning Scratch.
- **About page:** presentation of the platform and its developers.

## Tech stack

| Layer      | Technology |
|------------|------------|
| Front end  | HTML5, CSS3, JavaScript, jQuery 3.6 (AJAX) |
| Back end   | PHP 8 (PDO and MySQLi, sessions, prepared statements) |
| Database   | MySQL / MariaDB |
| Local dev  | XAMPP (Apache + MySQL) |
| Hosting    | InfinityFree (free PHP + MySQL hosting) |

## Project structure

```
README.md
.gitignore
ScratchLearn/                  <- the website (this folder is what goes in htdocs)
├── index.html                 redirects to SL_HomePage.html
├── SL_HomePage.html           home page
├── About_SL.html              about page
├── Quizes.html                quiz menu
├── MathQuiz.html / physQuiz.html
├── Muldiv.html / geometry.html / physics.html    lessons
├── learnonyourown.html        Discover Scratch (YouTube tutorials)
├── feed.html                  feedback + FAQ
├── Leaderboards.html          registered users
├── LogIn_SL.html / SignUp_SL.html
├── config.sample.php          database settings template (copy to config.php)
├── config.php                 YOUR database settings (git-ignored, not in the repo)
├── db.php                     PDO connection used by login/register/feedback
├── session_info.php           returns the "Welcome / Log in" header content
├── login.php, logout.php, register.php
├── submit_feedback.php, load_feedback.php
├── fetch_leaderboard.php      JSON list of users
├── get_quiz.php, check_quiz.php
├── css/css.css
├── js/script.js               quiz checking + helpers
├── js/session.js              fills the login/logout area of the header
├── imgs/  videos/
└── database/scratch_learn_db.sql    tables + quiz content + one demo account
```

## Database

The database is called `scratch_learn_db` and has five tables:

| Table       | Purpose |
|-------------|---------|
| `users`     | `id`, `email` (unique), `username` (unique), `password` (bcrypt hash) |
| `quizzes`   | one row per quiz (Math Quiz, Physics Quiz) |
| `questions` | quiz questions, linked to `quizzes` by `quiz_id` (foreign key) |
| `answers`   | answer options, linked to `questions` by `question_id` (foreign key), `is_correct` marks the right one |
| `feedback`  | name, message and timestamp of each comment |

## Authors

Hani Srour and Ali Ghandour, students at the Lebanese American University.
