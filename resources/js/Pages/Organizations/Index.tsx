import AppLayout from '@/Layouts/AppLayout'
import type { Organization } from '@/types/organizations'
import { Head, Link } from '@inertiajs/react'
import { Building2, Plus, Users } from 'lucide-react'

export default function Index({ organizations }: { organizations: Organization[] }) {
    return <AppLayout breadcrumbs={[{ label: 'Organisations' }]} actions={<Link className="rf-button rf-button--primary rf-button--small" href="/organizations/create"><Plus size={16} />Nouvelle organisation</Link>}>
        <Head title="Organisations" />
        <section className="rf-page-intro"><div><p className="rf-eyebrow">Espace de travail</p><h1>Organisations</h1><p>Les équipes permanentes que vous dirigez ou dont vous êtes membre.</p></div></section>
        {organizations.length ? (
            <div className="rf-project-grid">
                {organizations.map((organization) => (
                    <Link key={organization.id} href={`/organizations/${organization.id}`} className="rf-project-card">
                        <div className="rf-project-card__top">
                            <div className="rf-project-icon"><Building2 size={20} /></div>
                            <div className="rf-project-card__title"><h3>{organization.name}</h3><span>{organization.owner?.name ?? '—'}</span></div>
                        </div>
                        <p>{organization.description || 'Aucune description ajoutée pour le moment.'}</p>
                        <footer><span><Users size={14} />{organization.members_count}</span><span><Building2 size={14} />{organization.projects_count} projet{organization.projects_count > 1 ? 's' : ''}</span></footer>
                    </Link>
                ))}
            </div>
        ) : (
            <div className="rf-empty">
                <Building2 size={28} />
                <h2>Aucune organisation</h2>
                <p>Créez une organisation pour regrouper vos projets et votre équipe permanente au même endroit.</p>
                <Link className="rf-button rf-button--primary" href="/organizations/create"><Plus size={16} />Créer une organisation</Link>
            </div>
        )}
    </AppLayout>
}
