<?php

namespace Tests\Unit\Domain\Requirements;

use App\Domain\Requirements\Rules\RequirementsStepRules;
use Illuminate\Support\Facades\Validator;
use Tests\TestCase;

class RequirementsStepRulesTest extends TestCase
{
    public function test_step4_requires_a_category_or_the_free_text_field(): void
    {
        $this->assertTrue(Validator::make(['users' => [], 'otherUsers' => ''], RequirementsStepRules::rules(4))->fails());
        $this->assertTrue(Validator::make(['users' => ['Élèves'], 'otherUsers' => ''], RequirementsStepRules::rules(4))->passes());
        $this->assertTrue(Validator::make(['users' => [], 'otherUsers' => 'Vétérinaires'], RequirementsStepRules::rules(4))->passes());
    }

    public function test_step5_rejects_duplicate_function_ids(): void
    {
        $duplicate = ['functions' => [
            ['id' => 'F1', 'name' => 'Détecter'],
            ['id' => 'F1', 'name' => 'Éviter'],
        ]];
        $this->assertTrue(Validator::make($duplicate, RequirementsStepRules::rules(5))->fails());

        $unique = ['functions' => [
            ['id' => 'F1', 'name' => 'Détecter'],
            ['id' => 'F2', 'name' => 'Éviter'],
        ]];
        $this->assertTrue(Validator::make($unique, RequirementsStepRules::rules(5))->passes());
    }

    public function test_step9_requires_all_five_labels(): void
    {
        $incomplete = ['missionLabel' => 'Mission', 'perceptionLabel' => '', 'decisionLabel' => 'Décision', 'actionLabel' => 'Action', 'feedbackLabel' => 'Retour'];
        $this->assertTrue(Validator::make($incomplete, RequirementsStepRules::rules(9))->fails());

        $complete = ['missionLabel' => 'Mission', 'perceptionLabel' => 'Perception', 'decisionLabel' => 'Décision', 'actionLabel' => 'Action', 'feedbackLabel' => 'Retour'];
        $this->assertTrue(Validator::make($complete, RequirementsStepRules::rules(9))->passes());
    }
}
