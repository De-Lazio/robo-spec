import InputError from '@/Components/InputError';
import PrimaryButton from '@/Components/PrimaryButton';
import TextInput from '@/Components/TextInput';
import GuestLayout from '@/Layouts/GuestLayout';
import { Head, useForm } from '@inertiajs/react';

export default function ConfirmPassword() {
    const { data, setData, post, processing, errors, reset } = useForm({
        password: '',
    });

    const submit = (e) => {
        e.preventDefault();

        post(route('password.confirm'), {
            onFinish: () => reset('password'),
        });
    };

    return (
        <GuestLayout>
            <Head title="Confirmer le mot de passe" />

            <p className="rf-auth-hint">
                Ceci est une zone sécurisée de l&apos;application. Merci de
                confirmer votre mot de passe avant de continuer.
            </p>

            <form onSubmit={submit} className="rf-form">
                <label>
                    Mot de passe
                    <TextInput
                        id="password"
                        type="password"
                        name="password"
                        value={data.password}
                        isFocused={true}
                        onChange={(e) => setData('password', e.target.value)}
                    />
                    <InputError message={errors.password} />
                </label>

                <PrimaryButton className="rf-button--block" disabled={processing}>
                    Confirmer
                </PrimaryButton>
            </form>
        </GuestLayout>
    );
}
