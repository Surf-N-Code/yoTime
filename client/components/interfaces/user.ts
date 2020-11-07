export interface User {
  '@id'?: string;
  email?: string;
  username?: string;
  slack_user_id?: string;
  tz?: string;
  tz_offset?: string;
  display_name?: string;
  projects?: string[];
  tasks?: string[];
  clients?: string[];
  slack_teams?: any;
  contract_work_hours?: number;
  daily_summary?: string[];
  full_name?: string;
  timers?: string[];
  roles?: any;
  password?: string;
  is_active?: boolean;
  readonly salt?: any;
}
