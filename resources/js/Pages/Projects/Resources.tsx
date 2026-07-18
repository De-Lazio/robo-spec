import InputError from '@/Components/InputError'
import AppLayout from '@/Layouts/AppLayout'
import { categoryLabels, kindLabels } from '@/types/projects'
import type { Resource, ResourceCategory, ResourceKind } from '@/types/projects'
import { Head, router, useForm } from '@inertiajs/react'
import { Archive, Code2, Cpu, Download, Eye, File as FileIcon, FileImage, FileText, Search, Trash2, Upload, Zap } from 'lucide-react'
import type { FormEvent } from 'react'

interface ResourcesProps {
    project: { id: string; name: string }
    resources: Resource[]
    filters: { category?: string; kind?: string; search?: string }
    categories: ResourceCategory[]
    kinds: ResourceKind[]
    canUpload: boolean
}

const kindIcons: Record<ResourceKind, typeof FileIcon> = {
    image: FileImage,
    document: FileText,
    code: Code2,
    cad: Cpu,
    schema: Zap,
    archive: Archive,
    other: FileIcon,
}

function formatSize(bytes: number): string {
    if (bytes < 1024) return `${bytes} o`
    if (bytes < 1024 * 1024) return `${(bytes / 1024).toFixed(0)} Ko`
    return `${(bytes / 1024 / 1024).toFixed(1)} Mo`
}

export default function Resources({ project, resources, filters, categories, canUpload }: ResourcesProps) {
    const form = useForm<{ file: File | null; category: ResourceCategory; description: string }>({
        file: null,
        category: categories[0] ?? 'other',
        description: '',
    })

    const submitUpload = (event: FormEvent) => {
        event.preventDefault()
        form.post(`/projects/${project.id}/resources`, {
            forceFormData: true,
            onSuccess: () => form.reset('file', 'description'),
        })
    }

    const applyFilter = (category: string | null) => {
        router.get(`/projects/${project.id}/resources`, category ? { category } : {}, { preserveState: true })
    }

    const applySearch = (search: string) => {
        router.get(`/projects/${project.id}/resources`, { ...filters, search: search || undefined }, { preserveState: true })
    }

    const deleteResource = (resource: Resource) => {
        if (window.confirm(`Supprimer « ${resource.name} » ?`)) {
            router.delete(`/projects/${project.id}/resources/${resource.id}`)
        }
    }

    return (
        <AppLayout breadcrumbs={[{ label: 'Mes projets', href: '/projects' }, { label: project.name, href: `/projects/${project.id}` }, { label: 'Ressources' }]}>
            <Head title={`Ressources · ${project.name}`} />
            <section className="rf-page-intro"><p className="rf-eyebrow">Projet</p><h1>Ressources</h1><p>Fichiers mécaniques, électroniques et logiciels de <strong>{project.name}</strong>.</p></section>

            {canUpload && (
                <div className="rf-panel">
                    <h2>Ajouter une ressource</h2>
                    <form className="rf-form" onSubmit={submitUpload}>
                        <div className="rf-form-grid">
                            <label>
                                Fichier
                                <input type="file" onChange={(e) => form.setData('file', e.target.files?.[0] ?? null)} required />
                                <InputError message={form.errors.file} />
                            </label>
                            <label>
                                Catégorie
                                <select value={form.data.category} onChange={(e) => form.setData('category', e.target.value as ResourceCategory)}>
                                    {categories.map((category) => <option key={category} value={category}>{categoryLabels[category]}</option>)}
                                </select>
                                <InputError message={form.errors.category} />
                            </label>
                        </div>
                        <label>
                            Description (optionnel)
                            <input type="text" value={form.data.description} onChange={(e) => form.setData('description', e.target.value)} maxLength={500} />
                            <InputError message={form.errors.description} />
                        </label>
                        <button type="submit" className="rf-button rf-button--primary" disabled={form.processing}><Upload size={15} />{form.processing ? 'Envoi…' : 'Envoyer'}</button>
                    </form>
                </div>
            )}

            <div className="rf-panel">
                <div className="rf-toolbar">
                    <form onSubmit={(e) => { e.preventDefault(); applySearch((new FormData(e.currentTarget).get('search') as string) ?? '') }}>
                        <Search size={14} />
                        <input type="text" name="search" placeholder="Rechercher un fichier..." defaultValue={filters.search ?? ''} />
                    </form>
                </div>

                <div className="rf-filter-row" style={{ marginBottom: 16 }}>
                    <button type="button" className={`rf-filter-chip ${!filters.category ? 'is-active' : ''}`} onClick={() => applyFilter(null)}>Toutes</button>
                    {categories.map((category) => (
                        <button key={category} type="button" className={`rf-filter-chip ${filters.category === category ? 'is-active' : ''}`} onClick={() => applyFilter(category)}>
                            {categoryLabels[category]}
                        </button>
                    ))}
                </div>

                <div className="rf-member-list">
                    {resources.map((resource) => {
                        const Icon = kindIcons[resource.kind]
                        return (
                            <div className="rf-member-row" key={resource.id}>
                                <div className="rf-member-identity">
                                    <div className="rf-resource-icon"><Icon size={16} /></div>
                                    <div>
                                        <strong>{resource.name}</strong>
                                        <span>{formatSize(resource.size_bytes)} · {kindLabels[resource.kind]} · Ajouté par {resource.uploader?.name ?? '—'}</span>
                                        {resource.description && <span>{resource.description}</span>}
                                    </div>
                                </div>
                                <div className="rf-member-actions">
                                    <span className={`rf-badge rf-badge--${resource.category}`}>{categoryLabels[resource.category]}</span>
                                    {resource.preview_url && (
                                        <a href={resource.preview_url} target="_blank" rel="noreferrer" className="rf-button rf-button--secondary rf-button--small" aria-label="Aperçu"><Eye size={14} /></a>
                                    )}
                                    <a href={resource.download_url} className="rf-button rf-button--secondary rf-button--small" aria-label="Télécharger"><Download size={14} /></a>
                                    {resource.can_delete && (
                                        <button type="button" className="rf-button rf-button--secondary rf-button--small" onClick={() => deleteResource(resource)} aria-label="Supprimer"><Trash2 size={14} /></button>
                                    )}
                                </div>
                            </div>
                        )
                    })}
                    {resources.length === 0 && <div className="rf-empty"><p>Aucune ressource trouvée.</p></div>}
                </div>
            </div>
        </AppLayout>
    )
}
