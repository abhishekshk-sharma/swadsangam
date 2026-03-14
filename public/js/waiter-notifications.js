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

            // Remove reactivated orders from notified set so they can be re-notified when ready again
            if (data.reactivated_orders && data.reactivated_orders.length > 0) {
                data.reactivated_orders.forEach(id => {
                    this.notifiedOrders.delete(id);
                    this.updateOrderCardStatus(id, 'preparing');
                });
                localStorage.setItem('waiterNotifiedOrders', JSON.stringify([...this.notifiedOrders]));
            }

            if (data.ready_orders && data.ready_orders.length > 0) {
                const newReadyOrders = data.ready_orders.filter(order => !this.notifiedOrders.has(order.id));

                if (newReadyOrders.length > 0) {
                    newReadyOrders.forEach(order => {
                        this.showNotification(`Order #${order.id} Ready!`, `Table ${order.table_number} - Ready to serve`);
                        this.updateOrderCardStatus(order.id, 'ready');
                        this.notifiedOrders.add(order.id);
                    });

                    this.playSound();

                    const ordersArray = [...this.notifiedOrders];
                    if (ordersArray.length > 100) {
                        this.notifiedOrders = new Set(ordersArray.slice(-100));
                    }
                    localStorage.setItem('waiterNotifiedOrders', JSON.stringify([...this.notifiedOrders]));
                }
            }

            this.lastCheck = data.timestamp;
            localStorage.setItem('waiterLastCheck', data.timestamp);
        } catch (error) {
            console.error('Polling error:', error);
        }

        setTimeout(() => this.poll(), this.pollInterval);
    }

    updateOrderCardStatus(orderId, status) {
        // DOM updates are handled by order-poll.js — no-op here
    }

    showNotification(title, message) {
        if ('Notification' in window && Notification.permission === 'granted') {
            new Notification(title, { body: message, icon: '/favicon.ico' });
        }

        const notification = document.createElement('div');
        notification.className = 'fixed top-20 left-1/2 transform -translate-x-1/2 bg-green-500 text-white px-6 py-4 rounded-lg shadow-lg z-50 text-center';
        notification.innerHTML = `<strong>${title}</strong><br>${message}`;
        document.body.appendChild(notification);
        setTimeout(() => notification.remove(), 4000);
    }

    playSound() {
        const audio = new Audio('data:audio/wav;base64,UklGRnoGAABXQVZFZm10IBAAAAABAAEAQB8AAEAfAAABAAgAZGF0YQoGAACBhYqFbF1fdJivrJBhNjVgodDbq2EcBj+a2/LDciUFLIHO8tiJNwgZaLvt559NEAxQp+PwtmMcBjiR1/LMeSwFJHfH8N2QQAoUXrTp66hVFApGn+DyvmwhBSuBzvLZiTYIGGS57OihUBELTKXh8bllHAU2jdXvzn0pBSh+zPDajzsKElyx6OyrWBUIQ5zd8sFuJAUuhM/z24k2CBhku+zooVARC0yl4fG5ZRwFNo3V7899KQUofsz');
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
