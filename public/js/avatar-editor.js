import { createAvatar } from 'https://esm.sh/@dicebear/core@9';
import * as styles from 'https://esm.sh/@dicebear/collection@9';

const getSelectedValue = (name) => {
    const selected = document.querySelector(`input[name="${name}"]:checked`);
    return selected ? selected.value : null;
};

window.generateRandomAvatar = () => {
    const skinColors = ['ffdbb4', 'edb98a', 'd08b5b', 'ae5d29', '7c3c16'];
    const hairColors = ['000000', '2c1b18', '724133', 'b58143', 'd9532f'];
    const hairs = ['short01', 'short02', 'short03', 'short04', 'short05', 'long01', 'long02', 'long03', 'long04', 'long05', 'long06', 'long07', 'long08', 'long09', 'long10'];
    
    const randomSkin = skinColors[Math.floor(Math.random() * skinColors.length)];
    const randomHairColor = hairColors[Math.floor(Math.random() * hairColors.length)];
    const randomHair = hairs[Math.floor(Math.random() * hairs.length)];
    const randomSeed = "user_" + Math.random().toString(36).substring(2, 9);
    
    const seedInput = document.getElementById('current-seed');
    if (seedInput) seedInput.value = randomSeed;
    
    const seedElement = document.getElementById('avatar-data');
    if (seedElement) seedElement.setAttribute('data-seed', randomSeed);
    
    const setRadioChecked = (name, value) => {
        const radio = document.querySelector(`input[name="${name}"][value="${value}"]`);
        if (radio) radio.checked = true;
    };
    
    setRadioChecked('skinColor', randomSkin);
    setRadioChecked('hairColor', randomHairColor);
    setRadioChecked('hair', randomHair);
    
    updateAvatar(); 
};

const updateAvatar = () => {
    const skinColor = getSelectedValue('skinColor');
    const hairColor = getSelectedValue('hairColor');
    const hair = getSelectedValue('hair');
    
    const seedElement = document.getElementById('avatar-data');
    const seed = seedElement ? seedElement.getAttribute('data-seed') : "default_seed";
    
    const options = {
        seed: seed, 
        backgroundColor: ["b6e3f4"],
        hair: hair ? [hair] : undefined,
        skinColor: skinColor ? [skinColor] : undefined,
        hairColor: hairColor ? [hairColor] : undefined,
    };
    
    try {
        const avatar = createAvatar(styles.adventurer, options);
        const container = document.getElementById("avatar-img");
        if (container) {
            container.innerHTML = avatar.toString();
        }
    } catch (error) {
        console.error("Ошибка создания аватара:", error);
    }
};

document.addEventListener('DOMContentLoaded', () => {
    updateAvatar();
    
    document.querySelectorAll('.avatar-option').forEach(radio => {
        radio.addEventListener('change', updateAvatar);
    });
});