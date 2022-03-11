import VoteType from "../types/Vote";

interface SingleVote {
  vote: string;
  status: boolean;
}

type statusArray = SingleVote[];

export default function voteStateArray(voteState: VoteType): statusArray {
  const voteArray: statusArray = [];

  const keys = Object.keys(voteState);
  const values = Object.values(voteState);

  for (let i = 0; i <= keys.length; i += 1) {
    if (Object.prototype.hasOwnProperty.call(values, i)) {
      const singleVote: SingleVote = {
        vote: keys[i],
        status: values[i],
      };

      voteArray.push(singleVote);
    }
  }

  return voteArray;
}
