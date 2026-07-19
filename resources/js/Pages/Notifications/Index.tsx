import AppLayout from '@/Layouts/AppLayout'
import { describeNotification } from '@/lib/notificationLabels'
import type { Paginated } from '@/types/projects'
import type { NotificationItem } from '@/types/notifications'
import { Head, router } from '@inertiajs/react'
import { Bell, Check } from 'lucide-react'

interface IndexProps {
    notifications: Paginated<NotificationItem>
}

export default function Index({ notifications }: IndexProps) {
    const openNotification = (notification: NotificationItem) => {
        if (!notification.read) {
            router.post(`/notifications/${notification.id}/read`, {}, { preserveScroll: true, preserveState: true })
        }

        router.visit(notification.url)
    }

    const markAllAsRead = () => {
        router.post('/notifications/read-all', {}, { preserveScroll: true, preserveState: true })
    }

    return (
        <AppLayout
            breadcrumbs={[{ label: 'Notifications' }]}
            actions={<button type="button" className="rf-button rf-button--secondary rf-button--small" onClick={markAllAsRead}><Check size={15} />Tout marquer comme lu</button>}
        >
            <Head title="Notifications" />
            <section className="rf-page-intro"><p className="rf-eyebrow">Historique</p><h1>Notifications</h1><p>Événements récents sur vos projets.</p></section>

            <div className="rf-panel">
                <div className="rf-member-list">
                    {notifications.data.map((notification) => (
                        <button
                            type="button"
                            key={notification.id}
                            className={`rf-member-row rf-notification-row ${notification.read ? '' : 'is-unread'}`}
                            onClick={() => openNotification(notification)}
                        >
                            <div className="rf-member-identity">
                                <div className="rf-resource-icon"><Bell size={16} /></div>
                                <div>
                                    <strong>{describeNotification(notification)}</strong>
                                    <span>{notification.project_name} · {new Date(notification.created_at).toLocaleString('fr-FR')}</span>
                                </div>
                            </div>
                        </button>
                    ))}
                    {notifications.data.length === 0 && <div className="rf-empty"><Bell size={28} /><h2>Aucune notification</h2><p>Vous serez averti ici des événements pertinents sur vos projets.</p></div>}
                </div>

                {notifications.links.length > 3 && (
                    <div className="rf-pagination">
                        {notifications.links.map((link, index) => (
                            <button
                                key={index}
                                type="button"
                                disabled={link.url === null}
                                className={`rf-button rf-button--secondary rf-button--small ${link.active ? 'is-active' : ''}`}
                                onClick={() => link.url && router.get(link.url, {}, { preserveState: true })}
                                dangerouslySetInnerHTML={{ __html: link.label }}
                            />
                        ))}
                    </div>
                )}
            </div>
        </AppLayout>
    )
}
