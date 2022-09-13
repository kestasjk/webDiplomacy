import React, { ReactElement, FC } from "react";
import { omit } from "lodash";

interface WDButtonProps extends React.ButtonHTMLAttributes<HTMLButtonElement> {
  children: React.ReactNode;
  color?: "primary" | "secondary";
  disabled?: boolean;
  startIcon?: React.ReactNode | undefined;
  doAnimateGlow?: boolean;
}

const WDButton: FC<WDButtonProps> = function ({
  children,
  color,
  disabled,
  startIcon,
  doAnimateGlow,
  ...rest
}): ReactElement {
  return (
    <button
      type="button"
      className={`${rest.className} ${
        color === "primary"
          ? "bg-black text-white hover:bg-gray-500"
          : "bg-white border border-black text-black hover:bg-gray-200"
      } flex justify-center items-center px-3 sm:px-5 py-2.5 rounded-full text-center ${
        disabled && "bg-gray-400 cursor-not-allowed"
      }`}
      disabled={disabled}
      style={{
        animation: doAnimateGlow && !disabled ? "glowing 1s infinite" : "",
        pointerEvents: "auto",
      }}
      // eslint-disable-next-line react/jsx-props-no-spreading
      {...omit(rest, ["className"])}
    >
      <style>
        {`
        @keyframes glowing {
          0% {
            background-color: #447733;
            box-shadow: 0 0 15px #447733;
          }
          50% {
            background-color: #000000;
            box-shadow: 0 0 5px #000000;
          }
          100% {
            background-color: #447733;
            box-shadow: 0 0 15px #447733;
          }
        }`}
      </style>
      {startIcon && <span className="mr-1">{startIcon}</span>}
      {children}
    </button>
  );
};

WDButton.defaultProps = {
  color: "primary",
  disabled: false,
  startIcon: undefined,
  doAnimateGlow: false,
};

export default WDButton;
