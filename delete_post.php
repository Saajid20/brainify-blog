<?php

session_start();

if (!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true) {
    header("Location: login.php");
    exit;
}

include 'includes/db.php';  


if (isset($_GET['id'])) {
    $post_id = intval($_GET['id']);

    $sql = "DELETE FROM blogPost WHERE id = ? AND user_id = ?";
   
    if ($stmt = $conn->prepare($sql)) {
        $stmt->bind_param("ii", $post_id, $_SESSION['id']);

        if ($stmt->execute()) {

            header("Location: index.php");
            exit;
        } else {
           header("Location: index.php");
            exit;
        }
        $stmt->close();
    }
    } else {
        header("Location: index.php");
    exit;
    }
$conn->close();
?>  