import type { CdcFunction } from '@/types/technicalChoices'

export type TaskStatus = 'todo' | 'in-progress' | 'done'

export const TASK_STATUSES: TaskStatus[] = ['todo', 'in-progress', 'done']

export const taskStatusLabels: Record<TaskStatus, string> = {
    todo: 'À faire',
    'in-progress': 'En cours',
    done: 'Terminé',
}

export interface AssignableUser {
    id: number
    name: string
}

export interface Task {
    id: number
    title: string
    description: string | null
    status: TaskStatus
    due_date: string | null
    linked_function_ids: string[]
    assignee: AssignableUser | null
}

export type { CdcFunction }
