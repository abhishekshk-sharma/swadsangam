<!DOCTYPE html>
<html>
<head>
    <title>Image Test</title>
</head>
<body>
    <h1>Testing Images</h1>
    <?php
    $images = [
        'uploads/menu/sample_1.jpg',
        'uploads/menu/sample_2.jpg',
        'uploads/menu/sample_3.jpg',
    ];
    foreach($images as $img) {
        echo '<div style="margin:20px;">';
        echo '<p>Path: ' . $img . '</p>';
        echo '<p>Full URL: ' . asset($img) . '</p>';
        echo '<p>File exists: ' . (file_exists(public_path($img)) ? 'YES' : 'NO') . '</p>';
        echo '<img src="' . asset($img) . '" width="100" style="border:2px solid red;">';
        echo '</div>';
    }
    ?>
</body>
</html>
