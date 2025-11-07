<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

include 'includes/db.php';
include 'includes/header.php';

$error = "";
$success = "";

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $username = trim($_POST['username']);
    $email = trim($_POST['email']);
    $password = trim($_POST['password']);
    $password_confirm = trim($_POST['password_confirm']);

    if (empty($username) || empty($email) || empty($password)) {
        $error = "Please fill out all fields.";
    } elseif ($password !== $password_confirm) {
        $error = "Passwords do not match.";
    }else {
        $hashed_password = password_hash($password, PASSWORD_DEFAULT);

        $sql = "INSERT INTO user (username, email, password) VALUES (?, ?, ?)";

        if ($stmt = $conn->prepare($sql)) {
            $stmt->bind_param("sss", $username, $email, $hashed_password);

            if ($stmt->execute()) {
                // Registration successful
                $success = "Registration successful! You can now <a href='login.php'>login</a>.";
                exit();
            } else {
                if ($conn->errno == 1062) {
                    $error = "This email address is already registered.";
                } else {
                    $error = "Database error: " . $conn->error;
                }
            }
            $stmt->close();
        }else {
            $error = "Database error: " . $conn->error;
        }
    }     
    $conn->close();
     
}

?>

<div class="max-w-md mx-auto mt-16 mb-20">
    <div class="bg-tech-medium shadow-2xl border-2 border-tech-dark p-10">
        <h2 class="text-4xl font-heading text-tech-neon mb-8 tracking-widest text-center">NEW USER</h2>
        
        <form action="register.php" method="POST" class="space-y-5">
            <?php if ($error !== ""): ?><div class="bg-red-900/50 border-l-4 border-red-500 text-red-300 p-4 text-sm tracking-wider"><?php echo $error; ?></div><?php endif; ?>
            <?php if ($success !== ""): ?><div class="bg-green-900/50 border-l-4 border-green-500 text-green-300 p-4 text-sm tracking-wider"><?php echo $success; ?></div><?php endif; ?>

            <div><label class="block text-tech-accent text-sm font-bold mb-2 uppercase tracking-widest">Username</label><input class="w-full px-4 py-3 bg-tech-dark border-2 border-tech-medium text-tech-light focus:border-tech-neon focus:outline-none transition duration-300" type="text" name="username" required value="<?php echo isset($username) ? htmlspecialchars($username) : ''; ?>"></div>
            <div><label class="block text-tech-accent text-sm font-bold mb-2 uppercase tracking-widest">Email</label><input class="w-full px-4 py-3 bg-tech-dark border-2 border-tech-medium text-tech-light focus:border-tech-neon focus:outline-none transition duration-300" type="email" name="email" required value="<?php echo isset($email) ? htmlspecialchars($email) : ''; ?>"></div>
            <div><label class="block text-tech-accent text-sm font-bold mb-2 uppercase tracking-widest">Password</label><input class="w-full px-4 py-3 bg-tech-dark border-2 border-tech-medium text-tech-light focus:border-tech-neon focus:outline-none transition duration-300" type="password" name="password" required></div>
            <div><label class="block text-tech-accent text-sm font-bold mb-2 uppercase tracking-widest">Confirm</label><input class="w-full px-4 py-3 bg-tech-dark border-2 border-tech-medium text-tech-light focus:border-tech-neon focus:outline-none transition duration-300" type="password" name="password_confirm" required></div>

            <button class="w-full bg-tech-neon text-tech-dark font-heading text-xl py-4 hover:bg-white transition duration-300 tracking-widest mt-8" type="submit">INITIALIZE</button>
        </form>
    </div>
</div>
<?php include 'includes/footer.php'; ?>