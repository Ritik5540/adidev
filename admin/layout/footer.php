    <!-- Footer -->
    <footer class="footer">
        <div class="footer-left">
            <p>&copy; 2026 CMS Panel. All rights reserved.</p>
        </div>
        <div class="footer-links">
            <a href="#"><i class="fas fa-shield-alt"></i> Privacy</a>
            <a href="#"><i class="fas fa-file-contract"></i> Terms</a>
            <a href="#"><i class="fas fa-headset"></i> Support</a>
        </div>
    </footer>

    <script>
        document.querySelectorAll(".menu-dropdown .toggle").forEach(item => {
            item.addEventListener("click", function() {
                let parent = this.parentElement;

                // close others (accordion style)
                document.querySelectorAll(".menu-dropdown").forEach(el => {
                    if (el !== parent) el.classList.remove("active");
                });

                parent.classList.toggle("active");
            });
        });
    </script>