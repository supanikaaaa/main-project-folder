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

/* =========================================
   NEW: TOP-UP / REGEN SYNC LOGIC
   ========================================= */
if ($game === 'topup') {
    $amount = (int)($data['amount'] ?? 0);
    if ($amount > 0) {
        $_SESSION['points'] += $amount;
        echo json_encode(['status' => 'success', 'newPoints' => $_SESSION['points']]);
    } else {
        echo json_encode(['error' => 'Invalid top-up amount']);
    }
    exit(); // Stop here so it doesn't run the bet checks below
}

/* =========================================
   NORMAL GAME BET CHECKS
   ========================================= */
$betAmount = (int)($data['betAmount'] ?? 0);

// Basic security check for bets
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
   GAME 3: PRIME PREDICTOR LOGIC (50/50 ODDS)
   ========================================= */
elseif ($game === 'prime') {
    $primeType = $data['primeType'] ?? '';

    // The 50/50 Requirement: First, decide if the result will be prime or not
    $isItGoingToBePrime = (rand(0, 1) === 0);

    if ($isItGoingToBePrime) {
        // Pick randomly from the 10 Prime numbers between 1-30
        $primes = [2, 3, 5, 7, 11, 13, 17, 19, 23, 29];
        $finalNum = $primes[array_rand($primes)];
        $finalIsPrime = true;
    } else {
        // Pick randomly from the 20 Non-Prime numbers between 1-30
        $nonPrimes = [1, 4, 6, 8, 9, 10, 12, 14, 15, 16, 18, 20, 21, 22, 24, 25, 26, 27, 28, 30];
        $finalNum = $nonPrimes[array_rand($nonPrimes)];
        $finalIsPrime = false;
    }

    $win = ($primeType === 'prime' && $finalIsPrime) || ($primeType === 'not_prime' && !$finalIsPrime);

    $reward = 0;
    if ($win) {
        $multiplier = 2; // Standard 2x payout for a 50/50 bet
        $reward = floor($betAmount * $multiplier);
        $_SESSION['points'] += $reward;
        $_SESSION['total_won'] += $reward;
    }

    echo json_encode([
        'status' => 'success',
        'finalNum' => $finalNum,
        'finalIsPrime' => $finalIsPrime,
        'win' => $win,
        'reward' => $reward,
        'newPoints' => $_SESSION['points']
    ]);
    exit();
}

// Fallback if no game matched
echo json_encode(['error' => 'Unknown game']);
?>
