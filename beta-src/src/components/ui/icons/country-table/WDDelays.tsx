import * as React from "react";

const Delays: React.FC = function (): React.ReactElement {
  return (
    <svg
      width="16"
      height="16"
      viewBox="0 0 16 16"
      fill="none"
      xmlns="http://www.w3.org/2000/svg"
    >
      <circle cx="8" cy="8" r="7" stroke="black" strokeWidth="2" />
      <path d="M8 3.5L8 8.5L11 10.5" stroke="black" strokeWidth="2" />
    </svg>
  );
};

export default Delays;
