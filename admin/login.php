<?php
// Admin Sign in
session_start();


require_once __DIR__ . '/../inc/db.php';

// Check admin
if (isset($_SESSION['user_id'])) {
    header("Location: dashboard.php");
    exit;
}

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email'] ?? '');
    $password = trim($_POST['password'] ?? '');

    if (!empty($email) && !empty($password)) {
        try {
            // Find email depends user
            $stmt = $pdo->prepare("SELECT * FROM user WHERE email = :email LIMIT 1");
            $stmt->execute([':email' => $email]);
            $user = $stmt->fetch(PDO::FETCH_ASSOC);

            // Get password hash from database
            $db_hashed_password = $user['password_hash'] ?? null;

            // Verify password
            if ($user && $db_hashed_password && password_verify($password, $db_hashed_password)) {

                // Set session variables for logged-in user
                $_SESSION['user_id']  = $user['user_id'];
                $_SESSION['username'] = $user['username'] ?? $user['name'] ?? 'Admin';
                $_SESSION['role']     = $user['role'] ?? 'admin';

                header("Location: dashboard.php");
                exit;
            } else {
                $error = 'Invalid email or password!';
            }
        } catch (PDOException $e) {
            $error = 'Database error: ' . $e->getMessage();
        }
    } else {
        $error = 'Please fill in all fields!';
    }
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Sign In - Vision College</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>

<body class="bg-light d-flex align-items-center justify-content-center vh-100">

    <div class="card shadow border-0 rounded-3" style="width: 100%; max-width: 400px;">
        <div class="card-body p-4 text-center">

            <img src="../image/VCLC_Light Blue.png" alt="Vision College Logo" width="140" class="mb-3">
            <h4 class="fw-bold mb-4" style="color: var(--vc-navy-blue);">ADMIN SIGN IN</h4>

            <?php if (!empty($error)): ?>
                <div class="alert alert-danger py-2 small text-start" role="alert">
                    <?php echo htmlspecialchars($error); ?>
                </div>
            <?php endif; ?>

            <form action="login.php" method="POST" class="text-start">
                <div class="mb-3">
                    <label for="email" class="form-label fw-medium small">Email</label>
                    <input type="email" class="form-control" id="email" name="email" required autofocus>
                </div>

                <div class="mb-4">
                    <label for="password" class="form-label fw-medium small">Password</label>
                    <input type="password" class="form-control" id="password" name="password" required>
                </div>

                <button type="submit" class="btn btn-primary w-100 fw-bold py-2 shadow-sm" style="border: none;">
                    Sign In
                </button>
            </form>

            <div class="mt-4 pt-3 border-top">
                <a href="../index.php" class="text-decoration-none small text-muted">&larr; Back to Website</a>
            </div>

        </div>
    </div>

</body>

</html>