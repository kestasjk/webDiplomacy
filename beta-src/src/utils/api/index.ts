import axios, { AxiosResponse } from "axios";
import ApiRoute from "../../enums/ApiRoute";

export type QueryParams = {
  [key: string]: string;
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

const api = axios.create({
  baseURL: process.env.REACT_APP_WD_BASE_URL,
  headers: {
    "Content-Type": "multipart/form-data",
  },
});

export const getGameApiRequest = (
  route: ApiRoute,
  queryParams: QueryParams,
): Promise<AxiosResponse> =>
  api.get(`api.php?route=${route}&${buildQueryString(queryParams)}`);

const orderSubmission = axios.create({
  baseURL: process.env.REACT_APP_WD_BASE_URL,
  headers: {
    "Content-Type": "application/x-www-form-urlencoded",
  },
});

export const submitOrders = (
  orders,
  queryParams: QueryParams = {},
): Promise<AxiosResponse> => {
  if (Object.keys(queryParams).length) {
    return orderSubmission.post(
      `ajax.php?${buildQueryString(queryParams)}`,
      orders,
    );
  }
  return orderSubmission.post("ajax.php", orders);
};
