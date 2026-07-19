import InputError from '@/Components/InputError'
import AppLayout from '@/Layouts/AppLayout'
import type { AlgorithmDiagramSummary, DiagramFormalism } from '@/types/algorithmDiagrams'
import { FORMALISM_LABELS } from '@/types/algorithmDiagrams'
import { Head, Link, router, useForm } from '@inertiajs/react'
import { Check, Plus, Trash2, Workflow } from 'lucide-react'
import type { FormEvent } from 'react'

interface AlgorithmDiagramsIndexProps {
    project: { id: string; name: string }
    diagrams: AlgorithmDiagramSummary[]
    canManage: boolean
}

const FORMALISM_DESCRIPTIONS: Record<DiagramFormalism, string> = {
    algorigramme: 'Algorithme classique : Début/Fin, actions, tests, boucles.',
    grafcet: 'Comportement séquentiel et parallèle : Étapes, Transitions, divergences ET/OU.',
}

export default function Index({ project, diagrams, canManage }: AlgorithmDiagramsIndexProps) {
    const form = useForm<{ name: string; formalism: DiagramFormalism }>({ name: '', formalism: 'algorigramme' })

    const submit = (event: FormEvent) => {
        event.preventDefault()
        form.post(`/projects/${project.id}/algorithm-diagrams`)
    }

    const deleteDiagram = (diagram: AlgorithmDiagramSummary) => {
        if (window.confirm(`Supprimer le diagramme « ${diagram.name} » ?`)) {
            router.delete(`/projects/${project.id}/algorithm-diagrams/${diagram.id}`)
        }
    }

    return (
        <AppLayout project={project} breadcrumbs={[{ label: 'Mes projets', href: '/projects' }, { label: project.name, href: `/projects/${project.id}` }, { label: 'Algorigrammes' }]}>
            <Head title={`Algorigrammes · ${project.name}`} />
            <section className="rf-page-intro"><p className="rf-eyebrow">Projet</p><h1>Algorigrammes</h1><p>Décrivez le comportement de <strong>{project.name}</strong> de façon graphique.</p></section>

            {canManage && (
                <div className="rf-panel" style={{ marginBottom: 18 }}>
                    <h2>Nouveau diagramme</h2>
                    <form className="rf-form" onSubmit={submit}>
                        <label>Nom<input className="rf-input" value={form.data.name} onChange={(e) => form.setData('name', e.target.value)} placeholder="Ex. Séquence principale" /><InputError message={form.errors.name} /></label>
                        <div>
                            <div className="rf-label">Formalisme</div>
                            <div className="rf-choice-grid" style={{ ['--rf-choice-cols' as string]: 2 }}>
                                {(Object.keys(FORMALISM_LABELS) as DiagramFormalism[]).map((formalism) => {
                                    const checked = form.data.formalism === formalism
                                    return (
                                        <label key={formalism} className={`rf-choice-card ${checked ? 'is-checked' : ''}`} style={{ alignItems: 'flex-start' }}>
                                            <span className="rf-choice-mark rf-choice-mark--round">{checked && <Check size={12} strokeWidth={3} />}</span>
                                            <input type="radio" className="rf-choice-input" checked={checked} onChange={() => form.setData('formalism', formalism)} />
                                            <span>
                                                <strong style={{ display: 'block' }}>{FORMALISM_LABELS[formalism]}</strong>
                                                <span style={{ fontWeight: 400 }}>{FORMALISM_DESCRIPTIONS[formalism]}</span>
                                            </span>
                                        </label>
                                    )
                                })}
                            </div>
                            <InputError message={form.errors.formalism} />
                        </div>
                        <button type="submit" className="rf-button rf-button--primary" style={{ justifySelf: 'start' }} disabled={form.processing}><Plus size={15} />{form.processing ? 'Création…' : 'Créer'}</button>
                    </form>
                </div>
            )}

            <div className="rf-panel">
                <h2>Diagrammes du projet</h2>
                <div className="rf-member-list">
                    {diagrams.map((diagram) => (
                        <div className="rf-member-row" key={diagram.id}>
                            <Link href={`/projects/${project.id}/algorithm-diagrams/${diagram.id}/edit`} className="rf-member-identity" style={{ textDecoration: 'none', color: 'inherit' }}>
                                <div className="rf-resource-icon"><Workflow size={16} /></div>
                                <div>
                                    <strong>{diagram.name}</strong>
                                    <span>{FORMALISM_LABELS[diagram.formalism]}{diagram.updated_at ? ` · modifié le ${new Intl.DateTimeFormat('fr-FR', { dateStyle: 'medium' }).format(new Date(diagram.updated_at))}` : ''}</span>
                                </div>
                            </Link>
                            {canManage && (
                                <div className="rf-member-actions">
                                    <button type="button" className="rf-button rf-button--secondary rf-button--small" onClick={() => deleteDiagram(diagram)} aria-label="Supprimer ce diagramme"><Trash2 size={14} /></button>
                                </div>
                            )}
                        </div>
                    ))}
                </div>
                {diagrams.length === 0 && (
                    <div className="rf-empty">
                        <Workflow size={28} />
                        <h2>Aucun diagramme</h2>
                        <p>Créez un premier algorigramme pour décrire le comportement du robot.</p>
                    </div>
                )}
            </div>
        </AppLayout>
    )
}
