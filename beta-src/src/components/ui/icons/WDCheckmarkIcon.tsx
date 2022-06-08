import * as React from "react";

interface WDCheckmarkIconProps {
  // eslint-disable-next-line react/require-default-props
  color?: string;
}

const WDCheckmarkIcon: React.FC<WDCheckmarkIconProps> = function ({
  color = "#000",
}): React.ReactElement {
  return (
    <svg
      width={17}
      height={16}
      viewBox="0 0 14 14"
      fill="none"
      xmlns="http://www.w3.org/2000/svg"
    >
      <path d="m2 6.164 2.5 2.5 5.5-7.5" stroke={color} strokeWidth="3" />
    </svg>
  );
};

export default WDCheckmarkIcon;
