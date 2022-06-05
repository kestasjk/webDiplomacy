import Move from "../enums/Move";

type MoveStatus = {
  [key in Move]: boolean;
};

export default MoveStatus;
