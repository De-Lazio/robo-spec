import { describeNotification } from '@/lib/notificationLabels'
import type { NotificationItem } from '@/types/notifications'
import { Link, router, usePage } from '@inertiajs/react'
import { Bell, Check } from 'lucide-react'
import { useState } from 'react'

interface NotificationsShared {
    unread_count: number
    recent: NotificationItem[]
}

export default function NotificationBell() {
    const [isOpen, setIsOpen] = useState(false)
    const page = usePage<{ notifications: NotificationsShared }>()
    const { unread_count: unreadCount, recent } = page.props.notifications

    const openNotification = (notification: NotificationItem) => {
        setIsOpen(false)

        if (!notification.read) {
            router.post(`/notifications/${notification.id}/read`, {}, { preserveScroll: true, preserveState: true })
        }

        router.visit(notification.url)
    }

    const markAllAsRead = () => {
        router.post('/notifications/read-all', {}, { preserveScroll: true, preserveState: true })
    }

    return (
        <div className="rf-notification-bell">
            <button type="button" className="rf-button rf-button--secondary rf-button--small" onClick={() => setIsOpen((open) => !open)} aria-label="Notifications">
                <Bell size={16} />
                {unreadCount > 0 && <span className="rf-notification-badge">{unreadCount > 9 ? '9+' : unreadCount}</span>}
            </button>
            {isOpen && (
                <>
                    <button type="button" className="rf-notification-scrim" aria-label="Fermer" onClick={() => setIsOpen(false)} />
                    <div className="rf-notification-dropdown">
                        <div className="rf-notification-dropdown-header">
                            <strong>Notifications</strong>
                            <button type="button" onClick={markAllAsRead}><Check size={13} /> Tout marquer comme lu</button>
                        </div>
                        <div className="rf-notification-list">
                            {recent.length === 0 && <p className="rf-field-hint">Aucune notification.</p>}
                            {recent.map((notification) => (
                                <button
                                    type="button"
                                    key={notification.id}
                                    className={`rf-notification-item ${notification.read ? '' : 'is-unread'}`}
                                    onClick={() => openNotification(notification)}
                                >
                                    <span>{describeNotification(notification)}</span>
                                    <span className="rf-field-hint">{notification.project_name}</span>
                                </button>
                            ))}
                        </div>
                        <Link href="/notifications" className="rf-notification-footer" onClick={() => setIsOpen(false)}>
                            Voir tout l'historique
                        </Link>
                    </div>
                </>
            )}
        </div>
    )
}
