export default function DangerButton({
    className = '',
    disabled,
    children,
    ...props
}) {
    return (
        <button
            {...props}
            className={'rf-button rf-button--danger ' + className}
            disabled={disabled}
        >
            {children}
        </button>
    );
}
