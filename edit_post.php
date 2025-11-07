<?php
session_start();
if (!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true) {
    header("Location: login.php");
    exit;
}
include 'includes/db.php'; include 'includes/header.php';

$error = ""; $title = ""; $content = ""; $tags_string = "";

// Fetch all tags for native dropdown
$all_tags = [];
$tags_query = "SELECT name FROM tags ORDER BY name ASC";
$tags_result = $conn->query($tags_query);
while ($row = $tags_result->fetch_assoc()) { $all_tags[] = $row['name']; }

if (isset($_GET['id'])) { $post_id = intval($_GET['id']); } elseif (isset($_POST['post_id'])) { $post_id = intval($_POST['post_id']); } else { header("Location: index.php"); exit; }

if ($_SERVER["REQUEST_METHOD"] != "POST") {
    $sql = "SELECT title, content FROM blogPost WHERE id = ? AND user_id = ?";
    if ($stmt = $conn->prepare($sql)) {
        $stmt->bind_param("ii", $post_id, $_SESSION['id']);
        $stmt->execute();
        $stmt->store_result();
        if ($stmt->num_rows == 1) {
            $stmt->bind_result($title, $content);
            $stmt->fetch();
            $tag_sql = "SELECT tags.name FROM tags JOIN post_tags ON tags.id = post_tags.tag_id WHERE post_tags.post_id = ?";
            $tag_stmt = $conn->prepare($tag_sql);
            $tag_stmt->bind_param("i", $post_id);
            $tag_stmt->execute();
            $tag_result = $tag_stmt->get_result();
            $current_tags = [];
            while ($tag_row = $tag_result->fetch_assoc()) { $current_tags[] = $tag_row['name']; }
            $tags_string = implode(", ", $current_tags);
            $tag_stmt->close();
        } else { echo "<div class='max-w-4xl mx-auto mt-8 bg-red-50 text-red-700 p-4 rounded-lg'>Access denied.</div>"; include 'includes/footer.php'; exit; }
        $stmt->close();
    }
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $title = trim($_POST['title']);
    $content = trim($_POST['content']);
    $tags_input = trim($_POST['tags']);

    if (empty($title) || empty($content)) { $error = "Title and content cannot be empty."; }
    else {
        $sql = "UPDATE blogPost SET title = ?, content = ? WHERE id = ? AND user_id = ?";
        if ($stmt = $conn->prepare($sql)) {
            $stmt->bind_param("ssii", $title, $content, $post_id, $_SESSION['id']);
            if ($stmt->execute()) {
                $delete_tags = $conn->prepare("DELETE FROM post_tags WHERE post_id = ?");
                $delete_tags->bind_param("i", $post_id);
                $delete_tags->execute();
                $delete_tags->close();

                if (!empty($tags_input)) {
                    $tags = array_map('trim', explode(',', $tags_input));
                    $tags = array_unique(array_filter($tags));
                    foreach ($tags as $tag_name) {
                        $tag_id = 0;
                        $check_tag = $conn->prepare("SELECT id FROM tags WHERE name = ?");
                        $check_tag->bind_param("s", $tag_name);
                        $check_tag->execute(); $check_tag->store_result();
                        if ($check_tag->num_rows > 0) { $check_tag->bind_result($tag_id); $check_tag->fetch(); }
                        else {
                            $insert_tag = $conn->prepare("INSERT INTO tags (name) VALUES (?)");
                            $insert_tag->bind_param("s", $tag_name); $insert_tag->execute();
                            $tag_id = $conn->insert_id; $insert_tag->close();
                        }
                        $check_tag->close();
                        if ($tag_id > 0) {
                            $link_tag = $conn->prepare("INSERT IGNORE INTO post_tags (post_id, tag_id) VALUES (?, ?)");
                            $link_tag->bind_param("ii", $post_id, $tag_id); $link_tag->execute(); $link_tag->close();
                        }
                    }
                }
                header("Location: view_post.php?id=" . $post_id); exit;
            } else { $error = "Error updating post."; }
            $stmt->close();
        }
    }
}
?>

<div class="max-w-4xl mx-auto bg-white shadow-xl rounded-2xl overflow-hidden mt-10">
    <div class="py-6 px-8 bg-blue-50 border-b border-blue-100">
        <h2 class="text-3xl font-black text-blue-900">Edit Post</h2>
    </div>
    <div class="p-8">
        <?php if ($error !== ""): ?><div class="bg-red-50 text-red-700 p-4 rounded-lg mb-6 border-l-4 border-red-500 font-medium"><?php echo $error; ?></div><?php endif; ?>
        <form action="edit_post.php" method="POST" class="space-y-6">
            <input type="hidden" name="post_id" value="<?php echo $post_id; ?>">
            <div><label class="block text-lg font-bold text-gray-700 mb-2">Title</label><input type="text" name="title" required value="<?php echo htmlspecialchars($title); ?>" class="w-full px-4 py-3 rounded-xl border-2 border-gray-200 focus:border-blue-500 focus:ring-0 transition duration-200 font-medium text-gray-700 text-lg"></div>
            
            <div>
                <label class="block text-lg font-bold text-gray-700 mb-2">Tags</label>
                <input type="text" name="tags" list="tag-list-edit" required value="<?php echo htmlspecialchars($tags_string); ?>" class="w-full px-4 py-3 rounded-xl border-2 border-gray-200 focus:border-blue-500 focus:ring-0 transition duration-200 font-medium text-gray-700" placeholder="e.g., tech, travel">
                <datalist id="tag-list-edit">
                    <?php foreach ($all_tags as $tag): ?>
                        <option value="<?php echo htmlspecialchars($tag); ?>">
                    <?php endforeach; ?>
                </datalist>
                <p class="text-sm text-gray-500 mt-2">Separate multiple tags with commas.</p>
            </div>

            <div><label class="block text-lg font-bold text-gray-700 mb-2">Content</label><textarea name="content" rows="12" required class="w-full px-4 py-3 rounded-xl border-2 border-gray-200 focus:border-blue-500 focus:ring-0 transition duration-200 font-medium text-gray-700 text-base leading-relaxed"><?php echo htmlspecialchars($content); ?></textarea></div>
            <div class="flex justify-end space-x-4">
                <a href="view_post.php?id=<?php echo $post_id; ?>" class="px-6 py-3 bg-gray-100 text-gray-600 font-semibold rounded-xl hover:bg-gray-200 transition">Cancel</a>
                <button type="submit" class="px-8 py-3 bg-blue-600 hover:bg-blue-700 text-white text-lg font-bold rounded-xl transition shadow-md hover:shadow-lg transform hover:-translate-y-0.5">Update Post</button>
            </div>
        </form>
    </div>
</div>
<?php include 'includes/footer.php'; ?>