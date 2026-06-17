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
    <link href="https://fonts.googleapis.com/css2?family=Nunito:wght@400;600;700;800&family=Playfair+Display:ital,wght@0,600;0,700;0,800;1,600&display=swap" rel="stylesheet">
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        :root {
            --emerald: #006838;
            --emerald-dark: #004d28;
            --gold: #D4AF37;
            --bg-soft: #F4F9F6;
            --white: #ffffff;
            --text-dark: #002814;
            --text-light: #557A68;
            --font-main: 'Nunito', sans-serif;
            --font-display: 'Playfair Display', serif;
        }
        body { 
            font-family: var(--font-main); 
            background: linear-gradient(135deg, var(--bg-soft) 0%, #e0e8e4 100%); 
            display: flex; 
            justify-content: center; 
            align-items: center; 
            height: 100vh; 
            color: var(--text-dark);
            position: relative;
            overflow: hidden;
        }
        /* Background decorative elements */
        .bg-shape {
            position: absolute;
            border-radius: 50%;
            background: linear-gradient(135deg, rgba(0,104,56,0.05), rgba(212,175,55,0.05));
            z-index: 0;
        }
        .shape-1 { width: 400px; height: 400px; top: -100px; left: -100px; }
        .shape-2 { width: 300px; height: 300px; bottom: -50px; right: -50px; }
        
        .login-wrapper {
            position: relative;
            z-index: 1;
            width: 100%;
            max-width: 400px;
            padding: 0 20px;
        }
        .login-box { 
            background: rgba(255, 255, 255, 0.85); 
            backdrop-filter: blur(12px);
            padding: 40px; 
            border-radius: 24px; 
            box-shadow: 0 8px 32px rgba(0, 104, 56, 0.08), 0 1px 0 rgba(212,175,55,0.15); 
            text-align: center;
            border: 1px solid rgba(255,255,255,0.4);
        }
        .logo-icon {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            width: 64px;
            height: 64px;
            background: linear-gradient(135deg, var(--emerald), var(--emerald-dark));
            color: var(--gold);
            border-radius: 16px;
            font-size: 28px;
            margin-bottom: 20px;
            box-shadow: 0 8px 24px rgba(0,104,56,0.2);
        }
        .login-box h2 {
            font-family: var(--font-display);
            margin-top: 0;
            font-weight: 800;
            font-size: 28px;
            margin-bottom: 5px;
            color: var(--emerald);
        }
        .subtitle {
            color: var(--text-light);
            margin-bottom: 30px;
            font-size: 14px;
            font-weight: 600;
        }
        .input-group {
            position: relative;
            margin-bottom: 20px;
            text-align: left;
        }
        .input-group i.icon {
            position: absolute;
            left: 16px;
            top: 50%;
            transform: translateY(-50%);
            color: var(--text-light);
            font-size: 14px;
        }
        input { 
            font-family: var(--font-main);
            width: 100%; 
            padding: 14px 14px 14px 44px; 
            background: rgba(255,255,255,0.9);
            border: 2px solid #e0e8e4; 
            border-radius: 12px; 
            color: var(--text-dark);
            font-size: 15px;
            font-weight: 600;
            transition: all 0.3s ease;
        }
        input:focus {
            outline: none;
            border-color: var(--emerald);
            background: #fff;
            box-shadow: 0 4px 12px rgba(0,104,56,0.06);
        }
        input::placeholder {
            color: #9ca3af;
            font-weight: 500;
        }
        .toggle-password {
            position: absolute;
            right: 16px;
            top: 50%;
            transform: translateY(-50%);
            color: var(--text-light);
            cursor: pointer;
            transition: color 0.3s;
        }
        .toggle-password:hover { color: var(--emerald); }
        button { 
            font-family: var(--font-main);
            width: 100%; 
            padding: 14px; 
            background: var(--emerald);
            color: var(--gold); 
            border: none; 
            border-radius: 12px; 
            cursor: pointer; 
            font-size: 16px; 
            font-weight: 800;
            transition: all 0.3s cubic-bezier(0.22, 1, 0.36, 1);
            margin-top: 10px;
            box-shadow: 0 4px 12px rgba(0,104,56,0.2);
            position: relative;
            overflow: hidden;
        }
        button:hover { 
            transform: translateY(-2px);
            background: var(--emerald-dark);
            box-shadow: 0 8px 24px rgba(0,104,56,0.3);
        }
        button::after {
            content: '';
            position: absolute;
            top: 0; left: -100%; width: 50%; height: 100%;
            background: linear-gradient(to right, transparent, rgba(255,255,255,0.1), transparent);
            transform: skewX(-20deg);
        }
        button:hover::after {
            animation: shine 0.7s;
        }
        @keyframes shine { 100% { left: 200%; } }
        .error { 
            background: #fef2f2;
            color: #ef4444; 
            padding: 12px;
            border-radius: 10px;
            margin-bottom: 20px; 
            font-size: 14px;
            font-weight: 600;
            border: 1px solid #fee2e2;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
        }
    </style>
</head>
<body>
    <div class="bg-shape shape-1"></div>
    <div class="bg-shape shape-2"></div>
    
    <div class="login-wrapper">
        <div class="login-box">
            <div class="logo-icon">
                <i class="fas fa-fire"></i>
            </div>
            <h2>SS Admin</h2>
            <div class="subtitle">Enter your credentials to access the portal</div>
            
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
