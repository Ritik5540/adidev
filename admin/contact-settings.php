<?php
include "../admin-config.php";
date_default_timezone_set('Asia/Kolkata');

// Initialize message variables
$message = '';
$message_type = '';

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['update_contact'])) {
    $success = true;
    
    foreach ($_POST['settings'] as $key => $value) {
        $value = $conn->real_escape_string(trim($value));
        $sql = "UPDATE contact_settings SET setting_value = '$value', updated_at = NOW() WHERE setting_key = '$key'";
        if (!$conn->query($sql)) {
            $success = false;
            break;
        }
    }
    
    if ($success) {
        $message = 'Contact settings updated successfully!';
        $message_type = 'success';
    } else {
        $message = 'Error updating contact settings.';
        $message_type = 'error';
    }
}

// Fetch all contact settings
$settings_result = $conn->query("SELECT * FROM contact_settings ORDER BY sort_order ASC, setting_group, setting_key");
$settings = [];
while ($row = $settings_result->fetch_assoc()) {
    $settings[$row['setting_key']] = $row;
}

// Group settings by type
$phone_numbers = [];
$emails = [];
$address_parts = [];
$others = [];

foreach ($settings as $key => $setting) {
    if ($setting['setting_type'] == 'phone') {
        $phone_numbers[] = $setting;
    } elseif ($setting['setting_type'] == 'email') {
        $emails[] = $setting;
    } elseif (strpos($key, 'address') !== false || in_array($key, ['city', 'state', 'pincode', 'country'])) {
        $address_parts[] = $setting;
    } else {
        $others[] = $setting;
    }
}
?>
<?php include 'layout/header.php'; ?>
<?php include 'layout/sidebar.php'; ?>

<style>
    
    .settings-section {
        background: white;
        border-radius: 10px;
        padding: 25px;
        margin-bottom: 25px;
        box-shadow: 0 5px 15px rgba(0,0,0,0.05);
        border: 1px solid #eaeaea;
    }
    
    .section-title {
        font-size: 20px;
        color: #2c3e50;
        margin-bottom: 25px;
        padding-bottom: 15px;
        border-bottom: 2px solid #f0f2f5;
        display: flex;
        align-items: center;
        gap: 10px;
    }
    
    .section-title i {
        color: #667eea;
    }
    
    .settings-grid {
        display: grid;
        grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
        gap: 20px;
    }
    
    .setting-item {
        margin-bottom: 20px;
    }
    
    .setting-label {
        display: block;
        margin-bottom: 8px;
        font-weight: 500;
        color: #2c3e50;
        font-size: 14px;
        text-transform: uppercase;
        letter-spacing: 0.5px;
    }
    
    .setting-key {
        font-size: 12px;
        color: #718096;
        margin-top: 3px;
        font-family: monospace;
    }
    
    .form-control {
        width: 100%;
        padding: 12px 15px;
        border: 2px solid #e0e6ed;
        border-radius: 6px;
        font-size: 14px;
        transition: all 0.3s;
        background: #f8fafc;
    }
    
    .form-control:focus {
        outline: none;
        border-color: #667eea;
        background: white;
        box-shadow: 0 0 0 3px rgba(102, 126, 234, 0.1);
    }
    
    textarea.form-control {
        min-height: 120px;
        resize: vertical;
        font-family: inherit;
    }
    
    .form-actions {
        display: flex;
        gap: 15px;
        margin-top: 30px;
        padding-top: 20px;
        border-top: 1px solid #eee;
    }
    
    .btn {
        padding: 12px 24px;
        border-radius: 6px;
        border: none;
        font-weight: 500;
        font-size: 14px;
        cursor: pointer;
        transition: all 0.3s ease;
        display: inline-flex;
        align-items: center;
        gap: 8px;
        text-decoration: none;
    }
    
    .btn-primary {
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        color: white;
    }
    
    .btn-primary:hover {
        transform: translateY(-2px);
        box-shadow: 0 7px 14px rgba(102, 126, 234, 0.2);
    }
    
    .btn-secondary {
        background: #6c757d;
        color: white;
    }
    
    .btn-secondary:hover {
        background: #5a6268;
    }
    
    .message {
        padding: 15px 20px;
        border-radius: 6px;
        margin-bottom: 25px;
        display: flex;
        align-items: center;
        gap: 12px;
        animation: slideIn 0.3s ease;
    }
    
    .message-success {
        background: linear-gradient(135deg, #d4edda 0%, #c3e6cb 100%);
        color: #155724;
        border: 1px solid #b1dfbb;
    }
    
    .message-error {
        background: linear-gradient(135deg, #f8d7da 0%, #f5c6cb 100%);
        color: #721c24;
        border: 1px solid #f1b0b7;
    }
    
    @keyframes slideIn {
        from {
            opacity: 0;
            transform: translateY(-10px);
        }
        to {
            opacity: 1;
            transform: translateY(0);
        }
    }
    
    .add-more-btn {
        background: transparent;
        border: 2px dashed #667eea;
        color: #667eea;
        padding: 10px 20px;
        border-radius: 6px;
        cursor: pointer;
        display: inline-flex;
        align-items: center;
        gap: 8px;
        margin-top: 10px;
        transition: all 0.3s;
    }
    
    .add-more-btn:hover {
        background: #667eea;
        color: white;
        border-style: solid;
    }
    
    .remove-btn {
        background: transparent;
        border: 1px solid #ff6b6b;
        color: #ff6b6b;
        padding: 5px 10px;
        border-radius: 4px;
        cursor: pointer;
        font-size: 12px;
        margin-top: 5px;
        display: inline-flex;
        align-items: center;
        gap: 5px;
    }
    
    .remove-btn:hover {
        background: #ff6b6b;
        color: white;
    }
    
    @media (max-width: 768px) {
        .settings-grid {
            grid-template-columns: 1fr;
        }
        
        .settings-section {
            padding: 20px;
        }
        
        .form-actions {
            flex-direction: column;
        }
        
        .btn {
            width: 100%;
            justify-content: center;
        }
    }
    
    .preview-section {
        background: #f8fafc;
        border-radius: 8px;
        padding: 20px;
        margin-top: 30px;
        border: 1px dashed #cbd5e0;
    }
    
    .preview-title {
        font-size: 16px;
        color: #4a5568;
        margin-bottom: 15px;
        display: flex;
        align-items: center;
        gap: 8px;
    }
    
    .contact-preview {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
        gap: 20px;
    }
    
    .preview-item {
        background: white;
        padding: 15px;
        border-radius: 6px;
        border: 1px solid #e2e8f0;
    }
    
    .preview-label {
        font-size: 12px;
        color: #718096;
        text-transform: uppercase;
        margin-bottom: 5px;
    }
    
    .preview-value {
        font-size: 14px;
        color: #2d3748;
        font-weight: 500;
    }
</style>

<!-- Main Content -->
<main class="main-content">
    <div class="contact-settings-container">
        <!-- Alert Message -->
        <?php if ($message): ?>
            <div class="message message-<?php echo $message_type; ?>">
                <i class="fas fa-<?php echo $message_type == 'success' ? 'check-circle' : 'exclamation-circle'; ?>"></i>
                <span><?php echo htmlspecialchars($message); ?></span>
            </div>
            <script>
                setTimeout(() => {
                    const alert = document.querySelector('.message');
                    if (alert) {
                        alert.style.opacity = '0';
                        setTimeout(() => {
                            if (alert) alert.style.display = 'none';
                        }, 300);
                    }
                }, 5000);
            </script>
        <?php endif; ?>

        <div class="form-header">
            <h2 class="form-title">
                <i class="fas fa-address-book"></i>
                Contact Information Management
            </h2>
            <p class="form-subtitle">Update your website's contact details</p>
        </div>

        <form method="POST" action="" id="contactSettingsForm">
            <input type="hidden" name="update_contact" value="1">
            
            <!-- Phone Numbers Section -->
            <div class="settings-section">
                <h3 class="section-title">
                    <i class="fas fa-phone"></i>
                    Phone Numbers
                </h3>
                
                <div class="settings-grid" id="phoneNumbersContainer">
                    <?php foreach ($phone_numbers as $index => $phone): ?>
                        <div class="setting-item phone-item" data-index="<?php echo $index; ?>">
                            <label class="setting-label">
                                Phone <?php echo $index + 1; ?>
                                <span class="setting-key"><?php echo htmlspecialchars($phone['setting_key']); ?></span>
                            </label>
                            <input type="tel" 
                                   name="settings[<?php echo $phone['setting_key']; ?>]" 
                                   class="form-control" 
                                   value="<?php echo htmlspecialchars($phone['setting_value']); ?>" 
                                   placeholder="Enter phone number">
                            <?php if ($index > 1): ?>
                                <button type="button" class="remove-btn" onclick="removePhoneField(this)">
                                    <i class="fas fa-times"></i> Remove
                                </button>
                            <?php endif; ?>
                        </div>
                    <?php endforeach; ?>
                </div>
                
                <button type="button" class="add-more-btn" onclick="addPhoneField()">
                    <i class="fas fa-plus"></i> Add Another Phone Number
                </button>
            </div>

            <!-- Email Addresses Section -->
            <div class="settings-section">
                <h3 class="section-title">
                    <i class="fas fa-envelope"></i>
                    Email Addresses
                </h3>
                
                <div class="settings-grid" id="emailsContainer">
                    <?php foreach ($emails as $index => $email): ?>
                        <div class="setting-item email-item">
                            <label class="setting-label">
                                <?php echo $index == 0 ? 'Primary Email' : 'Secondary Email'; ?>
                                <span class="setting-key"><?php echo htmlspecialchars($email['setting_key']); ?></span>
                            </label>
                            <input type="email" 
                                   name="settings[<?php echo $email['setting_key']; ?>]" 
                                   class="form-control" 
                                   value="<?php echo htmlspecialchars($email['setting_value']); ?>" 
                                   placeholder="Enter email address">
                        </div>
                    <?php endforeach; ?>
                </div>
                
                <button type="button" class="add-more-btn" onclick="addEmailField()">
                    <i class="fas fa-plus"></i> Add Another Email Address
                </button>
            </div>

            <!-- Address Section -->
            <div class="settings-section">
                <h3 class="section-title">
                    <i class="fas fa-map-marker-alt"></i>
                    Address Information
                </h3>
                
                <div class="settings-grid">
                    <?php foreach ($address_parts as $address): ?>
                        <div class="setting-item">
                            <label class="setting-label">
                                <?php 
                                    $label = str_replace(['_', 'line'], [' ', 'Line '], $address['setting_key']);
                                    echo ucwords($label);
                                ?>
                                <span class="setting-key"><?php echo htmlspecialchars($address['setting_key']); ?></span>
                            </label>
                            <input type="text" 
                                   name="settings[<?php echo $address['setting_key']; ?>]" 
                                   class="form-control" 
                                   value="<?php echo htmlspecialchars($address['setting_value']); ?>" 
                                   placeholder="Enter <?php echo str_replace('_', ' ', $address['setting_key']); ?>">
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>

            <!-- Other Settings -->
            <div class="settings-section">
                <h3 class="section-title">
                    <i class="fas fa-cog"></i>
                    Additional Information
                </h3>
                
                <div class="settings-grid">
                    <?php foreach ($others as $other): ?>
                        <div class="setting-item">
                            <label class="setting-label">
                                <?php 
                                    $label = str_replace('_', ' ', $other['setting_key']);
                                    echo ucwords($label);
                                ?>
                                <span class="setting-key"><?php echo htmlspecialchars($other['setting_key']); ?></span>
                            </label>
                            <?php if ($other['setting_type'] == 'textarea'): ?>
                                <textarea name="settings[<?php echo $other['setting_key']; ?>]" 
                                          class="form-control" 
                                          placeholder="Enter <?php echo str_replace('_', ' ', $other['setting_key']); ?>"
                                          rows="4"><?php echo htmlspecialchars($other['setting_value']); ?></textarea>
                            <?php else: ?>
                                <input type="text" 
                                       name="settings[<?php echo $other['setting_key']; ?>]" 
                                       class="form-control" 
                                       value="<?php echo htmlspecialchars($other['setting_value']); ?>" 
                                       placeholder="Enter <?php echo str_replace('_', ' ', $other['setting_key']); ?>">
                            <?php endif; ?>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>

            <!-- Live Preview -->
            <div class="preview-section">
                <h4 class="preview-title">
                    <i class="fas fa-eye"></i>
                    Live Preview
                </h4>
                <div class="contact-preview">
                    <div class="preview-item">
                        <div class="preview-label">Phone Numbers</div>
                        <div class="preview-value" id="previewPhone">
                            <?php foreach ($phone_numbers as $phone): ?>
                                <?php if (!empty($phone['setting_value'])): ?>
                                    <div><?php echo htmlspecialchars($phone['setting_value']); ?></div>
                                <?php endif; ?>
                            <?php endforeach; ?>
                        </div>
                    </div>
                    
                    <div class="preview-item">
                        <div class="preview-label">Email Addresses</div>
                        <div class="preview-value" id="previewEmail">
                            <?php foreach ($emails as $email): ?>
                                <?php if (!empty($email['setting_value'])): ?>
                                    <div><?php echo htmlspecialchars($email['setting_value']); ?></div>
                                <?php endif; ?>
                            <?php endforeach; ?>
                        </div>
                    </div>
                    
                    <div class="preview-item">
                        <div class="preview-label">Address</div>
                        <div class="preview-value" id="previewAddress">
                            <?php 
                            $address_text = '';
                            foreach ($address_parts as $address) {
                                if (!empty($address['setting_value'])) {
                                    $address_text .= $address['setting_value'] . ', ';
                                }
                            }
                            echo rtrim($address_text, ', ');
                            ?>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Form Actions -->
            <div class="form-actions">
                <button type="submit" class="btn btn-primary">
                    <i class="fas fa-save"></i> Save All Changes
                </button>
                <button type="button" class="btn btn-secondary" onclick="resetForm()">
                    <i class="fas fa-undo"></i> Reset to Original
                </button>
            </div>
        </form>
    </div>
</main>

<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
    let phoneCounter = <?php echo count($phone_numbers); ?>;
    let emailCounter = <?php echo count($emails); ?>;
    
    // Update preview in real-time
    document.querySelectorAll('.form-control').forEach(input => {
        input.addEventListener('input', updatePreview);
    });
    
    function updatePreview() {
        // Update phone numbers preview
        const phoneInputs = document.querySelectorAll('input[name^="settings[phone"]');
        const phonePreview = document.getElementById('previewPhone');
        phonePreview.innerHTML = '';
        phoneInputs.forEach(input => {
            if (input.value.trim()) {
                const div = document.createElement('div');
                div.textContent = input.value;
                phonePreview.appendChild(div);
            }
        });
        
        // Update emails preview
        const emailInputs = document.querySelectorAll('input[name^="settings[email"]');
        const emailPreview = document.getElementById('previewEmail');
        emailPreview.innerHTML = '';
        emailInputs.forEach(input => {
            if (input.value.trim()) {
                const div = document.createElement('div');
                div.textContent = input.value;
                emailPreview.appendChild(div);
            }
        });
        
        // Update address preview
        const addressInputs = document.querySelectorAll('input[name*="address"], input[name="city"], input[name="state"], input[name="pincode"], input[name="country"]');
        const addressPreview = document.getElementById('previewAddress');
        let addressText = '';
        addressInputs.forEach(input => {
            if (input.value.trim()) {
                addressText += input.value + ', ';
            }
        });
        addressPreview.textContent = addressText.replace(/, $/, '');
    }
    
    // Add new phone field
    function addPhoneField() {
        phoneCounter++;
        const newKey = 'phone_' + phoneCounter;
        const container = document.getElementById('phoneNumbersContainer');
        
        const newField = document.createElement('div');
        newField.className = 'setting-item phone-item';
        newField.innerHTML = `
            <label class="setting-label">
                Phone ${phoneCounter}
                <span class="setting-key">${newKey}</span>
            </label>
            <input type="tel" 
                   name="settings[${newKey}]" 
                   class="form-control" 
                   placeholder="Enter phone number">
            <button type="button" class="remove-btn" onclick="removePhoneField(this)">
                <i class="fas fa-times"></i> Remove
            </button>
        `;
        
        container.appendChild(newField);
        
        // Add event listener for real-time preview
        newField.querySelector('input').addEventListener('input', updatePreview);
        
        // Scroll to new field
        newField.scrollIntoView({ behavior: 'smooth', block: 'nearest' });
    }
    
    // Add new email field
    function addEmailField() {
        emailCounter++;
        const newKey = 'email_' + emailCounter;
        const container = document.getElementById('emailsContainer');
        
        const newField = document.createElement('div');
        newField.className = 'setting-item email-item';
        newField.innerHTML = `
            <label class="setting-label">
                Additional Email
                <span class="setting-key">${newKey}</span>
            </label>
            <input type="email" 
                   name="settings[${newKey}]" 
                   class="form-control" 
                   placeholder="Enter email address">
            <button type="button" class="remove-btn" onclick="removeEmailField(this)">
                <i class="fas fa-times"></i> Remove
            </button>
        `;
        
        container.appendChild(newField);
        
        // Add event listener for real-time preview
        newField.querySelector('input').addEventListener('input', updatePreview);
        
        // Scroll to new field
        newField.scrollIntoView({ behavior: 'smooth', block: 'nearest' });
    }
    
    // Remove phone field
    function removePhoneField(button) {
        const field = button.closest('.phone-item');
        field.style.opacity = '0';
        field.style.transform = 'translateX(-20px)';
        
        setTimeout(() => {
            field.remove();
            updatePreview();
            renumberPhoneFields();
        }, 300);
    }
    
    // Remove email field
    function removeEmailField(button) {
        const field = button.closest('.email-item');
        field.style.opacity = '0';
        field.style.transform = 'translateX(-20px)';
        
        setTimeout(() => {
            field.remove();
            updatePreview();
        }, 300);
    }
    
    // Renumber phone fields
    function renumberPhoneFields() {
        const phoneFields = document.querySelectorAll('.phone-item');
        phoneFields.forEach((field, index) => {
            const label = field.querySelector('.setting-label');
            const keySpan = field.querySelector('.setting-key');
            const key = keySpan.textContent.replace(/phone_\d+/, 'phone_' + (index + 1));
            const input = field.querySelector('input');
            
            // Update label
            label.innerHTML = `Phone ${index + 1} <span class="setting-key">${key}</span>`;
            
            // Update input name
            input.name = `settings[${key}]`;
            
            // Update key span
            keySpan.textContent = key;
        });
        
        phoneCounter = phoneFields.length;
    }
    
    // Reset form
    function resetForm() {
        Swal.fire({
            title: 'Reset Form?',
            text: 'Are you sure you want to reset all changes?',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#dc3545',
            cancelButtonColor: '#6c757d',
            confirmButtonText: 'Yes, reset!',
            cancelButtonText: 'Cancel'
        }).then((result) => {
            if (result.isConfirmed) {
                location.reload();
            }
        });
    }
    
    // Form validation
    document.getElementById('contactSettingsForm').addEventListener('submit', function(e) {
        let isValid = true;
        let errorMessage = '';
        
        // Validate emails
        const emailInputs = document.querySelectorAll('input[type="email"]');
        emailInputs.forEach(input => {
            if (input.value && !isValidEmail(input.value)) {
                isValid = false;
                errorMessage = 'Please enter valid email addresses.';
                input.style.borderColor = '#dc3545';
            } else {
                input.style.borderColor = '';
            }
        });
        
        if (!isValid) {
            e.preventDefault();
            Swal.fire({
                title: 'Validation Error',
                text: errorMessage,
                icon: 'error',
                confirmButtonText: 'OK'
            });
        }
    });
    
    function isValidEmail(email) {
        const re = /^(([^<>()\[\]\\.,;:\s@"]+(\.[^<>()\[\]\\.,;:\s@"]+)*)|(".+"))@((\[[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}])|(([a-zA-Z\-0-9]+\.)+[a-zA-Z]{2,}))$/;
        return re.test(String(email).toLowerCase());
    }
    
    // Initialize preview on page load
    document.addEventListener('DOMContentLoaded', updatePreview);
</script>

<?php include 'layout/footer.php'; ?>
</body>
</html>