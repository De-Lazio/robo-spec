import InputError from '@/Components/InputError'
import AppLayout from '@/Layouts/AppLayout'
import { componentTypeLabels } from '@/types/components'
import type { ComponentCategory, ComponentType } from '@/types/components'
import { Head, router, useForm } from '@inertiajs/react'
import { Plus, Trash2 } from 'lucide-react'
import type { FormEvent } from 'react'
import { useState } from 'react'

interface CategoriesIndexProps {
    categories: ComponentCategory[]
    types: ComponentType[]
    canManage: boolean
}

export default function CategoriesIndex({ categories, types, canManage }: CategoriesIndexProps) {
    const form = useForm({ type: types[0] ?? 'sensor', name: '' })
    const [renaming, setRenaming] = useState<Record<number, string>>({})

    const submitCategory = (event: FormEvent) => {
        event.preventDefault()
        form.post('/components/categories', { onSuccess: () => form.reset('name') })
    }

    const renameCategory = (category: ComponentCategory) => {
        const name = renaming[category.id]
        if (name && name.trim() !== '' && name !== category.name) {
            router.put(`/components/categories/${category.id}`, { name })
        }
    }

    const deleteCategory = (category: ComponentCategory) => {
        if (window.confirm(`Supprimer la catégorie « ${category.name} » ?`)) {
            router.delete(`/components/categories/${category.id}`)
        }
    }

    return <AppLayout breadcrumbs={[{ label: 'Bibliothèque', href: '/components' }, { label: 'Catégories' }]}>
        <Head title="Catégories de composants" />
        <section className="rf-page-intro"><p className="rf-eyebrow">Bibliothèque</p><h1>Catégories</h1><p>Organisez le catalogue par type de composant.</p></section>

        {canManage && (
            <div className="rf-panel">
                <h2>Nouvelle catégorie</h2>
                <form className="rf-form" onSubmit={submitCategory}>
                    <div className="rf-form-grid">
                        <label>Type<select value={form.data.type} onChange={(e) => form.setData('type', e.target.value as ComponentType)}>{types.map((type) => <option key={type} value={type}>{componentTypeLabels[type]}</option>)}</select><InputError message={form.errors.type} /></label>
                        <label>Nom<input value={form.data.name} onChange={(e) => form.setData('name', e.target.value)} required /><InputError message={form.errors.name} /></label>
                    </div>
                    <button type="submit" className="rf-button rf-button--primary" disabled={form.processing}><Plus size={15} />{form.processing ? 'Ajout…' : 'Ajouter'}</button>
                </form>
            </div>
        )}

        {types.map((type) => {
            const typeCategories = categories.filter((c) => c.type === type)
            if (typeCategories.length === 0) return null
            return (
                <div className="rf-panel" key={type}>
                    <h2>{componentTypeLabels[type]}</h2>
                    <div className="rf-member-list">
                        {typeCategories.map((category) => (
                            <div className="rf-member-row" key={category.id}>
                                <div className="rf-member-identity">
                                    <div>
                                        {canManage ? (
                                            <input className="rf-input" defaultValue={category.name} onChange={(e) => setRenaming((prev) => ({ ...prev, [category.id]: e.target.value }))} onBlur={() => renameCategory(category)} />
                                        ) : (
                                            <strong>{category.name}</strong>
                                        )}
                                        <span>{category.components_count} composant{category.components_count > 1 ? 's' : ''}</span>
                                    </div>
                                </div>
                                {canManage && (
                                    <div className="rf-member-actions">
                                        <button type="button" className="rf-button rf-button--secondary rf-button--small" onClick={() => deleteCategory(category)} aria-label="Supprimer cette catégorie" disabled={category.components_count > 0}><Trash2 size={14} /></button>
                                    </div>
                                )}
                            </div>
                        ))}
                    </div>
                </div>
            )
        })}
        {categories.length === 0 && <div className="rf-empty"><p>Aucune catégorie pour le moment.</p></div>}
    </AppLayout>
}
