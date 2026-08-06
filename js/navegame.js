// ===== NAVE GAME - DISTRICARNES =====
// Juego de naves espaciales (canvas) con temática espacial.

(function () {
    'use strict';

    const canvas = document.getElementById('naveCanvas');
    if (!canvas) return;
    const ctx = canvas.getContext('2d');

    // ---- Dimensiones lógicas del juego ----
    const GAME_W = 800;
    const GAME_H = 560;
    canvas.width = GAME_W;
    canvas.height = GAME_H;

    // ---- Referencias al HUD ----
    const scoreEl = document.getElementById('hudScore');
    const livesEl = document.getElementById('hudLives');
    const levelEl = document.getElementById('hudLevel');

    const startOverlay = document.getElementById('startOverlay');
    const pauseOverlay = document.getElementById('pauseOverlay');
    const gameOverOverlay = document.getElementById('gameOverOverlay');

    const finalScoreEl = document.getElementById('finalScore');
    const finalBestEl = document.getElementById('finalBest');

    const bestScoreEl = document.getElementById('bestScore');
    const lastScoreEl = document.getElementById('lastScore');

    const HIGH_SCORE_KEY = 'districarnes_navegame_highscore';
    const LAST_SCORE_KEY = 'districarnes_navegame_lastscore';

    // ---- Estado global ----
    let state = 'menu'; // menu | playing | paused | gameover
    let score = 0;
    let lives = 3;
    let level = 1;
    let highScore = parseInt(localStorage.getItem(HIGH_SCORE_KEY) || '0', 10) || 0;
    let lastScore = parseInt(localStorage.getItem(LAST_SCORE_KEY) || '0', 10) || 0;
    let lastTime = 0;
    let frameCount = 0;

    // ---- Entidades ----
    let player = null;
    let bullets = [];
    let enemies = [];
    let particles = [];
    let stars = [];

    // ---- Controles ----
    const keys = { ArrowLeft: false, ArrowRight: false, ArrowUp: false, ArrowDown: false, a: false, d: false, w: false, s: false };
    let fireHeld = false;
    let autoFire = true;

    // ---- Sonido (Web Audio API) ----
    let audioCtx = null;
    function ensureAudio() {
        if (!audioCtx) {
            try {
                audioCtx = new (window.AudioContext || window.webkitAudioContext)();
            } catch (e) {
                audioCtx = null;
            }
        }
        if (audioCtx && audioCtx.state === 'suspended') {
            audioCtx.resume();
        }
    }

    function playTone(freq, duration, type, volume) {
        if (!audioCtx) return;
        const osc = audioCtx.createOscillator();
        const gain = audioCtx.createGain();
        osc.type = type || 'square';
        osc.frequency.setValueAtTime(freq, audioCtx.currentTime);
        gain.gain.setValueAtTime(volume || 0.12, audioCtx.currentTime);
        gain.gain.exponentialRampToValueAtTime(0.001, audioCtx.currentTime + duration);
        osc.connect(gain);
        gain.connect(audioCtx.destination);
        osc.start();
        osc.stop(audioCtx.currentTime + duration);
    }

    function sfxShoot() { playTone(880, 0.08, 'square', 0.06); }
    function sfxExplosion() { playTone(120, 0.35, 'sawtooth', 0.14); }
    function sfxHit() { playTone(320, 0.12, 'triangle', 0.1); }
    function sfxLevel() {
        playTone(523, 0.12, 'square', 0.1);
        setTimeout(function () { playTone(784, 0.16, 'square', 0.1); }, 120);
    }
    function sfxGameOver() {
        playTone(392, 0.2, 'sawtooth', 0.1);
        setTimeout(function () { playTone(262, 0.2, 'sawtooth', 0.1); }, 180);
        setTimeout(function () { playTone(131, 0.4, 'sawtooth', 0.12); }, 360);
    }

    // ---- Utilidades ----
    function rand(min, max) {
        return Math.random() * (max - min) + min;
    }
    function randInt(min, max) {
        return Math.floor(rand(min, max + 1));
    }
    function clamp(v, min, max) {
        return Math.max(min, Math.min(max, v));
    }

    // ---- Estrellas de fondo ----
    function createStars() {
        stars = [];
        const count = 120;
        for (let i = 0; i < count; i++) {
            stars.push({
                x: Math.random() * GAME_W,
                y: Math.random() * GAME_H,
                r: rand(0.5, 2.2),
                speed: rand(0.3, 2.5),
                twinkle: Math.random() * Math.PI * 2
            });
        }
    }

    // ---- Jugador ----
    function createPlayer() {
        return {
            x: GAME_W / 2,
            y: GAME_H - 70,
            w: 40,
            h: 44,
            speed: 6.2,
            fireCooldown: 0,
            fireRate: 14, // frames entre disparos
            invuln: 0,
            alive: true
        };
    }

    // ---- Disparos ----
    function spawnBullet() {
        bullets.push({
            x: player.x,
            y: player.y - player.h / 2,
            w: 5,
            h: 16,
            speed: 11
        });
    }

    // ---- Enemigos ----
    function enemyTypeForLevel() {
        const roll = Math.random();
        if (level >= 3 && roll < 0.18) return 'ship';        // nave enemiga
        if (level >= 2 && roll < 0.45) return 'fast';        // asteroide rápido
        if (roll < 0.7) return 'asteroid';                    // asteroide normal
        return 'big';                                         // asteroide grande
    }

    function spawnEnemy() {
        const type = enemyTypeForLevel();
        const base = 1 + (level - 1) * 0.35;

        let size, hp, speed, scoreValue, color;
        switch (type) {
            case 'ship':
                size = 30; hp = 2; speed = rand(2.2, 3.2); scoreValue = 50;
                color = { body: '#ff3b3b', glow: '#ff0000' };
                break;
            case 'fast':
                size = 16; hp = 1; speed = rand(4.2, 5.6); scoreValue = 30;
                color = { body: '#d9a514', glow: '#ffb300' };
                break;
            case 'big':
                size = 46; hp = 3; speed = rand(1.1, 1.8); scoreValue = 20;
                color = { body: '#8a3a3a', glow: '#cc3333' };
                break;
            default: // asteroid
                size = 26; hp = 1; speed = rand(1.8, 3.0); scoreValue = 10;
                color = { body: '#b34d4d', glow: '#ff5555' };
                break;
        }

        enemies.push({
            type: type,
            x: rand(size + 10, GAME_W - size - 10),
            y: -size - 10,
            size: size,
            hp: hp,
            maxHp: hp,
            speed: speed * base,
            scoreValue: scoreValue,
            color: color,
            wobble: rand(0, Math.PI * 2),
            wobbleSpeed: rand(0.02, 0.06),
            drift: rand(-0.8, 0.8),
            angle: rand(0, Math.PI * 2),
            rotSpeed: rand(-0.05, 0.05),
            points: makeAsteroidShape(size)
        });
    }

    function makeAsteroidShape(size) {
        const pts = [];
        const n = randInt(7, 11);
        for (let i = 0; i < n; i++) {
            const a = (i / n) * Math.PI * 2;
            const r = size * rand(0.55, 1);
            pts.push({ x: Math.cos(a) * r, y: Math.sin(a) * r });
        }
        return pts;
    }

    // ---- Partículas (explosiones) ----
    function explode(x, y, color, count) {
        for (let i = 0; i < (count || 18); i++) {
            const a = Math.random() * Math.PI * 2;
            const sp = rand(1, 5);
            particles.push({
                x: x, y: y,
                vx: Math.cos(a) * sp,
                vy: Math.sin(a) * sp,
                life: rand(20, 50),
                maxLife: 50,
                size: rand(1.5, 4),
                color: Math.random() < 0.5 ? '#ffffff' : (color || '#ff0000')
            });
        }
    }

    // ---- Reset del juego ----
    function resetGame() {
        score = 0;
        lives = 3;
        level = 1;
        player = createPlayer();
        bullets = [];
        enemies = [];
        particles = [];
        frameCount = 0;
        updateHUD();
    }

    function startGame() {
        ensureAudio();
        resetGame();
        state = 'playing';
        startOverlay.classList.add('hidden');
        pauseOverlay.classList.add('hidden');
        gameOverOverlay.classList.add('hidden');
    }

    function togglePause() {
        if (state === 'playing') {
            state = 'paused';
            pauseOverlay.classList.remove('hidden');
        } else if (state === 'paused') {
            state = 'playing';
            pauseOverlay.classList.add('hidden');
            lastTime = 0; // evita salto temporal
        }
    }

    function backToMenu() {
        state = 'menu';
        pauseOverlay.classList.add('hidden');
        gameOverOverlay.classList.add('hidden');
        startOverlay.classList.remove('hidden');
        startOverlay.querySelector('.overlay-sub').textContent =
            'Pilotea tu nave, esquiva los meteoritos y dispara para lograr la máxima puntuación.';
    }

    function gameOver() {
        state = 'gameover';
        sfxGameOver();
        lastScore = score;
        localStorage.setItem(LAST_SCORE_KEY, String(lastScore));
        let isRecord = false;
        if (score > highScore) {
            highScore = score;
            localStorage.setItem(HIGH_SCORE_KEY, String(highScore));
            isRecord = true;
        }
        finalScoreEl.textContent = score;
        finalBestEl.textContent = highScore;
        gameOverOverlay.querySelector('.overlay-sub').innerHTML =
            isRecord
                ? '¡NUEVO RÉCORD! Eres el mejor piloto de DISTRICARNES.'
                : 'Tu nave fue destruida. ¡Inténtalo de nuevo, piloto!';
        gameOverOverlay.classList.remove('hidden');
        updateRecords();
    }

    // ---- Colisiones AABB ----
    function circleRect(cx, cy, cr, rx, ry, rw, rh) {
        const px = clamp(cx, rx, rx + rw);
        const py = clamp(cy, ry, ry + rh);
        const dx = cx - px;
        const dy = cy - py;
        return (dx * dx + dy * dy) <= (cr * cr);
    }

    function rectRect(a, b) {
        return a.x - a.w / 2 < b.x + b.w / 2 &&
            a.x + a.w / 2 > b.x - b.w / 2 &&
            a.y - a.h / 2 < b.y + b.h / 2 &&
            a.y + a.h / 2 > b.y - b.h / 2;
    }

    // ---- Spawning ----
    function spawnLogic() {
        // Enemigos: dificultad escalada por nivel
        const maxEnemies = Math.min(3 + level * 2, 22);
        const chance = 0.016 + level * 0.004;
        if (enemies.length < maxEnemies && Math.random() < chance) {
            spawnEnemy();
        }
    }

    function levelLogic() {
        const next = 1 + Math.floor(score / 300);
        if (next > level) {
            level = next;
            sfxLevel();
            updateHUD();
            showToast('¡NIVEL ' + level + '! ¡Dificultad aumentada!');
        }
    }

    // ---- Lógica de actualización ----
    function update() {
        frameCount++;

        // Jugador invulnerabilidad
        if (player.invuln > 0) player.invuln--;

        // Movimiento
        let dx = 0, dy = 0;
        if (keys.ArrowLeft || keys.a) dx -= 1;
        if (keys.ArrowRight || keys.d) dx += 1;
        if (keys.ArrowUp || keys.w) dy -= 1;
        if (keys.ArrowDown || keys.s) dy += 1;

        if (dx !== 0 && dy !== 0) {
            dx *= 0.7071;
            dy *= 0.7071;
        }

        player.x = clamp(player.x + dx * player.speed, player.w / 2, GAME_W - player.w / 2);
        player.y = clamp(player.y + dy * player.speed, player.h / 2, GAME_H - player.h / 2);

        // Disparo
        if (player.fireCooldown > 0) player.fireCooldown--;
        if (fireHeld && player.fireCooldown <= 0) {
            spawnBullet();
            player.fireCooldown = player.fireRate;
            sfxShoot();
        }

        // Disparos
        for (let i = bullets.length - 1; i >= 0; i--) {
            const b = bullets[i];
            b.y -= b.speed;
            if (b.y + b.h < 0) bullets.splice(i, 1);
        }

        // Enemigos
        for (let i = enemies.length - 1; i >= 0; i--) {
            const e = enemies[i];
            e.y += e.speed;
            e.x += e.drift;
            e.wobble += e.wobbleSpeed;
            e.x += Math.sin(e.wobble) * 0.6;
            e.angle += e.rotSpeed;

            if (e.x < e.size / 2) e.x = e.size / 2;
            if (e.x > GAME_W - e.size / 2) e.x = GAME_W - e.size / 2;

            // Colisión con jugador
            if (player.invuln <= 0 && e.y > 0) {
                const py = player.y - player.h / 2;
                if (circleRect(e.x, e.y, e.size * 0.7, player.x - player.w / 2, py, player.w, player.h)) {
                    explode(player.x, player.y, '#ff0000', 30);
                    explode(e.x, e.y, e.color.glow, 16);
                    enemies.splice(i, 1);
                    loseLife();
                    continue;
                }
            }

            // Colisión con balas
            let removed = false;
            for (let j = bullets.length - 1; j >= 0; j--) {
                const b = bullets[j];
                if (circleRect(e.x, e.y, e.size * 0.7, b.x - b.w / 2, b.y, b.w, b.h)) {
                    bullets.splice(j, 1);
                    e.hp--;
                    explode(e.x, e.y, e.color.glow, 6);
                    sfxHit();
                    if (e.hp <= 0) {
                        score += e.scoreValue;
                        explode(e.x, e.y, e.color.glow, 22);
                        sfxExplosion();
                        enemies.splice(i, 1);
                        updateHUD();
                        levelLogic();
                        removed = true;
                    } else {
                        // retroceso leve
                        e.y += 8;
                    }
                    break;
                }
            }
            if (removed) continue;

            // Enemigo sale de pantalla
            if (e.y - e.size > GAME_H) {
                enemies.splice(i, 1);
            }
        }

        // Partículas
        for (let i = particles.length - 1; i >= 0; i--) {
            const p = particles[i];
            p.x += p.vx;
            p.y += p.vy;
            p.vx *= 0.97;
            p.vy *= 0.97;
            p.life--;
            if (p.life <= 0) particles.splice(i, 1);
        }

        // Estrellas
        for (let i = 0; i < stars.length; i++) {
            const s = stars[i];
            s.y += s.speed;
            if (s.y > GAME_H) {
                s.y = -2;
                s.x = Math.random() * GAME_W;
            }
        }

        spawnLogic();
    }

    function loseLife() {
        lives--;
        updateHUD();
        if (lives <= 0) {
            player.alive = false;
            explode(player.x, player.y, '#ff0000', 40);
            gameOver();
        } else {
            player.invuln = 90; // ~1.5s de invulnerabilidad
            player.x = GAME_W / 2;
            player.y = GAME_H - 70;
            showToast('¡Nave impactada! Vidas restantes: ' + lives, true);
        }
    }

    // ---- Render ----
    function drawBackground() {
        ctx.fillStyle = '#000000';
        ctx.fillRect(0, 0, GAME_W, GAME_H);

        // Nebulosa roja tenue
        const grad = ctx.createRadialGradient(GAME_W * 0.8, GAME_H * 0.25, 10, GAME_W * 0.8, GAME_H * 0.25, 260);
        grad.addColorStop(0, 'rgba(255,0,0,0.07)');
        grad.addColorStop(1, 'rgba(255,0,0,0)');
        ctx.fillStyle = grad;
        ctx.fillRect(0, 0, GAME_W, GAME_H);

        // Estrellas
        for (let i = 0; i < stars.length; i++) {
            const s = stars[i];
            const alpha = 0.5 + 0.5 * Math.sin(s.twinkle + frameCount * 0.05);
            ctx.globalAlpha = clamp(alpha, 0.3, 1);
            ctx.fillStyle = '#ffffff';
            ctx.beginPath();
            ctx.arc(s.x, s.y, s.r, 0, Math.PI * 2);
            ctx.fill();
        }
        ctx.globalAlpha = 1;
    }

    function drawShip(ship) {
        const blink = ship.invuln > 0 && Math.floor(frameCount / 6) % 2 === 0;
        if (blink) return;

        ctx.save();
        ctx.translate(ship.x, ship.y);
        ctx.rotate(ship.tilt || 0);

        // Estela del motor
        const flame = 8 + Math.sin(frameCount * 0.6) * 3;
        ctx.fillStyle = '#ff0000';
        ctx.beginPath();
        ctx.moveTo(-6, ship.h * 0.35);
        ctx.lineTo(6, ship.h * 0.35);
        ctx.lineTo(0, ship.h * 0.35 + flame);
        ctx.closePath();
        ctx.fill();
        ctx.fillStyle = 'rgba(255,255,255,0.85)';
        ctx.beginPath();
        ctx.moveTo(-3, ship.h * 0.35);
        ctx.lineTo(3, ship.h * 0.35);
        ctx.lineTo(0, ship.h * 0.35 + flame * 0.5);
        ctx.closePath();
        ctx.fill();

        // Cuerpo de la nave
        ctx.shadowColor = '#ff0000';
        ctx.shadowBlur = 14;
        ctx.fillStyle = '#e60000';
        ctx.beginPath();
        ctx.moveTo(0, -ship.h / 2);
        ctx.lineTo(ship.w / 2, ship.h / 2);
        ctx.lineTo(0, ship.h / 2 - 8);
        ctx.lineTo(-ship.w / 2, ship.h / 2);
        ctx.closePath();
        ctx.fill();
        ctx.shadowBlur = 0;

        // Cabina
        ctx.fillStyle = '#ffffff';
        ctx.beginPath();
        ctx.moveTo(0, -ship.h / 2 + 6);
        ctx.lineTo(6, -ship.h / 2 + 14);
        ctx.lineTo(0, -ship.h / 2 + 20);
        ctx.lineTo(-6, -ship.h / 2 + 14);
        ctx.closePath();
        ctx.fill();

        ctx.restore();
    }

    function drawEnemy(e) {
        ctx.save();
        ctx.translate(e.x, e.y);
        ctx.rotate(e.angle);
        ctx.shadowColor = e.color.glow;
        ctx.shadowBlur = 10;

        if (e.type === 'ship') {
            // Nave enemiga apuntando hacia abajo
            ctx.fillStyle = e.color.body;
            ctx.beginPath();
            ctx.moveTo(0, e.size / 2);
            ctx.lineTo(e.size / 2, -e.size / 2);
            ctx.lineTo(0, -e.size / 2 + 8);
            ctx.lineTo(-e.size / 2, -e.size / 2);
            ctx.closePath();
            ctx.fill();
            ctx.fillStyle = '#ffffff';
            ctx.beginPath();
            ctx.moveTo(0, e.size / 2 - 4);
            ctx.lineTo(5, -e.size / 2 + 10);
            ctx.lineTo(-5, -e.size / 2 + 10);
            ctx.closePath();
            ctx.fill();
        } else {
            // Asteroide con forma irregular
            ctx.fillStyle = e.color.body;
            ctx.strokeStyle = e.color.glow;
            ctx.lineWidth = 2;
            ctx.beginPath();
            e.points.forEach(function (p, idx) {
                if (idx === 0) ctx.moveTo(p.x, p.y);
                else ctx.lineTo(p.x, p.y);
            });
            ctx.closePath();
            ctx.fill();
            ctx.stroke();

            // Barra de vida para enemigos resistentes
            if (e.maxHp > 1) {
                ctx.shadowBlur = 0;
                ctx.fillStyle = '#222';
                ctx.fillRect(-e.size / 2, -e.size / 2 - 8, e.size, 4);
                ctx.fillStyle = '#ff0000';
                ctx.fillRect(-e.size / 2, -e.size / 2 - 8, e.size * (e.hp / e.maxHp), 4);
            }
        }
        ctx.restore();
    }

    function drawBullets() {
        for (let i = 0; i < bullets.length; i++) {
            const b = bullets[i];
            ctx.save();
            ctx.shadowColor = '#ff0000';
            ctx.shadowBlur = 10;
            const grad = ctx.createLinearGradient(b.x, b.y, b.x, b.y + b.h);
            grad.addColorStop(0, '#ffffff');
            grad.addColorStop(1, '#ff0000');
            ctx.fillStyle = grad;
            ctx.fillRect(b.x - b.w / 2, b.y, b.w, b.h);
            ctx.restore();
        }
    }

    function drawParticles() {
        for (let i = 0; i < particles.length; i++) {
            const p = particles[i];
            ctx.globalAlpha = clamp(p.life / p.maxLife, 0, 1);
            ctx.fillStyle = p.color;
            ctx.beginPath();
            ctx.arc(p.x, p.y, p.size, 0, Math.PI * 2);
            ctx.fill();
        }
        ctx.globalAlpha = 1;
    }

    function render() {
        drawBackground();

        // Enemigos
        for (let i = 0; i < enemies.length; i++) drawEnemy(enemies[i]);

        // Jugador
        if (player && player.alive) {
            // Inclinación según movimiento
            let tilt = 0;
            if (keys.ArrowLeft || keys.a) tilt = -0.18;
            else if (keys.ArrowRight || keys.d) tilt = 0.18;
            player.tilt += (tilt - player.tilt) * 0.2;
            drawShip(player);
        }

        drawBullets();
        drawParticles();
    }

    // ---- HUD y récords ----
    function updateHUD() {
        if (scoreEl) scoreEl.textContent = score;
        if (livesEl) livesEl.textContent = lives;
        if (levelEl) levelEl.textContent = level;

        // Animación de pulso en el marcador
        if (scoreEl) {
            scoreEl.classList.remove('pulse');
            void scoreEl.offsetWidth; // reinicia la animación
            scoreEl.classList.add('pulse');
        }
    }

    function updateRecords() {
        if (bestScoreEl) bestScoreEl.textContent = highScore;
        if (lastScoreEl) lastScoreEl.textContent = lastScore;
    }

    // ---- Toast ----
    let toastTimer = null;
    function showToast(msg, isError) {
        let toast = document.querySelector('.game-toast');
        if (!toast) {
            toast = document.createElement('div');
            toast.className = 'game-toast';
            document.body.appendChild(toast);
        }
        toast.textContent = msg;
        toast.classList.toggle('error', !!isError);
        toast.classList.add('show');
        if (toastTimer) clearTimeout(toastTimer);
        toastTimer = setTimeout(function () {
            toast.classList.remove('show');
        }, 2200);
    }

    // ---- Bucle principal ----
    function loop(timestamp) {
        requestAnimationFrame(loop);
        if (!lastTime) lastTime = timestamp;
        const delta = timestamp - lastTime;
        lastTime = timestamp;

        if (state === 'playing') {
            // Actualización en pasos fijos para consistencia
            const steps = Math.min(Math.floor(delta / 16.6), 4);
            for (let i = 0; i < steps; i++) update();
            render();
        } else if (state === 'menu' || state === 'paused' || state === 'gameover') {
            // Mantener estrellas animadas de fondo
            for (let i = 0; i < stars.length; i++) {
                const s = stars[i];
                s.y += s.speed * 0.4;
                if (s.y > GAME_H) { s.y = -2; s.x = Math.random() * GAME_W; }
            }
            drawBackground();
            if (player) drawShip(player);
            if (state === 'menu') {
                // Nave menú volando lentamente
                player.x += Math.sin(frameCount * 0.02) * 0.4;
            }
        }
        frameCount++;
    }

    // ---- Eventos de teclado ----
    document.addEventListener('keydown', function (e) {
        if (e.key === ' ') {
            e.preventDefault();
            ensureAudio();
            if (state === 'playing') {
                fireHeld = true;
            } else if (state === 'menu' || state === 'gameover') {
                startGame();
            }
            return;
        }
        if (e.key === 'p' || e.key === 'P' || e.key === 'Escape') {
            if (state === 'playing' || state === 'paused') {
                e.preventDefault();
                togglePause();
            }
            return;
        }
        if (keys.hasOwnProperty(e.key)) {
            keys[e.key] = true;
            e.preventDefault();
        }
    });

    document.addEventListener('keyup', function (e) {
        if (e.key === ' ') {
            fireHeld = false;
        }
        if (keys.hasOwnProperty(e.key)) {
            keys[e.key] = false;
        }
    });

    // ---- Soporte táctil / ratón ----
    function getGamePos(e) {
        const rect = canvas.getBoundingClientRect();
        const scaleX = GAME_W / rect.width;
        const scaleY = GAME_H / rect.height;
        const clientX = e.touches ? e.touches[0].clientX : e.clientX;
        const clientY = e.touches ? e.touches[0].clientY : e.clientY;
        return {
            x: (clientX - rect.left) * scaleX,
            y: (clientY - rect.top) * scaleY
        };
    }

    let pointerActive = false;
    canvas.addEventListener('mousemove', function (e) {
        if (state !== 'playing') return;
        const pos = getGamePos(e);
        player.x = clamp(pos.x, player.w / 2, GAME_W - player.w / 2);
        player.y = clamp(pos.y, player.h / 2, GAME_H - player.h / 2);
    });

    canvas.addEventListener('mousedown', function (e) {
        e.preventDefault();
        ensureAudio();
        if (state === 'playing') {
            pointerActive = true;
            fireHeld = true;
        } else if (state === 'menu' || state === 'gameover') {
            startGame();
        }
    });

    document.addEventListener('mouseup', function () {
        if (!pointerActive) return;
        pointerActive = false;
        fireHeld = false;
    });

    canvas.addEventListener('touchstart', function (e) {
        e.preventDefault();
        ensureAudio();
        if (state === 'playing') {
            pointerActive = true;
            fireHeld = true;
            const pos = getGamePos(e);
            player.x = clamp(pos.x, player.w / 2, GAME_W - player.w / 2);
            player.y = clamp(pos.y, player.h / 2, GAME_H - player.h / 2);
        } else if (state === 'menu' || state === 'gameover') {
            startGame();
        }
    }, { passive: false });

    canvas.addEventListener('touchmove', function (e) {
        e.preventDefault();
        if (state !== 'playing' || !pointerActive) return;
        const pos = getGamePos(e);
        player.x = clamp(pos.x, player.w / 2, GAME_W - player.w / 2);
        player.y = clamp(pos.y, player.h / 2, GAME_H - player.h / 2);
    }, { passive: false });

    canvas.addEventListener('touchend', function (e) {
        e.preventDefault();
        pointerActive = false;
        fireHeld = false;
    }, { passive: false });

    // ---- Botones de la UI ----
    const btnStart = document.getElementById('btnStart');
    const btnResume = document.getElementById('btnResume');
    const btnRestart = document.getElementById('btnRestart');
    const btnMenu = document.getElementById('btnMenu');
    const btnMenu2 = document.getElementById('btnMenu2');
    const btnPause = document.getElementById('btnPause');

    if (btnStart) btnStart.addEventListener('click', startGame);
    if (btnResume) btnResume.addEventListener('click', togglePause);
    if (btnRestart) {
        btnRestart.addEventListener('click', function () {
            if (state === 'gameover' || state === 'paused' || state === 'menu') {
                startGame();
            }
        });
    }
    if (btnMenu) btnMenu.addEventListener('click', backToMenu);
    if (btnMenu2) btnMenu2.addEventListener('click', backToMenu);
    if (btnPause) btnPause.addEventListener('click', togglePause);

    // ---- Inicialización ----
    function init() {
        createStars();
        player = createPlayer();
        updateRecords();
        updateHUD();
        requestAnimationFrame(loop);
    }

    init();
})();
