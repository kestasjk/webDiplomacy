import axios, { AxiosError, AxiosResponse } from "axios";
import { useCallback, useEffect, useState } from "react";

export enum ApiRoute {
  GAME_TOGGLEVOTE = "game/togglevote",
  GAME_STATUS = "game/status",
  GAME_OVERVIEW = "game/overview",
  GAME_ORDERS = "game/orders",
  GAME_SENDMESSAGE = "game/sendmessage",
  PLAYERS_CD = "players/cd",
  PLAYERS_MISSING_ORDERS = "players/missing_orders",
}

interface ErrorBase<T> {
  error: AxiosError<T> | Error;
}

type QueryParams = {
  [key: string]: string;
};

export const api = axios.create({
  baseURL: process.env.REACT_APP_WD_API_BASE_URL,
  headers: {
    "Content-Type": "multipart/form-data",
  },
});

// gameID comes from query param
// http://localhost/api.php?route=game/overview&gameID=${gameID}

export const useWDApi = (
  asyncFunction: () => Promise<AxiosResponse>,
  onComplete?: (AxiosResponse) => void,
  onError?: (err: ErrorBase<AxiosError | Error>) => void,
  immediate = true,
  runOnInitialLoadOnly = false,
) => {
  const [error, setError] = useState<ErrorBase<AxiosError> | null>(null);
  const [hasNotRunOnce, setHasNotRunOnce] = useState(true);
  const [isLoading, setIsLoading] = useState(false);
  const [response, setResponse] = useState<AxiosResponse | null>(null);

  const callApi = useCallback(async () => {
    setResponse(null);
    setError(null);
    setIsLoading(true);

    if (hasNotRunOnce) {
      setHasNotRunOnce(false);
    }

    try {
      const asyncResponse = await asyncFunction();
      setResponse(asyncResponse);
      onComplete && onComplete(asyncResponse);
    } catch (err) {
      const caughtError = err as ErrorBase<AxiosError>;
      setError(caughtError);
      onError && onError(caughtError);
    } finally {
      setIsLoading(false);
    }
  }, [asyncFunction, hasNotRunOnce, onComplete]);

  useEffect(() => {
    if (immediate || (runOnInitialLoadOnly && hasNotRunOnce)) {
      callApi();
    }
  }, [callApi, hasNotRunOnce, immediate, runOnInitialLoadOnly]);

  return {
    callApi,
    status: response?.status,
    response,
    error,
    isLoading,
  };
};

const buildQueryString = (params: QueryParams): string =>
  Object.entries(params)
    .reduce((keyValuePairs: string[], [key, value]) => {
      if (value) {
        keyValuePairs.push(`${encodeURI(key)}=${encodeURI(value)}`);
      }
      return keyValuePairs;
    }, [])
    .join("&");

export const getGameApiRequest = (
  route: ApiRoute,
  queryParams: QueryParams,
): Promise<AxiosResponse | Error> =>
  api.get(`?route=${route}&${buildQueryString(queryParams)}`);
