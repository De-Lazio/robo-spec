import AppLayout from '@/Layouts/AppLayout';
import { Head } from '@inertiajs/react';
import DeleteUserForm from './Partials/DeleteUserForm';
import UpdatePasswordForm from './Partials/UpdatePasswordForm';
import UpdateProfileInformationForm from './Partials/UpdateProfileInformationForm';

export default function Edit({ mustVerifyEmail, status }) {
    return (
        <AppLayout breadcrumbs={[{ label: 'Mon profil' }]}>
            <Head title="Profil" />

            <div className="rf-page-intro">
                <p className="rf-eyebrow">Compte</p>
                <h1>Mon profil</h1>
                <p>Gérez vos informations personnelles et la sécurité de votre compte.</p>
            </div>

            <div className="rf-stack">
                <div className="rf-panel">
                    <UpdateProfileInformationForm
                        mustVerifyEmail={mustVerifyEmail}
                        status={status}
                    />
                </div>

                <div className="rf-panel">
                    <UpdatePasswordForm />
                </div>

                <div className="rf-panel">
                    <DeleteUserForm />
                </div>
            </div>
        </AppLayout>
    );
}
