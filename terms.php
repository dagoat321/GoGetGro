<?php
require __DIR__ . '/includes/bootstrap.php';
$pageTitle = 'Terms & Conditions';
$extraCss = ['stylesheet1.css'];
require_once __DIR__ . '/includes/header.php';
?>
<style>
.terms-hero{background:linear-gradient(135deg,#007a5e,#00a884);padding:50px 30px 44px;border-radius:0 0 24px 24px;margin-bottom:36px}
.terms-breadcrumb{font-size:.85rem;color:rgba(255,255,255,.7);margin-bottom:12px}
.terms-breadcrumb a{color:rgba(255,255,255,.85);text-decoration:none}
.terms-hero h1{color:#fff;font-weight:900;font-size:2rem;margin:0}
.terms-hero p{color:rgba(255,255,255,.8);margin-top:8px}
.terms-toc{background:#fff;border-radius:14px;padding:22px 28px;margin-bottom:28px;box-shadow:0 2px 12px rgba(0,122,94,.07);border:1px solid #d8ede7}
.terms-toc h6{color:#007a5e;font-weight:700;text-transform:uppercase;letter-spacing:1px;font-size:.8rem;margin-bottom:12px}
.terms-toc ol{margin:0;padding-left:18px}
.terms-toc li{margin-bottom:5px}
.terms-toc li a{color:#007a5e;text-decoration:none;font-size:.9rem}
.terms-section{background:#fff;border-radius:16px;padding:32px 36px;margin-bottom:20px;box-shadow:0 2px 12px rgba(0,122,94,.07)}
.terms-section .snum{display:inline-flex;align-items:center;justify-content:center;width:34px;height:34px;background:#007a5e;color:#fff;border-radius:50%;font-weight:700;font-size:.88rem;margin-right:10px;flex-shrink:0}
.terms-section h3{color:#1a1a1a;font-weight:700;font-size:1.1rem;margin-bottom:14px;display:flex;align-items:center}
.terms-section p,.terms-section li{color:#555;line-height:1.75;font-size:.95rem}
.terms-section ul{padding-left:22px;margin-top:8px}
.terms-section li{margin-bottom:6px}
.terms-note{background:#fff8e8;border-left:4px solid #f59e0b;border-radius:6px;padding:12px 16px;margin-top:10px;font-size:.88rem;color:#444}
.terms-cta{background:linear-gradient(135deg,#007a5e,#00a884);border-radius:16px;padding:32px 36px;text-align:center;color:#fff;margin-bottom:40px}
.terms-cta h4{font-weight:800;font-size:1.3rem;margin-bottom:8px}
#backToTop{position:fixed;bottom:30px;right:30px;width:44px;height:44px;background:#007a5e;color:#fff;border:none;border-radius:50%;font-size:1.1rem;cursor:pointer;display:none;z-index:999;box-shadow:0 4px 15px rgba(0,122,94,.3);transition:.2s}
#backToTop:hover{background:#005a46;transform:translateY(-2px)}
</style>
<div style="background:#f8faf9;min-height:80vh">
<div class="container py-2">
  <div class="terms-hero">
    <nav class="terms-breadcrumb"><a href="index.php">Home</a> &rsaquo; Terms &amp; Conditions</nav>
    <h1><i class="bi bi-file-earmark-text me-2"></i>Terms &amp; Conditions</h1>
    <p>By using GoGetGro, you agree to the following terms. Please read them carefully.</p>
  </div>

  <div class="terms-toc">
    <h6><i class="bi bi-list-ul me-1"></i>Contents</h6>
    <ol>
      <li><a href="#t1">Account Registration</a></li>
      <li><a href="#t2">Use of the System</a></li>
      <li><a href="#t3">Pricing and Product Availability</a></li>
      <li><a href="#t4">Payments and Security</a></li>
      <li><a href="#t5">Intellectual Property</a></li>
      <li><a href="#t6">Limitation of Liability</a></li>
      <li><a href="#t7">Changes to Terms</a></li>
      <li><a href="#t8">Return Policy</a></li>
    </ol>
  </div>

  <div class="terms-section" id="t1">
    <h3><span class="snum">1</span>Account Registration</h3>
    <p>To access certain features of GoGetGro, you may be required to create an account. You are responsible for maintaining the confidentiality of your account details and password. You agree to accept responsibility for all activities that occur under your account.</p>
    <p>The G3 QUAD team reserves the right to refuse service, terminate accounts, or remove or edit content at our sole discretion.</p>
  </div>

  <div class="terms-section" id="t2">
    <h3><span class="snum">2</span>Use of the System</h3>
    <p>GoGetGro provides an online platform for grocery shopping. Users are prohibited from:</p>
    <ul>
      <li>Using the system for any fraudulent or unlawful activity.</li>
      <li>Attempting to bypass the security features of the PHP and MySQL backend.</li>
      <li>Interfering with the smooth browsing experience of other users.</li>
      <li>Extracting data from the platform for commercial purposes without our consent.</li>
    </ul>
    <div class="terms-note"><i class="bi bi-exclamation-triangle me-1"></i>Violation of these terms may result in immediate account suspension.</div>
  </div>

  <div class="terms-section" id="t3">
    <h3><span class="snum">3</span>Pricing and Product Availability</h3>
    <p>We strive to ensure all products are listed with accurate pricing and availability. However, errors may occur. In the event that an item is listed at an incorrect price, GoGetGro reserves the right to cancel any orders placed for that item.</p>
    <p>Availability of items may change without notice due to the nature of grocery supply chains.</p>
  </div>

  <div class="terms-section" id="t4">
    <h3><span class="snum">4</span>Payments and Security</h3>
    <p>GoGetGro uses secure data handling protocols to process your transactions. By completing a purchase, you agree to provide current, complete, and accurate purchase and account information for all purchases made at our store.</p>
  </div>

  <div class="terms-section" id="t5">
    <h3><span class="snum">5</span>Intellectual Property</h3>
    <p>The GoGetGro system, including its UI/UX design, logos, graphics, and the "G3 QUAD" branding, is the intellectual property of the developers. You may not reproduce, duplicate, or copy material from GoGetGro unless specifically authorized by the team.</p>
  </div>

  <div class="terms-section" id="t6">
    <h3><span class="snum">6</span>Limitation of Liability</h3>
    <p>While we use Agile methodology to continuously improve the system, the G3 QUAD team does not guarantee that the platform will be error-free or uninterrupted. We are not liable for any loss or damage resulting from the use of our platform.</p>
  </div>

  <div class="terms-section" id="t7">
    <h3><span class="snum">7</span>Changes to Terms</h3>
    <p>We reserve the right to revise these terms at any time. As our system evolves through development cycles, we encourage users to check this page regularly to stay informed of any updates.</p>
  </div>

  <div class="terms-section" id="t8">
    <h3><span class="snum">8</span>Return Policy</h3>
    <p>GoGetGro implements a strict <strong>No Return Policy</strong> for all grocery items purchased through our platform.</p>
    <div class="terms-note">
      <i class="bi bi-info-circle me-1"></i>
      <strong>Exception:</strong> Returns or refunds are only accepted if the item sent from the store is expired upon delivery or pickup. If you receive an expired item, please email us directly at <strong>gogetgrosupport@gmail.com</strong> with your concern. All such concerns shall be discussed and resolved via email.
    </div>
  </div>

  <div class="terms-cta">
    <h4><i class="bi bi-chat-dots me-2"></i>Have Questions About Our Terms?</h4>
    <p>Contact the G3 QUAD team through our <a href="help.php" style="color:#d4f9ee;font-weight:600;">official support channel</a>.</p>
  </div>
</div>
</div>
<button id="backToTop" onclick="window.scrollTo({top:0,behavior:'smooth'})"><i class="bi bi-chevron-up"></i></button>
<script>window.onscroll=function(){document.getElementById('backToTop').style.display=(document.body.scrollTop>400||document.documentElement.scrollTop>400)?'block':'none'};</script>
<?php require_once __DIR__ . '/includes/footer.php'; ?>
