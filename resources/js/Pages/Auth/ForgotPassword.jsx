import InputError from '@/Components/InputError';
import PrimaryButton from '@/Components/PrimaryButton';
import TextInput from '@/Components/TextInput';
import GuestLayout from '@/Layouts/GuestLayout';
import { Head, useForm } from '@inertiajs/react';

export default function ForgotPassword({ status }) {
    const { data, setData, post, processing, errors } = useForm({
        email: '',
    });

    const submit = (e) => {
        e.preventDefault();

        post(route('password.email'));
    };

    return (
        <GuestLayout>
            <Head title="Mot de passe oublié" />

            <p className="rf-auth-hint">
                Indiquez votre adresse e-mail et nous vous enverrons un lien
                pour choisir un nouveau mot de passe.
            </p>

            {status && <div className="rf-auth-status">{status}</div>}

            <form onSubmit={submit} className="rf-form">
                <label>
                    Email
                    <TextInput
                        id="email"
                        type="email"
                        name="email"
                        value={data.email}
                        isFocused={true}
                        onChange={(e) => setData('email', e.target.value)}
                    />
                    <InputError message={errors.email} />
                </label>

                <PrimaryButton className="rf-button--block" disabled={processing}>
                    Envoyer le lien de réinitialisation
                </PrimaryButton>
            </form>
        </GuestLayout>
    );
}
