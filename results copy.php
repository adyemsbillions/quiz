<?php
session_start();
require 'db_connection.php';

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

$quiz_id = $_GET['quiz_id'];

$stmt = $conn->prepare("SELECT q.*, ur.selected_answer 
    FROM questions q 
    LEFT JOIN user_responses ur ON q.id = ur.question_id 
    WHERE q.quiz_id = ? AND ur.user_id = ?");
$stmt->execute([$quiz_id, $_SESSION['user_id']]);
$results = $stmt->fetchAll();

// Calculate summary
$total_questions = count($results);
$correct_answers = 0;
foreach ($results as $result) {
    if ($result['selected_answer'] === $result['correct_answer']) {
        $correct_answers++;
    }
}
$score_percentage = $total_questions > 0 ? ($correct_answers / $total_questions) * 100 : 0;
?>

<!DOCTYPE html>
<html>
<head>
    <title>Quiz Results</title>
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
        .summary {
            background-color: #f3e5f5; /* Light purple background */
            padding: 15px;
            border-radius: 5px;
            margin-bottom: 20px;
            color: #333;
        }
        .summary p {
            margin: 5px 0;
            font-size: 1.1em;
        }
        .question-block {
            border: 1px solid #d8b4fe; /* Light purple border */
            padding: 15px;
            margin-bottom: 15px;
            border-radius: 5px;
        }
        .question-block p {
            margin: 5px 0;
        }
        .correct {
            color: #2ecc71; /* Green for correct */
            font-weight: bold;
        }
        .wrong {
            color: #e74c3c; /* Red for wrong */
            font-weight: bold;
        }
        a {
            display: inline-block;
            background-color: #6a0dad; /* Purple */
            color: white;
            padding: 10px 20px;
            text-decoration: none;
            border-radius: 5px;
            margin-top: 20px;
        }
        a:hover {
            background-color: #8a2be2; /* Lighter purple on hover */
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>Quiz Results</h1>
        
        <!-- Summary Section -->
        <div class="summary">
            <p>Total Questions: <?php echo $total_questions; ?></p>
            <p>Correct Answers: <?php echo $correct_answers; ?></p>
            <p>Score: <?php echo number_format($score_percentage, 2); ?>%</p>
        </div>

        <!-- Detailed Results -->
        <?php foreach($results as $result): ?>
            <div class="question-block">
                <p><strong>Question:</strong> <?php echo htmlspecialchars($result['question_text']); ?></p>
                <p><strong>Your Answer:</strong> <?php echo htmlspecialchars($result['selected_answer']); ?></p>
                <p><strong>Correct Answer:</strong> <?php echo htmlspecialchars($result['correct_answer']); ?></p>
                <p class="<?php echo $result['selected_answer'] === $result['correct_answer'] ? 'correct' : 'wrong'; ?>">
                    <?php echo $result['selected_answer'] === $result['correct_answer'] ? 'Correct!' : 'Wrong'; ?>
                </p>
            </div>
        <?php endforeach; ?>
        
        <a href="index.php">Back to Quizzes</a>
    </div>
</body>
</html>