<h2>Algorigramme(s)</h2>
@if(count($diagrams) === 0)
    <p class="empty">Aucun algorigramme enregistré.</p>
@else
    @foreach($diagrams as $entry)
        <h3>{{ $entry['diagram']->name }} ({{ $entry['diagram']->formalism->value === 'grafcet' ? 'GRAFCET' : 'Algorigramme' }})</h3>
        @if($entry['imageDataUri'])
            <img class="diagram-image" src="{{ $entry['imageDataUri'] }}">
        @else
            @php($nodes = $entry['diagram']->data['nodes'] ?? [])
            @php($edges = $entry['diagram']->data['edges'] ?? [])
            @if(count($nodes) === 0)
                <p class="empty">Ce diagramme est vide.</p>
            @else
                <p><strong>Étapes :</strong></p>
                <ul>
                    @foreach($nodes as $node)
                        <li>{{ $node['data']['label'] ?? $node['id'] ?? '' }} ({{ $node['type'] ?? '' }})</li>
                    @endforeach
                </ul>
                @if(count($edges) > 0)
                    <p><strong>Transitions :</strong></p>
                    <ul>
                        @foreach($edges as $edge)
                            <li>{{ $edge['source'] ?? '' }} → {{ $edge['target'] ?? '' }}</li>
                        @endforeach
                    </ul>
                @endif
            @endif
        @endif
    @endforeach
@endif
