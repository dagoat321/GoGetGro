<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Create Password | GoGetGro</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <link rel="stylesheet" href="stylesheet_CP.css">
</head>
<body>

    <a href="login.php" class="back-btn">&lt;</a>
    
    <div class="create-container">
        <h1>Create Password</h1>
        
        <div class="input-group">
            <label for="new-password">New Password</label>
            <input type="password" id="new-password" placeholder="Enter new password">
        </div>

        <div class="input-group">
            <label for="confirm-password">Confirm Password</label>
            <input type="password" id="confirm-password" placeholder="Confirm new password">
        </div>

        <button class="create-btn" id="resetBtn">RESET PASSWORD</button>
    </div>

    <div class="modal-overlay" id="modalOverlay">
        <div class="modal-box">
            <div class="icon-circle">
                <i class="bi bi-check-lg"></i>
            </div>
            <h2>Your password has been changed successfully.</h2>
            <p>Redirecting you back to Login...</p>
        </div>
    </div>

    <script>
        const resetBtn = document.getElementById('resetBtn');
        const modal = document.getElementById('modalOverlay');
        const pass1 = document.getElementById('new-password');
        const pass2 = document.getElementById('confirm-password');

        resetBtn.onclick = function() {
            let hasError = false;

            // Validation
            if (pass1.value.trim() === "") { applyError(pass1); hasError = true; }
            if (pass2.value.trim() === "") { applyError(pass2); hasError = true; }

            if (hasError) return;

            if (pass1.value !== pass2.value) {
                applyError(pass1);
                applyError(pass2);
                alert("Passwords do not match!");
                return;
            }

            // Show Success Popup
            modal.classList.add('show');
            
            // Redirect after 3 seconds
            setTimeout(() => {
                window.location.href = "login.php"; 
            }, 3000);
        }

        function applyError(element) {
            element.classList.add('error-border', 'error-shake');
            setTimeout(() => { element.classList.remove('error-shake'); }, 500);
        }

        [pass1, pass2].forEach(input => {
            input.addEventListener('input', () => {
                input.classList.remove('error-border');
            });
        });
    </script>

</body>
</html>
