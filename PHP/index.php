
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Bet Life Games</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>

    <div id="login-screen" class="screen-container">
        <h1 class="pixel-text yellow-text">BET LIFE GAMES</h1>
        <div class="login-panel">
            <p class="log-in-title">LOG IN</p>
            <div class="profile-upload" onclick="document.getElementById('file-input').click()">
                <img id="login-avatar" src="user.png" alt="Profile">
                <input type="file" id="file-input" hidden accept="image/*" onchange="previewImage(this)">
            </div>
            <p class="label-text">PROFILE</p>
            
            <div class="input-group">
                <label>PLAYER NUMBER:</label>
                <input type="text" id="player-num" placeholder="0001">
            </div>
            <div class="input-group">
                <label>USERNAME:</label>
                <input type="text" id="username" placeholder="Enter Name">
            </div>
            <button class="login-btn" onclick="handleLogin()">LOG IN</button>
        </div>
    </div>

    <div id="main-menu" class="screen-container" style="display: none;">
        <header class="menu-header" style="display: flex; justify-content: space-between;">
            <div>
                <h1 class="pixel-text yellow-text" style="margin-top:0;">BET LIFE GAMES</h1>
                <p class="subtitle">BET ALL YOUR LIFE SAVINGS (JK) RIGHT NOW AND TRY TO WIN</p>
            </div>
            <div class="header-profile" onclick="toggleProfile()">
                <img id="header-avatar" src="user.png" alt="User">
                <span id="header-name">User</span>
            </div>
        </header>

        <section class="menu-content">
            <h2 class="pixel-text yellow-text">Game Modes</h2>
            
            <div class="card-container">
                <div class="game-card" onclick="alert('Coming Soon!')">
                    <img src="card.png" alt="Cards Background">
                    <div class="card-overlay">Cards</div>
                </div>
                
                <div class="game-card dice-bg" onclick="showScreen('dice-game')">
                    <img src="dice.png" alt="Dice Background">
                    <div class="card-overlay">3D Dice</div>
                </div>
                
                <div class="game-card" onclick="alert('Coming Soon!')">
                    <img src="dice.png" alt="Roulette Background">
                    <div class="card-overlay">Roulette</div>
                </div>
            </div>
            
        </section>
    </div>

    <div id="dice-game" class="screen-container" style="display: none; position: relative;">
        <div class="game-nav">
            <button class="back-btn" onclick="showScreen('main-menu')">← BACK TO MENU</button>
        </div>
        
        <div class="dice-game-layout">
            <div id="chip-counter">
                🪙 Chips: <span id="balance">1000</span>
            </div>

            <div class="bet-controls">
                <label>Bet Amount:
                    <input type="number" id="bet-amount" min="10" step="10" value="100">
                </label>
                <label>Your Guess:
                    <select id="bet-type">
                        <option value="low">Low (2-6)</option>
                        <option value="high">High (8-12)</option>
                        <option value="exact">Exact Number</option>
                    </select>
                </label>
                <label id="exact-guess-wrapper" style="display: none;">Exact Number:
                    <input type="number" id="exact-number" min="2" max="12" value="7">
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

            <button id="roll-btn">PLACE BET & ROLL</button>
            <div id="result">Ready to roll when you are!</div>
        </div>
    </div>

    <div id="profile-modal" class="modal-overlay" style="display: none;" onclick="toggleProfile()">
        <div class="chrome-modal" onclick="event.stopPropagation()">
            <div class="chrome-profile-header">
                <img id="modal-avatar" src="user.png" alt="Avatar">
                <h3 id="modal-name">Name</h3>
                <p id="modal-rank-display">Rank: Unranked</p>
                <button class="chrome-blue-btn" onclick="toggleProfile()">Close Profile</button>
            </div>
            <hr class="chrome-divider">
            <div class="chrome-menu-section">
                <div class="chrome-menu-item">
                    <span class="icon">💰</span>
                    <span>Current Chips: <span id="modal-chips">1000</span></span>
                </div>
                <div class="chrome-menu-item">
                    <span class="icon">📈</span>
                    <span>Total Spent: <span id="modal-spent">0</span></span>
                </div>
                <div class="chrome-menu-item">
                    <span class="icon">🏆</span>
                    <span>Next Rank At: <span id="modal-next-rank">1000</span></span>
                </div>
            </div>
            <hr class="chrome-divider">
            <div class="chrome-menu-section">
                <div class="chrome-menu-item" onclick="location.reload()">
                    <span class="icon">🚪</span>
                    <span>Log out</span>
                </div>
            </div>
        </div>
    </div>

    <script src="script.js"></script>
</body>
</html>
