import ProjectCard from '@/Components/projects/ProjectCard'
import AppLayout from '@/Layouts/AppLayout'
import type { Organization } from '@/types/organizations'
import type { Project } from '@/types/projects'
import { Head, Link } from '@inertiajs/react'
import { FolderOpen, Settings, Users } from 'lucide-react'

interface ShowProps {
    organization: Organization
    projects: Project[]
    canManage: boolean
}

export default function Show({ organization, projects, canManage }: ShowProps) {
    return <AppLayout breadcrumbs={[{ label: 'Organisations', href: '/organizations' }, { label: organization.name }]}
        actions={canManage ? <Link href={`/organizations/${organization.id}/settings`} className="rf-button rf-button--secondary rf-button--small"><Settings size={15} />Paramètres</Link> : undefined}>
        <Head title={organization.name} />
        <section className="rf-project-hero">
            <div className="rf-project-hero__icon">🏷️</div>
            <div><div className="rf-project-title"><h1>{organization.name}</h1></div><p>{organization.owner ? `Dirigée par ${organization.owner.name}` : ''} · {organization.members_count} membre{organization.members_count > 1 ? 's' : ''}</p></div>
        </section>

        <div className="rf-detail-grid">
            <section className="rf-panel"><h2>À propos</h2><p className="rf-description">{organization.description || 'Cette organisation n’a pas encore de description.'}</p></section>
            <aside className="rf-panel">
                <h2>Indicateurs</h2>
                <dl className="rf-metrics">
                    <div><dt><Users size={16} />Membres</dt><dd>{organization.members_count}</dd></div>
                    <div><dt><FolderOpen size={16} />Projets</dt><dd>{organization.projects_count}</dd></div>
                </dl>
            </aside>
        </div>

        <section className="rf-section-heading">
            <div><h2>Projets</h2><p>Les projets rattachés à cette organisation.</p></div>
            <Link href={`/organizations/${organization.id}/members`} className="rf-text-link">Gérer l’équipe</Link>
        </section>
        {projects.length ? (
            <div className="rf-project-grid">{projects.map((project) => <ProjectCard key={project.id} project={project} />)}</div>
        ) : (
            <div className="rf-empty"><FolderOpen size={28} /><h2>Aucun projet rattaché</h2><p>Rattachez un projet à cette organisation depuis sa création.</p></div>
        )}
    </AppLayout>
}
