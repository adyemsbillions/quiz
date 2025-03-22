<?php
session_start();
require 'db_connection.php';

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

$quiz_id = $_GET['id'];

// Get quiz details
$stmt = $conn->prepare("SELECT * FROM quizzes WHERE id = ?");
$stmt->execute([$quiz_id]);
$quiz = $stmt->fetch();

if (!$quiz) {
    header("Location: index.php");
    exit;
}

// Get total number of questions for this quiz (for max value)
$stmt = $conn->prepare("SELECT COUNT(*) as question_count FROM questions WHERE quiz_id = ?");
$stmt->execute([$quiz_id]);
$total_questions = $stmt->fetchColumn();
?>

<!DOCTYPE html>
<html>
<head>
    <title>Start Quiz - <?php echo htmlspecialchars($quiz['title']); ?></title>
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
        }
        form {
            display: flex;
            flex-direction: column;
            gap: 15px;
            max-width: 400px;
            margin: 20px auto;
            text-align: center;
        }
        label {
            color: #333;
            font-weight: bold;
        }
        input[type="number"] {
            width: 100px;
            padding: 8px;
            border: 1px solid #d8b4fe; /* Light purple border */
            border-radius: 5px;
            box-sizing: border-box;
            margin: 0 auto;
            display: block;
        }
        input[type="number"]:focus {
            border-color: #6a0dad; /* Purple on focus */
            outline: none;
        }
        button {
            background-color: #6a0dad; /* Purple */
            color: white;
            padding: 10px 20px;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            font-size: 1em;
            margin: 0 auto;
            display: block;
        }
        button:hover {
            background-color: #8a2be2; /* Lighter purple on hover */
        }
        .back-link {
            text-align: center;
            margin-top: 20px;
        }
        .back-link a {
            background-color: #6a0dad; /* Purple */
            color: white;
            padding: 10px 20px;
            text-decoration: none;
            border-radius: 5px;
        }
        .back-link a:hover {
            background-color: #8a2be2; /* Lighter purple on hover */
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>Start Quiz: <?php echo htmlspecialchars($quiz['title']); ?></h1>
        
        <form method="POST" action="take_quiz.php?id=<?php echo $quiz_id; ?>">
            <label>
                Number of Questions (Max: <?php echo $total_questions; ?>):
                <input type="number" name="question_count" min="1" max="<?php echo $total_questions; ?>" value="<?php echo min(5, $total_questions); ?>" required>
            </label>
            <button type="submit">Start Quiz</button>
        </form>

        <div class="back-link">
            <a href="index.php">Back to Quizzes</a>
        </div>
    </div>
</body>
</html>