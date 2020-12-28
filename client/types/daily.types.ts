import {IUser} from "./user.types";

export interface IDaily {
  id: number;
  '@id'?: string;
  daily_summary: string;
  date: Date;
  time_worked_in_s: number;
  time_break_in_s?: number;
  user: IUser[];
  is_email_sent: boolean;
  start_time: Date;
  end_time: Date;
  is_synced_to_personio: boolean;
}
