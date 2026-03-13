// Waiter Panel - Real-time Notifications (Tenant-specific)
class WaiterNotifications {
    constructor() {
        this.lastCheck = localStorage.getItem('waiterLastCheck') || new Date().toISOString();
        this.pollInterval = 3000; // 3 seconds
        this.isPolling = false;
        this.notifiedOrders = new Set(JSON.parse(localStorage.getItem('waiterNotifiedOrders') || '[]'));
    }

    start() {
        if (this.isPolling) return;
        this.isPolling = true;
        this.poll();
    }

    stop() {
        this.isPolling = false;
    }

    async poll() {
        if (!this.isPolling) return;

        try {
            const response = await fetch(`/api/waiter/order-updates?last_check=${encodeURIComponent(this.lastCheck)}`);
            const data = await response.json();

            const currentTime = data.timestamp;
            
            if (data.ready_orders && data.ready_orders.length > 0) {
                const newReadyOrders = data.ready_orders.filter(order => !this.notifiedOrders.has(order.id));
                
                if (newReadyOrders.length > 0) {
                    this.handleReadyOrders(newReadyOrders);
                    
                    // Mark orders as notified
                    newReadyOrders.forEach(order => this.notifiedOrders.add(order.id));
                    
                    // Keep only last 100 entries
                    const ordersArray = [...this.notifiedOrders];
                    if (ordersArray.length > 100) {
                        this.notifiedOrders = new Set(ordersArray.slice(-100));
                    }
                    localStorage.setItem('waiterNotifiedOrders', JSON.stringify([...this.notifiedOrders]));
                    
                    // Update order cards via AJAX instead of refresh
                    this.updateOrderCards(newReadyOrders);
                }
            }
            
            // Update lastCheck after processing
            this.lastCheck = currentTime;
            localStorage.setItem('waiterLastCheck', currentTime);
        } catch (error) {
            console.error('Polling error:', error);
        }

        setTimeout(() => this.poll(), this.pollInterval);
    }

    handleReadyOrders(orders) {
        orders.forEach(order => {
            this.showNotification(`Order #${order.id} Ready!`, `Table ${order.table_number} - Ready to serve`);
            this.playSound();
        });
    }

    updateOrderCards(orders) {
        orders.forEach(order => {
            // Find the order card in the DOM
            const orderCards = document.querySelectorAll('[class*="bg-white p-4 rounded-lg shadow"]');
            orderCards.forEach(card => {
                const orderTitle = card.querySelector('h3');
                if (orderTitle && orderTitle.textContent.includes(`Order #${order.id}`)) {
                    // Update status badge
                    const statusBadge = card.querySelector('span[class*="px-2 py-1 rounded"]');
                    if (statusBadge) {
                        statusBadge.className = 'px-2 py-1 rounded text-xs font-semibold bg-green-100 text-green-800';
                        statusBadge.textContent = 'Ready';
                    }
                    
                    // Update buttons - remove "Add Items" and show "Mark as Served"
                    const buttonContainer = card.querySelector('.flex.gap-2');
                    if (buttonContainer) {
                        buttonContainer.innerHTML = `
                            <button onclick="markServed(${order.id})" 
                                    class="flex-1 bg-green-500 text-white px-4 py-2 rounded text-sm font-semibold">
                                Mark as Served
                            </button>
                        `;
                    }
                    
                    // Add visual highlight
                    card.classList.add('ring-2', 'ring-green-500');
                    setTimeout(() => {
                        card.classList.remove('ring-2', 'ring-green-500');
                    }, 3000);
                }
            });
        });
    }

    showNotification(title, message) {
        if ('Notification' in window && Notification.permission === 'granted') {
            new Notification(title, { body: message, icon: '/favicon.ico' });
        }

        const notification = document.createElement('div');
        notification.className = 'fixed top-20 left-1/2 transform -translate-x-1/2 bg-green-500 text-white px-6 py-4 rounded-lg shadow-lg z-50';
        notification.innerHTML = `<strong>${title}</strong><br>${message}`;
        document.body.appendChild(notification);

        setTimeout(() => notification.remove(), 4000);
    }

    playSound() {
        const audio = new Audio('data:audio/wav;base64,UklGRnoGAABXQVZFZm10IBAAAAABAAEAQB8AAEAfAAABAAgAZGF0YQoGAACBhYqFbF1fdJivrJBhNjVgodDbq2EcBj+a2/LDciUFLIHO8tiJNwgZaLvt559NEAxQp+PwtmMcBjiR1/LMeSwFJHfH8N2QQAoUXrTp66hVFApGn+DyvmwhBSuBzvLZiTYIGGS57OihUBELTKXh8bllHAU2jdXvzn0pBSh+zPDajzsKElyx6OyrWBUIQ5zd8sFuJAUuhM/z24k2CBhku+zooVARC0yl4fG5ZRwFNo3V7859KQUofsz');
        audio.play().catch(() => {});
    }

    requestNotificationPermission() {
        if ('Notification' in window && Notification.permission === 'default') {
            Notification.requestPermission();
        }
    }
}

document.addEventListener('DOMContentLoaded', function() {
    const notifications = new WaiterNotifications();
    notifications.requestNotificationPermission();
    notifications.start();
});
