import * as React from "react";

interface LefGameIconProps {
  height?: number;
}

const LeftGameIcon: React.FC<LefGameIconProps> = function ({
  height,
}): React.ReactElement {
  return (
    <svg
      width={height}
      height={height}
      viewBox="0 0 18 18"
      fill="none"
      xmlns="http://www.w3.org/2000/svg"
    >
      <path
        fillRule="evenodd"
        clipRule="evenodd"
        d="M13.7245 4.72425L18 8.99998L13.7245 13.2757L12.1337 11.685L13.6936 10.1251H5.07541V7.87511H13.6933L12.1334 6.31524L13.7245 4.72425ZM11.9953 4.60547V0H0V18H11.9953V13.3945H9.74508V15.7499H2.24975V2.25005H9.74508V4.6054H11.9952L11.9953 4.60547Z"
        fill="#C00"
      />
    </svg>
  );
};

LeftGameIcon.defaultProps = {
  height: 18,
};

export default LeftGameIcon;
