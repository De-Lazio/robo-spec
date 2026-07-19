import InputError from '@/Components/InputError'
import AppLayout from '@/Layouts/AppLayout'
import { categoryLabels, kindLabels } from '@/types/projects'
import type { Resource, ResourceCategory, ResourceKind } from '@/types/projects'
import { Head, router, useForm } from '@inertiajs/react'
import { Archive, Code2, Cpu, Download, Eye, File as FileIcon, FileImage, FileText, FolderInput, Search, Trash2, Upload, Zap } from 'lucide-react'
import { lazy, Suspense, useMemo, useState } from 'react'
import type { FormEvent } from 'react'

const ModelPreviewModal = lazy(() => import('@/Components/resources/ModelPreviewModal'))

interface ResourcesProps {
    project: { id: string; name: string }
    resources: Resource[]
    filters: { category?: string; kind?: string; search?: string }
    categories: ResourceCategory[]
    kinds: ResourceKind[]
    canUpload: boolean
    folders: Partial<Record<ResourceCategory, string[]>>
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

interface FolderGroup {
    folder: string | null
    items: Resource[]
}

function groupByFolder(resources: Resource[]): FolderGroup[] {
    const map = new Map<string, Resource[]>()
    for (const resource of resources) {
        const key = resource.folder ?? ''
        if (!map.has(key)) map.set(key, [])
        map.get(key)!.push(resource)
    }

    const keys = Array.from(map.keys()).sort((a, b) => (a === '' ? -1 : b === '' ? 1 : a.localeCompare(b)))

    return keys.map((key) => ({ folder: key === '' ? null : key, items: map.get(key)! }))
}

export default function Resources({ project, resources, filters, categories, canUpload, folders }: ResourcesProps) {
    const form = useForm<{ file: File | null; category: ResourceCategory; description: string; folder: string }>({
        file: null,
        category: categories[0] ?? 'other',
        description: '',
        folder: '',
    })

    const [previewResource, setPreviewResource] = useState<Resource | null>(null)
    const [editingFolderId, setEditingFolderId] = useState<string | null>(null)
    const [editingFolderValue, setEditingFolderValue] = useState('')

    const allFolderNames = useMemo(() => Array.from(new Set(Object.values(folders).flat())).sort(), [folders])
    const uploadFolderSuggestions = folders[form.data.category] ?? []

    const submitUpload = (event: FormEvent) => {
        event.preventDefault()
        form.post(`/projects/${project.id}/resources`, {
            forceFormData: true,
            onSuccess: () => form.reset('file', 'description', 'folder'),
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

    const startMoveResource = (resource: Resource) => {
        setEditingFolderId(resource.id)
        setEditingFolderValue(resource.folder ?? '')
    }

    const commitMoveResource = (resource: Resource) => {
        setEditingFolderId(null)
        if (editingFolderValue === (resource.folder ?? '')) return
        router.patch(`/projects/${project.id}/resources/${resource.id}`, { folder: editingFolderValue }, { preserveScroll: true })
    }

    const openPreview = (resource: Resource) => {
        if (resource.preview_kind === 'model') {
            setPreviewResource(resource)
        } else if (resource.preview_url) {
            window.open(resource.preview_url, '_blank', 'noreferrer')
        }
    }

    const renderResourceRow = (resource: Resource) => {
        const Icon = kindIcons[resource.kind]
        const isEditingFolder = editingFolderId === resource.id

        return (
            <div className="rf-member-row" key={resource.id}>
                <div className="rf-member-identity">
                    <div className="rf-resource-icon"><Icon size={16} /></div>
                    <div>
                        <strong>{resource.name}</strong>
                        <span>{formatSize(resource.size_bytes)} · {kindLabels[resource.kind]} · Ajouté par {resource.uploader?.name ?? '—'}</span>
                        {resource.description && <span>{resource.description}</span>}
                        {isEditingFolder && (
                            <span>
                                <input
                                    className="rf-input"
                                    style={{ marginTop: 4 }}
                                    list="folder-suggestions"
                                    autoFocus
                                    value={editingFolderValue}
                                    placeholder="Sans dossier"
                                    onChange={(e) => setEditingFolderValue(e.target.value)}
                                    onBlur={() => commitMoveResource(resource)}
                                    onKeyDown={(e) => {
                                        if (e.key === 'Enter') { e.preventDefault(); commitMoveResource(resource) }
                                        if (e.key === 'Escape') setEditingFolderId(null)
                                    }}
                                />
                            </span>
                        )}
                    </div>
                </div>
                <div className="rf-member-actions">
                    <span className={`rf-badge rf-badge--${resource.category}`}>{categoryLabels[resource.category]}</span>
                    {resource.preview_url && (
                        <button type="button" className="rf-button rf-button--secondary rf-button--small" onClick={() => openPreview(resource)} aria-label="Aperçu"><Eye size={14} /></button>
                    )}
                    <a href={resource.download_url} className="rf-button rf-button--secondary rf-button--small" aria-label="Télécharger"><Download size={14} /></a>
                    {resource.can_update && (
                        <button type="button" className="rf-button rf-button--secondary rf-button--small" onClick={() => startMoveResource(resource)} aria-label="Déplacer vers un dossier"><FolderInput size={14} /></button>
                    )}
                    {resource.can_delete && (
                        <button type="button" className="rf-button rf-button--secondary rf-button--small" onClick={() => deleteResource(resource)} aria-label="Supprimer"><Trash2 size={14} /></button>
                    )}
                </div>
            </div>
        )
    }

    const groups = groupByFolder(resources)

    return (
        <AppLayout breadcrumbs={[{ label: 'Mes projets', href: '/projects' }, { label: project.name, href: `/projects/${project.id}` }, { label: 'Ressources' }]}>
            <Head title={`Ressources · ${project.name}`} />
            <section className="rf-page-intro"><p className="rf-eyebrow">Projet</p><h1>Ressources</h1><p>Fichiers mécaniques, électroniques et logiciels de <strong>{project.name}</strong>.</p></section>

            <datalist id="folder-suggestions">
                {allFolderNames.map((name) => <option key={name} value={name} />)}
            </datalist>

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
                        <div className="rf-form-grid">
                            <label>
                                Dossier (optionnel)
                                <input
                                    type="text"
                                    list="upload-folder-suggestions"
                                    value={form.data.folder}
                                    onChange={(e) => form.setData('folder', e.target.value)}
                                    maxLength={120}
                                    placeholder="Ex. Châssis"
                                />
                                <datalist id="upload-folder-suggestions">
                                    {uploadFolderSuggestions.map((name) => <option key={name} value={name} />)}
                                </datalist>
                                <InputError message={form.errors.folder} />
                            </label>
                            <label>
                                Description (optionnel)
                                <input type="text" value={form.data.description} onChange={(e) => form.setData('description', e.target.value)} maxLength={500} />
                                <InputError message={form.errors.description} />
                            </label>
                        </div>
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
                    {groups.map((group) =>
                        group.folder === null ? (
                            <div key="__ungrouped">{group.items.map(renderResourceRow)}</div>
                        ) : (
                            <details key={group.folder} className="rf-folder-group" open>
                                <summary>{group.folder} <span className="rf-field-hint">({group.items.length})</span></summary>
                                {group.items.map(renderResourceRow)}
                            </details>
                        ),
                    )}
                    {groups.length === 0 && <div className="rf-empty"><p>Aucune ressource trouvée.</p></div>}
                </div>
            </div>

            {previewResource && (
                <Suspense fallback={null}>
                    <ModelPreviewModal
                        show={previewResource !== null}
                        onClose={() => setPreviewResource(null)}
                        name={previewResource.original_name}
                        url={previewResource.preview_url ?? ''}
                    />
                </Suspense>
            )}
        </AppLayout>
    )
}
