export interface Idea {
    id: number;
    text: string;
    createdAt: string;
    groupId?: number;
}

export interface IdeaGroup {
    id: number;
    name: string;
    ideas: Idea[];
}

export interface PlanDetail {
    id: number;
    name: string;
    description: string;
    status: 'active' | 'inactive' | 'archived';
    createdAt: string;
    updatedAt: string;
    ideaGroups: IdeaGroup[];
}
