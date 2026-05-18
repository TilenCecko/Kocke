<?php
session_start();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: index.php');
    exit;
}

$diceCount = (int)($_POST['steviloKock'] ?? 3);
$roundCount = (int)($_POST['steviloKrogov'] ?? 1);
$diceCount = max(1, min(3, $diceCount));
$roundCount = max(1, min(9, $roundCount));

$users = [];
$diceResults = [];
$playerDiceResults = [];
$playerRoundResults = [];
$playerSums = [];
$winners = [];

for ($i = 1; $i <= 3; $i++) {
    $ime = htmlspecialchars(trim($_POST["ime{$i}"] ?? ''), ENT_QUOTES, 'UTF-8');

    $users[$i] = [$ime];
    $playerDiceResults[$i] = [];
    $playerRoundResults[$i] = [];
    $playerSums[$i] = 0;
}

for ($round = 1; $round <= $roundCount; $round++) {
    for ($player = 1; $player <= 3; $player++) {
        $roundDice = [];
        $roundSum = 0;

        for ($dice = 1; $dice <= $diceCount; $dice++) {
            $value = rand(1, 6);
            $diceResults[] = $value;
            $roundDice[] = $value;
            $playerDiceResults[$player][] = $value;
            $roundSum += $value;
        }

        $playerSums[$player] += $roundSum;
        $playerRoundResults[$player][$round] = [
            'dice' => $roundDice,
            'roundSum' => $roundSum,
            'total' => $playerSums[$player],
        ];
    }
}

$maxSum = max($playerSums);
foreach ($playerSums as $playerIndex => $sum) {
    if ($sum === $maxSum) {
        $winners[] = $playerIndex;
    }
}

$_SESSION['users'] = $users;
$_SESSION['diceResults'] = $diceResults;
$_SESSION['playerDiceResults'] = $playerDiceResults;
$_SESSION['playerRoundResults'] = $playerRoundResults;
$_SESSION['playerSums'] = $playerSums;
$_SESSION['diceCount'] = $diceCount;
$_SESSION['roundCount'] = $roundCount;
$_SESSION['winners'] = $winners;
$_SESSION['generatedAt'] = time();
?>

<!DOCTYPE html>
<html lang="sl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Kocke - Igra</title>
    <link rel="icon" type="image/x-icon" href="img/favicon.png">
    <link rel="stylesheet" href="css/style.css?v=6">
</head>
<body>
    <div class="result-wrapper">
        <div id="animation-panel" class="animation-panel">
            <h3 class="animation-heading">Mešam kocke...</h3>
            <p class="game-settings-note">
                <span id="round-label">Krog 1 od <?= $roundCount ?></span> |
                <?= $diceCount ?> kock<?= $diceCount === 1 ? 'a' : 'e' ?> na igralca
            </p>
            <div class="animation-group">
                <?php foreach ($users as $index => $user): ?>
                    <div class="animation-player-box">
                        <strong><?= $user[0] ?></strong>
                        <div class="animation-row">
                            <?php for ($dice = 1; $dice <= $diceCount; $dice++): ?>
                                <img src="img/dice-anim.gif?v=2" alt="Animacija kock" class="animation-dice">
                            <?php endfor; ?>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>

        <?php for ($round = 1; $round <= $roundCount; $round++): ?>
            <div class="round-panel" data-round="<?= $round ?>">
                <div class="result-panel">
                    <h3>Krog <?= $round ?> / <?= $roundCount ?></h3>
                    <p class="game-settings-note">Vsota se sproti sešteva po vsakem krogu.</p>

                    <?php foreach ($users as $index => $user): ?>
                        <div class="result-card">
                            <div class="result-info">
                                <h3><?= $user[0] ?></h3>
                                <p><strong>Ta krog:</strong> <?= $playerRoundResults[$index][$round]['roundSum'] ?> točk</p>
                                <p><strong>Skupaj:</strong> <?= $playerRoundResults[$index][$round]['total'] ?> točk</p>
                            </div>
                            <div class="result-dice">
                                <?php foreach ($playerRoundResults[$index][$round]['dice'] as $diceValue): ?>
                                    <img src="img/dice<?= $diceValue ?>.png" alt="Kocka <?= $diceValue ?>" class="dice-img">
                                <?php endforeach; ?>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
        <?php endfor; ?>

        <div id="redirect-panel" class="round-panel">
            <div class="result-panel">
                <h3>Vsi krogi so končani</h3>
                <p class="result-note">Preusmerjam na lestvico rezultatov...</p>
            </div>
        </div>
    </div>

    <script>
        window.addEventListener('DOMContentLoaded', () => {
            const animationPanel = document.getElementById('animation-panel');
            const roundLabel = document.getElementById('round-label');
            const roundPanels = Array.from(document.querySelectorAll('.round-panel[data-round]'));
            const redirectPanel = document.getElementById('redirect-panel');
            let currentRound = 0;

            function hideAllRounds() {
                roundPanels.forEach((panel) => panel.classList.remove('visible'));
                redirectPanel.classList.remove('visible');
            }

            function showRound() {
                hideAllRounds();
                animationPanel.style.display = 'block';
                roundLabel.textContent = `Krog ${currentRound + 1} od ${roundPanels.length}`;

                setTimeout(() => {
                    animationPanel.style.display = 'none';
                    roundPanels[currentRound].classList.add('visible');

                    setTimeout(() => {
                        currentRound += 1;

                        if (currentRound < roundPanels.length) {
                            showRound();
                            return;
                        }

                        hideAllRounds();
                        redirectPanel.classList.add('visible');
                        setTimeout(() => {
                            window.location.href = 'rezultati.php';
                        }, 1400);
                    }, 2600);
                }, 1300);
            }

            showRound();
        });
    </script>
    <noscript>
        <div class="result-note">JavaScript ni na voljo; ročno obišči <a href="rezultati.php">rezultati.php</a>.</div>
    </noscript>
</body>
</html>
