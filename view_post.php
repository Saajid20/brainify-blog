<?php
session_start();
include 'includes/db.php';
include 'includes/header.php';


$user_vote = 0; 
$total_score = 0;

if (isset($_GET['id'])) {
    $post_id = intval($_GET['id']);


    $sql = "SELECT blogPost.id, blogPost.user_id, blogPost.title, blogPost.content, blogPost.created_at, user.username 
            FROM blogPost 
            JOIN user ON blogPost.user_id = user.id 
            WHERE blogPost.id = ?";
            
    if ($stmt = $conn->prepare($sql)) {
        $stmt->bind_param("i", $post_id);
        $stmt->execute();
        $stmt->store_result();

        if ($stmt->num_rows == 1) {
            $stmt->bind_result($id, $user_id, $title, $content, $created_at, $username);
            $stmt->fetch();
            
          -
         
            $score_query = "SELECT SUM(rating_value) as total_score FROM post_ratings WHERE post_id = ?";
            $score_stmt = $conn->prepare($score_query);
            $score_stmt->bind_param("i", $id);
            $score_stmt->execute();
            $total_score = $score_stmt->get_result()->fetch_assoc()['total_score'] ?? 0;
            $score_stmt->close();

           
            if (isset($_SESSION['id'])) {
                $vote_query = "SELECT rating_value FROM post_ratings WHERE post_id = ? AND user_id = ?";
                $vote_stmt = $conn->prepare($vote_query);
                $vote_stmt->bind_param("ii", $id, $_SESSION['id']);
                $vote_stmt->execute();
                if ($vote_result = $vote_stmt->get_result()->fetch_assoc()) {
                    $user_vote = $vote_result['rating_value'];
                }
                $vote_stmt->close();
            }
           
            ?>
            
         <article class="max-w-4xl mx-auto mt-12 mb-24">
    <header class="bg-tech-medium p-10 border-b-2 border-tech-neon">
        <h1 class="text-5xl md:text-7xl font-heading text-white mb-6 leading-tight tracking-wide"><?php echo htmlspecialchars($title); ?></h1>
        <div class="flex flex-col md:flex-row md:items-center justify-between text-tech-accent font-mono text-sm uppercase tracking-widest">
            <div class="mb-4 md:mb-0">
                <span class="text-tech-neon">// AUTHOR:</span> <?php echo htmlspecialchars($username); ?> 
                <span class="mx-4">|</span> 
                <span class="text-tech-neon">DATE:</span> <?php echo date("Y.m.d", strtotime($created_at)); ?>
            </div>
            
            <div class="flex items-center bg-tech-dark border border-tech-medium px-4 py-2 rounded-full">
                <button id="like-btn" data-post-id="<?php echo $id; ?>" data-action="like" 
                        class="p-2 transition <?php echo ($user_vote == 1) ? 'text-tech-neon' : 'text-tech-medium hover:text-tech-light'; ?>">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" viewBox="0 0 20 20" fill="currentColor"><path d="M2 10.5a1.5 1.5 0 113 0v6a1.5 1.5 0 01-3 0v-6zM6 10.333v5.432a2.001 2.001 0 001.077 1.637l1.378.89a2 2 0 002.585.342l4.887-3.957A2.002 2.002 0 0018 12.017V8.5a2 2 0 00-2-2h-3.37l-1.458-2.502A2 2 0 0010.58 3H8.381a2 2 0 00-1.5 2.502L7.5 7.5H6a2 2 0 00-2 2z" /></svg>
                </button>
                <span id="score-display" class="font-heading text-xl px-4 text-white"><?php echo $total_score; ?></span>
                <button id="dislike-btn" data-post-id="<?php echo $id; ?>" data-action="dislike" 
                        class="p-2 transition <?php echo ($user_vote == -1) ? 'text-red-500' : 'text-tech-medium hover:text-tech-light'; ?>">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 transform rotate-180" viewBox="0 0 20 20" fill="currentColor"><path d="M2 10.5a1.5 1.5 0 113 0v6a1.5 1.5 0 01-3 0v-6zM6 10.333v5.432a2.001 2.001 0 001.077 1.637l1.378.89a2 2 0 002.585.342l4.887-3.957A2.002 2.002 0 0018 12.017V8.5a2 0 00-2-2h-3.37l-1.458-2.502A2 2 0 0010.58 3H8.381a2 2 0 00-1.5 2.502L7.5 7.5H6a2 2 0 00-2 2z" /></svg>
                </button>
            </div>
        </div>
    </header>
    
    <div class="bg-tech-medium p-10 text-lg leading-relaxed text-tech-light font-sans">
        <?php echo nl2br(htmlspecialchars($content)); ?>
    </div>

    <footer class="bg-tech-dark p-6 border-t border-tech-medium flex justify-between items-center">
        <a href="index.php" class="text-tech-accent hover:text-tech-neon transition font-bold tracking-widest uppercase flex items-center">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-2" viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M9.707 16.707a1 1 0 01-1.414 0l-6-6a1 1 0 010-1.414l6-6a1 1 0 011.414 1.414L5.414 9H17a1 1 0 110 2H5.414l4.293 4.293a1 1 0 010 1.414z" clip-rule="evenodd" /></svg>
            RETURN TO BASE
        </a>
        <?php if (isset($_SESSION["loggedin"]) && $_SESSION["loggedin"] === true && $_SESSION["id"] == $user_id): ?>
            <div class="flex space-x-4">
                <a href="edit_post.php?id=<?php echo $id; ?>" class="px-6 py-2 bg-tech-medium text-tech-neon border border-tech-neon hover:bg-tech-neon hover:text-tech-dark transition font-bold tracking-widest uppercase">EDIT</a>
                <a href="delete_post.php?id=<?php echo $id; ?>" onclick="return confirm('CONFIRM DELETION?');" class="px-6 py-2 bg-red-900/20 text-red-500 border border-red-900 hover:bg-red-500 hover:text-white transition font-bold tracking-widest uppercase">DELETE</a>
            </div>
        <?php endif; ?>
    </footer>
</article>
            </article>
            
            <script>
                document.addEventListener('DOMContentLoaded', () => {
                    const likeBtn = document.getElementById('like-btn');
                    const dislikeBtn = document.getElementById('dislike-btn');
                    const scoreDisplay = document.getElementById('score-display');
                    // PHP prints 'true' or 'false' directly into the JS
                    const isLoggedIn = <?php echo isset($_SESSION['id']) ? 'true' : 'false'; ?>;

                    const handleVote = async (button, action) => {
                        if (!isLoggedIn) {
                            alert('Please log in to rate posts.');
                            return;
                        }

                        const postId = button.dataset.postId;
                        const formData = new FormData();
                        formData.append('post_id', postId);
                        formData.append('action', action);

                        try {
                            const response = await fetch('rate_post.php', {
                                method: 'POST',
                                body: formData
                            });
                            const data = await response.json();

                            if (data.success) {
                                // Update the number
                                scoreDisplay.textContent = data.total_score;
                                
                                // Reset standard styling for both buttons
                                likeBtn.className = 'p-2 rounded-full transition duration-150 text-blue-400 hover:bg-blue-50';
                                dislikeBtn.className = 'p-2 rounded-full transition duration-150 text-blue-400 hover:bg-blue-50';

                                // Apply active styling based on new state
                                if (data.new_rating === 1) {
                                    likeBtn.className = 'p-2 rounded-full transition duration-150 bg-blue-600 text-white';
                                } else if (data.new_rating === -1) {
                                    dislikeBtn.className = 'p-2 rounded-full transition duration-150 bg-blue-600 text-white';
                                }
                            } else {
                                alert(data.message);
                            }
                        } catch (error) {
                            console.error('Error:', error);
                        }
                    };

                    likeBtn.addEventListener('click', () => handleVote(likeBtn, 'like'));
                    dislikeBtn.addEventListener('click', () => handleVote(dislikeBtn, 'dislike'));
                });
            </script>
            
            <?php
        } else {
            echo "<div class='max-w-md mx-auto bg-yellow-50 p-6 rounded-lg text-yellow-800 font-semibold text-center shadow-sm'>Post not found.</div>";
        }
        $stmt->close();
    } else {
        echo "Error preparing statement: " . $conn->error;
    }
} else {
    header("Location: index.php");
    exit;
}
$conn->close();
include 'includes/footer.php';
?>