// --- GAME STATE ---
let points = 1000; 
let totalSpent = 0;
let payoutMultiplier = 1.2;
let userData = { name: "", num: "", pic: "" };

// --- DOM ELEMENTS ---
const button = document.getElementById('roll-btn');
const die1 = document.getElementById('die-1');
const die2 = document.getElementById('die-2');
const resultText = document.getElementById('result');
const balanceDisplay = document.getElementById('balance');
const betInput = document.getElementById('bet-amount');
const betTypeSelect = document.getElementById('bet-type');
const exactGuessWrapper = document.getElementById('exact-guess-wrapper');
const exactNumberInput = document.getElementById('exact-number');

// Show/Hide Exact Number input
betTypeSelect.addEventListener('change', (e) => {
    exactGuessWrapper.style.display = e.target.value === 'exact' ? 'flex' : 'none';
});

// --- 1. PROFILE & LOGIN ---
function previewImage(input) {
    if (input.files && input.files[0]) {
        let reader = new FileReader();
        reader.onload = function(e) {
            document.getElementById('login-avatar').src = e.target.result;
            userData.pic = e.target.result;
        };
        reader.readAsDataURL(input.files[0]);
    }
}

function handleLogin() {
    userData.name = document.getElementById('username').value;
    userData.num = document.getElementById('player-num').value;
    if(!userData.name || !userData.num) return alert("Fill in your details!");

    document.getElementById('header-name').innerText = userData.name;
    document.getElementById('header-avatar').src = userData.pic || 'user.png';
    document.getElementById('modal-name').innerText = userData.name;
    document.getElementById('modal-avatar').src = userData.pic || 'user.png';

    updateProfileStats();
    showScreen('main-menu');
}

function showScreen(id) {
    document.querySelectorAll('.screen-container').forEach(s => s.style.display = 'none');
    document.getElementById(id).style.display = 'block';
}

function toggleProfile() {
    let modal = document.getElementById('profile-modal');
    modal.style.display = (modal.style.display === 'none') ? 'flex' : 'none';
}

function updateProfileStats() {
    let rankName = "Unranked";
    let nextRankTarget = "Max Rank Achieved!";

    if (totalSpent >= 3000) { rankName = "High Roller"; } 
    else if (totalSpent >= 2000) { rankName = "Godly"; nextRankTarget = 3000; } 
    else if (totalSpent >= 1500) { rankName = "Newbie"; nextRankTarget = 2000; } 
    else if (totalSpent >= 1000) { rankName = "Beginner"; nextRankTarget = 1500; } 
    else { nextRankTarget = 1000; }

    document.getElementById('modal-rank-display').innerText = `Rank: ${rankName}`;
    document.getElementById('modal-chips').innerText = points;
    document.getElementById('modal-spent').innerText = totalSpent;
    document.getElementById('modal-next-rank').innerText = nextRankTarget;
}

// --- 2. 3D DICE LOGIC ---
function getRotation(number) {
    switch(number) {
        case 1: return {x: 0, y: 0}; case 2: return {x: 0, y: -90 };
        case 3: return {x: -90, y: 0}; case 4: return {x: 90, y: 0};
        case 5: return {x: 0, y: 90}; case 6: return {x: 180, y: 0};
    }
}

function toggleInputs(disabled) {
    button.disabled = disabled; betInput.disabled = disabled;
    betTypeSelect.disabled = disabled; exactNumberInput.disabled = disabled;
}

button.addEventListener('click', () => {
    const betAmount = parseInt(betInput.value);
    if (isNaN(betAmount) || betAmount <= 0) return alert("Please enter a valid bet amount.");
    if (betAmount > points) return alert("You don't have enough chips for that bet!");

    // Track spent amount for profile achievements
    totalSpent += betAmount;
    updateProfileStats();

    toggleInputs(true);
    resultText.className = ""; 
    resultText.innerText = "Rolling...";

    const num1 = Math.floor(Math.random() * 6) + 1;
    const num2 = Math.floor(Math.random() * 6) + 1;
    const rot1 = getRotation(num1);
    const rot2 = getRotation(num2);

    const spinX1 = Math.floor(Math.random() * 3 + 2) * 360;
    const spinY1 = Math.floor(Math.random() * 3 + 2) * 360;
    const spinX2 = Math.floor(Math.random() * 3 + 2) * 360;
    const spinY2 = Math.floor(Math.random() * 3 + 2) * 360;

    die1.style.transform = `rotateX(${rot1.x + spinX1}deg) rotateY(${rot1.y + spinY1}deg)`;
    die2.style.transform = `rotateX(${rot2.x + spinX2}deg) rotateY(${rot2.y + spinY2}deg)`;

    setTimeout(() => {
        const total = num1 + num2;
        const betType = betTypeSelect.value;
        let isWinner = false;

        if (betType === 'low' && total >= 2 && total <= 6) isWinner = true;
        else if (betType === 'high' && total >= 8 && total <= 12) isWinner = true;
        else if (betType === 'exact' && total === parseInt(exactNumberInput.value)) isWinner = true;

        if (isWinner) {
            const winnings = Math.floor(betAmount * payoutMultiplier);
            points += winnings;
            resultText.innerHTML = `You rolled a ${total}! <span class="win-text">You WIN ${winnings} chips! 🎉</span>`;
        } else {
            points -= betAmount;
            resultText.innerHTML = `You rolled a ${total}. <span class="lose-text">You lost ${betAmount} chips. 😢</span>`;
        }

        balanceDisplay.innerText = points;
        updateProfileStats(); // Update profile modal with new chip balance
        toggleInputs(false);

        if (points <= 0) {
            setTimeout(() => {
                alert("You're out of chips! The house has given you a 1000 chip pity-loan.");
                points = 1000;
                balanceDisplay.innerText = points;
                updateProfileStats();
            }, 500);
        }
    }, 1500);
});
