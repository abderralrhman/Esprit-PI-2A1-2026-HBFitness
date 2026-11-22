<?php
require_once __DIR__ . '/../../controller/AuthController.php';
require_once __DIR__ . '/../../controller/article/ArticleController.php';

$currentUser = AuthController::currentUser();
$isAdmin     = AuthController::isAdmin();
$isCoach     = AuthController::isCoach();
$articleC    = new ArticleController();
$allArticles = $articleC->listArticles();

// Count articles per category so each sport card shows live DB counts
$catCounts = [];
foreach ($allArticles as $a) {
    $cat = $a['category'] ?? '';
    $catCounts[$cat] = ($catCounts[$cat] ?? 0) + 1;
}

// Sports disciplines catalog matching the HB FITNESS fitness theme
$sportsCatalog = [
    [
        'name'      => 'Strength & Powerlifting',
        'group'     => 'strength',
        'category'  => 'Strength Training',
        'icon'      => 'fa-dumbbell',
        'gradient'  => 'linear-gradient(135deg, #030303, #910329)',
        'intensity' => 'High',
        'kcal'      => '450 kcal/h',
        'freq'      => '3–5x / wk',
        'focus'     => 'Full Body',
        'summary'   => 'Build raw strength, lean muscle mass, and bone density through progressive compound lifts like squats, deadlifts, and presses.',
        'benefits'  => ['Progressive overload mastery', 'Higher resting metabolism', 'Joint stability & posture'],
    ],
    [
        'name'      => 'HIIT & Metabolic Conditioning',
        'group'     => 'cardio',
        'category'  => 'Cardio & HIIT',
        'icon'      => 'fa-bolt',
        'gradient'  => 'linear-gradient(135deg, #910329, #E8405E)',
        'intensity' => 'Extreme',
        'kcal'      => '780 kcal/h',
        'freq'      => '2–3x / wk',
        'focus'     => 'Heart & Core',
        'summary'   => 'Short, explosive bursts of all-out effort paired with active recovery to skyrocket VO2 max and trigger afterburn.',
        'benefits'  => ['Maximum burn in 20 minutes', 'Boosts anaerobic threshold', 'Minimal equipment needed'],
    ],
    [
        'name'      => 'Running & Marathon',
        'group'     => 'cardio',
        'category'  => 'Running & Endurance',
        'icon'      => 'fa-person-running',
        'gradient'  => 'linear-gradient(135deg, #1A1A1D, #910329)',
        'intensity' => 'High',
        'kcal'      => '650 kcal/h',
        'freq'      => '3–5x / wk',
        'focus'     => 'Legs & Lungs',
        'summary'   => 'Develop aerobic stamina, mental grit, and race-day pacing from 5K speedwork to negative-split half-marathons.',
        'benefits'  => ['Cardiovascular longevity', 'Endorphin stress relief', 'Structured pace progression'],
    ],
    [
        'name'      => 'Yoga & Athletic Mobility',
        'group'     => 'mind',
        'category'  => 'Yoga & Mobility',
        'icon'      => 'fa-spa',
        'gradient'  => 'linear-gradient(135deg, #030303, #2E2E34)',
        'intensity' => 'Low',
        'kcal'      => '260 kcal/h',
        'freq'      => '4–7x / wk',
        'focus'     => 'Joints & Core',
        'summary'   => 'Restore range of motion, decompress the spine, and accelerate active recovery between heavy training days.',
        'benefits'  => ['Injury prevention', 'Deep diaphragmatic breathing', 'Balance & hip mobility'],
    ],
    [
        'name'      => 'Boxing & Combat Fitness',
        'group'     => 'combat',
        'category'  => 'Combat Sports',
        'icon'      => 'fa-hand-fist',
        'gradient'  => 'linear-gradient(135deg, #0D0D10, #910329)',
        'intensity' => 'Extreme',
        'kcal'      => '820 kcal/h',
        'freq'      => '2–4x / wk',
        'focus'     => 'Upper & Agility',
        'summary'   => 'Combine heavy-bag combinations, footwork drills, and rotational core power for explosive conditioning.',
        'benefits'  => ['Hand-eye coordination', 'Explosive rotational power', 'Unmatched stress release'],
    ],
    [
        'name'      => 'Swimming & Triathlon',
        'group'     => 'cardio',
        'category'  => 'Running & Endurance',
        'icon'      => 'fa-person-swimming',
        'gradient'  => 'linear-gradient(135deg, #1A1A1D, #E8405E)',
        'intensity' => 'Moderate',
        'kcal'      => '600 kcal/h',
        'freq'      => '2–4x / wk',
        'focus'     => 'Full Body',
        'summary'   => 'Zero-impact aquatic conditioning that strengthens the back, shoulders, and lungs while protecting joints.',
        'benefits'  => ['Zero joint impact', 'Superior lung capacity', 'Balanced upper/lower tone'],
    ],
    [
        'name'      => 'Cycling & Spin Endurance',
        'group'     => 'cardio',
        'category'  => 'Cardio & HIIT',
        'icon'      => 'fa-person-biking',
        'gradient'  => 'linear-gradient(135deg, #030303, #6A021D)',
        'intensity' => 'High',
        'kcal'      => '620 kcal/h',
        'freq'      => '3–4x / wk',
        'focus'     => 'Quads & Glutes',
        'summary'   => 'High-cadence climbs and threshold intervals indoors or on open roads to sculpt lower-body endurance.',
        'benefits'  => ['Knee-friendly cardio', 'Power-to-weight gains', 'Zone-2 aerobic base'],
    ],
    [
        'name'      => 'Sports Nutrition & Recovery',
        'group'     => 'mind',
        'category'  => 'Nutrition & Recovery',
        'icon'      => 'fa-apple-whole',
        'gradient'  => 'linear-gradient(135deg, #5A0219, #E8405E)',
        'intensity' => 'Moderate',
        'kcal'      => 'Fuel Plan',
        'freq'      => 'Daily',
        'focus'     => 'Metabolism',
        'summary'   => 'Optimize macronutrient timing, hydration, electrolytes, and sleep architecture to turn workouts into results.',
        'benefits'  => ['Faster muscle repair', 'Sustained energy output', 'Science-backed habits'],
    ],
];
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sports & Disciplines Explorer — HB FITNESS</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <link rel="stylesheet" href="../assets/css/fitness.css">
</head>
<body>
<div class="app-wrapper">
    <?php
    $basePrefix = '../../';
    $activeNav  = 'sports';
    include __DIR__ . '/../partials/navbar.php';
    ?>

    <div class="container">
        <!-- Hero Banner -->
        <section class="crud-page-header">
            <div>
                <div class="breadcrumb-trail">
                    <a href="../../index.php?page=<?= $currentUser ? ($isAdmin ? 'admin' : ($isCoach ? 'coach' : 'user')) : 'landing' ?>">
                        <?= $currentUser ? ($isAdmin ? 'Admin Dashboard' : ($isCoach ? 'Coach Studio' : 'Athlete Hub')) : 'Home' ?>
                    </a>
                    <span>/</span> <span>Sports Explorer</span>
                </div>
                <h1><i class="fa-solid fa-trophy"></i> Sports & Training Disciplines</h1>
                <p>Explore athletic sports, compare calorie burn and intensity levels, and jump straight into related community articles.</p>
            </div>
            <div style="display:flex;gap:0.65rem;flex-wrap:wrap;z-index:1;">
                <?php if ($currentUser): ?>
                    <a href="../article/createArticle.php" class="btn btn-green"><i class="fa-solid fa-pen-nib"></i> Write a Sport Article</a>
                    <a href="../article/listArticles.php" class="btn btn-outline" ><i class="fa-solid fa-newspaper"></i> All Articles</a>
                <?php else: ?>
                    <a href="../auth/signup.php" class="btn btn-green"><i class="fa-solid fa-user-plus"></i> Join to Publish</a>
                <?php endif; ?>
            </div>
        </section>

        <!-- Filter Pills + Live Search -->
        <div class="crud-subnav" style="justify-content:space-between;">
            <div style="display:flex;gap:0.45rem;flex-wrap:wrap;" id="sportFilterBar">
                <button type="button" class="subnav-pill active" data-group="all"><i class="fa-solid fa-border-all"></i> All Sports (<?= count($sportsCatalog) ?>)</button>
                <button type="button" class="subnav-pill" data-group="strength"><i class="fa-solid fa-dumbbell"></i> Strength</button>
                <button type="button" class="subnav-pill" data-group="cardio"><i class="fa-solid fa-heart-pulse"></i> Cardio & Endurance</button>
                <button type="button" class="subnav-pill" data-group="combat"><i class="fa-solid fa-hand-fist"></i> Combat</button>
                <button type="button" class="subnav-pill" data-group="mind"><i class="fa-solid fa-spa"></i> Mind & Fuel</button>
            </div>
            <div class="search-input-wrap">
                <i class="fa-solid fa-magnifying-glass"></i>
                <input type="text" class="form-control" id="sportSearch" placeholder="Search a sport or benefit...">
            </div>
        </div>

        <!-- Sports Cards Grid -->
        <div class="cards-grid" id="sportsGrid" style="margin-bottom:2rem;">
            <?php foreach ($sportsCatalog as $sp):
                $artCount = $catCounts[$sp['category']] ?? 0;
            ?>
            <div class="sport-card" data-group="<?= htmlspecialchars($sp['group']) ?>">
                <div class="sport-card-banner" style="background:<?= $sp['gradient'] ?>;">
                    <div>
                        <span class="badge" style="background:rgba(255,255,255,0.22);color:#fff;margin-bottom:0.55rem;">
                            <?= htmlspecialchars($sp['category']) ?>
                        </span>
                        <h3 style="font-size:1.2rem;font-weight:800;line-height:1.25;"><?= htmlspecialchars($sp['name']) ?></h3>
                    </div>
                    <div class="sport-icon-circle">
                        <i class="fa-solid <?= htmlspecialchars($sp['icon']) ?>"></i>
                    </div>
                </div>

                <div class="sport-card-body">
                    <div>
                        <p style="font-size:0.87rem;color:var(--slate-700);"><?= htmlspecialchars($sp['summary']) ?></p>

                        <div class="sport-metrics">
                            <div>
                                <strong><?= htmlspecialchars($sp['kcal']) ?></strong>
                                <span>Est. Burn</span>
                            </div>
                            <div>
                                <strong><?= htmlspecialchars($sp['freq']) ?></strong>
                                <span>Frequency</span>
                            </div>
                            <div>
                                <strong><?= htmlspecialchars($sp['focus']) ?></strong>
                                <span>Focus</span>
                            </div>
                        </div>

                        <ul style="list-style:none;margin-bottom:1rem;">
                            <?php foreach ($sp['benefits'] as $b): ?>
                            <li style="font-size:0.83rem;color:var(--slate-700);padding:0.2rem 0;">
                                <i class="fa-solid fa-circle-check" style="color:var(--emerald-600);margin-right:0.4rem;"></i>
                                <?= htmlspecialchars($b) ?>
                            </li>
                            <?php endforeach; ?>
                        </ul>
                    </div>

                    <div style="display:flex;justify-content:space-between;align-items:center;padding-top:0.85rem;border-top:1px solid var(--slate-100);gap:0.5rem;flex-wrap:wrap;">
                        <span class="badge badge-<?= htmlspecialchars($sp['intensity']) ?>">
                            <i class="fa-solid fa-gauge-high"></i> <?= htmlspecialchars($sp['intensity']) ?> Intensity
                        </span>
                        <div style="display:flex;gap:0.4rem;align-items:center;">
                            <?php if ($currentUser): ?>
                                <a href="../article/listArticles.php?category=<?= urlencode($sp['category']) ?>" class="btn btn-outline btn-sm">
                                    <i class="fa-solid fa-newspaper"></i> <?= $artCount ?> Article<?= $artCount === 1 ? '' : 's' ?>
                                </a>
                            <?php else: ?>
                                <a href="../auth/login.php" class="btn btn-outline btn-sm">
                                    <i class="fa-solid fa-newspaper"></i> <?= $artCount ?> Article<?= $artCount === 1 ? '' : 's' ?>
                                </a>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>
            <?php endforeach; ?>
        </div>

        <!-- Interactive Athlete BMI & Target Heart Rate Calculator -->
        <div class="full-card">
            <div class="card-header">
                <div class="card-title"><i class="fa-solid fa-heart-pulse"></i> Quick Athlete Calculator — BMI & Target Heart-Rate Zone</div>
                <span class="badge badge-green"><i class="fa-solid fa-bolt"></i> Interactive Tool</span>
            </div>
            <div class="form-grid" style="align-items:end;">
                <div class="form-group" style="margin-bottom:0;">
                    <label for="calcWeight">Weight (kg)</label>
                    <input type="number" id="calcWeight" class="form-control" value="72" min="30" max="250">
                </div>
                <div class="form-group" style="margin-bottom:0;">
                    <label for="calcHeight">Height (cm)</label>
                    <input type="number" id="calcHeight" class="form-control" value="176" min="120" max="230">
                </div>
                <div class="form-group" style="margin-bottom:0;">
                    <label for="calcAge">Age (years)</label>
                    <input type="number" id="calcAge" class="form-control" value="25" min="12" max="90">
                </div>
                <div>
                    <button type="button" id="btnCalcFit" class="btn btn-primary" style="width:100%;justify-content:center;padding:0.72rem;">
                        <i class="fa-solid fa-calculator"></i> Calculate Metrics
                    </button>
                </div>
            </div>
            <div id="calcOutput" class="bmi-result-pill" style="margin-top:1.1rem;display:flex;justify-content:space-between;flex-wrap:wrap;gap:1rem;">
                <span><i class="fa-solid fa-scale-balanced" style="color:var(--orange-600);"></i> BMI: <strong id="outBmi">23.2</strong> (<span id="outBmiCat">Healthy Athletic Range</span>)</span>
                <span><i class="fa-solid fa-heart-pulse" style="color:var(--emerald-600);"></i> Fat-Burn Zone: <strong id="outZone2">117–137 bpm</strong></span>
                <span><i class="fa-solid fa-fire" style="color:var(--orange-600);"></i> HIIT Peak Zone: <strong id="outZone4">156–185 bpm</strong></span>
            </div>
        </div>
    </div>
</div>

<script src="../assets/js/fitness.js"></script>
<script>
(function () {
    // Filter sports by discipline group + search query
    let activeGroup = 'all';
    const filterBtns = document.querySelectorAll('#sportFilterBar .subnav-pill');
    const searchInput = document.getElementById('sportSearch');
    const cards = document.querySelectorAll('#sportsGrid .sport-card');

    function applyFilters() {
        const q = searchInput.value.toLowerCase().trim();
        cards.forEach(card => {
            const matchesGroup = activeGroup === 'all' || card.dataset.group === activeGroup;
            const matchesQuery = q === '' || card.textContent.toLowerCase().includes(q);
            card.style.display = (matchesGroup && matchesQuery) ? '' : 'none';
        });
    }

    filterBtns.forEach(btn => {
        btn.addEventListener('click', () => {
            filterBtns.forEach(b => b.classList.remove('active'));
            btn.classList.add('active');
            activeGroup = btn.dataset.group;
            applyFilters();
        });
    });
    searchInput.addEventListener('input', applyFilters);

    // Interactive BMI & Heart-Rate Zone calculator
    function updateCalc() {
        const w = parseFloat(document.getElementById('calcWeight').value) || 0;
        const hCm = parseFloat(document.getElementById('calcHeight').value) || 0;
        const age = parseFloat(document.getElementById('calcAge').value) || 25;
        if (w <= 0 || hCm <= 0) return;

        const hM = hCm / 100;
        const bmi = w / (hM * hM);
        let cat = 'Healthy Athletic Range';
        if (bmi < 18.5) cat = 'Underweight — Focus on Lean Strength';
        else if (bmi >= 25 && bmi < 30) cat = 'Overweight — Mix Strength & Cardio';
        else if (bmi >= 30) cat = 'High BMI — Start Low-Impact Cardio';

        const maxHr = Math.max(120, 220 - age);
        const z2Lo = Math.round(maxHr * 0.60), z2Hi = Math.round(maxHr * 0.70);
        const z4Lo = Math.round(maxHr * 0.80), z4Hi = Math.round(maxHr * 0.95);

        document.getElementById('outBmi').textContent = bmi.toFixed(1);
        document.getElementById('outBmiCat').textContent = cat;
        document.getElementById('outZone2').textContent = `${z2Lo}–${z2Hi} bpm`;
        document.getElementById('outZone4').textContent = `${z4Lo}–${z4Hi} bpm`;
    }

    document.getElementById('btnCalcFit').addEventListener('click', updateCalc);
    updateCalc();
})();
</script>
</body>
</html>
