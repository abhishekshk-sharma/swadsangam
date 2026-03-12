<!DOCTYPE html>
<html>
<head>
    <title>Upload Test</title>
</head>
<body>
    <h1>Test Image Upload</h1>
    <form action="/debug/upload" method="POST" enctype="multipart/form-data">
        @csrf
        <input type="file" name="image" accept="image/*" required>
        <button type="submit">Upload Test</button>
    </form>
</body>
</html>
