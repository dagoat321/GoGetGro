<?php
require __DIR__ . '/includes/bootstrap.php';
$pageTitle = 'Privacy Policy';
$extraCss = ['stylesheet1.css'];
require_once __DIR__ . '/includes/header.php';
?>
<style>
.policy-hero{background:linear-gradient(135deg,#007a5e,#00a884);padding:50px 30px 44px;border-radius:0 0 24px 24px;margin-bottom:36px}
.policy-breadcrumb{font-size:.85rem;color:rgba(255,255,255,.7);margin-bottom:12px}
.policy-breadcrumb a{color:rgba(255,255,255,.85);text-decoration:none}
.policy-hero h1{color:#fff;font-weight:900;font-size:2rem;margin:0}
.policy-hero p{color:rgba(255,255,255,.8);margin-top:8px}
.policy-toc{background:#fff;border-radius:14px;padding:22px 28px;margin-bottom:28px;box-shadow:0 2px 12px rgba(0,122,94,.07);border:1px solid #d8ede7}
.policy-toc h6{color:#007a5e;font-weight:700;text-transform:uppercase;letter-spacing:1px;font-size:.8rem;margin-bottom:12px}
.policy-toc ol{margin:0;padding-left:18px}
.policy-toc li{margin-bottom:5px}
.policy-toc li a{color:#007a5e;text-decoration:none;font-size:.9rem}
.policy-section{background:#fff;border-radius:16px;padding:32px 36px;margin-bottom:20px;box-shadow:0 2px 12px rgba(0,122,94,.07)}
.policy-section .snum{display:inline-flex;align-items:center;justify-content:center;width:34px;height:34px;background:#007a5e;color:#fff;border-radius:50%;font-weight:700;font-size:.88rem;margin-right:10px;flex-shrink:0}
.policy-section h3{color:#1a1a1a;font-weight:700;font-size:1.1rem;margin-bottom:14px;display:flex;align-items:center}
.policy-section p,.policy-section li{color:#555;line-height:1.75;font-size:.95rem}
.policy-section ul{padding-left:22px;margin-top:8px}
.policy-section li{margin-bottom:6px}
.policy-note{background:#f0faf6;border-left:4px solid #007a5e;border-radius:6px;padding:12px 16px;margin-top:10px;font-size:.88rem;color:#333}
.policy-cta{background:linear-gradient(135deg,#007a5e,#00a884);border-radius:16px;padding:32px 36px;text-align:center;color:#fff;margin-bottom:40px}
.policy-cta h4{font-weight:800;font-size:1.3rem;margin-bottom:8px}
#backToTop{position:fixed;bottom:30px;right:30px;width:44px;height:44px;background:#007a5e;color:#fff;border:none;border-radius:50%;font-size:1.1rem;cursor:pointer;display:none;z-index:999;box-shadow:0 4px 15px rgba(0,122,94,.3);transition:.2s}
#backToTop:hover{background:#005a46;transform:translateY(-2px)}
</style>
<div style="background:#f8faf9;min-height:80vh">
<div class="container py-2">
  <div class="policy-hero">
    <nav class="policy-breadcrumb"><a href="index.php">Home</a> &rsaquo; Privacy Policy</nav>
    <h1><i class="bi bi-shield-check me-2"></i>Privacy Policy</h1>
    <p>Last updated: May 2026 &mdash; We value your privacy and take data protection seriously.</p>
  </div>

  <div class="policy-toc">
    <h6><i class="bi bi-list-ul me-1"></i>Contents</h6>
    <ol>
      <li><a href="#p1">Information We Collect</a></li>
      <li><a href="#p2">How We Use Your Information</a></li>
      <li><a href="#p3">Log Files and Cookies</a></li>
      <li><a href="#p4">Data Security</a></li>
      <li><a href="#p5">Third-Party Privacy Policies</a></li>
      <li><a href="#p6">Your Data Rights</a></li>
      <li><a href="#p7">Changes to This Policy</a></li>
    </ol>
  </div>

  <div class="policy-section" id="p1">
    <h3><span class="snum">1</span>Information We Collect</h3>
    <p>When you register for an account on GoGetGro, we may ask for your personal information, including:</p>
    <ul>
      <li><strong>Contact Information:</strong> Name, email address, and phone number.</li>
      <li><strong>Delivery Details:</strong> Shipping address to ensure your groceries reach you.</li>
      <li><strong>Account Credentials:</strong> Username and encrypted passwords managed via PHP/MySQL backend.</li>
    </ul>
    <div class="policy-note"><i class="bi bi-info-circle me-1"></i>We never store your raw password — all passwords are hashed using industry-standard encryption.</div>
  </div>

  <div class="policy-section" id="p2">
    <h3><span class="snum">2</span>How We Use Your Information</h3>
    <ul>
      <li>Provide, operate, and maintain our grocery platform.</li>
      <li>Improve, personalize, and expand the user experience.</li>
      <li>Process your transactions and manage order history.</li>
      <li>Communicate with you regarding order updates or support.</li>
      <li>Find and prevent fraudulent activities.</li>
    </ul>
  </div>

  <div class="policy-section" id="p3">
    <h3><span class="snum">3</span>Log Files and Cookies</h3>
    <p>GoGetGro follows standard procedures of using log files and session cookies. These store information including preferences and visited pages, helping us optimize content and user experience.</p>
  </div>

  <div class="policy-section" id="p4">
    <h3><span class="snum">4</span>Data Security</h3>
    <p>The G3 QUAD team implements secure data handling protocols to protect your personal information from unauthorized access, alteration, or disclosure.</p>
    <div class="policy-note"><i class="bi bi-exclamation-triangle me-1"></i>No internet transmission is 100% secure, but we use commercially acceptable means to protect your data.</div>
  </div>

  <div class="policy-section" id="p5">
    <h3><span class="snum">5</span>Third-Party Privacy Policies</h3>
    <p>GoGetGro's Privacy Policy does not apply to other websites. We advise consulting respective Privacy Policies of third-party services (such as e-wallet or bank payment gateways) for more information.</p>
  </div>

  <div class="policy-section" id="p6">
    <h3><span class="snum">6</span>Your Data Rights</h3>
    <ul>
      <li><strong>Right to Access:</strong> You may request copies of your personal data.</li>
      <li><strong>Right to Rectification:</strong> You may request correction of inaccurate information.</li>
      <li><strong>Right to Erasure:</strong> You may request deletion of your personal data, under certain conditions.</li>
    </ul>
  </div>

  <div class="policy-section" id="p7">
    <h3><span class="snum">7</span>Changes to This Privacy Policy</h3>
    <p>Our Privacy Policy may be updated periodically as we continue Agile development. We will notify you of changes by posting the new policy on this page. We encourage you to review it regularly.</p>
  </div>

  <div class="policy-cta">
    <h4><i class="bi bi-envelope-heart me-2"></i>Questions About Our Policy?</h4>
    <p>Contact the G3 QUAD team through our <a href="help.php" style="color:#d4f9ee;font-weight:600;">official support channel</a> and we'll be happy to help.</p>
  </div>
</div>
</div>
<button id="backToTop" onclick="window.scrollTo({top:0,behavior:'smooth'})"><i class="bi bi-chevron-up"></i></button>
<script>window.onscroll=function(){document.getElementById('backToTop').style.display=(document.body.scrollTop>400||document.documentElement.scrollTop>400)?'block':'none'};</script>
<?php require_once __DIR__ . '/includes/footer.php'; ?>
