export interface RequirementsData {
    step1: { projectName: string; team: string; date: string; projectType: string; supervisor: string }
    step2: { context: string; problem: string; whyRobot: string }
    step3: { missions: string[] }
    step4: { users: string[]; otherUsers: string }
    step5: { functions: Array<{ id: string; name: string }> }
    step6: {
        capteurs: Array<{ name: string; role: string }>
        controlUnit: string
        otherControlUnit: string
        actionneurs: Array<{ name: string; role: string }>
        energySource: string
    }
    step7: {
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
    step8: { criteria: Array<{ name: string; value: string }> }
    step9: {
        missionLabel: string
        perceptionLabel: string
        decisionLabel: string
        actionLabel: string
        feedbackLabel: string
    }
    step10: { materials: Array<{ component: string; quantity: string; reference: string }> }
    step11: { planning: Array<{ stage: string; date: string; status: string }> }
    step12: { expectedResult: string; successCriteria: string; testMethod: string }
}

export type RequirementsStepKey = keyof RequirementsData

export const STEP_NAMES = [
    'Informations générales',
    'Contexte & problématique',
    'Mission du robot',
    'Utilisateurs cibles',
    'Fonctions principales',
    'Architecture du robot',
    'Contraintes du projet',
    "Critères de performance",
    'Schéma fonctionnel',
    'Matériel nécessaire',
    'Planning du projet',
    'Résultat attendu',
]

export interface RequirementsOptions {
    userOptions: string[]
    controlUnits: string[]
    energySources: string[]
    safetyConstraints: string[]
    planningStatuses: string[]
}

export interface RequirementsDocumentSummary {
    data: RequirementsData
    version: number
    publishedAt?: string | null
}
