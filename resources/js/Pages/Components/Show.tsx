import AppLayout from '@/Layouts/AppLayout'
import { componentTypeLabels } from '@/types/components'
import type { LibraryComponent } from '@/types/components'
import { Head, Link, router } from '@inertiajs/react'
import { Download, Pencil, Power, Trash2 } from 'lucide-react'

interface ShowProps {
    component: LibraryComponent
    canManage: boolean
}

function formatPrice(cents: number | null, currency: string | null): string {
    if (cents === null) return '—'
    return `${(cents / 100).toFixed(2)} ${currency ?? ''}`.trim()
}

export default function Show({ component, canManage }: ShowProps) {
    const confirmAndSend = (message: string, action: () => void) => { if (window.confirm(message)) action() }

    return <AppLayout breadcrumbs={[{ label: 'Bibliothèque', href: '/components' }, { label: component.name }]}
        actions={canManage ? <>
            <Link href={`/components/${component.id}/edit`} className="rf-button rf-button--secondary rf-button--small"><Pencil size={14} />Modifier</Link>
            <button type="button" className="rf-button rf-button--secondary rf-button--small" onClick={() => router.patch(`/components/${component.id}/toggle-active`)}><Power size={14} />{component.is_active ? 'Désactiver' : 'Réactiver'}</button>
            <button type="button" className="rf-button rf-button--danger rf-button--small" onClick={() => confirmAndSend(`Supprimer « ${component.name} » du catalogue ?`, () => router.delete(`/components/${component.id}`))}><Trash2 size={14} />Supprimer</button>
        </> : undefined}>
        <Head title={component.name} />
        <section className="rf-project-hero">
            <div className="rf-project-hero__icon">🔧</div>
            <div>
                <div className="rf-project-title"><h1>{component.name}</h1>{!component.is_active && <span className="rf-badge rf-badge--archived">Inactif</span>}</div>
                <p>{component.category ? `${componentTypeLabels[component.category.type]} · ${component.category.name}` : 'Sans catégorie'} {component.manufacturer ? `· ${component.manufacturer}` : ''} {component.reference ? `· réf. ${component.reference}` : ''}</p>
            </div>
        </section>

        <div className="rf-detail-grid">
            <section className="rf-panel">
                <h2>Description</h2>
                <p className="rf-description">{component.description || 'Aucune description ajoutée pour le moment.'}</p>
                <h2>Caractéristiques techniques</h2>
                {Object.keys(component.specs).length > 0 ? (
                    <dl className="rf-metrics">
                        {Object.entries(component.specs).map(([key, value]) => <div key={key}><dt>{key}</dt><dd>{value}</dd></div>)}
                    </dl>
                ) : <p style={{ color: 'var(--rf-text-muted)', fontSize: 13 }}>Aucune caractéristique renseignée.</p>}
            </section>
            <aside className="rf-panel">
                <h2>Informations</h2>
                <dl className="rf-metrics">
                    <div><dt>Prix</dt><dd>{formatPrice(component.price_cents, component.currency)}</dd></div>
                    <div><dt>Fournisseur</dt><dd>{component.supplier_url ? <a href={component.supplier_url} target="_blank" rel="noreferrer">Lien</a> : '—'}</dd></div>
                </dl>
                {component.datasheet_url && (
                    <a href={component.datasheet_url} className="rf-button rf-button--secondary rf-button--block" style={{ marginTop: 14 }}><Download size={14} />Télécharger la fiche technique</a>
                )}
            </aside>
        </div>
    </AppLayout>
}
