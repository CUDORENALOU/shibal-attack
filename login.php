<?php
session_start();

// Get user's IP address
$user_ip = $_SERVER['REMOTE_ADDR'];

// Initialize failed attempts and lockout time
if (!isset($_SESSION['failed_attempts'])) {
    $_SESSION['failed_attempts'] = [];
}
if (!isset($_SESSION['lockout_time'])) {
    $_SESSION['lockout_time'] = [];
}

// Check if the IP is in lockout mode
$remaining_time = 0;
if (isset($_SESSION['lockout_time'][$user_ip])) {
    $remaining_time = $_SESSION['lockout_time'][$user_ip] - time();
    
    if ($remaining_time <= 0) {
        unset($_SESSION['lockout_time'][$user_ip]); // Remove lockout after time expires
        $_SESSION['failed_attempts'][$user_ip] = 0; // Reset failed attempts
        $remaining_time = 0;
    }
}


$correct_username = "RENALOU";
$correct_password = "CUDORENA";

// Handle login attempt
$notification = "";
if ($_SERVER['REQUEST_METHOD'] == "POST") {
    if ($remaining_time > 0) {
        $notification = "🚫 Locked! Please wait $remaining_time seconds.";
    } else {
        $username = $_POST['username'];
        $password = $_POST['password'];

        if ($username === $correct_username && $password === $correct_password) {
            $_SESSION['failed_attempts'][$user_ip] = 0; // Reset counter
            
          
            header("Location: https://cudorenalou.github.io/it-28-ecommerce-m-cudo/home.html");
            exit();
        } else {
            // Track failed attempts
            if (!isset($_SESSION['failed_attempts'][$user_ip])) {
                $_SESSION['failed_attempts'][$user_ip] = 0;
            }
            $_SESSION['failed_attempts'][$user_ip]++;

            // Lock user out after 3 failed attempts
            if ($_SESSION['failed_attempts'][$user_ip] >= 3) {
                $_SESSION['lockout_time'][$user_ip] = time() + 60; // Set 60 sec lockout
                $remaining_time = 60;
                $_SESSION['lockout_message'] = "❌ Too many failed attempts! Locked for 60 seconds.";
            } else {
                $notification = "❌ Login Failed! Attempt " . $_SESSION['failed_attempts'][$user_ip] . "/3";
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background: #fde6e6; 
            height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0;
        }
        .login-container {
            background: white;
            padding: 30px;
            border-radius: 10px;
            box-shadow: 0px 4px 10px rgba(0, 0, 0, 0.2);
            text-align: center;
            width: 350px;
        }
        .login-container h2 {
            margin-bottom: 20px;
            color: black;
            font-weight: bold;
        }
        .input-field {
            width: 100%;
            padding: 10px;
            margin: 10px 0;
            border: 1px solid #ccc;
            border-radius: 5px;
            font-size: 16px;
        }
        .btn {
            width: 100%;
            padding: 10px;
            background: #ff5733; 
            border: none;
            color: white;
            font-size: 18px;
            cursor: pointer;
            border-radius: 5px;
            transition: 0.3s;
        }
        .btn:hover {
            background: #e74c3c;
        }
        .error-message {
            color: red;
            font-size: 14px;
            margin: 10px 0;
        }
    </style>
    <script>
        let lockoutTime = <?php echo $remaining_time; ?>;

        function startTimer() {
            let inputFields = document.querySelectorAll("input, button");
            inputFields.forEach(field => field.disabled = true);

            let countdown = setInterval(() => {
                document.getElementById("timer").innerText = "⏳ Wait " + lockoutTime + " seconds";
                lockoutTime--;

                if (lockoutTime <= 0) {
                    clearInterval(countdown);
                    document.getElementById("timer").innerText = "";
                    document.getElementById("error-message").innerText = ""; // REMOVE LOCKOUT MESSAGE
                    inputFields.forEach(field => field.disabled = false);
                }
            }, 1000);
        }

        if (lockoutTime > 0) {
            window.onload = startTimer;
        }
    </script>
</head>
<body>
    <div class="login-container">
        <h2>Login</h2>

        <?php if (!empty($notification)) { ?>
            <p class="error-message"><?php echo $notification; ?></p>
        <?php } ?>

        <?php if ($remaining_time > 0) { ?>
            <p id="timer">⏳ Wait <?php echo $remaining_time; ?> seconds</p>
            <p id="error-message"><?php echo $_SESSION['lockout_message'] ?? ''; ?></p>
        <?php } else {
            unset($_SESSION['lockout_message']); // Remove message after lockout ends
        } ?>

        <form method="POST">
            <input type="text" name="username" class="input-field" placeholder="Username" required><br>
            <input type="password" name="password" class="input-field" placeholder="Password" required><br>
            <button type="submit" class="btn" <?php echo ($remaining_time > 0) ? 'disabled' : ''; ?>>Login</button>
        </form>
    </div>
</body>
</html>
