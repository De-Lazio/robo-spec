import ProjectCard from '@/Components/projects/ProjectCard'
import AppLayout from '@/Layouts/AppLayout'
import type { Paginated, Project } from '@/types/projects'
import { Head, Link } from '@inertiajs/react'
import { CheckCircle2, FolderOpen, Plus, TrendingUp } from 'lucide-react'

interface DashboardProps { projects: Paginated<Project>; stats: { total: number; in_progress: number; completed: number; archived: number } }

export default function Dashboard({ projects, stats }: DashboardProps) {
    const cards = [
        { label: 'Projets totaux', value: stats.total, icon: FolderOpen }, { label: 'En cours', value: stats.in_progress, icon: TrendingUp },
        { label: 'Terminés', value: stats.completed, icon: CheckCircle2 }, { label: 'Archivés', value: stats.archived, icon: FolderOpen },
    ]
    return <AppLayout breadcrumbs={[{ label: 'Tableau de bord' }]} actions={<Link className="rf-button rf-button--primary rf-button--small" href="/projects/create"><Plus size={16} />Nouveau projet</Link>}>
        <Head title="Tableau de bord" />
        <section className="rf-page-intro"><div><p className="rf-eyebrow">Espace de travail</p><h1>Tableau de bord</h1><p>Suivez l’avancement de vos projets robotiques au même endroit.</p></div></section>
        <section className="rf-stat-grid">{cards.map(({ label, value, icon: Icon }) => <article className="rf-stat-card" key={label}><span><Icon size={19} /></span><div><strong>{value}</strong><p>{label}</p></div></article>)}</section>
        <section className="rf-section-heading"><div><h2>Projets récents</h2><p>Les derniers projets que vous pouvez consulter.</p></div><Link href="/projects" className="rf-text-link">Voir tous les projets</Link></section>
        {projects.data.length ? <div className="rf-project-grid">{projects.data.map((project) => <ProjectCard key={project.id} project={project} />)}</div> : <EmptyProjects />}
    </AppLayout>
}

function EmptyProjects() { return <div className="rf-empty"><FolderOpen size={28} /><h2>Votre espace est prêt</h2><p>Créez votre premier projet robotique pour ajouter son cahier des charges, ses ressources et son équipe.</p><Link className="rf-button rf-button--primary" href="/projects/create"><Plus size={16} />Créer un projet</Link></div> }
