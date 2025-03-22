<?php
session_start();
require 'db_connection.php';

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $title = $_POST['title'];
    $time_limit = $_POST['time_limit'];
    $creator_id = $_SESSION['user_id'];
    
    try {
        $stmt = $conn->prepare("INSERT INTO quizzes (title, creator_id, time_limit) VALUES (?, ?, ?)");
        $stmt->execute([$title, $creator_id, $time_limit]);
        $quiz_id = $conn->lastInsertId();
        
        header("Location: add_questions.php?quiz_id=" . $quiz_id);
        exit;
    } catch (PDOException $e) {
        echo "Error creating quiz: " . $e->getMessage();
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Create Quiz</title>
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
            margin: 0 auto;
        }
        label {
            color: #333;
            font-weight: bold;
        }
        input[type="text"],
        input[type="number"] {
            width: 100%;
            padding: 8px;
            border: 1px solid #d8b4fe; /* Light purple border */
            border-radius: 5px;
            box-sizing: border-box;
        }
        input[type="text"]:focus,
        input[type="number"]:focus {
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
        .error {
            color: #e74c3c; /* Red for errors */
            text-align: center;
            margin-top: 10px;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>Create New Quiz</h1>
        
        <form method="POST">
            <label>Title: <input type="text" name="title" required></label>
            <label>Time Limit (minutes): <input type="number" name="time_limit" min="1" required></label>
            <button type="submit">Create Quiz</button>
        </form>

        <?php if (isset($e)): ?>
            <p class="error">Error creating quiz: <?php echo htmlspecialchars($e->getMessage()); ?></p>
        <?php endif; ?>

        <div class="back-link">
            <a href="index.php">Back to Quizzes</a>
        </div>
    </div>
</body>
</html>