<?php

/**
 * Description: Where customer will send questions to admin.
 * Author: An Bao Le
 */
?>
<?php
require_once __DIR__ . '/inc/db.php';
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$success_msg = $_SESSION['success_msg'] ?? "";
unset($_SESSION['success_msg']);
$errors = [];
// prevent f5 to send again data to database

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $full_name = trim($_POST['full_name'] ?? $_POST['name'] ?? '');
    $email     = trim($_POST['email'] ?? '');
    $message   = trim($_POST['message'] ?? '');

    // --- phone number ---
    $raw_phone = trim($_POST['phone_raw'] ?? $_POST['phone'] ?? ''); // phone from plugin input
    $phone = '';

    if (!empty($raw_phone)) {
        // Delete space, hyphen
        $clean_phone = preg_replace('/[\s\-\(\)]+/', '', $raw_phone);

        // delete 0 in the start
        if (str_starts_with($clean_phone, '0')) {
            $clean_phone = substr($clean_phone, 1);
        }

        // check country
        if (!str_starts_with($clean_phone, '+')) {
            $phone = '+64' . $clean_phone;
        } else {
            $phone = $clean_phone;
        }
    }
    // ----------------------------------------------

    // --- validation full name ---
    if (empty($full_name)) {
        $errors['full_name'] = "Please enter your full name.";
    } elseif (mb_strlen($full_name, 'UTF-8') > 100) {
        $errors['full_name'] = "Full name cannot exceed 100 characters.";
    }

    if (empty($email)) {
        $errors['email'] = "Please enter your email address.";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors['email'] = "Please enter a valid email address.";
    }

    if (empty($message)) {
        $errors['message'] = "Please enter your message.";
    }

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

            $_SESSION['success_msg'] = "Your message has been sent successfully!";
            header("Location: contact.php");
            exit();
        } catch (PDOException $e) {
            $errors['db'] = "Database error: " . $e->getMessage();
        }
    }
}
require_once __DIR__ . '/inc/header.html';
?>
<!--breadcrumb-->
<div class="bg-light py-2 border-bottom">
    <div class="container">
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb mb-0 small">
                <li class="breadcrumb-item"><a href="index.php" class="text-decoration-none">Home</a></li>
                <li class="breadcrumb-item active text-dark" aria-current="page">Contact us</li>
            </ol>
        </nav>
    </div>
</div>
<main class="flex-grow-1 py-5">
    <div class="container">
        <!-- Notification Alert -->
        <?php if (!empty($success_msg)): ?>
            <div class="alert alert-success text-center max-w-700 mx-auto mb-4"><?php echo htmlspecialchars($success_msg); ?></div>
        <?php endif; ?>

        <?php if (!empty($errors)): ?>
            <div class="alert alert-danger text-center max-w-700 mx-auto mb-4">
                <ul class="mb-0 list-unstyled">
                    <?php foreach ($errors as $error): ?>
                        <li><?php echo htmlspecialchars($error); ?></li>
                    <?php endforeach; ?>
                </ul>
            </div>
        <?php endif; ?>

        <div class="text-center mb-5">
            <h1 class="fw-bold">CONTACT US</h1>
            <p class="fs-5">Have a question? Please send a message to our team.</p>
        </div>

        <div class="row g-4 align-items-stretch">
            <div class="col-lg-6">
                <div class="p-4 bg-white rounded shadow-sm h-100 border">
                    <h2 class="fw-bold mb-4">Send us a message</h2>
                    <form action="contact.php" method="POST" id="contactForm">
                        <div class="mb-3">
                            <label for="full_name" class="form-label fw-semibold">Full Name <span class="text-danger">*</span></label>
                            <input type="text" name="full_name" id="full_name" class="form-control" placeholder="John Doe" minlength="1" maxlength="100" required value="<?php echo htmlspecialchars($_POST['full_name'] ?? ''); ?>">
                        </div>
                        <div class="mb-3">
                            <label for="email" class="form-label fw-semibold">Email <span class="text-danger">*</span></label>
                            <input type="email" name="email" id="email" class="form-control" placeholder="example@gmail.com" required value="<?php echo htmlspecialchars($_POST['email'] ?? ''); ?>">
                        </div>
                        <div class="mb-3">
                            <label for="phone" class="form-label fw-semibold d-block">Phone (Optional)</label>
                            <input type="tel" id="phone" name="phone_raw" class="form-control w-100" placeholder="021 123 4567" value="<?php echo htmlspecialchars($_POST['phone_raw'] ?? ''); ?>">
                        </div>
                        <div class="mb-3">
                            <label for="message" class="form-label fw-semibold">Message <span class="text-danger">*</span></label>
                            <textarea name="message" class="form-control" id="message" rows="4" placeholder="Enter your message" required><?php echo htmlspecialchars($_POST['message'] ?? ''); ?></textarea>
                        </div>
                        <button type="submit" class="btn btn-primary w-100 fw-bold py-2">SEND MESSAGE</button>
                    </form>
                </div>
            </div>

            <div class="col-lg-6">
                <div class="p-4 bg-white rounded shadow-sm h-100 border d-flex flex-column">
                    <h2 class="fw-bold mb-3">Our Location</h2>
                    <p class="text-muted mb-3">
                        <i class="fi fi-rr-marker me-2"></i>21 Ruakura Road, Hamilton East, Hamilton 3216
                    </p>
                    <div class="flex-grow-1 rounded overflow-hidden shadow-sm border" style="min-height: 320px;">
                        <iframe src="https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d1576.7345956101842!2d175.2991019653364!3d-37.77904272703472!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x6d6d18c267f747ab%3A0x32ae8d4c80ea3728!2sVision%20College!5e0!3m2!1sen!2snz!4v1789284312325!5m2!1sen!2snz" width="100%" height="100%" style="border:0;" allowfullscreen="" loading="lazy"></iframe>
                    </div>
                </div>
            </div>
        </div>
    </div>
</main>

<?php require_once('./inc/footer.html'); ?>