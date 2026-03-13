// Chef Panel - Real-time Notifications (Tenant-specific)
class ChefNotifications {
    constructor() {
        this.lastCheck = localStorage.getItem('chefLastCheck') || new Date().toISOString();
        this.pollInterval = 3000; // 3 seconds
        this.isPolling = false;
        this.notifiedOrders = new Set(JSON.parse(localStorage.getItem('chefNotifiedOrders') || '[]'));
        this.notifiedItems = new Set(JSON.parse(localStorage.getItem('chefNotifiedItems') || '[]'));
        this.cleanupOldNotifications();
    }

    cleanupOldNotifications() {
        // Clean up notifications older than 30 minutes
        const thirtyMinutesAgo = new Date(Date.now() - 30 * 60 * 1000).toISOString();
        if (this.lastCheck < thirtyMinutesAgo) {
            this.notifiedOrders.clear();
            this.notifiedItems.clear();
            localStorage.removeItem('chefNotifiedOrders');
            localStorage.removeItem('chefNotifiedItems');
            this.lastCheck = new Date().toISOString();
            localStorage.setItem('chefLastCheck', this.lastCheck);
        }
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
            const response = await fetch(`/api/chef/order-updates?last_check=${encodeURIComponent(this.lastCheck)}`);
            
            if (!response.ok) {
                console.error('API response not OK:', response.status, response.statusText);
                setTimeout(() => this.poll(), this.pollInterval);
                return;
            }
            
            const data = await response.json();
            console.log('Chef polling data:', data);
            console.log('Last check time:', this.lastCheck);

            let shouldRefresh = false;

            // Check for new orders
            if (data.new_orders && Array.isArray(data.new_orders) && data.new_orders.length > 0) {
                console.log('Raw new orders:', data.new_orders);
                
                const newOrders = data.new_orders.filter(order => {
                    const key = `order_${order.id}`;
                    const isNotified = this.notifiedOrders.has(key);
                    console.log(`Order ${order.id} - Already notified:`, isNotified);
                    return !isNotified;
                });
                
                if (newOrders.length > 0) {
                    console.log('New orders to notify:', newOrders);
                    this.handleNewOrders(newOrders);
                    shouldRefresh = true;
                    
                    // Mark orders as notified
                    newOrders.forEach(order => {
                        const key = `order_${order.id}`;
                        this.notifiedOrders.add(key);
                        console.log('Marked as notified:', key);
                    });
                    
                    // Keep only last 100 entries
                    const ordersArray = [...this.notifiedOrders];
                    if (ordersArray.length > 100) {
                        this.notifiedOrders = new Set(ordersArray.slice(-100));
                    }
                    localStorage.setItem('chefNotifiedOrders', JSON.stringify([...this.notifiedOrders]));
                }
            }

            // Check for additional items with deduplication
            if (data.additional_items && Array.isArray(data.additional_items) && data.additional_items.length > 0) {
                console.log('Raw additional items:', data.additional_items);
                
                const newAdditionalItems = [];
                data.additional_items.forEach(order => {
                    if (order.items && Array.isArray(order.items)) {
                        const unnotifiedItems = order.items.filter(item => {
                            const key = `item_${item.id}`;
                            return !this.notifiedItems.has(key);
                        });
                        
                        if (unnotifiedItems.length > 0) {
                            newAdditionalItems.push({
                                ...order,
                                items: unnotifiedItems,
                                new_items_count: unnotifiedItems.length
                            });
                            
                            // Mark items as notified
                            unnotifiedItems.forEach(item => {
                                const key = `item_${item.id}`;
                                this.notifiedItems.add(key);
                                console.log('Marked item as notified:', key);
                            });
                        }
                    }
                });
                
                if (newAdditionalItems.length > 0) {
                    console.log('New additional items to notify:', newAdditionalItems);
                    this.handleAdditionalItems(newAdditionalItems);
                    shouldRefresh = true;
                    
                    // Keep only last 200 entries
                    const itemsArray = [...this.notifiedItems];
                    if (itemsArray.length > 200) {
                        this.notifiedItems = new Set(itemsArray.slice(-200));
                    }
                    localStorage.setItem('chefNotifiedItems', JSON.stringify([...this.notifiedItems]));
                }
            }
            
            // Update lastCheck after processing
            this.lastCheck = data.timestamp;
            localStorage.setItem('chefLastCheck', this.lastCheck);
            console.log('Updated last check to:', this.lastCheck);

            // Refresh page after showing notification
            if (shouldRefresh) {
                console.log('Refreshing page in 3 seconds...');
                setTimeout(() => {
                    window.location.reload();
                }, 3000);
            }
        } catch (error) {
            console.error('Polling error:', error);
        }

        setTimeout(() => this.poll(), this.pollInterval);
    }

    handleNewOrders(orders) {
        orders.forEach(order => {
            const itemsList = order.items ? order.items.map(item => `${item.name} (x${item.quantity})`).join(', ') : '';
            const message = itemsList ? 
                `Table ${order.table_number} - ${order.items_count} items\n${itemsList}` :
                `Table ${order.table_number} - ${order.items_count} items`;
            this.showNotification(`🔔 New Order #${order.id}`, message);
            this.playSound();
        });
    }

    handleAdditionalItems(orders) {
        orders.forEach(order => {
            const itemsList = order.items ? order.items.map(item => `${item.name} (x${item.quantity})`).join(', ') : '';
            const message = itemsList ?
                `Table ${order.table_number} - ${order.new_items_count} new items (Total: ${order.total_items_count})\n${itemsList}` :
                `Table ${order.table_number} - ${order.new_items_count} new items (Total: ${order.total_items_count})`;
            this.showNotification(`➕ Items Added to Order #${order.id}`, message);
            this.playSound();
        });
    }

    showNotification(title, message) {
        if ('Notification' in window && Notification.permission === 'granted') {
            new Notification(title, { body: message, icon: '/favicon.ico' });
        }

        const notification = document.createElement('div');
        notification.className = 'fixed top-20 left-1/2 transform -translate-x-1/2 bg-blue-600 text-white px-6 py-4 rounded-lg shadow-lg z-50';
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
    const notifications = new ChefNotifications();
    notifications.requestNotificationPermission();
    notifications.start();
});
