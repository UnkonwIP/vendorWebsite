<?php
// Admin sidebar include. Edit this file to change sidebar across admin pages.
// Assumes the including page has already started session and loaded any required CSS.
?>
<aside class="sidebar">
    <div class="cp-main-menu__container">
        <div class="cp-main-menu__logo-container">
            <a href="AdminHome.php" title="Home" class="sidebar-brand">
                <img src="Image/company%20logo.png" alt="logo" style="height:34px; display:inline-block; margin-right:10px;">
                <span class="sidebar-brand-text">Admin Panel</span>
            </a>
        </div>
        <ul class="links" id="cp-main-menu__link-list">
            <li class="list-item">
                <a class="list-item__link" href="AdminHome.php">
                    <span class="list-item__icon" aria-hidden="true">
                        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg"><path d="M3 11L12 3l9 8v8a1 1 0 0 1-1 1h-5v-6H9v6H4a1 1 0 0 1-1-1v-8z" fill="var(--cp-white)"></path></svg>
                    </span>
                    <span class="list-item__text">Home</span>
                </a>
            </li>
            <li class="list-item">
                <a class="list-item__link" href="AdminVendorManagement.php">
                    <span class="list-item__icon" aria-hidden="true">
                        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg"><circle cx="12" cy="7" r="4" fill="var(--cp-white)"></circle><path d="M4 21c0-4 4-6 8-6s8 2 8 6v1H4v-1z" fill="var(--cp-white)"></path></svg>
                    </span>
                    <span class="list-item__text">Vendor Management</span>
                </a>
            </li>
            <li class="list-item">
                <?php
                // Choose registration page based on logged-in role: head users go to head registration.
                $regLink = 'AdminRegistrationManagement.php';
                if (isset($_SESSION['role']) && $_SESSION['role'] === 'admin_head') {
                    $regLink = 'AdminHeadRegisrationManagement.php';
                }
                ?>
                <a class="list-item__link" href="<?php echo htmlspecialchars($regLink); ?>">
                    <span class="list-item__icon" aria-hidden="true">
                        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg"><circle cx="12" cy="12" r="9" fill="var(--cp-white)"></circle></svg>
                    </span>
                    <span class="list-item__text">Registration</span>
                </a>
            </li>
            <li class="list-item">
                <a class="list-item__link" href="#" onclick="alert('Development in progress'); return false;">
                    <span class="list-item__icon" aria-hidden="true">
                        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg"><rect x="3" y="4" width="18" height="6" rx="1" fill="var(--cp-white)"></rect><rect x="3" y="14" width="18" height="6" rx="1" fill="var(--cp-white)"></rect></svg>
                    </span>
                    <span class="list-item__text">Procurement</span>
                </a>
            </li>
            <li class="list-item">
                <a class="list-item__link" href="#" onclick="alert('Development in progress'); return false;">
                    <span class="list-item__icon" aria-hidden="true">
                        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg"><path d="M4 7h16v10H4z" fill="var(--cp-white)"></path></svg>
                    </span>
                    <span class="list-item__text">Contract</span>
                </a>
            </li>
        </ul>
    </div>
</aside>
