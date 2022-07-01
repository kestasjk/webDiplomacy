import axios, { AxiosResponse } from "axios";
import ApiRoute from "../../enums/ApiRoute";

export type QueryParams = {
  [key: string]: string;
};

const buildQueryString = (params: QueryParams): string =>
  Object.entries(params)
    .filter(([key, value]) => typeof value !== "undefined")
    .reduce((keyValuePairs: string[], [key, value]) => {
      if (value) {
        keyValuePairs.push(`${encodeURI(key)}=${encodeURI(value)}`);
      }
      return keyValuePairs;
    }, [])
    .join("&");

const api = axios.create({
  // why do we need multipart form-data?
  // headers: {
  //   "Content-Type": "multipart/form-data",
  // },
});

export const getGameApiRequest = (
  route: ApiRoute,
  queryParams: QueryParams,
  timeout?: number,
): Promise<AxiosResponse> =>
  api.get(`../api.php?route=${route}&${buildQueryString(queryParams)}`, {
    timeout,
  });

export const postGameApiRequest = (
  route: ApiRoute,
  json: QueryParams,
  timeout?: number,
): Promise<AxiosResponse> =>
  api.post(`../api.php?route=${route}`, json, { timeout });

const orderSubmission = axios.create({
  // baseURL: process.env.REACT_APP_WD_BASE_URL,
  headers: {
    "Content-Type": "application/x-www-form-urlencoded",
  },
});

export const submitOrders = (
  orders,
  queryParams: QueryParams = {},
): Promise<AxiosResponse> => {
  // console.log({ submittedOrders: orders });
  if (Object.keys(queryParams).length) {
    return orderSubmission.post(
      `../ajax.php?${buildQueryString(queryParams)}`,
      orders,
    );
  }
  return orderSubmission.post("../ajax.php", orders);
};
