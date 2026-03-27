<?php
// 1. START THE PHP SESSION
session_start();

// 2. HANDLE THE FORM SUBMISSION
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'login') {
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

// 5. Ensure session keys exist
if ($isLoggedIn) {
    if (!isset($_SESSION['points'])) $_SESSION['points'] = 20;
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
    <link rel="stylesheet" href="style.css?v=14.0">
    
    <style>
        /* 1. APPLY HEAVY BLACK OVERLAY TO HIDE THE BLUE */
        body, html, .screen-container {
            background-color: #0b0f19 !important;
            background-image: linear-gradient(rgba(11, 15, 25, 0.95), rgba(11, 15, 25, 0.95)), url('front.png') !important;
            background-size: cover !important;
            background-position: center !important;
            background-attachment: fixed !important;
            background-repeat: no-repeat !important;
        }

        /* 2. GLASSMORPHISM UI OVERRIDES */
        .game-card, .login-panel, .chrome-modal, .dice-game-layout, .screen-container > header {
            background-color: rgba(15, 23, 42, 0.65) !important;
            backdrop-filter: blur(12px) !important;
            -webkit-backdrop-filter: blur(12px) !important;
            border: 1px solid rgba(255, 255, 255, 0.08) !important;
            box-shadow: 0 8px 32px 0 rgba(0, 0, 0, 0.4) !important;
            border-radius: 12px;
        }
    </style>
</head>
<body>

    <div id="global-hud" class="regen-widget" style="display: <?php echo $isLoggedIn ? 'flex' : 'none'; ?>;">
        <div class="regen-bar">
            <span class="regen-icon">🪙</span>
            <span id="hud-chip-text"><?php echo $isLoggedIn ? $_SESSION['points'] : '20'; ?>/100</span>
            <button class="regen-plus" onclick="toggleShop()">+</button>
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
                <div class="game-card" onclick="showScreen('color-game')" style="background-image: url('color-roulette.jpg') !important; background-size: cover !important; background-position: center !important;">
                    <div class="card-overlay">Color Roulette</div>
                </div>
                <div class="game-card dice-bg" onclick="showScreen('dice-game')" style="background-image: url('dice.jpg') !important; background-size: cover !important; background-position: center !important;">
                    <div class="card-overlay">3D Dice</div>
                </div>
                <div class="game-card" onclick="showScreen('prime-game')" style="background-image: url('prime prediction.jfif') !important; background-size: cover !important; background-position: center !important;">
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
                    <div class="face bottom"><div class="dot"></div><div class="dot"></div><div class="dot"></div><div class="dot"></div></div>
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
                <h3 style="margin: 0 0 10px 0; color: #f1c40f; font-size: 1rem; text-align: center;">📊 PROBABILITY</h3>
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
                <h3 style="margin: 0 0 10px 0; color: #e74c3c; font-size: 1rem; text-align: center;">📊 PROBABILITY</h3>
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
                <label>Bet: <input type="number" id="prime-bet-amount" min="10" step="10" value="10" style="width:100px;"></label>
                <label>Prediction:
                    <select id="prime-bet-type">
                        <option value="prime">Prime</option>
                        <option value="not_prime">Not Prime</option>
                    </select>
                </label>
            </div>
            <p style="font-size: 0.85rem; color: #f1c40f; margin-top: 0;">(50/50 Odds | 2x Payout)</p>

            <div class="slots" style="margin: 20px 0;">
                <div class="slot" id="prime-spinner" style="color: #333;">--</div>
            </div>

            <div class="trend-box">
                <h3 style="margin: 0 0 10px 0; color: #3498db; font-size: 1rem; text-align: center;">📊 PROBABILITY</h3>
                <div class="trend-stats">
                    <div class="trend-col" style="text-align:center; width: 100%; flex-direction: row; justify-content: space-around;">
                        <span>PRIME: <span id="trend-prime" class="white-text">0%</span></span> 
                        <span>NOT PRIME: <span id="trend-notprime" class="white-text">0%</span></span>
                    </div>
                </div>
                <div id="trend-pred-prime" class="trend-pred">Waiting for spins...</div>
            </div>

            <button id="prime-btn" class="login-btn" style="float:none; margin: 0 auto; padding: 15px 50px;">SPIN NUMBER</button>
            <div id="prime-result" class="game-result-text">Guess Prime or Not Prime!</div>
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
                <div class="chrome-menu-item" onclick="showLogoutConfirm()"><span class="icon">🚪</span><span>Log out</span></div>
            </div>
        </div>
    </div>

    <div id="shop-modal" class="modal-overlay" style="display: none;" onclick="toggleShop()">
        <div class="chrome-modal shop-modal-content" onclick="event.stopPropagation()">
            <div class="chrome-profile-header" style="padding-bottom: 10px;">
                <h3 style="font-size: 20px; color: #2ecc71; margin: 0;">🛒 RECHARGE CHIPS</h3>
                <p style="color: #bdc3c7; font-size: 12px; margin-top: 5px;">Get more bang for your buck!</p>
                <button class="chrome-blue-btn" onclick="toggleShop()" style="margin-top: 10px;">Close Shop</button>
            </div>
            <hr class="chrome-divider">
            <div class="shop-grid">
                <div class="shop-item" onclick="confirmBuyChips(150, 100)">
                    <span class="shop-chip-amt">🪙 150</span>
                    <button class="buy-btn">$100</button>
                </div>
                <div class="shop-item" onclick="confirmBuyChips(250, 150)">
                    <span class="shop-chip-amt">🪙 250</span>
                    <button class="buy-btn">$150</button>
                </div>
                <div class="shop-item" onclick="confirmBuyChips(400, 200)">
                    <span class="shop-chip-amt">🪙 400</span>
                    <button class="buy-btn">$200</button>
                </div>
                <div class="shop-item" onclick="confirmBuyChips(1100, 500)">
                    <span class="shop-chip-amt">🪙 1100</span>
                    <button class="buy-btn">$500</button>
                </div>
                <div class="shop-item" onclick="confirmBuyChips(1800, 800)">
                    <span class="shop-chip-amt">🪙 1800</span>
                    <button class="buy-btn">$800</button>
                </div>
                <div class="shop-item" onclick="confirmBuyChips(2500, 1000)">
                    <span class="shop-chip-amt">🪙 2500</span>
                    <button class="buy-btn">$1000</button>
                </div>
            </div>
        </div>
    </div>

    <div id="logout-confirm-modal" class="modal-overlay" style="display: none; z-index: 2002;">
        <div class="chrome-modal shop-modal-content" style="margin-top: 30vh; text-align: center; padding-bottom: 20px;">
            <div class="chrome-profile-header" style="padding-bottom: 10px;">
                <h3 style="font-size: 22px; color: #e74c3c; margin: 0;">🚪 LOG OUT</h3>
                <p style="color: #e8eaed; font-size: 16px; margin-top: 15px; padding: 0 20px;">Are you sure to log out?</p>
            </div>
            <div style="display: flex; justify-content: center; gap: 15px; margin-top: 15px; padding: 0 20px;">
                <button class="buy-btn" style="background: #e74c3c;" onclick="document.getElementById('logout-confirm-modal').style.display='none'">Cancel</button>
                <button class="buy-btn" style="background: #2ecc71;" onclick="window.location.href='index.php?logout=true'">Confirm</button>
            </div>
        </div>
    </div>
    
    <div id="recharge-confirm-modal" class="modal-overlay" style="display: none; z-index: 2003;">
        <div class="chrome-modal shop-modal-content" style="margin-top: 30vh; text-align: center; padding-bottom: 20px;">
            <div class="chrome-profile-header" style="padding-bottom: 10px;">
                <h3 style="font-size: 22px; color: #2ecc71; margin: 0;">💰 CONFIRM PURCHASE</h3>
                <p id="recharge-confirm-msg" style="color: #e8eaed; font-size: 16px; margin-top: 15px; padding: 0 20px;">Are you sure you want to buy chips?</p>
            </div>
            <div style="display: flex; justify-content: center; gap: 15px; margin-top: 15px; padding: 0 20px;">
                <button class="buy-btn" style="background: #e74c3c;" onclick="cancelRecharge()">Cancel</button>
                <button class="buy-btn" style="background: #2ecc71;" onclick="executeRecharge()">Confirm</button>
            </div>
        </div>
    </div>

    <div id="confirm-modal" class="modal-overlay" style="display: none; z-index: 2000;">
        <div class="chrome-modal shop-modal-content" style="margin-top: 30vh; text-align: center; padding-bottom: 20px;">
            <div class="chrome-profile-header" style="padding-bottom: 10px;">
                <h3 style="font-size: 22px; color: #f1c40f; margin: 0;">⚠️ CONFIRM BET</h3>
                <p id="confirm-msg" style="color: #e8eaed; font-size: 16px; margin-top: 15px; padding: 0 20px;">Are you sure?</p>
            </div>
            
            <div style="margin-top: 15px; font-size: 14px; color: #bdc3c7; display: flex; justify-content: center; align-items: center;">
                <input type="checkbox" id="skip-confirm-checkbox" style="cursor: pointer; margin-right: 8px; width: 16px; height: 16px;">
                <label for="skip-confirm-checkbox" style="cursor: pointer;">Don't ask again for this session</label>
            </div>

            <div style="display: flex; justify-content: center; gap: 15px; margin-top: 15px; padding: 0 20px;">
                <button class="buy-btn" style="background: #e74c3c;" onclick="closeConfirmModal()">Cancel</button>
                <button class="buy-btn" style="background: #2ecc71;" onclick="executePendingBet()">Confirm</button>
            </div>
        </div>
    </div>

    <div id="alert-modal" class="modal-overlay" style="display: none; z-index: 2001;">
        <div class="chrome-modal shop-modal-content" style="margin-top: 30vh; text-align: center; padding-bottom: 20px;">
            <div class="chrome-profile-header" style="padding-bottom: 10px;">
                <h3 style="font-size: 22px; color: #e74c3c; margin: 0;">⚠️ ACTION BLOCKED</h3>
                <p id="alert-msg" style="color: #e8eaed; font-size: 16px; margin-top: 15px; padding: 0 20px;">Message goes here</p>
            </div>
            <div style="display: flex; justify-content: center; gap: 15px; margin-top: 15px; padding: 0 20px;">
                <button class="buy-btn" style="background: #e74c3c; width: 50%;" onclick="closeAlertModal()">OK</button>
            </div>
        </div>
    </div>

    <div id="game-over-modal" class="modal-overlay" style="display: none;">
        <div class="game-over-box">
            <h2 class="go-title">OUT OF CHIPS!</h2>
            <div class="stats-container">
                <p>Matches Played<br><span id="go-matches-played" class="go-stat">0</span></p>
                <p>Highest Chips<br><span id="go-highest-chips" class="go-stat">20</span></p>
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
            // Pass PHP variables cleanly to JavaScript
            window.serverPoints = <?php echo $_SESSION['points']; ?>;
            window.serverTotalWon = <?php echo $_SESSION['total_won']; ?>;
            window.serverTotalSpent = <?php echo $_SESSION['total_spent']; ?>;
            window.serverName = "<?php echo $_SESSION['username']; ?>";
        <?php endif; ?>
    </script>
    <script src="Script.js?v=14.0"></script>
</body>
</html>
