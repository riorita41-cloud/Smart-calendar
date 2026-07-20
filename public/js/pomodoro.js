document.addEventListener('DOMContentLoaded', function() {
    const display = document.getElementById('timer-display');
    const startBtn = document.getElementById('start-btn');
    const pauseBtn = document.getElementById('pause-btn');
    const resetBtn = document.getElementById('reset-btn');
    const messageBox = document.getElementById('timer-message');
    const sessionsCountEl = document.getElementById('sessions-count');
    
    const levelTextEl = document.querySelector('.level-text');
    const xpTextEl = document.querySelector('.xp-text');
    const xpFillEl = document.querySelector('.xp-fill');
    const streakTextEl = document.querySelector('.streak-text');
    
    const timerModeEl = document.getElementById('timer-mode');
    const cycleDotsEl = document.getElementById('cycle-dots');
    const cycleInfoEl = document.getElementById('cycle-info');
    const timerCardEl = document.querySelector('.timer-card');
    
    const WORK_DURATION = 25 * 60;       
    const SHORT_BREAK = 5 * 60;          
    const LONG_BREAK = 15 * 60;          
    const CYCLES_BEFORE_LONG_BREAK = 4;  
    
    const TIMER_STORAGE_KEY = 'pomodoro_timer_state';

    let timeLeft = WORK_DURATION;
    let timerId = null;
    let isRunning = false;
    let currentMode = 'work';      
    let currentCycle = 1;          

    const SVG_ICONS = {
        work: `<svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
            <circle cx="12" cy="12" r="10"></circle>
            <polyline points="12 6 12 12 16 14"></polyline>
        </svg>`,
        shortBreak: `<svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
            <path d="M17 8h1a4 4 0 1 1 0 8h-1"></path>
            <path d="M3 8h14v9a4 4 0 0 1-4 4H7a4 4 0 0 1-4-4Z"></path>
            <line x1="6" x2="6" y1="2" y2="4"></line>
            <line x1="10" x2="10" y1="2" y2="4"></line>
            <line x1="14" x2="14" y1="2" y2="4"></line>
        </svg>`,
        longBreak: `<svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
            <circle cx="12" cy="12" r="4"></circle>
            <path d="M12 2v2"></path>
            <path d="M12 20v2"></path>
            <path d="m4.93 4.93 1.41 1.41"></path>
            <path d="m17.66 17.66 1.41 1.41"></path>
            <path d="M2 12h2"></path>
            <path d="M20 12h2"></path>
            <path d="m6.34 17.66-1.41 1.41"></path>
            <path d="m19.07 4.93-1.41 1.41"></path>
        </svg>`
    };

    function saveTimerState() {
        const endTime = Date.now() + (timeLeft * 1000);
        localStorage.setItem(TIMER_STORAGE_KEY, JSON.stringify({
            endTime: endTime,
            isRunning: isRunning,
            mode: currentMode,
            cycle: currentCycle
        }));
    }

    function clearTimerState() {
        localStorage.removeItem(TIMER_STORAGE_KEY);
    }

    function checkRestoredTimer() {
        const savedData = localStorage.getItem(TIMER_STORAGE_KEY);
        if (!savedData) return false;
        
        const data = JSON.parse(savedData);
        const now = Date.now();
        const remainingMs = data.endTime - now;
        timeLeft = Math.ceil(remainingMs / 1000);
        currentMode = data.mode || 'work';
        currentCycle = data.cycle || 1;

        if (timeLeft > 0) {
            isRunning = data.isRunning;
            updateModeUI();
            updateDisplay();
            
            if (isRunning) {
                startBtn.style.display = 'none';
                pauseBtn.style.display = 'inline-block';
                timerId = setInterval(tick, 1000);
            } else {
                startBtn.style.display = 'inline-block';
                pauseBtn.style.display = 'none';
                startBtn.textContent = 'Продолжить';
            }
            return true;
        } else {
            clearTimerState();
            timeLeft = 0;
            updateDisplay();
            handlePhaseEnd();
            return true;
        }
    }

    function updateModeUI() {
        if (timerModeEl) {
            timerModeEl.classList.remove('work', 'short-break', 'long-break');
            if (currentMode === 'work') {
                timerModeEl.classList.add('work');
                timerModeEl.innerHTML = `${SVG_ICONS.work}<span class="mode-text">Режим: Работа</span>`;
            } else if (currentMode === 'shortBreak') {
                timerModeEl.classList.add('short-break');
                timerModeEl.innerHTML = `${SVG_ICONS.shortBreak}<span class="mode-text">Короткий отдых</span>`;
            } else if (currentMode === 'longBreak') {
                timerModeEl.classList.add('long-break');
                timerModeEl.innerHTML = `${SVG_ICONS.longBreak}<span class="mode-text">Длинный отдых</span>`;
            }
        }

        if (timerCardEl) {
            timerCardEl.classList.remove('break-mode', 'long-break-mode');
            if (currentMode === 'shortBreak') timerCardEl.classList.add('break-mode');
            if (currentMode === 'longBreak') timerCardEl.classList.add('long-break-mode');
        }

        if (cycleDotsEl) {
            const dots = cycleDotsEl.querySelectorAll('.cycle-dot');
            dots.forEach((dot, index) => {
                dot.classList.remove('active', 'completed');
                if (index + 1 < currentCycle) {
                    dot.classList.add('completed');
                } else if (index + 1 === currentCycle && currentMode === 'work') {
                    dot.classList.add('active');
                } else if (index + 1 === currentCycle && currentMode !== 'work') {
                    dot.classList.add('completed');
                }
            });
        }

        if (cycleInfoEl) {
            if (currentMode === 'work') {
                cycleInfoEl.textContent = `Сессия ${currentCycle} из ${CYCLES_BEFORE_LONG_BREAK}`;
            } else if (currentMode === 'shortBreak') {
                cycleInfoEl.textContent = `Отдых перед сессией ${currentCycle + 1}`;
            } else if (currentMode === 'longBreak') {
                cycleInfoEl.textContent = `Длинный перерыв после полного цикла!`;
            }
        }
    }

    function switchToNextMode() {
        if (currentMode === 'work') {
            if (currentCycle >= CYCLES_BEFORE_LONG_BREAK) {
                currentMode = 'longBreak';
                timeLeft = LONG_BREAK;
            } else {
                currentMode = 'shortBreak';
                timeLeft = SHORT_BREAK;
            }
        } else if (currentMode === 'shortBreak') {
            currentCycle++;
            currentMode = 'work';
            timeLeft = WORK_DURATION;
        } else if (currentMode === 'longBreak') {
            currentCycle = 1;
            currentMode = 'work';
            timeLeft = WORK_DURATION;
        }
        updateModeUI();
    }

    function handlePhaseEnd() {
        if (currentMode === 'work') {
            completeWorkSession();
        } else {
            switchToNextMode();
            updateDisplay();
            showBreakEndMessage();
            setTimeout(() => {
                if (!isRunning) startTimer();
            }, 3000);
        }
    }

    function showBreakEndMessage() {
        if (currentMode === 'work') {
            messageBox.innerHTML = `${SVG_ICONS.work} <b>Время работать!</b><br>Сессия ${currentCycle} из ${CYCLES_BEFORE_LONG_BREAK}`;
            messageBox.className = 'timer-message info';
        } else if (currentMode === 'longBreak') {
            messageBox.innerHTML = `${SVG_ICONS.longBreak} <b>Заслуженный длинный отдых!</b><br>Вы прошли полный цикл!`;
            messageBox.className = 'timer-message success level-up';
        } else {
            messageBox.innerHTML = `${SVG_ICONS.shortBreak} <b>Время отдохнуть!</b><br>Сделайте пару глотков воды и потянитесь.`;
            messageBox.className = 'timer-message info';
        }
        messageBox.style.display = 'block';
    }

    function updateDisplay() {
        const minutes = Math.max(0, Math.floor(timeLeft / 60));
        const seconds = Math.max(0, timeLeft % 60);
        display.textContent = `${minutes.toString().padStart(2, '0')}:${seconds.toString().padStart(2, '0')}`;
    }

    function tick() {
        if (timeLeft > 0) {
            timeLeft--;
            updateDisplay();
            saveTimerState(); 
        } else {
            clearTimerState();
            handlePhaseEnd();
        }
    }

    function startTimer() {
        if (isRunning) return;
        isRunning = true;
        startBtn.style.display = 'none';
        pauseBtn.style.display = 'inline-block';
        messageBox.style.display = 'none';

        saveTimerState(); 
        timerId = setInterval(tick, 1000); 
    }

    function pauseTimer() {
        isRunning = false;
        clearInterval(timerId);
        startBtn.style.display = 'inline-block';
        pauseBtn.style.display = 'none';
        startBtn.textContent = 'Продолжить';
        
        saveTimerState(); 
    }

    function resetTimer() {
        pauseTimer();
        currentMode = 'work';
        currentCycle = 1;
        timeLeft = WORK_DURATION;
        updateModeUI();
        updateDisplay();
        startBtn.textContent = 'Старт';
        messageBox.style.display = 'none';
        clearTimerState(); 
    }

    function completeWorkSession() {
        pauseTimer();
        
        messageBox.textContent = 'Сохраняем прогресс и начисляем XP...';
        messageBox.className = 'timer-message info';
        messageBox.style.display = 'block';

        const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');

        fetch('/api/pomodoro/complete', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-Requested-With': 'XMLHttpRequest',
                'X-CSRF-TOKEN': csrfToken
            },
            body: JSON.stringify({
                isLongBreakBonus: currentCycle >= CYCLES_BEFORE_LONG_BREAK
            })
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                if (data.xp.leveledUp) {
                    messageBox.innerHTML = ` <b>Поздравляем с повышением!</b><br>Вам присвоен титул «${data.xp.title}» (уровень ${data.xp.newLevel}).`;
                    messageBox.className = 'timer-message success level-up';
                } else {
                    messageBox.textContent = `💪 Ещё один шаг к цели! +${data.xp.xpAdded} XP`;
                    messageBox.className = 'timer-message success';
                }
                messageBox.style.display = 'block';
                
                if (sessionsCountEl) {
                    const currentCount = parseInt(sessionsCountEl.textContent || 0, 10);
                    sessionsCountEl.textContent = currentCount + 1;
                }

                if (levelTextEl) {
                    levelTextEl.textContent = `Уровень ${data.xp.newLevel} — ${data.xp.title}`;
                }
                if (xpTextEl) {
                    const nextLevelXp = Math.pow(data.xp.newLevel, 2) * 100;
                    xpTextEl.textContent = `${data.xp.xp} / ${nextLevelXp} XP`;
                }
                if (xpFillEl) {
                    const prevLevelXp = Math.pow(data.xp.newLevel - 1, 2) * 100;
                    const nextLevelXp = Math.pow(data.xp.newLevel, 2) * 100;
                    const levelRange = nextLevelXp - prevLevelXp;
                    const currentLevelXp = data.xp.xp - prevLevelXp;
                    const xpPercent = levelRange > 0 ? (currentLevelXp / levelRange * 100) : 100;
                    xpFillEl.style.width = `${xpPercent}%`;
                }
                if (streakTextEl && data.xp.streakDays !== undefined) {
                    streakTextEl.textContent = ` Серия дней: ${data.xp.streakDays}`;
                }

                switchToNextMode();
                updateDisplay();
                
                setTimeout(() => {
                    startTimer();
                }, 3000);

            } else {
                messageBox.textContent = 'Ошибка сохранения. Попробуйте еще раз.';
                messageBox.className = 'timer-message error';
            }
        })
        .catch(error => {
            console.error('Error:', error);
            messageBox.textContent = 'Ошибка сети. Проверьте подключение.';
            messageBox.className = 'timer-message error';
        });
    }

    if (startBtn) startBtn.addEventListener('click', startTimer);
    if (pauseBtn) pauseBtn.addEventListener('click', pauseTimer);
    if (resetBtn) resetBtn.addEventListener('click', resetTimer);

    if (!checkRestoredTimer()) {
        updateModeUI();
        updateDisplay();
    }
});