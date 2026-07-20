{{-- Floating WhatsApp Widget --}}
<div class="sf-whatsapp-widget" style="position: fixed; bottom: 80px; right: 24px; z-index: 1000; font-family: sans-serif;">
    
    <!-- Widget Button -->
    <button type="button" id="wa-widget-btn" onclick="toggleWaMenu()" style="width: 56px; height: 56px; border-radius: 50%; background: #25d366; border: none; color: white; font-size: 28px; cursor: pointer; box-shadow: 0 4px 16px rgba(37,211,102,0.4); display: flex; align-items: center; justify-content: center; transition: transform 0.25s ease;">
        <i class="bi bi-whatsapp" id="wa-icon-show"></i>
        <i class="bi bi-x-lg" id="wa-icon-close" style="display: none; font-size: 20px;"></i>
    </button>

    <!-- Support Options Menu -->
    <div id="wa-widget-menu" style="display: none; position: absolute; bottom: 70px; right: 0; width: 280px; background: white; border-radius: 12px; box-shadow: 0 8px 24px rgba(0,0,0,0.15); border: 1px solid rgba(0,0,0,0.08); overflow: hidden; animation: slideUpWa 0.25s ease;">
        
        <!-- Header -->
        <div style="background: #075e54; color: white; padding: 16px; text-align: left;">
            <div style="font-weight: bold; font-size: 14px; display: flex; align-items: center; gap: 8px;">
                <i class="bi bi-whatsapp"></i> Chat Support
            </div>
            <div style="font-size: 11px; opacity: 0.8; margin-top: 4px;">Click an option below to start a chat</div>
        </div>

        <!-- Options list -->
        <div style="padding: 8px 0;">
            
            <!-- Option 1 -->
            <a href="#" id="wa-link-beauty" target="_blank" onclick="closeWaMenu()" style="display: flex; align-items: center; gap: 12px; padding: 12px 16px; text-decoration: none; color: #333; transition: background 0.2s;">
                <div style="width: 36px; height: 36px; border-radius: 50%; background: #e8f5e9; color: #2e7d32; display: flex; align-items: center; justify-content: center; font-size: 18px;">
                    <i class="bi bi-chat-heart-fill"></i>
                </div>
                <div style="text-align: left;">
                    <div style="font-size: 13px; font-weight: 600;">Beauty Consultant</div>
                    <div style="font-size: 11px; color: #777;">Product recommendations</div>
                </div>
            </a>

            <!-- Option 2 -->
            <a href="#" id="wa-link-orders" target="_blank" onclick="closeWaMenu()" style="display: flex; align-items: center; gap: 12px; padding: 12px 16px; text-decoration: none; color: #333; transition: background 0.2s; border-top: 1px solid #f1f1f1;">
                <div style="width: 36px; height: 36px; border-radius: 50%; background: #e3f2fd; color: #1565c0; display: flex; align-items: center; justify-content: center; font-size: 18px;">
                    <i class="bi bi-truck"></i>
                </div>
                <div style="text-align: left;">
                    <div style="font-size: 13px; font-weight: 600;">Order & Tracking Support</div>
                    <div style="font-size: 11px; color: #777;">Check delivery status</div>
                </div>
            </a>

            <!-- Option 3 -->
            <a href="#" id="wa-link-bulk" target="_blank" onclick="closeWaMenu()" style="display: flex; align-items: center; gap: 12px; padding: 12px 16px; text-decoration: none; color: #333; transition: background 0.2s; border-top: 1px solid #f1f1f1;">
                <div style="width: 36px; height: 36px; border-radius: 50%; background: #fff8e1; color: #f57f17; display: flex; align-items: center; justify-content: center; font-size: 18px;">
                    <i class="bi bi-shop"></i>
                </div>
                <div style="text-align: left;">
                    <div style="font-size: 13px; font-weight: 600;">Salon Bulk Quotes</div>
                    <div style="font-size: 11px; color: #777;">Wholesale price list</div>
                </div>
            </a>

        </div>

    </div>
</div>

<style>
@keyframes slideUpWa {
    from { transform: translateY(15px); opacity: 0; }
    to { transform: translateY(0); opacity: 1; }
}
#wa-widget-btn:hover {
    transform: scale(1.08);
}
#wa-widget-menu a:hover {
    background: #f9f9f9;
}
</style>

<script>
function toggleWaMenu() {
    var menu = document.getElementById('wa-widget-menu');
    var openIcon = document.getElementById('wa-icon-show');
    var closeIcon = document.getElementById('wa-icon-close');
    
    if (menu.style.display === 'none') {
        menu.style.display = 'block';
        openIcon.style.display = 'none';
        closeIcon.style.display = 'block';
    } else {
        closeWaMenu();
    }
}

function closeWaMenu() {
    var menu = document.getElementById('wa-widget-menu');
    var openIcon = document.getElementById('wa-icon-show');
    var closeIcon = document.getElementById('wa-icon-close');
    
    if (menu) menu.style.display = 'none';
    if (openIcon) openIcon.style.display = 'block';
    if (closeIcon) closeIcon.style.display = 'none';
}

document.addEventListener('DOMContentLoaded', function() {
    // Generate custom pre-filled message depending on page context
    var beautyNum = '919999999999'; // Configurable default WhatsApp numbers
    var ordersNum = '919999999999';
    var bulkNum   = '919999999999';
    
    var currentUrl = window.location.href;
    var pdpNameEl = document.querySelector('.sf-section-title');
    var pdpName = pdpNameEl ? pdpNameEl.textContent.trim() : '';

    var beautyText = "Hi, I would like to consult with a beauty expert.";
    if (pdpName) {
        beautyText = "Hi, I have a query about: " + pdpName + ". Can you assist me?";
    }
    
    var ordersText = "Hi, I want to check my order status.";
    var bulkText = "Hi, I want a catalog and price list for salon bulk queries.";

    document.getElementById('wa-link-beauty').href = "https://wa.me/" + beautyNum + "?text=" + encodeURIComponent(beautyText);
    document.getElementById('wa-link-orders').href = "https://wa.me/" + ordersNum + "?text=" + encodeURIComponent(ordersText);
    document.getElementById('wa-link-bulk').href = "https://wa.me/" + bulkNum + "?text=" + encodeURIComponent(bulkText);
});
</script>
