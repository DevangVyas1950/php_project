<?php  we were only 11
require_once 'config/constants.php';
if (is_logged_in()) {
    if (is_admin()) redirect('admin/dashboard.php');
    else redirect('user/dashboard.php');
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="description" content="FitLife - Your personal health & fitness companion">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>FitLife - Health & Fitness</title>

    <!-- Google Font -->
    <link href="https://fonts.googleapis.com/css?family=Nunito+Sans:400,600,700,800,900&display=swap" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css?family=Oswald:300,400,500,600,700&display=swap" rel="stylesheet">

    <!-- Css Styles (Activitar template, bundled with FitLife) -->
    <link rel="stylesheet" href="assets/activitar/css/bootstrap.min.css" type="text/css">
    <link rel="stylesheet" href="assets/activitar/css/font-awesome.min.css" type="text/css">
    <link rel="stylesheet" href="assets/activitar/css/elegant-icons.css" type="text/css">
    <link rel="stylesheet" href="assets/activitar/css/nice-select.css" type="text/css">
    <link rel="stylesheet" href="assets/activitar/css/owl.carousel.min.css" type="text/css">
    <link rel="stylesheet" href="assets/activitar/css/magnific-popup.css" type="text/css">
    <link rel="stylesheet" href="assets/activitar/css/slicknav.min.css" type="text/css">
    <link rel="stylesheet" href="assets/activitar/css/style.css" type="text/css">
    <link rel="stylesheet" href="assets/css/fitlife-theme.css" type="text/css">
</head>

<body>
    <!-- Page Preloder -->
    <div id="preloder">
        <div class="loader"></div>
    </div>

    <!-- Header Section Begin -->
    <header class="header-section">
        <div class="container-fluid">
            <div class="logo">
                <a href="index.php"><i class="fa fa-heartbeat"></i> FitLife</a>
            </div>
            <div class="top-social">
                <a href="login.php" class="fl-auth-link">Login</a>
                <a href="register.php" class="fl-auth-link fl-auth-cta">Sign Up Free</a>
            </div>
            <div class="container">
                <div class="nav-menu">
                    <nav class="mainmenu mobile-menu">
                        <ul>
                            <li class="active"><a href="index.php">Home</a></li>
                            <li><a href="#features">Features</a></li>
                            <li><a href="#about">About</a></li>
                            <li><a href="#plans">Programs</a></li>
                            <li><a href="login.php">Login</a></li>
                            <li><a href="register.php">Sign Up</a></li>
                        </ul>
                    </nav>
                </div>
            </div>
            <div id="mobile-menu-wrap"></div>
        </div>
    </header>
    <!-- Header End -->

    <!-- Hero Section Begin -->
    <section class="hero-section">
        <div class="hero-items owl-carousel">
            <div class="single-hero-item set-bg" data-setbg="assets/activitar/img/hero-slider/hero-1.jpg">
                <div class="container">
                    <div class="row">
                        <div class="col-lg-12">
                            <div class="hero-text">
                                <h2>Join Us Now</h2>
                                <h1>STAY FIT, STAY HEALTHY</h1>
                                <a href="register.php" class="primary-btn">Get Started</a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="single-hero-item set-bg" data-setbg="assets/activitar/img/hero-slider/hero-2.jpg">
                <div class="container">
                    <div class="row">
                        <div class="col-lg-12">
                            <div class="hero-text">
                                <h2>Track Every Step</h2>
                                <h1>DIET, WORKOUTS & PROGRESS</h1>
                                <a href="register.php" class="primary-btn">Get Started</a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="single-hero-item set-bg" data-setbg="assets/activitar/img/hero-slider/hero-3.jpg">
                <div class="container">
                    <div class="row">
                        <div class="col-lg-12">
                            <div class="hero-text">
                                <h2>Personalized For You</h2>
                                <h1>YOUR FITNESS COMPANION</h1>
                                <a href="login.php" class="primary-btn">Login</a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
    <!-- Hero End -->

    <!-- Feature Section Begin -->
    <section class="feature-section" id="features">
        <div class="container-fluid">
            <div class="row">
                <div class="col-md-4">
                    <div class="feature-item set-bg" data-setbg="assets/activitar/img/feature/feature-1.jpg">
                        <h3>DIET CHARTS</h3>
                        <p>Personalized meal plans built around your<br /> goal, age and body stats</p>
                        <a href="register.php" class="primary-btn f-btn">Get Started</a>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="feature-item set-bg" data-setbg="assets/activitar/img/feature/feature-2.jpg">
                        <h3>EXERCISE LIBRARY</h3>
                        <p>Cardio, strength, yoga and flexibility<br /> routines for every level</p>
                        <a href="register.php" class="primary-btn f-btn">Get Started</a>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="feature-item set-bg" data-setbg="assets/activitar/img/feature/feature-3.jpg">
                        <h3>PROGRESS TRACKING</h3>
                        <p>Log weight, food and daily tasks to<br /> see real results over time</p>
                        <a href="register.php" class="primary-btn f-btn">Get Started</a>
                    </div>
                </div>
            </div>
        </div>
    </section>
    <!-- Feature Section End -->

    <!-- About Section Begin -->
    <section class="home-about spad" id="about">
        <div class="container">
            <div class="row">
                <div class="col-lg-6">
                    <div class="about-text">
                        <h2>WELCOME TO FITLIFE</h2>
                        <p class="short-details">Your personal health & fitness companion, all in one dashboard.</p>
                        <p class="long-details">FitLife brings your diet charts, exercise plans, food logging, daily
                            tasks and weight progress together in one place. Set a goal — lose weight, gain muscle,
                            or stay healthy — and FitLife tailors recommendations to your age, weight, height and
                            gender automatically.</p>
                        <a href="register.php" class="primary-btn about-btn">Create Your Account</a>
                    </div>
                </div>
                <div class="col-lg-6">
                    <div class="about-img">
                        <img src="assets/activitar/img/home-about.jpg" alt="FitLife">
                    </div>
                </div>
            </div>
        </div>
    </section>
    <!-- About Section End -->

    <!-- Classes Section Begin -->
    <section class="classes-section" id="plans">
        <div class="class-title set-bg" data-setbg="assets/activitar/img/classes-title-bg.jpg">
            <div class="container">
                <div class="row">
                    <div class="col-lg-7 m-auto text-center">
                        <div class="section-title pl-lg-4 pr-lg-4 pl-0 pr-0">
                            <h2>Choose Your Goal</h2>
                            <p>FitLife adapts your diet and workout recommendations to the goal you set at sign-up.</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="container-fluid">
            <div class="row">
                <div class="col-lg-4 col-sm-6">
                    <div class="classes-item set-bg" data-setbg="assets/activitar/img/classes/class-1.jpg">
                        <h4>Lose Weight</h4>
                        <p>Calorie-controlled meal plans and cardio-focused routines to burn fat sustainably.</p>
                        <a href="register.php" class="primary-btn class-btn">Get Started</a>
                    </div>
                </div>
                <div class="col-lg-4 col-sm-6">
                    <div class="classes-item set-bg" data-setbg="assets/activitar/img/classes/class-2.jpg">
                        <h4>Gain Muscle</h4>
                        <p>Higher protein targets paired with strength training to build lean muscle.</p>
                        <a href="register.php" class="primary-btn class-btn">Get Started</a>
                    </div>
                </div>
                <div class="col-lg-4 col-sm-6">
                    <div class="classes-item set-bg" data-setbg="assets/activitar/img/classes/class-3.jpg">
                        <h4>Stay Healthy</h4>
                        <p>Balanced nutrition and flexibility work to keep you feeling your best.</p>
                        <a href="register.php" class="primary-btn class-btn">Get Started</a>
                    </div>
                </div>
            </div>
        </div>
    </section>
    <!-- Classes Section End -->

    <!-- Footer Section Begin -->
    <footer class="footer-section">
        <div class="container">
            <div class="row">
                <div class="col-lg-4">
                    <div class="footer-logo-item">
                        <div class="f-logo">
                            <a href="index.php"><i class="fa fa-heartbeat"></i> FitLife</a>
                        </div>
                        <p>Your personal health & fitness companion — diet charts, workouts, food logs and progress
                            tracking in one dashboard.</p>
                    </div>
                </div>
                <div class="col-lg-4">
                    <div class="footer-widget">
                        <h5>Get Started</h5>
                        <ul class="workout-program">
                            <li><a href="register.php">Create an account</a></li>
                            <li><a href="login.php">Login</a></li>
                            <li><a href="#features">Features</a></li>
                            <li><a href="#about">About FitLife</a></li>
                        </ul>
                    </div>
                </div>
                <div class="col-lg-4">
                    <div class="footer-widget">
                        <h5>Programs</h5>
                        <ul class="footer-info">
                            <li><span>Lose Weight</span></li>
                            <li><span>Gain Muscle</span></li>
                            <li><span>Stay Healthy</span></li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
        <div class="copyright-text">
            <div class="container">
                <div class="row">
                    <div class="col-lg-12 text-center">
                        <div class="ct-inside">
                            &copy; <?= date('Y') ?> FitLife. Design based on the free
                            <a href="https://colorlib.com" target="_blank" rel="noopener">Activitar template by Colorlib</a>,
                            licensed under CC BY 3.0.
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </footer>
    <!-- Footer Section End -->

    <!-- Js Plugins -->
    <script src="assets/activitar/js/jquery-3.3.1.min.js"></script>
    <script src="assets/activitar/js/bootstrap.min.js"></script>
    <script src="assets/activitar/js/jquery.magnific-popup.min.js"></script>
    <script src="assets/activitar/js/mixitup.min.js"></script>
    <script src="assets/activitar/js/jquery.nice-select.min.js"></script>
    <script src="assets/activitar/js/jquery.slicknav.js"></script>
    <script src="assets/activitar/js/owl.carousel.min.js"></script>
    <script src="assets/activitar/js/masonry.pkgd.min.js"></script>
    <script src="assets/activitar/js/main.js"></script>
</body>

</html>
