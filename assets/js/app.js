const btn=document.getElementById('menuBtn'),nav=document.getElementById('mainNav');
if(btn) btn.addEventListener('click',()=>nav.classList.toggle('open'));