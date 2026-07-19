<h2>Ressources</h2>
@if($resources->isEmpty())
    <p class="empty">Aucune ressource enregistrée.</p>
@else
    <table>
        <tr><th>Nom</th><th style="width: 100px;">Catégorie</th><th style="width: 100px;">Dossier</th><th style="width: 70px;">Taille</th></tr>
        @foreach($resources as $resource)
            <tr>
                <td>{{ $resource->original_name }}</td>
                <td>{{ \App\Domain\Export\Support\ExportLabels::resourceCategory($resource->category) }}</td>
                <td>{{ $resource->folder ?? '—' }}</td>
                <td>{{ number_format($resource->size_bytes / 1024, 0) }} Ko</td>
            </tr>
        @endforeach
    </table>
@endif
