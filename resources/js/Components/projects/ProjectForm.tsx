import InputError from '@/Components/InputError'
import type { Project, ProjectStatus, RobotType } from '@/types/projects'
import { useForm } from '@inertiajs/react'
import { FormEvent, useState } from 'react'

interface ProjectFormProps {
    project?: Project
    robotTypes: RobotType[]
    statuses?: ProjectStatus[]
    submitLabel: string
    onSubmit: (form: ReturnType<typeof useForm>) => void
}

const typeLabels: Record<RobotType, string> = { mobile: 'Robot mobile', arm: 'Bras robotisé', drone: 'Drone', fixed: 'Robot fixe', humanoid: 'Humanoïde', other: 'Autre' }
const statusLabels: Record<ProjectStatus, string> = { draft: 'Brouillon', in_progress: 'En cours', testing: 'Tests', completed: 'Terminé', archived: 'Archivé' }

export default function ProjectForm({ project, robotTypes, statuses, submitLabel, onSubmit }: ProjectFormProps) {
    const form = useForm({
        name: project?.name ?? '', description: project?.description ?? '', robot_type: project?.robot_type ?? 'mobile' as RobotType,
        domain: project?.domain ?? '', status: project?.status ?? 'draft' as ProjectStatus, tags: project?.tags ?? [] as string[],
    })
    const [tagValue, setTagValue] = useState('')
    const addTag = () => {
        const tag = tagValue.trim()
        if (tag && !form.data.tags.some((item) => item.toLocaleLowerCase() === tag.toLocaleLowerCase()) && form.data.tags.length < 12) form.setData('tags', [...form.data.tags, tag])
        setTagValue('')
    }
    const submit = (event: FormEvent) => { event.preventDefault(); onSubmit(form) }
    return <form className="rf-form" onSubmit={submit}>
        <label>Nom du projet<input value={form.data.name} onChange={(event) => form.setData('name', event.target.value)} required autoFocus /><InputError message={form.errors.name} /></label>
        <label>Description<textarea value={form.data.description} onChange={(event) => form.setData('description', event.target.value)} rows={5} placeholder="Objectif, contexte et périmètre du projet…" /><InputError message={form.errors.description} /></label>
        <div className="rf-form-grid">
            <label>Type de robot<select value={form.data.robot_type} onChange={(event) => form.setData('robot_type', event.target.value as RobotType)}>{robotTypes.map((type) => <option key={type} value={type}>{typeLabels[type]}</option>)}</select><InputError message={form.errors.robot_type} /></label>
            <label>Domaine<input value={form.data.domain} onChange={(event) => form.setData('domain', event.target.value)} placeholder="Industrie, agriculture…" /><InputError message={form.errors.domain} /></label>
        </div>
        {statuses && <label>Statut<select value={form.data.status} onChange={(event) => form.setData('status', event.target.value as ProjectStatus)}>{statuses.map((status) => <option key={status} value={status}>{statusLabels[status]}</option>)}</select><InputError message={form.errors.status} /></label>}
        <label>Technologies et tags<div className="rf-tag-editor"><div>{form.data.tags.map((tag) => <button type="button" key={tag} onClick={() => form.setData('tags', form.data.tags.filter((item) => item !== tag))}>{tag} ×</button>)}</div><input value={tagValue} onChange={(event) => setTagValue(event.target.value)} onKeyDown={(event) => { if (event.key === 'Enter') { event.preventDefault(); addTag() } }} onBlur={addTag} placeholder="ESP32, LIDAR…" /></div><small>Appuyez sur Entrée pour ajouter un tag (12 maximum).</small><InputError message={form.errors.tags} /></label>
        <button type="submit" className="rf-button rf-button--primary" disabled={form.processing}>{form.processing ? 'Enregistrement…' : submitLabel}</button>
    </form>
}
