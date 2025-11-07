<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
?>
<!DOCTYPE html>
<html lang="en" class="scroll-smooth">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Brainify</title>
    
    <script src="https://cdn.tailwindcss.com"></script>

    <script>
        tailwind.config = {
          theme: {
            extend: {
              colors: {
                'tech-dark': '#0B0C10',
                'tech-medium': '#1F2833',
                'tech-light': '#C5C6C7',
                'tech-neon': '#66FCF1',
                'tech-accent': '#45A29E',
              },
              fontFamily: {
                  'sans': ['Montserrat', 'sans-serif'],
                  'heading': ['Anton', 'sans-serif'],
              }
            }
          }
        }
    </script>

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Anton&family=Montserrat:wght@400;600;700&display=swap" rel="stylesheet">

    <style>
       
        body {
            font-family: 'Montserrat', sans-serif;
            background-color: #0B0C10; 
            color: #C5C6C7; 
        }
        h1, h2, h3, .font-heading {
            font-family: 'Anton', sans-serif;
            letter-spacing: 0.05em; 
        }
    </style>
</head>
<body class="min-h-screen flex flex-col">

    <nav class="bg-tech-dark/90 backdrop-blur-md sticky top-0 z-50 border-b border-tech-medium">
        <div class="max-w-6xl mx-auto px-6">
            <div class="flex justify-between items-center py-5">
                <div>
                    <a href="index.php" class="text-3xl font-heading text-tech-neon hover:text-white transition duration-300 tracking-widest">
                        BRAINIFY
                    </a>
                </div>
                <div class="flex items-center space-x-8 font-semibold text-sm uppercase tracking-widest">
                    <a href="index.php" class="hover:text-tech-neon transition">Home</a>
                    <?php if (isset($_SESSION["loggedin"]) && $_SESSION["loggedin"] === true): ?>
                        <a href="create_post.php" class="hover:text-tech-neon transition">Write</a>
                        <div class="hidden md:flex items-center ml-6 space-x-6">
                            <span class="text-tech-accent">USER: <b class="text-white"><?php echo htmlspecialchars($_SESSION["username"]); ?></b></span>
                            <a href="logout.php" class="border-2 border-tech-neon text-tech-neon px-6 py-2 hover:bg-tech-neon hover:text-tech-dark transition duration-300">Logout</a>
                        </div>
                    <?php else: ?>
                        <a href="login.php" class="hover:text-tech-neon transition">Login</a>
                        <a href="register.php" class="bg-tech-neon text-tech-dark px-6 py-3 hover:bg-white transition duration-300 font-bold">Get Started</a>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </nav>

    <main class="flex-grow">