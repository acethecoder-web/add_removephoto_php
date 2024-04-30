<!DOCTYPE html>
<html>

<head>
    <title>Image Upload and Management</title>
</head>

<body>

    <h2>Upload Image</h2>
    <form action="<?php echo $_SERVER['PHP_SELF']; ?>" method="post" enctype="multipart/form-data">
        Select image to upload:
        <input type="file" name="fileToUpload" id="fileToUpload">
        <input type="submit" value="Upload Image" name="submit">
    </form>

    <?php
// Database configuration
$servername = "localhost";
$username = "root";
$password = "";
$database = "crud_test";
$table = "images";

// Create connection
$conn = new mysqli($servername, $username, $password, $database);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Handle image upload
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST["submit"])) {
    $target_dir = "uploads/";
    $target_file = $target_dir . basename($_FILES["fileToUpload"]["name"]);
    $uploadOk = 1;
    $imageFileType = strtolower(pathinfo($target_file, PATHINFO_EXTENSION));

    // Check if image file is a actual image or fake image
    $check = getimagesize($_FILES["fileToUpload"]["tmp_name"]);
    if ($check !== false) {
        $uploadOk = 1;
    } else {
        $uploadOk = 0;
    }

    // Check file size
    if ($_FILES["fileToUpload"]["size"] > 500000) {
        $uploadOk = 0;
    }

    // Allow certain file formats
    if($imageFileType != "jpg" && $imageFileType != "png" && $imageFileType != "jpeg" && $imageFileType != "gif") {
        $uploadOk = 0;
    }

    // Check if $uploadOk is set to 0 by an error
    if ($uploadOk == 0) {
        echo "Sorry, your file was not uploaded.";
    } else {
        if (move_uploaded_file($_FILES["fileToUpload"]["tmp_name"], $target_file)) {
            // Insert filename into database
            $filename = basename($_FILES["fileToUpload"]["name"]);
            $sql = "INSERT INTO $table (filename) VALUES ('$filename')";
            if ($conn->query($sql) === TRUE) {
                echo "Image uploaded successfully.";
            } else {
                echo "Error: " . $sql . "<br>" . $conn->error;
            }
        } else {
            echo "Sorry, there was an error uploading your file.";
        }
    }
}

// Handle image removal
if(isset($_POST['remove_image'])){
    $image_id = $_POST['image_id'];
    $sql = "SELECT filename FROM $table WHERE id = $image_id";
    $result = $conn->query($sql);
    if ($result->num_rows > 0) {
        $row = $result->fetch_assoc();
        $filename = $row['filename'];
        $filepath = "uploads/" . $filename;
        // Delete image file from server
        if(file_exists($filepath)){
            unlink($filepath);
        }
        // Delete image record from database
        $sql = "DELETE FROM $table WHERE id = $image_id";
        if ($conn->query($sql) === TRUE) {
            echo "Image removed successfully.";
        } else {
            echo "Error: " . $sql . "<br>" . $conn->error;
        }
    }
}

// Display uploaded images
$sql = "SELECT id, filename FROM $table";
$result = $conn->query($sql);

if ($result->num_rows > 0) {
    while($row = $result->fetch_assoc()) {
        $image_id = $row['id'];
        $filename = $row['filename'];
        echo "<div>";
        echo "<img src='uploads/$filename' alt='Uploaded Image' style='width:150px;height:150px;'>";
        echo "<form method='post' action='".$_SERVER['PHP_SELF']."'>";
        echo "<input type='hidden' name='image_id' value='$image_id'>";
        echo "<input type='submit' name='remove_image' value='Remove'>";
        echo "</form>";
        echo "</div>";
    }
} else {
    echo "No images uploaded.";
}

$conn->close();
?>

</body>

</html>