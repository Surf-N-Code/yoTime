export interface ITimer {
  '@id'?: string;
  '@type'?: string;
  id: number;
  date_start: Date;
  date_end?: Date;
  project?: string;
  task?: string;
  user?: string;
  timer_type: string;
  lastSyncAttempt?: Date;
  optimisticTimer?: boolean;
}
