<?php

namespace Tests\Feature\Domain\Export;

use App\Domain\AlgorithmDiagrams\Enums\DiagramFormalism;
use App\Domain\AlgorithmDiagrams\Services\AlgorithmDiagramService;
use App\Domain\Export\DTOs\ExportSelectionDTO;
use App\Domain\Export\Services\ProjectExportService;
use App\Domain\Resources\Enums\ResourceCategory;
use App\Domain\Resources\Enums\ResourceKind;
use App\Models\Component;
use App\Models\Project;
use App\Models\ProjectComponent;
use App\Models\RequirementsDocument;
use App\Models\Resource;
use App\Models\Task;
use App\Models\User;
use Barryvdh\DomPDF\Facade\Pdf;
use Barryvdh\DomPDF\PDF as PdfDocument;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Mockery;
use Tests\TestCase;

class ProjectExportServiceTest extends TestCase
{
    use RefreshDatabase;

    public function test_it_only_includes_selected_sections_in_the_view_data(): void
    {
        $owner = User::factory()->create();
        $project = Project::factory()->create(['owner_id' => $owner->getKey()]);
        RequirementsDocument::factory()->published()->create(['project_id' => $project->getKey()]);
        Task::factory()->create(['project_id' => $project->getKey()]);

        $captured = $this->captureLoadView();

        $selection = new ExportSelectionDTO(
            generalInfo: true,
            requirements: false,
            team: false,
            technicalChoices: false,
            algorithmDiagrams: false,
            tasks: true,
            resources: false,
        );

        app(ProjectExportService::class)->generate($project, $selection);

        $this->assertSame('exports.project', $captured->view);
        $this->assertNull($captured->data['requirementsDocument']);
        $this->assertCount(1, $captured->data['tasks']);
        $this->assertSame([], $captured->data['members']);
        $this->assertTrue($captured->data['sections']['generalInfo']);
        $this->assertFalse($captured->data['sections']['requirements']);
    }

    public function test_it_computes_the_technical_choices_total_using_the_same_formula_as_the_controller(): void
    {
        $owner = User::factory()->create();
        $project = Project::factory()->create(['owner_id' => $owner->getKey()]);
        $component = Component::factory()->create(['price_cents' => 1000]);
        ProjectComponent::query()->create([
            'project_id' => $project->getKey(),
            'component_id' => $component->getKey(),
            'quantity' => 3,
            'added_by' => $owner->getKey(),
        ]);

        $captured = $this->captureLoadView();

        app(ProjectExportService::class)->generate($project, new ExportSelectionDTO);

        $this->assertSame(3000, $captured->data['technicalChoicesTotalCents']);
    }

    public function test_diagram_without_an_exported_image_has_a_null_image_data_uri(): void
    {
        $owner = User::factory()->create();
        $project = Project::factory()->create(['owner_id' => $owner->getKey()]);
        app(AlgorithmDiagramService::class)->create($project, $owner, 'Séquence', DiagramFormalism::Algorigramme);

        $captured = $this->captureLoadView();

        app(ProjectExportService::class)->generate($project, new ExportSelectionDTO);

        $this->assertCount(1, $captured->data['diagrams']);
        $this->assertNull($captured->data['diagrams'][0]['imageDataUri']);
    }

    public function test_diagram_with_an_exported_image_embeds_it_as_a_data_uri(): void
    {
        Storage::fake('local');

        $owner = User::factory()->create();
        $project = Project::factory()->create(['owner_id' => $owner->getKey()]);
        $diagram = app(AlgorithmDiagramService::class)->create($project, $owner, 'Séquence', DiagramFormalism::Algorigramme);

        $path = 'projects/x/resources/y/diagram.png';
        Storage::disk('local')->put($path, 'fake-png-bytes');
        Resource::query()->create([
            'id' => (string) Str::ulid(),
            'project_id' => $project->getKey(),
            'algorithm_diagram_id' => $diagram->getKey(),
            'uploaded_by' => $owner->getKey(),
            'category' => ResourceCategory::Mechanical,
            'kind' => ResourceKind::Image,
            'name' => 'diagram.png',
            'original_name' => 'diagram.png',
            'disk' => 'local',
            'path' => $path,
            'mime_type' => 'image/png',
            'size_bytes' => 14,
        ]);

        $captured = $this->captureLoadView();

        app(ProjectExportService::class)->generate($project, new ExportSelectionDTO);

        $this->assertStringStartsWith('data:image/png;base64,', $captured->data['diagrams'][0]['imageDataUri']);
    }

    private function captureLoadView(): object
    {
        $captured = new \stdClass;

        Pdf::shouldReceive('loadView')->once()->andReturnUsing(function (string $view, array $data) use ($captured): PdfDocument {
            $captured->view = $view;
            $captured->data = $data;

            return Mockery::mock(PdfDocument::class);
        });

        return $captured;
    }
}
