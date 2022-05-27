export default interface UserActivity {
  lastActive: number;
  lastCall: number;
  makeNewCall: boolean;
  processTime: number | null | undefined;
  frequency: number;
}
