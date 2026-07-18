import { robotLabels } from '@/Components/projects/ProjectCard'
import { ProjectStatusBadge } from '@/Components/ui/ProjectStatusBadge'
import AppLayout from '@/Layouts/AppLayout'
import type { Project } from '@/types/projects'
import { Head, Link } from '@inertiajs/react'
import { ArrowRight, ClipboardList, FolderOpen, GitBranch, Settings, Users } from 'lucide-react'

export default function Show({ project }: { project: Project }) {
    return <AppLayout breadcrumbs={[{ label: 'Mes projets', href: '/projects' }, { label: project.name }]} actions={<Link href={`/projects/${project.id}/settings`} className="rf-button rf-button--secondary rf-button--small"><Settings size={15} />Paramètres</Link>}>
        <Head title={project.name} />
        <section className="rf-project-hero"><div className="rf-project-hero__icon">🤖</div><div><div className="rf-project-title"><h1>{project.name}</h1><ProjectStatusBadge status={project.status} /></div><p>{robotLabels[project.robot_type]} · {project.domain ?? 'Sans domaine'} · Créé le {project.created_at ? new Intl.DateTimeFormat('fr-FR', { dateStyle: 'medium' }).format(new Date(project.created_at)) : '—'}</p></div></section>
        <section className="rf-project-progress"><div><span>Avancement global</span><strong>{project.progress}%</strong></div><i><b style={{ width: `${project.progress}%` }} /></i></section>
        <div className="rf-detail-grid"><section className="rf-panel"><h2>Vue d’ensemble</h2><p className="rf-description">{project.description || 'Ce projet n’a pas encore de description.'}</p><div className="rf-tags">{project.tags.length ? project.tags.map((tag) => <span key={tag}>{tag}</span>) : <span>Aucun tag</span>}</div></section><aside className="rf-panel"><h2>Indicateurs</h2><dl className="rf-metrics"><div><dt><Users size={16} />Membres</dt><dd>{project.members_count}</dd></div><div><dt><FolderOpen size={16} />Ressources</dt><dd>0</dd></div><div><dt><ClipboardList size={16} />Activités</dt><dd>{project.activities_count}</dd></div></dl></aside></div>
        <section className="rf-section-heading"><div><h2>Espaces du projet</h2><p>Les modules métier seront activés au fil des prochaines phases.</p></div></section><div className="rf-module-grid"><Module icon={Users} title="Équipe" description="Invitez des membres et gérez leurs rôles." href={`/projects/${project.id}/members`} /><Module icon={ClipboardList} title="Cahier des charges" description="Définissez les 12 sections de votre spécification." /><Module icon={FolderOpen} title="Ressources" description="Centralisez fichiers mécaniques, électroniques et logiciels." /><Module icon={GitBranch} title="GitHub" description="Reliez le dépôt source et suivez ses activités." /></div>
    </AppLayout>
}

function Module({ icon: Icon, title, description, href }: { icon: typeof FolderOpen; title: string; description: string; href?: string }) {
    const content = <><Icon size={20} /><h3>{title}</h3><p>{description}</p><span>{href ? 'Voir' : 'Prochainement'} <ArrowRight size={14} /></span></>
    return href ? <Link href={href} className="rf-module">{content}</Link> : <article className="rf-module">{content}</article>
}
