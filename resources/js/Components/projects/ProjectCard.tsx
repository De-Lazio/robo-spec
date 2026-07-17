import { Link } from '@inertiajs/react'
import { Bot, Calendar, Cpu, FolderOpen, Users, Wrench, Zap } from 'lucide-react'
import { ProjectStatusBadge } from '@/Components/ui/ProjectStatusBadge'
import type { Project, RobotType } from '@/types/projects'

const robotIcons: Record<RobotType, typeof Bot> = { mobile: Bot, arm: Wrench, drone: Zap, fixed: Cpu, humanoid: Bot, other: Cpu }
const robotLabels: Record<RobotType, string> = { mobile: 'Robot mobile', arm: 'Bras robotisé', drone: 'Drone', fixed: 'Robot fixe', humanoid: 'Humanoïde', other: 'Autre' }

export default function ProjectCard({ project }: { project: Project }) {
    const Icon = robotIcons[project.robot_type]
    return (
        <Link href={`/projects/${project.id}`} className="rf-project-card">
            <div className="rf-project-card__top">
                <div className="rf-project-icon"><Icon size={20} /></div>
                <div className="rf-project-card__title"><h3>{project.name}</h3><span>{robotLabels[project.robot_type]} · {project.domain ?? 'Sans domaine'}</span></div>
                <ProjectStatusBadge status={project.status} />
            </div>
            <p>{project.description || 'Aucune description ajoutée pour le moment.'}</p>
            <div className="rf-tags">{project.tags.slice(0, 3).map((tag) => <span key={tag}>{tag}</span>)}</div>
            <div className="rf-progress"><div><span>Avancement</span><strong>{project.progress}%</strong></div><i><b style={{ width: `${project.progress}%` }} /></i></div>
            <footer><span><Users size={14} />{project.members_count}</span><span><FolderOpen size={14} />0</span><span><Calendar size={14} />{project.updated_at ? new Intl.DateTimeFormat('fr-FR', { day: 'numeric', month: 'short' }).format(new Date(project.updated_at)) : '—'}</span></footer>
        </Link>
    )
}

export { robotLabels }
