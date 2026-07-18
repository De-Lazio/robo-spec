export interface RequirementsData {
    step1: { projectName: string; team: string; date: string; projectType: string; supervisor: string }
    step2: { context: string; problem: string; whyRobot: string }
    step3: { missions: string[] }
    step4: { users: string[]; otherUsers: string }
    step5: { functions: Array<{ id: string; name: string }> }
    step6: {
        maxSize: string
        maxWeight: string
        minAutonomy: string
        minSpeed: string
        maxBudget: string
        estimatedCost: string
        safetyConstraints: string[]
        temperature: string
        usageConditions: string
    }
    step7: { criteria: Array<{ name: string; value: string }> }
    step8: { planning: Array<{ stage: string; date: string; status: string }> }
    step9: { expectedResult: string; successCriteria: string; testMethod: string }
}

export type RequirementsStepKey = keyof RequirementsData

export const STEP_NAMES = [
    'Informations générales',
    'Contexte & problématique',
    'Mission du robot',
    'Utilisateurs cibles',
    'Fonctions principales',
    'Contraintes du projet',
    "Critères de performance",
    'Planning du projet',
    'Résultat attendu',
]

export interface RequirementsOptions {
    userOptions: string[]
    safetyConstraints: string[]
    planningStatuses: string[]
}

export interface RequirementsDocumentSummary {
    data: RequirementsData
    version: number
    publishedAt?: string | null
}
