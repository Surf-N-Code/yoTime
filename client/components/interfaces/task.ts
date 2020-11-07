export interface Task {
  '@id'?: string;
  description: string;
  billable?: boolean;
  notes?: string;
  project?: string;
  user?: string;
  timers?: string[];
}
