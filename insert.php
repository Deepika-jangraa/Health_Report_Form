<?php
// Establish database connection
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "health_report_db";

$conn = new mysqli($servername, $username, $password, $dbname);
if ($conn->connect_error) {
  die("Connection failed: " . $conn->connect_error);
} 
    echo "Connected successfully!";


if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  // Insert user details into the database
  $name = $_POST['name'];
  $age = $_POST['age'];
  $weight = $_POST['weight'];
  $email = $_POST['email'];

  $sql = "INSERT INTO users (name, age, weight, email) VALUES ('$name', '$age', '$weight', '$email')";
  if ($conn->query($sql) === FALSE) {
    die("Error: " . $sql . "<br>" . $conn->error);
  }

  // Upload and insert PDF file into the database
  $targetDir = "uploads/";
  $targetFile = $targetDir . basename($_FILES["healthReport"]["name"]);
  $uploadOk = 1;
  $fileType = strtolower(pathinfo($targetFile, PATHINFO_EXTENSION));

  // Check if file is a PDF
  if ($fileType != "pdf") {
    echo "Error: Only PDF files are allowed.";
    $uploadOk = 0;
  }

  // Check if file already exists
  if (file_exists($targetFile)) {
    echo "Error: File already exists.";
    $uploadOk = 0;
  }

  // Check file size (limit to 5MB)
  if ($_FILES["healthReport"]["size"] > 5242880) {
    echo "Error: File size exceeds the limit of 5MB.";
    $uploadOk = 0;
  }

  // Upload file if everything is OK
  if ($uploadOk) {
    if (move_uploaded_file($_FILES["healthReport"]["tmp_name"], $targetFile)) {
      $pdfPath = $targetFile;

      $insertPdfSql = "INSERT INTO health_reports (email, pdf_path) VALUES ('$email', '$pdfPath')";
      if ($conn->query($insertPdfSql) === FALSE) {
        echo "Error: " . $insertPdfSql . "<br>" . $conn->error;
      } else {
        echo "Form submitted successfully!";
      }
    } else {
      echo "Error uploading file.";
    }
  }
}
// fetch user health report basedon email id
if (isset($_GET['email'])) {
  $email = $_GET['email'];

  // retrieve pdf path from health_reports table
  $fetchPdfSql = "SELECT pdf_path FROM health_reports WHERE email = '$email'";
  $result = $conn->query($fetchPdfSql);

  if ($result->num_rows > 0) {
    $row = $result->fetch_assoc();
    $pdfPath = $row['pdf_path'];

    // output the pdf file
    header("Content-type: application/pdf");
    header("Content-Disposition: inline; filename=health_report.pdf");
    readfile($pdfPath);
    exit;
  } else {
    echo "No health report found for the provided email.";
  }
}
$conn->close();
?>