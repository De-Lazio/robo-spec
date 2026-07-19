<h2>Équipe</h2>
@if(count($members) === 0)
    <p class="empty">Aucun membre.</p>
@else
    <table>
        <tr><th>Nom</th><th>Email</th><th>Rôle</th><th>Depuis le</th></tr>
        @foreach($members as $member)
            <tr>
                <td>{{ $member['name'] }}</td>
                <td>{{ $member['email'] }}</td>
                <td>{{ $member['role'] }}</td>
                <td>{{ $member['joined_at'] }}</td>
            </tr>
        @endforeach
    </table>
@endif
