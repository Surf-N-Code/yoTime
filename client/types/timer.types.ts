export interface ITimer {
  '@id'?: string;
  date_start?: Date;
  date_end?: Date;
  project?: string;
  task?: string;
  user?: string;
  timer_type?: string;
}
