<?php
session_start();
require 'db_connection.php';

$stmt = $conn->query("SELECT * FROM quizzes");
$quizzes = $stmt->fetchAll();

// Prepare quiz data for JavaScript
$quiz_titles = array_map(function($quiz) {
    return htmlspecialchars($quiz['title']);
}, $quizzes);
$quiz_ids = array_column($quizzes, 'id');
?>

<!DOCTYPE html>
<html>
<head>
    <title>Quiz List</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #f5f5f5;
            margin: 0;
            padding: 20px;
        }
        .container {
            max-width: 800px;
            margin: 0 auto;
            background-color: white;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 0 10px rgba(0,0,0,0.1);
        }
        h1 {
            color: #6a0dad; /* Purple */
            text-align: center;
            font-size: 2rem;
            margin-bottom: 1rem;
        }
        .auth-links {
            text-align: right;
            margin-bottom: 20px;
        }
        .auth-links a {
            color: #6a0dad; /* Purple */
            text-decoration: none;
            margin-left: 10px;
            font-size: 1rem;
        }
        .auth-links a:hover {
            color: #8a2be2; /* Lighter purple on hover */
        }
        .search-container {
            margin-bottom: 20px;
            text-align: center;
            position: relative;
        }
        input[type="text"] {
            width: 80%;
            max-width: 400px;
            padding: 10px;
            border: 1px solid #d8b4fe;
            border-radius: 5px;
            font-size: 1rem;
            box-sizing: border-box;
        }
        input[type="text"]:focus {
            border-color: #6a0dad;
            outline: none;
        }
        .quiz-list {
            margin-bottom: 20px;
        }
        .quiz-item {
            border: 1px solid #d8b4fe; /* Light purple border */
            padding: 15px;
            margin-bottom: 15px;
            border-radius: 5px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            flex-wrap: wrap;
        }
        .quiz-item h3 {
            margin: 0 0 10px 0;
            color: #333;
            font-size: 1.2rem;
            flex: 1;
            min-width: 0; /* Prevents overflow */
        }
        .quiz-item a {
            background-color: #6a0dad; /* Purple */
            color: white;
            padding: 8px 15px;
            text-decoration: none;
            border-radius: 5px;
            text-align: center;
            width: 120px; /* Fixed width for consistency */
            box-sizing: border-box;
            font-size: 1rem;
        }
        .quiz-item a:hover {
            background-color: #8a2be2; /* Lighter purple on hover */
        }
        .ad-space {
            background-color: #f3e5f5; /* Light purple background */
            padding: 20px;
            border-radius: 5px;
            text-align: center;
            margin-top: 20px;
            color: #666;
        }
        .ad-space p {
            margin: 0;
            font-style: italic;
            font-size: 1rem;
        }
        #suggestions {
            position: absolute;
            width: 80%;
            max-width: 400px;
            background-color: white;
            border: 1px solid #d8b4fe;
            border-radius: 5px;
            max-height: 200px;
            overflow-y: auto;
            display: none;
            left: 50%;
            transform: translateX(-50%);
            z-index: 10;
        }
        #suggestions .quiz-item {
            display: block;
            margin: 0;
            padding: 10px;
            border: none;
            border-bottom: 1px solid #d8b4fe;
        }
        #suggestions .quiz-item:last-child {
            border-bottom: none;
        }
        #suggestions .quiz-item h3 {
            margin: 0;
        }
        #suggestions .quiz-item a {
            width: 100%;
            text-align: center;
        }
        #suggestions .quiz-item:hover {
            background-color: #f3e5f5;
        }

        /* Responsive Adjustments */
        @media (max-width: 600px) {
            body {
                padding: 10px;
            }
            .container {
                padding: 15px;
            }
            h1 {
                font-size: 1.5rem;
            }
            .auth-links {
                text-align: center;
                margin-bottom: 15px;
            }
            .auth-links a {
                display: inline-block;
                margin: 5px;
            }
            input[type="text"] {
                width: 90%;
            }
            #suggestions {
                width: 90%;
            }
            .quiz-item {
                flex-direction: column;
                align-items: flex-start;
            }
            .quiz-item h3 {
                margin-bottom: 10px;
            }
            .quiz-item a {
                width: 100%;
                padding: 8px;
            }
            .ad-space {
                padding: 15px;
            }
        }

        @media (max-width: 400px) {
            h1 {
                font-size: 1.2rem;
            }
            input[type="text"] {
                width: 100%;
                font-size: 0.9rem;
            }
            #suggestions {
                width: 100%;
            }
            .quiz-item h3 {
                font-size: 1rem;
            }
            .quiz-item a {
                font-size: 0.9rem;
            }
            .ad-space p {
                font-size: 0.9rem;
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>Available Quizzes</h1>
        
        <!-- Authentication Links and Create Quiz -->
        <div class="auth-links">
            <?php if (isset($_SESSION['user_id'])): ?>
                <a href="create_quiz.php">Create New Quiz</a>
                <a href="logout.php">Logout</a>
            <?php else: ?>
                <p><a href="login.php">Login</a> | <a href="register.php">Register</a></p>
            <?php endif; ?>
        </div>

        <!-- Search Bar -->
        <div class="search-container">
            <input type="text" id="search" placeholder="Search quizzes...">
            <div id="suggestions"></div>
        </div>

        <!-- Quiz List -->
        <div class="quiz-list" id="quiz-list">
            <?php foreach($quizzes as $quiz): ?>
                <div class="quiz-item">
                    <h3><?php echo htmlspecialchars($quiz['title']); ?> (<?php echo htmlspecialchars($quiz['time_limit']); ?> mins)</h3>
                    <a href="quiz_start.php?id=<?php echo $quiz['id']; ?>">Take Quiz</a>
                </div>
            <?php endforeach; ?>
        </div>

        <!-- Ad Space -->
        <div class="ad-space">
            <p>Advertisement Space - Your Ad Could Be Here!</p>
        </div>
    </div>

    <script>
    const quizzes = <?php echo json_encode(array_combine($quiz_ids, $quiz_titles)); ?>;
    const searchInput = document.getElementById('search');
    const suggestionsDiv = document.getElementById('suggestions');
    const quizList = document.getElementById('quiz-list');

    searchInput.addEventListener('input', function() {
        const query = this.value.toLowerCase().trim();
        suggestionsDiv.innerHTML = '';
        quizList.style.display = 'none'; // Hide full list while typing

        if (query.length === 0) {
            suggestionsDiv.style.display = 'none';
            quizList.style.display = 'block';
            return;
        }

        const matches = Object.entries(quizzes).filter(([id, title]) => 
            title.toLowerCase().includes(query)
        );

        if (matches.length > 0) {
            matches.forEach(([id, title]) => {
                const item = document.createElement('div');
                item.className = 'quiz-item';
                item.innerHTML = `
                    <h3>${title}</h3>
                    <a href="quiz_start.php?id=${id}">Take Quiz</a>
                `;
                suggestionsDiv.appendChild(item);
            });
            suggestionsDiv.style.display = 'block';
        } else {
            suggestionsDiv.style.display = 'none';
        }
    });

    // Hide suggestions when clicking outside
    document.addEventListener('click', function(e) {
        if (!searchInput.contains(e.target) && !suggestionsDiv.contains(e.target)) {
            suggestionsDiv.style.display = 'none';
            quizList.style.display = 'block';
        }
    });

    // Show full list when search is cleared
    searchInput.addEventListener('blur', function() {
        if (!this.value) {
            suggestionsDiv.style.display = 'none';
            quizList.style.display = 'block';
        }
    });
    </script>
</body>
</html>