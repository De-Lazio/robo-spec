import Checkbox from '@/Components/Checkbox';
import InputError from '@/Components/InputError';
import PrimaryButton from '@/Components/PrimaryButton';
import TextInput from '@/Components/TextInput';
import GuestLayout from '@/Layouts/GuestLayout';
import { Head, Link, useForm } from '@inertiajs/react';

export default function Login({ status, canResetPassword }) {
    const { data, setData, post, processing, errors, reset } = useForm({
        email: '',
        password: '',
        remember: false,
    });

    const submit = (e) => {
        e.preventDefault();

        post(route('login'), {
            onFinish: () => reset('password'),
        });
    };

    return (
        <GuestLayout>
            <Head title="Connexion" />

            {status && <div className="rf-auth-status">{status}</div>}

            <form onSubmit={submit} className="rf-form">
                <label>
                    Email
                    <TextInput
                        id="email"
                        type="email"
                        name="email"
                        value={data.email}
                        autoComplete="username"
                        isFocused={true}
                        onChange={(e) => setData('email', e.target.value)}
                    />
                    <InputError message={errors.email} />
                </label>

                <label>
                    Mot de passe
                    <TextInput
                        id="password"
                        type="password"
                        name="password"
                        value={data.password}
                        autoComplete="current-password"
                        onChange={(e) => setData('password', e.target.value)}
                    />
                    <InputError message={errors.password} />
                </label>

                <div className="rf-auth-row">
                    <label style={{ display: 'flex', alignItems: 'center', gap: 8, fontWeight: 400, fontSize: 13 }}>
                        <Checkbox
                            name="remember"
                            checked={data.remember}
                            onChange={(e) =>
                                setData('remember', e.target.checked)
                            }
                        />
                        Se souvenir de moi
                    </label>
                    {canResetPassword && (
                        <Link href={route('password.request')} className="rf-auth-link">
                            Mot de passe oublié ?
                        </Link>
                    )}
                </div>

                <PrimaryButton className="rf-button--block" disabled={processing}>
                    Se connecter
                </PrimaryButton>
            </form>
        </GuestLayout>
    );
}
