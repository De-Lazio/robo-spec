import ComponentForm from '@/Components/Components/ComponentForm'
import Modal from '@/Components/Modal'
import type { ComponentCategoryOption, ComponentType, LibraryComponent } from '@/types/components'
import { X } from 'lucide-react'

interface LocalComponentModalProps {
    show: boolean
    onClose: () => void
    projectId: string
    categories: ComponentCategoryOption[]
    types: ComponentType[]
    component?: LibraryComponent
}

export default function LocalComponentModal({ show, onClose, projectId, categories, types, component }: LocalComponentModalProps) {
    const isEditing = component !== undefined
    const action = isEditing
        ? `/projects/${projectId}/local-components/${component.id}`
        : `/projects/${projectId}/local-components`

    return (
        <Modal show={show} onClose={onClose} maxWidth="lg">
            <div style={{ padding: 24 }}>
                <div style={{ display: 'flex', justifyContent: 'space-between', alignItems: 'center', marginBottom: 4 }}>
                    <h2 style={{ margin: 0 }}>{isEditing ? 'Modifier le composant local' : 'Nouveau composant local'}</h2>
                    <button type="button" onClick={onClose} style={{ border: 0, background: 'none', cursor: 'pointer' }} aria-label="Fermer"><X size={18} /></button>
                </div>
                <p className="rf-field-hint" style={{ marginBottom: 16 }}>Ce composant ne sera visible et utilisable que dans ce projet.</p>
                <ComponentForm
                    component={component}
                    categories={categories}
                    types={types}
                    showDatasheet={false}
                    submitLabel={isEditing ? 'Enregistrer' : 'Créer le composant'}
                    onSubmit={(form) => {
                        const options = { onSuccess: () => onClose() }

                        if (isEditing) {
                            form.put(action, options)
                        } else {
                            form.post(action, options)
                        }
                    }}
                />
            </div>
        </Modal>
    )
}
