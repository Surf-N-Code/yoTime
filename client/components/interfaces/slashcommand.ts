export interface SlashCommand {
  '@id'?: string;
  user_id?: string;
  user_name?: string;
  team_id?: string;
  channel_id?: string;
  channel_name?: string;
  command?: string;
  response_url?: string;
  trigger_id?: string;
  text?: string;
}
