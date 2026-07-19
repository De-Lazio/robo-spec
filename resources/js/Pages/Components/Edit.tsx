import ComponentForm from '@/Components/components/ComponentForm'
import AppLayout from '@/Layouts/AppLayout'
import type { ComponentCategoryOption, ComponentType, LibraryComponent } from '@/types/components'
import { Head } from '@inertiajs/react'

interface EditProps {
    component: LibraryComponent
    categories: ComponentCategoryOption[]
    types: ComponentType[]
}

export default function Edit({ component, categories, types }: EditProps) {
    return <AppLayout breadcrumbs={[{ label: 'Bibliothèque', href: '/components' }, { label: component.name, href: `/components/${component.id}` }, { label: 'Modifier' }]}>
        <Head title={`Modifier · ${component.name}`} />
        <section className="rf-form-page">
            <div className="rf-page-intro"><p className="rf-eyebrow">Bibliothèque</p><h1>Modifier {component.name}</h1></div>
            <div className="rf-panel"><ComponentForm component={component} categories={categories} types={types} submitLabel="Enregistrer les modifications" onSubmit={(form) => form.put(`/components/${component.id}`, { forceFormData: true })} /></div>
        </section>
    </AppLayout>
}
