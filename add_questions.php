<?php
session_start();
require 'db_connection.php';

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

$quiz_id = $_GET['quiz_id'];

// Count existing questions for this quiz
$stmt = $conn->prepare("SELECT COUNT(*) as question_count FROM questions WHERE quiz_id = ?");
$stmt->execute([$quiz_id]);
$question_count = $stmt->fetch(PDO::FETCH_ASSOC)['question_count'];

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $question = $_POST['question'];
    $options = $_POST['options']; // Array of options
    $correct_answer_index = $_POST['correct_answer']; // Index of correct answer
    $correct_answer = $options[$correct_answer_index]; // Get the actual text of correct answer
    $options_json = json_encode($options);
    
    $stmt = $conn->prepare("INSERT INTO questions (quiz_id, question_text, options, correct_answer) VALUES (?, ?, ?, ?)");
    $stmt->execute([$quiz_id, $question, $options_json, $correct_answer]);
    
    // Redirect to refresh the page and update the question count
    header("Location: add_questions.php?quiz_id=" . $quiz_id);
    exit;
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Add Questions</title>
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
        .question-count {
            text-align: center;
            color: #6a0dad; /* Purple */
            font-size: 1.2em;
            margin-bottom: 20px;
        }
        form {
            display: flex;
            flex-direction: column;
            gap: 15px;
            max-width: 400px;
            margin: 0 auto;
        }
        label {
            color: #333;
            font-weight: bold;
        }
        textarea,
        input[type="text"],
        select {
            width: 100%;
            padding: 8px;
            border: 1px solid #d8b4fe; /* Light purple border */
            border-radius: 5px;
            box-sizing: border-box;
            font-family: Arial, sans-serif;
        }
        textarea {
            height: 100px; /* Adjust height as needed */
            resize: vertical; /* Allow vertical resizing only */
        }
        textarea:focus,
        input[type="text"]:focus,
        select:focus {
            border-color: #6a0dad; /* Purple on focus */
            outline: none;
        }
        button {
            background-color: #6a0dad; /* Purple */
            color: white;
            padding: 10px;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            font-size: 1em;
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
        <h1>Add Questions to Quiz</h1>
        
        <div class="question-count">
            Questions Added: <?php echo $question_count; ?>
        </div>
        
        <form method="POST" id="questionForm">
            <label>Question: 
                <textarea name="question" required></textarea>
            </label>
            <label>Option 1: <input type="text" name="options[]" required oninput="updateCorrectAnswerOptions()"></label>
            <label>Option 2: <input type="text" name="options[]" required oninput="updateCorrectAnswerOptions()"></label>
            <label>Option 3: <input type="text" name="options[]" required oninput="updateCorrectAnswerOptions()"></label>
            <label>Option 4: <input type="text" name="options[]" required oninput="updateCorrectAnswerOptions()"></label>
            <label>Correct Answer: 
                <select name="correct_answer" id="correctAnswer" required>
                    <option value="">Select correct answer</option>
                </select>
            </label>
            <button type="submit">Add Question</button>
        </form>

        <div class="back-link">
            <a href="index.php">Finish Adding Questions</a>
        </div>
    </div>

    <script>
    function updateCorrectAnswerOptions() {
        const options = document.querySelectorAll('input[name="options[]"]');
        const select = document.getElementById('correctAnswer');
        
        // Clear existing options except the first one
        while (select.options.length > 1) {
            select.remove(1);
        }
        
        // Add new options based on input values
        options.forEach((input, index) => {
            if (input.value.trim() !== '') {
                const option = document.createElement('option');
                option.value = index;
                option.text = input.value;
                select.appendChild(option);
            }
        });
    }
    </script>
</body>
</html>