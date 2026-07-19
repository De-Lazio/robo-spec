export interface NotificationItem {
    id: string
    read: boolean
    created_at: string
    project_id: string
    project_name: string
    event: string
    actor_name: string | null
    subject_type: string
    properties: Record<string, unknown>
    url: string
}
