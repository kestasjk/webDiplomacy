export default interface UserActivity {
  lastActive: number;
  lastCall: number;
  makeNewCall: boolean;
  season: string;
  year: number;
  processTime: number | null | undefined;
}
