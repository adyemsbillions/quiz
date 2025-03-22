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

// Handle question count from form submission
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['question_count'])) {
    $question_count = (int)$_POST['question_count'];
    $_SESSION['quiz_question_count'][$quiz_id] = $question_count;
}

// Get all questions for the quiz
$stmt = $conn->prepare("SELECT * FROM questions WHERE quiz_id = ?");
$stmt->execute([$quiz_id]);
$all_questions = $stmt->fetchAll();

// Check if this is a new quiz attempt (no start time or question subset set)
if (!isset($_SESSION['quiz_start_time'][$quiz_id]) || !isset($_SESSION['quiz_questions'][$quiz_id])) {
    // Clear previous responses
    $stmt = $conn->prepare("DELETE FROM user_responses WHERE user_id = ? AND quiz_id = ?");
    $stmt->execute([$_SESSION['user_id'], $quiz_id]);
    
    // Set quiz start time
    $_SESSION['quiz_start_time'][$quiz_id] = time();
    
    // Shuffle and select questions
    $question_count = min($_SESSION['quiz_question_count'][$quiz_id] ?? count($all_questions), count($all_questions));
    shuffle($all_questions);
    $_SESSION['quiz_questions'][$quiz_id] = array_slice($all_questions, 0, $question_count);
}

// Use the shuffled subset of questions
$questions = $_SESSION['quiz_questions'][$quiz_id];
$total_questions = count($questions);

// Determine current question (page) - defaults to 1
$current_page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
if ($current_page < 1 || $current_page > $total_questions) {
    $current_page = 1;
}

// Get the current question
$current_question = $questions[$current_page - 1] ?? null;

// Calculate remaining time
$total_time_seconds = $quiz['time_limit'] * 60;
$elapsed_time = time() - $_SESSION['quiz_start_time'][$quiz_id];
$time_left = max(0, $total_time_seconds - $elapsed_time);

if ($_SERVER['REQUEST_METHOD'] == 'POST' && !isset($_POST['question_count'])) {
    if (isset($_POST['next']) || isset($_POST['submit'])) {
        $question_id = $_POST['question_id'];
        $answer = $_POST['answer'];
        
        // Save or update the answer
        $stmt = $conn->prepare("INSERT INTO user_responses (user_id, quiz_id, question_id, selected_answer) VALUES (?, ?, ?, ?) ON DUPLICATE KEY UPDATE selected_answer = ?");
        $stmt->execute([$_SESSION['user_id'], $quiz_id, $question_id, $answer, $answer]);
        
        // If last question or time is up and submitting, store results and clear everything
        if ((isset($_POST['submit']) && $current_page >= $total_questions) || $time_left <= 0) {
            // Store results in session
            $stmt = $conn->prepare("SELECT q.*, ur.selected_answer 
                FROM questions q 
                LEFT JOIN user_responses ur ON q.id = ur.question_id 
                WHERE q.quiz_id = ? AND ur.user_id = ?");
            $stmt->execute([$quiz_id, $_SESSION['user_id']]);
            $_SESSION['quiz_results'][$quiz_id] = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            // Clear session data and responses
            unset($_SESSION['quiz_start_time'][$quiz_id]);
            unset($_SESSION['quiz_questions'][$quiz_id]);
            unset($_SESSION['quiz_question_count'][$quiz_id]);
            $stmt = $conn->prepare("DELETE FROM user_responses WHERE user_id = ? AND quiz_id = ?");
            $stmt->execute([$_SESSION['user_id'], $quiz_id]);
            
            header("Location: results.php?quiz_id=" . $quiz_id);
        } else {
            header("Location: take_quiz.php?id=" . $quiz_id . "&page=" . ($current_page + 1));
        }
    } elseif (isset($_POST['previous'])) {
        header("Location: take_quiz.php?id=" . $quiz_id . "&page=" . ($current_page - 1));
    }
    exit;
}

// If time is already up, store results and clear everything
if ($time_left <= 0) {
    $stmt = $conn->prepare("SELECT q.*, ur.selected_answer 
        FROM questions q 
        LEFT JOIN user_responses ur ON q.id = ur.question_id 
        WHERE q.quiz_id = ? AND ur.user_id = ?");
    $stmt->execute([$quiz_id, $_SESSION['user_id']]);
    $_SESSION['quiz_results'][$quiz_id] = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    unset($_SESSION['quiz_start_time'][$quiz_id]);
    unset($_SESSION['quiz_questions'][$quiz_id]);
    unset($_SESSION['quiz_question_count'][$quiz_id]);
    $stmt = $conn->prepare("DELETE FROM user_responses WHERE user_id = ? AND quiz_id = ?");
    $stmt->execute([$_SESSION['user_id'], $quiz_id]);
    header("Location: results.php?quiz_id=" . $quiz_id);
    exit;
}

// Load previous answer if available (only for current session)
$stmt = $conn->prepare("SELECT selected_answer FROM user_responses WHERE user_id = ? AND quiz_id = ? AND question_id = ?");
$stmt->execute([$_SESSION['user_id'], $quiz_id, $current_question['id']]);
$previous_answer = $stmt->fetchColumn();
?>

<!DOCTYPE html>
<html>
<head>
    <title><?php echo htmlspecialchars($quiz['title']); ?></title>
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
        #timer {
            text-align: center;
            color: #6a0dad; /* Purple */
            font-size: 1.2em;
            margin-bottom: 20px;
        }
        .question-count {
            text-align: center;
            color: #333;
            margin-bottom: 20px;
        }
        .question-block {
            border: 1px solid #d8b4fe; /* Light purple border */
            padding: 15px;
            border-radius: 5px;
            margin-bottom: 20px;
        }
        .question-block p {
            margin: 0 0 15px 0;
            color: #333;
        }
        .question-block label {
            display: block;
            margin: 10px 0;
            color: #333;
        }
        .question-block input[type="radio"] {
            margin-right: 10px;
        }
        .button-group {
            display: flex;
            justify-content: center;
            gap: 15px;
        }
        button {
            background-color: #6a0dad; /* Purple */
            color: white;
            padding: 10px 20px;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            font-size: 1em;
        }
        button:hover {
            background-color: #8a2be2; /* Lighter purple on hover */
        }
        button:disabled {
            background-color: #cccccc;
            cursor: not-allowed;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1><?php echo htmlspecialchars($quiz['title']); ?></h1>
        <div id="timer">Time Left: <?php echo floor($time_left / 60); ?>:<?php echo sprintf("%02d", $time_left % 60); ?></div>
        <div class="question-count">
            Question <?php echo $current_page; ?> of <?php echo $total_questions; ?>
        </div>

        <?php if ($current_question): ?>
            <form method="POST" id="quizForm">
                <div class="question-block">
                    <p><?php echo htmlspecialchars($current_question['question_text']); ?></p>
                    <?php $options = json_decode($current_question['options']); ?>
                    <?php foreach ($options as $option): ?>
                        <label>
                            <input type="radio" name="answer" value="<?php echo htmlspecialchars($option); ?>" 
                                <?php echo $previous_answer === $option ? 'checked' : ''; ?> required>
                            <?php echo htmlspecialchars($option); ?>
                        </label>
                    <?php endforeach; ?>
                </div>
                <input type="hidden" name="question_id" value="<?php echo $current_question['id']; ?>">
                <div class="button-group">
                    <button type="submit" name="previous" <?php echo $current_page == 1 ? 'disabled' : ''; ?>>Previous</button>
                    <?php if ($current_page == $total_questions): ?>
                        <button type="submit" name="submit">Submit Quiz</button>
                    <?php else: ?>
                        <button type="submit" name="next">Next Question</button>
                    <?php endif; ?>
                </div>
            </form>
        <?php else: ?>
            <p>No questions available.</p>
        <?php endif; ?>
    </div>

    <script>
    let timeLeft = <?php echo $time_left; ?>;
    let timer = setInterval(() => {
        let minutes = Math.floor(timeLeft / 60);
        let seconds = timeLeft % 60;
        document.getElementById('timer').textContent = `Time Left: ${minutes}:${seconds < 10 ? '0' + seconds : seconds}`;
        timeLeft--;
        if (timeLeft < 0) {
            clearInterval(timer);
            document.getElementById('quizForm').submit();
        }
    }, 1000);
    </script>
</body>
</html>