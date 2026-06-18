<?php
require_once 'auth.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $name = $conn->real_escape_string($_POST['name']);
    $mrp = (float)$_POST['mrp'];
    $price = (float)$_POST['price'];
    $category = $conn->real_escape_string($_POST['category']);
    
    $image_url = '';
    
    // Handle Image Upload
    if (isset($_FILES['image']) && $_FILES['image']['error'] == 0) {
        $allowed = ['jpg', 'jpeg', 'png', 'gif', 'webp'];
        $filename = $_FILES['image']['name'];
        $ext = strtolower(pathinfo($filename, PATHINFO_EXTENSION));
        
        if (in_array($ext, $allowed)) {
            $new_filename = uniqid() . '.' . $ext;
            $upload_path = '../uploads/' . $new_filename;
            
            // Ensure uploads directory exists
            if (!is_dir('../uploads')) {
                mkdir('../uploads', 0755, true);
            }
            
            if (move_uploaded_file($_FILES['image']['tmp_name'], $upload_path)) {
                $image_url = 'uploads/' . $new_filename;
            } else {
                $error = "Failed to move uploaded file.";
            }
        } else {
            $error = "Invalid file type. Only JPG, PNG, GIF, WEBP are allowed.";
        }
    }
    
    if (!isset($error)) {
        $sql = "INSERT INTO products (name, mrp, price, category, image_url) VALUES ('$name', $mrp, $price, '$category', '$image_url')";
        if ($conn->query($sql)) {
            header("Location: index.php");
            exit;
        } else {
            $error = "Database Error: " . $conn->error;
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Add Product - SS Crackers</title>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Nunito:wght@400;600;700;800&family=Playfair+Display:ital,wght@0,600;0,700;0,800;1,600&display=swap" rel="stylesheet">
    <style>
        :root {
            --primary: #D32F2F;
            --primary-dark: #7F0000;
            --secondary: #D4AF37;
            --accent: #D32F2F;
            --white: #ffffff;
            --off-white: #f9fafb;
            --light-gray: #f3f4f6;
            --medium-gray: #e5e7eb;
            --text-dark: #2b1111;
            --text-medium: #5c3333;
            --text-light: #8a6b6b;
            --font-main: 'Nunito', sans-serif;
            --font-display: 'Playfair Display', serif;
            --radius-md: 14px;
            --radius-xl: 32px;
            --shadow-md: 0 4px 20px rgba(255,69,0,0.16);
        }
        body { font-family: var(--font-main); background: var(--off-white); margin: 0; padding: 20px; color: var(--text-dark); }
        .container { max-width: 600px; margin: auto; background: var(--white); padding: 30px; border-radius: var(--radius-md); box-shadow: var(--shadow-md); border: 1px solid var(--medium-gray); margin-top: 20px; }
        h2 { font-family: var(--font-main); margin-top: 0; font-size: 22px; color: var(--text-dark); font-weight: 700; margin-bottom: 25px; display: flex; align-items: center; gap: 8px; }
        h2 i { color: var(--primary); font-size: 20px; }
        .form-group { margin-bottom: 20px; }
        label { display: block; margin-bottom: 8px; font-weight: 600; color: var(--text-medium); font-size: 14px; }
        input[type="text"], input[type="number"], select { 
            font-family: var(--font-main);
            width: 100%; padding: 12px 16px; box-sizing: border-box; 
            border: 2px solid var(--medium-gray); border-radius: 8px; 
            font-size: 15px; font-weight: 500; color: var(--text-dark); transition: all 0.3s;
            background: var(--light-gray);
        }
        input:focus, select:focus { outline: none; border-color: var(--primary); background: var(--white); }
        .file-upload-wrapper {
            position: relative;
            width: 100%;
            height: 150px;
            border: 2px dashed var(--primary);
            border-radius: 12px;
            background: var(--light-gray);
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            cursor: pointer;
            overflow: hidden;
            transition: all 0.3s;
        }
        .file-upload-wrapper:hover {
            background: #ffebe6;
            border-color: var(--primary-dark);
        }
        .file-upload-wrapper input[type="file"] {
            position: absolute;
            width: 100%;
            height: 100%;
            opacity: 0;
            cursor: pointer;
            z-index: 2;
        }
        .file-upload-content {
            text-align: center;
            color: var(--primary);
            z-index: 1;
        }
        .file-upload-content i {
            font-size: 2.5rem;
            margin-bottom: 10px;
        }
        .file-upload-content span {
            font-family: var(--font-main);
            font-weight: 600;
            font-size: 14px;
        }
        #imagePreview {
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            object-fit: contain;
            background: var(--white);
            display: none;
            z-index: 1;
            padding: 10px;
            border-radius: 10px;
        }
        .btn-group { display: flex; gap: 15px; margin-top: 25px; }
        .btn { padding: 10px 20px; font-family: var(--font-main); background: rgba(211, 47, 47, 0.1); backdrop-filter: blur(8px); color: var(--primary); border: 1px solid rgba(211, 47, 47, 0.3); border-radius: var(--radius-xl); cursor: pointer; font-weight: 600; font-size: 14px; box-shadow: 0 4px 10px rgba(0,0,0,0.03); transition: all 0.3s; display: inline-flex; align-items: center; justify-content: center; gap: 8px; flex: 1; text-decoration: none; }
        .btn:hover { background: var(--primary); color: white; transform: translateY(-2px); box-shadow: 0 6px 15px rgba(211, 47, 47, 0.25); }
        .btn-cancel { background: transparent; color: var(--text-light); border: 1px solid var(--medium-gray); box-shadow: none; text-decoration: none; }
        .btn-cancel:hover { background: var(--light-gray); color: var(--text-dark); transform: translateY(-2px); box-shadow: 0 4px 10px rgba(0,0,0,0.05); }
        .error { background: #ffebe6; color: #d32f2f; padding: 12px; border-radius: 8px; margin-bottom: 20px; font-size: 14px; border: 1px solid #ffcdd2; font-weight: 500; }
    </style>
</head>
<body>
    <div class="container">
        <h2><i class="fas fa-plus-circle"></i> Add New Product</h2>
        <?php if(isset($error)) echo "<div class='error'><i class='fas fa-exclamation-circle'></i> $error</div>"; ?>
        <form method="POST" enctype="multipart/form-data">
            <div class="form-group">
                <label>Product Name</label>
                <input type="text" name="name" required>
            </div>
            <div class="form-group">
                <label>Category</label>
                <select name="category" required>
                    <?php 
                    $cats = ['bijili'=>'Bijili & Strings', 'bombs'=>'Bombs', 'flower'=>'Flower Pots', 'chakkra'=>'Ground Chakkra', 'rocket'=>'Rockets', 'aerial'=>'Aerial / Sky Shots', 'fancy'=>'Fancy / Kids Items', 'sparkler'=>'Sparklers'];
                    foreach($cats as $val => $label) {
                        echo "<option value='$val'>$label</option>";
                    }
                    ?>
                </select>
            </div>
            <div class="form-group">
                <label>MRP (₹)</label>
                <input type="number" name="mrp" step="0.01" required>
            </div>
            <div class="form-group">
                <label>Selling Price (₹)</label>
                <input type="number" name="price" step="0.01" required>
            </div>
            <div class="form-group">
                <label>Product Image</label>
                <div class="file-upload-wrapper">
                    <input type="file" name="image" id="imageInput" accept="image/*">
                    <div class="file-upload-content" id="uploadContent">
                        <i class="fas fa-cloud-upload-alt"></i><br>
                        <span>Drag & Drop or Click to Upload</span>
                    </div>
                    <img id="imagePreview" src="#" alt="Preview">
                </div>
            </div>
            <div class="btn-group">
                <button type="submit" class="btn"><i class="fas fa-save"></i> Save Product</button>
                <a href="index.php" class="btn btn-cancel"><i class="fas fa-times"></i> Cancel</a>
            </div>
        </form>
    </div>
    
    <script>
        const imageInput = document.getElementById('imageInput');
        const imagePreview = document.getElementById('imagePreview');
        const uploadContent = document.getElementById('uploadContent');

        imageInput.addEventListener('change', function() {
            const file = this.files[0];
            if (file) {
                const reader = new FileReader();
                reader.onload = function(e) {
                    imagePreview.src = e.target.result;
                    imagePreview.style.display = 'block';
                    uploadContent.style.display = 'none';
                }
                reader.readAsDataURL(file);
            } else {
                imagePreview.style.display = 'none';
                uploadContent.style.display = 'block';
            }
        });
    </script>
</body>
</html>
