import type { NotificationItem } from '@/types/notifications'

export function describeNotification(notification: NotificationItem): string {
    const actor = notification.actor_name ?? 'Quelqu\'un'
    const props = notification.properties

    switch (notification.event) {
        case 'project.created':
            return `${actor} a créé le projet.`
        case 'project.updated':
            return `${actor} a modifié les informations du projet.`
        case 'project.archived':
            return `${actor} a archivé le projet.`
        case 'project.restored':
            return `${actor} a restauré le projet.`
        case 'project.deleted':
            return `${actor} a supprimé le projet.`
        case 'member.role_updated':
            return `${actor} a modifié le rôle d'un membre.`
        case 'member.removed':
            return `${actor} a retiré un membre du projet.`
        case 'member.invited':
            return `${actor} a invité ${props.email ?? 'un nouveau membre'}.`
        case 'member.joined':
            return `${actor} a rejoint le projet.`
        case 'requirements.step_saved':
            return `${actor} a enregistré l'étape ${props.step ?? ''} du CDC.`
        case 'requirements.published':
            return `${actor} a publié le CDC (version ${props.version ?? ''}).`
        case 'resource.uploaded':
            return `${actor} a ajouté la ressource « ${props.name ?? ''} ».`
        case 'resource.updated':
            return `${actor} a déplacé la ressource « ${props.name ?? ''} ».`
        case 'resource.deleted':
            return `${actor} a supprimé la ressource « ${props.name ?? ''} ».`
        case 'technical_choice.added':
            return `${actor} a ajouté « ${props.component ?? ''} » aux choix techniques.`
        case 'technical_choice.removed':
            return `${actor} a retiré « ${props.component ?? ''} » des choix techniques.`
        case 'task.created':
            return `${actor} a créé la tâche « ${props.title ?? ''} ».`
        case 'task.status_changed':
            return `${actor} a déplacé la tâche « ${props.title ?? ''} ».`
        case 'task.deleted':
            return `${actor} a supprimé la tâche « ${props.title ?? ''} ».`
        case 'algorithm_diagram.created':
            return `${actor} a créé l'algorigramme « ${props.name ?? ''} ».`
        case 'algorithm_diagram.updated':
            return `${actor} a modifié l'algorigramme « ${props.name ?? ''} ».`
        case 'algorithm_diagram.deleted':
            return `${actor} a supprimé l'algorigramme « ${props.name ?? ''} ».`
        case 'github.linked':
            return `${actor} a lié le dépôt ${props.owner ?? ''}/${props.repository ?? ''}.`
        case 'github.sync_requested':
            return `${actor} a demandé une synchronisation GitHub.`
        case 'github.unlinked':
            return `${actor} a délié le dépôt GitHub.`
        case 'export.generated':
            return `${actor} a exporté le projet en PDF.`
        default:
            return `${actor} a effectué une action sur le projet.`
    }
}
