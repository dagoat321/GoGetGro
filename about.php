<?php
require __DIR__ . '/includes/bootstrap.php';

$pageTitle = 'About Us';
$extraCss = ['stylesheet1.css'];
require_once __DIR__ . '/includes/header.php';
?>

<style>
.about-hero {
    background: linear-gradient(135deg, #007a5e 0%, #00a884 100%);
    padding: 50px 30px 44px;
    border-radius: 0 0 24px 24px;
    margin-bottom: 36px;
    position: relative;
    overflow: hidden;
}
.about-hero::before {
    content: 'GoGetGro';
    position: absolute;
    right: -30px;
    top: 50%;
    transform: translateY(-50%);
    font-size: 9rem;
    font-weight: 900;
    color: rgba(255,255,255,0.07);
    letter-spacing: -4px;
    pointer-events: none;
    user-select: none;
}
.about-hero h1 { color: #fff; font-weight: 900; font-size: 2rem; margin: 0; }
.about-hero p { color: rgba(255,255,255,0.8); margin-top: 8px; }
.about-breadcrumb { font-size: 0.85rem; color: rgba(255,255,255,0.7); margin-bottom: 12px; }
.about-breadcrumb a { color: rgba(255,255,255,0.85); text-decoration: none; }
.about-breadcrumb a:hover { color: #fff; text-decoration: underline; }

.about-section {
    background: #fff;
    border-radius: 16px;
    padding: 32px 36px;
    margin-bottom: 20px;
    box-shadow: 0 2px 12px rgba(0,122,94,0.07);
}
.about-section h2 { color: #1a1a1a; font-weight: 700; font-size: 1.5rem; margin-bottom: 14px; }
.about-section h3 { color: #1a1a1a; font-weight: 700; font-size: 1.1rem; margin-bottom: 14px; display: flex; align-items: center; }
.about-section p, .about-section li { color: #555; line-height: 1.75; font-size: 0.95rem; }
.about-section ul { margin-top: 8px; padding-left: 22px; }
.about-section ul li { margin-bottom: 6px; }

.about-banner-footer {
    background: linear-gradient(135deg, #007a5e 0%, #005a46 100%);
    border-radius: 16px;
    padding: 36px 40px;
    margin-bottom: 40px;
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 20px;
    flex-wrap: wrap;
}
.about-banner-footer .text-left { color: rgba(255,255,255,0.7); font-size: 1.1rem; font-weight: 300; }
.about-banner-footer .logo-center img { height: 56px; filter: brightness(0) invert(1); }
.about-banner-footer .text-right { color: #fff; font-size: 1.1rem; font-weight: 700; }
</style>

<main class="content-wrapper">
    <div class="container my-5">
        <div class="about-hero">
            <nav class="about-breadcrumb"><a href="index.php">Home</a> &rsaquo; <span>About Us</span></nav>
            <h1><i class="bi bi-info-circle me-2"></i>GO GET GROCERIES,</h1>
            <p>The Fast and Easy Way. Discover why thousands of Filipinos trust GoGetGro for their grocery needs.</p>
        </div>

        <div class="about-section">
            <h2><i class="bi bi-bag-heart me-2" style="color:#007a5e;"></i>There's More to Discover with GoGetGro.</h2>
            <p>More and more users are turning to GoGetGro and discovering the fast, convenient, and accessible shopping experience our platform provides. You might be wondering what makes GoGetGro special — let us show you the many reasons why our system stands out.</p>
        </div>

        <div class="row g-4 mb-4">
            <div class="col-md-6">
                <div class="about-section h-100 mb-0">
                    <h3><i class="bi bi-phone me-2" style="color:#007a5e;"></i>More Convenience</h3>
                    <p>At GoGetGro, shopping for groceries becomes faster and easier. Browse products, add items to your cart, and complete purchases without the hassle of traditional shopping — anytime, anywhere.</p>
                </div>
            </div>
            <div class="col-md-6">
                <div class="about-section h-100 mb-0">
                    <h3><i class="bi bi-cpu me-2" style="color:#007a5e;"></i>More Efficiency</h3>
                    <p>Built with <strong>PHP, MySQL, HTML, CSS, JavaScript, and Bootstrap</strong> on XAMPP — these technologies work together to deliver secure data handling, smooth browsing, and a responsive experience.</p>
                </div>
            </div>
            <div class="col-md-6">
                <div class="about-section h-100 mb-0">
                    <h3><i class="bi bi-palette me-2" style="color:#007a5e;"></i>More User-Friendly Design</h3>
                    <p>Our 60-30-10 UI/UX rule uses green & white for freshness, grayscale for clarity, and accent colors for key interactions — making GoGetGro visually appealing and easy to navigate.</p>
                </div>
            </div>
            <div class="col-md-6">
                <div class="about-section h-100 mb-0">
                    <h3><i class="bi bi-arrow-repeat me-2" style="color:#007a5e;"></i>More Innovation</h3>
                    <p>Following Agile methodology, our team continuously improves the platform — identifying issues early, adapting to new needs, and delivering a better experience with each development cycle.</p>
                </div>
            </div>
        </div>

        <div class="about-section mb-4 border-0">
            <h3><i class="bi bi-people me-2" style="color:#007a5e;"></i>More Teamwork Behind the System</h3>
            <p class="mb-4">G3 QUAD represents the collaborative effort of four developers behind GoGetGro. <strong>G3</strong> stands for <em>Go, Get, Gro</em> &mdash; fast access, easy purchasing, grocery convenience. <strong>QUAD</strong> represents our four-member team.</p>
            <p style="font-weight: 600; color: #007a5e; font-size: 1.05rem;">
                Angelika Serrano, Michaela Libut, Frederick Castro, Rica Tuvillara
            </p>
        </div>

        <div class="about-banner-footer border-0">
            <div class="text-left">Go get groceries...</div>
            <div class="logo-center"><img src="images/Group 19.png" alt="GoGetGro Logo"></div>
            <div class="text-right">the fast and easy way!</div>
        </div>
    </div>
</main>

<?php require_once __DIR__ . '/includes/footer.php'; ?>

