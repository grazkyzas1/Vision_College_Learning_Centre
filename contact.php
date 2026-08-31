<?php
// Blogs Page
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once __DIR__ . '/inc/db.php';
require_once __DIR__ . '/inc/header.php';

$success_msg = "";
$error_msg = "";

// do when send message to database
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $full_name = $_POST['full_name'] ?? '';
    $email = $_POST['email'] ?? '';
    $phone = $_POST['phone'] ?? '';
    $message = $_POST['message'] ?? '';
    // test empty values
    if (!empty($full_name) && !empty($email) && !empty($message)) {
        try {
            // Insert message in database
            $sql = "INSERT INTO contact_message (full_name, email, phone, message) VALUES (:name, :email, :phone, :message)";
            $stmt = $pdo->prepare($sql);
            $stmt->bindParam(':name', $full_name);
            $stmt->bindParam(':email', $email);
            $stmt->bindParam(':phone', $phone);
            $stmt->bindParam(':message', $message);
            $stmt->execute();
            $success_msg = "Your message has been sent successfully!";
        } catch (PDOException $e) {
            $error_msg = "Error: " . $e->getMessage();
        }
    } else {
        $error_msg = "Please fill in all required fields.";
    }
}
?>
<!-- form notification -->
<?php if (!empty($success_msg)): ?>
    <div class="alert alert-success text-start"><?php echo $success_msg; ?></div>
<?php endif; ?>
<?php if (!empty($error_msg)): ?>
    <div class="alert alert-danger text-start"><?php echo $error_msg; ?></div>
<?php endif; ?>

<!-- Page Header -->
<section class="py-4 bg-light border-bottom mb-5">
    <div class="container text-center">
        <h2 class="fw-bold mb-2" style="letter-spacing: 1.5px; color: var(--vc-navy-blue);">CONTACT US</h2>
        <p class="fs-5 mb-0">Have a question? Please send a question to our team.</p>
    </div>
</section>

<form method="POST">
    <div class="mb-3" style="max-width: 700px; margin: 0 auto;">
        <label for="full_name" class="form-label">Full Name</label>
        <input type="text" class="form-control" id="full_name" name="full_name" placeholder="Enter your full name" style="background-color: var(--bg-light);">
    </div>

    <div class="mb-3" style="max-width: 700px; margin: 0 auto;">
        <label for="email" class="form-label">Email</label>
        <input type="email" class="form-control" id="email" name="email" placeholder="Enter your email" style="background-color: var(--bg-light);">
    </div>

    <div class="mb-3" style="max-width: 700px; margin: 0 auto;">
        <label for="phone" class="form-label">Phone (Optional)</label>
        <input type="text" class="form-control" id="phone" name="phone" placeholder="Enter your phone number" style="background-color: var(--bg-light);">
    </div>

    <div class="mb-3" style="max-width: 700px; margin: 0 auto;">
        <label for="message" class="form-label">Message</label>
        <textarea class="form-control" id="message" name="message" rows="5" placeholder="Enter your message" style="background-color: var(--bg-light); color: var(--black);"></textarea>
    </div>

    <button type="submit" class="btn btn-primary btn-lg px-4 py-2 fw-bold rounded-1 shadow-sm my-5" style="max-width: 300px; margin: 0 auto; display: block;">
        SEND MESSAGE
    </button>
</form>
<?php require_once('./inc/footer.php'); ?>