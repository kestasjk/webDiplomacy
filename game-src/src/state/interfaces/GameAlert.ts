/* eslint-disable no-param-reassign */
export default interface GameAlert {
  message: string;
  idx: number;
  visible: boolean;
}

export function setAlert(alert: GameAlert, message: string) {
  alert.message = message;
  alert.idx += 1;
  alert.visible = true;
}
