export default function PrimaryButton({
    className = '',
    disabled,
    children,
    ...props
}) {
    return (
        <button
            {...props}
            className={'rf-button rf-button--primary ' + className}
            disabled={disabled}
        >
            {children}
        </button>
    );
}
