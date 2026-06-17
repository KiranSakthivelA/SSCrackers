<?php
require_once 'auth.php';

$sql = "SELECT * FROM products ORDER BY id DESC";
$result = $conn->query($sql);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Products Dashboard - SS Crackers</title>
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
            --shadow-sm: 0 2px 8px rgba(255,69,0,0.10);
            --shadow-md: 0 4px 20px rgba(255,69,0,0.16);
        }
        body { font-family: var(--font-main); background: var(--off-white); margin: 0; padding: 40px 20px; color: var(--text-dark); }
        .container { max-width: 1200px; margin: auto; background: var(--white); padding: 30px; border-radius: var(--radius-md); box-shadow: var(--shadow-md); border: 1px solid var(--medium-gray); }
        .header { display: flex; justify-content: space-between; align-items: center; border-bottom: 2px solid var(--medium-gray); padding-bottom: 20px; margin-bottom: 30px; }
        .header h2 { margin: 0; font-size: 28px; color: var(--text-dark); font-weight: 800; font-family: var(--font-display); display: flex; align-items: center; gap: 10px; }
        .header h2 i { color: var(--primary); }
        .btn { padding: 10px 24px; font-family: var(--font-main); background: linear-gradient(135deg, var(--primary), var(--secondary)); color: white; text-decoration: none; border-radius: var(--radius-xl); font-weight: 600; font-size: 0.95rem; box-shadow: 0 4px 15px rgba(230,57,70,0.35); transition: all 0.3s; display: inline-flex; align-items: center; gap: 8px; border: 2px solid transparent; }
        .btn:hover { transform: translateY(-2px); box-shadow: 0 8px 25px rgba(230,57,70,0.45); }
        .btn-outline { background: transparent; color: var(--primary); border: 2px solid var(--primary); box-shadow: none; }
        .btn-outline:hover { background: var(--primary); color: white; transform: translateY(-2px); }
        
        .table-container { overflow-x: auto; border-radius: var(--radius-md); border: 1px solid var(--medium-gray); background: var(--white); }
        table { width: 100%; border-collapse: collapse; white-space: nowrap; }
        th, td { text-align: left; padding: 16px 20px; border-bottom: 1px solid var(--medium-gray); }
        th { background: var(--light-gray); font-weight: 700; color: var(--text-dark); font-size: 14px; text-transform: uppercase; letter-spacing: 0.5px; }
        tr:last-child td { border-bottom: none; }
        tr:hover { background: var(--off-white); }
        
        .img-thumb { width: 48px; height: 48px; object-fit: cover; border-radius: 8px; box-shadow: var(--shadow-sm); border: 2px solid var(--light-gray); }
        .no-img { width: 48px; height: 48px; background: var(--light-gray); color: var(--primary); border-radius: 8px; display: flex; align-items: center; justify-content: center; font-size: 20px; }
        
        .product-name { font-weight: 700; color: var(--text-dark); }
        .category-badge { display: inline-block; padding: 6px 16px; background: linear-gradient(135deg, var(--primary), var(--secondary)); color: white; border-radius: 20px; font-size: 12px; font-weight: 700; text-transform: uppercase; box-shadow: var(--shadow-sm); }
        
        .price { font-weight: 700; color: var(--success); font-size: 1.1rem; }
        .mrp { color: var(--text-light); text-decoration: line-through; font-size: 14px; font-weight: 500; }
        
        .actions a { margin-right: 15px; color: var(--text-medium); text-decoration: none; transition: color 0.2s; font-size: 18px; }
        .actions a.edit:hover { color: #3b82f6; }
        .actions a.delete:hover { color: #ef4444; }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h2><i class="fas fa-boxes"></i> Inventory Management</h2>
            <div>
                <a href="add_product.php" class="btn"><i class="fas fa-plus"></i> Add Product</a>
                <a href="logout.php" class="btn btn-outline" style="margin-left:10px;"><i class="fas fa-sign-out-alt"></i> Logout</a>
            </div>
        </div>

        <div class="table-container">
        <table>
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Image</th>
                    <th>Name</th>
                    <th>Category</th>
                    <th>MRP</th>
                    <th>Price</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php if ($result->num_rows > 0): ?>
                    <?php while($row = $result->fetch_assoc()): ?>
                        <tr>
                            <td><?= $row['id'] ?></td>
                            <td>
                                <?php if($row['image_url']): ?>
                                    <img src="../<?= htmlspecialchars($row['image_url']) ?>" class="img-thumb">
                                <?php else: ?>
                                    <div class="no-img"><i class="fas fa-image"></i></div>
                                <?php endif; ?>
                            </td>
                            <td class="product-name"><?= htmlspecialchars($row['name']) ?></td>
                            <td><span class="category-badge"><?= htmlspecialchars($row['category']) ?></span></td>
                            <td class="mrp">₹<?= number_format($row['mrp'], 2) ?></td>
                            <td class="price">₹<?= number_format($row['price'], 2) ?></td>
                            <td class="actions">
                                <a href="edit_product.php?id=<?= $row['id'] ?>" class="edit" title="Edit"><i class="fas fa-pen"></i></a>
                                <a href="delete_product.php?id=<?= $row['id'] ?>" class="delete" title="Delete" onclick="return confirm('Are you sure you want to delete this product?');"><i class="fas fa-trash"></i></a>
                            </td>
                        </tr>
                    <?php endwhile; ?>
                <?php else: ?>
                    <tr><td colspan="7" style="text-align:center;">No products found.</td></tr>
                <?php endif; ?>
            </tbody>
        </table>
        </div>
    </div>
</body>
</html>
