<?php
require __DIR__ . '/includes/bootstrap.php';
$pageTitle = 'FAQs';
$extraCss = ['stylesheet1.css'];
require_once __DIR__ . '/includes/header.php';
?>
<style>
.faqs-hero{background:linear-gradient(135deg,#007a5e,#00a884);padding:50px 30px 44px;border-radius:0 0 24px 24px;margin-bottom:36px}
.faqs-breadcrumb{font-size:.85rem;color:rgba(255,255,255,.7);margin-bottom:12px}
.faqs-breadcrumb a{color:rgba(255,255,255,.85);text-decoration:none}
.faqs-hero h1{color:#fff;font-weight:900;font-size:2rem;margin:0}
.faqs-hero p{color:rgba(255,255,255,.8);margin-top:8px}

.faq-nav{display:flex;flex-wrap:wrap;gap:10px;margin-bottom:32px}
.faq-nav a{display:inline-flex;align-items:center;gap:6px;padding:8px 18px;border-radius:50px;background:#fff;color:#007a5e;font-weight:600;font-size:.88rem;text-decoration:none;border:2px solid #c8e8de;transition:.2s}
.faq-nav a:hover,.faq-nav a.active{background:#007a5e;color:#fff;border-color:#007a5e}

.faq-category-block{margin-bottom:36px}
.faq-category-title{display:flex;align-items:center;gap:10px;color:#007a5e;font-weight:800;font-size:1.2rem;margin-bottom:16px;padding-bottom:10px;border-bottom:2px solid #e0ede8}
.faq-category-title i{font-size:1.4rem}

.faq-item{background:#fff;border-radius:14px;margin-bottom:10px;box-shadow:0 2px 8px rgba(0,122,94,.06);overflow:hidden;border:1px solid #e8f0ec}
.faq-question{padding:18px 20px;font-weight:600;font-size:.97rem;color:#1a1a1a;cursor:pointer;display:flex;justify-content:space-between;align-items:center;transition:.2s;user-select:none}
.faq-question:hover{background:#f0faf6;color:#007a5e}
.faq-question.open{background:#007a5e;color:#fff}
.faq-question .faq-icon{font-size:1rem;transition:transform .3s;flex-shrink:0;margin-left:10px}
.faq-question.open .faq-icon{transform:rotate(180deg)}
.faq-answer{display:none;padding:0 20px 18px;color:#555;font-size:.93rem;line-height:1.75;border-top:1px solid #e8f0ec}
.faq-answer.show{display:block}

.faq-cta{background:linear-gradient(135deg,#007a5e,#00a884);border-radius:16px;padding:32px 36px;text-align:center;color:#fff;margin-bottom:40px}
.faq-cta h4{font-weight:800;font-size:1.3rem;margin-bottom:8px}

#backToTop{position:fixed;bottom:30px;right:30px;width:44px;height:44px;background:#007a5e;color:#fff;border:none;border-radius:50%;font-size:1.1rem;cursor:pointer;display:none;z-index:999;box-shadow:0 4px 15px rgba(0,122,94,.3);transition:.2s}
#backToTop:hover{background:#005a46;transform:translateY(-2px)}
</style>

<div style="background:#f8faf9;min-height:80vh">
<div class="container py-2">
  <div class="faqs-hero">
    <nav class="faqs-breadcrumb"><a href="index.php">Home</a> &rsaquo; FAQs</nav>
    <h1><i class="bi bi-question-circle me-2"></i>Frequently Asked Questions</h1>
    <p>Everything you need to know about shopping with GoGetGro.</p>
  </div>

  <div class="faq-nav">
    <a href="#faq-orders" class="active"><i class="bi bi-bag-check"></i> Orders</a>
    <a href="#faq-payments"><i class="bi bi-credit-card"></i> Payments</a>
    <a href="#faq-delivery"><i class="bi bi-truck"></i> Delivery</a>
    <a href="#faq-returns"><i class="bi bi-arrow-repeat"></i> Returns</a>
    <a href="#faq-account"><i class="bi bi-person"></i> Account</a>
  </div>

  <!-- ORDERS -->
  <div class="faq-category-block" id="faq-orders">
    <div class="faq-category-title"><i class="bi bi-bag-check"></i> Orders</div>
    <div class="faq-item">
      <div class="faq-question" onclick="toggleFaq(this)">How do I place an order? <i class="bi bi-chevron-down faq-icon"></i></div>
      <div class="faq-answer">Browse products by category or search for specific items. Add them to your cart, then head to Checkout. Choose your delivery or pickup option, select your payment method, and click Confirm Order.</div>
    </div>
    <div class="faq-item">
      <div class="faq-question" onclick="toggleFaq(this)">Can I cancel or modify my order? <i class="bi bi-chevron-down faq-icon"></i></div>
      <div class="faq-answer">Yes, you can modify or cancel your order while it is still in "To Pay" or "To Ship" status. Once an order is in transit, changes may not be possible. Please contact support for assistance.</div>
    </div>
    <div class="faq-item">
      <div class="faq-question" onclick="toggleFaq(this)">How do I track my order? <i class="bi bi-chevron-down faq-icon"></i></div>
      <div class="faq-answer">Track your order through your account under <strong>Order History</strong>. Each order shows its current status: To Pay, To Ship, or Completed.</div>
    </div>
    <div class="faq-item">
      <div class="faq-question" onclick="toggleFaq(this)">Do I need an account to place an order? <i class="bi bi-chevron-down faq-icon"></i></div>
      <div class="faq-answer">Yes, creating an account allows you to track orders, save delivery addresses, and manage your purchase history easily. You can browse without an account, but checkout requires login.</div>
    </div>
    <div class="faq-item">
      <div class="faq-question" onclick="toggleFaq(this)">Can I reorder previous purchases? <i class="bi bi-chevron-down faq-icon"></i></div>
      <div class="faq-answer">Yes! Go to your Order History, find the completed order, and click <strong>Buy Again</strong> to quickly add those items back to your cart.</div>
    </div>
  </div>

  <!-- PAYMENTS -->
  <div class="faq-category-block" id="faq-payments">
    <div class="faq-category-title"><i class="bi bi-credit-card"></i> Payments</div>
    <div class="faq-item">
      <div class="faq-question" onclick="toggleFaq(this)">What payment methods are accepted? <i class="bi bi-chevron-down faq-icon"></i></div>
      <div class="faq-answer">We accept <strong>Cash on Delivery (COD)</strong> and various online payment gateways including GCash, Maya, BDO Online Banking, BPI, Metrobank, UnionBank, SeaBank, CIMB, InstaPay, and DragonPay.</div>
    </div>
    <div class="faq-item">
      <div class="faq-question" onclick="toggleFaq(this)">Is my payment secure? <i class="bi bi-chevron-down faq-icon"></i></div>
      <div class="faq-answer">Yes, all transactions are encrypted and securely processed. We use industry-standard security protocols to protect your payment information.</div>
    </div>
    <div class="faq-item">
      <div class="faq-question" onclick="toggleFaq(this)">Can I use a voucher code? <i class="bi bi-chevron-down faq-icon"></i></div>
      <div class="faq-answer">Yes! Enter your voucher code in the checkout page. For example, use code <strong>G3Launch</strong> for 10% off on orders of ₱999 and above.</div>
    </div>
    <div class="faq-item">
      <div class="faq-question" onclick="toggleFaq(this)">Why was my payment declined? <i class="bi bi-chevron-down faq-icon"></i></div>
      <div class="faq-answer">This may be due to insufficient balance, incorrect payment details, or restrictions from your bank. Please double-check your information or try a different payment method.</div>
    </div>
  </div>

  <!-- DELIVERY -->
  <div class="faq-category-block" id="faq-delivery">
    <div class="faq-category-title"><i class="bi bi-truck"></i> Delivery</div>
    <div class="faq-item">
      <div class="faq-question" onclick="toggleFaq(this)">How long does delivery take? <i class="bi bi-chevron-down faq-icon"></i></div>
      <div class="faq-answer">Regular delivery takes 1–3 business days. Express delivery is available for next-day arrival, and Priority delivery offers same-day service in selected areas.</div>
    </div>
    <div class="faq-item">
      <div class="faq-question" onclick="toggleFaq(this)">What are the delivery fees? <i class="bi bi-chevron-down faq-icon"></i></div>
      <div class="faq-answer">Delivery fees depend on the type: <strong>Regular — ₱50</strong>, <strong>Express — ₱150</strong>, <strong>Priority — ₱250</strong>. Store pickup is free.</div>
    </div>
    <div class="faq-item">
      <div class="faq-question" onclick="toggleFaq(this)">Do you deliver nationwide? <i class="bi bi-chevron-down faq-icon"></i></div>
      <div class="faq-answer">Yes, we deliver to most locations nationwide, but availability may vary in remote areas. Contact our support for more information about your specific location.</div>
    </div>
    <div class="faq-item">
      <div class="faq-question" onclick="toggleFaq(this)">What happens if I miss my delivery? <i class="bi bi-chevron-down faq-icon"></i></div>
      <div class="faq-answer">Our courier will attempt redelivery or contact you to reschedule. Please ensure someone is available at your delivery address during the scheduled time.</div>
    </div>
  </div>

  <!-- RETURNS -->
  <div class="faq-category-block" id="faq-returns">
    <div class="faq-category-title"><i class="bi bi-arrow-repeat"></i> Returns &amp; Refunds</div>
    <div class="faq-item">
      <div class="faq-question" onclick="toggleFaq(this)">What if I receive damaged items? <i class="bi bi-chevron-down faq-icon"></i></div>
      <div class="faq-answer">Report the issue within 24 hours of receiving your order through our Help page. We will arrange a replacement or a full refund for damaged items.</div>
    </div>
    <div class="faq-item">
      <div class="faq-question" onclick="toggleFaq(this)">How do refunds work? <i class="bi bi-chevron-down faq-icon"></i></div>
      <div class="faq-answer">Refunds are processed within 3–7 business days depending on your payment method. Online payments are refunded to the original source; COD refunds are issued via GCash or bank transfer.</div>
    </div>
    <div class="faq-item">
      <div class="faq-question" onclick="toggleFaq(this)">Can I return items if I change my mind? <i class="bi bi-chevron-down faq-icon"></i></div>
      <div class="faq-answer">Returns may be accepted for non-perishable items in original condition within 24 hours. Fresh grocery items cannot be returned once delivered unless damaged or incorrect.</div>
    </div>
  </div>

  <!-- ACCOUNT -->
  <div class="faq-category-block" id="faq-account">
    <div class="faq-category-title"><i class="bi bi-person"></i> Account</div>
    <div class="faq-item">
      <div class="faq-question" onclick="toggleFaq(this)">How do I create an account? <i class="bi bi-chevron-down faq-icon"></i></div>
      <div class="faq-answer">Click the <strong>Sign Up</strong> button on the top navigation bar. Fill in your full name, username, email address, and password, then submit the form.</div>
    </div>
    <div class="faq-item">
      <div class="faq-question" onclick="toggleFaq(this)">I forgot my password. What should I do? <i class="bi bi-chevron-down faq-icon"></i></div>
      <div class="faq-answer">Click <strong>"Forgot Password?"</strong> on the login page and enter your registered email address. Follow the instructions sent to reset your password.</div>
    </div>
    <div class="faq-item">
      <div class="faq-question" onclick="toggleFaq(this)">Can I update my account details? <i class="bi bi-chevron-down faq-icon"></i></div>
      <div class="faq-answer">Yes, go to your <a href="profile.php" style="color:#007a5e;font-weight:600;">Profile page</a> and click <strong>Edit Info</strong> to update your name and email. You can also change your password and manage saved addresses.</div>
    </div>
    <div class="faq-item">
      <div class="faq-question" onclick="toggleFaq(this)">How do I subscribe to the newsletter? <i class="bi bi-chevron-down faq-icon"></i></div>
      <div class="faq-answer">Enter your email in the newsletter box at the bottom of any page, or manage your newsletter subscription from your <a href="profile.php" style="color:#007a5e;font-weight:600;">Profile page</a>.</div>
    </div>
  </div>

  <div class="faq-cta">
    <h4><i class="bi bi-headset me-2"></i>Still have questions?</h4>
    <p>Our support team is ready to help. <a href="help.php" style="color:#d4f9ee;font-weight:600;">Contact Us</a> anytime.</p>
  </div>
</div>
</div>

<button id="backToTop" onclick="window.scrollTo({top:0,behavior:'smooth'})"><i class="bi bi-chevron-up"></i></button>
<script>
function toggleFaq(el) {
    const answer = el.nextElementSibling;
    const isOpen = el.classList.contains('open');
    // Close all open items in same category
    document.querySelectorAll('.faq-question.open').forEach(q => {
        q.classList.remove('open');
        q.nextElementSibling.classList.remove('show');
    });
    if (!isOpen) {
        el.classList.add('open');
        answer.classList.add('show');
    }
}
window.onscroll = function() {
    document.getElementById('backToTop').style.display =
        (document.body.scrollTop > 400 || document.documentElement.scrollTop > 400) ? 'block' : 'none';
};
// Smooth scroll for nav links
document.querySelectorAll('.faq-nav a').forEach(link => {
    link.addEventListener('click', function(e) {
        document.querySelectorAll('.faq-nav a').forEach(a => a.classList.remove('active'));
        this.classList.add('active');
    });
});
</script>
<?php require_once __DIR__ . '/includes/footer.php'; ?>
