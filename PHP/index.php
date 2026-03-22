<?php
// 1. START THE PHP SESSION
session_start();

// 2. HANDLE THE FORM SUBMISSION
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'login') {
    // Save user data to the PHP Session
    $_SESSION['username'] = htmlspecialchars($_POST['username']);
    $_SESSION['player_num'] = htmlspecialchars($_POST['player_num']);
}

// 3. HANDLE LOGOUT
if (isset($_GET['logout'])) {
    session_destroy();
    header("Location: index.php");
    exit();
}

// 4. Check if user is logged in
$isLoggedIn = isset($_SESSION['username']);

// THE FIX: If they are logged in, make sure these session variables exist so we don't get the "Undefined array key" warning!
if ($isLoggedIn) {
    if (!isset($_SESSION['points'])) $_SESSION['points'] = 100;
    if (!isset($_SESSION['total_won'])) $_SESSION['total_won'] = 0;
    if (!isset($_SESSION['total_spent'])) $_SESSION['total_spent'] = 0;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Bet Life Games</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>

    <div id="global-hud" class="regen-widget" style="display: <?php echo $isLoggedIn ? 'flex' : 'none'; ?>;">
        <div class="regen-bar">
            <span class="regen-icon">🪙</span>
            <span id="hud-chip-text"><?php echo $isLoggedIn ? $_SESSION['points'] : '100'; ?>/100</span>
            <button class="regen-plus" onclick="alert('Shop feature coming soon!')">+</button>
        </div>
        <div id="regen-timer" class="regen-timer">+ 10 IN 05:00</div>
    </div>

    <?php if (!$isLoggedIn): ?>
    
    <div id="login-screen" class="screen-container">
        <h1 class="pixel-text yellow-text">BET LIFE GAMES</h1>
        
        <form class="login-panel" method="POST" action="index.php">
            <input type="hidden" name="action" value="login">
            <p class="log-in-title">LOG IN</p>
            <div class="profile-upload" onclick="document.getElementById('file-input').click()">
                <img id="login-avatar" src="user.png" alt="Profile">
                <input type="file" id="file-input" hidden accept="image/*" onchange="previewImage(this)">
            </div>
            <p class="label-text">PROFILE</p>
            <div class="input-group">
                <label>PLAYER NUMBER:</label>
                <input type="text" name="player_num" id="player-num" placeholder="0001" required>
            </div>
            <div class="input-group">
                <label>USERNAME:</label>
                <input type="text" name="username" id="username" placeholder="Enter Name" required>
            </div>
            <button type="submit" class="login-btn">LOG IN</button>
        </form>
    </div>

    <?php else: ?>

    <div id="main-menu" class="screen-container">
        <header class="menu-header">
            <div>
                <h1 class="pixel-text yellow-text" style="margin-top:0;">BET LIFE GAMES</h1>
                <p class="subtitle">PREDICT THE PATTERN AND WIN BIG</p>
            </div>
            <div class="header-profile" onclick="toggleProfile()">
                <img id="header-avatar" src="user.png" alt="User">
                <span id="header-name"><?php echo $_SESSION['username']; ?></span>
            </div>
        </header>

        <section class="menu-content">
            <h2 class="pixel-text yellow-text">Game Modes</h2>
            <div class="card-container">
                <div class="game-card" onclick="showScreen('color-game')">
                    <div class="card-overlay">Color Roulette</div>
                </div>
                <div class="game-card dice-bg" onclick="showScreen('dice-game')">
                    <div class="card-overlay">3D Dice</div>
                </div>
                <div class="game-card" onclick="showScreen('prime-game')">
                    <div class="card-overlay">Prime Predictor</div>
                </div>
            </div>
        </section>
    </div>

    <div id="dice-game" class="screen-container" style="display: none; position: relative;">
        <div class="game-nav">
            <button class="back-btn" onclick="showScreen('main-menu')">← BACK TO MENU</button>
        </div>
        <div class="dice-game-layout">
            <h1 class="pixel-text yellow-text">3D DICE PATTERNS</h1>
            <div class="bet-controls">
                <label>Bet Amount: <input type="number" id="dice-bet-amount" min="10" step="10" value="10" style="width:100px;"></label>
                <label>Pattern:
                    <select id="dice-bet-type">
                        <option value="odd">Odd (2x)</option>
                        <option value="even">Even (2x)</option>
                        <option value="low">Low 2-6 (2x)</option>
                        <option value="high">High 8-12 (2x)</option>
                        <option value="lucky7">Lucky 7 (4x)</option>
                    </select>
                </label>
            </div>

            <div class="dice-wrapper">
                <div class="die" id="die-1">
                    <div class="face front"><div class="dot"></div></div>
                    <div class="face right"><div class="dot"></div><div class="dot"></div></div>
                    <div class="face top"><div class="dot"></div><div class="dot"></div><div class="dot"></div></div>
                    <div class="face bottom"><div class="dot"></div><div class="dot"></div><div class="dot"></div></div>
                    <div class="face left"><div class="dot"></div><div class="dot"></div><div class="dot"></div><div class="dot"></div><div class="dot"></div></div>
                    <div class="face back"><div class="dot"></div><div class="dot"></div><div class="dot"></div><div class="dot"></div><div class="dot"></div><div class="dot"></div></div>
                </div>
                <div class="die" id="die-2">
                    <div class="face front"><div class="dot"></div></div>
                    <div class="face right"><div class="dot"></div><div class="dot"></div></div>
                    <div class="face top"><div class="dot"></div><div class="dot"></div><div class="dot"></div></div>
                    <div class="face bottom"><div class="dot"></div><div class="dot"></div><div class="dot"></div><div class="dot"></div></div>
                    <div class="face left"><div class="dot"></div><div class="dot"></div><div class="dot"></div><div class="dot"></div><div class="dot"></div></div>
                    <div class="face back"><div class="dot"></div><div class="dot"></div><div class="dot"></div><div class="dot"></div><div class="dot"></div><div class="dot"></div></div>
                </div>
            </div>

            <div class="trend-box">
                <h3 style="margin: 0 0 10px 0; color: #f1c40f; font-size: 1rem; text-align: center;">📊 DICE TRENDS</h3>
                <div class="trend-stats">
                    <div class="trend-col"><span>ODD: <span id="trend-odd" class="white-text">0%</span></span><span>EVEN: <span id="trend-even" class="white-text">0%</span></span></div>
                    <div class="trend-col"><span>LOW: <span id="trend-low" class="white-text">0%</span></span><span>HIGH: <span id="trend-high" class="white-text">0%</span></span></div>
                </div>
                <div id="trend-pred-dice" class="trend-pred">Waiting for rolls...</div>
            </div>

            <button id="roll-btn" class="login-btn" style="float:none; margin: 0 auto; padding: 15px 50px;">CONFIRM & ROLL</button>
            <div id="dice-result" class="game-result-text">Pick your pattern and bet!</div>
        </div>
    </div>

    <div id="color-game" class="screen-container" style="display: none; position: relative;">
        <div class="game-nav">
            <button class="back-btn" onclick="showScreen('main-menu')">← BACK TO MENU</button>
        </div>
        <div class="dice-game-layout">
            <h1 class="pixel-text yellow-text">COLOR ROULETTE</h1>
            <div class="bet-controls">
                <label>Bet Amount: <input type="number" id="color-bet-amount" min="10" step="10" value="10" style="width:100px;"></label>
                <label>Select Color:
                    <select id="color-bet-type">
                        <option value="red">🔴 Red (2x)</option>
                        <option value="black">⚫ Black (2x)</option>
                        <option value="green">🟢 Green (4x)</option>
                    </select>
                </label>
            </div>

            <div class="roulette-container">
                <div class="selector-line"></div>
                <div class="roulette-track" id="color-track"></div>
            </div>

            <div class="trend-box">
                <h3 style="margin: 0 0 10px 0; color: #e74c3c; font-size: 1rem; text-align: center;">📊 COLOR TRENDS</h3>
                <div class="trend-stats">
                    <div class="trend-col" style="text-align:center; width: 100%; flex-direction: row; justify-content: center;">
                        <span style="margin-right:15px;">RED: <span id="trend-red" class="white-text">0%</span></span>
                        <span style="margin-right:15px;">BLACK: <span id="trend-black" class="white-text">0%</span></span>
                        <span>GREEN: <span id="trend-green" class="white-text">0%</span></span>
                    </div>
                </div>
                <div id="trend-pred-color" class="trend-pred">Waiting for draws...</div>
            </div>

            <button id="color-btn" class="login-btn" style="float:none; margin: 0 auto; padding: 15px 50px;">SPIN ROULETTE</button>
            <div id="color-result" class="game-result-text">Where will it land?</div>
        </div>
    </div>

    <div id="prime-game" class="screen-container" style="display: none; position: relative;">
        <div class="game-nav">
            <button class="back-btn" onclick="showScreen('main-menu')">← BACK TO MENU</button>
        </div>
        <div class="dice-game-layout">
            <h1 class="pixel-text yellow-text">PRIME PREDICTOR (1-30)</h1>
            <div class="bet-controls" style="margin-bottom: 10px;">
                <label>Bet: <input type="number" id="prime-bet-amount" min="10" step="10" value="10" style="width:80px;"></label>
                <label>Prediction:
                    <select id="prime-bet-type">
                        <option value="prime">Prime</option>
                        <option value="not_prime">Not Prime</option>
                    </select>
                </label>
                <label>Color:
                    <select id="prime-color-type">
                        <option value="red">🔴 Red</option>
                        <option value="green">🟢 Green</option>
                    </select>
                </label>
            </div>
            <p style="font-size: 0.85rem; color: #f1c40f; margin-top: 0;">(Match 1 = 1.5x Payout | Match Both = 2x Payout)</p>

            <div class="slots" style="margin: 20px 0;">
                <div class="slot" id="prime-spinner" style="color: #333;">--</div>
            </div>

            <div class="trend-box">
                <h3 style="margin: 0 0 10px 0; color: #3498db; font-size: 1rem; text-align: center;">📊 PRIME TRENDS</h3>
                <div class="trend-stats" style="flex-direction: column; gap: 8px;">
                    <div class="trend-col" style="text-align:center; width: 100%;">
                        <span>PRIME: <span id="trend-prime" class="white-text" style="margin-right:20px;">0%</span> NOT PRIME: <span id="trend-notprime" class="white-text">0%</span></span>
                    </div>
                    <div class="trend-col" style="text-align:center; width: 100%;">
                        <span style="color:#e74c3c;">RED: <span id="trend-prime-red" class="white-text" style="margin-right:20px;">0%</span></span> 
                        <span style="color:#2ecc71;">GREEN: <span id="trend-prime-green" class="white-text">0%</span></span>
                    </div>
                </div>
                <div id="trend-pred-prime" class="trend-pred">Waiting for spins...</div>
            </div>

            <button id="prime-btn" class="login-btn" style="float:none; margin: 0 auto; padding: 15px 50px;">SPIN NUMBER</button>
            <div id="prime-result" class="game-result-text">Guess Number & Color!</div>
        </div>
    </div>

    <div id="profile-modal" class="modal-overlay" style="display: none;" onclick="toggleProfile()">
        <div class="chrome-modal" onclick="event.stopPropagation()">
            <div class="chrome-profile-header">
                <img id="modal-avatar" src="user.png" alt="Avatar">
                <h3 id="modal-name"><?php echo $_SESSION['username']; ?></h3>
                <p id="modal-achievement-display" style="color: #f1c40f; font-weight: bold; margin: 5px 0 15px 0; font-size: 14px;">Achievement: Newbie</p>
                <button class="chrome-blue-btn" onclick="toggleProfile()">Close Profile</button>
            </div>
            <hr class="chrome-divider">
            <div class="chrome-menu-section">
                <div class="chrome-menu-item"><span class="icon">💰</span><span>Current Chips: <span id="modal-chips"><?php echo $_SESSION['points']; ?></span></span></div>
                <div class="chrome-menu-item"><span class="icon">🏆</span><span>Total Won: <span id="modal-total-won"><?php echo $_SESSION['total_won']; ?></span></span></div>
                <div class="chrome-menu-item"><span class="icon">📈</span><span>Total Spent: <span id="modal-spent"><?php echo $_SESSION['total_spent']; ?></span></span></div>
                <div class="chrome-menu-item"><span class="icon">⭐</span><span>Next Title At: <span id="modal-next-ach">1000</span></span></div>
            </div>
            <hr class="chrome-divider">
            <div class="chrome-menu-section">
                <div class="chrome-menu-item" onclick="window.location.href='index.php?logout=true'"><span class="icon">🚪</span><span>Log out</span></div>
            </div>
        </div>
    </div>

    <div id="game-over-modal" class="modal-overlay" style="display: none;">
        <div class="game-over-box">
            <h2 class="go-title">OUT OF CHIPS!</h2>
            <div class="stats-container">
                <p>Matches Played<br><span id="go-matches-played" class="go-stat">0</span></p>
                <p>Highest Chips<br><span id="go-highest-chips" class="go-stat">100</span></p>
                <p>Final Rank<br><span id="go-final-rank" class="go-stat">Unranked</span></p>
            </div>
            <div class="modal-btn-group">
                <button class="ok-btn" onclick="resetGame()">PLAY AGAIN</button>
                <button class="ok-btn return-btn" onclick="returnToMenuFromGameOver()">RETURN MENU</button>
            </div>
        </div>
    </div>

    <?php endif; ?>

    <script>
        let isUserLoggedIn = <?php echo $isLoggedIn ? 'true' : 'false'; ?>;
        
        <?php if ($isLoggedIn): ?>
            window.serverPoints = <?php echo $_SESSION['points']; ?>;
            window.serverTotalWon = <?php echo $_SESSION['total_won']; ?>;
            window.serverTotalSpent = <?php echo $_SESSION['total_spent']; ?>;
            window.serverName = "<?php echo $_SESSION['username']; ?>";
        <?php endif; ?>
    </script>
    <script src="Script.js"></script>
</body>
</html>
