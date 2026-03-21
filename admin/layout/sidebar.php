<?php
include 'sidebar-data.php';
?>
<!-- Sidebar -->
<aside class="sidebar">
    <div class="sidebar-header">
        <h2><i class="fas fa-layer-group"></i> CMS Panel</h2>
        <p>Content Management System</p>
    </div>
    <nav class="sidebar-menu">
        <!-- Dashboard Section -->
        <div class="menu-section">
            <div class="menu-section-title">Main</div>
            <a href="./dashboard.php" class="menu-item <?= isActive('dashboard.php'); ?>">
                <i class="fas fa-home"></i>
                <span>Dashboard</span>
            </a>
        </div>

        <!-- ================= PRODUCT CATALOG SECTION ================= -->
        <div class="menu-section">
            <div class="menu-section-title">Product Catalog</div>

            <a href="#productMenu" class="menu-item has-submenu <?= isActive(['products.php', 'product-add.php', 'product-edit.php', 'product-variants.php', 'bulk-pricing.php']); ?>" data-toggle="submenu">
                <i class="fas fa-box"></i>
                <span>Products</span>
                <i class="fas fa-chevron-right submenu-icon"></i>
            </a>
            <div class="submenu" id="productMenu">
                <a href="./products.php" class="submenu-item <?= isActive('products.php'); ?>">
                    <i class="fas fa-list"></i>
                    <span>All Products</span>
                    <span class="menu-badge">18</span>
                </a>
                <a href="./product-add.php" class="submenu-item <?= isActive('product-add.php'); ?>">
                    <i class="fas fa-plus-circle"></i>
                    <span>Add Product</span>
                </a>
                <a href="./bulk-pricing.php" class="submenu-item <?= isActive('bulk-pricing.php'); ?>">
                    <i class="fas fa-tags"></i>
                    <span>Bulk Pricing</span>
                </a>
            </div>

            <a href="#categoryMenu" class="menu-item has-submenu <?= isActive(['main-categories.php', 'sub-categories.php']); ?>" data-toggle="submenu">
                <i class="fas fa-sitemap"></i>
                <span>Categories</span>
                <i class="fas fa-chevron-right submenu-icon"></i>
            </a>
            <div class="submenu" id="categoryMenu">
                <a href="./main-categories.php" class="submenu-item <?= isActive('main-categories.php'); ?>">
                    <i class="fas fa-folder"></i>
                    <span>Main Categories</span>
                    <span class="menu-badge">3</span>
                </a>
                <a href="./sub-categories.php" class="submenu-item <?= isActive('sub-categories.php'); ?>">
                    <i class="fas fa-folder-open"></i>
                    <span>Sub Categories</span>
                    <span class="menu-badge">12</span>
                </a>
            </div>

            <a href="./inventory.php" class="menu-item <?= isActive('inventory.php'); ?>">
                <i class="fas fa-warehouse"></i>
                <span>Inventory</span>
                <!-- <span class="menu-badge warning">Low Stock</span> -->
            </a>
        </div>

        <!-- ================= ORDER MANAGEMENT SECTION ================= -->
        <div class="menu-section">
            <div class="menu-section-title">Order Management</div>

            <a href="#orderMenu" class="menu-item has-submenu <?= isActive(['orders.php', 'bulk-orders.php', 'order-details.php']); ?>" data-toggle="submenu">
                <i class="fas fa-shopping-cart"></i>
                <span>Orders</span>
                <i class="fas fa-chevron-right submenu-icon"></i>
                <!-- <span class="menu-badge">12</span> -->
            </a>
            <div class="submenu" id="orderMenu">
                <a href="./orders.php" class="submenu-item <?= isActive('orders.php'); ?>">
                    <i class="fas fa-clock"></i>
                    <span>All Orders</span>
                    <span class="menu-badge">8</span>
                </a>
                <a href="./pending-orders.php" class="submenu-item <?= isActive('pending-orders.php'); ?>">
                    <i class="fas fa-hourglass-half"></i>
                    <span>Pending</span>
                    <span class="menu-badge warning">3</span>
                </a>
                <a href="./processing-orders.php" class="submenu-item <?= isActive('processing-orders.php'); ?>">
                    <i class="fas fa-cog"></i>
                    <span>Processing</span>
                </a>
                <a href="./completed-orders.php" class="submenu-item <?= isActive('completed-orders.php'); ?>">
                    <i class="fas fa-check-circle"></i>
                    <span>Completed</span>
                </a>
                <a href="./cancelled-orders.php" class="submenu-item <?= isActive('cancelled-orders.php'); ?>">
                    <i class="fas fa-times-circle"></i>
                    <span>Cancelled</span>
                </a>
            </div>

            <a href="#bulkOrderMenu" class="menu-item has-submenu <?= isActive(['bulk-inquiries.php', 'bulk-quotations.php']); ?>" data-toggle="submenu">
                <i class="fas fa-boxes"></i>
                <span>Bulk Orders</span> &nbsp;
                <i class="fas fa-chevron-right submenu-icon"></i>
                <!-- <span class="menu-badge">4</span> -->
            </a>
            <div class="submenu" id="bulkOrderMenu">
                <a href="./bulk-inquiries.php" class="submenu-item <?= isActive('bulk-inquiries.php'); ?>">
                    <i class="fas fa-question-circle"></i>
                    <span>Inquiries</span>
                    <span class="menu-badge">4</span>
                </a>
                <a href="./bulk-quotations.php" class="submenu-item <?= isActive('bulk-quotations.php'); ?>">
                    <i class="fas fa-file-invoice"></i>
                    <span>Quotations</span>
                </a>
            </div>
        </div>

        <!-- ================= CUSTOMER MANAGEMENT SECTION ================= -->
        <div class="menu-section">
            <div class="menu-section-title">Customer Management</div>

            <a href="#customerMenu" class="menu-item has-submenu <?= isActive(['customers.php', 'customer-groups.php', 'customer-details.php']); ?>" data-toggle="submenu">
                <i class="fas fa-user-friends"></i>
                <span>Customers</span>
                <i class="fas fa-chevron-right submenu-icon"></i>
                <!-- <span class="menu-badge">156</span> -->
            </a>
            <div class="submenu" id="customerMenu">
                <a href="./customers.php" class="submenu-item <?= isActive('customers.php'); ?>">
                    <i class="fas fa-users"></i>
                    <span>All Customers</span>
                    <span class="menu-badge">142</span>
                </a>
                <a href="./customer-groups.php" class="submenu-item <?= isActive('customer-groups.php'); ?>">
                    <i class="fas fa-layer-group"></i>
                    <span>Customer Groups</span>
                    <span class="menu-badge">4</span>
                </a>
                <a href="./wholesalers.php" class="submenu-item <?= isActive('wholesalers.php'); ?>">
                    <i class="fas fa-store"></i>
                    <span>Wholesalers</span>
                    <span class="menu-badge">14</span>
                </a>
            </div>

            <a href="#addressMenu" class="menu-item has-submenu <?= isActive(['addresses.php']); ?>" data-toggle="submenu">
                <i class="fas fa-map-marker-alt"></i>
                <span>Addresses</span>
                <i class="fas fa-chevron-right submenu-icon"></i>
            </a>
            <div class="submenu" id="addressMenu">
                <a href="./addresses.php" class="submenu-item <?= isActive('addresses.php'); ?>">
                    <i class="fas fa-map-marked-alt"></i>
                    <span>All Addresses</span>
                </a>
                <a href="./pincodes.php" class="submenu-item <?= isActive('pincodes.php'); ?>">
                    <i class="fas fa-map-pin"></i>
                    <span>Serviceable Pincodes</span>
                </a>
            </div>
        </div>

        <!-- ================= MARKETING & PROMOTIONS SECTION ================= -->
        <div class="menu-section">
            <div class="menu-section-title">Marketing</div>

            <a href="#couponMenu" class="menu-item has-submenu <?= isActive(['coupons.php', 'coupon-add.php']); ?>" data-toggle="submenu">
                <i class="fas fa-ticket-alt"></i>
                <span>Coupons</span>
                <i class="fas fa-chevron-right submenu-icon"></i>
                <!-- <span class="menu-badge">5</span> -->
            </a>
            <div class="submenu" id="couponMenu">
                <a href="./coupons.php" class="submenu-item <?= isActive('coupons.php'); ?>">
                    <i class="fas fa-list"></i>
                    <span>All Coupons</span>
                    <span class="menu-badge">5</span>
                </a>
                <a href="./coupon-add.php" class="submenu-item <?= isActive('coupon-add.php'); ?>">
                    <i class="fas fa-plus-circle"></i>
                    <span>Create Coupon</span>
                </a>
            </div>

            <a href="#reviewMenu" class="menu-item has-submenu <?= isActive(['reviews.php']); ?>" data-toggle="submenu">
                <i class="fas fa-star"></i>
                <span>Reviews</span>
                <i class="fas fa-chevron-right submenu-icon"></i>
                <!-- <span class="menu-badge">23</span> -->
            </a>
            <div class="submenu" id="reviewMenu">
                <a href="./reviews.php" class="submenu-item <?= isActive('reviews.php'); ?>">
                    <i class="fas fa-star-half-alt"></i>
                    <span>All Reviews</span>
                    <span class="menu-badge">18</span>
                </a>
                <a href="./pending-reviews.php" class="submenu-item <?= isActive('pending-reviews.php'); ?>">
                    <i class="fas fa-clock"></i>
                    <span>Pending Approval</span>
                    <span class="menu-badge warning">5</span>
                </a>
            </div>

            <a href="./wishlist.php" class="menu-item <?= isActive('wishlist.php'); ?>">
                <i class="fas fa-heart"></i>
                <span>Wishlists</span>
                <!-- <span class="menu-badge">45</span> -->
            </a>
        </div>

        <!-- ================= FINANCE & REPORTING SECTION ================= -->
        <div class="menu-section">
            <div class="menu-section-title">Finance</div>

            <a href="#invoiceMenu" class="menu-item has-submenu <?= isActive(['invoices.php']); ?>" data-toggle="submenu">
                <i class="fas fa-file-invoice-dollar"></i>
                <span>Invoices</span>
                <i class="fas fa-chevron-right submenu-icon"></i>
            </a>
            <div class="submenu" id="invoiceMenu">
                <a href="./invoices.php" class="submenu-item <?= isActive('invoices.php'); ?>">
                    <i class="fas fa-file-invoice"></i>
                    <span>All Invoices</span>
                </a>
                <a href="./credit-notes.php" class="submenu-item <?= isActive('credit-notes.php'); ?>">
                    <i class="fas fa-file-invoice"></i>
                    <span>Credit Notes</span>
                </a>
            </div>

            <a href="#paymentMenu" class="menu-item has-submenu <?= isActive(['payments.php']); ?>" data-toggle="submenu">
                <i class="fas fa-credit-card"></i>
                <span>Payments</span>
                <i class="fas fa-chevron-right submenu-icon"></i>
            </a>
            <div class="submenu" id="paymentMenu">
                <a href="./payments.php" class="submenu-item <?= isActive('payments.php'); ?>">
                    <i class="fas fa-list"></i>
                    <span>Transactions</span>
                </a>
                <a href="./refunds.php" class="submenu-item <?= isActive('refunds.php'); ?>">
                    <i class="fas fa-undo-alt"></i>
                    <span>Refunds</span>
                </a>
            </div>

            <a href="./price-history.php" class="menu-item <?= isActive('price-history.php'); ?>">
                <i class="fas fa-chart-line"></i>
                <span>Price History</span>
            </a>
        </div>

        <!-- ================= DOCUMENT SECTION ================= -->
        <div class="menu-section">
            <div class="menu-section-title">Document Management</div>

            <a href="./documents.php" class="menu-item <?= isActive('documents.php'); ?>">
                <i class="fas fa-file-alt"></i>
                <span>Documents</span>
            </a>
        </div>

        <!-- ================= Gallery SECTION ================= -->
        <div class="menu-section">
            <div class="menu-section-title">Gallery Management</div>

            <a href="./gallery.php" class="menu-item <?= isActive('gallery.php'); ?>">
                <i class="fas fa-images"></i>
                <span>Gallery</span>
            </a>
        </div>

        <!-- ================= SUPPORT & SERVICE SECTION ================= -->
        <div class="menu-section">
            <div class="menu-section-title">Support</div>

            <a href="#ticketMenu" class="menu-item has-submenu <?= isActive(['support-tickets.php']); ?>" data-toggle="submenu">
                <i class="fas fa-headset"></i>
                <span>Tickets</span>
                <i class="fas fa-chevron-right submenu-icon"></i>
                <span class="menu-badge">7</span>
            </a>
            <div class="submenu" id="ticketMenu">
                <a href="./support-tickets.php" class="submenu-item <?= isActive('support-tickets.php'); ?>">
                    <i class="fas fa-ticket-alt"></i>
                    <span>All Tickets</span>
                    <span class="menu-badge">4</span>
                </a>
                <a href="./open-tickets.php" class="submenu-item <?= isActive('open-tickets.php'); ?>">
                    <i class="fas fa-exclamation-circle"></i>
                    <span>Open</span>
                    <span class="menu-badge warning">3</span>
                </a>
            </div>

            <a href="#returnMenu" class="menu-item has-submenu <?= isActive(['return-requests.php']); ?>" data-toggle="submenu">
                <i class="fas fa-undo-alt"></i>
                <span>Returns</span>
                <i class="fas fa-chevron-right submenu-icon"></i>
                <span class="menu-badge">2</span>
            </a>
            <div class="submenu" id="returnMenu">
                <a href="./return-requests.php" class="submenu-item <?= isActive('return-requests.php'); ?>">
                    <i class="fas fa-list"></i>
                    <span>Requests</span>
                    <span class="menu-badge">2</span>
                </a>
            </div>
        </div>

        <!-- ================= SYSTEM & SECURITY SECTION ================= -->
        <div class="menu-section">
            <div class="menu-section-title">System</div>

            <a href="#adminMenu" class="menu-item has-submenu <?= isActive(['admins.php', 'admin-add.php', 'roles.php']); ?>" data-toggle="submenu">
                <i class="fas fa-user-shield"></i>
                <span>Administrators</span>
                <i class="fas fa-chevron-right submenu-icon"></i>
            </a>
            <div class="submenu" id="adminMenu">
                <a href="./admins.php" class="submenu-item <?= isActive('admins.php'); ?>">
                    <i class="fas fa-users-cog"></i>
                    <span>All Admins</span>
                </a>
                <a href="./admin-add.php" class="submenu-item <?= isActive('admin-add.php'); ?>">
                    <i class="fas fa-user-plus"></i>
                    <span>Add Admin</span>
                </a>
                <a href="./roles.php" class="submenu-item <?= isActive('roles.php'); ?>">
                    <i class="fas fa-shield-alt"></i>
                    <span>Roles & Permissions</span>
                </a>
            </div>

            <a href="#auditMenu" class="menu-item has-submenu <?= isActive(['audit-logs.php', 'login-logs.php']); ?>" data-toggle="submenu">
                <i class="fas fa-history"></i>
                <span>Audit Logs</span>
                <i class="fas fa-chevron-right submenu-icon"></i>
            </a>
            <div class="submenu" id="auditMenu">
                <a href="./audit-logs.php" class="submenu-item <?= isActive('audit-logs.php'); ?>">
                    <i class="fas fa-clipboard-list"></i>
                    <span>All Changes</span>
                </a>
                <a href="./login-logs.php" class="submenu-item <?= isActive('login-logs.php'); ?>">
                    <i class="fas fa-sign-in-alt"></i>
                    <span>Login History</span>
                </a>
            </div>

            <a href="#fraudMenu" class="menu-item has-submenu <?= isActive(['fraud-analysis.php']); ?>" data-toggle="submenu">
                <i class="fas fa-shield-virus"></i>
                <span>Fraud Analysis</span>
                <i class="fas fa-chevron-right submenu-icon"></i>
            </a>
            <div class="submenu" id="fraudMenu">
                <a href="./fraud-analysis.php" class="submenu-item <?= isActive('fraud-analysis.php'); ?>">
                    <i class="fas fa-exclamation-triangle"></i>
                    <span>Risk Reports</span>
                </a>
            </div>
        </div>

        <!-- ================= NOTIFICATIONS SECTION ================= -->
        <div class="menu-section">
            <div class="menu-section-title">Notifications</div>

            <a href="./notifications.php" class="menu-item <?= isActive('notifications.php'); ?>">
                <i class="fas fa-bell"></i>
                <span>All Notifications</span>
                <span class="menu-badge">8</span>
            </a>
        </div>

        <!-- Settings Section (Original) -->
        <div class="menu-section">
            <div class="menu-section-title">Settings</div>
            <a href="./users.php" class="menu-item <?= isActive('users.php'); ?>">
                <i class="fas fa-users"></i>
                <span>Users</span>
            </a>
            <a href="./settings.php" class="menu-item <?= isActive('settings.php'); ?>">
                <i class="fas fa-cog"></i>
                <span>General Settings</span>
            </a>
        </div>
    </nav>
</aside>
<style>
    /* Sidebar Styles - Optimized */
    .sidebar {
        width: 280px;
        background: linear-gradient(180deg, #1a1e2b 0%, #232838 100%);
        color: #fff;
        height: 100vh;
        position: fixed;
        left: 0;
        top: 0;
        overflow-y: auto;
        overflow-x: hidden;
        transition: all 0.3s ease;
        z-index: 1000;
        box-shadow: 4px 0 10px rgba(0, 0, 0, 0.1);
    }

    /* Sidebar Header */
    .sidebar-header {
        padding: 25px 20px;
        border-bottom: 1px solid rgba(255, 255, 255, 0.1);
        background: rgba(0, 0, 0, 0.2);
    }

    .sidebar-header h2 {
        margin: 0;
        font-size: 1.5rem;
        font-weight: 600;
        display: flex;
        align-items: center;
        gap: 10px;
        color: #fff;
    }

    .sidebar-header h2 i {
        color: #4CAF50;
    }

    .sidebar-header p {
        margin: 5px 0 0 0;
        font-size: 0.85rem;
        color: #a0a6b5;
    }

    /* Sidebar Menu */
    .sidebar-menu {
        padding: 15px 0;
    }

    /* Menu Sections */
    .menu-section {
        margin-bottom: 20px;
        padding: 0 15px;
    }

    .menu-section-title {
        font-size: 0.75rem;
        text-transform: uppercase;
        letter-spacing: 1px;
        color: #7a7f93;
        margin-bottom: 10px;
        padding-left: 15px;
        font-weight: 600;
    }

    /* Menu Items */
    .menu-item {
        display: flex;
        align-items: center;
        padding: 12px 15px;
        color: #d1d5e0;
        text-decoration: none;
        border-radius: 8px;
        margin-bottom: 2px;
        transition: all 0.2s ease;
        position: relative;
        white-space: nowrap;
    }

    .menu-item i:first-child {
        width: 20px;
        font-size: 1.1rem;
        margin-right: 12px;
        color: #6b7280;
        transition: all 0.2s ease;
    }

    .menu-item span {
        flex: 1;
        font-size: 0.9rem;
    }

    .menu-item:hover {
        background: rgba(255, 255, 255, 0.1);
        color: #fff;
    }

    .menu-item:hover i:first-child {
        color: #4CAF50;
    }

    .menu-item.active {
        background: linear-gradient(90deg, #4CAF50 0%, #45a049 100%);
        color: #fff;
        box-shadow: 0 4px 10px rgba(76, 175, 80, 0.3);
    }

    .menu-item.active i:first-child {
        color: #fff;
    }

    /* Submenu Items */
    .menu-item.has-submenu {
        position: relative;
        cursor: pointer;
    }

    .menu-item.has-submenu .submenu-icon {
        margin-left: auto;
        font-size: 0.75rem;
        transition: transform 0.3s ease;
    }

    .menu-item.has-submenu.open .submenu-icon {
        transform: rotate(90deg);
    }

    /* Submenu Container */
    .submenu {
        max-height: 0;
        overflow: hidden;
        transition: max-height 0.3s ease-out;
        background: rgba(0, 0, 0, 0.2);
        border-radius: 8px;
        margin: 2px 0 2px 30px;
        padding-left: 10px;
    }

    .submenu.show {
        max-height: 500px;
        /* Adjust based on content */
        transition: max-height 0.5s ease-in;
    }

    /* Submenu Items */
    .submenu-item {
        display: flex;
        align-items: center;
        padding: 10px 15px;
        color: #b0b5c5;
        text-decoration: none;
        border-radius: 6px;
        margin: 2px 0;
        font-size: 0.85rem;
        transition: all 0.2s ease;
        white-space: nowrap;
    }

    .submenu-item i {
        width: 20px;
        font-size: 0.9rem;
        margin-right: 10px;
        color: #5a6275;
    }

    .submenu-item:hover {
        background: rgba(255, 255, 255, 0.08);
        color: #fff;
        padding-left: 20px;
    }

    .submenu-item:hover i {
        color: #4CAF50;
    }

    .submenu-item.active {
        color: #4CAF50;
        font-weight: 500;
    }

    .submenu-item.active i {
        color: #4CAF50;
    }

    /* Menu Badges */
    .menu-badge {
        padding: 2px 8px;
        background: #ef4444;
        color: #fff;
        border-radius: 20px;
        font-size: 0.7rem;
        font-weight: 600;
        margin-left: 8px;
        min-width: 20px;
        text-align: center;
    }

    .menu-badge.warning {
        background: #f59e0b;
    }

    .menu-badge.success {
        background: #10b981;
    }

    /* Scrollbar Styling */
    .sidebar::-webkit-scrollbar {
        width: 5px;
    }

    .sidebar::-webkit-scrollbar-track {
        background: rgba(255, 255, 255, 0.05);
    }

    .sidebar::-webkit-scrollbar-thumb {
        background: rgba(255, 255, 255, 0.2);
        border-radius: 10px;
    }

    .sidebar::-webkit-scrollbar-thumb:hover {
        background: rgba(255, 255, 255, 0.3);
    }

    /* Collapsed Sidebar (Optional) */
    .sidebar.collapsed {
        width: 70px;
    }

    .sidebar.collapsed .sidebar-header h2 span,
    .sidebar.collapsed .sidebar-header p,
    .sidebar.collapsed .menu-item span,
    .sidebar.collapsed .menu-section-title,
    .sidebar.collapsed .submenu,
    .sidebar.collapsed .menu-badge {
        display: none;
    }

    .sidebar.collapsed .menu-item {
        justify-content: center;
        padding: 15px;
    }

    .sidebar.collapsed .menu-item i:first-child {
        margin-right: 0;
        font-size: 1.3rem;
    }

    .sidebar.collapsed .menu-item.has-submenu .submenu-icon {
        display: none;
    }

    /* Responsive */
    @media (max-width: 768px) {
        .sidebar {
            width: 0;
            transform: translateX(-100%);
        }

        .sidebar.open {
            width: 280px;
            transform: translateX(0);
        }
    }
</style>
<script>
    // Sidebar Submenu Toggle
    document.addEventListener('DOMContentLoaded', function() {
        const menuItems = document.querySelectorAll('.menu-item.has-submenu');

        menuItems.forEach(item => {
            item.addEventListener('click', function(e) {
                e.preventDefault();

                // Toggle current submenu
                const submenuId = this.getAttribute('href').substring(1);
                const submenu = document.getElementById(submenuId);

                if (submenu) {
                    // Close other open submenus (optional)
                    document.querySelectorAll('.submenu.show').forEach(menu => {
                        if (menu.id !== submenuId) {
                            menu.classList.remove('show');
                            menu.previousElementSibling?.classList.remove('open');
                        }
                    });

                    // Toggle current
                    submenu.classList.toggle('show');
                    this.classList.toggle('open');
                }
            });
        });

        // Keep submenu open if child is active
        const activeSubmenuItem = document.querySelector('.submenu-item.active');
        if (activeSubmenuItem) {
            const parentSubmenu = activeSubmenuItem.closest('.submenu');
            const parentMenuItem = document.querySelector(`[href="#${parentSubmenu?.id}"]`);

            if (parentSubmenu && parentMenuItem) {
                parentSubmenu.classList.add('show');
                parentMenuItem.classList.add('open');
            }
        }
    });
</script>