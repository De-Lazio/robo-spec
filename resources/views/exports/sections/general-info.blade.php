<h2>Informations générales</h2>
<table>
    <tr><th style="width: 160px;">Nom</th><td>{{ $project->name }}</td></tr>
    <tr><th>Type de robot</th><td>{{ \App\Domain\Export\Support\ExportLabels::robotType($project->robot_type) }}</td></tr>
    <tr><th>Statut</th><td>{{ \App\Domain\Export\Support\ExportLabels::projectStatus($project->status) }}</td></tr>
    <tr><th>Avancement</th><td>{{ $project->progress }}%</td></tr>
    @if($project->domain)
        <tr><th>Domaine</th><td>{{ $project->domain }}</td></tr>
    @endif
    <tr><th>Propriétaire</th><td>{{ $project->owner?->name }}</td></tr>
</table>
@if($project->description)
    <p>{{ $project->description }}</p>
@endif
