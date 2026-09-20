import * as React from "react";
import { useTheme } from "@mui/material";

const Centers: React.FC = function (): React.ReactElement {
  const theme = useTheme();
  return (
    <svg
      width="18"
      height="16"
      viewBox="0 0 18 18"
      fill="none"
      xmlns="http://www.w3.org/2000/svg"
    >
      <path
        d="M9.00005 17C13.4183 17 17.0001 13.4183 17.0001 9C17.0001 4.58172 13.4183 1 9.00005 1C4.58174 1 1 4.58172 1 9C1 13.4183 4.58174 17 9.00005 17Z"
        stroke="black"
      />
      <path
        d="M9.00324 13.3635C11.4132 13.3635 13.3668 11.4098 13.3668 8.99987C13.3668 6.5899 11.4132 4.63623 9.00324 4.63623C6.5933 4.63623 4.63965 6.5899 4.63965 8.99987C4.63965 11.4098 6.5933 13.3635 9.00324 13.3635Z"
        fill={theme.palette.primary.main}
      />
    </svg>
  );
};

export default Centers;
