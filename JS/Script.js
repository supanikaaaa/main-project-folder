// Check if PHP passed us any saved points, otherwise default to 20
let points = typeof window.serverPoints !== 'undefined' ? window.serverPoints : 20;
let totalWon = typeof window.serverTotalWon !== 'undefined' ? window.serverTotalWon : 0;
let totalSpent = typeof window.serverTotalSpent !== 'undefined' ? window.serverTotalSpent : 0;

let maxChips = 100, regenAmount = 10, regenTimeLimit = 300, regenTimer = regenTimeLimit; 
let matchesPlayed = 0, highestChips = points, currentRank = "Unranked"; 
let diceHistory = [], colorHistory = [], primeHistory = [];

let isGameActive = false; 

// Stores the function to run after they click "Confirm" in the modal
let pendingBetCallback = null;

// Tracks if the user selected "Don't ask me again"
let skipConfirm = false;

if (typeof isUserLoggedIn !== 'undefined' && isUserLoggedIn) {
    setupInitialRoulette(); 
    updateProfileStats();
}

function previewImage(input) {
    if (input.files && input.files[0]) {
        let reader = new FileReader();
        reader.onload = (e) => { 
            document.getElementById('login-avatar').src = e.target.result; 
        };
        reader.readAsDataURL(input.files[0]);
    }
}

function showScreen(id) { 
    if (isGameActive) {
        showAlertModal("Please wait for the current round to finish!");
        return; 
    }
    document.querySelectorAll('.screen-container').forEach(s => s.style.display = 'none'); 
    document.getElementById(id).style.display = 'block'; 
    
    // Hide the shop (+) button if we are not in the main menu!
    let shopBtn = document.querySelector('.regen-plus');
    if (shopBtn) {
        shopBtn.style.display = (id === 'main-menu') ? 'flex' : 'none';
    }
}

function toggleProfile() { 
    if (isGameActive) {
        showAlertModal("Please wait for the current round to finish!");
        return; 
    }
    let m = document.getElementById('profile-modal'); 
    m.style.display = (m.style.display === 'none') ? 'flex' : 'none'; 
}

function toggleShop() {
    if (isGameActive) {
        showAlertModal("Please wait for the current round to finish!");
        return;
    }
    let m = document.getElementById('shop-modal');
    m.style.display = (m.style.display === 'none') ? 'flex' : 'none';
}

// --- LOGOUT CONFIRM LOGIC ---
function showLogoutConfirm() {
    document.getElementById('profile-modal').style.display = 'none'; // Hide the profile menu
    document.getElementById('logout-confirm-modal').style.display = 'flex'; // Show the logout prompt
}

// --- RECHARGE CONFIRM LOGIC ---
let pendingRechargeAmount = 0;
let pendingRechargeCost = 0;

function confirmBuyChips(amount, cost) {
    pendingRechargeAmount = amount;
    pendingRechargeCost = cost;
    document.getElementById('recharge-confirm-msg').innerText = `Are you sure you want to buy ${amount} chips for $${cost}?`;
    document.getElementById('recharge-confirm-modal').style.display = 'flex';
}

function cancelRecharge() {
    document.getElementById('recharge-confirm-modal').style.display = 'none';
    pendingRechargeAmount = 0;
    pendingRechargeCost = 0;
}

function executeRecharge() {
    document.getElementById('recharge-confirm-modal').style.display = 'none';
    buyChips(pendingRechargeAmount, pendingRechargeCost); // Call the actual buy function
}

// --- CONFIRM MODAL LOGIC WITH CHECKBOX ---
function showConfirmModal(message, callback) {
    // If they checked the box previously, skip the modal and run the bet!
    if (skipConfirm) {
        callback();
        return;
    }
    
    document.getElementById('confirm-msg').innerText = message;
    pendingBetCallback = callback;
    
    // Reset the checkbox visual state when showing
    document.getElementById('skip-confirm-checkbox').checked = false; 
    
    document.getElementById('confirm-modal').style.display = 'flex';
}

function closeConfirmModal() {
    document.getElementById('confirm-modal').style.display = 'none';
    pendingBetCallback = null;
}

function executePendingBet() {
    if (typeof pendingBetCallback === 'function') {
        // Did they check the box just now?
        if (document.getElementById('skip-confirm-checkbox').checked) {
            skipConfirm = true; // Save choice for future bets
        }
        
        let runBet = pendingBetCallback;
        document.getElementById('confirm-modal').style.display = 'none';
        pendingBetCallback = null;
        runBet();
    }
}

// --- ALERT MODAL LOGIC (FIXED FOR CONGRATS) ---
function showAlertModal(message, title = "⚠️ ACTION BLOCKED", color = "#e74c3c") {
    document.getElementById('alert-msg').innerText = message;
    
    // Find the title element and update it dynamically
    let modalTitle = document.querySelector('#alert-modal h3');
    if (modalTitle) {
        modalTitle.innerText = title;
        modalTitle.style.color = color;
    }
    
    document.getElementById('alert-modal').style.display = 'flex';
}

function closeAlertModal() {
    document.getElementById('alert-modal').style.display = 'none';
}
// ------------------------------------

function buyChips(chipsBought, cost) {
    let sfx = new Audio('cashtopup.mp3');
    sfx.play().catch(e => console.log(e));
    
    // Sync the purchase with the PHP server
    fetch('game_logic.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ game: 'topup', amount: chipsBought })
    })
    .then(res => res.json())
    .then(data => {
        if (data.error) {
            showAlertModal("Transaction failed: " + data.error);
            return;
        }
        
        // Server synced! Update local points to match the server
        points = data.newPoints;
        updateProfileStats();
        toggleShop(); // This closes the shop menu
        
        showAlertModal(`Success! You bought ${chipsBought} chips for $${cost}.`, "🎉 CONGRATS!", "#2ecc71");
    })
    .catch(err => {
        console.error(err);
        showAlertModal("Network error while buying chips.");
    });
}

function updateProfileStats() {
    if (totalSpent >= 3000) currentRank = "High Roller"; 
    else if (totalSpent >= 2000) currentRank = "Godly"; 
    else if (totalSpent >= 1500) currentRank = "Newbie Rank"; 
    else if (totalSpent >= 1000) currentRank = "Beginner"; 
    else currentRank = "Unranked";
    
    let achLevel = 0, achName = "Newbie", nextAchTarget = 1000;
    
    if (totalWon >= 50000) { achLevel = 5; achName = "God"; nextAchTarget = "Maxed!"; } 
    else if (totalWon >= 10000) { achLevel = 4; achName = "Legend"; nextAchTarget = 50000; } 
    else if (totalWon >= 5000) { achLevel = 3; achName = "Monster"; nextAchTarget = 10000; } 
    else if (totalWon >= 2500) { achLevel = 2; achName = "Unique"; nextAchTarget = 5000; } 
    else if (totalWon >= 1000) { achLevel = 1; achName = "Normal"; nextAchTarget = 2500; }
    
    maxChips = 100 + (achLevel * 25);
    document.getElementById('modal-achievement-display').innerText = `Achievement: ${achName}`;
    document.getElementById('modal-chips').innerText = points; 
    document.getElementById('modal-total-won').innerText = totalWon;
    document.getElementById('modal-spent').innerText = totalSpent; 
    document.getElementById('modal-next-ach').innerText = nextAchTarget;
    document.getElementById('hud-chip-text').innerText = `${points}/${maxChips}`;
}

setInterval(() => {
    if (points < maxChips) {
        regenTimer--; 
        let m = Math.floor(regenTimer / 60).toString().padStart(2, '0'); 
        let s = (regenTimer % 60).toString().padStart(2, '0');
        document.getElementById('regen-timer').innerText = `+ ${regenAmount} IN ${m}:${s}`;
        
        if (regenTimer <= 0) { 
            let amountToAdd = regenAmount;
            if (points + regenAmount > maxChips) {
                amountToAdd = maxChips - points;
            }
            
            if (amountToAdd > 0) {
                fetch('game_logic.php', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({ game: 'topup', amount: amountToAdd })
                })
                .then(res => res.json())
                .then(data => {
                    if (!data.error) {
                        points = data.newPoints;
                        updateProfileStats();
                    }
                }).catch(err => console.error(err));
            }
            
            regenTimer = regenTimeLimit; 
        }
    } else { 
        document.getElementById('regen-timer').innerText = "MAX CAPACITY"; 
        regenTimer = regenTimeLimit; 
    }
}, 1000);

function processEndOfGame() {
    matchesPlayed++; 
    if (points > highestChips) highestChips = points; 
    updateProfileStats();
    
    if (points <= 0) {
        setTimeout(() => { showGameOver(); }, 800); 
    }
}

/* =========================================
   GAME 1: 3D DICE
   ========================================= */
function getRot(n) { 
    switch(n) { 
        case 1: return {x:0,y:0}; 
        case 2: return {x:0,y:-90}; 
        case 3: return {x:-90,y:0}; 
        case 4: return {x:90,y:0}; 
        case 5: return {x:0,y:90}; 
        case 6: return {x:180,y:0}; 
    } 
}

document.getElementById('roll-btn').addEventListener('click', () => {
    const amt = parseInt(document.getElementById('dice-bet-amount').value);
    const type = document.getElementById('dice-bet-type').value;
    const btn = document.getElementById('roll-btn');
    const txt = document.getElementById('dice-result');
    
    if (isNaN(amt) || amt <= 0 || amt > points) {
        return showAlertModal("Invalid Bet! Check your chips."); 
    }
    
    let patternName = document.getElementById('dice-bet-type').options[document.getElementById('dice-bet-type').selectedIndex].text;
    
    showConfirmModal(`Are you sure you want to bet ${amt} chips on ${patternName}?`, () => {
        isGameActive = true;
        btn.disabled = true; 
        txt.innerText = "Rolling..."; 
        
        // IMMEDIATELY deduct bet visually to show the action registered
        points -= amt;
        totalSpent += amt;
        updateProfileStats();
        
        let sfx = new Audio('soundeffect2.mp3');
        sfx.play().catch(e => console.log("Audio play blocked", e));

        fetch('game_logic.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ game: 'dice', betAmount: amt, betType: type })
        })
        .then(res => res.json())
        .then(data => {
            if (data.error) {
                // If error, refund the visual chips
                points += amt;
                totalSpent -= amt;
                updateProfileStats();
                showAlertModal(data.error);
                btn.disabled = false; isGameActive = false; return;
            }

            // DO NOT update total winnings/points yet! Wait for animation.
            
            document.getElementById('die-1').style.transform = `rotateX(${getRot(data.n1).x + Math.floor(Math.random() * 3 + 2) * 360}deg) rotateY(${getRot(data.n1).y + Math.floor(Math.random() * 3 + 2) * 360}deg)`;
            document.getElementById('die-2').style.transform = `rotateX(${getRot(data.n2).x + Math.floor(Math.random() * 3 + 2) * 360}deg) rotateY(${getRot(data.n2).y + Math.floor(Math.random() * 3 + 2) * 360}deg)`;
            
            setTimeout(() => {
                // ANIMATION FINISHED: Now update the chips and reveal!
                points = data.newPoints;
                if(data.win) totalWon += data.reward;
                updateProfileStats();
                
                diceHistory.push(data.total); 
                if(diceHistory.length > 10) diceHistory.shift(); 
                updateDice();
                
                if (data.win) { 
                    new Audio('winning.mp3').play().catch(e => console.log(e));
                    txt.innerHTML = `<span class="win-text">YOU WIN! Payout: ${data.reward} Chips</span>`; 
                } else { 
                    new Audio('lossing.mp3').play().catch(e => console.log(e));
                    txt.innerHTML = `<span class="lose-text">LOSE! Rolled ${data.total}.</span>`; 
                }
                
                btn.disabled = false; 
                isGameActive = false;
                processEndOfGame();
            }, 1280); 
        }).catch(err => { 
            console.error(err); 
            points += amt; totalSpent -= amt; updateProfileStats(); // Refund visually
            btn.disabled = false; isGameActive = false; 
        });
    });
});

function updateDice() {
    if(!diceHistory.length) return; 
    let o=0, e=0, l=0, h=0; 
    
    diceHistory.forEach(n => {
        if(n%2!==0) o++; else e++; 
        if(n<=6) l++; 
        if(n>=8) h++;
    }); 
    
    let t = diceHistory.length;
    document.getElementById('trend-odd').innerText = Math.round((o/t)*100)+'%'; 
    document.getElementById('trend-even').innerText = Math.round((e/t)*100)+'%'; 
    document.getElementById('trend-low').innerText = Math.round((l/t)*100)+'%'; 
    document.getElementById('trend-high').innerText = Math.round((h/t)*100)+'%';
    
    let p = "Pattern is mixed!"; 
    if(t>=3) { 
        let l3 = diceHistory.slice(-3); 
        if(l3.every(n => n%2!==0)) p="🔥 3 Odds! Bet EVEN."; 
        else if(l3.every(n => n%2===0)) p="🔥 3 Evens! Bet ODD."; 
        else if(l3.every(n => n<=6)) p="🔥 3 Lows! Bet HIGH."; 
        else if(l3.every(n => n>=8)) p="🔥 3 Highs! Bet LOW."; 
    }
    document.getElementById('trend-pred-dice').innerText = p;
}

/* =========================================
   GAME 2: COLOR ROULETTE
   ========================================= */
function generateTrackArray(length) {
    let arr = []; 
    let nextColor = 'red';
    for(let i=0; i<length; i++) {
        if (i % 15 === 7) { 
            arr.push('green'); 
        } else { 
            arr.push(nextColor); 
            nextColor = (nextColor === 'red') ? 'black' : 'red'; 
        }
    }
    return arr;
}

function setupInitialRoulette() {
    const track = document.getElementById('color-track'); 
    track.innerHTML = ''; 
    track.style.transition = 'none'; 
    track.style.transform = 'translateX(0)';
    
    let colors = generateTrackArray(30);
    colors.forEach(c => { 
        let block = document.createElement('div'); 
        block.className = 'roulette-block block-' + c; 
        block.innerHTML = c === 'green' ? '🟢' : (c === 'red' ? '🔴' : '⚫'); 
        track.appendChild(block); 
    });
}

document.getElementById('color-btn').addEventListener('click', () => {
    const amt = parseInt(document.getElementById('color-bet-amount').value);
    const type = document.getElementById('color-bet-type').value;
    const btn = document.getElementById('color-btn');
    const txt = document.getElementById('color-result');
    const track = document.getElementById('color-track');
    
    if (isNaN(amt) || amt <= 0 || amt > points) {
        return showAlertModal("Invalid Bet! Check your chips."); 
    }
    
    showConfirmModal(`Are you sure you want to bet ${amt} chips on ${type.toUpperCase()}?`, () => {
        isGameActive = true;
        btn.disabled = true; 
        txt.innerText = "Spinning..."; 
        
        // IMMEDIATELY deduct bet visually
        points -= amt;
        totalSpent += amt;
        updateProfileStats();
        
        let sfx = new Audio('soundeffect1.mp3');
        sfx.play().catch(e => console.log(e));
        
        fetch('game_logic.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ game: 'color', betAmount: amt, betType: type })
        })
        .then(res => res.json())
        .then(data => {
            if (data.error) {
                points += amt; totalSpent -= amt; updateProfileStats();
                showAlertModal(data.error);
                btn.disabled = false; isGameActive = false; return;
            }

            // DO NOT update points yet! Wait for animation.
            
            const finalColorStr = data.finalColorStr;
            
            track.style.transition = 'none'; 
            track.style.transform = 'translateX(0)'; 
            track.innerHTML = '';
            
            let colors = generateTrackArray(100);
            colors.forEach(c => { 
                let block = document.createElement('div'); 
                block.className = 'roulette-block block-' + c; 
                block.innerHTML = c === 'green' ? '🟢' : (c === 'red' ? '🔴' : '⚫'); 
                track.appendChild(block); 
            });

            let targetIndex = 60 + Math.floor(Math.random() * 15);
            while (colors[targetIndex] !== finalColorStr) targetIndex++; 

            void track.offsetWidth; 
            const blockWidth = track.children[0].offsetWidth; 
            const containerWidth = document.querySelector('.roulette-container').offsetWidth;
            const targetCenter = (targetIndex * blockWidth) + (blockWidth / 2);
            const randomOffset = (Math.random() * (blockWidth * 0.6)) - (blockWidth * 0.3);
            const exactStopPos = targetCenter - (containerWidth / 2) + randomOffset;

            track.style.transition = 'transform 8.31s cubic-bezier(0.15, 0.85, 0.3, 1)';
            track.style.transform = `translateX(-${exactStopPos}px)`;

            setTimeout(() => {
                // ANIMATION FINISHED: Update points and reveal
                points = data.newPoints;
                if(data.win) totalWon += data.reward;
                updateProfileStats();

                colorHistory.push(finalColorStr); 
                if(colorHistory.length>10) colorHistory.shift(); 
                updateColor();
                
                if(data.win){ 
                    new Audio('winning.mp3').play().catch(e => console.log(e));
                    txt.innerHTML = `<span class="win-text">WIN! ${finalColorStr.toUpperCase()} pays ${data.reward} Chips</span>`; 
                } else { 
                    new Audio('lossing.mp3').play().catch(e => console.log(e));
                    txt.innerHTML = `<span class="lose-text">LOSE! Landed on ${finalColorStr.toUpperCase()}.</span>`; 
                }
                
                btn.disabled = false; 
                isGameActive = false;
                processEndOfGame();
            }, 8310); 
        }).catch(err => { 
            console.error(err); 
            points += amt; totalSpent -= amt; updateProfileStats();
            btn.disabled = false; isGameActive = false; 
        });
    });
});

function updateColor() {
    if(!colorHistory.length) return; 
    let r=0, b=0, g=0; 
    colorHistory.forEach(c => {
        if(c==='red') r++; 
        else if(c==='black') b++; 
        else g++;
    }); 
    
    let t = colorHistory.length;
    document.getElementById('trend-red').innerText = Math.round((r/t)*100)+'%'; 
    document.getElementById('trend-black').innerText = Math.round((b/t)*100)+'%'; 
    document.getElementById('trend-green').innerText = Math.round((g/t)*100)+'%';
    
    let p = "Pattern is mixed!"; 
    if(t>=3){
        let l3 = colorHistory.slice(-3); 
        if(l3.every(c => c==='red')) p="🔥 3 Reds! Bet BLACK or GREEN."; 
        else if(l3.every(c => c==='black')) p="🔥 3 Blacks! Bet RED or GREEN.";
    }
    document.getElementById('trend-pred-color').innerText = p;
}

/* =========================================
   GAME 3: PRIME PREDICTOR
   ========================================= */
document.getElementById('prime-btn').addEventListener('click', () => {
    const amt = parseInt(document.getElementById('prime-bet-amount').value);
    const primeType = document.getElementById('prime-bet-type').value;
    const btn = document.getElementById('prime-btn');
    const txt = document.getElementById('prime-result');
    const sp = document.getElementById('prime-spinner');
    
    if (isNaN(amt) || amt <= 0 || amt > points) {
        return showAlertModal("Invalid Bet! Check your chips."); 
    }
    
    let primeText = primeType === 'prime' ? 'PRIME' : 'NOT PRIME';
    
    showConfirmModal(`Are you sure you want to bet ${amt} chips on ${primeText}?`, () => {
        isGameActive = true;
        btn.disabled = true; 
        txt.innerText = "Spinning..."; 
        
        // IMMEDIATELY deduct bet visually
        points -= amt;
        totalSpent += amt;
        updateProfileStats();
        
        let sfx = new Audio('soundeffect3.mp3');
        sfx.play().catch(e => console.log(e));
        
        fetch('game_logic.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ game: 'prime', betAmount: amt, primeType: primeType })
        })
        .then(res => res.json())
        .then(data => {
            if (data.error) {
                points += amt; totalSpent -= amt; updateProfileStats();
                showAlertModal(data.error);
                btn.disabled = false; isGameActive = false; return;
            }

            // DO NOT update points yet! Wait for animation.
            
            let s = 0;
            let anim = setInterval(() => {
                sp.innerText = Math.floor(Math.random()*30)+1;
                sp.style.color = '#ffcc00'; 
                s++;
                
                if(s >= 40) { 
                    clearInterval(anim); 
                    
                    // ANIMATION FINISHED: Update points and reveal
                    points = data.newPoints;
                    if(data.win) totalWon += data.reward;
                    updateProfileStats();
                    
                    const finalNum = data.finalNum; 
                    const finalIsPrime = data.finalIsPrime;
                    
                    sp.innerText = finalNum; 
                    sp.style.color = finalIsPrime ? '#3498db' : '#e74c3c'; 
                    
                    primeHistory.push(finalIsPrime); 
                    if(primeHistory.length>10) primeHistory.shift(); 
                    
                    updatePrime();
                    
                    let primeTextDisplay = finalIsPrime ? 'PRIME' : 'NOT PRIME';

                    if(data.win){ 
                        new Audio('winning.mp3').play().catch(e => console.log(e));
                        txt.innerHTML = `<span class="win-text">WIN! ${finalNum} is ${primeTextDisplay}. Pays ${data.reward} Chips</span>`; 
                    } else {
                        new Audio('lossing.mp3').play().catch(e => console.log(e));
                        txt.innerHTML = `<span class="lose-text">LOSE! ${finalNum} is ${primeTextDisplay}.</span>`;
                    }
                    
                    btn.disabled=false; 
                    isGameActive = false;
                    processEndOfGame();
                }
            }, 51); 
        }).catch(err => { 
            console.error(err); 
            points += amt; totalSpent -= amt; updateProfileStats();
            btn.disabled = false; isGameActive = false; 
        });
    });
});

function updatePrime() {
    if(!primeHistory.length) return; 
    let p=0, np=0; 
    primeHistory.forEach(i => { if(i) p++; else np++; }); 
    
    let t = primeHistory.length;
    document.getElementById('trend-prime').innerText = Math.round((p/t)*100)+'%'; 
    document.getElementById('trend-notprime').innerText = Math.round((np/t)*100)+'%';
    
    let pt="Pattern is mixed!";
    
    if(t>=3){
        let l3p = primeHistory.slice(-3); 
        if(l3p.every(x=>x===true)) pt="🔥 3 Primes in a row! Bet NOT PRIME."; 
        else if(l3p.every(x=>x===false)) pt="🔥 3 Not Primes! Prime is due.";
    }
    
    document.getElementById('trend-pred-prime').innerText = pt;
}

/* GAMEOVER */
function showGameOver() {
    document.getElementById('go-matches-played').innerText = matchesPlayed; 
    document.getElementById('go-highest-chips').innerText = highestChips; 
    document.getElementById('go-final-rank').innerText = currentRank; 
    document.getElementById('game-over-modal').style.display = 'flex';
}

function resetGame() {
    matchesPlayed = 0; 
    diceHistory = []; 
    colorHistory = []; 
    primeHistory = []; 
    
    document.querySelectorAll('.white-text').forEach(e => e.innerText='0%'); 
    document.querySelectorAll('.trend-pred').forEach(e => e.innerText='Waiting...');
    
    updateProfileStats(); 
    setupInitialRoulette(); 
    
    document.getElementById('prime-spinner').innerText = '--'; 
    document.getElementById('prime-spinner').style.color = '#333'; 
    document.getElementById('game-over-modal').style.display = 'none';
    
    let msg = (points <= 0) ? "Wait for chips to restock!" : "Ready!";
    document.getElementById('dice-result').innerText = msg; 
    document.getElementById('color-result').innerText = msg; 
    document.getElementById('prime-result').innerText = msg;
}

function returnToMenuFromGameOver() { 
    resetGame(); 
    showScreen('main-menu'); 
}
