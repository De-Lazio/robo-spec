export default function InputError({ message, className = '', ...props }) {
    return message ? (
        <p
            {...props}
            className={'rf-error ' + className}
        >
            {message}
        </p>
    ) : null;
}
