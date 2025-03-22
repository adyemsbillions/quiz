<?php
session_start();
require 'db_connection.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $username = $_POST['username'];
    $password = password_hash($_POST['password'], PASSWORD_DEFAULT);
    
    $stmt = $conn->prepare("INSERT INTO users (username, password) VALUES (?, ?)");
    $stmt->execute([$username, $password]);
    $user_id = $conn->lastInsertId();
    
    $_SESSION['user_id'] = $user_id;
    header("Location: index.php");
    exit;
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Register</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #f5f5f5;
            margin: 0;
            padding: 20px;
        }
        .container {
            max-width: 400px; /* Narrower for register form */
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
            margin-bottom: 1.5rem;
        }
        form {
            display: flex;
            flex-direction: column;
            gap: 15px;
        }
        label {
            color: #333;
            font-weight: bold;
            font-size: 1rem;
        }
        input[type="text"],
        input[type="password"] {
            width: 100%;
            padding: 10px;
            border: 1px solid #d8b4fe; /* Light purple border */
            border-radius: 5px;
            font-size: 1rem;
            box-sizing: border-box;
        }
        input[type="text"]:focus,
        input[type="password"]:focus {
            border-color: #6a0dad; /* Purple on focus */
            outline: none;
        }
        button {
            background-color: #6a0dad; /* Purple */
            color: white;
            padding: 10px;
            border: none;
            border-radius: 5px;
            font-size: 1rem;
            cursor: pointer;
            width: 120px; /* Matches Login and Take Quiz button size */
            margin: 0 auto; /* Centers button */
        }
        button:hover {
            background-color: #8a2be2; /* Lighter purple on hover */
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
            input[type="text"],
            input[type="password"] {
                font-size: 0.9rem;
                padding: 8px;
            }
            button {
                padding: 8px;
                font-size: 0.9rem;
                width: 100%; /* Full width on small screens */
            }
        }

        @media (max-width: 400px) {
            h1 {
                font-size: 1.2rem;
            }
            input[type="text"],
            input[type="password"] {
                font-size: 0.8rem;
            }
            button {
                font-size: 0.8rem;
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>Register</h1>
        
        <form method="POST">
            <label>Username: <input type="text" name="username" required></label>
            <label>Password: <input type="password" name="password" required></label>
            <button type="submit">Register</button>
        </form>
    </div>
</body>
</html>