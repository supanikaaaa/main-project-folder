<?php
// 1. Start the session to track points securely
session_start();
header('Content-Type: application/json');

// Check if logged in
if (!isset($_SESSION['username'])) {
    echo json_encode(['error' => 'Not logged in']);
    exit();
}

// 2. Get the incoming data from JavaScript
$data = json_decode(file_get_contents('php://input'), true);
$game = $data['game'] ?? '';
$betAmount = (int)($data['betAmount'] ?? 0);

// Basic security check
if ($betAmount <= 0 || $betAmount > $_SESSION['points']) {
    echo json_encode(['error' => 'Invalid bet amount']);
    exit();
}

// Deduct the bet amount immediately
$_SESSION['points'] -= $betAmount;
$_SESSION['total_spent'] += $betAmount;

/* =========================================
   GAME 1: 3D DICE LOGIC
   ========================================= */
if ($game === 'dice') {
    $betType = $data['betType'] ?? '';
    $n1 = rand(1, 6);
    $n2 = rand(1, 6);
    $total = $n1 + $n2;

    $win = false;
    $multiplier = 2;

    switch ($betType) {
        case 'odd': if ($total % 2 !== 0) $win = true; break;
        case 'even': if ($total % 2 === 0) $win = true; break;
        case 'low': if ($total <= 6) $win = true; break;
        case 'high': if ($total >= 8) $win = true; break;
        case 'lucky7': if ($total === 7) { $win = true; $multiplier = 4; } break;
    }

    $reward = 0;
    if ($win) {
        $reward = $betAmount * $multiplier;
        $_SESSION['points'] += $reward;
        $_SESSION['total_won'] += $reward;
    }

    echo json_encode([
        'status' => 'success',
        'n1' => $n1, 'n2' => $n2, 'total' => $total,
        'win' => $win, 'reward' => $reward,
        'newPoints' => $_SESSION['points']
    ]);
    exit();
}

/* =========================================
   GAME 2: COLOR ROULETTE LOGIC
   ========================================= */
elseif ($game === 'color') {
    $betType = $data['betType'] ?? '';
    $rand = mt_rand(1, 1000); 
    
    // PHP Conditionals for Probability
    if ($rand <= 10) { $finalColorStr = 'green'; } 
    elseif ($rand <= 505) { $finalColorStr = 'red'; } 
    else { $finalColorStr = 'black'; }

    $win = ($betType === $finalColorStr);
    $reward = 0;

    if ($win) {
        $multiplier = ($finalColorStr === 'green') ? 4 : 2;
        $reward = $betAmount * $multiplier;
        $_SESSION['points'] += $reward;
        $_SESSION['total_won'] += $reward;
    }

    echo json_encode([
        'status' => 'success',
        'finalColorStr' => $finalColorStr,
        'win' => $win,
        'reward' => $reward,
        'newPoints' => $_SESSION['points']
    ]);
    exit();
}

/* =========================================
   GAME 3: PRIME PREDICTOR LOGIC
   ========================================= */
elseif ($game === 'prime') {
    $primeType = $data['primeType'] ?? '';
    $colorType = $data['colorType'] ?? '';

    $finalNum = rand(1, 30);
    $finalColor = (rand(0, 1) === 0) ? 'red' : 'green';

    // Helper function for logic
    function isPrimePhp($n) {
        if ($n <= 1) return false;
        for ($i = 2; $i <= sqrt($n); $i++) {
            if ($n % $i == 0) return false;
        }
        return true;
    }

    $finalIsPrime = isPrimePhp($finalNum);
    $isPrimeCorrect = ($primeType === 'prime' && $finalIsPrime) || ($primeType === 'not_prime' && !$finalIsPrime);
    $isColorCorrect = ($colorType === $finalColor);

    $win = ($isPrimeCorrect || $isColorCorrect);
    $multiplier = 0;

    if ($isPrimeCorrect && $isColorCorrect) { $multiplier = 2; } 
    elseif ($isPrimeCorrect || $isColorCorrect) { $multiplier = 1.5; }

    $reward = 0;
    if ($win) {
        $reward = floor($betAmount * $multiplier);
        $_SESSION['points'] += $reward;
        $_SESSION['total_won'] += $reward;
    }

    echo json_encode([
        'status' => 'success',
        'finalNum' => $finalNum,
        'finalColor' => $finalColor,
        'finalIsPrime' => $finalIsPrime,
        'isColorCorrect' => $isColorCorrect,
        'win' => $win,
        'reward' => $reward,
        'newPoints' => $_SESSION['points']
    ]);
    exit();
}

// Fallback if no game matched
echo json_encode(['error' => 'Unknown game']);
?>
