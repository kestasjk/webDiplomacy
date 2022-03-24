import { AxiosError } from "axios";

type GameErrorResponse = AxiosError | Error | string | null | undefined;

export default GameErrorResponse;
