<?php
include 'db.php';

// Set timezone to ensure NOW() matches Malaysia time
date_default_timezone_set('Asia/Kuala_Lumpur');

// Fetch the 3 latest programs that HAVE NOT ended yet
$query = "SELECT * FROM programs 
          WHERE STR_TO_DATE(CONCAT(end_date, ' ', end_time), '%Y-%m-%d %H:%i:%s') >= NOW()
          ORDER BY created_at DESC 
          LIMIT 3";

$result = $conn->query($query);
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>CharityCare+ | Caring Together, Helping Each Other</title>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">

    <style>
        :root {
            --prof-blue: #1e3a8a;
            --accent-blue: #2964c9;
            --light-red: #ef4444;
            --soft-white: #f8fafc;
            --text-dark: #334155;
        }

        body {
            font-family: 'Inter', -apple-system, BlinkMacSystemFont, sans-serif;
            background-color: white;
            color: var(--text-dark);
            line-height: 1.6;
            scroll-behavior: smooth;
        }

        .navbar {
            background-color: white;
            border-bottom: 3px solid var(--prof-blue);
            padding: 0.6rem 0;
        }

        .navbar-brand img {
            width: 95px;
            height: auto;
            transition: transform 0.3s ease;
        }

        .logo-text {
            color: #3b3b3be8;
            font-weight: 700;
            letter-spacing: -1px;
        }

        .logo-accent {
            color: var(--accent-blue);
        }

        .nav-link {
            color: var(--prof-blue) !important;
            font-weight: 600;
            text-transform: uppercase;
            font-size: 0.85rem;
            margin: 0 12px;
        }

        .hero-container {
            position: relative;
            width: 100%;
            height: 700px;
            overflow: hidden;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .hero-img-element {
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            object-fit: cover;
            z-index: 1;
        }

        .hero-overlay-content {
            position: relative;
            z-index: 2;
            width: 100%;
            height: 100%;
            background: linear-gradient(rgba(30, 58, 138, 0.8), rgba(30, 58, 138, 0.8));
            display: flex;
            align-items: center;
            text-align: center;
        }

        .btn-primary-custom {
            background-color: var(--prof-blue);
            color: white;
            border: none;
            padding: 12px 35px;
            font-weight: bold;
            transition: background 0.3s;
        }

        .btn-primary-custom:hover {
            background-color: #172554;
            color: white;
        }

        .section-title {
            color: var(--prof-blue);
            font-weight: 800;
            position: relative;
            padding-bottom: 15px;
            margin-bottom: 30px;
        }

        .section-title::after {
            content: '';
            position: absolute;
            left: 0;
            bottom: 0;
            width: 55px;
            height: 4px;
            background-color: var(--light-red);
        }

        .about-section {
            padding: 90px 0;
            background-color: var(--soft-white);
        }

        .about-narrative p {
            font-size: 1.1rem;
            margin-bottom: 1.5rem;
            color: #475569;
            text-align: justify;
        }

        .value-item {
            padding: 30px;
            transition: transform 0.3s;
        }

        .value-icon {
            font-size: 2.8rem;
            color: var(--prof-blue);
            margin-bottom: 20px;
        }

        .activity-grid-item {
            border: 1px solid #e2e8f0;
            border-radius: 12px;
            background: white;
            overflow: hidden;
            height: 100%;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.05);
            display: flex;
            flex-direction: column;
        }

        .activity-grid-item:hover {
            box-shadow: 0 12px 20px -5px rgba(0, 0, 0, 0.1);
        }

        .activity-img {
            height: 180px;
            width: 100%;
            object-fit: cover;
        }

        .badge-type {
            background-color: var(--soft-white);
            color: var(--prof-blue);
            border: 1px solid var(--prof-blue);
            font-size: 0.75rem;
            text-transform: uppercase;
            padding: 5px 10px;
        }


        footer {
            background-color: var(--prof-blue);
            color: white;
            padding: 70px 0 25px;
        }

        footer a {
            color: rgba(255, 255, 255, 0.8);
            text-decoration: none;
            transition: color 0.3s;
        }

        footer a:hover {
            color: var(--light-red);
        }
    </style>
</head>

<body>

    <nav class="navbar navbar-expand-lg sticky-top">
        <div class="container">
            <a class="navbar-brand d-flex align-items-center" href="#">
                <img src="UPTM Logo.png" alt="UPTM Logo" class="me-3">
                <span class="logo-text fs-4">Charity<span class="logo-accent">Care+</span></span>
            </a>

            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#mainNav">
                <span class="navbar-toggler-icon"></span>
            </button>

            <div class="collapse navbar-collapse" id="mainNav">
                <ul class="navbar-nav mx-auto">
                    <li class="nav-item"><a class="nav-link" href="#home">Home</a></li>
                    <li class="nav-item"><a class="nav-link" href="#reports">Our Activities</a></li>
                    <li class="nav-item"><a class="nav-link" href="#about">About Us</a></li>
                    <li class="nav-item"><a class="nav-link" href="#contact">Contact Us</a></li>
                </ul>
                <div class="d-flex align-items-center">
                    <a href="login.php" class="btn btn-link text-decoration-none fw-bold me-2" style="color: var(--prof-blue) ">Log In</a>
                    <a href="signup.php" class="btn btn-primary-custom rounded-pill shadow-sm">Sign Up</a>
                </div>
            </div>
        </div>
    </nav>

    <header class="hero-container" id="home">
        <img src="../Staff/library4.jpg" alt="Charity in Malaysia" class="hero-img-element" onerror="this.onerror=null; this.src='library4.jpg';">
        <div class="hero-overlay-content">
            <div class="container text-white">
                <h1 class="display-3 fw-bold mb-3">"Caring Together, Helping Each Other"</h1>
                <p class="fs-4 mb-5 opacity-90">Connecting heart and hand to support meaningful causes across our campus.</p>
            </div>
        </div>
    </header>

    <section class="py-5 bg-white" id="reports">
        <div class="container">
            <div class="mb-5">
                <h2 class="section-title mb-0">Featured Activities</h2>
                <p class="text-muted">Browse active campaigns and volunteer opportunities within UPTM</p>
            </div>

            <div class="row g-4">
                <?php if ($result->num_rows > 0): ?>
                    <?php while ($row = $result->fetch_assoc()): ?>
                        <div class="col-md-4">
                            <div class="activity-grid-item">
                                <?php 
                                    $raw_path = $row['image_path'];
                                    if (strpos($raw_path, 'uploads/') === false) {
                                        $final_path = "../Staff/uploads/" . $raw_path;
                                    } else {
                                        $final_path = "../Staff/" . $raw_path;
                                    }
                                ?>
                                <img src="<?php echo htmlspecialchars($final_path); ?>" 
                                     class="activity-img" 
                                     alt="Campaign Image" 
                                     onerror="this.onerror=null; this.src='../Staff/default_campaign.jpg';">
                                
                                <div class="p-4 flex-grow-1 d-flex flex-column">
                                    <span class="badge badge-type mb-3"><?php echo htmlspecialchars($row['type']); ?></span>
                                    <h5 class="fw-bold"><?php echo htmlspecialchars($row['title']); ?></h5>
                                    
                                    <?php if ($row['type'] == 'Donation'): ?>
                                        <?php 
                                            $goal = $row['goal_amount'] ?? 0;
                                            $current = $row['current_amount'] ?? 0;
                                            $percent = ($goal > 0) ? min(100, round(($current / $goal) * 100)) : 0;
                                            $balance = max(0, $goal - $current);
                                        ?>
                                        <div class="mt-3 mb-1 d-flex justify-content-between align-items-end">
                                            <small class="fw-bold text-primary"><?php echo $percent; ?>% Raised</small>
                                            <small class="text-muted" style="font-size: 0.7rem;">Goal: RM <?php echo number_format($goal, 2); ?></small>
                                        </div>
                                        <div class="progress mb-2" style="height: 8px; background-color: #e2e8f0; border-radius: 10px;">
                                            <div class="progress-bar progress-bar-striped progress-bar-animated" 
                                                 role="progressbar" 
                                                 style="width: <?php echo $percent; ?>%; background-color: var(--accent-blue);" 
                                                 aria-valuenow="<?php echo $percent; ?>" aria-valuemin="0" aria-valuemax="100">
                                            </div>
                                        </div>
                                        <p class="small fw-bold mb-3" style="font-size: 0.75rem;">
                                            RM <?php echo number_format($balance, 2); ?> needed to reach goal
                                        </p>
                                    <?php else: ?>
                                        <p class="small text-muted mb-3"><?php echo substr(htmlspecialchars($row['description']), 0, 85) . '...'; ?></p>
                                    <?php endif; ?>

                                    <div class="d-flex gap-3 mb-4 small text-secondary mt-1">
                                        <div>
                                            <i class="far fa-calendar-alt me-1 text-primary"></i>
                                            <?php echo (!empty($row['start_date'])) ? date('d M Y', strtotime($row['start_date'])) : 'TBA'; ?>
                                        </div>
                                        <div>
                                            <i class="far fa-clock me-1 text-primary"></i>
                                            <?php echo (!empty($row['start_time'])) ? date('h:i A', strtotime($row['start_time'])) : 'TBA'; ?>
                                        </div>
                                    </div>

                                    <div class="d-flex justify-content-between align-items-center mt-auto">
                                        <?php if ($row['type'] == 'Donation'): ?>
                                            <a href="public_donate.php?id=<?php echo $row['id']; ?>" class="btn btn-sm btn-outline-primary px-4 rounded-pill w-100 py-2 fw-bold">
                                                Donate Now
                                            </a>
                                        <?php else: ?>
                                            <button class="btn btn-sm btn-outline-primary px-4 rounded-pill w-100 py-2 fw-bold" onclick="checkAuth()">
                                                Join Volunteer
                                            </button>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            </div>
                        </div>
                    <?php endwhile; ?>
                <?php else: ?>
                    <div class="col-12">
                        <div class="no-campaign-box shadow-sm text-center py-5">
                            <i class="fas fa-bullhorn fa-3x mb-3 opacity-25"></i>
                            <h4 class="fw-bold">No Active Campaigns Yet</h4>
                            <p class="mb-0">Check back later! Organizers are preparing new initiatives for the UPTM community.</p>
                        </div>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </section>

    <section class="about-section" id="about">
        <div class="container">
            <div class="row">
                <div class="col-lg-12">
                    <h2 class="section-title">About Us</h2>
                    <div class="about-narrative">
                        <p>University Poly-Tech Malaysia (UPTM) is a premier institution dedicated to hands-on learning and fostering social responsibility within its community. This system introduces <strong>CharityCare+</strong>, a centralized web-based platform designed specifically to overcome traditional manual challenges. By replacing manual procedures with an efficient digital system, we empower UPTM students and organizers to plan, execute, and track charity events with absolute precision.</p>
                        <p>In this ecosystem, <strong>Donation</strong> is a transparent financial contribution where students and staff can securely support specific campus-led causes, such as welfare funds or relief efforts. Instead of anonymous manual collections, the system provides 100% clarity on where funds go, allowing donors to see real-time progress through campaign funding percentages. Students select a campaign, enter their contribution, and receive immediate verification of their impact. <strong>Volunteering</strong> is the active contribution of time and skills to community projects. Through the system, student clubs post specific volunteer needs, and students can browse and "Join Volunteer" with a single click. This moves the university away from disorganized manual sign-up sheets to a managed digital roster, ensuring that every event has the right team to succeed.</p>
                    </div>

                    <div class="row text-center mt-5">
                        <div class="col-md-4">
                            <div class="value-item">
                                <i class="fas fa-lightbulb value-icon"></i>
                                <h5 class="fw-bold">Our Vision</h5>
                                <p class="small text-muted">A world where social support is accessible, transparent and driven by a community of care.</p>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="value-item">
                                <i class="fas fa-handshake value-icon"></i>
                                <h5 class="fw-bold">Our Mission</h5>
                                <p class="small text-muted">To provide a seamless platform where donors and volunteers connect directly with those in need.</p>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="value-item">
                                <i class="fas fa-users value-icon"></i>
                                <h5 class="fw-bold">Our Impact Goal</h5>
                                <p class="small text-muted">To empower every UPTM community member to contribute with 100% clarity.</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <footer id="contact">
        <div class="container">
            <div class="row g-5">
                <div class="col-md-4">
                    <h5 class="fw-bold mb-4">UPTM CharityCare+</h5>
                    <p class="small opacity-75">A centralized donation and volunteer management system dedicated to UPTM. Empowering our community through collaborative action.</p>
                </div>
                <div class="col-md-3 offset-md-1">
                    <h6 class="fw-bold mb-4">LINKS</h6>
                    <ul class="list-unstyled small">
                        <li class="mb-3"><a href="../Staff/staff_login.php">Organizer Portal</a></li>
                    </ul>
                </div>
                <div class="col-md-4">
                    <h6 class="fw-bold mb-4">Contact Us</h6>
                    <ul class="list-unstyled small opacity-75">
                        <li class="mb-3"><i class="fas fa-envelope me-3 text-danger"></i>pluscharitycare@gmail.com</li>
                        <li class="mb-3"><i class="fas fa-phone me-3 text-danger"></i>03-9206 9700</li>
                        <li class="mb-3"><i class="fas fa-map-marker-alt me-3 text-danger"></i> Universiti Poly-Tech Malaysia, Kuala Lumpur</li>
                        <li class="mb-3"><i class="fas fa-video me-3 text-danger"></i><a href="https://youtu.be/_YKgA5cgadw" target="_blank" rel="noopener noreferrer">Watch User Manual</a></li>
                    </ul>
                </div>
            </div>
            <hr class="my-5 opacity-25">
            <p class="text-center small mb-0 opacity-50">&copy; 2026 CharityCare+. All Rights Reserved.</p>
        </div>
    </footer>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        function checkAuth() {
            alert("Please log in or sign up to continue.");
            window.location.href = "login.php";
        }
    </script>
</body>
</html>
