import ComponentForm from '@/Components/components/ComponentForm'
import AppLayout from '@/Layouts/AppLayout'
import type { ComponentCategoryOption, ComponentType } from '@/types/components'
import { Head } from '@inertiajs/react'

interface CreateProps {
    categories: ComponentCategoryOption[]
    types: ComponentType[]
}

export default function Create({ categories, types }: CreateProps) {
    return <AppLayout breadcrumbs={[{ label: 'Bibliothèque', href: '/components' }, { label: 'Nouveau composant' }]}>
        <Head title="Nouveau composant" />
        <section className="rf-form-page">
            <div className="rf-page-intro"><p className="rf-eyebrow">Bibliothèque</p><h1>Ajouter un composant</h1><p>Ce composant sera disponible pour tous les projets de la plateforme.</p></div>
            <div className="rf-panel"><ComponentForm categories={categories} types={types} submitLabel="Ajouter au catalogue" onSubmit={(form) => form.post('/components', { forceFormData: true })} /></div>
        </section>
    </AppLayout>
}
