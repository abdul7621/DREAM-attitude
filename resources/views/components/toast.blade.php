<div id="sf-toast-container" style="position:fixed;top:80px;right:16px;z-index:9999;"></div>
<script>
    if (typeof window.Store !== 'undefined') {
        window.Store.on('toast', (data) => {
            const el = document.createElement('div');
            el.className = 'sf-toast sf-toast-' + data.type;
            el.innerHTML = data.message;
            document.getElementById('sf-toast-container').appendChild(el);
            setTimeout(() => el.classList.add('sf-toast-show'), 10);
            setTimeout(() => { 
                el.classList.remove('sf-toast-show'); 
                setTimeout(() => el.remove(), 300); 
            }, 3000);
        });
    }
</script>
