interface StepIndicatorProps {
    current: number
    total: number
    title: string
}

export default function StepIndicator({ current, total, title }: StepIndicatorProps) {
    return (
        <div className="rf-step-indicator">
            <div className="rf-step-indicator__head">
                <div>
                    <div className="rf-step-indicator__eyebrow">Étape {current} sur {total}</div>
                    <div className="rf-step-indicator__title">{title}</div>
                </div>
                <div className="rf-step-indicator__num">{String(current).padStart(2, '0')}</div>
            </div>
            <div className="rf-step-dots">
                {Array.from({ length: total }).map((_, i) => (
                    <span key={i} style={{ display: 'flex', alignItems: 'center', gap: 4, flex: i < total - 1 ? 1 : undefined }}>
                        <span className={`rf-step-dot ${i === current - 1 ? 'is-current' : i < current - 1 ? 'is-done' : ''}`} />
                        {i < total - 1 && <span className={`rf-step-connector ${i < current - 1 ? 'is-done' : ''}`} />}
                    </span>
                ))}
            </div>
        </div>
    )
}
