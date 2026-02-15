<?php
session_start();
include 'db_connect.php';

if (!isset($_SESSION['user'])) { header("Location: index.php"); exit(); }
$username = $_SESSION['user'];

// 1. Fetch User Data
$user_query = $conn->query("SELECT * FROM users WHERE username='$username'");
$u = $user_query->fetch_assoc();
$email = $u['email'];
$salary = $u['salary'];

// 2. Financial Calculations
$exp_query = $conn->query("SELECT SUM(amount) as t FROM expenses WHERE user_email='$email'");
$total_spent = $exp_query->fetch_assoc()['t'] ?? 0;
$balance = $salary - $total_spent;

// 3. The Virtual Economy Math
// Every ₹100 saved = 1 Builder Point
$total_capacity = max(0, floor($balance / 100)); 

// Check if in disaster
$is_disaster = ($balance < 0);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>FinCity Architect - FinTrackPro</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Inter:wght@300;400;600;700&family=Montserrat:wght@500;700;800;900&display=swap');
        
        * { box-sizing: border-box; margin: 0; padding: 0; }
        body { background: #0f172a; color: #fff; font-family: 'Inter', sans-serif; margin: 0; padding: 0; overflow: hidden; }
        
        /* SIDEBAR (Fixed) */
        .sidebar { position: fixed; top: 0; left: 0; width: 260px; height: 100vh; background: #1e293b; padding: 30px 20px; display: flex; flex-direction: column; border-right: 1px solid #334155; z-index: 100; }
        .brand { font-family: 'Montserrat', sans-serif; font-size: 22px; font-weight: 800; margin-bottom: 50px; display: flex; align-items: center; gap: 12px; color: #fff; padding-left: 5px; }
        .brand i { color: #06b6d4; font-size: 24px; } .brand span { color: #06b6d4; }
        .nav-link { display: flex; align-items: center; gap: 15px; padding: 14px; color: #94a3b8; text-decoration: none; border-radius: 12px; margin-bottom: 8px; font-weight: 500; transition: 0.3s; border-left: 4px solid transparent; }
        .nav-link:hover, .nav-link.active { background: linear-gradient(90deg, rgba(6, 182, 212, 0.1), transparent); color: #06b6d4; font-weight: 700; border-left: 4px solid #06b6d4; }
        .nav-link i { width: 20px; text-align: center; }

        /* MAIN LAYOUT */
        .main { margin-left: 260px; height: 100vh; display: flex; flex-direction: column; }
        
        /* HEADER HUD */
        .hud { background: #1e293b; padding: 20px 40px; border-bottom: 1px solid #334155; display: flex; justify-content: space-between; align-items: center; z-index: 10; box-shadow: 0 10px 30px rgba(0,0,0,0.5); }
        .hud-title { font-family: 'Montserrat', sans-serif; font-size: 24px; font-weight: 800; display: flex; align-items: center; gap: 10px; }
        
        .capacity-box { background: #0f172a; border: 1px solid #06b6d4; padding: 10px 20px; border-radius: 12px; display: flex; align-items: center; gap: 15px; box-shadow: 0 0 15px rgba(6, 182, 212, 0.2); }
        .cap-text { font-size: 12px; color: #94a3b8; text-transform: uppercase; font-weight: 700; }
        .cap-val { font-size: 24px; font-weight: 900; color: #06b6d4; font-family: 'Montserrat', sans-serif; }

        /* WORKSPACE (Grid + Shop) */
        .workspace { flex: 1; display: flex; overflow: hidden; position: relative; }
        
        /* THE MAP / GRID */
        .map-area { flex: 1; background: radial-gradient(circle, #1e293b 0%, #0f172a 100%); display: flex; justify-content: center; align-items: center; padding: 40px; <?= $is_disaster ? 'background: radial-gradient(circle, #450a0a 0%, #0f172a 100%); animation: lightning 3s infinite;' : '' ?> }
        
        @keyframes lightning { 0%, 95%, 98% { opacity: 1; } 96%, 99% { opacity: 0.8; background: #fff; } }

        .city-grid { 
            display: grid; grid-template-columns: repeat(5, 1fr); gap: 15px; 
            background: rgba(15, 23, 42, 0.8); padding: 20px; border-radius: 20px; 
            border: 2px solid #334155; box-shadow: 0 25px 50px rgba(0,0,0,0.5);
            transform: perspective(1000px) rotateX(10deg); /* Slight 3D tilt */
        }

        .plot { 
            width: 100px; height: 100px; background: #1e293b; border: 2px dashed #475569; 
            border-radius: 15px; display: flex; justify-content: center; align-items: center; 
            cursor: pointer; transition: 0.3s; position: relative;
        }
        .plot:hover { border-color: #06b6d4; background: rgba(6, 182, 212, 0.1); transform: translateY(-5px); box-shadow: 0 10px 20px rgba(6, 182, 212, 0.2); }
        .plot i { font-size: 45px; filter: drop-shadow(0 10px 10px rgba(0,0,0,0.5)); transition: 0.3s; }
        .plot.filled { border-style: solid; }
        
        /* Warning state for buildings if user goes broke */
        .plot.deficit { border-color: #f43f5e; animation: pulseRed 1.5s infinite; }
        .plot.deficit i { color: #f43f5e !important; }
        @keyframes pulseRed { 0% { box-shadow: 0 0 0 0 rgba(244, 63, 94, 0.4); } 70% { box-shadow: 0 0 0 15px rgba(244, 63, 94, 0); } }

        /* THE SHOP SIDEBAR */
        .shop-panel { width: 320px; background: #1e293b; border-left: 1px solid #334155; padding: 30px 20px; overflow-y: auto; display: flex; flex-direction: column; gap: 20px; }
        .shop-title { font-size: 14px; text-transform: uppercase; color: #94a3b8; font-weight: 700; letter-spacing: 1px; border-bottom: 1px solid #334155; padding-bottom: 10px; margin-bottom: 10px; }
        
        .shop-item { 
            background: #0f172a; border: 1px solid #334155; padding: 15px; border-radius: 16px; 
            display: flex; align-items: center; gap: 15px; cursor: pointer; transition: 0.2s;
        }
        .shop-item:hover { border-color: #06b6d4; transform: translateX(-5px); }
        .shop-item.selected { border-color: #22c55e; background: rgba(34, 197, 94, 0.1); box-shadow: 0 0 15px rgba(34, 197, 94, 0.2); }
        
        .shop-icon { width: 50px; height: 50px; background: #1e293b; border-radius: 12px; display: flex; justify-content: center; align-items: center; font-size: 24px; }
        .shop-details { flex: 1; }
        .shop-name { font-weight: 700; font-size: 15px; color: #fff; }
        .shop-cost { font-size: 12px; color: #06b6d4; font-weight: 700; margin-top: 5px; display: flex; align-items: center; gap: 5px;}

        /* Tooltip */
        .tooltip { position: absolute; bottom: 110%; background: #0f172a; padding: 5px 10px; border-radius: 8px; font-size: 11px; color: #fff; border: 1px solid #334155; pointer-events: none; opacity: 0; transition: 0.2s; white-space: nowrap; }
        .plot:hover .tooltip { opacity: 1; }
    </style>
</head>
<body>

    <?php include 'sidebar.php'; ?>

    <div class="main">
        
        <div class="hud">
            <div class="hud-title"><i class="fa-solid fa-map-location-dot" style="color: #06b6d4;"></i> Architect Mode</div>
            <div style="display: flex; gap: 20px;">
                <button onclick="clearCity()" style="background: transparent; border: 1px solid #f43f5e; color: #f43f5e; padding: 10px 20px; border-radius: 12px; cursor: pointer; font-weight: bold; transition: 0.3s;">Demolish All</button>
                <div class="capacity-box">
                    <div>
                        <div class="cap-text">Building Capacity</div>
                        <div style="font-size: 10px; color: #64748b;">(1 Pt = ₹100 Saved)</div>
                    </div>
                    <div class="cap-val"><span id="usedPoints">0</span> / <?= $total_capacity ?></div>
                </div>
            </div>
        </div>

        <div class="workspace">
            
            <div class="map-area">
                <div class="city-grid" id="cityGrid">
                    </div>
            </div>

            <div class="shop-panel">
                <div class="shop-title"><i class="fa-solid fa-store"></i> Blueprint Shop</div>
                
                <div class="shop-item" onclick="selectBlueprint('tree', 5, '#22c55e', 'fa-tree')">
                    <div class="shop-icon" style="color: #22c55e;"><i class="fa-solid fa-tree"></i></div>
                    <div class="shop-details">
                        <div class="shop-name">City Park</div>
                        <div class="shop-cost"><i class="fa-solid fa-cube"></i> 5 Pts</div>
                    </div>
                </div>

                <div class="shop-item" onclick="selectBlueprint('house', 20, '#cbd5e1', 'fa-house')">
                    <div class="shop-icon" style="color: #cbd5e1;"><i class="fa-solid fa-house"></i></div>
                    <div class="shop-details">
                        <div class="shop-name">Family Home</div>
                        <div class="shop-cost"><i class="fa-solid fa-cube"></i> 20 Pts</div>
                    </div>
                </div>

                <div class="shop-item" onclick="selectBlueprint('hospital', 100, '#f43f5e', 'fa-hospital')">
                    <div class="shop-icon" style="color: #f43f5e;"><i class="fa-solid fa-hospital"></i></div>
                    <div class="shop-details">
                        <div class="shop-name">Emergency Hospital</div>
                        <div class="shop-cost"><i class="fa-solid fa-cube"></i> 100 Pts</div>
                    </div>
                </div>

                <div class="shop-item" onclick="selectBlueprint('tech', 250, '#06b6d4', 'fa-building-shield')">
                    <div class="shop-icon" style="color: #06b6d4;"><i class="fa-solid fa-building-shield"></i></div>
                    <div class="shop-details">
                        <div class="shop-name">Cyber Skyscraper</div>
                        <div class="shop-cost"><i class="fa-solid fa-cube"></i> 250 Pts</div>
                    </div>
                </div>

                <?php if($is_disaster): ?>
                    <div style="background: rgba(244, 63, 94, 0.1); border: 1px solid #f43f5e; padding: 15px; border-radius: 12px; color: #f43f5e; font-size: 13px; font-weight: bold; text-align: center; margin-top: 20px;">
                        <i class="fa-solid fa-triangle-exclamation"></i> CITY BANKRUPT!<br>You cannot build while in debt.
                    </div>
                <?php endif; ?>
            </div>

        </div>
    </div>

    <script>
        const MAX_CAPACITY = <?= $total_capacity ?>;
        const IS_DISASTER = <?= $is_disaster ? 'true' : 'false' ?>;
        const USERNAME = "<?= $username ?>"; // To save data uniquely per user
        
        let currentBlueprint = null;
        let cityData = JSON.parse(localStorage.getItem('fincity_' + USERNAME)) || new Array(15).fill(null);

        // Initialize Grid
        const gridEl = document.getElementById('cityGrid');
        function renderGrid() {
            gridEl.innerHTML = '';
            let usedPoints = 0;

            cityData.forEach((plot, index) => {
                const div = document.createElement('div');
                div.className = 'plot';
                div.onclick = () => buildOnPlot(index);

                if (plot) {
                    usedPoints += plot.cost;
                    div.classList.add('filled');
                    div.style.borderColor = plot.color;
                    div.innerHTML = `<i class="fa-solid ${plot.icon}" style="color: ${plot.color};"></i><div class="tooltip">${plot.name}</div>`;
                } else {
                    div.innerHTML = `<i class="fa-solid fa-plus" style="color: #334155; font-size: 20px;"></i>`;
                }
                gridEl.appendChild(div);
            });

            // Update HUD
            document.getElementById('usedPoints').innerText = usedPoints;

            // REAL-WORLD CONSEQUENCE CHECK
            // If user spent money in real life and their capacity dropped below their built city...
            if (usedPoints > MAX_CAPACITY) {
                document.getElementById('usedPoints').style.color = '#f43f5e';
                // Make all buildings flash red indicating unpaid debt
                document.querySelectorAll('.plot.filled').forEach(p => p.classList.add('deficit'));
            } else {
                document.getElementById('usedPoints').style.color = '#06b6d4';
            }
        }

        // Shop Selection
        function selectBlueprint(id, cost, color, icon) {
            if (IS_DISASTER) {
                alert("You cannot build! Your bank balance is negative.");
                return;
            }

            // UI Highlight
            document.querySelectorAll('.shop-item').forEach(el => el.classList.remove('selected'));
            event.currentTarget.classList.add('selected');

            currentBlueprint = { id, name: event.currentTarget.querySelector('.shop-name').innerText, cost, color, icon };
        }

        // Building Mechanic
        function buildOnPlot(index) {
            if (!currentBlueprint) return alert("Select a blueprint from the shop first!");
            if (IS_DISASTER) return alert("Clear your debts before building!");

            // Calculate current used points
            let currentUsed = cityData.reduce((sum, plot) => sum + (plot ? plot.cost : 0), 0);
            
            // If plot is already occupied, refund the points first
            if (cityData[index]) {
                currentUsed -= cityData[index].cost;
            }

            // Check if they can afford it
            if (currentUsed + currentBlueprint.cost > MAX_CAPACITY) {
                return alert("Not enough Building Capacity! Save more money to build this.");
            }

            // Build it!
            cityData[index] = currentBlueprint;
            localStorage.setItem('fincity_' + USERNAME, JSON.stringify(cityData));
            
            // Plop Animation
            renderGrid();
            gridEl.children[index].style.transform = 'scale(1.2)';
            setTimeout(() => { gridEl.children[index].style.transform = ''; }, 200);
        }

        function clearCity() {
            if(confirm("Are you sure you want to demolish your entire city?")) {
                cityData = new Array(15).fill(null);
                localStorage.setItem('fincity_' + USERNAME, JSON.stringify(cityData));
                renderGrid();
            }
        }

        // Initial Load
        renderGrid();
    </script>
</body>
</html>