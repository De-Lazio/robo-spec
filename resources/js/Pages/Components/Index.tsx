import AppLayout from '@/Layouts/AppLayout'
import { componentTypeLabels } from '@/types/components'
import type { ComponentCategoryOption, ComponentType, LibraryComponent } from '@/types/components'
import { Head, Link, router } from '@inertiajs/react'
import { ListTree, Plus, Search } from 'lucide-react'
import type { FormEvent } from 'react'

const PLACEHOLDER_IMAGE = '/images/component-placeholder.svg'

interface IndexProps {
    components: LibraryComponent[]
    categories: ComponentCategoryOption[]
    types: ComponentType[]
    filters: { type?: string; category_id?: string; search?: string }
    canManage: boolean
}

export default function Index({ components, categories, types, filters, canManage }: IndexProps) {
    const applyFilter = (type: string | null) => {
        router.get('/components', type ? { type } : {}, { preserveState: true })
    }

    return <AppLayout breadcrumbs={[{ label: 'Bibliothèque de composants' }]}
        actions={canManage ? <><Link href="/components/categories" className="rf-button rf-button--secondary rf-button--small"><ListTree size={15} />Catégories</Link><Link href="/components/create" className="rf-button rf-button--primary rf-button--small"><Plus size={15} />Nouveau composant</Link></> : undefined}>
        <Head title="Bibliothèque de composants" />
        <section className="rf-page-intro"><div><p className="rf-eyebrow">Ressources partagées</p><h1>Bibliothèque de composants</h1><p>Le catalogue transversal de microcontrôleurs, capteurs, actionneurs et sources d’énergie utilisés dans vos projets.</p></div></section>

        <div className="rf-panel">
            <div className="rf-toolbar">
                <form onSubmit={(e: FormEvent<HTMLFormElement>) => { e.preventDefault(); router.get('/components', { ...filters, search: (new FormData(e.currentTarget).get('search') as string) || undefined }, { preserveState: true }) }}>
                    <Search size={14} />
                    <input type="text" name="search" placeholder="Rechercher un composant…" defaultValue={filters.search ?? ''} />
                </form>
            </div>

            <div className="rf-filter-row" style={{ marginBottom: 16 }}>
                <button type="button" className={`rf-filter-chip ${!filters.type ? 'is-active' : ''}`} onClick={() => applyFilter(null)}>Tous</button>
                {types.map((type) => (
                    <button key={type} type="button" className={`rf-filter-chip ${filters.type === type ? 'is-active' : ''}`} onClick={() => applyFilter(type)}>{componentTypeLabels[type]}</button>
                ))}
            </div>

            <div className="rf-member-list">
                {components.map((component) => (
                    <Link key={component.id} href={`/components/${component.id}`} className="rf-member-row" style={{ textDecoration: 'none', color: 'inherit' }}>
                        <div className="rf-member-identity">
                            <img className="rf-component-thumb" src={component.image_url ?? PLACEHOLDER_IMAGE} alt="" />
                            <div>
                                <strong>{component.name}{!component.is_active && ' (inactif)'}</strong>
                                <span>{component.manufacturer ?? 'Fabricant inconnu'} · {component.category ? componentTypeLabels[component.category.type] : '—'} · {component.category?.name}</span>
                            </div>
                        </div>
                    </Link>
                ))}
                {components.length === 0 && <div className="rf-empty"><p>Aucun composant trouvé.</p></div>}
            </div>
        </div>

        {categories.length === 0 && canManage && (
            <p style={{ marginTop: 16, color: 'var(--rf-text-muted)', fontSize: 13 }}>Aucune catégorie n’existe encore — <Link href="/components/categories" className="rf-text-link">créez-en une</Link> avant d’ajouter un composant.</p>
        )}
    </AppLayout>
}
