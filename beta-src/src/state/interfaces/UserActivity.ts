export default interface UserActivity {
  lastActive: number;
  lastCall: number;
  makeNewCall: boolean;
  frequency: number;
  needsGameData: boolean;
}
