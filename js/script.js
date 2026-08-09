// js/script.js
document.addEventListener('DOMContentLoaded', function() {
    
    // ====== TYPING EFFECT ======
    const roles = [
        'Microfinance Professional',
        'Branch Manager',
        'Internal Auditor',
        'Officer'
    ];
    
    let roleIndex = 0;
    let charIndex = 0;
    let isDeleting = false;
    const typedText = document.getElementById('typed-text');
    
    if (typedText) {
        function typeEffect() {
            const currentRole = roles[roleIndex];
            
            if (isDeleting) {
                typedText.textContent = currentRole.substring(0, charIndex - 1);
                charIndex--;
            } else {
                typedText.textContent = currentRole.substring(0, charIndex + 1);
                charIndex++;
            }
            
            let speed = isDeleting ? 50 : 100;
            
            if (!isDeleting && charIndex === currentRole.length) {
                speed = 2000;
                isDeleting = true;
            } else if (isDeleting && charIndex === 0) {
                isDeleting = false;
                roleIndex = (roleIndex + 1) % roles.length;
                speed = 500;
            }
            
            setTimeout(typeEffect, speed);
        }
        typeEffect();
    }
    
    // ====== NAVIGATION HAMBURGER ======
    const hamburger = document.getElementById('hamburger');
    const navMenu = document.getElementById('navMenu');
    
    if (hamburger && navMenu) {
        hamburger.addEventListener('click', function() {
            this.classList.toggle('active');
            navMenu.classList.toggle('active');
        });
    }
    
    // ====== ACTIVE NAV LINK ======
    const sections = document.querySelectorAll('section[id]');
    const navLinks = document.querySelectorAll('.nav-menu a');
    
    window.addEventListener('scroll', function() {
        let current = '';
        sections.forEach(section => {
            const sectionTop = section.offsetTop - 100;
            if (window.scrollY >= sectionTop) {
                current = section.getAttribute('id');
            }
        });
        
        navLinks.forEach(link => {
            link.classList.remove('active');
            if (link.getAttribute('href') === '#' + current) {
                link.classList.add('active');
            }
        });
    });
    
    // ====== BACK TO TOP ======
    const backToTop = document.getElementById('backToTop');
    
    window.addEventListener('scroll', function() {
        if (window.scrollY > 500) {
            backToTop.classList.add('visible');
        } else {
            backToTop.classList.remove('visible');
        }
    });
    
    // ====== SKILL ANIMATION ======
    const skillItems = document.querySelectorAll('.skill-item');
    
    const observer = new IntersectionObserver((entries) => {
        entries.forEach(entry => {
            if (entry.isIntersecting) {
                const progress = entry.target.querySelector('.skill-progress');
                if (progress) {
                    const width = progress.style.width;
                    progress.style.width = '0';
                    setTimeout(() => {
                        progress.style.width = width;
                    }, 300);
                }
            }
        });
    }, { threshold: 0.3 });
    
    skillItems.forEach(item => observer.observe(item));
});