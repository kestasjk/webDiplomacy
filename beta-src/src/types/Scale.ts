import { Devices } from "../interfaces/Devices";

type Scale = {
  [key in keyof Devices]: number[];
};

export default Scale;
