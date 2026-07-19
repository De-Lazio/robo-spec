<h2>Choix techniques</h2>
@if($technicalChoices->isEmpty())
    <p class="empty">Aucun choix technique enregistré.</p>
@else
    <table>
        <tr>
            <th>Composant</th>
            <th>Fabricant</th>
            <th style="width: 60px;">Qté</th>
            <th style="width: 90px;">Prix unit.</th>
            <th style="width: 90px;">Total</th>
        </tr>
        @foreach($technicalChoices as $choice)
            <tr>
                <td>{{ $choice->component->name }}</td>
                <td>{{ $choice->component->manufacturer }}</td>
                <td>{{ $choice->quantity }}</td>
                <td>{{ number_format(($choice->component->price_cents ?? 0) / 100, 2, ',', ' ') }} {{ $choice->component->currency ?? '' }}</td>
                <td>{{ number_format((($choice->component->price_cents ?? 0) * $choice->quantity) / 100, 2, ',', ' ') }} {{ $choice->component->currency ?? '' }}</td>
            </tr>
        @endforeach
        <tr class="total-row">
            <td colspan="4">Total</td>
            <td>{{ number_format($technicalChoicesTotalCents / 100, 2, ',', ' ') }}</td>
        </tr>
    </table>
@endif
