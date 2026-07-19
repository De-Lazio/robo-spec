import InputError from '@/Components/InputError'
import AppLayout from '@/Layouts/AppLayout'
import { TASK_STATUSES, taskStatusLabels } from '@/types/tasks'
import type { AssignableUser, CdcFunction, Task, TaskStatus } from '@/types/tasks'
import { Head, router, useForm } from '@inertiajs/react'
import { CalendarDays, Kanban, Pencil, Plus, Trash2, User as UserIcon, X } from 'lucide-react'
import { useState, type DragEvent, type FormEvent } from 'react'

interface TasksIndexProps {
    project: { id: string; name: string }
    tasks: Task[]
    assignableUsers: AssignableUser[]
    functions: CdcFunction[]
    canManage: boolean
}

export default function Index({ project, tasks, assignableUsers, functions, canManage }: TasksIndexProps) {
    const [draggingId, setDraggingId] = useState<number | null>(null)

    const form = useForm({
        title: '',
        description: '',
        assignee_id: '' as number | '',
        due_date: '',
        linked_function_ids: [] as string[],
    })

    const submit = (event: FormEvent) => {
        event.preventDefault()
        form.post(`/projects/${project.id}/tasks`, { onSuccess: () => form.reset() })
    }

    const toggleFunction = (id: string) => {
        form.setData('linked_function_ids', form.data.linked_function_ids.includes(id)
            ? form.data.linked_function_ids.filter((f) => f !== id)
            : [...form.data.linked_function_ids, id])
    }

    const deleteTask = (task: Task) => {
        if (window.confirm(`Supprimer la tâche « ${task.title} » ?`)) {
            router.delete(`/projects/${project.id}/tasks/${task.id}`)
        }
    }

    const onDrop = (status: TaskStatus) => (event: DragEvent) => {
        event.preventDefault()
        if (draggingId === null) return
        router.patch(`/projects/${project.id}/tasks/${draggingId}/status`, { status }, { preserveScroll: true })
        setDraggingId(null)
    }

    return (
        <AppLayout project={project} breadcrumbs={[{ label: 'Mes projets', href: '/projects' }, { label: project.name, href: `/projects/${project.id}` }, { label: 'Tâches' }]}>
            <Head title={`Tâches · ${project.name}`} />
            <section className="rf-page-intro"><p className="rf-eyebrow">Projet</p><h1>Tâches</h1><p>Le suivi de l'avancement de <strong>{project.name}</strong>, organisé en Kanban.</p></section>

            {canManage && (
                <div className="rf-panel" style={{ marginBottom: 18 }}>
                    <h2>Nouvelle tâche</h2>
                    <form className="rf-form" onSubmit={submit}>
                        <label>Titre<input className="rf-input" value={form.data.title} onChange={(e) => form.setData('title', e.target.value)} /><InputError message={form.errors.title} /></label>
                        <label>Description (optionnel)<textarea className="rf-input" rows={2} value={form.data.description} onChange={(e) => form.setData('description', e.target.value)} /><InputError message={form.errors.description} /></label>
                        <div className="rf-form-grid">
                            <label>
                                Assigné à
                                <select className="rf-select" value={form.data.assignee_id} onChange={(e) => form.setData('assignee_id', e.target.value ? Number(e.target.value) : '')}>
                                    <option value="">Non assigné</option>
                                    {assignableUsers.map((user) => <option key={user.id} value={user.id}>{user.name}</option>)}
                                </select>
                                <InputError message={form.errors.assignee_id} />
                            </label>
                            <label>Échéance<input className="rf-input" type="date" value={form.data.due_date} onChange={(e) => form.setData('due_date', e.target.value)} /><InputError message={form.errors.due_date} /></label>
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
                        <button type="submit" className="rf-button rf-button--primary" disabled={form.processing}><Plus size={15} />{form.processing ? 'Création…' : 'Créer la tâche'}</button>
                    </form>
                </div>
            )}

            <div className="rf-kanban">
                {TASK_STATUSES.map((status) => (
                    <div key={status} className="rf-kanban-column" onDragOver={(e) => e.preventDefault()} onDrop={onDrop(status)}>
                        <h2><Kanban size={16} /> {taskStatusLabels[status]}</h2>
                        <div className="rf-kanban-cards">
                            {tasks.filter((task) => task.status === status).map((task) => (
                                <TaskCard
                                    key={task.id}
                                    task={task}
                                    projectId={project.id}
                                    canManage={canManage}
                                    assignableUsers={assignableUsers}
                                    functions={functions}
                                    onDelete={() => deleteTask(task)}
                                    onDragStart={() => setDraggingId(task.id)}
                                />
                            ))}
                            {tasks.filter((task) => task.status === status).length === 0 && (
                                <p className="rf-field-hint">Aucune tâche.</p>
                            )}
                        </div>
                    </div>
                ))}
            </div>
        </AppLayout>
    )
}

function TaskCard({ task, projectId, canManage, assignableUsers, functions, onDelete, onDragStart }: {
    task: Task
    projectId: string
    canManage: boolean
    assignableUsers: AssignableUser[]
    functions: CdcFunction[]
    onDelete: () => void
    onDragStart: () => void
}) {
    const [editing, setEditing] = useState(false)

    const form = useForm({
        title: task.title,
        description: task.description ?? '',
        assignee_id: (task.assignee?.id ?? '') as number | '',
        due_date: task.due_date ?? '',
        linked_function_ids: task.linked_function_ids,
    })

    const toggleFunction = (id: string) => {
        form.setData('linked_function_ids', form.data.linked_function_ids.includes(id)
            ? form.data.linked_function_ids.filter((f) => f !== id)
            : [...form.data.linked_function_ids, id])
    }

    const save = (event: FormEvent) => {
        event.preventDefault()
        form.put(`/projects/${projectId}/tasks/${task.id}`, { onSuccess: () => setEditing(false) })
    }

    if (editing) {
        return (
            <div className="rf-kanban-card">
                <form className="rf-form" onSubmit={save}>
                    <input className="rf-input" value={form.data.title} onChange={(e) => form.setData('title', e.target.value)} />
                    <InputError message={form.errors.title} />
                    <textarea className="rf-input" rows={2} value={form.data.description} onChange={(e) => form.setData('description', e.target.value)} />
                    <select className="rf-select" value={form.data.assignee_id} onChange={(e) => form.setData('assignee_id', e.target.value ? Number(e.target.value) : '')}>
                        <option value="">Non assigné</option>
                        {assignableUsers.map((user) => <option key={user.id} value={user.id}>{user.name}</option>)}
                    </select>
                    <input className="rf-input" type="date" value={form.data.due_date} onChange={(e) => form.setData('due_date', e.target.value)} />
                    {functions.length > 0 && (
                        <div className="rf-tags">
                            {functions.map((fn) => (
                                <span key={fn.id} onClick={() => toggleFunction(fn.id)} style={{ cursor: 'pointer', background: form.data.linked_function_ids.includes(fn.id) ? 'var(--rf-primary)' : undefined, color: form.data.linked_function_ids.includes(fn.id) ? '#fff' : undefined }}>
                                    {fn.id}
                                </span>
                            ))}
                        </div>
                    )}
                    <div style={{ display: 'flex', gap: 8 }}>
                        <button type="submit" className="rf-button rf-button--primary rf-button--small" disabled={form.processing}>Enregistrer</button>
                        <button type="button" className="rf-button rf-button--secondary rf-button--small" onClick={() => setEditing(false)}><X size={14} /></button>
                    </div>
                </form>
            </div>
        )
    }

    return (
        <div className="rf-kanban-card" draggable={canManage} onDragStart={onDragStart}>
            <strong>{task.title}</strong>
            {task.description && <p style={{ fontSize: 12.5, color: 'var(--rf-text-muted)', margin: '4px 0' }}>{task.description}</p>}
            <div className="rf-kanban-card-meta">
                {task.assignee && <span><UserIcon size={12} /> {task.assignee.name}</span>}
                {task.due_date && <span><CalendarDays size={12} /> {new Intl.DateTimeFormat('fr-FR', { dateStyle: 'short' }).format(new Date(task.due_date))}</span>}
            </div>
            {task.linked_function_ids.length > 0 && (
                <div className="rf-tags">{task.linked_function_ids.map((id) => <span key={id}>{id}</span>)}</div>
            )}
            {canManage && (
                <div className="rf-kanban-card-actions">
                    <button type="button" className="rf-button rf-button--secondary rf-button--small" onClick={() => setEditing(true)} aria-label="Modifier"><Pencil size={13} /></button>
                    <button type="button" className="rf-button rf-button--secondary rf-button--small" onClick={onDelete} aria-label="Supprimer"><Trash2 size={13} /></button>
                </div>
            )}
        </div>
    )
}
