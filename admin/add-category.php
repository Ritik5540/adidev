<?php
include "../admin-config.php";
$name = $_POST['category_name'];

$slug = strtolower(str_replace(" ", "-", $name));

// Simple insert query
$sql = "INSERT INTO categories (name, slug) 
        VALUES ('$name', '$slug')";

echo $sql; // For debugging purposes, remove in production

if ($conn->query($sql) === TRUE) {
    // redirect with success message
    header("Location: blog.php?status=success&message=Category added successfully");
    exit;
} else {
    header("Location: blog.php?status=error&message=Error adding category");
    exit;
}
?>
