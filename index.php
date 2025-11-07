<?php include 'includes/db.php'; include 'includes/header.php'; ?>

<section class="relative bg-tech-dark flex items-center justify-center py-32 px-4 border-b border-tech-medium">
    <div class="text-center max-w-4xl mx-auto z-10">
        <h1 class="text-8xl md:text-[12rem] font-heading text-tech-neon leading-none mb-2 select-none">
            BRAINIFY
        </h1>
        <p class="text-xl md:text-2xl text-tech-light max-w-2xl mx-auto font-sans tracking-wide mb-12">
            A creative, functional space for alluring thoughts.
        </p>
        
        <div class="max-w-xl mx-auto">
            <form action="index.php" method="GET" class="flex border-2 border-tech-medium focus-within:border-tech-neon transition duration-500">
                <input type="text" name="search" placeholder    ="SEARCH TOPICS..." 
                       class="flex-grow px-6 py-4 bg-transparent text-white placeholder-tech-medium focus:outline-none font-sans tracking-widest uppercase" 
                       value="<?php echo isset($_GET['search']) ? htmlspecialchars($_GET['search']) : ''; ?>">
                <button type="submit" class="bg-tech-neon text-tech-dark px-10 py-4 font-bold font-heading tracking-widest hover:bg-white transition duration-300">
                    EXPLORE
                </button>
            </form>
        </div>
    </div>
    <div class="absolute inset-0 bg-[radial-gradient(circle_at_center,_var(--tw-gradient-stops))] from-tech-medium/20 to-transparent pointer-events-none"></div>
</section>

<section id="latest-posts" class="max-w-6xl mx-auto px-6 py-24">
    <div class="flex items-center mb-16">
        <h2 class="text-5xl font-heading text-white">LATEST TRANSMISSIONS</h2>
        <div class="h-px flex-grow bg-tech-medium ml-10"></div>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-2 gap-10">
        <?php
        $search_query = "";
        $search_param = "";
        if (isset($_GET['search']) && !empty(trim($_GET['search']))) {
            $search_param = '%' . trim($_GET['search']) . '%';
            $search_query = " WHERE blogPost.title LIKE ? OR blogPost.content LIKE ? OR tags.name LIKE ?";
        }

        $sql = "SELECT DISTINCT blogPost.id, blogPost.title, SUBSTRING(blogPost.content, 1, 150) AS excerpt, blogPost.created_at, user.username, GROUP_CONCAT(tags.name SEPARATOR ', ') as tags_list FROM blogPost JOIN user ON blogPost.user_id = user.id LEFT JOIN post_tags ON blogPost.id = post_tags.post_id LEFT JOIN tags ON post_tags.tag_id = tags.id" . $search_query . " GROUP BY blogPost.id ORDER BY blogPost.created_at DESC";

        if ($stmt = $conn->prepare($sql)) {
            if ($search_query) { $stmt->bind_param("sss", $search_param, $search_param, $search_param); }
            $stmt->execute();
            $result = $stmt->get_result();

            if ($result->num_rows > 0) {
                while ($row = $result->fetch_assoc()) {
                    ?>
                    <article class="bg-tech-medium p-10 border-l-4 border-tech-neon hover:border-white transition-all duration-300 group">
                        <div class="flex justify-between items-start mb-6">
                            <div class="flex items-center space-x-3">
                                <span class="text-tech-neon font-heading uppercase tracking-wider text-lg">
                                    <?php echo htmlspecialchars($row['username']); ?>
                                </span>
                                <span class="text-tech-accent text-sm">/</span>
                                <span class="text-tech-light text-sm uppercase tracking-widest">
                                    <?php echo date("M d, Y", strtotime($row['created_at'])); ?>
                                </span>
                            </div>
                        </div>

                        <h3 class="text-4xl font-heading text-white mb-6 leading-tight group-hover:text-tech-neon transition duration-300">
                            <a href="view_post.php?id=<?php echo $row['id']; ?>">
                                <?php echo htmlspecialchars($row['title']); ?>
                            </a>
                        </h3>

                        <p class="text-tech-light font-sans leading-relaxed mb-8">
                            <?php echo htmlspecialchars($row['excerpt']); ?>...
                        </p>

                        <div class="flex justify-between items-end">
                            <?php if (!empty($row['tags_list'])): ?>
                                <div class="flex flex-wrap gap-2">
                                    <?php foreach (explode(', ', $row['tags_list']) as $tag): ?>
                                        <span class="text-xs text-tech-dark bg-tech-accent px-2 py-1 font-bold uppercase">
                                            #<?php echo htmlspecialchars($tag); ?>
                                        </span>
                                    <?php endforeach; ?>
                                </div>
                            <?php else: ?>
                                <div></div>
                            <?php endif; ?>

                            <a href="view_post.php?id=<?php echo $row['id']; ?>" class="text-tech-neon border-b-2 border-tech-neon pb-1 font-bold uppercase tracking-widest text-sm hover:text-white hover:border-white transition duration-300">
                                Read
                            </a>
                        </div>
                    </article>
                    <?php
                }
            } else {
                echo "<p class='col-span-full text-center text-tech-light text-xl uppercase font-heading tracking-widest'>No transmissions found.</p>";
            }
            $stmt->close();
        }
        $conn->close();
        ?>
    </div>
</section>

<?php include 'includes/footer.php'; ?>