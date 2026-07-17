import type { ProjectStatus } from '@/types/projects'

const labels: Record<ProjectStatus, string> = {
    draft: 'Brouillon',
    in_progress: 'En cours',
    testing: 'Tests',
    completed: 'Terminé',
    archived: 'Archivé',
}

export function ProjectStatusBadge({ status }: { status: ProjectStatus }) {
    return <span className={`rf-badge rf-badge--${status}`}>{labels[status]}</span>
}

export { labels as projectStatusLabels }
