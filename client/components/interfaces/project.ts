export interface Project {
  '@id'?: string;
  name?: string;
  description?: string;
  timers?: string[];
  tasks?: string[];
  client?: string;
  users?: string[];
}
