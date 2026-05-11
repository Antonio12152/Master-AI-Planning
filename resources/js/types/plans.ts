export interface Plan {
  id: number;
  name: string;
  description: string;
  ideasCount: number;
  status: 'active' | 'inactive' | 'archived';
  createdAt: string;
  updatedAt: string;
}