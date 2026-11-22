<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>HB FITNESS — Train Smart. Eat Well. Reuse More.</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <link rel="stylesheet" href="view/assets/css/fitness.css">
</head>
<body>
<canvas id="fitness-canvas"></canvas>

<?php if (!empty($flashMessage)): ?>
<div class="toast-alert <?= $flashType === 'error' ? 'toast-error' : '' ?>">
    <i class="fa-solid <?= $flashType === 'error' ? 'fa-circle-exclamation' : 'fa-circle-check' ?>"></i>
    <span><?= htmlspecialchars($flashMessage) ?></span>
</div>
<?php endif; ?>

<div class="app-wrapper">
    <!-- Navbar -->
    <nav class="landing-nav" id="landingNav">
        <a href="index.php?page=landing" class="brand-logo">
            <img src="view/assets/img/hb-monogram.svg" alt="HB Fitness Logo" class="brand-logo-img">
            <div><span style="color:#B4B4B4;">HB</span> <span style="color:#E8405E;">FITNESS</span> <span class="brand-subtitle">Train smart. Eat well. Reuse more.</span></div>
        </a>
        <div class="landing-nav-links">
            <a href="#features" class="link">Modules</a>
            <a href="#impact" class="link">Stats</a>
            <a href="#articles" class="link">Articles</a>
            <a href="view/sports/sports.php" class="link">Sports</a>
            <?php if ($currentUser): ?>
                <span class="role-pill <?= $currentUser['role'] === 'admin' ? 'admin' : ($currentUser['role'] === 'coach' ? 'coach' : '') ?>">
                    <i class="fa-solid <?= $currentUser['role'] === 'admin' ? 'fa-shield-halved' : ($currentUser['role'] === 'coach' ? 'fa-medal' : 'fa-user-check') ?>"></i>
                    <?= htmlspecialchars($currentUser['full_name']) ?> (<?= strtoupper(htmlspecialchars($currentUser['role'])) ?>)
                </span>
                <a href="index.php?page=<?= $currentUser['role'] === 'admin' ? 'admin' : ($currentUser['role'] === 'coach' ? 'coach' : 'user') ?>" class="btn btn-primary btn-sm">
                    <i class="fa-solid fa-gauge-high"></i> My Dashboard
                </a>
                <a href="index.php?action=logout" class="btn btn-outline btn-sm" title="Logout"><i class="fa-solid fa-right-from-bracket"></i></a>
            <?php else: ?>
                <a href="view/auth/login.php" class="btn btn-outline btn-sm"><i class="fa-solid fa-right-to-bracket"></i> Log in</a>
                <a href="view/auth/signup.php" class="btn btn-primary btn-sm"><i class="fa-solid fa-user-plus"></i> Sign up</a>
            <?php endif; ?>
        </div>
    </nav>

    <!-- Hero -->
    <header class="landing-hero">
        <div class="reveal">
            <span class="hero-eyebrow"><i class="fa-solid fa-fire-flame-curved"></i> HB FITNESS · Train Smart. Eat Well. Reuse More.</span>
            <h1>Train smart and grow <br><span class="gradient-text typed-word" id="typedWord">stronger</span></h1>
            <p class="lead">
                HB FITNESS connects athletes and coaches: publish training &amp; nutrition articles,
                exchange verified Coach Pick tips in comments, and explore top sports disciplines.
            </p>
            <div class="hero-ctas">
                <?php if ($currentUser): ?>
                    <a href="index.php?page=<?= $currentUser['role'] === 'admin' ? 'admin' : ($currentUser['role'] === 'coach' ? 'coach' : 'user') ?>" class="btn btn-primary btn-lg">
                        <i class="fa-solid fa-rocket"></i> Open my dashboard
                    </a>
                <?php else: ?>
                    <a href="view/auth/signup.php" class="btn btn-primary btn-lg"><i class="fa-solid fa-user-plus"></i> Join for free</a>
                    <a href="view/auth/login.php" class="btn btn-outline btn-lg"><i class="fa-solid fa-right-to-bracket"></i> Log in</a>
                <?php endif; ?>
                <a href="view/sports/sports.php" class="btn btn-green btn-lg"><i class="fa-solid fa-person-running"></i> Explore Sports</a>
            </div>
        </div>

        <div class="hero-visual reveal">
            <div class="pulse-ring r1"></div>
            <div class="pulse-ring r2"></div>
            <div class="pulse-ring r3"></div>
            <div class="hero-center-badge">
                <img src="view/assets/img/hb-fitness-logo.svg" alt="HB Fitness Monogram">
            </div>

            <div class="equalizer-bars">
                <div class="eq-bar" style="animation-delay:.1s;"></div>
                <div class="eq-bar" style="animation-delay:.3s;"></div>
                <div class="eq-bar" style="animation-delay:.05s;"></div>
                <div class="eq-bar" style="animation-delay:.45s;"></div>
                <div class="eq-bar" style="animation-delay:.2s;"></div>
                <div class="eq-bar" style="animation-delay:.5s;"></div>
                <div class="eq-bar" style="animation-delay:.15s;"></div>
                <div class="eq-bar" style="animation-delay:.35s;"></div>
            </div>

            <div class="floating-chip" style="top:28px;left:24px;"><i class="fa-solid fa-newspaper"></i> <?= (int)$stats['articles'] ?> fitness articles</div>
            <div class="floating-chip" style="top:120px;right:24px;animation-delay:1s;"><i class="fa-solid fa-comments"></i> <?= (int)$stats['comments'] ?> athlete replies</div>
            <div class="floating-chip" style="bottom:120px;left:32px;animation-delay:2s;"><i class="fa-solid fa-fire"></i> <?= (int)$stats['total_likes'] ?> upvotes</div>
        </div>
    </header>

    <!-- Modules -->
    <section class="landing-section" id="features">
        <h2 class="section-title reveal">Everything athletes need in one hub</h2>
        <p class="section-sub reveal">Every article, comment, and sport discipline is connected to your personal HB FITNESS account.</p>
        <div class="feature-grid">
            <div class="feature-card reveal">
                <div class="feature-icon"><i class="fa-solid fa-newspaper"></i></div>
                <h3>Fitness Articles</h3>
                <p style="color:var(--slate-700);">Read and publish science-backed guides on strength training, HIIT, nutrition, and recovery.</p>
                <ul>
                    <li><i class="fa-solid fa-check"></i> Categorised by sport & read time</li>
                    <li><i class="fa-solid fa-check"></i> Community upvotes & featured picks</li>
                    <li><i class="fa-solid fa-check"></i> Manage and update your own articles</li>
                </ul>
            </div>
            <div class="feature-card reveal">
                <div class="feature-icon"><i class="fa-solid fa-comments"></i></div>
                <h3>Athlete Comments</h3>
                <p style="color:var(--slate-700);">Ask form questions, share personal records, and get feedback from certified coaches.</p>
                <ul>
                    <li><i class="fa-solid fa-check"></i> Direct replies linked to every article</li>
                    <li><i class="fa-solid fa-check"></i> Highlighted Coach Pick answers</li>
                    <li><i class="fa-solid fa-check"></i> Full ownership & moderation controls</li>
                </ul>
            </div>
            <div class="feature-card reveal">
                <div class="feature-icon"><i class="fa-solid fa-person-running"></i></div>
                <h3>Sports Explorer</h3>
                <p style="color:var(--slate-700);">Discover 8 athletic disciplines with calorie burn, intensity zones, and an interactive BMI calculator.</p>
                <ul>
                    <li><i class="fa-solid fa-check"></i> Filter by Strength, Cardio, Mind & Combat</li>
                    <li><i class="fa-solid fa-check"></i> Live article counts per sport</li>
                    <li><i class="fa-solid fa-check"></i> Built-in BMI & Training Zone tool</li>
                </ul>
            </div>
        </div>
    </section>

    <!-- Impact stats -->
    <section class="landing-section" id="impact" style="padding-top:1rem;">
        <div class="stats-band reveal">
            <div><h3 class="counter-value" data-target="<?= (int)$stats['articles'] ?>">0</h3><span>Fitness Articles</span></div>
            <div><h3 class="counter-value" data-target="<?= (int)$stats['featured'] ?>">0</h3><span>Featured Guides</span></div>
            <div><h3 class="counter-value" data-target="<?= (int)$stats['comments'] ?>">0</h3><span>Community Comments</span></div>
            <div><h3 class="counter-value" data-target="<?= (int)$stats['athletes'] ?>">0</h3><span>Active Athletes</span></div>
            <div><h3 class="counter-value" data-target="<?= (int)$stats['total_likes'] ?>">0</h3><span>Article Upvotes</span></div>
        </div>
    </section>

    <!-- Featured articles -->
    <section class="landing-section" id="articles">
        <h2 class="section-title reveal">Latest fitness & training articles</h2>
        <p class="section-sub reveal">Fresh insights from coaches and athletes across strength, endurance, and recovery.</p>
        <div class="cards-grid">
            <?php foreach ($featuredArticles as $art): ?>
            <div class="entity-card reveal">
                <div>
                    <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:0.55rem;">
                        <span class="badge badge-orange"><?= htmlspecialchars($art['category']) ?></span>
                        <span style="font-size:0.78rem;color:var(--slate-500);font-weight:600;"><i class="fa-regular fa-clock"></i> <?= (int)$art['read_time'] ?> min read</span>
                    </div>
                    <h4 style="color:var(--navy-900);margin:0.4rem 0 0.45rem;font-size:1.08rem;"><?= htmlspecialchars($art['title']) ?></h4>
                    <p style="font-size:0.86rem;color:var(--slate-700);"><?= htmlspecialchars(mb_strimwidth($art['content'], 0, 145, '…')) ?></p>
                </div>
                <div style="display:flex;justify-content:space-between;align-items:center;font-size:0.8rem;color:var(--slate-500);margin-top:1rem;padding-top:0.8rem;border-top:1px solid var(--slate-100);">
                    <span><i class="fa-solid fa-user"></i> <?= htmlspecialchars($art['author_name'] ?? 'Athlete') ?></span>
                    <span>
                        <i class="fa-solid fa-thumbs-up" style="color:var(--orange-600);"></i> <?= (int)$art['likes_count'] ?> ·
                        <i class="fa-solid fa-comment" style="color:var(--emerald-600);"></i> <?= (int)$art['comments_count'] ?>
                    </span>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
        <div style="text-align:center;margin-top:2rem;">
            <a href="<?= $currentUser ? 'view/article/listArticles.php' : 'view/auth/signup.php' ?>" class="btn btn-primary">
                <i class="fa-solid fa-book-open"></i> <?= $currentUser ? 'Browse All Articles' : 'Sign up to Read & Comment' ?>
            </a>
        </div>
    </section>

    <!-- How it works -->
    <section class="landing-section" id="how" style="padding-top:0;">
        <h2 class="section-title reveal">How HB FITNESS works</h2>
        <p class="section-sub reveal">Four simple steps to level up your training routine.</p>
        <div class="steps-grid">
            <div class="step-card reveal"><div class="step-num">1</div><h4>Create your profile</h4><p style="font-size:0.86rem;color:var(--slate-500);">Sign up in seconds to unlock your personal athlete hub.</p></div>
            <div class="step-card reveal"><div class="step-num">2</div><h4>Explore sports</h4><p style="font-size:0.86rem;color:var(--slate-500);">Compare sports, intensity levels, and check your training metrics.</p></div>
            <div class="step-card reveal"><div class="step-num">3</div><h4>Publish articles</h4><p style="font-size:0.86rem;color:var(--slate-500);">Share workout plans, nutrition tips, and race strategies.</p></div>
            <div class="step-card reveal"><div class="step-num">4</div><h4>Discuss & grow</h4><p style="font-size:0.86rem;color:var(--slate-500);">Comment on articles, upvote guides, and learn from coaches.</p></div>
        </div>
    </section>

    <?php if (!$currentUser): ?>
    <section class="landing-section" style="padding-top:0;">
        <div class="cta-band reveal">
            <h2>Ready to elevate your fitness journey?</h2>
            <p style="opacity:0.92;margin-bottom:1.4rem;">Join HB FITNESS — Train smart. Eat well. Reuse more.</p>
            <a href="view/auth/signup.php" class="btn btn-lg" style="background:#030303;color:#fff;border:1px solid rgba(255,255,255,0.25);"><i class="fa-solid fa-user-plus"></i> Create my free account</a>
        </div>
    </section>
    <?php endif; ?>

    <footer class="landing-footer">
        <div class="brand-logo" style="justify-content:center;color:#fff;margin-bottom:0.6rem;">
            <img src="view/assets/img/hb-monogram.svg" alt="HB Fitness" class="brand-logo-img" style="height:38px;">
            <span><span style="color:#B4B4B4;">HB</span> <span style="color:#E8405E;">FITNESS</span></span>
        </div>
        <p style="font-size:0.85rem;">HB FITNESS · Train smart. Eat well. Reuse more. — &copy; <?= date('Y') ?></p>
    </footer>
</div>

<script src="view/assets/js/fitness.js"></script>
<script>
(function () {
    const words = ['stronger', 'faster', 'fitter', 'healthier', 'unstoppable'];
    const el = document.getElementById('typedWord');
    let w = 0, c = words[0].length, deleting = true;
    function tick() {
        const word = words[w];
        el.textContent = word.substring(0, c);
        if (deleting) {
            c--;
            if (c < 0) { deleting = false; w = (w + 1) % words.length; c = 0; }
        } else {
            c++;
            if (c > words[w].length) { deleting = true; setTimeout(tick, 1600); return; }
        }
        setTimeout(tick, deleting ? 60 : 110);
    }
    setTimeout(tick, 1800);

    const io = new IntersectionObserver(entries => {
        entries.forEach(e => {
            if (e.isIntersecting) { e.target.classList.add('visible'); io.unobserve(e.target); }
        });
    }, { threshold: 0.15 });
    document.querySelectorAll('.reveal').forEach((r, i) => {
        r.style.transitionDelay = (i % 4) * 0.08 + 's';
        io.observe(r);
    });

    const nav = document.getElementById('landingNav');
    window.addEventListener('scroll', () => nav.classList.toggle('scrolled', window.scrollY > 10));
})();
</script>
</body>
</html>
