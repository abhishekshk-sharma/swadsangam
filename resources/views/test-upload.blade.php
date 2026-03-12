<!DOCTYPE html>
<html>
<head><title>Simple Upload Test</title></head>
<body>
<h1>Upload Image for Menu Item #1</h1>
<form action="/test-upload-direct" method="POST" enctype="multipart/form-data">
    @csrf
    <input type="file" name="image" required><br><br>
    <button type="submit">Upload & Update Item 1</button>
</form>
</body>
</html>
