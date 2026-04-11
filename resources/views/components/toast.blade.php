<div id="sf-toast-container"></div>
<script>
    if (typeof window.Store !== 'undefined') {
        window.Store.on('toast', (data) => {
            const el = document.createElement('div');
            el.className = 'sf-toast sf-toast-' + (data.type || 'info');
            el.textContent = data.message;
            document.getElementById('sf-toast-container').appendChild(el);
            // Slight delay for CSS transition to register
            requestAnimationFrame(() => {
                requestAnimationFrame(() => el.classList.add('sf-toast-show'));
            });
            setTimeout(() => { 
                el.classList.remove('sf-toast-show'); 
                setTimeout(() => el.remove(), 400); 
            }, 4000);
        });
    }
</script>
