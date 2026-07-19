<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="utf-8">
    <title>{{ $project->name }} — Export RoboForge</title>
    <style>
        body { font-family: 'DejaVu Sans', sans-serif; font-size: 12px; color: #0f172a; }
        h1 { font-size: 20px; margin: 0 0 4px; }
        h2 { font-size: 15px; margin: 26px 0 10px; padding-bottom: 6px; border-bottom: 1.5px solid #2563eb; color: #2563eb; }
        h3 { font-size: 13px; margin: 14px 0 6px; }
        p { line-height: 1.5; margin: 0 0 8px; }
        table { width: 100%; border-collapse: collapse; margin-bottom: 10px; }
        th, td { text-align: left; padding: 6px 8px; border-bottom: 1px solid #e2eaf3; font-size: 11px; vertical-align: top; }
        th { background: #f1f5fb; font-weight: bold; }
        .header { margin-bottom: 10px; }
        .header .subtitle { color: #64748b; font-size: 11px; }
        .empty { color: #94a3b8; font-style: italic; }
        .diagram-image { max-width: 100%; max-height: 320px; margin: 8px 0; }
        .total-row td { font-weight: bold; border-top: 1.5px solid #2563eb; }
        ul { margin: 4px 0 8px; padding-left: 18px; }
    </style>
</head>
<body>
    <div class="header">
        <h1>{{ $project->name }}</h1>
        <div class="subtitle">Export généré le {{ now()->format('d/m/Y à H:i') }}</div>
    </div>

    @if($sections['generalInfo'])
        @include('exports.sections.general-info')
    @endif

    @if($sections['requirements'])
        @include('exports.sections.requirements')
    @endif

    @if($sections['team'])
        @include('exports.sections.team')
    @endif

    @if($sections['technicalChoices'])
        @include('exports.sections.technical-choices')
    @endif

    @if($sections['algorithmDiagrams'])
        @include('exports.sections.algorithm-diagrams')
    @endif

    @if($sections['tasks'])
        @include('exports.sections.tasks')
    @endif

    @if($sections['resources'])
        @include('exports.sections.resources')
    @endif
</body>
</html>
