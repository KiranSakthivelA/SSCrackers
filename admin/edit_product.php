<?php
require_once 'auth.php';

$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
if (!$id) {
    header("Location: index.php");
    exit;
}

// Fetch existing product
$sql = "SELECT * FROM products WHERE id = $id";
$result = $conn->query($sql);
if ($result->num_rows == 0) {
    header("Location: index.php");
    exit;
}
$product = $result->fetch_assoc();

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $name = $conn->real_escape_string($_POST['name']);
    $mrp = (float)$_POST['mrp'];
    $price = (float)$_POST['price'];
    $category = $conn->real_escape_string($_POST['category']);
    
    $image_url = $product['image_url']; // Keep old image by default
    
    // Handle Image Deletion Checkbox
    if (isset($_POST['remove_image']) && $_POST['remove_image'] == '1') {
        if ($image_url && file_exists('../' . $image_url)) {
            unlink('../' . $image_url);
        }
        $image_url = null;
    }
    
    // Handle Image Upload
    if (isset($_FILES['image']) && $_FILES['image']['error'] == 0) {
        $allowed = ['jpg', 'jpeg', 'png', 'gif', 'webp'];
        $filename = $_FILES['image']['name'];
        $ext = strtolower(pathinfo($filename, PATHINFO_EXTENSION));
        
        if (in_array($ext, $allowed)) {
            $new_filename = uniqid() . '.' . $ext;
            $upload_path = '../uploads/' . $new_filename;
            
            if (!is_dir('../uploads')) {
                mkdir('../uploads', 0755, true);
            }
            
            if (move_uploaded_file($_FILES['image']['tmp_name'], $upload_path)) {
                // Delete old image
                if ($image_url && file_exists('../' . $image_url)) {
                    unlink('../' . $image_url);
                }
                $image_url = 'uploads/' . $new_filename;
            } else {
                $error = "Failed to move uploaded file.";
            }
        } else {
            $error = "Invalid file type. Only JPG, PNG, GIF, WEBP are allowed.";
        }
    }
    
    if (!isset($error)) {
        $image_url_sql = $image_url ? "'$image_url'" : "NULL";
        $update_sql = "UPDATE products SET name='$name', mrp=$mrp, price=$price, category='$category', image_url=$image_url_sql WHERE id=$id";
        if ($conn->query($update_sql)) {
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
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Product - SS Crackers</title>
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
            --radius-md: 14px;
            --radius-xl: 32px;
            --shadow-md: 0 4px 20px rgba(255,69,0,0.16);
        }
        body { font-family: var(--font-main); background: var(--off-white); margin: 0; padding: 40px 20px; color: var(--text-dark); }
        .container { max-width: 600px; margin: auto; background: var(--white); padding: 40px; border-radius: var(--radius-md); box-shadow: var(--shadow-md); border: 1px solid var(--medium-gray); }
        h2 { font-family: var(--font-display); margin-top: 0; font-size: 28px; color: var(--text-dark); font-weight: 800; margin-bottom: 30px; display: flex; align-items: center; gap: 10px; }
        h2 i { color: var(--primary); }
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
        input[type="file"] {
            font-family: var(--font-main);
            width: 100%; padding: 10px; border: 2px dashed var(--primary);
            border-radius: 8px; background: var(--light-gray); cursor: pointer;
            color: var(--text-medium);
        }
        .btn-group { display: flex; gap: 15px; margin-top: 30px; }
        .btn { padding: 12px 24px; font-family: var(--font-main); background: linear-gradient(135deg, var(--primary), var(--secondary)); color: white; border: 2px solid transparent; border-radius: var(--radius-xl); cursor: pointer; font-weight: 600; font-size: 15px; box-shadow: 0 4px 15px rgba(255,69,0,0.3); transition: all 0.3s; display: inline-flex; align-items: center; justify-content: center; gap: 8px; flex: 1; }
        .btn:hover { transform: translateY(-2px); box-shadow: 0 8px 25px rgba(255,69,0,0.4); }
        .btn-cancel { background: transparent; color: var(--primary); border: 2px solid var(--primary); box-shadow: none; text-decoration: none; }
        .btn-cancel:hover { background: var(--primary); color: white; transform: translateY(-2px); box-shadow: 0 4px 15px rgba(255,69,0,0.3); }
        .error { background: #ffebe6; color: #d32f2f; padding: 12px; border-radius: 8px; margin-bottom: 20px; font-size: 14px; border: 1px solid #ffcdd2; font-weight: 500; }
        .current-img { max-width: 150px; margin-top: 15px; border-radius: 8px; box-shadow: var(--shadow-sm); border: 2px solid var(--light-gray); display: block; }
        .remove-img { display: inline-flex; align-items: center; gap: 8px; margin-top: 10px; color: #ef4444; font-size: 14px; font-weight: 600; cursor: pointer; }
        .remove-img input { width: auto; margin: 0; }
    </style>
</head>
<body>
    <div class="container">
        <h2><i class="fas fa-pen"></i> Edit Product</h2>
        <?php if(isset($error)) echo "<div class='error'><i class='fas fa-exclamation-circle'></i> $error</div>"; ?>
        <form method="POST" enctype="multipart/form-data">
            <div class="form-group">
                <label>Product Name</label>
                <input type="text" name="name" value="<?= htmlspecialchars($product['name']) ?>" required>
            </div>
            <div class="form-group">
                <label>Category</label>
                <select name="category" required>
                    <?php 
                    $cats = ['bijili'=>'Bijili & Strings', 'bombs'=>'Bombs', 'flower'=>'Flower Pots', 'chakkra'=>'Ground Chakkra', 'rocket'=>'Rockets', 'aerial'=>'Aerial / Sky Shots', 'fancy'=>'Fancy / Kids Items', 'sparkler'=>'Sparklers'];
                    foreach($cats as $val => $label) {
                        $sel = ($product['category'] == $val) ? 'selected' : '';
                        echo "<option value='$val' $sel>$label</option>";
                    }
                    ?>
                </select>
            </div>
            <div class="form-group">
                <label>MRP (₹)</label>
                <input type="number" name="mrp" step="0.01" value="<?= $product['mrp'] ?>" required>
            </div>
            <div class="form-group">
                <label>Selling Price (₹)</label>
                <input type="number" name="price" step="0.01" value="<?= $product['price'] ?>" required>
            </div>
            <div class="form-group">
                <label>Product Image (Leave blank to keep current)</label>
                <input type="file" name="image" accept="image/*">
                <?php if($product['image_url']): ?>
                    <img src="../<?= htmlspecialchars($product['image_url']) ?>" class="current-img">
                    <label class="remove-img">
                        <input type="checkbox" name="remove_image" value="1"> 
                        <i class="fas fa-trash-alt"></i> Delete current image
                    </label>
                <?php endif; ?>
            </div>
            <div class="btn-group">
                <button type="submit" class="btn"><i class="fas fa-save"></i> Update Product</button>
                <a href="index.php" class="btn btn-cancel"><i class="fas fa-times"></i> Cancel</a>
            </div>
        </form>
    </div>
</body>
</html>
