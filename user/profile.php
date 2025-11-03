<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once("../includes/config.php");

// Redirect if not logged in
if (!isset($_SESSION['userId'])) {
    header('Location: login.php');
    exit();
}

$pageCss = 'profile.css';
include("../includes/header.php");

// Get current user data
$current_user = null;
$customers_data = null;
if (isset($_SESSION['userId'])) {
    $user_id = $_SESSION['userId'];
    $user_sql = "SELECT * FROM users WHERE user_id = ?";
    $user_stmt = mysqli_prepare($conn, $user_sql);
    mysqli_stmt_bind_param($user_stmt, "i", $user_id);
    mysqli_stmt_execute($user_stmt);
    $user_result = mysqli_stmt_get_result($user_stmt);
    $current_user = mysqli_fetch_assoc($user_result);
    mysqli_stmt_close($user_stmt);
    
    // Get customers data if exists
    $customers_sql = "SELECT * FROM customers WHERE user_id = ?";
    $customers_stmt = mysqli_prepare($conn, $customers_sql);
    mysqli_stmt_bind_param($customers_stmt, "i", $user_id);
    mysqli_stmt_execute($customers_stmt);
    $customers_result = mysqli_stmt_get_result($customers_stmt);
    $customers_data = mysqli_fetch_assoc($customers_result);
    mysqli_stmt_close($customers_stmt);
}

// Handle profile data submission
if (isset($_POST['submit'])) {
    $lname = trim($_POST['lname']);
    $fname = trim($_POST['fname']);
    $title = trim($_POST['title']);
    $address = trim($_POST['address']);
    $town = trim($_POST['town']);
    $zipcode = trim($_POST['zipcode']);
    $phone = trim($_POST['phone']);
    $user_id = $_SESSION['userId'];

    if ($customers_data) {
        // Update existing customerss data
        $sql = "UPDATE customers SET title=?, lname=?, fname=?, addressline=?, town=?, zipcode=?, phone=? WHERE user_id=?";
        $stmt = mysqli_prepare($conn, $sql);
        mysqli_stmt_bind_param($stmt, "sssssssi", $title, $lname, $fname, $address, $town, $zipcode, $phone, $user_id);
        $result = mysqli_stmt_execute($stmt);
        mysqli_stmt_close($stmt);
    } else {
        // Insert new customers data
        $sql = "INSERT INTO customers (user_id, title, lname, fname, addressline, town, zipcode, phone) VALUES (?, ?, ?, ?, ?, ?, ?, ?)";
        $stmt = mysqli_prepare($conn, $sql);
        mysqli_stmt_bind_param($stmt, "isssssss", $user_id, $title, $lname, $fname, $address, $town, $zipcode, $phone);
        $result = mysqli_stmt_execute($stmt);
        mysqli_stmt_close($stmt);
    }

    if ($result) {
        $_SESSION['success'] = 'Profile updated successfully!';
        header("Location: profile.php");
        exit();
    } else {
        $_SESSION['error'] = 'Failed to update profile.';
    }
}

// Handle user account update
if (isset($_POST['update_user'])) {
    $name = trim($_POST['name']);
    $email = trim($_POST['email']);
    $user_id = $_SESSION['userId'];

    $sql = "UPDATE users SET name=?, email=? WHERE user_id=?";
    $stmt = mysqli_prepare($conn, $sql);
    mysqli_stmt_bind_param($stmt, "ssi", $name, $email, $user_id);
    $result = mysqli_stmt_execute($stmt);
    mysqli_stmt_close($stmt);

    if ($result) {
        $_SESSION['success'] = 'Account information updated successfully!';
        $_SESSION['email'] = $email; // Update session email
        header("Location: profile.php");
        exit();
    } else {
        $_SESSION['error'] = 'Failed to update account information.';
    }
}

// Handle account deletion
if (isset($_POST['delete_account'])) {
    $user_id = $_SESSION['userId'];
    
    // Delete customers data first
    $customers_sql = "DELETE FROM customers WHERE user_id = ?";
    $customers_stmt = mysqli_prepare($conn, $customers_sql);
    mysqli_stmt_bind_param($customers_stmt, "i", $user_id);
    mysqli_stmt_execute($customers_stmt);
    mysqli_stmt_close($customers_stmt);
    
    // Delete user account
    $user_sql = "DELETE FROM users WHERE user_id = ?";
    $user_stmt = mysqli_prepare($conn, $user_sql);
    mysqli_stmt_bind_param($user_stmt, "i", $user_id);
    $result = mysqli_stmt_execute($user_stmt);
    mysqli_stmt_close($user_stmt);
    
    if ($result) {
        session_destroy();
        header("Location: ../index.php");
        exit();
    } else {
        $_SESSION['error'] = 'Failed to delete account.';
    }
}

// Handle image upload
if (isset($_POST['upload_image'])) {
    if (isset($_FILES['profile_image']) && $_FILES['profile_image']['error'] == 0) {
        $file = $_FILES['profile_image'];
        
        // Validate file type
        $allowed_types = ['image/jpeg', 'image/jpg', 'image/png'];
        if (in_array($file['type'], $allowed_types)) {
            // Validate file size (5MB max)
            if ($file['size'] <= 5 * 1024 * 1024) {
                $file_extension = pathinfo($file['name'], PATHINFO_EXTENSION);
                $new_filename = 'user_' . $_SESSION['userId'] . '_' . time() . '.' . $file_extension;
                $target_path = 'images/' . $new_filename;
                
                if (move_uploaded_file($file['tmp_name'], $target_path)) {
                    // Update user's img_path in database
                    $update_sql = "UPDATE users SET img_path = ? WHERE user_id = ?";
                    $update_stmt = mysqli_prepare($conn, $update_sql);
                    mysqli_stmt_bind_param($update_stmt, "si", $target_path, $_SESSION['userId']);
                    
                    if (mysqli_stmt_execute($update_stmt)) {
                        $_SESSION['success'] = 'Profile image updated successfully!';
                        // Refresh user data
                        $user_sql = "SELECT * FROM users WHERE user_id = ?";
                        $user_stmt = mysqli_prepare($conn, $user_sql);
                        mysqli_stmt_bind_param($user_stmt, "i", $_SESSION['userId']);
                        mysqli_stmt_execute($user_stmt);
                        $user_result = mysqli_stmt_get_result($user_stmt);
                        $current_user = mysqli_fetch_assoc($user_result);
                        mysqli_stmt_close($user_stmt);
                    } else {
                        $_SESSION['error'] = 'Failed to update profile image in database.';
                    }
                    mysqli_stmt_close($update_stmt);
                } else {
                    $_SESSION['error'] = 'Failed to upload image.';
                }
            } else {
                $_SESSION['error'] = 'File size too large. Maximum 5MB allowed.';
            }
        } else {
            $_SESSION['error'] = 'Invalid file type. Only JPG, JPEG, and PNG files are allowed.';
        }
    } else {
        $_SESSION['error'] = 'Please select an image file.';
    }
    header("Location: profile.php");
    exit();
}
?>

<div class="profile-container">
    <!-- Profile Header -->
    <div class="profile-header">
        <div class="profile-picture">
            <?php 
            $profile_image = $current_user['img_path'] ?? 'images/default-avatar.png';
            ?>
            <img src="<?php echo htmlspecialchars($profile_image); ?>" alt="Profile Picture">
        </div>
    </div>

    <!-- Alert Messages -->
    <?php if (isset($_SESSION['success'])): ?>
        <div class="alert alert-success">
            <?php echo htmlspecialchars($_SESSION['success']); unset($_SESSION['success']); ?>
        </div>
    <?php endif; ?>
    
    <?php if (isset($_SESSION['error'])): ?>
        <div class="alert alert-danger">
            <?php echo htmlspecialchars($_SESSION['error']); unset($_SESSION['error']); ?>
        </div>
    <?php endif; ?>
    <!-- Account page navigation-->
    <nav class="nav nav-borders">
        <a class="nav-link active ms-0" href="https://www.bootdey.com/snippets/view/bs5-edit-profile-account-details" target="__blank">Profile</a>

    </nav>
    <hr class="mt-0 mb-4">
    <div class="profile-grid">
        <!-- Profile Picture Section -->
        <div class="profile-sidebar">
            <div class="profile-card">
                <div class="profile-card-header">Profile Picture</div>
                <div class="profile-card-body">
                    <div class="profile-picture">
                        <?php 
                        $profile_image = $current_user['img_path'] ?? 'images/default-avatar.png';
                        ?>
                        <img src="<?php echo htmlspecialchars($profile_image); ?>" alt="Profile Picture">
                        
                        <!-- Profile picture upload form -->
                        <form action="<?php echo htmlspecialchars($_SERVER['PHP_SELF']); ?>" method="POST" enctype="multipart/form-data" class="mt-3">
                            <input type="file" class="form-control" name="profile_image" accept="image/jpeg,image/jpg,image/png" required>
                            <div class="small text-muted mt-2">JPG or PNG no larger than 5 MB</div>
                            <button type="submit" name="upload_image" class="btn btn-primary">Upload new image</button>
                        </form>
                    </div>
                </div>
            </div>
        </div>

        <!-- Main Content Section -->
        <div class="profile-main">
            <!-- Account details card-->
            <div class="card mb-4">
                <div class="card-header">Account Details</div>
                <div class="card-body">
                    <form action="<?php echo $_SERVER['PHP_SELF']; ?>" method="POST" enctype="multipart/form-data">
                        <h6 class="mb-3">Profile Details</h6>
                        <div class="form-row">
                            <div class="form-group">
                                <label class="form-label" for="inputFirstName">First Name</label>
                                <input class="form-control" id="inputFirstName" type="text" name="fname" value="<?php echo htmlspecialchars($customers_data['fname'] ?? ''); ?>" placeholder="Enter your first name">
                            </div>
                            <div class="form-group">
                                <label class="form-label" for="inputLastName">Last Name</label>
                                <input class="form-control" id="inputLastName" type="text" name="lname" value="<?php echo htmlspecialchars($customers_data['lname'] ?? ''); ?>" placeholder="Enter your last name">
                            </div>
                        </div>

                        <div class="form-row">
                            <div class="form-group">
                                <label class="form-label" for="title">Title</label>
                                <input class="form-control" id="title" type="text" name="title" value="<?php echo htmlspecialchars($customers_data['title'] ?? ''); ?>" placeholder="Mr./Ms./Mrs.">
                            </div>
                            <div class="form-group">
                                <label class="form-label" for="inputPhone">Phone Number</label>
                                <input class="form-control" id="inputPhone" type="tel" name="phone" value="<?php echo htmlspecialchars($customers_data['phone'] ?? ''); ?>" placeholder="Enter your phone number">
                            </div>
                        </div>

                        <div class="form-row">
                            <div class="form-group">
                                <label class="form-label" for="address">Address</label>
                                <input class="form-control" id="address" type="text" name="address" value="<?php echo htmlspecialchars($customers_data['addressline'] ?? ''); ?>" placeholder="Enter your address">
                            </div>
                            <div class="form-group">
                                <label class="form-label" for="town">Town/City</label>
                                <input class="form-control" id="town" type="text" name="town" value="<?php echo htmlspecialchars($customers_data['town'] ?? ''); ?>" placeholder="Enter your town/city">
                            </div>
                        </div>

                        <div class="form-row">
                            <div class="form-group">
                                <label class="form-label" for="zip">ZIP Code</label>
                                <input class="form-control" id="zip" type="text" name="zipcode" value="<?php echo htmlspecialchars($customers_data['zipcode'] ?? ''); ?>" placeholder="Enter ZIP code">
                            </div>
                            <div class="form-group">
                                <!-- Intentionally empty to maintain grid alignment -->
                            </div>
                        </div>

                        <!-- Action buttons -->
                        <div class="d-flex gap-2">
                            <button class="btn btn-primary" type="submit" name="submit">Save changes</button>
                            <button class="btn btn-danger" type="submit" name="delete_account" onclick="return confirm('Are you sure you want to delete your account? This action cannot be undone!')">Delete Account</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>