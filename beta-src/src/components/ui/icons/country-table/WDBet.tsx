import * as React from "react";

const Bet: React.FC = function (): React.ReactElement {
  return (
    <svg
      width="16"
      height="16"
      viewBox="0 0 16 16"
      fill="none"
      xmlns="http://www.w3.org/2000/svg"
    >
      <path
        fillRule="evenodd"
        clipRule="evenodd"
        d="M8 16C12.4183 16 16 12.4183 16 8C16 3.58172 12.4183 0 8 0C3.58172 0 0 3.58172 0 8C0 12.4183 3.58172 16 8 16ZM4.79785 11.8914V4H8.17207C10.5674 4 11.9729 5.36172 11.9729 7.88828C11.9729 10.4148 10.5619 11.8914 8.17207 11.8914H4.79785ZM7.84394 5.84297H7.1166V10.0484H7.84394C9.00332 10.0484 9.61035 9.34297 9.61035 7.88828C9.61035 6.55938 8.9541 5.84297 7.84394 5.84297Z"
        fill="black"
      />
    </svg>
  );
};

export default Bet;
