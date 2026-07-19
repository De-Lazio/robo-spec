<h2>Cahier des charges</h2>
@if($requirementsDocument === null)
    <p class="empty">Aucun CDC publié pour ce projet.</p>
@else
    @php($data = $requirementsDocument->data)
    <p class="empty">Version {{ $requirementsDocument->version }} — publié le {{ $requirementsDocument->published_at?->format('d/m/Y') }}</p>

    <h3>1. Présentation du projet</h3>
    <table>
        <tr><th style="width: 160px;">Type de projet</th><td>{{ $data['step1']['projectType'] ?? '' }}</td></tr>
        <tr><th>Équipe</th><td>{{ $data['step1']['team'] ?? '' }}</td></tr>
        <tr><th>Date</th><td>{{ $data['step1']['date'] ?? '' }}</td></tr>
        <tr><th>Encadrant</th><td>{{ $data['step1']['supervisor'] ?? '' }}</td></tr>
    </table>

    <h3>2. Contexte et problématique</h3>
    <p>{{ $data['step2']['context'] ?? '' }}</p>
    <p>{{ $data['step2']['problem'] ?? '' }}</p>
    <p>{{ $data['step2']['whyRobot'] ?? '' }}</p>

    <h3>3. Missions</h3>
    <ul>
        @foreach(($data['step3']['missions'] ?? []) as $mission)
            <li>{{ $mission }}</li>
        @endforeach
    </ul>

    <h3>4. Utilisateurs</h3>
    <ul>
        @foreach(($data['step4']['users'] ?? []) as $user)
            <li>{{ $user }}</li>
        @endforeach
    </ul>
    @if(!empty($data['step4']['otherUsers']))
        <p>{{ $data['step4']['otherUsers'] }}</p>
    @endif

    <h3>5. Fonctions</h3>
    <table>
        <tr><th style="width: 60px;">ID</th><th>Fonction</th></tr>
        @foreach(($data['step5']['functions'] ?? []) as $function)
            <tr><td>{{ $function['id'] ?? '' }}</td><td>{{ $function['name'] ?? '' }}</td></tr>
        @endforeach
    </table>

    <h3>6. Contraintes techniques</h3>
    <table>
        <tr><th style="width: 160px;">Taille max.</th><td>{{ $data['step6']['maxSize'] ?? '' }}</td></tr>
        <tr><th>Poids max.</th><td>{{ $data['step6']['maxWeight'] ?? '' }}</td></tr>
        <tr><th>Autonomie min.</th><td>{{ $data['step6']['minAutonomy'] ?? '' }}</td></tr>
        <tr><th>Vitesse min.</th><td>{{ $data['step6']['minSpeed'] ?? '' }}</td></tr>
        <tr><th>Budget max.</th><td>{{ $data['step6']['maxBudget'] ?? '' }}</td></tr>
        <tr><th>Coût estimé</th><td>{{ $data['step6']['estimatedCost'] ?? '' }}</td></tr>
        <tr><th>Température</th><td>{{ $data['step6']['temperature'] ?? '' }}</td></tr>
        <tr><th>Conditions d'usage</th><td>{{ $data['step6']['usageConditions'] ?? '' }}</td></tr>
    </table>
    @if(!empty($data['step6']['safetyConstraints']))
        <h3>Contraintes de sécurité</h3>
        <ul>
            @foreach($data['step6']['safetyConstraints'] as $constraint)
                <li>{{ $constraint }}</li>
            @endforeach
        </ul>
    @endif

    <h3>7. Critères de performance</h3>
    <table>
        <tr><th>Critère</th><th>Valeur</th></tr>
        @foreach(($data['step7']['criteria'] ?? []) as $criterion)
            <tr><td>{{ $criterion['name'] ?? '' }}</td><td>{{ $criterion['value'] ?? '' }}</td></tr>
        @endforeach
    </table>

    <h3>8. Résultat attendu</h3>
    <p>{{ $data['step8']['expectedResult'] ?? '' }}</p>
    <p><strong>Critères de réussite :</strong> {{ $data['step8']['successCriteria'] ?? '' }}</p>
    <p><strong>Méthode de test :</strong> {{ $data['step8']['testMethod'] ?? '' }}</p>
@endif
