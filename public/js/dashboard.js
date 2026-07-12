import { createAvatar } from 'https://esm.sh/@dicebear/core@9';
import * as styles from 'https://esm.sh/@dicebear/collection@9';

function initTimer() {
    let timeLeft = 45 * 60;
    let timerInterval = null;
    let isRunning = false;
    
    const timerDisplay = document.getElementById('timer');
    const startButton = document.getElementById('startTimer');
    const timerCircle = document.querySelector('.timer-circle .fill');
    const totalTime = 45 * 60;
    const circumference = 2 * Math.PI * 65;
    
    function updateTimer() {
        const minutes = Math.floor(timeLeft / 60);
        const seconds = timeLeft % 60;
        if (timerDisplay) {
            timerDisplay.textContent = `${minutes.toString().padStart(2, '0')}:${seconds.toString().padStart(2, '0')}`;
            const offset = circumference * (1 - timeLeft / totalTime);
            timerCircle.style.strokeDashoffset = offset;
        }
    }
    
    if (startButton) {
        startButton.addEventListener('click', () => {
            if (isRunning) {
                clearInterval(timerInterval);
                isRunning = false;
                startButton.textContent = 'Начать';
            } else {
                isRunning = true;
                startButton.textContent = 'Пауза';
                timerInterval = setInterval(() => {
                    if (timeLeft > 0) {
                        timeLeft--;
                        updateTimer();
                    } else {
                        clearInterval(timerInterval);
                        isRunning = false;
                        startButton.textContent = 'Начать';
                        alert('Время вышло!');
                    }
                }, 1000);
            }
        });
    }
}

function displayAvatar() {
    const config = document.getElementById('avatar-config');
    if (!config) return;

    const options = {
        seed: config.dataset.seed,
        backgroundColor: ["b6e3f4"],
        skinColor: [config.dataset.skin],
        hairColor: [config.dataset.hairColor],
        hair: [config.dataset.hairStyle]
    };
    
    try {
        const avatar = createAvatar(styles.adventurer, options);
        const container = document.getElementById("user-avatar");
        if (container) {
            container.innerHTML = avatar.toString();
        }
    } catch (error) {
        console.error("Ошибка генерации аватара:", error);
    }
}

document.addEventListener('DOMContentLoaded', () => {
    initTimer();
    displayAvatar();
});