<h2>Tâches</h2>
@if($tasks->isEmpty())
    <p class="empty">Aucune tâche enregistrée.</p>
@else
    <table>
        <tr><th>Titre</th><th style="width: 100px;">Statut</th><th style="width: 130px;">Assigné à</th><th style="width: 90px;">Échéance</th></tr>
        @foreach($tasks as $task)
            <tr>
                <td>{{ $task->title }}</td>
                <td>{{ \App\Domain\Export\Support\ExportLabels::taskStatus($task->status) }}</td>
                <td>{{ $task->assignee?->name ?? '—' }}</td>
                <td>{{ $task->due_date?->format('d/m/Y') ?? '—' }}</td>
            </tr>
        @endforeach
    </table>
@endif
