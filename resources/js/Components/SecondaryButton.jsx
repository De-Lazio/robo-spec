export default function SecondaryButton({
    type = 'button',
    className = '',
    disabled,
    children,
    ...props
}) {
    return (
        <button
            {...props}
            type={type}
            className={'rf-button rf-button--secondary ' + className}
            disabled={disabled}
        >
            {children}
        </button>
    );
}
