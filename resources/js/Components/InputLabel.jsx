export default function InputLabel({
    value,
    className = '',
    children,
    ...props
}) {
    return (
        <label
            {...props}
            className={'rf-label ' + className}
        >
            {value ? value : children}
        </label>
    );
}
