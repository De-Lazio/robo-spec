import ConstraintsStep from '@/Components/requirements/Steps/ConstraintsStep'
import ContextStep from '@/Components/requirements/Steps/ContextStep'
import ExpectedResultStep from '@/Components/requirements/Steps/ExpectedResultStep'
import FunctionsStep from '@/Components/requirements/Steps/FunctionsStep'
import GeneralInformationStep from '@/Components/requirements/Steps/GeneralInformationStep'
import MissionStep from '@/Components/requirements/Steps/MissionStep'
import PerformanceStep from '@/Components/requirements/Steps/PerformanceStep'
import UsersStep from '@/Components/requirements/Steps/UsersStep'
import StepIndicator from '@/Components/requirements/StepIndicator'
import AppLayout from '@/Layouts/AppLayout'
import { STEP_NAMES } from '@/types/requirements'
import type { RequirementsData, RequirementsOptions } from '@/types/requirements'
import { Head, router } from '@inertiajs/react'
import type { FormDataConvertible } from '@inertiajs/core'
import { Check, ChevronLeft, ChevronRight, CheckCircle2, Save } from 'lucide-react'
import { useState } from 'react'

const TOTAL = 8

interface EditProps {
    project: { id: string; name: string }
    requirementsDocument: { data: RequirementsData; currentStep: number; version: number }
    completedSteps: Record<number, boolean>
    options: RequirementsOptions
}

export default function Edit({ project, requirementsDocument, completedSteps, options }: EditProps) {
    const [step, setStep] = useState(requirementsDocument.currentStep)
    const [cdc, setCdc] = useState<RequirementsData>(requirementsDocument.data)
    const [completed, setCompleted] = useState(completedSteps)
    const [saving, setSaving] = useState(false)
    const [publishing, setPublishing] = useState(false)
    const [stepErrors, setStepErrors] = useState<string[]>([])

    const isLast = step === TOTAL

    const updateStep = <K extends keyof RequirementsData>(key: K) => (value: RequirementsData[K]) =>
        setCdc((prev) => ({ ...prev, [key]: value }))

    const stepKey = `step${step}` as keyof RequirementsData

    const goNext = () => {
        setStepErrors([])
        router.put(`/projects/${project.id}/cdc/steps/${step}`, cdc[stepKey] as Record<string, FormDataConvertible>, {
            preserveScroll: true,
            onSuccess: () => {
                setCompleted((prev) => ({ ...prev, [step]: true }))
                setStep((s) => Math.min(TOTAL, s + 1))
            },
            onError: (errors) => setStepErrors(Object.values(errors).flat() as string[]),
        })
    }

    const saveDraft = () => {
        setSaving(true)
        router.put(`/projects/${project.id}/cdc/draft`, { data: cdc as unknown as FormDataConvertible }, {
            preserveScroll: true,
            onFinish: () => setSaving(false),
        })
    }

    const publish = () => {
        setStepErrors([])
        setPublishing(true)
        router.put(`/projects/${project.id}/cdc/steps/${step}`, cdc[stepKey] as Record<string, FormDataConvertible>, {
            preserveScroll: true,
            onSuccess: () => {
                setCompleted((prev) => ({ ...prev, [step]: true }))
                router.post(`/projects/${project.id}/cdc/publish`, {}, {
                    onError: (errors) => setStepErrors(Object.values(errors).flat() as string[]),
                    onFinish: () => setPublishing(false),
                })
            },
            onError: (errors) => {
                setStepErrors(Object.values(errors).flat() as string[])
                setPublishing(false)
            },
        })
    }

    const renderStep = () => {
        switch (step) {
            case 1: return <GeneralInformationStep data={cdc.step1} onChange={updateStep('step1')} />
            case 2: return <ContextStep data={cdc.step2} onChange={updateStep('step2')} />
            case 3: return <MissionStep data={cdc.step3} onChange={updateStep('step3')} />
            case 4: return <UsersStep data={cdc.step4} onChange={updateStep('step4')} userOptions={options.userOptions} />
            case 5: return <FunctionsStep data={cdc.step5} onChange={updateStep('step5')} />
            case 6: return <ConstraintsStep data={cdc.step6} onChange={updateStep('step6')} safetyConstraints={options.safetyConstraints} />
            case 7: return <PerformanceStep data={cdc.step7} onChange={updateStep('step7')} />
            case 8: return <ExpectedResultStep data={cdc.step8} onChange={updateStep('step8')} />
            default: return null
        }
    }

    return (
        <AppLayout project={project} breadcrumbs={[{ label: 'Mes projets', href: '/projects' }, { label: project.name, href: `/projects/${project.id}` }, { label: 'Cahier des charges', href: `/projects/${project.id}/cdc` }, { label: 'Modifier' }]}
            actions={<button type="button" className="rf-button rf-button--secondary rf-button--small" onClick={saveDraft} disabled={saving}><Save size={15} />{saving ? 'Enregistrement…' : 'Enregistrer'}</button>}>
            <Head title={`CDC · ${project.name}`} />

            <div className="rf-wizard">
                <nav className="rf-wizard-nav" aria-label="Étapes du cahier des charges">
                    {STEP_NAMES.map((name, i) => {
                        const n = i + 1
                        const active = n === step
                        const done = completed[n]
                        return (
                            <button key={n} type="button" className={`rf-wizard-step-link ${active ? 'is-active' : ''}`} onClick={() => setStep(n)}>
                                <span className={`rf-wizard-step-badge ${active ? 'is-active' : ''} ${done ? 'is-done' : ''}`}>
                                    {done ? <Check size={12} strokeWidth={3} /> : n}
                                </span>
                                {name}
                            </button>
                        )
                    })}
                </nav>

                <div className="rf-wizard-main">
                    <div className="rf-wizard-content">
                        <StepIndicator current={step} total={TOTAL} title={STEP_NAMES[step - 1]} />
                        {stepErrors.length > 0 && (
                            <div className="rf-hint-banner rf-hint-banner--warning" style={{ marginBottom: 18 }}>
                                <strong>Corrigez les points suivants :</strong>
                                <ul style={{ margin: '6px 0 0', paddingLeft: 18 }}>
                                    {stepErrors.map((message) => <li key={message}>{message}</li>)}
                                </ul>
                            </div>
                        )}
                        {renderStep()}
                    </div>

                    <div className="rf-wizard-footer">
                        <button type="button" className="rf-button rf-button--secondary" onClick={() => setStep((s) => Math.max(1, s - 1))} disabled={step === 1}>
                            <ChevronLeft size={16} /> Précédent
                        </button>

                        {isLast ? (
                            <button type="button" className="rf-button rf-button--primary" onClick={publish} disabled={publishing}>
                                <CheckCircle2 size={16} /> {publishing ? 'Publication…' : 'Publier le CDC'}
                            </button>
                        ) : (
                            <button type="button" className="rf-button rf-button--primary" onClick={goNext}>
                                Suivant <ChevronRight size={16} />
                            </button>
                        )}
                    </div>
                </div>
            </div>
        </AppLayout>
    )
}
