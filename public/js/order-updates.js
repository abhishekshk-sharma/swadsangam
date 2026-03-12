// Real-time Order Updates
class OrderUpdates {
    constructor() {
        this.lastCheck = localStorage.getItem('lastOrderCheck') || new Date().toISOString();
        this.pollInterval = 5000; // 5 seconds
        this.isPolling = false;
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
            const response = await fetch(`/api/order-updates?last_check=${encodeURIComponent(this.lastCheck)}`);
            const data = await response.json();

            // Update lastCheck IMMEDIATELY to prevent duplicates
            const newOrders = data.new_orders;
            const updatedOrders = data.updated_orders;
            this.lastCheck = data.timestamp;
            localStorage.setItem('lastOrderCheck', data.timestamp);

            if (newOrders.length > 0) {
                this.handleNewOrders(newOrders);
            }

            if (updatedOrders.length > 0) {
                this.handleUpdatedOrders(updatedOrders);
            }
        } catch (error) {
            console.error('Polling error:', error);
        }

        setTimeout(() => this.poll(), this.pollInterval);
    }

    handleNewOrders(orders) {
        orders.forEach(order => {
            this.showNotification(`New Order #${order.id}`, `Table: ${order.table_name} - ₹${order.total_amount}`);
            this.playSound();
        });
        
        // Reload page after a delay
        if (orders.length > 0) {
            setTimeout(() => window.location.reload(), 3000);
        }
    }

    handleUpdatedOrders(orders) {
        orders.forEach(order => {
            this.showNotification(`Order #${order.id} Updated`, `Status: ${order.status}`);
        });
        
        // Reload page after a delay
        if (orders.length > 0) {
            setTimeout(() => window.location.reload(), 3000);
        }
    }

    showNotification(title, message) {
        // Browser notification
        if ('Notification' in window && Notification.permission === 'granted') {
            new Notification(title, { body: message, icon: '/favicon.ico' });
        }

        // On-screen notification
        const notification = document.createElement('div');
        notification.className = 'fixed top-4 right-4 bg-green-500 text-white px-6 py-4 rounded-lg shadow-lg z-50 animate-bounce';
        notification.innerHTML = `<strong>${title}</strong><br>${message}`;
        document.body.appendChild(notification);

        setTimeout(() => notification.remove(), 5000);
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

// Auto-start on page load
document.addEventListener('DOMContentLoaded', function() {
    const orderUpdates = new OrderUpdates();
    orderUpdates.requestNotificationPermission();
    orderUpdates.start();
});
