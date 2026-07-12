import { createAvatar } from 'https://esm.sh/@dicebear/core@9';
import * as styles from 'https://esm.sh/@dicebear/collection@9';

function getSelectedValue(name) {
    var selected = document.querySelector('input[name="' + name + '"]:checked');
    return selected ? selected.value : null;
}

function generateRandomAvatar() {
    var skinColors = ['ffdbb4', 'edb98a', 'd08b5b', 'ae5d29', '7c3c16'];
    var hairColors = ['000000', '2c1b18', '724133', 'b58143', 'd9532f'];
    var hairs = ['short01', 'short02', 'short03', 'short04', 'short05', 'long01', 'long02', 'long03', 'long04', 'long05', 'long06', 'long07', 'long08', 'long09', 'long10'];
    
    var randomSkin = skinColors[Math.floor(Math.random() * skinColors.length)];
    var randomHairColor = hairColors[Math.floor(Math.random() * hairColors.length)];
    var randomHair = hairs[Math.floor(Math.random() * hairs.length)];
    var randomSeed = "user_" + Math.random().toString(36).substr(2, 9);
    
    var seedInput = document.getElementById('current-seed');
    if (seedInput) seedInput.value = randomSeed;
    
    var seedElement = document.getElementById('avatar-data');
    if (seedElement) seedElement.setAttribute('data-seed', randomSeed);
    
    updateAvatar(); 
}

function updateAvatar() {
    var skinColor = getSelectedValue('skinColor');
    var hairColor = getSelectedValue('hairColor');
    var hair = getSelectedValue('hair');
    
    var seedElement = document.getElementById('avatar-data');
    var seed = seedElement ? seedElement.getAttribute('data-seed') : "";
    
    var options = {
        seed: seed, 
        backgroundColor: ["b6e3f4"],
        skinColor: skinColor ? [skinColor] : undefined,
        hairColor: hairColor ? [hairColor] : undefined,
        hair: hair ? [hair] : undefined
    };
    
    console.log("Updating avatar with:", options);
    
    try {
        const avatar = createAvatar(styles.adventurer, options);
        const svg = avatar.toString();
        
        var container = document.getElementById("avatar-img");
        if (container) {
            container.innerHTML = svg;
        }
    } catch (error) {
        console.error("Error creating avatar:", error);
    }
}

document.addEventListener('DOMContentLoaded', function() {
    console.log("Page loaded, initializing avatar...");
    updateAvatar();
    
    var randomBtn = document.querySelector('.btn-random');
    if (randomBtn) {
        randomBtn.addEventListener('click', generateRandomAvatar);
    }
    
    var radios = document.querySelectorAll('.avatar-option');
    radios.forEach(function(radio) {
        radio.addEventListener('change', updateAvatar);
    });
});