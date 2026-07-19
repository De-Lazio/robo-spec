import InputError from '@/Components/InputError'
import type { Organization } from '@/types/organizations'
import { useForm } from '@inertiajs/react'
import { FormEvent } from 'react'

interface OrganizationFormProps {
    organization?: Organization
    submitLabel: string
    onSubmit: (form: ReturnType<typeof useForm>) => void
}

export default function OrganizationForm({ organization, submitLabel, onSubmit }: OrganizationFormProps) {
    const form = useForm({
        name: organization?.name ?? '',
        description: organization?.description ?? '',
    })
    const submit = (event: FormEvent) => { event.preventDefault(); onSubmit(form) }
    return <form className="rf-form" onSubmit={submit}>
        <label>Nom de l’organisation<input value={form.data.name} onChange={(event) => form.setData('name', event.target.value)} required autoFocus /><InputError message={form.errors.name} /></label>
        <label>Description<textarea value={form.data.description} onChange={(event) => form.setData('description', event.target.value)} rows={4} placeholder="Équipe, club, laboratoire…" /><InputError message={form.errors.description} /></label>
        <button type="submit" className="rf-button rf-button--primary" disabled={form.processing}>{form.processing ? 'Enregistrement…' : submitLabel}</button>
    </form>
}
