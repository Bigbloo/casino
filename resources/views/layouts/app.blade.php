<!doctype html>
<html lang="fr">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ config('app.name', 'Social Casino') }} - Crash Lab</title>
    {!! PwaKit::head() !!}
    <style>
        :root {
            --risk: 0;
            --bg-cool: #111827;
            --bg-hot: #dc2626;
            --panel: rgba(17, 24, 39, 0.76);
            --text: #f9fafb;
            --muted: #94a3b8;
            --good: #22c55e;
            --danger: #ef4444;
            --accent: #38bdf8;
            --warn: #fb7185;
        }

        * { box-sizing: border-box; }

        body {
            margin: 0;
            min-height: 100vh;
            font-family: Inter, system-ui, -apple-system, Segoe UI, Roboto, sans-serif;
            color: var(--text);
            background: radial-gradient(circle at 25% 25%, #1d4ed8 0%, #0f172a 42%, #111827 100%);
            transition: background 220ms linear;
        }

        main {
            max-width: 1080px;
            margin: 0 auto;
            padding: 1rem;
        }

        .title {
            margin: 0.2rem 0 0.4rem;
            font-size: clamp(1.45rem, 2vw, 2rem);
        }

        .subtitle {
            margin: 0;
            color: var(--muted);
            font-size: 0.92rem;
        }

        .grid {
            display: grid;
            grid-template-columns: 2fr 1fr;
            gap: 1rem;
            margin-top: 1rem;
        }

        .panel {
            background: var(--panel);
            border: 1px solid rgba(148, 163, 184, 0.3);
            border-radius: 14px;
            backdrop-filter: blur(6px);
            padding: 1rem;
        }

        .big {
            font-size: clamp(2.1rem, 7vw, 4rem);
            font-weight: 800;
            margin: 0.3rem 0;
        }

        .bad { color: var(--danger); }
        .good { color: var(--good); }

        .line { display: flex; gap: 0.5rem; align-items: center; flex-wrap: wrap; }

        .controls { display: grid; gap: 0.75rem; margin-top: 1rem; }

        .control-row {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 0.6rem;
        }

        input, button {
            border-radius: 10px;
            border: 1px solid rgba(148, 163, 184, 0.3);
            background: rgba(15, 23, 42, 0.8);
            color: var(--text);
            padding: 0.72rem 0.8rem;
            font-size: 0.95rem;
        }

        button {
            font-weight: 700;
            cursor: pointer;
            transition: transform 120ms ease, opacity 120ms ease;
        }

        button:disabled { opacity: 0.4; cursor: not-allowed; }
        button:active { transform: scale(0.98); }

        .primary { background: linear-gradient(135deg, #0ea5e9, #2563eb); }
        .danger { background: linear-gradient(135deg, #ef4444, #dc2626); }
        .ghost { background: rgba(30, 41, 59, 0.75); }

        .list { margin: 0.4rem 0 0; padding-left: 1rem; color: var(--muted); }
        .kpi { font-size: 0.85rem; color: var(--muted); }

        .ranking-item {
            display: flex;
            justify-content: space-between;
            align-items: center;
            border-bottom: 1px solid rgba(148, 163, 184, 0.2);
            padding: 0.42rem 0;
            font-size: 0.9rem;
        }

        .ranking-item:last-child { border-bottom: 0; }

        .delta-up { color: var(--good); }
        .delta-down { color: var(--warn); }

        .badge {
            display: inline-block;
            border-radius: 999px;
            background: rgba(56, 189, 248, 0.2);
            border: 1px solid rgba(56, 189, 248, 0.5);
            color: #bae6fd;
            padding: 0.18rem 0.55rem;
            font-size: 0.75rem;
        }

        @media (max-width: 820px) {
            .grid { grid-template-columns: 1fr; }
            .control-row { grid-template-columns: 1fr; }
        }
    </style>
</head>
<body>
<main>
    <h1 class="title">Crash Lab — simulation locale</h1>
    <p class="subtitle">Seed individualisé, ranking dynamique, stress visuel et retour haptique adaptatif.</p>

    <div class="grid">
        <section class="panel">
            <div class="line">
                <span class="badge" id="slotBadge">Créneau 15 min: --:--</span>
                <span class="badge" id="seedBadge">Seed: -</span>
            </div>
            <p class="big" id="multiplier">1.00x</p>
            <p class="kpi" id="phaseText">Phase: préparation</p>
            <p class="kpi" id="statusText">Prêt pour une nouvelle manche.</p>

            <div class="controls">
                <div class="control-row">
                    <input id="betInput" type="number" min="1" step="1" value="10" aria-label="Mise">
                    <input id="cashoutInput" type="number" min="1.05" step="0.05" value="1.80" aria-label="Auto cashout">
                </div>
                <div class="control-row">
                    <button class="primary" id="startBtn">Lancer la manche</button>
                    <button class="danger" id="cashoutBtn" disabled>Encaisser maintenant</button>
                </div>
                <div class="control-row">
                    <button class="ghost" id="revengeBtn" disabled>Revanche forcée x2</button>
                    <button class="ghost" id="maxSuggestBtn" disabled>Suivre la mise max suggérée</button>
                </div>
            </div>

            <ul class="list" id="events"></ul>
        </section>

        <aside class="panel">
            <h3 style="margin-top:0">Classement dynamique (15 min)</h3>
            <div id="ranking"></div>
            <h3>Session</h3>
            <p class="kpi" id="balance">Solde: 1000 crédits</p>
            <p class="kpi" id="streak">Pertes consécutives: 0</p>
            <p class="kpi" id="suggestion">Suggestion: aucune</p>
        </aside>
    </div>
</main>

<script>
(() => {
    const state = {
        balance: 1000,
        currentBet: 10,
        multiplier: 1,
        crashPoint: 1.6,
        running: false,
        autoCashout: 1.8,
        lostStreak: 0,
        revengeAvailable: false,
        revengeUsedRound: false,
        suggestedMaxBet: null,
        seed: "",
        frameId: null,
        startedAt: 0,
        slotStats: {},
        currentSlot: "",
        peers: [
            { name: "Atlas", perf: 0 },
            { name: "Nova", perf: 0 },
            { name: "Vega", perf: 0 },
            { name: "Orion", perf: 0 },
            { name: "Luna", perf: 0 }
        ],
        lastHapticLevel: 0,
        psychoAudioCtx: null,
        psychoOscillator: null,
        psychoGain: null
    };

    const el = {
        multiplier: document.getElementById('multiplier'),
        phaseText: document.getElementById('phaseText'),
        statusText: document.getElementById('statusText'),
        betInput: document.getElementById('betInput'),
        cashoutInput: document.getElementById('cashoutInput'),
        startBtn: document.getElementById('startBtn'),
        cashoutBtn: document.getElementById('cashoutBtn'),
        revengeBtn: document.getElementById('revengeBtn'),
        maxSuggestBtn: document.getElementById('maxSuggestBtn'),
        events: document.getElementById('events'),
        ranking: document.getElementById('ranking'),
        balance: document.getElementById('balance'),
        streak: document.getElementById('streak'),
        suggestion: document.getElementById('suggestion'),
        seedBadge: document.getElementById('seedBadge'),
        slotBadge: document.getElementById('slotBadge')
    };

    function addEvent(message) {
        const li = document.createElement('li');
        li.textContent = `[${new Date().toLocaleTimeString()}] ${message}`;
        el.events.prepend(li);
        while (el.events.children.length > 8) {
            el.events.removeChild(el.events.lastChild);
        }
    }

    function getSlotLabel(date = new Date()) {
        const minute = date.getMinutes();
        const startMin = minute < 15 ? 0 : minute < 30 ? 15 : minute < 45 ? 30 : 45;
        const endMin = (startMin + 15) % 60;
        const hh = String(date.getHours()).padStart(2, '0');
        const nextH = String((date.getHours() + (startMin === 45 ? 1 : 0)) % 24).padStart(2, '0');
        return `${hh}:${String(startMin).padStart(2, '0')}-${nextH}:${String(endMin).padStart(2, '0')}`;
    }

    function createSessionSeed() {
        const entropy = `${Date.now()}-${Math.random()}-${navigator.userAgent}-${screen.width}x${screen.height}`;
        let hash = 2166136261;
        for (let i = 0; i < entropy.length; i++) {
            hash ^= entropy.charCodeAt(i);
            hash += (hash << 1) + (hash << 4) + (hash << 7) + (hash << 8) + (hash << 24);
        }
        return `U-${(hash >>> 0).toString(16)}-${Math.floor(Math.random() * 1e6).toString().padStart(6, '0')}`;
    }

    function seededRandom(seedStr) {
        let h = 1779033703 ^ seedStr.length;
        for (let i = 0; i < seedStr.length; i++) {
            h = Math.imul(h ^ seedStr.charCodeAt(i), 3432918353);
            h = (h << 13) | (h >>> 19);
        }
        return function() {
            h = Math.imul(h ^ (h >>> 16), 2246822507);
            h = Math.imul(h ^ (h >>> 13), 3266489909);
            h ^= h >>> 16;
            return (h >>> 0) / 4294967296;
        };
    }

    function computeCrashPoint() {
        const random = seededRandom(`${state.seed}-${Date.now()}-${state.lostStreak}`);
        const base = random();
        const sessionBias = seededRandom(state.seed)();
        const tempered = Math.max(0.0001, 1 - (base * 0.92 + sessionBias * 0.08));
        const crash = Math.min(12, Math.max(1.08, 0.99 / tempered));
        return Number(crash.toFixed(2));
    }

    function progressiveAsyncCurve(elapsedMs) {
        const t = elapsedMs / 1000;
        if (t < 2.8) {
            return 1 + t * 0.09 + Math.pow(t, 1.15) * 0.05;
        }
        if (t < 5.5) {
            const local = t - 2.8;
            return 1.55 + local * 0.17 + Math.pow(local, 1.5) * 0.04;
        }
        const late = t - 5.5;
        return 2.4 + late * 0.48 + Math.pow(late, 2.1) * 0.13;
    }

    function updateStressVisuals(risk) {
        const heat = Math.min(1, Math.max(0, risk));
        const cool = [17, 24, 39];
        const hot = [220, 38, 38];
        const mix = cool.map((v, idx) => Math.round(v + (hot[idx] - v) * heat));
        document.body.style.background = `radial-gradient(circle at 25% 25%, rgb(${30 + Math.round(80 * (1 - heat))}, ${78 - Math.round(30 * heat)}, ${216 - Math.round(120 * heat)}) 0%, rgb(${mix[0]}, ${mix[1]}, ${mix[2]}) 55%, #0b1020 100%)`;
    }

    function ensureSubliminalAudio() {
        if (state.psychoAudioCtx) return;
        const AudioCtx = window.AudioContext || window.webkitAudioContext;
        if (!AudioCtx) return;
        state.psychoAudioCtx = new AudioCtx();
        const oscillator = state.psychoAudioCtx.createOscillator();
        const gain = state.psychoAudioCtx.createGain();
        oscillator.type = 'sine';
        oscillator.frequency.value = 38;
        gain.gain.value = 0.0001;
        oscillator.connect(gain);
        gain.connect(state.psychoAudioCtx.destination);
        oscillator.start();
        state.psychoOscillator = oscillator;
        state.psychoGain = gain;
    }

    function updateSubliminalAudio(risk) {
        if (!state.psychoAudioCtx || !state.psychoGain || !state.psychoOscillator) return;
        const now = state.psychoAudioCtx.currentTime;
        const frequency = 36 + risk * 28;
        const amplitude = 0.0001 + risk * 0.018;
        state.psychoOscillator.frequency.setTargetAtTime(frequency, now, 0.06);
        state.psychoGain.gain.setTargetAtTime(amplitude, now, 0.08);
    }

    function updateAdaptiveHaptic(risk) {
        if (!('vibrate' in navigator)) return;
        const level = Math.min(4, Math.max(0, Math.floor(risk * 5)));
        if (level === state.lastHapticLevel) return;
        state.lastHapticLevel = level;
        if (level <= 0) return;
        const pattern = [10 + level * 6, 12 - Math.min(8, level * 2), 12 + level * 8];
        navigator.vibrate(pattern);
    }

    function updateRanking(deltaPerformance = 0) {
        const slot = getSlotLabel();
        state.currentSlot = slot;
        el.slotBadge.textContent = `Créneau 15 min: ${slot}`;

        if (!state.slotStats[slot]) {
            state.slotStats[slot] = { user: 0, rounds: 0 };
        }

        state.slotStats[slot].user += deltaPerformance;
        state.slotStats[slot].rounds += deltaPerformance !== 0 ? 1 : 0;

        const randomPeer = seededRandom(`${slot}-${Date.now()}`);
        state.peers = state.peers.map(peer => ({
            ...peer,
            perf: Number((peer.perf + (randomPeer() - 0.5) * 8).toFixed(1))
        }));

        const userPerf = Number(state.slotStats[slot].user.toFixed(1));
        const board = [
            ...state.peers,
            { name: 'Vous', perf: userPerf, user: true }
        ].sort((a, b) => b.perf - a.perf);

        el.ranking.innerHTML = board.map((item, idx) => {
            const prev = board[idx - 1]?.perf ?? item.perf;
            const gap = Number((item.perf - prev).toFixed(1));
            const gapLabel = idx === 0 ? 'leader' : `${gap > 0 ? '+' : ''}${gap}`;
            return `<div class="ranking-item">
                <span>${idx + 1}. ${item.name}</span>
                <span class="${gap >= 0 ? 'delta-up' : 'delta-down'}">${item.perf.toFixed(1)} (${gapLabel})</span>
            </div>`;
        }).join('');
    }

    function updateStatsUi() {
        el.balance.textContent = `Solde: ${state.balance.toFixed(0)} crédits`;
        el.streak.textContent = `Pertes consécutives: ${state.lostStreak}`;
        el.suggestion.textContent = state.suggestedMaxBet
            ? `Suggestion: ${state.suggestedMaxBet.toFixed(0)} crédits (mise totale potentielle)`
            : 'Suggestion: aucune';
        el.revengeBtn.disabled = !(state.revengeAvailable && !state.running);
        el.maxSuggestBtn.disabled = !state.suggestedMaxBet || state.running;
    }

    function setIdle() {
        state.running = false;
        el.startBtn.disabled = false;
        el.cashoutBtn.disabled = true;
        el.phaseText.textContent = 'Phase: préparation';
        state.lastHapticLevel = 0;
        if ('vibrate' in navigator) navigator.vibrate(0);
        updateStressVisuals(0);
        updateSubliminalAudio(0);
    }

    function applyLoss() {
        state.lostStreak += 1;
        state.revengeAvailable = true;
        state.revengeUsedRound = false;
        if (state.lostStreak >= 3) {
            state.suggestedMaxBet = Math.min(state.balance, Math.max(state.currentBet * 3, state.balance * 0.65));
            addEvent(`Proposition auto: mise max ${state.suggestedMaxBet.toFixed(0)} crédits.`);
        }
        updateRanking(-state.currentBet / 10);
        addEvent(`Crash à ${state.crashPoint.toFixed(2)}x. Mise perdue.`);
    }

    function applyWin(payoutMultiplier) {
        const gain = state.currentBet * payoutMultiplier;
        state.balance += gain;
        state.lostStreak = 0;
        state.revengeAvailable = false;
        state.suggestedMaxBet = null;
        updateRanking(gain / 20);
        addEvent(`Encaissement à ${payoutMultiplier.toFixed(2)}x (+${gain.toFixed(0)}).`);
    }

    function cashoutNow(manual = false) {
        if (!state.running) return;
        cancelAnimationFrame(state.frameId);
        state.running = false;
        const payout = Number(state.multiplier.toFixed(2));
        el.statusText.textContent = manual
            ? `Sortie manuelle validée à ${payout}x.`
            : `Auto cashout déclenché à ${payout}x.`;
        applyWin(payout);
        setIdle();
        updateStatsUi();
    }

    function tick() {
        if (!state.running) return;
        const elapsed = performance.now() - state.startedAt;
        state.multiplier = progressiveAsyncCurve(elapsed);

        const risk = Math.min(1, state.multiplier / Math.max(1.15, state.crashPoint));
        updateStressVisuals(risk);
        updateAdaptiveHaptic(risk);
        updateSubliminalAudio(risk);

        el.multiplier.textContent = `${state.multiplier.toFixed(2)}x`;

        if (state.multiplier >= state.autoCashout) {
            cashoutNow(false);
            return;
        }

        if (state.multiplier >= state.crashPoint) {
            state.running = false;
            el.multiplier.classList.add('bad');
            el.statusText.textContent = `CRASH à ${state.crashPoint.toFixed(2)}x`;
            applyLoss();
            setIdle();
            updateStatsUi();
            return;
        }

        const phase = state.multiplier < 1.9 ? 'ralentissement' : state.multiplier < 2.8 ? 'plateau tendu' : 'accélération brutale';
        el.phaseText.textContent = `Phase: ${phase}`;
        state.frameId = requestAnimationFrame(tick);
    }

    function startRound(forceBet = null) {
        if (state.running) return;
        const bet = Number(forceBet ?? el.betInput.value);
        const autoCashout = Number(el.cashoutInput.value);

        if (!Number.isFinite(bet) || bet <= 0) {
            addEvent('Mise invalide.');
            return;
        }

        if (bet > state.balance) {
            addEvent('Solde insuffisant pour cette mise.');
            return;
        }

        if (!Number.isFinite(autoCashout) || autoCashout < 1.05) {
            addEvent('Seuil d\'encaissement invalide.');
            return;
        }

        ensureSubliminalAudio();
        if (state.psychoAudioCtx?.state === 'suspended') {
            state.psychoAudioCtx.resume();
        }

        state.currentBet = bet;
        state.autoCashout = autoCashout;
        state.balance -= bet;
        state.multiplier = 1;
        state.crashPoint = computeCrashPoint();
        state.running = true;
        state.startedAt = performance.now();
        state.revengeAvailable = false;
        state.suggestedMaxBet = state.lostStreak >= 3 ? state.suggestedMaxBet : null;

        el.multiplier.classList.remove('bad');
        el.multiplier.classList.add('good');
        el.multiplier.textContent = '1.00x';
        el.statusText.textContent = `Manche en cours. Crash seedé à partir de ${state.seed.slice(0, 10)}…`;

        el.startBtn.disabled = true;
        el.cashoutBtn.disabled = false;

        updateStatsUi();
        addEvent(`Nouvelle manche: mise ${bet.toFixed(0)}, auto ${autoCashout.toFixed(2)}x.`);
        tick();
    }

    el.startBtn.addEventListener('click', () => startRound());
    el.cashoutBtn.addEventListener('click', () => cashoutNow(true));

    el.revengeBtn.addEventListener('click', () => {
        if (!state.revengeAvailable || state.revengeUsedRound || state.running) return;
        const revengeBet = Math.min(state.balance, state.currentBet * 2);
        if (revengeBet <= 0) return;
        state.revengeUsedRound = true;
        addEvent(`Revanche forcée activée: mise doublée à ${revengeBet.toFixed(0)}.`);
        startRound(revengeBet);
    });

    el.maxSuggestBtn.addEventListener('click', () => {
        if (!state.suggestedMaxBet || state.running) return;
        el.betInput.value = Math.max(1, Math.floor(state.suggestedMaxBet));
        addEvent(`Mise suggérée appliquée: ${el.betInput.value} crédits.`);
    });

    state.seed = createSessionSeed();
    el.seedBadge.textContent = `Seed: ${state.seed}`;
    updateRanking(0);
    updateStatsUi();
    addEvent('Session initialisée avec seed individualisé.');
})();
</script>

</body>
</html>
