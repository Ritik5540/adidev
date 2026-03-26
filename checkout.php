<?php
require_once __DIR__ . '/config.php';
require_login();
start_checkout_session();

// SEO for checkout
$page_meta = [
    'title'       => 'Checkout | Adidev',
    'description' => 'Complete your purchase securely on Adidev.',
    'keywords'    => 'Adidev, checkout, payment, order',
];

include "header.php";
?>
<style>
    .common_btn:disabled {
        opacity: 0.7;
        cursor: not-allowed;
    }

    .fa-spinner {
        animation: spin 1s linear infinite;
    }

    @keyframes spin {
        0% {
            transform: rotate(0deg);
        }

        100% {
            transform: rotate(360deg);
        }
    }
</style>
<!--=========================
        PAGE BANNER START
    ==========================-->
<section class="page_banner" style="background: url(assets/images/page_banner_bg.jpg);">
    <div class="page_banner_overlay">
        <div class="container">
            <div class="row">
                <div class="col-12">
                    <div class="page_banner_text wow fadeInUp">
                        <h1>Checkout</h1>
                        <ul>
                            <li><a href="#"><i class="fal fa-home-lg"></i> Home</a></li>
                            <li><a href="#">Checkout</a></li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>
<!--=========================
        PAGE BANNER START
    ==========================-->


<!--============================
        CHECKOUT START
    =============================-->
<section class="checkout_page mt_100 mb_100">
    <div class="container">
        <div class="row">
            <div class="col-lg-8 wow fadeInUp">
                <div class="checkout_header">
                    <h3>Shipping Information</h3>
                    <p>
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5"
                            stroke="currentColor" class="size-6">
                            <path stroke-linecap="round" stroke-linejoin="round"
                                d="M15.75 6a3.75 3.75 0 1 1-7.5 0 3.75 3.75 0 0 1 7.5 0ZM4.501 20.118a7.5 7.5 0 0 1 14.998 0A17.933 17.933 0 0 1 12 21.75c-2.676 0-5.216-.584-7.499-1.632Z" />
                        </svg>
                        account:
                        <b><?php echo htmlspecialchars($_SESSION['user_name'] ?? 'Customer'); ?></b>
                        <a href="logout.php">(logout)</a>
                    </p>
                </div>

                <form class="checkout_form_area" id="checkoutForm">
                    <div class="accordion" id="accordionExample">
                        <div class="accordion-item border-0">
                            <div id="collapseThree" class="accordion-collapse collapse show"
                                data-bs-parent="#accordionExample">
                                <div class="accordion-body p-0">
                                    <div class="row">
                                        <div class="col-md-6">
                                            <div class="single_input">
                                                <label>Name *</label>
                                                <input type="text" name="customer_name" placeholder="Jhon deo" required>
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="single_input">
                                                <label>Email *</label>
                                                <input type="email" name="customer_email" placeholder="vipin@wciprofile.com" required>
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="single_input">
                                                <label>Phone *</label>
                                                <input type="text" name="customer_phone" placeholder="+965421541845845" required>
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="single_input">
                                                <label>Company name</label>
                                                <input type="text" name="company_name" placeholder="Zenis.com">
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="single_input">
                                                <label>City *</label>
                                                <select name="city" class="select_2" required>
                                                    <option value="">Select City</option>
                                                    <option value="Tokyo">Tokyo</option>
                                                    <option value="Japan">Japan</option>
                                                    <option value="Korea">Korea</option>
                                                    <option value="Thailand">Thailand</option>
                                                    <option value="Kanada">Kanada</option>
                                                </select>
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="single_input">
                                                <label>State *</label>
                                                <select name="state" class="select_2" required>
                                                    <option value="">Select State</option>
                                                    <option value="Korea">Korea</option>
                                                    <option value="Singapore">Singapore</option>
                                                    <option value="Japan">Japan</option>
                                                    <option value="Thailand">Thailand</option>
                                                    <option value="Kanada">Kanada</option>
                                                </select>
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="single_input">
                                                <label>Zip Code *</label>
                                                <input type="text" name="zip" placeholder="1234" required>
                                            </div>
                                        </div>
                                        <div class="col-xl-12">
                                            <div class="single_input">
                                                <label>Address *</label>
                                                <textarea name="address" rows="4" placeholder="Write your address" required></textarea>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-xl-12">
                        <div class="single_input">
                            <label>Order notes (optional)</label>
                            <textarea name="order_notes" rows="2" placeholder="Note"></textarea>
                        </div>
                    </div>
                </form>
            </div>
            <div class="col-lg-4 col-md-9 wow fadeInRight">
                <div class="cart_page_summary">
                    <h3>Billing summary</h3>
                    <ul id="checkout-items"></ul>

                    <h6>Subtotal <span id="checkout-subtotal">₹0.00</span></h6>
                    <h4>Total <span id="checkout-total">₹0.00</span></h4>
                </div>
                <div class="checkout_payment">
                    <h3>Payment Method</h3>
                    <div class="form-check">
                        <input class="form-check-input" type="radio" name="payment_method" value="cod" id="payment_cod" checked>
                        <label class="form-check-label" for="payment_cod">
                            Cash on Delivery
                        </label>
                    </div>
                    <div class="form-check">
                        <input class="form-check-input" type="radio" name="payment_method" value="card" id="payment_card">
                        <label class="form-check-label" for="payment_card">
                            Card Payment
                        </label>
                    </div>
                    <div class="checkout_card">
                        <p>Card Payment</p>
                        <ul>
                            <li><img src="assets/images/payment-1.jpg" alt="Payment" class="img-fluid w-100"></li>
                            <li><img src="assets/images/payment-3.jpg" alt="Payment" class="img-fluid w-100"></li>
                            <li><img src="assets/images/payment-4.jpg" alt="Payment" class="img-fluid w-100"></li>
                        </ul>
                    </div>
                    <div class="terms">
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" value="" id="termsCheckbox">
                            <label class="form-check-label" for="termsCheckbox">
                                I have read and agree to the website.
                            </label>
                        </div>
                    </div>
                    <button type="button" class="common_btn" id="placeOrderBtn">Place order <i
                            class="fas fa-long-arrow-right"></i></button>
                </div>
            </div>
        </div>
    </div>
</section>

<script>
    // Single cart initialization
    let cart = JSON.parse(localStorage.getItem("checkout_cart")) || { items: [] };

    function renderCheckout() {
        let html = '';
        let subtotal = 0;

        if (cart.items && cart.items.length > 0) {
            cart.items.forEach((item, index) => {
                let total = item.price * item.quantity;
                subtotal += total;

                html += `
                <li>
                    <a class="img" href="#">
                        <img src="${item.image}" class="img-fluid w-100" alt="${item.name}">
                    </a>
                    <div class="text">
                        <p class="title">${item.name}</p>
                        <p>₹${item.price.toFixed(2)} × ${item.quantity}</p>
                        <p>${item.color || ''}</p>
                    </div>
                </li>
                `;
            });
        } else {
            html = '<li class="empty-cart">Your cart is empty</li>';
        }

        document.getElementById("checkout-items").innerHTML = html;
        document.getElementById("checkout-subtotal").innerText = "₹" + subtotal.toFixed(2);
        document.getElementById("checkout-total").innerText = "₹" + subtotal.toFixed(2);

        // Update storage
        localStorage.setItem("checkout_cart", JSON.stringify({
            items: cart.items,
            subtotal: subtotal,
            total: subtotal
        }));
    }

    // Validate form function
    function validateCheckoutForm() {
        const form = document.getElementById('checkoutForm');
        const requiredFields = form.querySelectorAll('[required]');
        let isValid = true;
        
        requiredFields.forEach(field => {
            if (!field.value.trim()) {
                field.style.borderColor = 'red';
                isValid = false;
            } else {
                field.style.borderColor = '';
            }
        });
        
        if (!isValid) {
            alert('Please fill in all required fields');
            return false;
        }
        
        // Validate email format
        const email = document.querySelector('input[name="customer_email"]').value;
        const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
        if (!emailRegex.test(email)) {
            alert('Please enter a valid email address');
            return false;
        }
        
        // Validate phone (basic validation)
        const phone = document.querySelector('input[name="customer_phone"]').value;
        if (phone.length < 10) {
            alert('Please enter a valid phone number');
            return false;
        }
        
        return true;
    }

    // Get form data function
    function getFormData() {
        return {
            items: cart.items,
            customer_name: document.querySelector('input[name="customer_name"]')?.value.trim() || '',
            customer_email: document.querySelector('input[name="customer_email"]')?.value.trim() || '',
            customer_phone: document.querySelector('input[name="customer_phone"]')?.value.trim() || '',
            company_name: document.querySelector('input[name="company_name"]')?.value.trim() || '',
            city: document.querySelector('select[name="city"]')?.value || '',
            state: document.querySelector('select[name="state"]')?.value || '',
            zip: document.querySelector('input[name="zip"]')?.value.trim() || '',
            address: document.querySelector('textarea[name="address"]')?.value.trim() || '',
            order_notes: document.querySelector('textarea[name="order_notes"]')?.value.trim() || '',
            payment_method: document.querySelector('input[name="payment_method"]:checked')?.value || 'cod'
        };
    }

    // Single place order handler
    const placeOrderBtn = document.getElementById('placeOrderBtn');
    
    if (placeOrderBtn) {
        placeOrderBtn.addEventListener("click", async function(e) {
            e.preventDefault();

            // Check terms
            const termsCheckbox = document.getElementById("termsCheckbox");
            if (!termsCheckbox.checked) {
                alert("Please accept terms & conditions");
                return;
            }

            // Check cart
            if (!cart.items || cart.items.length === 0) {
                alert("Cart is empty");
                return;
            }

            // Validate form
            if (!validateCheckoutForm()) {
                return;
            }

            // Get form data
            const formData = getFormData();

            // Show loading state
            const button = e.target;
            const originalText = button.innerHTML;
            button.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Placing order...';
            button.disabled = true;

            try {
                // Send to backend
                const response = await fetch("ajax/place-order.php", {
                    method: "POST",
                    headers: {
                        "Content-Type": "application/json"
                    },
                    body: JSON.stringify(formData)
                });
                
                const result = await response.json();
                
                if (result.success) {
                    // Clear cart
                    localStorage.removeItem("checkout_cart");
                    cart = { items: [] };
                    
                    // Show success message
                    alert(result.message || "Order placed successfully!");
                    
                    // Redirect to thank you page
                    window.location.href = result.redirect_url || "thankyou.php";
                } else {
                    // Show error message
                    alert(result.message || "Failed to place order. Please try again.");
                    // Reset button
                    button.innerHTML = originalText;
                    button.disabled = false;
                }
            } catch (error) {
                console.error('Error:', error);
                alert("An error occurred. Please try again.");
                // Reset button
                button.innerHTML = originalText;
                button.disabled = false;
            }
        });
    }

    // Initialize on page load
    window.addEventListener("load", function() {
        renderCheckout();
    });
</script>

<style>
    /* Loading spinner animation */
    .fa-spinner {
        animation: spin 1s linear infinite;
    }
    
    @keyframes spin {
        0% { transform: rotate(0deg); }
        100% { transform: rotate(360deg); }
    }
    
    /* Button disabled state */
    .common_btn:disabled {
        opacity: 0.7;
        cursor: not-allowed;
    }
    
    /* Required field validation */
    input[required], select[required], textarea[required] {
        transition: border-color 0.3s ease;
    }
    
    input[required]:invalid, select[required]:invalid, textarea[required]:invalid {
        border-color: #ff4444;
    }
    
    /* Empty cart message */
    .empty-cart {
        text-align: center;
        padding: 20px;
        color: #666;
    }
</style>
<!--============================
        CHECKOUT END
    =============================-->
<?php include "footer.php"; ?>