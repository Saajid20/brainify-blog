<?php
session_start();
include 'includes/db.php';
include 'includes/header.php';

$error = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $email = trim($_POST['email']);
    $password = trim($_POST['password']);
    
    if (empty($email) || empty($password)) {
        $error = "Please enter both email and password.";
    } else {
       
        $sql = "SELECT id, username, password, role FROM user WHERE email = ?";
        if ($stmt = $conn->prepare($sql)) {
            $stmt->bind_param("s", $email);
            $stmt->execute();
            $stmt->store_result();
            if ($stmt->num_rows == 1) {
              
                $stmt->bind_result($id, $username, $hashed_password, $role);
                $stmt->fetch();
                if (password_verify($password, $hashed_password)) {
                    $_SESSION["loggedin"] = true;
                    $_SESSION["id"] = $id;
                    $_SESSION["username"] = $username;
                    $_SESSION["role"] = $role; //added $role to bind 
                    header("Location: index.php");
                    exit();
                } else {
                    $error = "Invalid password.";
                }
            } else {
                $error = "No account found with tha t email.";
            }
            $stmt->close();
        }
    }
    $conn->close();
}
?>

<div class="max-w-md mx-auto mt-20">
    <div class="bg-tech-medium shadow-2xl border-2 border-tech-dark rounded-none p-10">
        <h2 class="text-4xl font-heading text-tech-neon mb-8 tracking-widest text-center">ACCESS TERMINAL</h2>
        
        <form action="login.php" method="POST" class="space-y-6">
            <?php if ($error !== ""): ?>
                <div class="bg-red-900/50 border-l-4 border-red-500 text-red-300 p-4 text-sm tracking-wider">
                    <span class="font-bold mr-2">ERROR:</span><?php echo $error; ?>
                </div>
            <?php endif; ?>

            <div>
                <label class="block text-tech-accent text-sm font-bold mb-2 uppercase tracking-widest">Email</label>
                <input class="w-full px-4 py-3 bg-tech-dark border-2 border-tech-medium text-tech-light focus:border-tech-neon focus:outline-none transition duration-300 placeholder-tech-medium/50" type="email" name="email" required placeholder="USER@NET.COM">
            </div>

            <div>
                <label class="block text-tech-accent text-sm font-bold mb-2 uppercase tracking-widest">Password</label>
                <input class="w-full px-4 py-3 bg-tech-dark border-2 border-tech-medium text-tech-light focus:border-tech-neon focus:outline-none transition duration-300 placeholder-tech-medium/50" type="password" name="password" required placeholder="••••••••">
            </div>

            <button class="w-full bg-tech-neon text-tech-dark font-heading text-xl py-4 hover:bg-white transition duration-300 tracking-widest mt-8" type="submit">
                AUTHENTICATE
            </button>

            <div class="text-center text-tech-accent text-sm mt-6 tracking-wider">
                NO CREDENTIALS? <a class="text-tech-neon hover:underline ml-2" href="register.php">INITIALIZE USER</a>
            </div>
        </form>
    </div>
</div>
<?php include 'includes/footer.php'; ?>