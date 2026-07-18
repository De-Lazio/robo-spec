import InputError from '@/Components/InputError'
import DynamicTable from '@/Components/requirements/DynamicTable'
import { componentTypeLabels } from '@/types/components'
import type { ComponentCategoryOption, ComponentType, LibraryComponent } from '@/types/components'
import { useForm } from '@inertiajs/react'
import { FormEvent } from 'react'

interface ComponentFormProps {
    component?: LibraryComponent
    categories: ComponentCategoryOption[]
    types: ComponentType[]
    submitLabel: string
    onSubmit: (form: ReturnType<typeof useForm>) => void
}

export default function ComponentForm({ component, categories, types, submitLabel, onSubmit }: ComponentFormProps) {
    const form = useForm({
        component_category_id: component?.category?.id ?? categories[0]?.id ?? '',
        name: component?.name ?? '',
        manufacturer: component?.manufacturer ?? '',
        reference: component?.reference ?? '',
        description: component?.description ?? '',
        specs: component ? Object.entries(component.specs).map(([key, value]) => ({ key, value })) : [{ key: '', value: '' }],
        datasheet: null as File | null,
        price_cents: component?.price_cents?.toString() ?? '',
        currency: component?.currency ?? 'EUR',
        supplier_url: component?.supplier_url ?? '',
    })

    const submit = (event: FormEvent) => { event.preventDefault(); onSubmit(form) }

    return <form className="rf-form" onSubmit={submit}>
        <label>
            Catégorie
            <select value={form.data.component_category_id} onChange={(e) => form.setData('component_category_id', Number(e.target.value))}>
                {types.map((type) => {
                    const options = categories.filter((c) => c.type === type)
                    if (options.length === 0) return null
                    return <optgroup key={type} label={componentTypeLabels[type]}>{options.map((category) => <option key={category.id} value={category.id}>{category.name}</option>)}</optgroup>
                })}
            </select>
            <InputError message={form.errors.component_category_id} />
        </label>
        <label>Nom<input value={form.data.name} onChange={(e) => form.setData('name', e.target.value)} required autoFocus /><InputError message={form.errors.name} /></label>
        <div className="rf-form-grid">
            <label>Fabricant<input value={form.data.manufacturer} onChange={(e) => form.setData('manufacturer', e.target.value)} /><InputError message={form.errors.manufacturer} /></label>
            <label>Référence<input value={form.data.reference} onChange={(e) => form.setData('reference', e.target.value)} /><InputError message={form.errors.reference} /></label>
        </div>
        <label>Description<textarea value={form.data.description} onChange={(e) => form.setData('description', e.target.value)} rows={4} /><InputError message={form.errors.description} /></label>
        <label>
            Caractéristiques techniques
            <DynamicTable
                rows={form.data.specs}
                cols={[{ key: 'key', label: 'Propriété', placeholder: 'Tension' }, { key: 'value', label: 'Valeur', placeholder: '5V' }]}
                onChange={(rows) => form.setData('specs', rows as Array<{ key: string; value: string }>)}
                addLabel="Ajouter une caractéristique"
            />
            <InputError message={form.errors.specs} />
        </label>
        <div className="rf-form-grid">
            <label>Prix (centimes)<input type="number" min={0} value={form.data.price_cents} onChange={(e) => form.setData('price_cents', e.target.value)} /><InputError message={form.errors.price_cents} /></label>
            <label>Devise<input value={form.data.currency} onChange={(e) => form.setData('currency', e.target.value)} maxLength={3} /><InputError message={form.errors.currency} /></label>
        </div>
        <label>Lien fournisseur<input type="url" value={form.data.supplier_url} onChange={(e) => form.setData('supplier_url', e.target.value)} placeholder="https://…" /><InputError message={form.errors.supplier_url} /></label>
        <label>
            Fiche technique (PDF)
            <input type="file" accept="application/pdf" onChange={(e) => form.setData('datasheet', e.target.files?.[0] ?? null)} />
            {component?.datasheet_url && <small>Un fichier est déjà en place ; en envoyer un nouveau le remplacera.</small>}
            <InputError message={form.errors.datasheet} />
        </label>
        <button type="submit" className="rf-button rf-button--primary" disabled={form.processing}>{form.processing ? 'Enregistrement…' : submitLabel}</button>
    </form>
}
