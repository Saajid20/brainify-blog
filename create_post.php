<?php
session_start();
if (!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true) {
    header("Location: login.php");
    exit;
}
include 'includes/db.php'; include 'includes/header.php';

// Fetch existing tags for new posts
$all_tags = [];
$tags_query = "SELECT name FROM tags ORDER BY name ASC";
$tags_result = $conn->query($tags_query);
while ($row = $tags_result->fetch_assoc()) { $all_tags[] = $row['name']; }

$error = ""; $success = "";
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $title = trim($_POST['title']);
    $content = trim($_POST['content']);
    $tags_input = trim($_POST['tags']);

    if (empty($title) || empty($content) || empty($tags_input)) {
        $error = "Please fill in title, content, and at least one tag.";
    } else {
        $sql = "INSERT INTO blogPost (title, content, user_id) VALUES (?, ?, ?)";
        if ($stmt = $conn->prepare($sql)) {
            $stmt->bind_param("ssi", $title, $content, $_SESSION["id"]);
            if ($stmt->execute()) {
                $new_post_id = $conn->insert_id;
                $stmt->close();

                
                $tags = array_unique(array_filter($tags));

                foreach ($tags as $tag_name) {
                    $tag_id = 0;
                    $check_tag = $conn->prepare("SELECT id FROM tags WHERE name = ?");
                    $check_tag->bind_param("s", $tag_name);
                    $check_tag->execute();
                    $check_tag->store_result();
                    if ($check_tag->num_rows > 0) {
                        $check_tag->bind_result($tag_id); $check_tag->fetch();
                    } else {
                        $insert_tag = $conn->prepare("INSERT INTO tags (name) VALUES (?)");
                        $insert_tag->bind_param("s", $tag_name);
                        $insert_tag->execute();
                        $tag_id = $conn->insert_id;
                        $insert_tag->close();
                    }
                    $check_tag->close();
                    if ($tag_id > 0) {
                        $link_tag = $conn->prepare("INSERT IGNORE INTO post_tags (post_id, tag_id) VALUES (?, ?)");
                        $link_tag->bind_param("ii", $new_post_id, $tag_id);
                        $link_tag->execute();
                        $link_tag->close();
                    }
                }
                $success = "Post published successfully!"; $title = ""; $content = ""; $tags_input = "";
            } else { $error = "Error: " . $conn->error; }
        }
    }
    $conn->close();
}
?>
<div class="max-w-5xl mx-auto mt-12 mb-24">
    <div class="bg-tech-medium p-8 border-t-4 border-tech-neon shadow-2xl">
        <h2 class="text-4xl font-heading text-white mb-8 tracking-widest">NEW TRANSMISSION</h2>

        <?php if ($error !== ""): ?><div class="bg-red-900/30 text-red-300 p-4 mb-6 border-l-2 border-red-500 tracking-wider"><?php echo $error; ?></div><?php endif; ?>
        <?php if ($success !== ""): ?><div class="bg-green-900/30 text-green-300 p-4 mb-6 border-l-2 border-green-500 tracking-wider"><?php echo $success; ?></div><?php endif; ?>

        <form action="create_post.php" method="POST" class="space-y-8">
            <div>
                <label class="block text-tech-accent text-sm font-bold mb-2 uppercase tracking-widest">Subject Line</label>
                <input type="text" name="title" required value="<?php echo isset($title) ? htmlspecialchars($title) : ''; ?>" 
                       class="w-full px-6 py-4 bg-tech-dark border-2 border-tech-medium text-white text-lg focus:border-tech-neon focus:outline-none transition duration-300 font-mono" placeholder="// Enter title...">
            </div>

            <div>
                <label class="block text-tech-accent text-sm font-bold mb-2 uppercase tracking-widest">Tags / Categories</label>
                <input type="text" name="tags" list="tag-list" required value="<?php echo isset($tags_input) ? htmlspecialchars($tags_input) : ''; ?>" 
                       class="w-full px-6 py-4 bg-tech-dark border-2 border-tech-medium text-tech-neon focus:border-tech-neon focus:outline-none transition duration-300 font-mono" placeholder="tech, data, future">
                <datalist id="tag-list"><?php foreach ($all_tags as $tag): ?><option value="<?php echo htmlspecialchars($tag); ?>"><?php endforeach; ?></datalist>
            </div>

            <div>
                <label class="block text-tech-accent text-sm font-bold mb-2 uppercase tracking-widest">Content Data</label>
                <textarea name="content" rows="15" required 
                          class="w-full px-6 py-4 bg-tech-dark border-2 border-tech-medium text-tech-light focus:border-tech-neon focus:outline-none transition duration-300 font-mono leading-relaxed" placeholder="// Begin transmission..."><?php echo isset($content) ? htmlspecialchars($content) : ''; ?></textarea>
            </div>

            <div class="flex justify-end">
                <button type="submit" class="bg-tech-neon text-tech-dark px-10 py-4 font-bold font-heading tracking-widest hover:bg-white transition duration-300 text-xl">
                    UPLOAD
                </button>
            </div>
        </form>
    </div>
</div>
<?php include 'includes/footer.php'; ?>