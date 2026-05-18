<?php
session_start();

if (!isset($_SESSION['users']) || !isset($_SESSION['playerSums'])) {
    header('Location: index.php');
    exit;
}

$users = $_SESSION['users'];
$playerSums = $_SESSION['playerSums'];

arsort($playerSums);

$rankedPlayers = [];
foreach ($playerSums as $index => $score) {
    $rankedPlayers[] = [
        'index' => $index,
        'name' => $users[$index][0],
        'score' => $score,
    ];
}

$topScore = max($playerSums);
$winnerNames = [];
foreach ($playerSums as $index => $score) {
    if ($score === $topScore) {
        $winnerNames[] = $users[$index][0] . " ({$score} točk)";
    }
}

$winnerText = implode(', ', $winnerNames);
$podiumPlayers = [
    $rankedPlayers[1] ?? null,
    $rankedPlayers[0] ?? null,
    $rankedPlayers[2] ?? null,
];
?>

<!DOCTYPE html>
<html lang="sl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Končni rezultati</title>
    <link rel="stylesheet" href="css/style.css?v=3">
    <link rel="stylesheet" href="css/rezultati.css?v=3">
    <link rel="icon" type="image/x-icon" href="img/favicon.png">
</head>
<body>

<canvas id="fireworks"></canvas>

<div class="leaderboard">
    <h1>Podium rezultatov</h1>

    <canvas id="podiumCanvas" aria-label="Podium rezultatov"></canvas>

    <div class="winner-text">
        Zmagovalec(i):
        <?= htmlspecialchars($winnerText, ENT_QUOTES, 'UTF-8') ?>
    </div>

    <a href="index.php" class="back-btn">Nova igra</a>
</div>

<script>
    window.podiumPlayers = <?= json_encode($podiumPlayers, JSON_UNESCAPED_UNICODE) ?>;
</script>
<script src="js/script.js"></script>
<script src="js/podium.js?v=3"></script>

</body>
</html>
