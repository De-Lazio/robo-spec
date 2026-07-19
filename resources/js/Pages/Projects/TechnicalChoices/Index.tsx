import InputError from '@/Components/InputError'
import LocalComponentModal from '@/Components/technicalChoices/LocalComponentModal'
import AppLayout from '@/Layouts/AppLayout'
import { componentTypeLabels } from '@/types/components'
import type { ComponentCategoryOption, ComponentType, LibraryComponent } from '@/types/components'
import type { AvailableComponent, CdcFunction, ProjectComponentChoice } from '@/types/technicalChoices'
import { Head, router, useForm } from '@inertiajs/react'
import { Cpu, Pencil, Plus, Trash2 } from 'lucide-react'
import { useState } from 'react'
import type { FormEvent } from 'react'

const PLACEHOLDER_IMAGE = '/images/component-placeholder.svg'

interface TechnicalChoicesIndexProps {
    project: { id: string; name: string }
    choices: ProjectComponentChoice[]
    totalCostCents: number
    availableComponents: AvailableComponent[]
    localComponents: LibraryComponent[]
    componentCategories: ComponentCategoryOption[]
    componentTypes: ComponentType[]
    functions: CdcFunction[]
    canManage: boolean
}

function formatPrice(cents: number, currency = 'EUR'): string {
    return `${(cents / 100).toFixed(2)} ${currency}`
}

export default function Index({ project, choices, totalCostCents, availableComponents, localComponents, componentCategories, componentTypes, functions, canManage }: TechnicalChoicesIndexProps) {
    const form = useForm({
        component_id: availableComponents[0]?.id ?? '',
        quantity: '1',
        rationale: '',
        linked_function_ids: [] as string[],
    })

    const [localComponentModal, setLocalComponentModal] = useState<{ component?: LibraryComponent } | null>(null)

    const removeLocalComponent = (component: LibraryComponent) => {
        if (window.confirm(`Supprimer définitivement le composant local « ${component.name} » ?`)) {
            router.delete(`/projects/${project.id}/local-components/${component.id}`)
        }
    }

    const submit = (event: FormEvent) => {
        event.preventDefault()
        form.post(`/projects/${project.id}/technical-choices`, { onSuccess: () => form.reset('rationale', 'linked_function_ids') })
    }

    const toggleFunction = (id: string) => {
        form.setData('linked_function_ids', form.data.linked_function_ids.includes(id)
            ? form.data.linked_function_ids.filter((f) => f !== id)
            : [...form.data.linked_function_ids, id])
    }

    const removeChoice = (choice: ProjectComponentChoice) => {
        if (window.confirm(`Retirer « ${choice.component.name} » des choix techniques ?`)) {
            router.delete(`/projects/${project.id}/technical-choices/${choice.id}`)
        }
    }

    const grouped = choices.reduce<Record<string, ProjectComponentChoice[]>>((acc, choice) => {
        const type = choice.component.category.type
        acc[type] = acc[type] ?? []
        acc[type].push(choice)
        return acc
    }, {})

    return (
        <AppLayout project={project} breadcrumbs={[{ label: 'Mes projets', href: '/projects' }, { label: project.name, href: `/projects/${project.id}` }, { label: 'Choix techniques' }]}>
            <Head title={`Choix techniques · ${project.name}`} />
            <section className="rf-page-intro"><p className="rf-eyebrow">Projet</p><h1>Choix techniques</h1><p>La nomenclature de <strong>{project.name}</strong>, construite depuis la bibliothèque de composants.</p></section>

            <div className="rf-panel" style={{ marginBottom: 18 }}>
                <h2>Coût total estimé</h2>
                <p style={{ fontSize: 22, fontWeight: 800, color: 'var(--rf-primary)', margin: 0 }}>{formatPrice(totalCostCents)}</p>
            </div>

            {canManage && (
                <div className="rf-panel">
                    <div className="rf-section-heading" style={{ margin: '0 0 12px' }}>
                        <h2 style={{ margin: 0 }}>Ajouter un composant</h2>
                        <button type="button" className="rf-button rf-button--secondary rf-button--small" onClick={() => setLocalComponentModal({})}><Plus size={15} />Nouveau composant local</button>
                    </div>
                    {availableComponents.length === 0 ? (
                        <p style={{ color: 'var(--rf-text-muted)', fontSize: 13 }}>Aucun composant actif dans la bibliothèque pour le moment.</p>
                    ) : (
                        <form className="rf-form" onSubmit={submit}>
                            <div className="rf-form-grid">
                                <label>
                                    Composant
                                    <select value={form.data.component_id} onChange={(e) => form.setData('component_id', e.target.value)}>
                                        {Object.keys(componentTypeLabels).map((type) => {
                                            const options = availableComponents.filter((c) => c.category.type === type)
                                            if (options.length === 0) return null
                                            return <optgroup key={type} label={componentTypeLabels[type as keyof typeof componentTypeLabels]}>{options.map((c) => <option key={c.id} value={c.id}>{c.name}{c.manufacturer ? ` (${c.manufacturer})` : ''}</option>)}</optgroup>
                                        })}
                                        {availableComponents.some((c) => c.owner_project_id === project.id) && (
                                            <optgroup label="Composants locaux à ce projet">
                                                {availableComponents.filter((c) => c.owner_project_id === project.id).map((c) => <option key={c.id} value={c.id}>{c.name}{c.manufacturer ? ` (${c.manufacturer})` : ''}</option>)}
                                            </optgroup>
                                        )}
                                    </select>
                                    <InputError message={form.errors.component_id} />
                                </label>
                                <label>Quantité<input type="number" min={1} value={form.data.quantity} onChange={(e) => form.setData('quantity', e.target.value)} /><InputError message={form.errors.quantity} /></label>
                            </div>
                            {functions.length > 0 && (
                                <label>
                                    Fonctions liées (étape 5 du CDC)
                                    <div className="rf-tags">
                                        {functions.map((fn) => (
                                            <span key={fn.id} onClick={() => toggleFunction(fn.id)} style={{ cursor: 'pointer', background: form.data.linked_function_ids.includes(fn.id) ? 'var(--rf-primary)' : undefined, color: form.data.linked_function_ids.includes(fn.id) ? '#fff' : undefined }}>
                                                {fn.id} — {fn.name || 'Sans nom'}
                                            </span>
                                        ))}
                                    </div>
                                </label>
                            )}
                            <label>Justification (optionnel)<textarea value={form.data.rationale} onChange={(e) => form.setData('rationale', e.target.value)} rows={2} /><InputError message={form.errors.rationale} /></label>
                            <button type="submit" className="rf-button rf-button--primary" disabled={form.processing}><Plus size={15} />{form.processing ? 'Ajout…' : 'Ajouter'}</button>
                        </form>
                    )}
                </div>
            )}

            {canManage && localComponents.length > 0 && (
                <div className="rf-panel">
                    <h2>Composants locaux à ce projet</h2>
                    <div className="rf-member-list">
                        {localComponents.map((component) => (
                            <div className="rf-member-row" key={component.id}>
                                <div className="rf-member-identity">
                                    <img className="rf-component-thumb" src={component.image_url ?? PLACEHOLDER_IMAGE} alt="" />
                                    <div>
                                        <strong>{component.name}</strong>
                                        <span>
                                            {component.manufacturer}
                                            {component.price_cents !== null && ` · ${formatPrice(component.price_cents, component.currency ?? 'EUR')}`}
                                        </span>
                                    </div>
                                </div>
                                <div className="rf-member-actions">
                                    <button type="button" className="rf-button rf-button--secondary rf-button--small" onClick={() => setLocalComponentModal({ component })} aria-label="Modifier ce composant local"><Pencil size={14} /></button>
                                    <button type="button" className="rf-button rf-button--secondary rf-button--small" onClick={() => removeLocalComponent(component)} aria-label="Supprimer ce composant local"><Trash2 size={14} /></button>
                                </div>
                            </div>
                        ))}
                    </div>
                </div>
            )}

            {Object.entries(grouped).map(([type, items]) => (
                <div className="rf-panel" key={type}>
                    <h2>{componentTypeLabels[type as keyof typeof componentTypeLabels]}</h2>
                    <div className="rf-member-list">
                        {items.map((choice) => (
                            <div className="rf-member-row" key={choice.id}>
                                <div className="rf-member-identity">
                                    <img className="rf-component-thumb" src={choice.component.image_url ?? PLACEHOLDER_IMAGE} alt="" />
                                    <div>
                                        <strong>{choice.component.name}{choice.component.owner_project_id === project.id && ' (local)'}{!choice.component.is_active && ' (retiré du catalogue)'}</strong>
                                        <span>
                                            Qté {choice.quantity}
                                            {choice.component.price_cents !== null && ` · ${formatPrice(choice.component.price_cents * choice.quantity, choice.component.currency ?? 'EUR')}`}
                                            {choice.linked_function_ids.length > 0 && ` · ${choice.linked_function_ids.join(', ')}`}
                                        </span>
                                        {choice.rationale && <span>{choice.rationale}</span>}
                                    </div>
                                </div>
                                {canManage && (
                                    <div className="rf-member-actions">
                                        <button type="button" className="rf-button rf-button--secondary rf-button--small" onClick={() => removeChoice(choice)} aria-label="Retirer ce composant"><Trash2 size={14} /></button>
                                    </div>
                                )}
                            </div>
                        ))}
                    </div>
                </div>
            ))}
            {choices.length === 0 && <div className="rf-empty"><Cpu size={28} /><h2>Aucun choix technique</h2><p>Ajoutez des composants depuis la bibliothèque pour construire la nomenclature du projet.</p></div>}

            {localComponentModal && (
                <LocalComponentModal
                    show
                    onClose={() => setLocalComponentModal(null)}
                    projectId={project.id}
                    categories={componentCategories}
                    types={componentTypes}
                    component={localComponentModal.component}
                />
            )}
        </AppLayout>
    )
}
