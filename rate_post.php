<?php
session_start();
include 'includes/db.php';

// Must be logged in to rate
if (!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true) {
    echo json_encode(['success' => false, 'message' => 'Login required to rate.']);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['post_id']) && isset($_POST['action'])) {
    $post_id = intval($_POST['post_id']);
    $user_id = $_SESSION['id'];
    $action = $_POST['action']; // 'like' or 'dislike'

    // Determine the rating value
    $value = ($action == 'like') ? 1 : -1;

    // 1. Check if the user has already rated this post
    $check_sql = "SELECT rating_value FROM post_ratings WHERE post_id = ? AND user_id = ?";
    $check_stmt = $conn->prepare($check_sql);
    $check_stmt->bind_param("ii", $post_id, $user_id);
    $check_stmt->execute();
    $result = $check_stmt->get_result();

    $new_rating = $value;
    
    if ($result->num_rows > 0) {
        $row = $result->fetch_assoc();
        $old_value = $row['rating_value'];

        if ($old_value == $value) {
           
            $sql = "DELETE FROM post_ratings WHERE post_id = ? AND user_id = ?";
            $new_rating = 0;
        } else {
            // User clicks opposite vote: UPDATE the vote
            $sql = "UPDATE post_ratings SET rating_value = ? WHERE post_id = ? AND user_id = ?";
            $new_rating = $value;
        }
    } else {
        // First time voting: INSERT the vote
        $sql = "INSERT INTO post_ratings (post_id, user_id, rating_value) VALUES (?, ?, ?)";
    }
    
    // 2. Execute the action (INSERT, UPDATE, or DELETE)
    if ($stmt = $conn->prepare($sql)) {
        if ($new_rating !== 0 && $result->num_rows > 0) {
            $stmt->bind_param("iii", $value, $post_id, $user_id); 
        } elseif ($new_rating !== 0) {
            $stmt->bind_param("iii", $post_id, $user_id, $value); 
        } else {
            $stmt->bind_param("ii", $post_id, $user_id); 
        }
        
        $stmt->execute();
        
        
        $score_sql = "SELECT SUM(rating_value) as total_score FROM post_ratings WHERE post_id = ?";
        $score_stmt = $conn->prepare($score_sql);
        $score_stmt->bind_param("i", $post_id);
        $score_stmt->execute();
        $score_result = $score_stmt->get_result()->fetch_assoc();
        $total_score = $score_result['total_score'] ?? 0;
        
        echo json_encode([
            'success' => true, 
            'total_score' => $total_score,
            'new_rating' => $new_rating 
        ]);
        
    } else {
        echo json_encode(['success' => false, 'message' => $conn->error]);
    }

} else {
    echo json_encode(['success' => false, 'message' => 'Invalid request.']);
}
$conn->close();
?>