    <footer class="custom-footer">
        <div class="container">
            <div class="row">
                <div class="col-lg-auto col-md-12 mb-4 me-lg-5">
                    <div class="footer-logo-box">
                        <img src="images/Group 19.png" alt="GoGetGro Logo">
                    </div>
                    <div class="footer-socials">
                        <a href="https://www.facebook.com" target="_blank" rel="noreferrer"><i class="bi bi-facebook"></i></a>
                        <a href="https://www.instagram.com" target="_blank" rel="noreferrer"><i class="bi bi-instagram"></i></a>
                        <a href="https://www.tiktok.com" target="_blank" rel="noreferrer"><i class="bi bi-tiktok"></i></a>
                        <a href="https://web.whatsapp.com" target="_blank" rel="noreferrer"><i class="bi bi-whatsapp"></i></a>
                    </div>
                </div>

                <div class="col-lg-2 col-md-4 col-6 mb-4">
                    <ul class="footer-link-group">
                        <li><a href="about.php">About Us</a></li>
                        <li><a href="policy.php">Privacy Policy</a></li>
                        <li><a href="help.php">Contact Us</a></li>
                    </ul>
                </div>

                <div class="col-lg-2 col-md-4 col-6 mb-4">
                    <ul class="footer-link-group">
                        <li><a href="terms.php">Terms &amp; Conditions</a></li>
                        <li><a href="faqs.php">FAQs</a></li>
                    </ul>
                </div>

                <div class="col-lg-4 col-md-4 mb-4 ms-auto">
                    <div class="newsletter-title">Get updates on exclusives and awesome deals</div>
                    <p class="newsletter-desc">Enter your email address to subscribe</p>
                    <form class="subscribe-form" id="footerNewsletterForm" onsubmit="submitNewsletter(event)">
                        <input type="email" id="footerEmailInput" placeholder="youremail@example.com" required>
                        <button type="submit" class="arrow-btn"><i class="bi bi-arrow-right"></i></button>
                    </form>
                    <div id="newsletterToast" style="
                        display:none;
                        margin-top:10px;
                        background:#007a5e;
                        color:#fff;
                        border-radius:10px;
                        padding:10px 16px;
                        font-size:0.88rem;
                        font-weight:600;
                        animation: fadeInUp 0.4s ease;
                    ">
                        <i class="bi bi-check-circle-fill me-2"></i><span id="newsletterToastMsg">You're subscribed for exclusive updates!</span>
                    </div>
                </div>
            </div>

            <div class="copyright-bar">
                &copy; 2026 G3 Quad, Inc. All rights reserved.
            </div>
        </div>
    </footer>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>
    <style>
        @keyframes fadeInUp {
            from {
                opacity: 0;
                transform: translateY(8px);
            }

            to {
                opacity: 1;
                transform: translateY(0);
            }
        }
    </style>
    <script>
        function submitNewsletter(e) {
            e.preventDefault();
            const email = document.getElementById('footerEmailInput').value.trim();
            const toast = document.getElementById('newsletterToast');
            const msg = document.getElementById('newsletterToastMsg');
            if (!email) return;

            fetch('subscribe_ajax.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/x-www-form-urlencoded',
                    },
                    body: 'email=' + encodeURIComponent(email)
                })
                .then(response => response.json())
                .then(data => {
                    if (data.status === 'success') {
                        msg.textContent = email + ' subscribed for exclusive updates!';
                        toast.style.background = '#007a5e';
                    } else {
                        msg.textContent = data.message || 'Error subscribing.';
                        toast.style.background = '#dc3545';
                    }
                    toast.style.display = 'block';
                    document.getElementById('footerEmailInput').value = '';

                    setTimeout(() => {
                        toast.style.display = 'none';
                    }, 5000);
                })
                .catch(error => {
                    console.error('Error:', error);
                });
        }

        function confirmLogout() {
            if (confirm('Are you sure you want to logout?')) {
                window.location.href = 'logout.php';
            }
        }
    </script>
    </body>

    </html>
