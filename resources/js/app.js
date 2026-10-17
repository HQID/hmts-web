import { Html5Qrcode } from 'html5-qrcode';

window.Html5Qrcode = Html5Qrcode;

// ==========================================================================
// High-Performance Viewport Motion Observer (Zero Dependencies, 60fps)
// ==========================================================================
function initMotionReveals() {
    // Fallback for environments without IntersectionObserver
    if (!('IntersectionObserver' in window)) {
        document.querySelectorAll('.motion-reveal, .motion-scale').forEach(el => {
            el.classList.add('is-revealed');
        });
        return;
    }

    const observer = new IntersectionObserver((entries, obs) => {
        entries.forEach(entry => {
            if (entry.isIntersecting) {
                entry.target.classList.add('is-revealed');
                // Once revealed, disconnect observer from this element to free memory
                obs.unobserve(entry.target);
            }
        });
    }, {
        root: null,
        rootMargin: '0px 0px -40px 0px',
        threshold: 0.08
    });

    document.querySelectorAll('.motion-reveal:not(.is-revealed), .motion-scale:not(.is-revealed)').forEach(el => {
        observer.observe(el);
    });
}

// Initial Run on DOM Ready
if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', initMotionReveals);
} else {
    initMotionReveals();
}

// Re-evaluate on Livewire navigation or DOM updates
document.addEventListener('livewire:navigated', initMotionReveals);
document.addEventListener('livewire:initialized', () => {
    if (window.Livewire) {
        window.Livewire.hook('morph.updated', () => {
            initMotionReveals();
        });
    }
});

