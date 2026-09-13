<?php
require_once __DIR__ . '/inc/db.php';
// create session to save message
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
// get session successfully and delete immediately to prevent f5
$success_msg = $_SESSION['success_msg'] ?? "";
unset($_SESSION['success_msg']);
$errors = [];
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $full_name = trim($_POST['full_name'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $phone = trim($_POST['phone'] ?? '');
    $message = trim($_POST['message'] ?? '');
    // validation
    if (empty($full_name)) {
        $errors['full_name'] = "Please enter your full name.";
    }
    if (empty($email)) {
        $errors['email'] = "Please enter your email address.";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) { // check and use filter validate email to check @, .com, .ac.nz, ...
        $errors['email'] = "Please enter a valid email address.";
    }
    if (empty($message)) {
        $errors['message'] = "Please enter your message.";
    }
    // insert when no errors
    if (empty($errors)) {
        try {
            $sql = "INSERT INTO contact_message (full_name, email, phone, message) 
                    VALUES (:name, :email, :phone, :message)";
            $stmt = $pdo->prepare($sql);
            $stmt->bindParam(':name', $full_name);
            $stmt->bindParam(':email', $email);
            $stmt->bindParam(':phone', $phone);
            $stmt->bindParam(':message', $message);
            $stmt->execute();
            // save 
            $_SESSION['success_msg'] = "Your message has been sent successfully!";
            // move to prevent f5
            header("Location: contact.php");
            exit();
        } catch (PDOException $e) {
            $errors['db'] = "Database error: " . $e->getMessage();
        }
    }
}
require_once __DIR__ . '/inc/header.html';
?>
<main class="flex-grow-1 py-5">
    <div class="container">
        <!--title-->
        <div class="text-center mb-5">
            <h1 class="fw-bold text-primary">CONTACT US</h1>
            <p class="text-muted">Have a question? Please send a message to our team.</p>
        </div>
        <div class="row g-4 align-items-stretch">
            <!--left: contact-->
            <div class="col-lg-6">
                <div class="p-4 bg-white rounded shadow-sm h-100 border">
                    <h4 class="fw-bold mb-4">Send us a message</h4>
                    <form action="contact.php" method="POST">
                        <div class="mb-3">
                            <label class="form-label fw-semibold">Full Name <span class="text-danger">*</span></label>
                            <input type="text" name="name" class="form-control" placeholder="Enter your full name" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label fw-semibold">Email <span class="text-danger">*</span></label>
                            <input type="email" name="email" class="form-control" placeholder="Enter your email" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label fw-semibold">Phone (Optional)</label>
                            <input type="tel" name="phone" class="form-control" placeholder="Enter your phone number">
                        </div>
                        <div class="mb-3">
                            <label class="form-label fw-semibold">Message <span class="text-danger">*</span></label>
                            <textarea name="message" class="form-control" rows="4" placeholder="Enter your message" required></textarea>
                        </div>
                        <button type="submit" class="btn btn-primary w-100 fw-bold py-2">SEND MESSAGE</button>
                    </form>
                </div>
            </div>
            <!--right: map-->
            <div class="col-lg-6">
                <div class="p-4 bg-white rounded shadow-sm h-100 border d-flex flex-column">
                    <h4 class="fw-bold mb-3">Our Location</h4>
                    <p class="text-muted mb-3">
                        <i class="fi fi-rr-marker me-2"></i>21 Ruakura Road, Hamilton East, Hamilton 3216
                    </p>
                    <!--use gg map-->
                    <div class="flex-grow-1 rounded overflow-hidden shadow-sm border" style="min-height: 320px;">
                        <!-- orderflow-hidden help to sure map inside div -->
                        <iframe src="https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d1576.7345956101842!2d175.2991019653364!3d-37.77904272703472!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x6d6d18c267f747ab%3A0x32ae8d4c80ea3728!2sVision%20College!5e0!3m2!1sen!2snz!4v1789284312325!5m2!1sen!2snz" width="600" height="450" style="border:0;" allowfullscreen="" loading="lazy" referrerpolicy="strict-origin-when-cross-origin"></iframe>
                    </div>
                </div>
            </div>
        </div>
    </div>
</main>
<?php require_once('./inc/footer.html'); ?>