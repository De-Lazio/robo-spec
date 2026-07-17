export type Page =
  | 'login'
  | 'register'
  | 'forgot-password'
  | 'reset-password'
  | 'dashboard'
  | 'project-detail'
  | 'cdc-wizard'

export type ProjectTab = 'overview' | 'cdc' | 'resources' | 'github' | 'team' | 'settings'

export type ProjectType = 'mobile' | 'arm' | 'drone' | 'fixed' | 'humanoid' | 'other'
export type ProjectStatus = 'draft' | 'in-progress' | 'testing' | 'completed' | 'archived'
export type ResourceCategory = 'mécanique' | 'électronique' | 'informatique' | 'autre'
export type ResourceType = 'image' | 'pdf' | 'code' | 'cad' | 'schema' | 'doc' | 'archive' | 'other'

export interface User {
  id: string
  name: string
  email: string
  role: 'admin' | 'member' | 'viewer'
  avatar?: string
  organization?: string
}

export interface TeamMember {
  id: string
  name: string
  email: string
  role: 'chef' | 'mécanique' | 'électronique' | 'informatique' | 'autre'
  avatar?: string
  joinedAt: string
}

export interface Resource {
  id: string
  name: string
  type: ResourceType
  category: ResourceCategory
  size: number
  uploadedBy: string
  uploadedAt: string
  description?: string
  url?: string
}

export interface Commit {
  hash: string
  message: string
  author: string
  date: string
}

export interface GitHubRepo {
  url: string
  name: string
  owner: string
  description: string
  stars: number
  forks: number
  openIssues: number
  lastCommit: string
  defaultBranch: string
  branches: string[]
  commits: Commit[]
  language: string
}

export interface CDCData {
  step1: {
    projectName: string
    team: string
    date: string
    projectType: string
    supervisor?: string
  }
  step2: {
    context: string
    problem: string
    whyRobot: string
  }
  step3: {
    missions: string[]
  }
  step4: {
    users: string[]
    otherUsers: string
  }
  step5: {
    functions: Array<{ id: string; name: string }>
  }
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
  step8: {
    criteria: Array<{ name: string; value: string }>
  }
  step9: {
    missionLabel: string
    perceptionLabel: string
    decisionLabel: string
    actionLabel: string
    feedbackLabel: string
  }
  step10: {
    materials: Array<{ component: string; quantity: string; reference?: string }>
  }
  step11: {
    planning: Array<{ stage: string; date: string; status: string }>
  }
  step12: {
    expectedResult: string
    successCriteria: string
    testMethod: string
  }
}

export interface Project {
  id: string
  name: string
  description: string
  type: ProjectType
  status: ProjectStatus
  category: string
  tags: string[]
  team: TeamMember[]
  progress: number
  createdAt: string
  updatedAt: string
  hasCDC: boolean
  cdcData?: CDCData
  resources: Resource[]
  githubRepo?: GitHubRepo
}
