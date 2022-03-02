import Vote from "../enums/Vote";

type VoteType = {
  [key in Vote]: boolean;
};

export default VoteType;
