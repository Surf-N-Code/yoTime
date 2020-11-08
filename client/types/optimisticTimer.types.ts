export interface IOptimisticTimer {
  id: string;
  optimisticTimer: boolean;
  timer: {
    date_start: Date;
    date_end?: Date;
    timer_type: string;
  }
}
