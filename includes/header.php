<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (!isset($baseUrl)) {
    $docRoot = rtrim(str_replace('\\', '/', realpath($_SERVER['DOCUMENT_ROOT'])), '/');
    $projectRoot = rtrim(str_replace('\\', '/', realpath(__DIR__ . '/..')), '/');
    $baseUrl = str_replace($docRoot, '', $projectRoot);
    if ($baseUrl === '') $baseUrl = '';
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>GlamEssentials - Professional Salon Supplies</title>
    <link rel="stylesheet" href="<?= htmlspecialchars($baseUrl) ?>/includes/style/style.css">
    <?php if (!empty($pageCss)): ?>
        <link rel="stylesheet" href="<?= htmlspecialchars($baseUrl) ?>/includes/style/<?= htmlspecialchars($pageCss) ?>">
    <?php endif; ?>
    <script src="<?= htmlspecialchars($baseUrl) ?>/script.js" defer></script>
</head>
<body>

<!-- 🌸 Top Banner -->
<div class="top-banner">
    <div class="banner-content">
        <span class="banner-text">SIGN UP NOW TO START SHOPPING</span>
        <span class="banner-text">EXCLUSIVE DEALS OFFERED BY GLAMESSENTIALS</span>
        <span class="banner-text">SIGN UP NOW TO START SHOPPING</span>
        <span class="banner-text">EXCLUSIVE DEALS OFFERED BY GLAMESSENTIALS</span>
    </div>
</div>

<!-- 🖤 Header -->
<header class="header-container">
    <div class="header-main">
        <!-- Logo -->
        <a href="<?= htmlspecialchars($baseUrl) ?>/index.php" class="logo">
            <img src="<?= htmlspecialchars($baseUrl) ?>/assets/logo1.png" alt="GlamEssentials" class="logo-img">
        </a>

        <!-- Navigation -->
        <nav class="nav-container" id="mobile-nav">
            <ul class="main-nav">
                <li><a href="<?= htmlspecialchars($baseUrl) ?>/index.php" class="nav-link">Home</a></li>
                <li><a href="<?= htmlspecialchars($baseUrl) ?>/products.php" class="nav-link">Products</a></li>
                <li><a href="<?= htmlspecialchars($baseUrl) ?>/about.php" class="nav-link">About</a></li>
                <li><a href="<?= htmlspecialchars($baseUrl) ?>/faq.php" class="nav-link">FAQ</a></li>

                <?php if (isset($_SESSION['userId'])): ?>
                    <?php if ($_SESSION['role'] === 'admin'): ?>
                        <li class="nav-item dropdown">
                            <a href="#" class="nav-link">Admin</a>
                            <ul class="dropdown-menu">
                                <li><a href="<?= htmlspecialchars($baseUrl) ?>/admin/orders.php">Manage Orders</a></li>
                                <li><a href="<?= htmlspecialchars($baseUrl) ?>/admin/users.php">Manage Users</a></li>
                                <li><a href="<?= htmlspecialchars($baseUrl) ?>/item/index.php">Manage Items</a></li>
                            </ul>
                        </li>
                        <li class="mobile-only-nav-item">
                            <a href="<?= htmlspecialchars($baseUrl) ?>/user/logout.php" class="nav-link">
                                <span class="nav-icon">👤</span> Logout
                            </a>
                        </li>
                    <?php else: ?>
                        <li class="nav-item dropdown">
                            <a href="#" class="nav-link">My Account</a>
                            <ul class="dropdown-menu">
                                <li><a href="<?= htmlspecialchars($baseUrl) ?>/user/profile.php">Profile</a></li>
                                <li><a href="<?= htmlspecialchars($baseUrl) ?>/user/myorders.php">My Orders</a></li>
                                <li><a href="<?= htmlspecialchars($baseUrl) ?>/cart/view.php">My Cart</a></li>
                            </ul>
                        </li>
                        <li class="mobile-only-nav-item">
                            <a href="<?= htmlspecialchars($baseUrl) ?>/user/logout.php" class="nav-link">
                                <span class="nav-icon">👤</span> Logout
                            </a>
                        </li>
                    <?php endif; ?>
                <?php else: ?>
                    <li class="mobile-only-nav-item">
                        <a href="<?= htmlspecialchars($baseUrl) ?>/user/login.php" class="nav-link">
                            <span class="nav-icon">👤</span> Sign In
                        </a>
                    </li>
                <?php endif; ?>
                
                <li class="mobile-only-nav-item">
                    <a href="<?= htmlspecialchars($baseUrl) ?>/cart/view.php" class="nav-link">
                        <span class="nav-icon">🛒</span> Shopping Cart
                    </a>
                </li>
            </ul>
        </nav>

        <!-- Header Actions (Desktop Only) -->
        <div class="header-actions desktop-only">
            <!-- Icon Buttons -->
            <button class="icon-btn" aria-label="Search">
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" 
                        d="M21 21l-5.197-5.197m0 0A7.5 7.5 0 
                        105.196 5.196a7.5 7.5 0 
                        0010.607 10.607z" />
                </svg>
            </button>
            <button class="icon-btn" aria-label="Shopping Cart">
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" 
                    viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" 
                        d="M15.75 10.5V6a3.75 3.75 0 
                        10-7.5 0v4.5m11.356-1.993l1.263 
                        12c.07.665-.45 1.243-1.119 
                        1.243H4.25a1.125 1.125 0 
                        01-1.12-1.243l1.264-12A1.125 
                        1.125 0 015.513 
                        7.5h12.974c.576 0 1.059.435 
                        1.119 1.007zM8.625 
                        10.5a.375.375 0 
                        11-.75 0 .375.375 0 
                        01.75 0zm7.5 
                        0a.375.375 0 11-.75 0 .375.375 0 
                        01.75 0z" />
                </svg>
            </button>

            <!-- Account -->
            <?php if (!isset($_SESSION['userId'])): ?>
                <a href="<?= htmlspecialchars($baseUrl) ?>/user/login.php" class="sign-in-btn">Sign In</a>
            <?php else: ?>
                <span class="user-email"><?= htmlspecialchars($_SESSION['email']) ?></span>
                <a href="<?= htmlspecialchars($baseUrl) ?>/user/logout.php" class="sign-in-btn">Logout</a>
            <?php endif; ?>
        </div>

        <!-- Mobile Menu Button -->
        <button class="hamburger-btn" aria-label="Menu">
            <span class="hamburger-bar"></span>
            <span class="hamburger-bar"></span>
            <span class="hamburger-bar"></span>
        </button>
    </div>
</header>