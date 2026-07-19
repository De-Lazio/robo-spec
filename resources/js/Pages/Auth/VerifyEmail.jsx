import PrimaryButton from '@/Components/PrimaryButton';
import GuestLayout from '@/Layouts/GuestLayout';
import { Head, Link, useForm } from '@inertiajs/react';

export default function VerifyEmail({ status }) {
    const { post, processing } = useForm({});

    const submit = (e) => {
        e.preventDefault();

        post(route('verification.send'));
    };

    return (
        <GuestLayout>
            <Head title="Vérification de l'e-mail" />

            <p className="rf-auth-hint">
                Merci de votre inscription ! Avant de commencer, pouvez-vous
                confirmer votre adresse e-mail en cliquant sur le lien que
                nous venons de vous envoyer ? Si vous ne l&apos;avez pas reçu,
                nous pouvons vous en renvoyer un.
            </p>

            {status === 'verification-link-sent' && (
                <div className="rf-auth-status">
                    Un nouveau lien de vérification a été envoyé à l&apos;adresse
                    e-mail fournie lors de l&apos;inscription.
                </div>
            )}

            <form onSubmit={submit} className="rf-form">
                <div className="rf-auth-row">
                    <PrimaryButton disabled={processing}>
                        Renvoyer l&apos;e-mail de vérification
                    </PrimaryButton>

                    <Link href={route('logout')} method="post" as="button" className="rf-auth-link">
                        Se déconnecter
                    </Link>
                </div>
            </form>
        </GuestLayout>
    );
}
