<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Forgot Password | GoGetGro</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <link rel="stylesheet" href="stylesheet_FP.css">
</head>
<body>

    <a href="login.php" class="back-btn">&lt;</a>
    
    <div class="forgot-container">
        <h1>Forgot Password?</h1>
        
        <div class="input-group">
            <label for="email">Email</label>
            <input type="email" id="email" placeholder="Enter your email">
        </div>

        <button class="get-link-btn" id="getLinkBtn">GET LINK</button>
    </div>

    <div class="modal-overlay" id="modalOverlay">
        <div class="modal-box">
            <div class="icon-circle">
                <i class="bi bi-envelope-fill"></i>
            </div>
            <h2>Email has been sent successfully.</h2>
            <p>Redirecting you to link...</p>
        </div>
    </div>

    <script>
        const btn = document.getElementById('getLinkBtn');
        const modal = document.getElementById('modalOverlay');
        const emailInput = document.getElementById('email');

        btn.onclick = function() {
            // Validation check
            if (emailInput.value.trim() === "") {
                emailInput.classList.add('error-shake');
                setTimeout(() => {
                    emailInput.classList.remove('error-shake');
                }, 500);
                return;
            }

            // Show popup
            modal.classList.add('show');
            
            // Redirect to the "Create New Password" page after 3 seconds
            setTimeout(() => {
                window.location.href = "createpass.php"; 
            }, 3000);
        }
    </script>

</body>
</html>
