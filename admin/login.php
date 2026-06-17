<?php
session_start();

// Simple hardcoded auth for shared hosting basic admin
$admin_user = 'admin';
$admin_pass = 'sscrackers123'; // Change this later

if (isset($_POST['login'])) {
    if ($_POST['username'] === $admin_user && $_POST['password'] === $admin_pass) {
        $_SESSION['logged_in'] = true;
        header("Location: index.php");
        exit;
    } else {
        $error = "Invalid credentials";
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Login - SS Crackers</title>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700;800&family=Baloo+2:wght@600;700;800&display=swap" rel="stylesheet">
    <style>
        :root {
            --primary: #FF4500;
            --primary-dark: #CC3700;
            --secondary: #FF9A00;
            --accent: #FFD600;
            --white: #ffffff;
            --off-white: #FFFBF0;
            --light-gray: #FFF5E0;
            --medium-gray: #FFE0B2;
            --text-dark: #7A2800;
            --text-medium: #5C2E00;
            --text-light: #A0622A;
            --font-main: 'Poppins', sans-serif;
            --font-display: 'Baloo 2', cursive;
            --radius-xl: 32px;
            --radius-md: 14px;
        }
        body { 
            font-family: var(--font-main); 
            background: var(--off-white); 
            display: flex; 
            justify-content: center; 
            align-items: center; 
            height: 100vh; 
            margin: 0; 
            color: var(--text-dark);
        }
        .login-box { 
            background: var(--white); 
            padding: 40px; 
            border-radius: var(--radius-md); 
            box-shadow: 0 8px 40px rgba(255,69,0,0.15); 
            width: 100%; 
            max-width: 380px; 
            text-align: center;
            border: 1px solid var(--medium-gray);
        }
        .login-box h2 {
            font-family: var(--font-display);
            margin-top: 0;
            font-weight: 800;
            font-size: 28px;
            margin-bottom: 5px;
            background: linear-gradient(135deg, var(--primary), var(--secondary));
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
        }
        .subtitle {
            color: var(--text-medium);
            margin-bottom: 30px;
            font-size: 14px;
            font-weight: 500;
        }
        .input-group {
            position: relative;
            margin-bottom: 20px;
            text-align: left;
        }
        .input-group i.icon {
            position: absolute;
            left: 14px;
            top: 50%;
            transform: translateY(-50%);
            color: var(--text-light);
        }
        input { 
            font-family: var(--font-main);
            width: 100%; 
            padding: 14px 14px 14px 40px; 
            box-sizing: border-box; 
            background: var(--light-gray);
            border: 2px solid var(--medium-gray); 
            border-radius: var(--radius-xl); 
            color: var(--text-dark);
            font-size: 14px;
            font-weight: 500;
            transition: all 0.3s ease;
        }
        input:focus {
            outline: none;
            border-color: var(--primary);
        }
        .toggle-password {
            position: absolute;
            right: 14px;
            top: 50%;
            transform: translateY(-50%);
            color: var(--text-light);
            cursor: pointer;
            transition: color 0.3s;
        }
        .toggle-password:hover { color: var(--primary); }
        button { 
            font-family: var(--font-main);
            width: 100%; 
            padding: 14px; 
            background: linear-gradient(135deg, var(--primary), var(--secondary));
            color: white; 
            border: none; 
            border-radius: var(--radius-xl); 
            cursor: pointer; 
            font-size: 16px; 
            font-weight: 600;
            transition: transform 0.2s, box-shadow 0.2s;
            margin-top: 10px;
            box-shadow: 0 4px 15px rgba(255,69,0,0.3);
        }
        button:hover { 
            transform: translateY(-2px);
            background: linear-gradient(135deg, var(--primary-dark), var(--secondary-dark));
            box-shadow: 0 8px 25px rgba(255,69,0,0.4);
        }
        .error { 
            background: #ffebe6;
            color: #d32f2f; 
            padding: 12px;
            border-radius: 8px;
            margin-bottom: 20px; 
            font-size: 14px;
            border: 1px solid #ffcdd2;
        }
    </style>
</head>
<body>
    <div class="login-box">
        <h2>SS Crackers</h2>
        <div class="subtitle">Secure Admin Portal</div>
        
        <?php if(isset($error)) echo "<div class='error'><i class='fas fa-exclamation-circle'></i> $error</div>"; ?>
        
        <form method="POST">
            <div class="input-group">
                <i class="fas fa-user icon"></i>
                <input type="text" name="username" placeholder="Username" required autocomplete="off">
            </div>
            
            <div class="input-group">
                <i class="fas fa-lock icon"></i>
                <input type="password" name="password" id="pwd" placeholder="Password" required>
                <i class="fas fa-eye toggle-password" id="togglePwd" onclick="togglePassword()"></i>
            </div>
            
            <button type="submit" name="login">Sign In <i class="fas fa-arrow-right" style="margin-left:8px"></i></button>
        </form>
    </div>

    <script>
        function togglePassword() {
            const pwd = document.getElementById('pwd');
            const icon = document.getElementById('togglePwd');
            if (pwd.type === 'password') {
                pwd.type = 'text';
                icon.classList.remove('fa-eye');
                icon.classList.add('fa-eye-slash');
            } else {
                pwd.type = 'password';
                icon.classList.remove('fa-eye-slash');
                icon.classList.add('fa-eye');
            }
        }
    </script>
</body>
</html>
