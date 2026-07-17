import ProjectCard from '@/Components/projects/ProjectCard'
import { projectStatusLabels } from '@/Components/ui/ProjectStatusBadge'
import AppLayout from '@/Layouts/AppLayout'
import type { Paginated, Project, ProjectStatus } from '@/types/projects'
import { Head, Link, router } from '@inertiajs/react'
import { FolderOpen, Plus, Search } from 'lucide-react'
import { FormEvent, useState } from 'react'

interface IndexProps { projects: Paginated<Project>; filters: { search?: string; status?: string }; statuses: ProjectStatus[] }

export default function Index({ projects, filters, statuses }: IndexProps) {
    const [search, setSearch] = useState(filters.search ?? '')
    const submit = (event: FormEvent) => { event.preventDefault(); router.get('/projects', { search, status: filters.status }, { preserveState: true, replace: true }) }
    const changeStatus = (status: string) => router.get('/projects', { search: filters.search, status }, { preserveState: true, replace: true })
    return <AppLayout breadcrumbs={[{ label: 'Mes projets' }]} actions={<Link className="rf-button rf-button--primary rf-button--small" href="/projects/create"><Plus size={16} />Nouveau projet</Link>}>
        <Head title="Mes projets" />
        <section className="rf-page-intro"><div><p className="rf-eyebrow">Portefeuille</p><h1>Mes projets</h1><p>Retrouvez les projets dont vous êtes propriétaire ou membre.</p></div></section>
        <section className="rf-toolbar"><form onSubmit={submit}><Search size={16} /><input value={search} onChange={(event) => setSearch(event.target.value)} placeholder="Rechercher un projet…" /><button className="rf-button rf-button--secondary rf-button--small">Rechercher</button></form><select value={filters.status ?? ''} onChange={(event) => changeStatus(event.target.value)}><option value="">Tous les statuts</option>{statuses.map((status) => <option key={status} value={status}>{projectStatusLabels[status]}</option>)}</select></section>
        {projects.data.length ? <div className="rf-project-grid">{projects.data.map((project) => <ProjectCard key={project.id} project={project} />)}</div> : <div className="rf-empty"><FolderOpen size={28} /><h2>Aucun projet trouvé</h2><p>Modifiez vos filtres ou créez un nouveau projet.</p></div>}
    </AppLayout>
}
