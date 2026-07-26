document.addEventListener('DOMContentLoaded', function() {
    const streakSlots = document.querySelectorAll('.streak-slot');
    
    streakSlots.forEach((slot, index) => {
        slot.style.opacity = '0';
        slot.style.transform = 'scale(0.5)';
        
        setTimeout(() => {
            slot.style.transition = 'all 0.4s cubic-bezier(0.68, -0.55, 0.265, 1.55)';
            slot.style.opacity = '1';
            slot.style.transform = 'scale(1)';
        }, index * 100);
    });
    
    const activeSlot = document.querySelector('.streak-slot.active');
    if (activeSlot) {
        setTimeout(() => {
            activeSlot.style.animation = 'slotBounce 1.5s infinite';
        }, 800);
    }
});